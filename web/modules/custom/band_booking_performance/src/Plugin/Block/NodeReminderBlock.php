<?php

namespace Drupal\band_booking_performance\Plugin\Block;

use Drupal\band_booking_registration\RegistrationHelperInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an admin registration Block.
 *
 * @Block(
 *   id = "node_reminder_block",
 *   admin_label = @Translation("Node reminder"),
 *   category = @Translation("Band Booking"),
 * )
 */
class NodeReminderBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * @var \Drupal\band_booking_registration\RegistrationHelperInterface
   */
  protected RegistrationHelperInterface $registrationHelper;

  /**
   * Constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The current route match.
   * @param \Drupal\band_booking_registration\RegistrationHelperInterface $registrationHelper
   *   The bb registration helper.
   */
  public function __construct(
    array $configuration,
          $plugin_id,
          $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    FormBuilderInterface $form_builder,
    CurrentRouteMatch $currentRouteMatch,
    RegistrationHelperInterface $registrationHelper,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
    $this->currentRouteMatch = $currentRouteMatch;
    $this->registrationHelper = $registrationHelper;
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   *
   * @return NodeReminderBlock
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): NodeReminderBlock {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
      $container->get('current_route_match'),
      $container->get('band_booking_registration.registration_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account):AccessResult {
    return AccessResult::allowedIfHasPermission($account, 'use band booking reminder');
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    // Get contextual node id.
    $nid = $this->currentRouteMatch->getRawParameter('node');

    // TODO : check if node is published, date is to coming, etc.

    // Reminder no matter what day.
    if ($nid) {
      return [
        'title' => [
          '#markup' => '<h3>' . t('Relaunch pending registrations for this performance'). '</h3>',
        ],
        'description' => [
          '#markup' => '<p>' . t('This does not take into account scheduled reminders.'). '</p>',
        ],
        'form' => $this->formBuilder->getForm(
          'Drupal\band_booking_performance\Form\ReminderForm',
          ['nids' => [$nid]],
        ),
      ];
    } else {
      return [
        '#markup' => $this->t('Place this block in a performance page'),
      ];
    }
  }
}
