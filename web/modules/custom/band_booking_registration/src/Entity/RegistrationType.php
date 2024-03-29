<?php

namespace Drupal\band_booking_registration\Entity;

use Drupal\band_booking_registration\RegistrationTypeInterface;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Registration type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "registration_type",
 *   label = @Translation("Registration type"),
 *   label_collection = @Translation("Registration types"),
 *   label_singular = @Translation("registration type"),
 *   label_plural = @Translation("registrations types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count registrations type",
 *     plural = "@count registrations types",
 *   ),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\band_booking_registration\Form\RegistrationTypeForm",
 *       "edit" = "Drupal\band_booking_registration\Form\RegistrationTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\band_booking_registration\RegistrationTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   admin_permission = "administer registration types",
 *   bundle_of = "registration",
 *   config_prefix = "registration_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "allowed_roles" = "allowed_roles",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/registration_types/add",
 *     "edit-form" = "/admin/structure/registration_types/manage/{registration_type}",
 *     "delete-form" = "/admin/structure/registration_types/manage/{registration_type}/delete",
 *     "collection" = "/admin/structure/registration_types"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "allowed_roles",
 *     "uuid",
 *   }
 * )
 */
class RegistrationType extends ConfigEntityBundleBase implements RegistrationTypeInterface {

  /**
   * The machine name of this registration type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the registration type.
   *
   * @var string
   */
  protected $label;

  /**
   * An array of user roles allowed to be registered.
   *
   * @var array
   */
  protected array $allowed_roles;

  /**
   * The default registration state. TODO
   *
   * @var string
   */
  //protected string $defaultState = 'pending';

  /**
   * {@inheritdoc}
   * /
  public function getAllowedRoles(): array {
    return $this->allowed_roles;
  }

  /**
   * {@inheritdoc}
   * /
  public function setAllowedRoles($roles): RegistrationTypeInterface {
    $this->allowed_roles = $roles;
    return $this;
  }*/
}
