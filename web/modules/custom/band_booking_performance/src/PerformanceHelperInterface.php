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
   * @param bool $manual
   *   Wether this is a manual call or automatic.
   * @param bool $force
   *   Ignore reminder date, can be used with $startFromContextualTs. It shunts $nids.
   * @param array $nids
   *   An optional list of nodes.
   * @param bool $startFromContextualTs
   *   Get every node starting from contextual timestamp, or just for the day.
   * @param int|null $contextualTimestamp
   *   The timestamp for an optional specific date.
   * @param int|null $current_date
   *   Optional current date, for dev purpose.
   *
   * @return void
   */
  public function performanceReminder(bool $manual = false, bool $force = false, array $nids = [], bool $startFromContextualTs = false, int $contextualTimestamp = null, int $current_date = null): void;

  /**
   * TODO : improve description.
   * Get list of reminder to send. If nids are specified, it shunts the
   * contextual timestamp. If $startFromContextualTs is set to true, it takes
   * every node starting from this day. Otherwise, it only takes into account
   * nodes for the day. If no contextual timestamp is given, it takes the
   * current day instead.
   * $force avoid using $nids option and get every node ignoring relaunch day.
   * Only if node is published, event is not canceled, event is coming.
   *
   * @param bool $force
   *   Ignore reminder date, can be used with $startFromContextualTs. It shunts $nids.
   * @param array $nids
   *   An optional list of nodes.
   * @param bool $startFromContextualTs
   *   Get every node starting from contextual timestamp, or just for the day.
   * @param int|null $contextualTimestamp
   *   The timestamp for an optional specific date.
   * @param int|null $current_date
   *   Optional current date, for dev purpose.
   *
   * @return array
   *   A list of registrations.
   */
  public function getPerformancesReminders(bool $force = false, array $nids = [], bool $startFromContextualTs = false, int $contextualTimestamp = null, int $current_date = null): array;

  /**
   * Get list of reminder sort by node id > registration id > user id.
   *
   * @param bool $force
   *   Ignore reminder date, can be used with $startFromContextualTs. It shunts $nids.
   * @param array $nids
   *   An optional list of nodes.
   * @param bool $startFromContextualTs
   *   Get every node starting from contextual timestamp, or just for the day.
   * @param int|null $contextualTimestamp
   *   The timestamp for an optional specific date.
   * @param int|null $current_date
   *   Optional current date, for dev purpose.
   *
   * @return array
   *   A list of registrations sorted by node.
   */
  public function getPerformancesRemindersSortedByNode(bool $force = false, array $nids = [], bool $startFromContextualTs = false, int $contextualTimestamp = null, int $current_date = null): array;

  /**
   * TODO improve arg.
   *
   * Operation for performance reminders batch.
   *
   * @param bool $manual
   *   If this is a manual call or automatic.
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
  public static function batchPerformanceReminderOperation(bool $manual, Node $node, array $users, array $reminders, $operation_details, &$context): void;

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
   * @return string
   */
  public static function getDefaultReminderMailObject(): string;

  /**
   * Get default reminder mail content for former content.
   * TODO Should be deleted after import in D9.
   *
   * @return string
   */
  public static function getDefaultReminderMailMessage(): string;
}
