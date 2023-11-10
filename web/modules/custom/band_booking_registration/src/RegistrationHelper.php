<?php

//TODO clean

namespace Drupal\band_booking_registration;

use Drupal\band_booking_performance\PerformanceHelperInterface;
use Drupal\band_booking_registration\Entity\Registration;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\Entity\Node;
use Drupal\state_machine\Plugin\Field\FieldType\StateItem;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

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
  public function getUnregisteredUsersId(array $allowed_roles, array $registeredUsersId, array $positions = []): array {
    $query = \Drupal::entityQuery('user');
    $query->condition('status', 1);
    $query->condition('roles', $allowed_roles, 'IN');
    if (!empty($registeredUsersId)) {
      $query->condition('uid', $registeredUsersId, 'NOT IN');
    }
    if (!empty($positions)) {
      $query->condition('field_position', $positions, 'IN');
    }
    return $query->execute();
  }

  public function getPerformanceUserRegistrationsId(int $nid, int $uid): array {
    // Get registrations for nid.
    $query = \Drupal::entityQuery('registration');
    $query->condition('nid', $nid);
    $query->condition('registration_user_id', $uid);
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
      // TODO : we should get user names from submit + we should load users inside operations.
      $users = User::loadMultiple($uids);

      // Prepare title.
      $usersName = [];
      foreach ($users as $user) {
        $usersName[] = $user->getAccountName();
      }

      // TODO if op = register, title = ; operations; finished.
      $batch = [
        'title' => $this->t('Registering : @users.', ['@users' => implode(', ', $usersName)]),
        'operations' => [],
        'finished' => '\Drupal\band_booking_registration\RegistrationHelper::batchRegisterUnregisterUsersFinished',
      ];

      // Only one operation, but loop inside operation with limit.
      $batch['operations'][] = [
        '\Drupal\band_booking_registration\RegistrationHelper::batchRegisterUsersOperation',
        [
          $users,
          $uids,
          $registration_bundle,
          $nid,
          $this->t('Registering : @users.', ['@users' => implode(', ', $usersName)]),
        ],
      ];

      batch_set($batch);
    }
    else {
      // TODO if op = register.
      $message = $this->t('No user to register.');
      $this->messenger->addMessage($message, 'status', TRUE);
    }
  }

  /**
   * {@inheritdoc}
   *
   * @throws EntityStorageException
   */
  public static function batchRegisterUsersOperation($users, $uids, $registration_bundle, $nid, $operation_details, &$context) :void {
    if (empty($context['sandbox'])) {
      $context['sandbox'] = [];
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_node'] = 0;
      $context['sandbox']['max'] = count($uids);
    }

    // Process in groups of 2 (arbitrary value).
    $limit = 1; // "1" for group of 2 as it begins with 0.

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
      // TODO @var should come from bundle name.
      /** @var Registration $registrationEntity */
      $registrationEntity = $storage->create([
        'bundle' => $registration_bundle,
        'nid' => $nid,
        'registration_user_id' => $uid
      ]);
      $registration = $registrationEntity->save();

      // Prepare variables to send mail.
      /** @var User $destinationUser */
      $destinationUser = $users[$uid];
      $node = Node::load($nid);
      $object = $node->get('field_register_mail_object')->getValue()[0]['value'];
      $message = $node->get('field_register_mail_content')->getValue()[0]['value'];

      // $module tells in which .module to find hook_mail. See band_booking_registration_mail.
      $module = 'band_booking_registration';
      // For 'key' is used inside the hook_mail.
      $key = 'user_register';

      // Prepare and send mail.
      $mail = RegistrationHelper::getMailObjectAndMessageFromToken(
        $destinationUser,
        $object,
        $message,
        ['registration' => $registrationEntity],
        ['registration' => $registrationEntity],
      );
      $mailResult = RegistrationHelper::bookingSendMail($module, $key, $destinationUser, $mail['object'], $mail['message']);

      // Results passed to the 'finished' callback.
      $context['results'][] = [
        'operation' => 'register',
        // TODO pass mail result into status.
        'status' => $registration ? 'status' : 'error',
        'account_name' => $users[$uid]->getAccountName()
      ];

      // Update our progress information.
      $context['sandbox']['progress']++;
      $context['sandbox']['current_node'] = $row + 1;
      $context['message'] = t('Running Batch "@id" for user "@user"',
        [
          '@id' => $row,
          '@user' => $users[$uid]->getAccountName(),
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
  public static function batchRegisterUnregisterUsersFinished($success, $results, $operations):void {
    $translation = \Drupal::translation();
    $messenger = \Drupal::messenger();

    // Register or unregister ?
    $op = $results[0]['operation'];

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

      // TODO : show the number of nodes we processed ?
      // $messenger->addMessage(t('@count results processed.', ['@count' => count($results)]));

      // Print results.
      $amountSuccessful = count($successRegistrations);
      if ($amountSuccessful >= 1) {
        if ($op == 'register') {
          $message = $translation->formatPlural(
            $amountSuccessful,
            'User successfully registered : @users.', 'Users successfully registered : @users.',
            ['@users' => implode(', ', $successRegistrations)],
          );
        } else {
          $message = $translation->formatPlural(
            $amountSuccessful,
            'User successfully unregistered : @users.', 'Users successfully unregistered : @users.',
            ['@users' => implode(', ', $successRegistrations)],
          );
        }
        $messenger->addMessage($message, 'status', TRUE);
      }

      // Errors messages.
      $amountFailed = count($errorRegistrations);
      if ($amountFailed >= 1) {
        if ($op == 'register') {
          $message = $translation->formatPlural(
            $amountFailed,
            'User not registered : @users.', 'Users not registered : @users.',
            ['@users' => implode(', ', $errorRegistrations)],
          );
        } else {
          $message = $translation->formatPlural(
            $amountFailed,
            'User not registered : @users.', 'Users not registered : @users.',
            ['@users' => implode(', ', $errorRegistrations)],
          );
        }
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
  public function unRegisterUsers(int $nid, array $rids): void {
    // TODO : we should get user names from submit + we should load users and registrations inside operations.
    if (!empty($rids)) {
      $storage = $this->entityTypeManager->getStorage('registration');
      $tempRegistrations = $storage->loadMultiple($rids);
      $registrations = [];

      // Get list of user id and load users + build array of registration.
      $usersId = [];
      foreach ($tempRegistrations as $registration) {
        $registrations[] = $registration;
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

      $batch = [
        'title' => $this->t('Unregistering : @users.', ['@users' => implode(', ', $usernames)]),
        'operations' => [],
        'finished' => '\Drupal\band_booking_registration\RegistrationHelper::batchRegisterUnregisterUsersFinished',
      ];

      // Only one operation, but loop inside operation with limit.
      $batch['operations'][] = [
        '\Drupal\band_booking_registration\RegistrationHelper::batchUnregisterUsersOperation',
        [
          $nid,
          $registrations,
          $users,
          $this->t('Unregistering : @users.', ['@users' => implode(', ', $usernames)]),
        ],
      ];

      batch_set($batch);
    }
    else {
      $message = $this->t('No user to unregister.');
      $this->messenger->addMessage($message, 'status', TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function batchUnregisterUsersOperation(int $nid, array $registrations, array $users, $operation_details, &$context): void {
    if (empty($context['sandbox'])) {
      $context['sandbox'] = [];
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_node'] = 0;
      $context['sandbox']['max'] = count($registrations);
    }

    // Process in groups of 2 (arbitrary value).
    $limit = 1; // "1" for group of 2 as it begins with 0.

    // Retrieve the next group.
    $result = range($context['sandbox']['current_node'], $context['sandbox']['current_node'] + $limit);

    foreach ($result as $row) {
      // Do not go above maximum results.
      if ($row > $context['sandbox']['max'] - 1) {
        return;
      }

      /** @var RegistrationInterface $registration */
      $registration = $registrations[$row];
      // TODO check if it crash without content.
      $uid = $registration->get('registration_user_id')->first()->getValue()['target_id'];

      // TODO should create array of registrations and node according to limit amount, and then delete and send mail.
      // Delete registration.
      $storage = \Drupal::entityTypeManager()->getStorage('registration');
      $storage->delete([$registration]);
      $deleted = TRUE;
        // TODO : improve and ensure entity is deleted before sending messages.

      // Send mail.
      $node = Node::load($nid);
      $originalObject = $node->get('field_unregister_mail_object')->getValue();
      $originalMessage = $node->get('field_unregister_mail_content')->getValue();
      // Ensure message is not empty, for older content. Could be deleted.
      $originalObject = $originalObject[0]['value'] ?? RegistrationHelper::getDefaultUnregistrationMailObject();
      $originalMessage = $originalMessage[0]['value'] ?? RegistrationHelper::getDefaultUnregistrationMailMessage();

      // For 'key' see band_booking_registration_mail.
      $module = 'band_booking_registration';
      $key = 'user_unregister';
      $mailResult = RegistrationHelper::registrationSendMail($module, $key, $node, $registration, $users[$uid], $originalObject, $originalMessage);

      // Results passed to the 'finished' callback.
      $context['results'][] = [
        'operation' => 'unregister',
        'status' => $deleted ? 'status' : 'error',
        // TODO pass mail result.
        'account_name' => $users[$uid]->getAccountName()
      ];

      // Update our progress information.
      $context['sandbox']['progress']++;
      $context['sandbox']['current_node'] = $row + 1;
      $context['message'] = t('Running Batch "@id" for user "@user"',
        [
          '@id' => $row,
          '@user' => $users[$uid]->getAccountName(),
        ]
      );
    }

    // Finished ? TODO check if correctly used, works perfectly for the moment.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = ($context['sandbox']['progress'] > $context['sandbox']['max']);
    }
  }

  /**
   * TODO : translate of delete after import.
   * {@inheritdoc}
   */
  public static function getDefaultUnregistrationMailObject(): string {
    return '[site:name] | [registration:uid:entity:display-name] vous a retiré de l\'évènement [registration:nid:entity:title]';
  }

  /**
   * TODO : translate of delete after import.
   * {@inheritdoc}
   */
  public static function getDefaultUnregistrationMailMessage(): string {
    return '<p>Bonjour [registration:registration_user_id:entity:display-name],</p><p>Vous avez été retiré(e) de la prestation "[registration:nid:entity:title]".</p><p>Cordialement,&nbsp;<br>[registration:uid:entity:display-name].</p>';
  }

  /**
   * TODO should be in a separated module.
   * {@inheritdoc}
   */
  public static function getMailObjectAndMessageFromToken(User $user, string $originalObject, string $originalMessage, array $dataObject = [], array $dataMessage= []): array {
    $token_service = \Drupal::token();

    $mail = [
      'object' => '',
      'message' => '',
    ];

    $options = [
      'langcode' => $user->getPreferredLangcode(),
      'clear' => TRUE,
    ];

    if (isset($originalObject)) {
      $mail['object'] = $token_service->replace(
        $originalObject,
        $dataObject,
        $options
      );
    }
    if (isset($originalMessage)) {
      $mail['message'] =  $token_service->replace(
        $originalMessage,
        $dataMessage,
        $options
      );
    }

    return $mail;
  }

  /**
   * TODO should be in a separated module.
   * {@inheritdoc}
   */
  public static function bookingSendMail(string $module, string $key, User $toUser, string $object, string $message): array {
    /** @var MailManagerInterface $mailManager */
    $mailManager = \Drupal::service('plugin.manager.mail');
    $to = $toUser->get('mail')->getValue()[0]['value'];
    $config = \Drupal::config('system.site');
    $params['from'] = $config->get('mail');
    $params['message'] = Markup::create($message);
    $params['title'] = $object;
    $langcode = $toUser->getPreferredLangcode();

    return $mailManager->mail($module, $key, $to, $langcode, $params, $params['from']);
  }

  /**
   * Common registration mail sender. See also band_booking_registration_mail.
   * {@inheritdoc}
   *
   * TODO eventually : function that get token for mail object and message ?
   */
  public static function registrationSendMail(string $module, string $key, Node $node, Registration $registration, User $user, string $originalObject, string $originalMessage): array {
    $token_service = \Drupal::token();
    $options = [
      'langcode' => $user->getPreferredLangcode(),
      'clear' => TRUE,
    ];

    $object = '';
    if (isset($originalObject)) {
      $object =  $token_service->replace(
        $originalObject,
        ['registration' => $registration],
        $options
      );
    }

    $message = '';
    if (isset($originalMessage)) {
      $message =  $token_service->replace(
        $originalMessage,
        ['registration' => $registration],
        $options
      );
    }

    // TODO : check if it still works.
    return RegistrationHelper::bookingSendMail($module, $key, $user, $object, $message);

    /** @var MailManagerInterface $mailManager * /
    $mailManager = \Drupal::service('plugin.manager.mail');
    $to = $user->get('mail')->getValue()[0]['value'];
    $config = \Drupal::config('system.site');
    $params['from'] = $config->get('mail');

    $params['message'] = Markup::create($message);
    $params['title'] = $object;
    // TODO : replace currentUser by the user to send the email to.
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

    return $mailManager->mail($module, $key, $to, $langcode, $params, $params['from']);*/
  }

  /**
   * {@inheritdoc}
   */
  public function alterRegistrationForm(array &$form, FormStateInterface &$form_state, Registration $registration): void {
    /** @var UserInterface $owner */
    $owner = $registration->getOwner();

    if (isset($form['field_state'])) {
      if (isset($form['field_state']['widget'])) {
        $form['field_state']['widget']['#weight'] = 50;
        unset($form['field_state']['widget']['#title']);
      }

      /** @var StateItem $field_state */
      $field_state = $registration->get('field_state')->first();

      $form['field_state']['desc'] = [
        '#markup' => $this->t('%owner has added you to the performance.', ['%owner' => $owner->getAccountName()]),
        '#weight' => 10,
      ];
      $form['field_state']['actual_state_title'] = [
        '#markup' => '<h3>' . $this->t('Current status of your registration') . '</h3>',
        '#weight' => 20,
      ];
      $form['field_state']['actual_state'] = [
        '#markup' => $field_state->getLabel(),
        '#weight' => 30,
      ];
      $form['field_state']['field_state_title'] = [
        '#markup' => '<h3>' . $this->t('Modify your registration status') . '</h3>',
        '#weight' => 40,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getRegistrationRefusedBaseObject(): string {
    return '[site:name] | À propos de l\'évènement [registration:nid:entity:title]';
  }

  /**
   * {@inheritdoc}
   */
  public function getRegistrationRefusedBaseMessage(): string {
    return '<p>Bonjour [registration:uid:entity:display-name],</p><p>[registration:registration_user_id:entity:display-name] a décliné l\'inscription à la prestation "[registration:nid:entity:title]".</p>';
  }

  /**
   * TODO : standard function with different messages.
   * {@inheritdoc}
   */
  public function sendRegistrationRefusedMessage(Registration $registration): void {
    $originalMessage = $this->getRegistrationRefusedBaseMessage();
    $originalObject = $this->getRegistrationRefusedBaseObject();
    // $module tells in which .module to find hook_mail. See band_booking_registration_mail.
    $module = 'band_booking_registration';
    // For 'key' is used inside the hook_mail.
    $key = 'registration_refuses';

    $nid = $registration->get('nid')->first()->getValue()['target_id'];
    $node = Node::load($nid);

    $user = $registration->getOwner();
    //$this->registrationSendMail()
    $mailResult = RegistrationHelper::registrationSendMail($module, $key, $node, $registration, $user, $originalObject, $originalMessage);
  }

}
