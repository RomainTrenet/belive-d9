<?php

//TODO clean

namespace Drupal\band_booking_registration;

use Drupal\band_booking_registration\Entity\Registration;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
 * Service to provide ....
 */
class RegistrationHelper implements RegistrationHelperInterface {

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
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  //protected $moduleHandler;
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
  public function getSiteRoles(): array {
    $roles = [];
    $roles_entities = Role::loadMultiple();
    unset($roles_entities['administrator']);

    // Translate.
    foreach ($roles_entities as $key => $role) {
      $roles[$key] = $role->label();
    }

    return $roles;
  }

  /**
   * {@inheritdoc}
   */
  public function getTaxonomyTermsOptions(string $vid): array
  {
    $options = [];
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', $vid);
    $query->condition('status', 1);
    $query->sort('weight');
    $tids = $query->execute();
    $terms = Term::loadMultiple($tids);

    // TODO : check translation, check order ?
    foreach ($terms as $term) {
      $options[$term->id()] = $term->label();
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getRegisteredUsersId(int $nid): array {
    $usersId = [];

    // Get registrations for nid.
    $query = \Drupal::entityQuery('registration');
    $query->condition('nid', $nid);
    $rids = $query->execute();
    $registrations = $this->entityTypeManager->getStorage('registration')->loadMultiple($rids);

    // Get users id from registrations.
    /** @var Registration $registration */
    foreach ($registrations as $registration) {
      $value = $registration->get('registration_user_id')->getValue();
      if (isset($value[0]['target_id'])) {
        $usersId[$registration->id()] = $value[0]['target_id'];
      }
    }

    return $usersId;
  }

  /**
   * {@inheritdoc}
   */
  public function getUnregisteredUsersId(array $allowed_roles, array $registeredUsersId): array {
    $query = \Drupal::entityQuery('user');
    $query->condition('status', 1);
    $query->condition('roles', $allowed_roles, 'IN');
    if (!empty($registeredUsersId)) {
      $query->condition('uid', $registeredUsersId, 'NOT IN');
    }
    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionsUserList(array $uids): array
  {
    $users = User::loadMultiple($uids);

    $options = [];
    foreach ($users as $user) {
      $options[$user->id()] = $user->getAccountName();
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionsUserRegistrationList(array $uids): array {
    $users = User::loadMultiple($uids);

    $options = [];
    foreach ($uids as $rid => $uid) {
      $options[$rid] = $users[$uid]->getAccountName();
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function registerUsers(int $nid, string $registration_bundle, array $uids): void {
    if (!empty($uids)) {
      $users = User::loadMultiple($uids);

      $operations = [];
      $amountOperations = 'rien';

      // Only one operation, but loop inside operation with limit.
      $operations[] = [
        '\Drupal\band_booking_registration\RegistrationHelper::batchRegisterUsersOperation',
        [
          $users,
          $uids,
          $registration_bundle,
          $nid,
          $this->t('(--- @operation)', ['@operation' => $amountOperations]),
        ],
      ];

      $batch = [
        'title' => $this->t('Creating an array of @num operations', ['@num' => $amountOperations]),
        'operations' => $operations,
        'finished' => '\Drupal\band_booking_registration\RegistrationHelper::batchRegisterUsersFinished',
      ];
      batch_set($batch);
    }
    else {
      $message = $this->t('No user to register.');
      $this->messenger->addMessage($message, 'status', TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function batchRegisterSendMail($performance, $registration, $user): void {
    $messenger = \Drupal::messenger();
    $token_service = \Drupal::token();

    $tokenMailMessage = $performance->get('field_register_mail_content')->getValue();
    $tokenMailObject = $performance->get('field_register_mail_object')->getValue();

    $message = [];
    if (isset($tokenMailMessage[0]['value'])) {
      $message =  $token_service->replace(
        $tokenMailMessage[0]['value'],
        [
          'registration' => $registration,
        ],
        [
          'langcode' => $user->getPreferredLangcode(),
          //part of the Token replacement service; A boolean flag indicating
          // that tokens should be removed from the final text if no replacement
          // value can be generated
          'clear' => TRUE,
        ]
      );
    }
    $object = [];
    if (isset($tokenMailObject[0]['value'])) {
      $object =  $token_service->replace(
        $tokenMailObject[0]['value'],
        [
          'registration' => $registration,
        ],
        [
          'langcode' => $user->getPreferredLangcode(),
          //part of the Token replacement service; A boolean flag indicating
          // that tokens should be removed from the final text if no replacement
          // value can be generated
          'clear' => TRUE,
        ]
      );
    }

    /** @var MailManagerInterface $mailManager */
    $mailManager = \Drupal::service('plugin.manager.mail');
    // See band_booking_registration_module function.
    $module = 'band_booking_registration';
    $key = 'node_insert';
    $to = $user->get('mail')->getValue()[0]['value'];
    /** @var User $owner */
    $owner = $registration->getOwner();
    $params['from'] = $owner->get('mail')->getValue()[0]['value'];
    $params['message'] = Markup::create($message);
    $params['title'] = $object;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

    if (!$result['result']) {
      $message = t('There was a problem sending your email notification to @email.', array('@email' => $to));
      $messenger->addMessage($message, 'error', TRUE);
      //\Drupal::logger('mail-log')->error($message);
      return;
    }

    $message = t('An email notification has been sent to @email ', array('@email' => $to));
    $messenger->addMessage($message, 'status', TRUE);
    //\Drupal::logger('mail-log')->notice($message);
  }

  /**
   * {@inheritdoc}
   * @throws EntityStorageException
   */
  public static function batchRegisterUsersOperation($users, $uids, $registration_bundle, $nid, $operation_details, &$context) :void {
    // Use the $context['sandbox'] at your convenience to store the
    // information needed to track progression between successive calls.
    if (empty($context['sandbox'])) {
      $context['sandbox'] = [];
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_node'] = 0;
      $context['sandbox']['max'] = count($uids);
      $context['sandbox']['usernames'] = [];
    }

    // Process in groups of 2 (arbitrary value).
    $limit = 1; // 2 as it begins with 0.

    // Retrieve the next group.
    $result = range($context['sandbox']['current_node'], $context['sandbox']['current_node'] + $limit);

    foreach ($result as $row) {
      // Do not go above maximum results.
      if ($row > $context['sandbox']['max'] - 1) {
        return;
      }

      // Register entity.
      $uid = $uids[$row];
      $storage = \Drupal::entityTypeManager()->getStorage('registration');
      $registrationEntity = $storage->create([
        'bundle' => $registration_bundle,
        'nid' => $nid,
        'registration_user_id' => $uid
      ]);
      /*
      $registration = $storage->create([
        'bundle' => $registration_bundle,
        'nid' => $nid,
        'registration_user_id' => $uid
      ])->save();*/
      $registration = $registrationEntity->save();

      // Send mail.
      $performance = Node::load($nid);
      //$registration_entity = Registration::load()
      RegistrationHelper::batchRegisterSendMail($performance, $registrationEntity, $users[$uid]);

      // Results passed to the 'finished' callback.
      $context['results'][] = [
        'status' => $registration ? 'status' : 'error',
        'account_name' => $users[$uid]->getAccountName()
      ];

      // Update our progress information.
      $context['sandbox']['progress']++;
      $context['sandbox']['current_node'] = $row + 1;
      $context['message'] = t('Running Batch "@id" for user n* @uid',
        ['@id' => $row, '@uid' => $uids[$row]]
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
  public static function batchRegisterUsersFinished($success, $results, $operations):void {
    $messenger = \Drupal::messenger();
    if ($success) {
      // Prepare users vs status.
      $successRegistrations = [];
      $errorRegistrations = [];

      foreach ($results as $result) {
        if ($result['status'] == 'error') {
          $errorRegistrations[] = $result['account_name'];
        } else {
          $successRegistrations[] = $result['account_name'];
        }
      }

      // Here we could do something meaningful with the results.
      // We just display the number of nodes we processed...
      $messenger->addMessage(t('@count results processed.', ['@count' => count($results)]));

      // Print results.
      // TODO improve singular / plural.
      if (count($successRegistrations) >= 1) {
        $message = t('Users successfully registered : @users.', array('@users' => implode(', ', $successRegistrations)));
        $messenger->addMessage($message, 'status', TRUE);
      }
      if (count($errorRegistrations) >= 1) {
        $message = t('Users not registered : @users.', array('@users' => implode(', ', $errorRegistrations)));
        $messenger->addMessage($message, 'error', TRUE);
      }
    }
    else {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $error_operation = reset($operations);
      $messenger->addMessage(
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
   * {@inheritdoc}
   * @throws EntityStorageException
   */
  public function unRegisterUsers(array $rids): void {
    if (!empty($rids)) {
      $storage = $this->entityTypeManager->getStorage('registration');
      $registrations = $storage->loadMultiple($rids);
      $storage->delete($registrations);

      // TODO : improve and ensure entity is created before sending messages.

      // Get list of user id and load users.
      $usersId = [];
      foreach ($registrations as $registration) {
        $value = $registration->get('registration_user_id')->getValue();
        if (isset($value[0]['target_id'])) {
          $usersId[$registration->id()] = $value[0]['target_id'];
        }
      }

      // Create array of usernames.
      $users = User::loadMultiple($usersId);
      $usernames = [];
      foreach ($rids as $rid) {
        $uid = $usersId[$rid];
        $usernames[] = $users[$uid]->getAccountName();
      }

      // Message.
      $message = $this->t('Users successfully unregistered : @users.', array('@users' => implode(', ', $usernames)));
      $this->messenger->addMessage($message, 'status', TRUE);
    }
    else {
      $message = $this->t('No user to unregister.');
      $this->messenger->addMessage($message, 'status', TRUE);
    }
  }

}
