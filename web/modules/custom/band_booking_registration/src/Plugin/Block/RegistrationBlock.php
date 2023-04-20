<?php

// TODO add config to used registration_type, and get tid and roles from it.
// See https://www.agiledrop.com/blog/how-create-custom-block-drupal-8-9-10

namespace Drupal\band_booking_registration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an admin registration Block.
 *
 * @Block(
 *   id = "registration_block",
 *   admin_label = @Translation("Register artist"),
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
   * Constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    FormBuilderInterface $form_builder,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
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
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = $this->formBuilder->getForm(
      'Drupal\band_booking_registration\Form\RegisterUserForm',
      // TODO : dynamic arguments.
      [
        // Register type from node ?
        'register_type' => 'performance',
        // Taxonomy id from register type or from block config ?
        'taxonomy_id' => 'position',
        // Role id form register type.
        'roles_id' => ['artist'],
        // @todo get it from config.
        'filter_title' => $this->t('Filter by position'),
        'filter_description' => $this->t('Select the artists position to filter artists list.'),
        'add_title' => $this->t('Add artist'),
        'add_description' => $this->t('Select the artists you want to add. The artists already added will not appear.'),
      ],
    );

    return [
      '#theme' => 'admin_registration_block',
      '#registered' => 'ALREADY',
      '#register_form' => $form,
    ];
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
