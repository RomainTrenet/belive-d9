<?php

namespace Drupal\band_booking_registration\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\pluginformalter\Annotation\FormAlter;
use Drupal\pluginformalter\Plugin\FormAlterBase;

/**
 * Class BandBookingRegistrationEditFormAlter.
 *
 * @FormAlter(
 *   id = "band_booking_registration_edit_form_alter",
 *   label = @Translation("Altering every registration edit form."),
 *   form_id = {
 *    "registration_performance_edit_form"
 *   },
 * )
 *
 * @package Drupal\band_booking_registration\Plugin\FormAlter
 */
class BandBookingRegistrationEditFormAlter extends FormAlterBase {

  /**
   * {@inheritdoc}
   */
  public function formAlter(array &$form, FormStateInterface &$form_state, $form_id) {
    //$field_state = $form['field_state'];
    //unset($form['field_state']);

    if (isset($form['field_state'])) {
      if (isset($form['field_state']['widget'])) {
        $form['field_state']['widget']['#weight'] = 50;
        unset($form['field_state']['widget']['#title']);
      }
      $form['field_state']['desc'] = [
        '#markup' => 'XX vous a ajouté à l\'évènement',
        '#weight' => 10,
      ];
      $form['field_state']['actual_state_title'] = [
        '#markup' => '<h2>Statut actuel de votre inscription</h2>',
        '#weight' => 20,
      ];
      $form['field_state']['actual_state'] = [
        '#markup' => 'ACTUAL STATE !',
        '#weight' => 30,
      ];
      $form['field_state']['field_state_title'] = [
        '#markup' => '<h2>Modifier l\'état de votre inscription</h2>',
        '#weight' => 40,
      ];
    }

    /*
    $form['desc'] = [
      '#markup' => 'XX vous a ajouté à l\'évènement',
      '#weight' => 10,
    ];
    $form['actual_state_title'] = [
      '#markup' => '<h2>Statut actuel de votre inscription</h2>',
      '#weight' => 20,
    ];
    $form['actual_state'] = [
      '#markup' => 'ACTUAL STATE !',
      '#weight' => 30,
    ];
    $form['field_state_title'] = [
      '#markup' => '<h2>Modifier l\'état de votre inscription</h2>',
      '#weight' => 40,
    ];
    $form['taxonomy'] = array(
      '#type' => 'markup',
      '#value' => "<p>These are the instructions</p>",
      '#weight' => -1,
    );
    */

    // It will be shown on every node edit form.
    //$form['#prefix'] = '<h2>Edit form</h2>';
    $foo = 'bar';
  }

}
