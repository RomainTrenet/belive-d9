<?php
namespace Drupal\band_booking_registration\EventSubscriber;

use Drupal\band_booking_registration\Entity\Registration;
use Drupal\band_booking_registration\RegistrationHelperInterface;
use Drupal\entity_events\Event\EntityEvent;
use Drupal\entity_events\EventSubscriber\EntityEventUpdateSubscriber;

class RegistrationSubscriber extends EntityEventUpdateSubscriber {

  /**
   * @var \Drupal\band_booking_registration\RegistrationHelperInterface
   */
  protected RegistrationHelperInterface $registrationHelper;

  /**
   * Constructor.
   *
   * @param \Drupal\band_booking_registration\RegistrationHelperInterface $registrationHelper
   *   The bb registration helper.
   */
  public function __construct(
    RegistrationHelperInterface $registrationHelper,
  ) {
    $this->registrationHelper = $registrationHelper;
  }

  public function onEntityUpdate(EntityEvent $event) {
    /** @var Registration $registration */
    $registration = $event->getEntity();
    // TODO : check the entity type. For the moment, entity get type wont get
    // "registration" but "performance".
    if ($registration->hasField('field_state')) {
      $state = $registration->get('field_state')->first()->getValue()['value'];

      // Only if state is refused.
      if ($state == 'refused') {
        $this->registrationHelper->sendRegistrationRefusedMessage($registration);
      }
    }

  }

}
