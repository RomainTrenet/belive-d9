<?php

declare(strict_types = 1);

namespace Drupal\band_booking_registration\Entity\Controller;

use Drupal\band_booking_registration\Entity\Registration;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\Entity\Node;

/**
 * Registration entity controller.
 */
final class RegistrationController extends ControllerBase {

  public function editTitle(RouteMatchInterface $route_match, EntityInterface $_entity = NULL) {
    /** @var Registration $registration */
    $registration = $route_match->getParameter('registration');

    $value = $registration->get('nid')->getValue();
    if (isset($value[0]['target_id'])) {
      $performance = Node::load($value[0]['target_id']);
      return $this->t('Edit your registration to %performance', ['%performance' => $performance->getTitle()]);
    } else {
      return $this->t('Edit you registration');
    }

  }

}
