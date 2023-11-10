<?php

namespace Drupal\band_booking_performance\Event;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that is fired when a performance node is deleted.
 */
class PerformanceDeleteEvent extends Event {

  const EVENT_NAME = 'performance_delete';

  /**
   * The entity.
   *
   * @var EntityInterface
   */
  public EntityInterface $entity;

  /**
   * Constructs the object.
   *
   * @param EntityInterface $entity
   *   The entity of the user logged in.
   */
  public function __construct(EntityInterface $entity) {
    $this->entity = $entity;
  }

}
