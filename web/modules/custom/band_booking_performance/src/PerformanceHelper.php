<?php

namespace Drupal\band_booking_performance;

use Drupal\band_booking_registration\Entity\Registration;
use Drupal\band_booking_registration\RegistrationHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Service to provide ....
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
  public function performanceReminder(array $nids = [], int $contextualTimestamp = null, int $current_date = null): void {
    // Get list of reminders.
    $sortedReminders = $this->getPerformancesRemindersSortedByNode($nids, $contextualTimestamp, $current_date);

    // Batch : une opération = 1 node
    // Dans le batch, tous les 5 utilisateurs on découpe.
    if (!empty($sortedReminders)) {
      // Prepare nodes.
      $nodes = Node::loadMultiple(array_keys($sortedReminders));


      $countReminders = 0;
      $operations = [];

      // Foreach node containing reminders.
      foreach ($sortedReminders as $nid => $reminders) {
        $users_id = [];
        foreach ($reminders as $reminder){
          $users_id[] = $reminder['uid'];
        }
        $users = User::loadMultiple($users_id);

        $countReminders += count($reminders);

        // Add node's reminders. Reminders will be splited inside operation.
        $operations[] = [
          '\Drupal\band_booking_performance\PerformanceHelper::batchPerformanceReminderOperation',
          [
            $nodes[$nid],
            $users,
            $reminders,
            // TODO.
            //$this->t('(Amount of operations : @operations)', ['@operations' => $currentOperationRemindersCount]),
            $this->t('(Reminders for event : @node)', ['@node' => $nodes[$nid]->getTitle()]),
          ],
        ];
      }

      // Prepare operations, 1 op = 1 node.
      $batch = [
        // TODO plural + count reminders.
        'title' => $this->t('Send @num reminders', ['@num' => $countReminders]),
        'operations' => $operations,
        'finished' => '\Drupal\band_booking_performance\PerformanceHelper::batchPerformanceReminderFinished',
      ];

      batch_set($batch);
    }
    else {
      // TODO.
      $message = $this->t('No user to register.');
      $this->messenger->addMessage($message, 'status', TRUE);
    }
  }

  /**
   * Todo = if nids !!!.
   *
   * {@inheritdoc}
   */
  public function getPerformancesReminders(array $nids = [], int $contextualTimestamp = null, int $current_date = null): array {
    // Connection.
    $connection = \Drupal::database();
    $query = $connection->select('node', 'n');

    // Ensure type is performance.
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

    // Ensure event is not canceled.
    $query->leftjoin('node__field_confirmation', 'nfc', 'nfc.entity_id = n.nid');
    $query->where('nfc.field_confirmation_value  != :conf', [
      ':conf' => 'canceled',
    ]);

    // Only node with relaunch for the date, or all after current day.
    $day = date('Y-m-d', $contextualTimestamp ?? time());
    $query->leftjoin('node__field_relaunch', 'rl', 'rl.entity_id = n.nid');
    // If timestamp is given, relaunch only for the day, otherwise relaunch up to the day.
    if (isset($contextualTimestamp)) {
      $query->where('rl.field_relaunch_value = :day', [
        ':day' => $day,
      ]);
    } else {
      $query->where('rl.field_relaunch_value >= :day', [
        ':day' => $day,
      ]);
    }

    // Join registrations ; only registration with "waiting" state.
    $query->leftjoin('registration_field_data', 'rfd', 'rfd.nid = n.nid');
    $query->isNotNull('rfd.registration_user_id');
    $query->leftjoin('registration__field_state', 'st', 'st.entity_id = rfd.id');
    $query->where('st.field_state_value = :state', [
      ':state' => 'waiting',
    ]);

    // Get necessary fields. TODO : check if all necessary.
    $query->fields('n', ['nid']);
    $query->fields('rfd', ['id', 'registration_user_id']);

    return $query->execute()->fetchAll();
  }

  /**
   * {@inheritdoc}
   */
  public function getPerformancesRemindersSortedByNode(array $nids = [], int $contextualTimestamp = null, int $current_date = null): array {
    $reminders = $this->getPerformancesReminders($nids, $contextualTimestamp, $current_date);
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

      //$sortedReminders[$current_nid][$reminder->id] = $reminder->registration_user_id;
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
  public static function batchPerformanceReminderOperation(Node $node, array $users, array $reminders, $operation_details, &$context) :void {
    // Use the $context['sandbox'] at your convenience to store the
    // information needed to track progression between successive calls.
    if (empty($context['sandbox'])) {
      $context['sandbox'] = [];
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_node'] = 0;
      $context['sandbox']['max'] = count($reminders);
      // TODO.
      $context['sandbox']['usernames'] = [];
    }

    // TODO.
    // Process in groups of 2 (arbitrary value).
    $limit = 2; // 5 as it begins with 0.

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

      // Send mail.
      $originalObject = $node->get('field_mail_object')->getValue();
      $originalMessage = $node->get('field_mail_content')->getValue();
      // Ensure message is not empty, for older content. Could be deleted.
      $originalObject = $originalObject[0]['value'] ?? PerformanceHelper::getDefaultReminderMailObject();
      $originalMessage = $originalMessage[0]['value'] ?? PerformanceHelper::getDefaultReminderMailMessage();
      // For 'key' and module ?, see band_booking_registration_mail.
      $result = RegistrationHelper::registrationSendMail('reminder', 'band_booking_registration', 'reminder', $node, $registration, $user, $originalObject, $originalMessage);

      // Results passed to the 'finished' callback.
      $context['results'][] = [
        'status' => $result['result'] ? 'status' : 'error',
        //TODO : add translation.
        'reminder_name' => t('"@user" on event "@event"',
          [
            '@user' => $user->getAccountName(),
            '@event' => $node->getTitle(),
          ]
        ),
      ];

      // Update our progress information.
      $context['sandbox']['progress']++;
      $context['sandbox']['current_node'] = $row + 1;
      //TODO : add translation.
      $context['message'] = t('Running Batch "@id" for user "@user" on event "@event"',
        [
          '@id' => $row,
          '@user' => $user->getAccountName(),
          '@event' => $node->getTitle(),
        ]
      );
    }

    // Finished ? TODO Check if id is ok.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = ($context['sandbox']['progress'] > $context['sandbox']['max']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function batchPerformanceReminderFinished($success, $results, $operations):void {
    $messenger = \Drupal::messenger();
    if ($success) {
      // Prepare users vs status.
      $successes = [];
      $errors = [];

      foreach ($results as $result) {
        if ($result['status'] == 'error') {
          $errors[] = $result['reminder_name'];
        } else {
          $successes[] = $result['reminder_name'];
        }
      }

      // Global message.
      // TODO : translate or adapt message.
      // TODO improve singular / plural.
      $messenger->addMessage(t('@count reminder processed.', ['@count' => count($results)]));
      /*
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One post processed.', '@count posts processed.'
      );
      */

      /*
      TODO clean, for example.
      $messenger = \Drupal::messenger();
      if (!$result['result']) {
        $message = t('There was a problem sending your email notification to @email.', array('@email' => $to));
        $messenger->addMessage($message, 'error', TRUE);
        // TODO log.
        //\Drupal::logger('mail-log')->error($message);
        return;
      }

      $message = t('An email notification has been sent to @email ', array('@email' => $to));
      $messenger->addMessage($message, 'status', TRUE);
      //\Drupal::logger('mail-log')->notice($message);
      */

      // Success messages.
      if (count($successes) >= 1) {
        $messenger->addMessage(t('Successful reminders :'), 'status', FALSE);
        foreach ($successes as $success) {
          $messenger->addMessage($success, 'status', TRUE);
        }
      }
      // Errors messages.
      if (count($errors) >= 1) {
        $messenger->addMessage(t('Failed reminders :'), 'status', FALSE);
        foreach ($errors as $error) {
          $messenger->addMessage($error, 'status', TRUE);
        }
      }
    }
    else {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $error_operation = reset($operations);
      $messenger->addMessage(
        // TODO translate.
        t('An error occurred while processing @operation with arguments : @args',
          [
            '@operation' => $error_operation[0],
            '@args' => print_r($error_operation[0], TRUE),
          ]
        )
      );
    }
  }

  /**
   * TODO : translate of delete after import.
   * {@inheritdoc}
   */
  public static function getDefaultReminderMailObject(): array {
    return [
      0 => [
        'value' => '<p>Bonjour [registration:registration_user_id:entity:display-name],</p><p>Vous avez été ajouté(e) à la prestation "[registration:nid:entity:title]".&nbsp;&nbsp;<br>Veuillez me prévenir de votre présence <a href="[registration:url]/edit">à cette adresse</a>.</p><p>Merci d\'avance,&nbsp;&nbsp;<br>[registration:uid:entity:display-name].</p>',
        'format' => 'full_html',
      ]
    ];
  }

  /**
   * TODO : translate of delete after import.
   * {@inheritdoc}
   */
  public static function getDefaultReminderMailMessage(): array {
    return [
      0 => [
        'value' => '[site:name] | [registration:uid:entity:display-name] vous a inscrit à l\'évènement [registration:nid:entity:title]',
      ]
    ];
  }

}
