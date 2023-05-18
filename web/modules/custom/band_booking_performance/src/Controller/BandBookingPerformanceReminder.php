<?php

declare(strict_types = 1);

namespace Drupal\band_booking_performance\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\band_booking_performance\PerformanceHelperInterface;

/**
 * Returns performance reminder.
 */
final class BandBookingPerformanceReminder extends ControllerBase {

  /**
   * @var \Drupal\band_booking_performance\PerformanceHelperInterface
   */
  protected PerformanceHelperInterface $performanceHelper;

  /**
   * Constructor.
   *
   * @param \Drupal\band_booking_performance\PerformanceHelperInterface $performanceHelper
   *   The bb performance helper.
   */
  public function __construct(
    PerformanceHelperInterface $performanceHelper,
  ) {
    $this->performanceHelper = $performanceHelper;
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @return BandBookingPerformanceReminder
   */
  public static function create(ContainerInterface $container): BandBookingPerformanceReminder {
    return new static(
      $container->get('band_booking_performance.performance_helper')
    );
  }

  /**
   * Create form.
   */
  public function content(): array {
    $ts = $date = time();
    $ts = strtotime("-1 day");
    $truc = $this->performanceHelper->sendReminder([], $ts);
    // $truc = $this->performanceHelper->sendReminder();

    return [
      '#markup' => '<p>REMINDER</p>'
    ];
  }

}
