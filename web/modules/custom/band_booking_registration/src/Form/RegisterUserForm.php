<?php
/**
 * @file
 * Contains \Drupal\band_booking_registration\Form\RegisterUserForm.
 */
namespace Drupal\band_booking_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class RegisterUserForm extends FormBase {
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
    // Prepare values for 'register_user' field.
    $taxonomy_id = $arg['taxonomy_id'] ?? '';
    $roles_id = $arg['roles_id'] ?? [];
    $register_type = $arg['register_type'] ?? '';
    $filter_title = $arg['filter_title'] ?? [];
    $filter_description = $arg['filter_description'] ?? [];
    $add_title = $arg['add_title'] ?? [];
    $add_description = $arg['add_description'] ?? [];

    // Get default values.
    $value = $form_state->getUserInput();
    $terms_id = $value['register_user'][$taxonomy_id] ?? [];
    $users_id = $value['register_user']['users'] ?? [];

    $form['register_user'] = [
      '#tree' => TRUE,
      '#type' => 'selectusers',
      '#default_value' => [
        'terms_id' => $terms_id,
        'users_id' => $users_id,
      ],
      '#taxonomy_id' => $taxonomy_id,
      '#roles_id' => $roles_id,
      '#filter_title' => $filter_title,
      '#filter_description' => $filter_description,
      '#add_title' => $add_title,
      '#add_description' => $add_description,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Register'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO : user / event.
    $output = $this->t('@emp_name ,Your application is being submitted!', array('@emp_name' => $form_state->getValue('employee_name')));
    $type = 'status';
    $this->messenger()->addMessage($output, $type, TRUE);
  }
}
