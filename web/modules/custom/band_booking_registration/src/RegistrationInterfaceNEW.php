<?php

namespace Drupal\band_booking_registration;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * An interface for all Registration type plugins.
 *
 * When defining a new plugin type you need to define an interface that all
 * plugins of the new type will implement. This ensures that consumers of the
 * plugin type have a consistent way of accessing the plugin's functionality. It
 * should include access to any public properties, and methods for accomplishing
 * whatever business logic anyone accessing the plugin might want to use.
 *
 * For example, an image manipulation plugin might have a "process" method that
 * takes a known input, probably an image file, and returns the processed
 * version of the file.
 *
 * In our case we'll define methods for accessing the human readable description
 * of a registration and the number of calories per serving. As well as a method for
 * ordering a registration.
 */
interface RegistrationInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Provide a description of the registration.
   *
   * @return string
   *   A string description of the registration.
   */
  public function description();

  /**
   * Provide the number of calories per serving for the registration.
   *
   * @return float
   *   The number of calories per serving.
   */
  public function calories();

  /**
   * Place an order for a registration.
   *
   * This is just an example method on our plugin that we can call to get
   * something back.
   *
   * @param array $extras
   *   An array of extra ingredients to include with this registration.
   *
   * @return string
   *   Description of the registration that was just ordered.
   */
  public function order(array $extras);

}
