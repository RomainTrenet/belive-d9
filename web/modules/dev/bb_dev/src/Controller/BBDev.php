<?php

declare(strict_types = 1);

namespace Drupal\bb_dev\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for POP Produit routes.
 */
final class BBDev extends ControllerBase {

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
  public static function create(ContainerInterface $container): BBDev {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('form_builder')
    );
  }

  /**
   * Returns customized user register form.
   *
   * @see band_booking_artist_entity_type_build.
   */
  public function content(): array {
    return $this->formBuilder->getForm(\Drupal\bb_dev\Form\PerformanceRegisterForm::class);
    /*return [
      '#markup' => '<p>ICI</p>'
    ];*/
  }

}
