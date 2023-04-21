<?php

namespace Drupal\band_booking_registration\Element;

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
      '#theme_wrappers' => ['selectusers_wrapper'],
      '#title' => '',
      '#description' => '',
      '#taxonomy_id' => '',
      '#taxonomy_terms' => [],
      '#users' => [],
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
    // Variables.
    $taxonomy_id = $element['#taxonomy_id'];

    // Construct element.
    $element['#tree'] = TRUE;
    if (!empty($taxonomy_id)) {
      $element[$taxonomy_id] = [
        //'#type' => 'selectbox',
        '#type' => 'select',
        '#title' => $element['#filter_title'] ?? '',
        '#description' => $element['#filter_description'] ?? '',
        '#required' => FALSE,

        // TODO improve, les coches ne fonctionnent pas si on ne le met pas.
        '#multiple' => TRUE,
        '#attributes' => [
          'multiple' => TRUE,
          'direction' => 'left',
        ],

        '#size' => 3,
        '#options' => $element['#taxonomy_terms'] ?? '',
        '#default_value' => $element['#default_value']['terms_id']
      ];
    }

    $element['users'] = [
      //'#type' => 'selectbox',
      '#type' => 'select',
      '#title' => $element['#add_title'] ?? '',
      '#description' => $element['#add_description'] ?? '',
      '#required' => TRUE,

      // TODO improve, les coches ne fonctionnent pas si on ne le met pas.
      '#multiple' => TRUE,
      '#attributes' => [
        'multiple' => TRUE,
        'direction' => 'left',
      ],

      '#size' => 3,
      '#options' => $element['#users'] ?? [],
      '#default_value' => $element['#default_value']['users_id']
    ];

    return $element;
  }

  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    // TODO clean.

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
