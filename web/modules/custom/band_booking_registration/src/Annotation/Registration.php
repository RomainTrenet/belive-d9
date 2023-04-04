<?php

namespace Drupal\band_booking_registration\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Registration annotation object.
 *
 * Provides an example of how to define a new annotation type for use in
 * defining a plugin type. Demonstrates documenting the various properties that
 * can be used in annotations for plugins of this type.
 *
 * Note that the "@ Annotation" line below is required and should be the last
 * line in the docblock. It's used for discovery of Annotation definitions.
 *
 * @see \Drupal\band_booking_registration\RegistrationPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class Registration extends Plugin {
  /**
   * A brief, human readable, description of the registration type.
   *
   * This property is designated as being translatable because it will appear
   * in the user interface. This provides a hint to other developers that they
   * should use the Translation() construct in their annotation when declaring
   * this property.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * The number of calories per serving of this registration type.
   *
   * This property is a float value, so we indicate that to other developers
   * who are writing annotations for a Registration plugin.
   *
   * @var int
   */
  public $calories;

}
