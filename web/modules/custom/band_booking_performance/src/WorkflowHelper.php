<?php

namespace Drupal\band_booking_performance;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowTransition;

/**
 * Contains helper methods to retrieve workflow related data from entities.
 */
class WorkflowHelper implements WorkflowHelperInterface {

  /**
   * The current user proxy.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a WorkflowHelper.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The service that contains the current user.
   */
  public function __construct(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
  }


  /**
   * {@inheritdoc}
   */
  public function isWorkflowStatePublished($state_id, WorkflowInterface $workflow) {
    // We rely on being able to inspect the plugin definition. Throw an error if
    // this is not the case.
    if (!$workflow instanceof PluginInspectionInterface) {
      $label = $workflow->getLabel();
      throw new \InvalidArgumentException("The '$label' workflow is not plugin based.");
    }

    // Retrieve the raw plugin definition, as all additional plugin settings
    // are stored there.
    $raw_workflow_definition = $workflow->getPluginDefinition();
    return !empty($raw_workflow_definition['states'][$state_id]['published']);
  }

  /**
   * {@inheritdoc}
   */
  public function isWorkflowStateArchived($state_id, WorkflowInterface $workflow) {
    // We rely on being able to inspect the plugin definition. Throw an error if
    // this is not the case.
    if (!$workflow instanceof PluginInspectionInterface) {
      $label = $workflow->getLabel();
      throw new \InvalidArgumentException("The '$label' workflow is not plugin based.");
    }

    // Retrieve the raw plugin definition, as all additional plugin settings
    // are stored there.
    $raw_workflow_definition = $workflow->getPluginDefinition();
    return !empty($raw_workflow_definition['states'][$state_id]['archived']);
  }

  public function getDefinition($plugin_id, $exception_on_invalid = TRUE)
  {
    // TODO: Implement getDefinition() method.
  }

  public function getDefinitions()
  {
    // TODO: Implement getDefinitions() method.
  }

  public function hasDefinition($plugin_id)
  {
    // TODO: Implement hasDefinition() method.
  }

  public function createInstance($plugin_id, array $configuration = [])
  {
    // TODO: Implement createInstance() method.
  }

  public function getInstance(array $options)
  {
    // TODO: Implement getInstance() method.
  }

  public function getAvailableStates(FieldableEntityInterface $entity, AccountInterface $user = NULL)
  {
    // TODO: Implement getAvailableStates() method.
  }

  public function getAvailableTransitions(FieldableEntityInterface $entity, AccountInterface $user)
  {
    // TODO: Implement getAvailableTransitions() method.
  }

  public static function getEntityStateFieldDefinitions(FieldableEntityInterface $entity)
  {
    // TODO: Implement getEntityStateFieldDefinitions() method.
  }

  public static function getEntityStateFieldDefinition(FieldableEntityInterface $entity)
  {
    // TODO: Implement getEntityStateFieldDefinition() method.
  }

  public function getEntityStateField(FieldableEntityInterface $entity)
  {
    // TODO: Implement getEntityStateField() method.
  }

  public function hasEntityStateField(FieldableEntityInterface $entity)
  {
    // TODO: Implement hasEntityStateField() method.
  }
}
