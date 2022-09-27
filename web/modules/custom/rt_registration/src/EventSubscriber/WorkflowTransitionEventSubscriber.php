<?php

namespace Drupal\rt_registration\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\rt_registration\WorkflowHelperInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\state_machine\Plugin\Workflow\WorkflowInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowState;
//use Drupal\state_machine_workflow\RevisionManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to handle actions on workflow-enabled entities.
 */
class WorkflowTransitionEventSubscriber implements EventSubscriberInterface {

  /**
   * The workflow helper.
   *
   * @var \Drupal\rt_registration\WorkflowHelperInterface
   */
  protected $workflowHelper;

  /**
   * Constructs a new WorkflowTransitionEventSubscriber object.
   *
   * @param \Drupal\rt_registration\WorkflowHelperInterface $workflowHelper
   *   The workflow helper.
   */
  public function __construct(WorkflowHelperInterface $workflowHelper) {
    $this->workflowHelper = $workflowHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'state_machine.pre_transition' => 'handleAction',
    ];
  }

  /**
   * handle action based on the workflow.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The state change event.
   */
  public function handleAction(WorkflowTransitionEvent $event) {
    $entity = $event->getEntity();

    // Verify if the new state is marked as published state.
    $is_published_state = $this->isPublishedState($event->getToState(), $event->getWorkflow());

    if ($entity instanceof EntityPublishedInterface) {
      if ($is_published_state) {
        $entity->setPublished();
      }
      else {
        $entity->setUnpublished();
      }

    }
  }

  /**
   * Checks if a state is set as published in a certain workflow.
   *
   * @param \Drupal\state_machine\Plugin\Workflow\WorkflowState $state
   *   The state to check.
   * @param \Drupal\state_machine\Plugin\Workflow\WorkflowInterface $workflow
   *   The workflow the state belongs to.
   *
   * @return bool
   *   TRUE if the state is set as published in the workflow, FALSE otherwise.
   */
  protected function isPublishedState(WorkflowState $state, WorkflowInterface $workflow) {
    return $this->workflowHelper->isWorkflowStatePublished($state->getId(), $workflow);
  }

}
