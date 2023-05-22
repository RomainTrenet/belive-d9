<?php

declare(strict_types = 1);

namespace Drupal\band_booking_performance\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
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
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructor.
   *
   * @param \Drupal\band_booking_performance\PerformanceHelperInterface $performanceHelper
   *   The bb performance helper.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(
    PerformanceHelperInterface $performanceHelper,
    FormBuilderInterface $form_builder
  ) {
    $this->performanceHelper = $performanceHelper;
    $this->formBuilder = $form_builder;
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
      $container->get('band_booking_performance.performance_helper'),
      $container->get('form_builder')
    );
  }

  /**
   * Create form.
   */
  public function content(): array {
    // TODO : contextual node id + others.
    return $this->formBuilder->getForm('Drupal\band_booking_performance\Form\ReminderForm');

    /*
    return [
      '#markup' => '<p>REMINDER</p>'
    ];
    */
  }

}
