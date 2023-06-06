<?php

namespace Drupal\band_booking_registration\Plugin\FormAlter;

use Drupal\band_booking_registration\Entity\Registration;
use Drupal\band_booking_registration\RegistrationHelperInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\pluginformalter\Plugin\FormAlterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BandBookingRegistrationEditFormAlter.
 *
 * @FormAlter(
 *   id = "band_booking_registration_block_registration_form_alter",
 *   label = @Translation("Alter registration form called from entityTypeManager->getFormObject."),
 *   form_id = {
 *    "registration_performance_band_booking_registration_status_form"
 *   },
 * )
 *
 * @package Drupal\band_booking_registration\Plugin\FormAlter
 */
class BandBookingBlockRegistrationFormAlter extends FormAlterBase {

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
   * @param \Drupal\band_booking_registration\RegistrationHelperInterface $registrationHelper
   *   The bb registration helper.
   */
  public function __construct(
    array $configuration,
          $plugin_id,
          $plugin_definition,
    RegistrationHelperInterface $registrationHelper,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
  ): BandBookingBlockRegistrationFormAlter {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('band_booking_registration.registration_helper'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formAlter(array &$form, FormStateInterface &$form_state, $form_id) {
    // Get registration from form state.
    $storage = $form_state->getStorage();

    if (isset($storage['registration'])) {
      $this->registrationHelper->alterRegistrationForm($form, $form_state, $storage['registration']);
    }
  }

}
