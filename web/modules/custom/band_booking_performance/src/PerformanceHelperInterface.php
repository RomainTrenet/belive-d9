<?php

namespace Drupal\band_booking_performance;

use Drupal\band_booking_registration\Entity\Registration;
use Drupal\band_booking_registration\RegistrationInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;

/**
 * Provides an interface defining a registration helper.
 */
interface PerformanceHelperInterface {

  /**
   * Send reminder batch function.
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
   * Batch creation for performance changed.
   *
   * @param Node $performance
   *   The current performance.
   * @param string $object
   *   Object of mail to send.
   * @param string $message
   *   Message of mail to send.
   * @param array $registrationStates
   *   The registration states admitted. 'all' by default.
   * @param bool $manual
   *   Manual action or not, to see batch. True by default.
   *
   * @return void
   */
  public function performanceChanged(Node $performance, string $object, string $message, array $registrationStates = ['all'], bool $manual = true): void;

  /**
   * @param Node $performance
   *   The current performance.
   * @param array $registrationStates
   *   The registration states admitted. 'all' by default.
   * @param bool $getUserId
   *   Get users id instead of registrations.
   * @return array
   *   Array of registrations id.
   */
  public function getPerformanceRegistrationsId(Node $performance, array $registrationStates = ['all'], bool $getUserId = false): array;

  /**
   * @param bool $manual
   *   Manual action or not, to see batch. True by default.
   * @param array $registrations
   *   A list of registration, matching users.
   * @param Node $performance
   *   The performance node.
   * @param array $users
   *   A list of users to which send mail.
   * @param string $module
   *   The name of the module in which find hook_mail.
   * @param string $key
   *   The key used inside hook_mail.
   * @param string $object
   *   Object of mail to send.
   * @param string $message
   *   Message of mail to send.
   * @param $operation_details
   *   The operation details.
   * @param $context
   *   The batch context.
   * @return void
   */
  public static function batchPerformanceChangedOperation(bool $manual, Node $performance, array $registrations, array $users, string $module, string $key, string $object, string $message, $operation_details, &$context) :void;

  /**
   * Default Deleted performance mail object. TODO : set in a config form.
   *
   * @return string
   */
  public static function getDefaultDeletedPerformanceMailObject(): string;

  /**
   * Default Deleted performance mail message. TODO : set in a config form.
   *
   * @return string
   */
  public static function getDefaultDeletedPerformanceMailMessage(): string;

  /**
   * Default performance mail object when date changed. TODO : set in a config form.
   *
   * @return string
   */
  public static function getDefaultDateChangedPerformanceMailObject(): string;

  /**
   * Default performance mail message when date changed. TODO : set in a config form.
   *
   * @return string
   */
  public static function getDefaultDateChangedPerformanceMailMessage(): string;

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
