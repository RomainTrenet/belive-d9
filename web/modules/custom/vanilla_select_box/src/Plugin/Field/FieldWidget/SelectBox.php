<?php

namespace Drupal\vanilla_select_box\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * A widget select box. @todo Romain.
 *
 * @FieldWidget(
 *   id = "selectbox",
 *   label = @Translation("Select box"),
 *   field_types = {
 *     "baz",
 *     "string"
 *   }
 * )
 */
class SelectBox extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    /*
    $element += [
      // Add to the element render array passed in.
      // See WidgetInterface::formElement().
    ];

    return ['value' => $element];
    */

    $element['value'] = $element + [
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#size' => $this->getSetting('size'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        // Create the custom setting 'length', and
        // assign a default value of 6
        'length' => 6,
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['length'] = [
      '#type' => 'number',
      '#title' => $this->t('Length of select'),
      '#default_value' => $this->getSetting('length'),
      '#required' => TRUE,
      '#min' => 1,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Length of select: @length', array('@length' => $this->getSetting('length')));

    return $summary;
  }

}
