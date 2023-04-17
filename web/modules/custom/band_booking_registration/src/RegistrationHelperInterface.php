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
}
