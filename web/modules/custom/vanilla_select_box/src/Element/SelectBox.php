<?php

namespace Drupal\vanilla_select_box\Element;

use Drupal\Core\Render\Element\FormElement;

/**
 * Provides a selectbox element.
 *
 * @FormElement("selectbox")
 */
class SelectBox extends FormElement
{
  /**
   * {@inheritdoc}
   */
  public function getInfo() {

    // Returns an array of default properties that will be merged with any
    // properties defined in a render array when using this element type.
    // You can use any standard render array property here, and you can also
    // custom properties that are specific to your new element type.
    return [
      '#options' => [],
      '#sort_options' => FALSE,
      '#sort_start' => 0,
      '#empty_option' => $this->t('Select'),
      '#default_value' => NULL,
      '#required' => FALSE,
      '#size' => 5,
      '#input' => TRUE,
      // See render_example_theme() where this new theme hook is declared.
      '#theme' => 'select',
      '#theme_wrappers' => ['form_element'],
      // Define a default #pre_render method. We will use this to handle
      // additional processing for the custom attributes we add below.
      '#pre_render' => [
        [self::class, 'preRenderSelectBox'],
      ],
      '#attached' => [
        'library' => [
          'vanilla_select_box/select-box'
        ]
      ],
      '#attributes' => [
        'multiple' => FALSE,
        'direction' => 'left',
      ]
    ];
  }

  /**
   * Pre-render callback; Process custom attribute options.
   *
   * @param array $element
   *   The renderable array representing the element with '#type' => 'selectbox'
   *   property set.
   *
   * @return array
   *   The passed in element with changes made to attributes depending on
   *   context.
   */
  public static function preRenderSelectBox(array $element) {
    $element['#attributes']['type'] = 'select';
    // Ensure keeping class when overriding attributes.
    $element['#attributes']['class'] = ['vanilla-select-box'];
    return $element;
  }
}
