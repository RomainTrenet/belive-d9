<?php

namespace Drupal\band_booking_registration\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Route subscriber class.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    /** @var \Symfony\Component\Routing\Route $entityNodeCanonical */
    $entityRegistrationEditForm = $collection->get('entity.registration.edit_form');
    $entityRegistrationEditForm->setDefault(
      '_title_callback', '\Drupal\band_booking_registration\Entity\Controller\RegistrationController::editTitle',
    );
  }

}
