<?php

// Not really needed, but could be great to send mail on confirmation change.

namespace Drupal\band_booking_performance\EventSubscriber;

use Drupal\band_booking_performance\WorkflowHelperInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\state_machine\Plugin\Workflow\WorkflowInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowState;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to handle actions on workflow-enabled entities.
 */
class WorkflowTransitionEventSubscriber implements EventSubscriberInterface {

  /**
   * The workflow helper.
   *
   * @var \Drupal\band_booking_performance\WorkflowHelperInterface
   */
  protected $workflowHelper;

  /**
   * Constructs a new WorkflowTransitionEventSubscriber object.
   *
   * @param \Drupal\band_booking_performance\WorkflowHelperInterface $workflowHelper
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
      //'state_machine.pre_transition' => 'handleAction',
    ];
  }

  /**
   * handle action based on the workflow.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The state change event.
   */
  public function handleAction(WorkflowTransitionEvent $event) {
    // If necessary, check which transition group this is.
    $toto = "titi";
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
