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

    // Reminder for today.
    $item_list['today'] = [
      'title' => [
        '#markup' => '<h2>' . t('Relaunch pending registrations for today'). '</h2>',
      ],
      'description' => [
        '#markup' => '<p>' . t('This includes every performances, and takes into account the days scheduled for relaunch'). '</p>',
      ],
      'form' => $this->formBuilder->getForm(
        'Drupal\band_booking_performance\Form\ReminderForm'
      ),
      // TODO improve.
      'sep' => [
        '#markup' => '<br>',
      ],
    ];

    // Reminder no matter what day.
    $item_list['everyday'] = [
      'title' => [
        '#markup' => '<h2>' . t('Relaunch pending registrations for all days'). '</h2>',
      ],
      'description' => [
        '#markup' => '<p>' . t('This includes every performances, and doesn\'t takes into account the days scheduled for relaunch'). '</p>',
      ],
      'form' => $this->formBuilder->getForm(
        'Drupal\band_booking_performance\Form\ReminderForm',
        ['force' => TRUE],
      ),
    ];

    // TODO = remove. Dev prupose.
    /*
    $special_dev_date = '-2 day';
    $nids = [];
    $contextualTimestamp = strtotime($special_dev_date);
    $startFromContextualTs = false;
    $current_date = strtotime($special_dev_date);

    $item_list['dev'] = $this->formBuilder->getForm(
      'Drupal\band_booking_performance\Form\ReminderForm',
      [
        'force' => FALSE,
        'nids' => $nids,
        'startFromContextualTs' => $startFromContextualTs,
        'contextualTimestamp' => $contextualTimestamp,
        //'current_date' => $current_date,
      ],
    );
    */

    return [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $item_list,
    ];
  }

}
