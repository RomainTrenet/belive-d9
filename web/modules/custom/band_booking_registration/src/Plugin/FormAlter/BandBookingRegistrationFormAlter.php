<?php

namespace Drupal\band_booking_registration\Plugin\FormAlter;

use Drupal\band_booking_registration\Entity\Registration;
use Drupal\band_booking_registration\RegistrationHelperInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\pluginformalter\Plugin\FormAlterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BandBookingRegistrationEditFormAlter.
 * Alter the RegistrationForm (entity).
 *
 * @FormAlter(
 *   id = "band_booking_registration_registration_form_alter",
 *   label = @Translation("Altering Registration Form."),
 *   form_id = {
 *    "registration_performance_edit_form"
 *   },
 * )
 *
 * @package Drupal\band_booking_registration\Plugin\FormAlter
 */
class BandBookingRegistrationFormAlter extends FormAlterBase {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * @var \Drupal\band_booking_registration\RegistrationHelperInterface
   */
  protected RegistrationHelperInterface $registrationHelper;

  /**
   * Constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The current route match.
   * @param \Drupal\band_booking_registration\RegistrationHelperInterface $registrationHelper
   *   The bb registration helper.
   */
  public function __construct(
    array $configuration,
          $plugin_id,
          $plugin_definition,
    CurrentRouteMatch $currentRouteMatch,
    RegistrationHelperInterface $registrationHelper,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentRouteMatch = $currentRouteMatch;
    $this->registrationHelper = $registrationHelper;
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   *
   * @return BandBookingRegistrationFormAlter
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
                       $plugin_id,
                       $plugin_definition,
  ): BandBookingRegistrationFormAlter {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('band_booking_registration.registration_helper'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formAlter(array &$form, FormStateInterface &$form_state, $form_id) {
    /** @var Registration $registration */
    $registration = $this->currentRouteMatch->getParameter('registration');

    if ($registration) {
      $this->registrationHelper->alterRegistrationForm($form, $form_state, $registration);
    }
  }

}
