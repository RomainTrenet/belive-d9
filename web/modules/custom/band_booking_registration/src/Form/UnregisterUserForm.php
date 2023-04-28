<?php
/**
 * @file
 * Contains \Drupal\band_booking_registration\Form\UnregisterUserForm.
 */
namespace Drupal\band_booking_registration\Form;

use Drupal\band_booking_registration\Plugin\Block\RegistrationBlock;
use Drupal\band_booking_registration\RegistrationHelperInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UnregisterUserForm extends FormBase {

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

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @return RegistrationBlock
   */
  public static function create(ContainerInterface $container): UnregisterUserForm {
    return new static(
      $container->get('band_booking_registration.registration_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'band_booking_unregistration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $arg = NULL) {
    // Prepare values for submit.
    $form_state->set('context_nid', $arg['context_nid'] ?? '');
    $form_state->set('register_bundle', $arg['register_bundle'] ?? '');

    $registered_users_by_rid = $arg['registered_users_by_rid'] ?? [];

    // Text.
    $remove_title = $arg['remove_title'] ?? [];
    $remove_description = $arg['remove_description'] ?? [];

    $form['unregister_user'] = [
      //'#type' => 'selectbox',
      '#type' => 'select',
      '#title' => $remove_title,
      '#description' => $remove_description,
      '#required' => TRUE,

      // TODO improve, les coches ne fonctionnent pas si on ne le met pas.
      '#multiple' => TRUE,
      '#attributes' => [
        'multiple' => TRUE,
        //'direction' => 'left',
        // TODO remove.
        'class' => ['vanilla-select-box'],
      ],
      // TODO remove.
      '#attached' => [
        'library' => [
          'vanilla_select_box/select-box'
        ]
      ],

      '#size' => 3,
      '#options' => $registered_users_by_rid,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Unregister'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, $arg = NULL) {
    $rids = $form_state->getValue('unregister_user');
    $this->registrationHelper->unRegisterUsers($rids);
  }
}
