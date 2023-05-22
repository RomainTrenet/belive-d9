<?php

namespace Drupal\band_booking_performance\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form with examples on how to use batch api.
 */
class ReminderForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'band_booking_performance_reminder_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['reminder'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Reminder'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $performanceHelper = \Drupal::service('band_booking_performance.performance_helper');

    $special_dev_date = '-0 day';
    $current_date = strtotime($special_dev_date);
    $contextualTimestamp = strtotime($special_dev_date);
    $performanceHelper->performanceReminder([], $contextualTimestamp, $current_date);
  }

}
