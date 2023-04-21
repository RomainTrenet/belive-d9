<?php

/**
 * @file
 * Contains \Drupal\bb_dev\Form\PerformanceRegisterForm.
 */
//Contains \Drupal\bb_dev\Form\PerformanceRegisterForm.
namespace Drupal\bb_dev\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class PerformanceRegisterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    //return ('bb_dev_form');
    return ('performance_register_form');
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['selectbox'] = [
      '#type' => 'selectbox',
      '#title' => $this->t('My select box'),
      '#description' => $this->t('My description'),
      '#required' => TRUE,
      '#multiple' => TRUE,
      '#attributes' => [
        'multiple' => TRUE,
        'direction' => 'left',
      ],
      '#size' => 3,
      '#options' => [
        '1a' => $this
          ->t('One a'),
        '1b' => $this
          ->t('One b'),
        '1c' => $this
          ->t('One c'),
        '2' => [
          '2.1' => $this
            ->t('Two point one'),
          '2.2' => $this
            ->t('Two point two'),
        ],
        '3' => $this
          ->t('Three'),
        '4' => $this
          ->t('Four'),
        '5' => $this
          ->t('Five'),
      ],
      '#default_value' => ['3', '4']
    ];

    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    /*
    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    */

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit !'),
      '#button_type' => 'primary',
    );

    return $form;

  }

  /**
   * Validate the title and the checkbox of the form
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $title = $form_state->getValue('title');
    $accept = $form_state->getValue('accept');

    if (strlen($title) < 10) {
      // Set an error for the form element with a key of "title".
      $form_state->setErrorByName('title', $this->t('The title must be at least 10 characters long.'));
    }

    if (empty($accept)){
      // Set an error for the form element with a key of "accept".
      $form_state->setErrorByName('accept', $this->t('You must accept the terms of use to continue'));
    }

  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Display the results.

    // Call the Static Service Container wrapper
    // We should inject the messenger service, but its beyond the scope of this example.
    $messenger = \Drupal::messenger();
    //$messenger->addMessage('Title: '.$form_state->getValue('title'));
    //$messenger->addMessage('Accept: '.$form_state->getValue('accept'));
    $truc = $form_state->getValue('selectbox');

    // Redirect to home
    $form_state->setRedirect('<front>');

  }
}
