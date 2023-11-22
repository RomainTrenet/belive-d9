<?php

namespace Drupal\band_booking_performance\Event;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that is fired when a registration status is changed.
 */
class RegistrationStatusChangedEvent extends Event {

  const EVENT_NAME = 'registration_status_changed';

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
