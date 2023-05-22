<?php

namespace Drupal\band_booking_performance;

use Drupal\node\Entity\Node;

/**
 * Provides an interface defining a registration helper.
 */
interface PerformanceHelperInterface {

  /**
   * Send reminder function.
   *
   * @param array $nids
   *   An optional list of nodes.
   * @param integer $contextualTimestamp
   *   The timestamp for a optional specific date.
   * @param int|null $current_date
   *   Optional current date, for dev purpose.
   * @return array
   */
  public function performanceReminder(array $nids, int $contextualTimestamp, int $current_date = null): void;

  /**
   * Get list of reminder to send.
   *
   * @param array $nids
   *   An optional list of nodes.
   * @param int|null $contextualTimestamp
   *   The timestamp for an optional specific date.
   * @param int|null $current_date
   *   Optional current date, for dev purpose.
   * @return array
   *   A list of registrations.
   */
  public function getPerformancesReminders(array $nids, int $contextualTimestamp = null, int $current_date = null): array;

  /**
   * Get list of reminder sort by node id > registration id > user id.
   *
   * @param array $nids
   *   An optional list of nodes.
   * @param int|null $contextualTimestamp
   *   The timestamp for an optional specific date
   * @param int|null $current_date
   *   Optional current date, for dev purpose.
   * @return array
   *   A list of registrations sorted by node.
   */
  public function getPerformancesRemindersSortedByNode(array $nids, int $contextualTimestamp = null, int $current_date = null): array;

  /**
   * TODO improve arg.
   *
   * Operation for performance reminders batch.
   *
   * @param Node $node
   *   The current node.
   * @param array $users
   *   An array of users.
   * @param array $reminders
   *   A list of reminder.
   * @param $operation_details
   *   The operation details.
   * @param $context
   *   The batch context.
   * @return void
   */
  public static function batchPerformanceReminderOperation(Node $node, array $users, array $reminders, $operation_details, &$context): void;

  /**
   * Batch 'finished' callback for performance reminders batch.
   *
   * @param $success
   * @param $results
   * @param $operations
   * @return void
   */
  public static function batchPerformanceReminderFinished($success, $results, $operations): void;

  /**
   * Get default reminder mail object for former content.
   * TODO Should be deleted after import in D9.
   *
   * @return array
   */
  public static function getDefaultReminderMailObject(): array;

  /**
   * Get default reminder mail content for former content.
   * TODO Should be deleted after import in D9.
   *
   * @return array
   */
  public static function getDefaultReminderMailMessage(): array;
}
