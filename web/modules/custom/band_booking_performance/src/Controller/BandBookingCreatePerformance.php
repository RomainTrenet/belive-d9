<?php

declare(strict_types = 1);

namespace Drupal\band_booking_performance\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns performance node add form.
 */
final class BandBookingCreatePerformance extends ControllerBase {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FormBuilderInterface $form_builder) {
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): BandBookingCreatePerformance {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('form_builder')
    );
  }

  /**
   * Create form.
   *
   * @see band_booking_artist_entity_type_build.
   */
  public function content(): array {
    $entity = $this->entityTypeManager
      ->getStorage('node')
      ->create(['type' => 'performance']);

    $formObject = $this->entityTypeManager
      ->getFormObject('node', 'default')
      ->setEntity($entity);

    return $this->formBuilder->getForm($formObject);
  }

}
