<?php

namespace Drupal\band_booking_performance\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an admin registration Block.
 *
 * @Block(
 *   id = "registration_block",
 *   admin_label = @Translation("Admin registration"),
 *   category = @Translation("Band Booking"),
 * )
 */
class RegistrationBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => $this->t('Registration, World!'),
    ];
  }

}
