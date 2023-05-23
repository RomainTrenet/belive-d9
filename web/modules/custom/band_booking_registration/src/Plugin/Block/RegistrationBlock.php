<?php

// TODO add config to used registration_type, and get tid and roles from it.
// See https://www.agiledrop.com/blog/how-create-custom-block-drupal-8-9-10

namespace Drupal\band_booking_registration\Plugin\Block;

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
 *   id = "registration_block",
 *   admin_label = @Translation("Artists registration"),
 *   category = @Translation("Band Booking"),
 * )
 */
class RegistrationBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * @return RegistrationBlock
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): RegistrationBlock {
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
    return AccessResult::allowedIfHasPermissions($account, ['create registration', 'delete registration']);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get contextual node id.
    $nid = $this->currentRouteMatch->getRawParameter('node');

    // Return content only if node id is defined.
    if ($nid) {
      // Settings.
      // TODO Register type from node ?
      $register_bundle = 'performance';
      // TODO Role id form register type.
      $allowed_roles = ['artist'];
      // Taxonomy id from register type or from block config ?
      $taxonomy_id = 'position';

      // Calculate.
      $registered_users_id = $this->registrationHelper->getRegisteredUsersId($nid);
      $registered_users = $this->registrationHelper->getOptionsUserList($registered_users_id);
      $registered_users_by_rid = $this->registrationHelper->getOptionsUserRegistrationList($registered_users_id);
      $unregistered_users_id = $this->registrationHelper->getUnregisteredUsersId($allowed_roles, $registered_users_id);
      $unregistered_users = $this->registrationHelper->getOptionsUserList($unregistered_users_id);
      $taxonomy_terms = $this->registrationHelper->getTaxonomyTermsOptions($taxonomy_id);

      $register_form = $this->formBuilder->getForm(
        'Drupal\band_booking_registration\Form\RegisterUserForm',
        [
          'context_nid' => $nid,
          'register_bundle' => $register_bundle,
          'taxonomy_id' => $taxonomy_id,
          'taxonomy_terms' => $taxonomy_terms,
          'users' => $unregistered_users,
          'registered_users' => $registered_users,

          // @todo get it from config.
          'form_title' => $this->t('Add artist'),
          'already_title' => $this->t('Artists already registered'),
          'already_no_user' => $this->t('No artist already registered.'),
          'filter_title' => $this->t('Filter by position'),
          'filter_description' => $this->t('Select the artists position to filter artists list.'),
          'add_title' => $this->t('Artists'),
          'add_description' => $this->t('Select the artists you want to add. The artists already added will not appear.'),
          'no_taxonomy' => $this->t('No position available.'),
          'no_artist' => $this->t('No artist to register.'),
        ],
      );

      $unregister_form = $this->formBuilder->getForm(
        'Drupal\band_booking_registration\Form\UnregisterUserForm',
        [
          'context_nid' => $nid,
          'register_bundle' => $register_bundle,
          'registered_users_by_rid' => $registered_users_by_rid,
          // @todo get it from config.
          'form_title' => $this->t('Unregister artists'),
          'remove_title' => $this->t('Artists'),
          'remove_description' => $this->t('Select the artists you want to unregister.'),
          'no_artist' => $this->t('No artist to unregister.'),
        ],
      );

      return [
        '#theme' => 'admin_registration_block',
        '#register_form' => $register_form,
        '#unregister_form' => $unregister_form,
      ];
    }
    else {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   * /
  public function blockForm($form, FormStateInterface $form_state) : array {
    $form = parent::blockForm($form, $form_state);

    // Retrieve the blocks configuration as the values provided in the form
    // are stored there.
    $config = $this->getConfiguration();

    // The form field is defined and added to the form array here.
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#description' => $this->t('Type the message you want visitors to see'),
      '#default_value' => $config['message'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   * /
  public function blockSubmit($form, FormStateInterface $form_state) : void {
    // We do this to ensure no other configuration options get lost.
    parent::blockSubmit($form, $form_state);

    // Here the value entered by the user is saved into the configuration.
    $this->configuration['message'] = $form_state->getValue('message');
  }

  /**
   * {@inheritdoc}
   * /
  public function blockValidate($form, FormStateInterface $form_state) : void {
    // The configuration form validation is performed here.
    // In this example we don't want the message text to be 'Helloworld!'
    if ($form_state->getValue('message') === 'Hello world!') {
      $form_state->setErrorByName(
        'message',
        $this->t('You cannot enter the most generic text ever!')
      );
    }
  }
  */
}
