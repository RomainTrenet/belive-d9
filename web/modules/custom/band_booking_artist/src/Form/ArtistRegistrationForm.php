<?php
/**
 * @file
 * Contains \Drupal\band_booking_artist\Form\ArtistRegistrationForm.
 */
namespace Drupal\band_booking_artist\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\RegisterForm;

class ArtistRegistrationForm extends RegisterForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return ('bba_artist_registration_form');
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->activate();
    $this->entity->addRole('artist');

    parent::save($form, $form_state);
  }
}
