<?php

namespace Drupal\band_booking_registration;

use Drupal\Component\Plugin\PluginBase;

/**
 * A base class to help developers implement their own registration plugins.
 *
 * This is a helper class which makes it easier for other developers to
 * implement registration plugins in their own modules. In RegistrationBase we provide
 * some generic methods for handling tasks that are common to pretty much all
 * registration plugins. Thereby reducing the amount of boilerplate code required to
 * implement a registration plugin.
 *
 * In this case both the description and calories properties can be read from
 * the @Registration annotation. In most cases it is probably fine to just use that
 * value without any additional processing. However, if an individual plugin
 * needed to provide special handling around either of these things it could
 * just override the method in that class definition for that plugin.
 *
 * We intentionally declare our base class as abstract, and don't implement the
 * order() method required by \Drupal\band_booking_registration\RegistrationInterface.
 * This way even if they are using our base class, developers will always be
 * required to define an order() method for their custom registration type.
 *
 * @see \Drupal\band_booking_registration\Annotation\Registration
 * @see \Drupal\band_booking_registration\RegistrationInterface
 */
abstract class RegistrationBase extends PluginBase implements RegistrationInterface {

  /**
   * {@inheritdoc}
   * /
  public function description() {
    // Retrieve the @description property from the annotation and return it.
    return $this->pluginDefinition['description'];
  }*/

  /**
   * {@inheritdoc}
   * /
  public function calories() {
    // Retrieve the @calories property from the annotation and return it.
    return (float) $this->pluginDefinition['calories'];
  }*/

  /**
   * {@inheritdoc}
   */
  abstract public function order(array $extras);

}
