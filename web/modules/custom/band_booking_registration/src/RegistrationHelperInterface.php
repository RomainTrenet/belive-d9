<?php

namespace Drupal\band_booking_registration;

use Drupal\band_booking_registration\Entity\Registration;
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
   * Send mail when register user in batch.
   *
   * @param Node $performance
   * @param Registration $registration
   * @param User $user
   * @return void
   */
  public static function batchRegisterSendMail(Node $performance, Registration $registration, User $user): void;

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
   * Batch 'finished' callback used by both batch 1 and batch 2.
   *
   * @param $success
   * @param $results
   * @param $operations
   * @return void
   */
  public static function batchRegisterUsersFinished($success, $results, $operations): void;

  /**
   * Unregister users.
   *
   * @param array $rids
   *   A list of registrations id.
   *
   * @return void
   */
  public function unRegisterUsers(array $rids): void;
}
