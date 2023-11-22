<?php

namespace Drupal\band_booking_performance;

use Drupal\band_booking_registration\Entity\Registration;
use Drupal\band_booking_registration\RegistrationHelper;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Service to provide helpers functions to performance module.
 */
class PerformanceHelper implements PerformanceHelperInterface {

  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * @param EntityTypeManagerInterface $entity_type_manager
   * @param TranslationInterface $string_translation
   * @param MessengerInterface $messenger
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    TranslationInterface $string_translation,
    MessengerInterface $messenger
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public function performanceReminder(bool $manual = false, bool $force = false, array $nids = [], bool $startFromContextualTs = false, int $contextualTimestamp = null, int $current_date = null): void {
    // Get list of reminders.
    $sortedReminders = $this->getPerformancesRemindersSortedByNode($force, $nids, $startFromContextualTs, $contextualTimestamp, $current_date);

    // Batch : one operation = 1 node.
    if (!empty($sortedReminders)) {
      // Prepare variables.
      $nodes = Node::loadMultiple(array_keys($sortedReminders));
      $countReminders = 0;
      $operations = [];

      // Foreach node containing reminders.
      foreach ($sortedReminders as $nid => $reminders) {
        $countReminders += count($reminders);

        // Prepare users array.
        $users_id = [];
        foreach ($reminders as $reminder){
          $users_id[] = $reminder['uid'];
        }
        $users = User::loadMultiple($users_id);

        // Add node's reminders. Reminders will be splited inside operation.
        $operations[] = [
          '\Drupal\band_booking_performance\PerformanceHelper::batchPerformanceReminderOperation',
          [
            $manual,
            $nodes[$nid],
            $users,
            $reminders,
            $this->t('Reminders for event "@node"', ['@node' => $nodes[$nid]->getTitle()]),
          ],
        ];
      }

      // Prepare operations, 1 op = 1 node.
      $batch = [
        'title' => $this->formatPlural(
          $countReminders,
          'Sending a single reminder', 'Sending @count reminders',
          ['@count' => $countReminders]
        ),
        'operations' => $operations,
        'finished' => '\Drupal\band_booking_performance\PerformanceHelper::batchPerformanceReminderFinished',
      ];

      batch_set($batch);
    }
    else {
      $message = $this->t('No reminder to send.');
      $this->messenger->addMessage($message, 'status', TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPerformancesReminders(bool $force = false, array $nids = [], bool $startFromContextualTs = false, int $contextualTimestamp = null, int $current_date = null): array {
    // Connection.
    $connection = \Drupal::database();
    $query = $connection->select('node', 'n');

    // Ensure type is performance. TODO : performance or standard name ?
    $query->where('n.type = :type', [
      ':type' => 'performance',
    ]);

    // Ensure node is published.
    $query->leftjoin('node_field_data', 'nfd', 'nfd.nid = n.nid');
    $query->where('nfd.status = 1');

    // Ensure event is coming.
    $current_date = !is_null($current_date) ? $current_date : time();
    $min_day = date('Y-m-d', $current_date);
    $query->leftjoin('node__field_date_non_utc', 'dt', 'dt.entity_id = n.nid');
    $query->where('dt.field_date_non_utc_value >= :min_day', [
      ':min_day' => $min_day,
    ]);
    // TODO : order by date. Doesn't work for the moment.
    // $query->orderBy('dt.field_date_non_utc_value', 'DESC');

    // Ensure event is not canceled.
    $query->leftjoin('node__field_confirm', 'nfc', 'nfc.entity_id = n.nid');
    // TODO : param for remind depending on confirmation  ?
    $query->where('nfc.field_confirm_value  != :conf', [
      ':conf' => 'canceled',
    ]);

    // Join registrations ; only registration with "waiting" state.
    $query->leftjoin('registration_field_data', 'rfd', 'rfd.nid = n.nid');
    $query->isNotNull('rfd.registration_user_id');
    $query->leftjoin('registration__field_state', 'st', 'st.entity_id = rfd.id');
    // TODO : param for remind depending on status  ?
    $query->where('st.field_state_value = :state', [
      ':state' => 'waiting',
    ]);
    // Search in this file : $query->condition('st.field_state_value', $states, 'IN');

    // Force avoid using $nids option and get every node ignoring relaunch day.
    if (!$force) {
      // If nodes id are specified OR manage relaunch date, only if nids are not specified.
      if (!empty($nids)) {
        $query->condition('n.nid', $nids, 'IN');
      } else {
        // Get relaunch day to be taken into account.
        $query->leftjoin('node__field_relaunch', 'rl', 'rl.entity_id = n.nid');
        $day = date('Y-m-d', $contextualTimestamp ?? time());

        // Takes every node after OR for the day.
        if ($startFromContextualTs) {
          // This generates duplicates, as this is multiple value field. So, we use distinct.
          $query->condition('rl.field_relaunch_value', $day, '>=');
        } else {
          $query->condition('rl.field_relaunch_value', $day, '=');
        }
      }
    }

    // Get necessary fields.
    $query->fields('n', ['nid']);
    $query->fields('rfd', ['id', 'registration_user_id']);

    // We use distinct because of the field_relaunch_value which is multiple value field.
    return $query->distinct()->execute()->fetchAll();
  }

  /**
   * {@inheritdoc}
   */
  public function getPerformancesRemindersSortedByNode(bool $force = false, array $nids = [], bool $startFromContextualTs = false, int $contextualTimestamp = null, int $current_date = null): array {
    $reminders = $this->getPerformancesReminders($force, $nids, $startFromContextualTs, $contextualTimestamp, $current_date);
    $sortedReminders = [];
    $current_nid = null;

    $count = 0;
    foreach ($reminders as $reminder) {
      // If first reminder or new node.
      if (
        isset($reminders[$count]) &&
        $reminders[$count]->nid != $current_nid
      ) {
        $current_nid = $reminders[$count]->nid;
        $sortedReminders[$current_nid] = [];
      }

      $sortedReminders[$current_nid][] = [
        'rid' => $reminder->id,
        'uid' => $reminder->registration_user_id,
      ];
      $count++;
    }

    return $sortedReminders;
  }

  /**
   * {@inheritdoc}
   * @throws EntityStorageException
   */
  public static function batchPerformanceReminderOperation(bool $manual, Node $node, array $users, array $reminders, $operation_details, &$context) :void {
    if (empty($context['sandbox'])) {
      $context['sandbox'] = [];
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_node'] = 0;
      $context['sandbox']['max'] = count($reminders);
    }

    // Process in groups of 5 (arbitrary value).
    $limit = 4; // "4" for group of 5 as it begins with 0.

    // Retrieve the next group.
    $result = range($context['sandbox']['current_node'], $context['sandbox']['current_node'] + $limit);

    foreach ($result as $row) {
      // Do not go above maximum results.
      if ($row > $context['sandbox']['max'] - 1) {
        return;
      }

      // Prepare variables.
      /** @var User $user */
      $uid = $reminders[$row]['uid'];
      $user = $users[$uid];
      $rid = $reminders[$row]['rid'];
      $registration = Registration::load($rid);

      // Prepare variables to send mail.
      $currentUser = \Drupal::currentUser();
      $object = $node->get('field_reminder_mail_object')->getValue()[0]['value'];
      $message = $node->get('field_reminder_mail_content')->getValue()[0]['value'];

      // $module tells in which .module to find hook_mail. See band_booking_registration_mail.
      $module = 'band_booking_registration';
      // For 'key' is used inside the hook_mail.
      $key = 'performance_reminder';

      // Prepare and send mail.
      $mail = RegistrationHelper::getMailObjectAndMessageFromToken(
        $user,
        $object,
        $message,
        [
          'node' => $node,
          'registration' => $registration,
          'user' => $currentUser,
        ],
        [
          'node' => $node,
          'registration' => $registration,
          'user' => $currentUser,
        ],
      );
      $mailResult = RegistrationHelper::bookingSendMail($module, $key, $user, $mail['object'], $mail['message']);

      // Results passed to the 'finished' callback.
      $context['results'][] = [
        // TODO : check if mail() return a result key or nothing. Impossible to find.
        'status' => $mailResult['result'] ? 'status' : 'error',
        'action_name' => t('"@user" on the "@event" performance',
          [
            '@user' => $user->getAccountName(),
            '@event' => $node->getTitle(),
          ]
        ),
        'manual' => $manual ?? false,
      ];

      // Update our progress information.
      $context['sandbox']['progress']++;
      $context['sandbox']['current_node'] = $row + 1;
      $context['message'] = t('Running Batch "@id" for user "@user" on event "@event"',
        [
          '@id' => $row,
          '@user' => $user->getAccountName(),
          '@event' => $node->getTitle(),
        ]
      );
    }

    // Finished ? TODO check if correctly used, works perfectly for the moment.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = ($context['sandbox']['progress'] > $context['sandbox']['max']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function batchPerformanceReminderFinished($success, $results, $operations):void {
    $translation = \Drupal::translation();

    // Check only the first result, FALSE if nothing.
    $manual = $results[0]['manual'] ?? FALSE;

    if ($manual) {
      $messenger = \Drupal::messenger();
    }

    if ($success) {
      // Prepare results messages.
      $successfulResults = [];
      $failedResults = [];
      foreach ($results as $result) {
        if ($result['status'] == 'error') {
          $failedResults[] = $result['action_name'];
        } else {
          $successfulResults[] = $result['action_name'];
        }
      }

      // Success messages.
      $amountSuccessful = count($successfulResults);
      if ($amountSuccessful >= 1) {
        if ($manual) {
          $message = $translation->formatPlural(
            $amountSuccessful,
            'One single successful reminder :', '@count successful reminders :',
            ['@count' => $amountSuccessful],
          );
          $messenger->addMessage($message, 'status');
          foreach ($successfulResults as $successfulResult) {
            $messenger->addMessage($successfulResult, 'status', TRUE);
          }
        } else {
          foreach ($successfulResults as $successfulResult) {
            $prefix = t('Successful reminder') . ' : ';
            \Drupal::logger('band_booking_performance')->info($prefix . $successfulResult);
          }
        }
      }

      // Errors messages.
      $amountFailed = count($failedResults);
      if ($amountFailed >= 1) {
        if ($manual) {
          $message = $translation->formatPlural(
            $amountFailed,
            'One single failed reminder :', '@count failed reminders :',
            ['@count' => $amountFailed],
          );
          $messenger->addMessage($message, 'error');
          foreach ($failedResults as $failedResult) {
            $messenger->addMessage($failedResult, 'error', TRUE);
          }
        } else {
          $prefix = t('Failed reminder') . ' : ';
          foreach ($failedResults as $failedResult) {
            \Drupal::logger('band_booking_performance')->error($prefix . $failedResult);
          }
        }

      }
    }
    else {
      // An error occurred.
      $error_operation = reset($operations);
      $message = t('An error occurred while processing @operation with arguments : @args',
        [
          '@operation' => $error_operation[0],
          '@args' => print_r($error_operation[0], TRUE),
        ]
      );

      if ($manual) {
        $messenger->addMessage($message);
      } else {
        \Drupal::logger('band_booking_performance')->error($message);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function performanceChanged(Node $performance, string $object, string $message, array $registrationStates = ['all'], bool $manual = true): void {
    // Get list of registrations, easier to get every data for mail's token.
    $rids = $this->getPerformanceRegistrationsId($performance);

    // Batch : only one operation = 1 node, but multiple sub-operations
    if (!empty($rids)) {
      // Prepare variables. , and convert object to array.
      // Load registrations.
      $tempRegistrations = $this->entityTypeManager->getStorage('registration')->loadMultiple($rids);
      // Prepare users id array.
      $users_id = [];
      // Convert registrations object to registrations array.
      $registrations = [];
      foreach ($tempRegistrations as $registration) {
        $registrations[] = $registration;
        $registration_user_id = $registration->get('registration_user_id')->getValue();
        $users_id[] = $registration_user_id[0]['target_id'] ?? null;
      }
      unset($tempRegistrations);

      // Load users and convert object to array.
      $tempUsers = User::loadMultiple($users_id);
      $users = [];
      foreach ($tempUsers as $user) {
        $users[] = $user;
      }
      unset($tempUsers);

      $count = count($users);
      $operations = [];

      // Add node's reminders. Reminders will be splited inside operation.
      $operations[] = [
        '\Drupal\band_booking_performance\PerformanceHelper::batchPerformanceChangedOperation',
        [
          $manual,
          $performance,
          $registrations,
          $users,
          'band_booking_registration',
          'performance_deleted',
          $object,
          $message,
          $this->formatPlural(
            $count,
            'Send one mail for \"@event\"', 'Send @count mails for \"@event\"',
            [
              '@count' => $count,
              '@event' => $performance->getTitle(),
            ]
          ),
        ],
      ];

      // Prepare operations, 1 op = many users.
      $batch = [
        'title' => $this->formatPlural(
          $count,
          'Sending a single mail', 'Sending @count mails',
          ['@count' => $count]
        ),
        'operations' => $operations,
        'finished' => '\Drupal\band_booking_performance\PerformanceHelper::batchPerformanceChangedFinished',
      ];

      batch_set($batch);
    }
    else {
      $message = $this->t('No mail to send.');
      $this->messenger->addMessage($message, 'status', TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPerformanceRegistrationsId(Node $performance, array $registrationStates = ['all'], bool $getUserId = false): array {
    // Connection.
    $connection = \Drupal::database();

    // For the current node, ensure type is performance and node is published.
    $query = $connection->select('node', 'n');
    $query->condition('n.nid', $performance->id(), '=');
    $query->where('n.type = :type', [
      ':type' => 'performance',
    ]);
    $query->leftjoin('node_field_data', 'nfd', 'nfd.nid = n.nid');
    $query->where('nfd.status = 1');

    // Ensure event is coming.
    $current_date = time();
    $min_day = date('Y-m-d', $current_date);
    $query->leftjoin('node__field_date_non_utc', 'dt', 'dt.entity_id = n.nid');
    $query->where('dt.field_date_non_utc_value >= :min_day', [
      ':min_day' => $min_day,
    ]);

    // Ensure event is not canceled.
    /* TODO delete if not necessary for the client.
    $query->leftjoin('node__field_confirm', 'nfc', 'nfc.entity_id = n.nid');
    $query->where('nfc.field_confirm_value  != :conf', [
      ':conf' => 'canceled',
    ]); */

    // Join registrations data.
    $query->leftjoin('registration_field_data', 'rfd', 'rfd.nid = n.nid');
    $query->isNotNull('rfd.registration_user_id');
    $query->leftjoin('registration__field_state', 'st', 'st.entity_id = rfd.id');
    // Check registration states, or not.
    $all = in_array('all', $registrationStates);
    if (!$all) {
      $query->condition('st.field_state_value', $registrationStates, 'IN');
    }

    // Get necessary fields.
    if ($getUserId) {
      $query->fields('rfd', ['registration_user_id']);
    } else {
      $query->fields('rfd', ['id']);
    }

    // Execute and return array of id.
    return $query->execute()->fetchCol();
  }

  /**
   * {@inheritdoc}
   * @throws EntityStorageException
   */
  public static function batchPerformanceChangedOperation(bool $manual, Node $performance, array $registrations, array $users, string $module, string $key, string $object, string $message, $operation_details, &$context) :void {
    if (empty($context['sandbox'])) {
      $context['sandbox'] = [];
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_node'] = 0;
      $context['sandbox']['max'] = count($users);
    }

    // Process in groups of 6 (arbitrary value).
    $limit = 5; // "5" for group of 5 as it begins with 0.

    // Retrieve the next group.
    $result = range($context['sandbox']['current_node'], $context['sandbox']['current_node'] + $limit);

    foreach ($result as $row) {
      // Do not go above maximum results.
      if ($row > $context['sandbox']['max'] - 1) {
        return;
      }

      // Prepare variables.
      /** @var User $destinationUser */
      $destinationUser = $users[$row];
      $currentUser = \Drupal::currentUser();

      // Prepare and send mail.
      $mail = RegistrationHelper::getMailObjectAndMessageFromToken(
        $destinationUser,
        $object,
        $message,
        [
          'node' => $performance,
          'registration' => $registrations[$row],
          'user' => $currentUser,
        ],
        [
          'node' => $performance,
          'registration' => $registrations[$row],
          'user' => $currentUser,
        ],
      );
      $mailResult = RegistrationHelper::bookingSendMail($module, $key, $destinationUser, $mail['object'], $mail['message']);

      // Results passed to the 'finished' callback.
      $context['results'][] = [
        // TODO : check if mail() return a result key or nothing. Impossible to find.
        'status' => $mailResult['result'] ? 'status' : 'error',
        'action_name' => t('Mail sent to "@user"',
          [
            '@user' => $destinationUser->getAccountName(),
          ]
        ),
        'manual' => $manual ?? false,
      ];

      // Update our progress information.
      $context['sandbox']['progress']++;
      $context['sandbox']['current_node'] = $row + 1;
      $context['message'] = t('Running Batch "@id" for user "@user"',
        [
          '@id' => $row,
          '@user' => $destinationUser->getAccountName(),
        ]
      );
    }

    // Finished ? TODO check if correctly used, works perfectly for the moment.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = ($context['sandbox']['progress'] > $context['sandbox']['max']);
    }
  }

  /**
   * TODO : same function as for the reminder => only one function ?
   * {@inheritdoc}
   */
  public static function batchPerformanceChangedFinished($success, $results, $operations):void {
    $translation = \Drupal::translation();

    // Check only the first result, FALSE if nothing.
    $manual = $results[0]['manual'] ?? FALSE;

    if ($manual) {
      $messenger = \Drupal::messenger();
    }

    if ($success) {
      // Prepare results messages.
      $successfulResults = [];
      $failedResults = [];
      foreach ($results as $result) {
        if ($result['status'] == 'error') {
          $failedResults[] = $result['action_name'];
        } else {
          $successfulResults[] = $result['action_name'];
        }
      }

      // Success messages.
      $amountSuccessful = count($successfulResults);
      if ($amountSuccessful >= 1) {
        if ($manual) {
          $message = $translation->formatPlural(
            $amountSuccessful,
            'Successful operation :', '@count successful operations :',
            ['@count' => $amountSuccessful],
          );
          $messenger->addMessage($message, 'status');
          foreach ($successfulResults as $successfulResult) {
            $messenger->addMessage($successfulResult, 'status', TRUE);
          }
        } else {
          foreach ($successfulResults as $successfulResult) {
            $prefix = t('Successful operation') . ' : ';
            \Drupal::logger('band_booking_performance')->info($prefix . $successfulResult);
          }
        }
      }

      // Errors messages.
      $amountFailed = count($failedResults);
      if ($amountFailed >= 1) {
        if ($manual) {
          $message = $translation->formatPlural(
            $amountFailed,
            'One single failed reminder :', '@count failed reminders :',
            ['@count' => $amountFailed],
          );
          $messenger->addMessage($message, 'error');
          foreach ($failedResults as $failedResult) {
            $messenger->addMessage($failedResult, 'error', TRUE);
          }
        } else {
          $prefix = t('Failed reminder') . ' : ';
          foreach ($failedResults as $failedResult) {
            \Drupal::logger('band_booking_performance')->error($prefix . $failedResult);
          }
        }

      }
    }
    else {
      // An error occurred.
      $error_operation = reset($operations);
      $message = t('An error occurred while processing @operation with arguments : @args',
        [
          '@operation' => $error_operation[0],
          '@args' => print_r($error_operation[0], TRUE),
        ]
      );

      if ($manual) {
        $messenger->addMessage($message);
      } else {
        \Drupal::logger('band_booking_performance')->error($message);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultDeletedPerformanceMailObject(): string {
    $config = \Drupal::config('system.site');
    return t('"[node:title]" deleted by [user:display-name] | @site',
      [
        '@site' => $config->get('name'),
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultDeletedPerformanceMailMessage(): string {
    return t('<p>Hello [registration:registration_user_id:entity:display-name],</p><p>"[node:title]" has been deleted.</p><p>[user:display-name].</p>');
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultDateChangedPerformanceMailObject(): string {
    $config = \Drupal::config('system.site');
    return t('"[node:title]" date modified by [user:display-name] | @site',
      [
        '@site' => $config->get('name'),
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultDateChangedPerformanceMailMessage(): string {
    return t('<p>Hello [registration:registration_user_id:entity:display-name],</p><p>The date of "[node:title]" changed for [node:field_date:date:bb_medium].</p><p>[user:display-name].</p>');
  }

}
