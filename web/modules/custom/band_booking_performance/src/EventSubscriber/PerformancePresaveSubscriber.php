<?php

namespace Drupal\band_booking_performance\EventSubscriber;

use Drupal\band_booking_performance\PerformanceHelperInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\band_booking_performance\Event\PerformancePresaveEvent;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PerformancePresaveSubscriber.
 *
 * @package Drupal\custom_events\EventSubscriber
 */
class PerformancePresaveSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * @var DateFormatterInterface
   */
  private DateFormatterInterface $date_formatter;

  /**
   * @var PerformanceHelperInterface
   */
  protected PerformanceHelperInterface $performanceHelper;

  /**
   * Constructor.
   *
   * @param DateFormatterInterface $date_formatter
   * @param PerformanceHelperInterface $performanceHelper
   */
  public function __construct(
    DateFormatterInterface $date_formatter,
    PerformanceHelperInterface $performanceHelper
  ) {
    $this->date_formatter = $date_formatter;
    $this->performanceHelper = $performanceHelper;
  }

  /**
   * @return array
   */
  public static function getSubscribedEvents(): array {
    return [
      PerformancePresaveEvent::EVENT_NAME => 'onPerformancePresave',
    ];
  }

  /**
   * Subscribe to the performance presave dispatched.
   *
   * @param PerformancePresaveEvent $event
   *   The event.
   */
  public function onPerformancePresave(PerformancePresaveEvent $event): void
  {
    // Save non utc date.
    /** @var NodeInterface $performance */
    $performance = $event->entity;

    // Tell drupal that the date is UTC, so that when converting to user
    // timezone it gets the right day. For example, for a French date of
    // 1rst of june 00h00, it will record the $date_value as 31th of may 22h00.
    $date_value = $performance->get('field_date')->getValue();
    $date_utc = isset($date_value[0]['value']) ?
      new DrupalDateTime( $date_value[0]['value'], 'UTC' ) :
      null;

    // Ensure the date is with correct timezone, wanted by user.
    $date_non_utc = !is_null($date_utc) ?
      $this->date_formatter->format(
        $date_utc->getTimestamp(),
        'custom',
        'Y-m-d'
      ) :
      null;

    // Compare date to call event subscriber.
    $nid = $performance->id();
    $formerPerf = Node::load($nid);
    $former_date_value = $formerPerf->get('field_date')->getValue();
    if ($former_date_value[0]['value'] != $date_value[0]['value']) {
      $mailObject = $this->performanceHelper->getDefaultDateChangedPerformanceMailObject();
      $mailMessage = $this->performanceHelper->getDefaultDateChangedPerformanceMailMessage();
      $this->performanceHelper->performanceChanged(
        $performance,
        $mailObject,
        $mailMessage
      );
    }

    // Finally save.
    $performance->set('field_date_non_utc', $date_non_utc);
  }
}
