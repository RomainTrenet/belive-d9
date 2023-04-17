<?php

//TODO clean, remove unwanted service.

namespace Drupal\band_booking_registration;

// use Drupal\Core\Entity\EntityTypeManagerInterface;
// use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\user\Entity\Role;

/**
 * Service to provide ....
 */
class RegistrationHelper implements RegistrationHelperInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   * /
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   * /
  protected $moduleHandler;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
  }*/

  /**
   * {@inheritdoc}
   */
  public function getSiteRoles(): array {
    $roles = [];
    $roles_entities = Role::loadMultiple();
    unset($roles_entities['administrator']);

    // Translate.
    foreach ($roles_entities as $key => $role) {
      $roles[$key] = $role->label();
    }

    return $roles;
  }

}
