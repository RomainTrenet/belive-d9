<?php

namespace Drupal\band_booking_registration;

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
   * Get options list of users.
   *
   * @param array $uids
   *   An array of users id.
   *
   * @return array
   *   An array of users.
   */

  /**
   * Register users.
   *
   * @param int $nid
   *   The node id to which register.
   * @param string $registration_type
   *   The registration bundle to create.
   * @param array $users
   *   A list of users id.
   *
   * @return void
   */
  public function registerUsers(int $nid, string $registration_bundle, array $users): void;
}
