<?php

declare(strict_types = 1);

namespace Drupal\band_booking_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns dashboard page.
 */
final class BandBookingDashboard extends ControllerBase {

  /**
   * Constructor.
   */
  public function __construct() {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): BandBookingDashboard {
    return new static(
    );
  }

  /**
   * Returns empty page, filled with blocks.
   */
  public function content(): array {
    return [];
  }

}
