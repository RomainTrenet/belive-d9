<?php

//TODO clean

namespace Drupal\band_booking_registration;

use Drupal\band_booking_registration\Entity\Registration;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Messenger\MessengerInterface;
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

  public function registerUsers(int $nid, string $registration_bundle, array $uids): void {
    if (!empty($uids)) {
      // Prepare entity.
      $storage = $this->entityTypeManager->getStorage('registration');
      $users = User::loadMultiple($uids);

      $usernames = [];
      foreach ($uids as $uid) {
        // TODO : improve and ensure entity is created before sending messages.
        $storage->create([
          'bundle' => $registration_bundle,
          'nid' => $nid,
          'registration_user_id' => $uid
        ])->save();

        $usernames[] = $users[$uid]->getAccountName();
      }

      // Message.
      $message = $this->t('Users successfully registered : @users.', array('@users' => implode(', ', $usernames)));
      $this->messenger->addMessage($message, 'status', TRUE);
    }
    else {
      $message = $this->t('No user to register.');
      $this->messenger->addMessage($message, 'status', TRUE);
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
