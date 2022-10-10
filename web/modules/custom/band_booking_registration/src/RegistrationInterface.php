<?php

namespace Drupal\band_booking_registration;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a registration entity type.
 */
interface RegistrationInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
