<?php

// TODO : clean baseFieldDefinitions + calories and order.

namespace Drupal\band_booking_registration\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\band_booking_registration\RegistrationInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the registration entity class.
 *
 * @ingroup registration
 *
 * @ContentEntityType(
 *   id = "registration",
 *   label = @Translation("Registration"),
 *   label_collection = @Translation("Registrations"),
 *   label_singular = @Translation("registration"),
 *   label_plural = @Translation("registrations"),
 *   label_count = @PluralTranslation(
 *     singular = "@count registrations",
 *     plural = "@count registrations",
 *   ),
 *   bundle_label = @Translation("Registration type"),
 *   handlers = {
 *     "list_builder" = "Drupal\band_booking_registration\RegistrationListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\band_booking_registration\RegistrationAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\band_booking_registration\Form\RegistrationForm",
 *       "edit" = "Drupal\band_booking_registration\Form\RegistrationForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "registration",
 *   data_table = "registration_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer registration types",
 *   entity_keys = {
 *     "id" = "id",
 *     "langcode" = "langcode",
 *     "bundle" = "bundle",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/registration",
 *     "add-form" = "/registration/add/{registration_type}",
 *     "add-page" = "/registration/add",
 *     "canonical" = "/registration/{registration}",
 *     "edit-form" = "/registration/{registration}/edit",
 *     "delete-form" = "/registration/{registration}/delete",
 *   },
 *   bundle_entity_type = "registration_type",
 *   field_ui_base_route = "entity.registration_type.edit_form",
 * )
 */
class Registration extends ContentEntityBase implements RegistrationInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setTranslatable(TRUE)
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the registration was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the registration was last edited.'));

    $fields['registration_user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setDescription(t('The user registered.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      // TODO : get role id from registration type.
      //->setSetting('handler_settings', ['target_bundles' => ['custom_vocabulary' => 'custom_vocabulary']]);
      //handler_settings
      // @todo : check
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function description() {
    // Retrieve the @description property from the annotation and return it.
    return $this->pluginDefinition['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function calories() {
    // Retrieve the @calories property from the annotation and return it.
    return (float) $this->pluginDefinition['calories'];
  }

  /**
   * Place an order for a sandwich.
   *
   * This is just an example method on our plugin that we can call to get
   * something back.
   *
   * @param array $extras
   *   Array of extras to include with this order.
   *
   * @return string
   *   A description of the sandwich ordered.
   */
  public function order(array $extras) {
    $ingredients = ['ham, mustard', 'rocket', 'sun-dried tomatoes'];
    $sandwich = array_merge($ingredients, $extras);
    return 'You ordered an ' . implode(', ', $sandwich) . ' sandwich. Enjoy!';
  }

}
