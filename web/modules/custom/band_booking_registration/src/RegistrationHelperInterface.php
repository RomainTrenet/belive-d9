<?php

namespace Drupal\band_booking_registration;

use Drupal\band_booking_registration\Entity\Registration;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Provides an interface defining a registration helper.
 */
interface RegistrationHelperInterface {

  /**
   * Get sites roles.
   *
   * @return array
   *   An array of roles, excluding admin.
   */
  public function getSiteRoles(): array;

  /**
   * Get a list of options from taxonomy vocabulary.
   *
   * @param string $vid
   *   The taxonomy vocabulary id.
   *
   * @return array
   *   An array of taxonomy terms.
   */
  public function getTaxonomyTermsOptions(string $vid): array;

  /**
   * Get list of registered users id for a node.
   *
   * @param int $nid
   *   The node id.
   *
   * @return array
   *   An array of users id.
   */
  public function getRegisteredUsersId(int $nid): array;

  /**
   * Get list of registered users id for a node.
   *
   * @param array $allowed_roles
   *   The list of allowed roles.
   * @param array $registeredUsersId
   *   The list of already registered users
   *
   * @return array
   *   An array of users id.
   */
  public function getUnregisteredUsersId(array $allowed_roles, array $registeredUsersId): array;

  /**
   * Get list of registrations id for a user and a node.
   *
   * @param int $nid
   *   The node id.
   * @param int $uid
   *   The user id.
   *
   * @return array
   *   An array of registrations id.
   */
  public function getPerformanceUserRegistrationsId(int $nid, int $uid): array;

  /**
   * Get options list of users.
   *
   * @param array $uids
   *   An array of users id.
   *
   * @return array
   *   An array of users.
   */
  public function getOptionsUserList(array $uids): array;

  /**
   * Get list of registrations id with user label.
   *
   * @param array $uids
   *   An array of users id.
   *
   * @return array
   *   An array of user's name by registration id.
   */
  public function getOptionsUserRegistrationList(array $uids): array;

  /**
   * Register users.
   *
   * @param int $nid
   *   The node id to which register.
   * @param string $registration_bundle
   *   The registration bundle to create.
   *
   * @param array $uids
   *   A list of users id.
   *
   * @return void
   */
  public function registerUsers(int $nid, string $registration_bundle, array $uids): void;

  /**
   * TODO
   * Operation for register users batch.
   *
   * @param $users
   *   A list of loaded user.
   * @param $uids
   *   A list of user id.
   * @param $registration_bundle
   *   The registration bundle.
   * @param $nid
   *   The node id to which register.
   * @param $operation_details
   *   The operation details.
   * @param $context
   *   The batch context.
   * @return void
   */
  public static function batchRegisterUsersOperation($users, $uids, $registration_bundle, $nid, $operation_details, &$context): void;

  /**
   * Batch 'finished' callback for register users batch.
   *
   * @param $success
   * @param $results
   * @param $operations
   * @return void
   */
  public static function batchRegisterUnregisterUsersFinished($success, $results, $operations): void;

  /**
   * Unregister users.
   *
   * @param int $nid
   *   The node id to which unregister.
   * @param array $rids
   *   A list of registrations id.
   *
   * @return void
   */
  public function unRegisterUsers(int $nid, array $rids): void;

  /**
   * TODO improve
   * Operation for unregister users batch.
   *
   * @param int $nid
   *   The node id to which unregister.
   * @param array $registrations
   *   A list of registrations.
   * @param $users
   *   A list of loaded user.
   * @param $operation_details
   *   The operation details.
   * @param $context
   *   The batch context.
   * @return void
   */
  public static function batchUnregisterUsersOperation(int $nid, array $registrations, array $users, $operation_details, &$context): void;

  /**
   * Batch 'finished' callback for unregister users batch.
   *
   * @param $success
   * @param $results
   * @param $operations
   * @return void
   */
  //public static function batchUnregisterUsersFinished($success, $results, $operations): void;

  /**
   * Get default registration mail object for former content.
   *
   * @return string
   */
  public static function getDefaultRegistrationMailObject(): string;

  /**
   * Get default registration mail content for former content.
   *
   * @return string
   */
  public static function getDefaultRegistrationMailMessage(): string;

  /**
   * Get default registration mail object for former content.
   * TODO Should be deleted after import in D9.
   *
   * @return string
   */
  public static function getDefaultUnregistrationMailObject(): string;

  /**
   * Get default registration mail content for former content.
   * TODO Should be deleted after import in D9.
   *
   * @return string
   */
  public static function getDefaultUnregistrationMailMessage(): string;

  /**
   * TODO
   * Send mail for registration.
   *
   * @param string $module.
   *   The module string used in send_mail.
   * @param string $key.
   *   The key string used in send_mail.
   * @param Node $node
   *   The event node.
   * @param Registration $registration
   *   The registration for which send mail.
   * @param User $user
   *   The user to which send mail.
   * @param string $originalObject
   *   The object containing tokens.
   * @param string $originalMessage
   *   The message containing tokens.
   *
   * @return array
   *  Array with sending mail result, 'to'.
   */
  public static function registrationSendMail(string $module, string $key, Node $node, Registration $registration, User $user, string $originalObject, string $originalMessage): array;

  /**
   * Alter registration form.
   *
   * @param array $form
   *   The registration form to alter.
   * @param FormStateInterface $form_state
   *   The form state.
   * @param Registration $registration
   *   The registration entity.
   *
   * @return void
   */
  public function alterRegistrationForm(array &$form, FormStateInterface &$form_state, Registration $registration): void;
}
