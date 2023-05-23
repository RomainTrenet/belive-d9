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
   * Keep track of how many times the form
   * is placed on a page.
   *
   * @var int
   */
  protected static $instanceId;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    if (empty(self::$instanceId)) {
      self::$instanceId = 1;
    }
    else {
      self::$instanceId++;
    }

    return 'band_booking_performance_reminder_form_' . self::$instanceId;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $arg = NULL) {
    // Record arguments such contextual timestamp, nids, etc., to get it in submit.
    $form_state->set('arg', $arg);

    $form['reminder'] = array(
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Relaunch', array(), array('context' => 'Reminder form')),
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
      $arg['force'] ?? false,// Avoid ? We don't want to send default value.
      $arg['nids'] ?? [],
      $arg['startFromContextualTs'] ?? false,// Avoid ? We don't want to send default value.
      $arg['contextualTimestamp'] ?? null,
      $arg['current_date'] ?? null,
    );
  }

}
