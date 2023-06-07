<?php

namespace Drupal\band_booking_registration\Plugin\Block;

use Drupal\band_booking_registration\RegistrationHelperInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a status registration Block.
 *
 * @Block(
 *   id = "registration_status_block",
 *   admin_label = @Translation("Status registration"),
 *   category = @Translation("Band Booking"),
 * )
 */
class RegistrationStatusBlock extends BlockBase implements ContainerFactoryPluginInterface
{
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
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

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
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user service.
   */
  public function __construct(
    array $configuration,
          $plugin_id,
          $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    FormBuilderInterface $form_builder,
    CurrentRouteMatch $currentRouteMatch,
    RegistrationHelperInterface $registrationHelper,
    AccountProxyInterface $current_user,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
    $this->currentRouteMatch = $currentRouteMatch;
    $this->registrationHelper = $registrationHelper;
    $this->currentUser = $current_user;
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   *
   * @return RegistrationBlock
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
                       $plugin_id,
                       $plugin_definition,
  ): RegistrationStatusBlock {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
      $container->get('current_route_match'),
      $container->get('band_booking_registration.registration_helper'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  /*
  TODO.
  protected function blockAccess(AccountInterface $account):AccessResult {
    return AccessResult::allowedIfHasPermission($account, 'manage registration');
  }
  */

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $content = [];

    // Get contextual node object.
    /** @var Node $node */
    $node = $this->currentRouteMatch->getParameter('node');
    if ($node) {
      $registered_users_id = $this->registrationHelper->getRegisteredUsersId($node->id());
      $uid = $this->currentUser->id();

      // If current user has registration for this node.
      if (in_array($uid, $registered_users_id)) {
        // Get performance user registrationS.

        // Load each registration.
        $entity_type_id = 'registration';
        $rids = $this->registrationHelper->getPerformanceUserRegistrationsId($node->id(), $uid);

        foreach ($rids as $rid) {
          $registration = $this->entityTypeManager->getStorage($entity_type_id)->load($rid);
          // See 'band_booking_registration_entity_type_build' which add form class.
          $formObject = $this->entityTypeManager->getFormObject($entity_type_id, 'band_booking_registration_status');
          $formObject->setEntity($registration);
          $content[$rid] = $this->formBuilder->getForm($formObject, $registration);
        }
      }
    }

    return $content;
  }
}
