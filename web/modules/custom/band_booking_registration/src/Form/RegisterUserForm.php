<?php
/**
 * @file
 * Contains \Drupal\band_booking_registration\Form\RegisterUserForm.
 */
namespace Drupal\band_booking_registration\Form;

use Drupal\band_booking_registration\Plugin\Block\RegistrationBlock;
use Drupal\band_booking_registration\RegistrationHelperInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RegisterUserForm extends FormBase {

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
  public static function create(ContainerInterface $container): RegisterUserForm {
    return new static(
      $container->get('band_booking_registration.registration_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'band_booking_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $arg = NULL) {
    // Prepare values for submit.
    $form_state->set('context_nid', $arg['context_nid'] ?? '');
    $form_state->set('register_bundle', $arg['register_bundle'] ?? '');

    // Values.
    $taxonomy_id = $arg['taxonomy_id'] ?? '';
    $taxonomy_terms = $arg['taxonomy_terms'] ?? [];
    $users = $arg['users'] ?? [];
    $registered_users = $arg['registered_users'] ?? [];

    // Text.
    $form_title = $arg['form_title'] ?? [];
    $already_title = $arg['already_title'] ?? [];
    $already_no_user = $arg['already_no_user'] ?? [];
    $filter_title = $arg['filter_title'] ?? [];
    $filter_description = $arg['filter_description'] ?? [];
    $add_title = $arg['add_title'] ?? [];
    $add_description = $arg['add_description'] ?? [];
    $no_taxonomy = $arg['no_taxonomy'] ?? [];
    $no_artist = $arg['no_artist'] ?? [];

    // Get default values.
    $value = $form_state->getUserInput();
    $terms_id = $value['register_users'][$taxonomy_id] ?? [];
    $users_id = $value['register_users']['users'] ?? [];

    if ($form_title) {
      $form['title'] = [
        '#type' => 'item',
        '#markup' => '<h3>' . $form_title . '</h3>',
      ];
    }

    // Already registered.
    $form['already_registered'] = [
      '#type' => 'item',
      '#title' => $already_title,
      '#markup' => empty($registered_users) ? $already_no_user : implode(', ', $registered_users),
    ];

    // Register user.
    $form['register_users'] = [
      '#tree' => TRUE,
      '#type' => 'selectusers',
      '#default_value' => [
        'terms_id' => $terms_id,
        'users_id' => $users_id,
      ],
      '#taxonomy_id' => $taxonomy_id,
      '#taxonomy_terms' => $taxonomy_terms,
      '#users' => $users,

      // Texts.
      '#filter_title' => $filter_title,
      '#filter_description' => $filter_description,
      '#add_title' => $add_title,
      '#add_description' => $add_description,
      '#no_taxonomy' => $no_taxonomy,
      '#no_artist' => $no_artist,
    ];

    // Display actions only if there are users.
    if (!empty($users)) {
      $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Register', array(), array('context' => 'Register form')),
        '#button_type' => 'primary',
      );
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, $arg = NULL) {
    $context_nid = $form_state->get('context_nid');
    $register_bundle = $form_state->get('register_bundle');
    $users = $form_state->getValue('register_users')['users'];

    $this->registrationHelper->registerUsers($context_nid, $register_bundle, $users);
  }
}
