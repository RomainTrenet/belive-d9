<?php

//TODO clean, remove unwanted service.

namespace Drupal\band_booking_registration;

// use Drupal\Core\Extension\ModuleHandlerInterface;
// use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\Role;

/**
 * Service to provide ....
 */
class RegistrationHelper implements RegistrationHelperInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  //protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   * /
  //protected $moduleHandler;
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {//, ModuleHandlerInterface $module_handler) {
    $this->entityTypeManager = $entity_type_manager;
    //$this->moduleHandler = $module_handler;
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

  /**
   * {@inheritdoc}
   */
  public function getTaxonomyTermsOptions(string $vid): array
  {
    $options = [];
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', $vid);
    $query->condition('status', 1);
    $query->sort('weight');
    $tids = $query->execute();
    $terms = Term::loadMultiple($tids);

    // TODO : check translation, check order ?
    foreach ($terms as $term) {
      $options[$term->id()] = $term->label();
    }

    return $options;
  }

}
