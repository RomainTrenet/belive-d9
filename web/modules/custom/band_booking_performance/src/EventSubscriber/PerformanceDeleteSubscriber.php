<?php

namespace Drupal\band_booking_performance\EventSubscriber;

use Drupal\band_booking_performance\PerformanceHelperInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\band_booking_performance\Event\PerformanceDeleteEvent;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PerformanceDeleteSubscriber.
 *
 * @package Drupal\custom_events\EventSubscriber
 */
class PerformanceDeleteSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * Constructor.
   *
   * @param PerformanceHelperInterface $performanceHelper
   */
  public function __construct(PerformanceHelperInterface $performanceHelper) {
    $this->performanceHelper = $performanceHelper;
  }

  /**
   * @return array
   */
  public static function getSubscribedEvents(): array {
    return [
      PerformanceDeleteEvent::EVENT_NAME => 'onPerformanceDelete',
    ];
  }

  /**
   * Subscribe to the performance delete dispatched.
   * TODO : delete registrations !
   *
   * @param PerformanceDeleteEvent $event
   *   The event.
   */
  public function onPerformanceDelete(PerformanceDeleteEvent $event): void
  {
    // Send mail to registrered users by batch.
    /** @var NodeInterface $performance */
    $performance = $event->entity;
    $mailObject = $this->performanceHelper->getDefaultDeletedPerformanceMailObject();
    $mailMessage = $this->performanceHelper->getDefaultDeletedPerformanceMailMessage();
    $this->performanceHelper->performanceChanged(
      $performance,
      $mailObject,
      $mailMessage
    );
  }
}
