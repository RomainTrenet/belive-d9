<?php

namespace Drupal\band_booking_performance\Form;

use Drupal\band_booking_performance\PerformanceHelper;
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
  public function buildForm(array $form, FormStateInterface $form_state, $arg = NULL) {
    // Record arguments such contextual timestamp, nids, etc., to get it in submit.
    $form_state->set('arg', $arg);

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
    /** @var PerformanceHelper $performanceHelper */
    $performanceHelper = \Drupal::service('band_booking_performance.performance_helper');

    // Get argument such timestamp, nids, etc.
    $arg = $form_state->get('arg');

    $performanceHelper->performanceReminder(
      TRUE,
      $arg['nids'] ?? [],
      $arg['contextualTimestamp'] ?? null,
      $arg['startFromContextualTs'] ?? false,// Avoid ? We don't want to send default value.
      $arg['current_date'] ?? null,
    );
  }

}
