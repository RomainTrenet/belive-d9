<?php

namespace Drupal\band_booking_registration\Element;

use Drupal\band_booking_registration\RegistrationHelper;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a selectusers element.
 *
 * @FormElement("selectusers")
 */
class SelectUsers extends Element\FormElement {

  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processSelectUsers'],
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
        [$class, 'preRenderGroup'],
      ],
      '#theme' => 'selectusers_form',
      '#title' => '',
      '#description' => '',
      '#theme_wrappers' => ['selectusers_wrapper'],
      '#taxonomy_id' => '',
      '#users_rid' => [],
      '#filter_title' => '',
      '#filter_description' => '',
      '#add_title' => '',
      '#add_description' => '',
      /*
      '#element_validate' => [
        [$class, 'validateSelectusers'],//validateTimerange
      ],
      '#time_callbacks' => [],
      */
    ];
  }

  public static function processSelectUsers(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#tree'] = TRUE;
    $users_rid = $element['#users_rid'];

    // Use helper for this static method.
    /** @var $registration_helper \Drupal\band_booking_registration\RegistrationHelper */
    $registration_helper = \Drupal::service('band_booking_registration.registration_helper');

    $taxonomy_id = $element['#taxonomy_id'];
    if (!empty($taxonomy_id)) {
      $element[$taxonomy_id] = [
        //'#type' => 'selectbox',
        '#type' => 'select',
        '#title' => $element['#filter_title'] ?? '',
        '#description' => $element['#filter_description'] ?? '',
        '#required' => FALSE,
        '#multiple' => TRUE,
        '#attributes' => [
          'multiple' => TRUE,
          'direction' => 'left',
        ],
        '#size' => 3,
        '#options' => $registration_helper->getTaxonomyTermsOptions($taxonomy_id),
        '#default_value' => $element['#default_value']['terms_id']
      ];
    }

    $element['users'] = [
      //'#type' => 'selectbox',
      '#type' => 'select',
      '#title' => $element['#add_title'] ?? '',
      '#description' => $element['#add_description'] ?? '',
      '#required' => FALSE,
      '#multiple' => TRUE,
      '#attributes' => [
        'multiple' => TRUE,
        'direction' => 'left',
      ],
      '#size' => 3,
      '#options' => [
        '0' => 'User 0',
        '1' => 'User 1',
        '2' => 'User 2',
        '3' => 'User 3',
        '4' => 'User 4',
      ],
      '#default_value' => $element['#default_value']['users_id']
    ];

    return $element;
  }

  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    // TODO improve.
    //$element['#taxonomy_id'];
    // Load terms id ? load users ?

    // Provide default values if there are none.
    if (!isset($element['#default_value']['terms_id'])) {
      $element['#default_value']['terms_id'] = [];
    }
    if (!isset($element['#default_value']['users_id'])) {
      $element['#default_value']['users_id'] = [];
    }

    if ($input !== FALSE) {
      /*
      $format = isset($element['#time_format']) && $element['#time_format'] ? $element['#time_format'] : 'html_time';
      $time_format =  DateFormat::load($format)->getPattern();

      try {
        DrupalDateTime::createFromFormat($time_format, $input['start'], NULL);
      }
      catch (\Exception $e) {
        $input['start'] = NULL;
      }

      try {
        DrupalDateTime::createFromFormat($time_format, $input['end'], NULL);
      }
      catch (\Exception $e) {
        $input['end'] = NULL;
      }*/
    }
    else {
      $input = [
        //'start' => $element['#default_value']['start'],
        //'end' => $element['#default_value']['end'],
        'terms_id' => $element['#default_value']['terms_id'],
        'users_id' => $element['#default_value']['users_id'],
      ];
    }

    return $input;
  }
}
