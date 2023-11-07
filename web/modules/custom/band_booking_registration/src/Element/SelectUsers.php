<?php

namespace Drupal\band_booking_registration\Element;

use Drupal\band_booking_registration\RegistrationHelper;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
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

    if (!empty($element['#users'])) {

      // Display taxonomy filter.
      if (!empty($taxonomy_id) && !empty($element['#taxonomy_terms'])) {
        $element[$taxonomy_id] = [
          // TODO : check why my selectbox doesn't work.
          //'#type' => 'selectbox',
          '#type' => 'select',
          '#title' => $element['#filter_title'] ?? '',
          '#description' => $element['#filter_description'] ?? '',
          '#required' => FALSE,

          // TODO improve, check marks doesn't work without multiple true.
          '#multiple' => TRUE,
          '#attributes' => [
            'multiple' => TRUE,
            //'direction' => 'left',
            // TODO remove.
            'class' => ['vanilla-select-box'],
          ],
          // TODO remove.
          '#attached' => [
            'library' => [
              'vanilla_select_box/select-box',
              'band_booking_registration/band-booking-registration',
            ]
          ],
          //'#size' => 3,
          '#options' => $element['#taxonomy_terms'],
          '#default_value' => $element['#default_value']['terms_id'],
          '#ajax' => [
            'callback' => ['\Drupal\band_booking_registration\Element\SelectUsers', 'refindUsers'],
            'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
            'event' => 'change',
            'progress' => [
              'type' => 'throbber',
              'message' => t('Filtering'),
            ],
          ]
        ];
      } else {
        $element[$taxonomy_id] = [
          '#type' => 'item',
          '#title' => $element['#filter_title'] ?? '',
          '#markup' => $element['#no_taxonomy'] ?? '',
        ];
      }

      // Display users.
      $element['users'] = [
        // TODO : idem.
        //'#type' => 'selectbox',
        '#type' => 'select',
        // TODO : title no showing.
        '#title' => $element['#add_title'] ?? '',
        '#description' => $element['#add_description'] ?? '',
        '#required' => TRUE,

        // TODO improve, check marks doesn't work without multiple true.
        '#multiple' => TRUE,
        '#attributes' => [
          'multiple' => TRUE,
          //'direction' => 'left',
          // TODO remove.
          'class' => ['vanilla-select-box'],
        ],
        // TODO remove.
        '#attached' => [
          'library' => [
            'vanilla_select_box/select-box'
          ]
        ],

        '#size' => 3,
        '#options' => $element['#users'] ?? [],
        '#default_value' => $element['#default_value']['users_id']
      ];
    }

    // Empty users.
    else {
      $element['users'] = [
        '#type' => 'item',
        '#title' => $element['#add_title'] ?? '',
        '#markup' => $element['#no_artist'] ?? '',
      ];
    }

    return $element;
  }

  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    // Provide default values if there are none.
    if (!isset($element['#default_value']['terms_id'])) {
      $element['#default_value']['terms_id'] = [];
    }
    if (!isset($element['#default_value']['users_id'])) {
      $element['#default_value']['users_id'] = [];
    }

    // TODO clean.
    if ($input !== FALSE) {
    }
    else {
      $input = [
        'terms_id' => $element['#default_value']['terms_id'],
        'users_id' => $element['#default_value']['users_id'],
      ];
    }

    return $input;
  }

  /**
   * Call as ajax callback from SelectUsers. TODO Should be in Element.
   *
   * @param array $form
   *   The current form.
   * @param FormStateInterface $form_state
   *   The current form state.
   *
   * @return AjaxResponse
   */
  public static function refindUsers(array &$form, FormStateInterface $form_state): AjaxResponse
  {
    // TODO : use dependency injection.
    /** @var RegistrationHelper $registrationHelper */
    $registrationHelper = \Drupal::service('band_booking_registration.registration_helper');

    // Get values.
    $allowed_roles = $form['register_users']['#allowed_roles'];
    $registered_users_id = $form['register_users']['#registered_users_id'];
    $registerUsersFormState = $form_state->getValue('register_users');
    $positions = $registerUsersFormState['position'];

    // Get Users to hide.
    $users = $form['register_users']['users']['#options'];
    $usersToShow = $registrationHelper->getUnregisteredUsersId($allowed_roles, $registered_users_id, $positions);
    $usersToHide = array_diff_key($users, $usersToShow);

    // Construct ajax response : call custom javascript to hide non-wanted users.
    $response = new AjaxResponse();
    // TODO should come from form id attributes ? To avoid problem with double.
    $select_id = '#btn-group-edit-register-users-users';
    $method = 'bbrRefindArtists';
    $arguments = [
      $select_id,
      $usersToHide,
    ];

    $response->addCommand(new InvokeCommand(NULL, $method, $arguments));
    return $response;
  }
}
