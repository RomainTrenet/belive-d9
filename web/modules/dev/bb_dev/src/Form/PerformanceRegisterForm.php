<?php
/**
 * @file
 * Contains \Drupal\band_booking_artist\Form\ArtistRegistrationForm.
 */
namespace Drupal\bb_dev\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class PerformanceRegisterForm extends BaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return ('bb_dev_form');
  }
}
