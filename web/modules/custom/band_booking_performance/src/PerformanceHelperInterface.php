<?php

namespace Drupal\band_booking_performance;

/**
 * Provides an interface defining a registration helper.
 */
interface PerformanceHelperInterface {

  /**
   * TODO
   * @param array $nids
   *   An optional list of nodes.
   * @param integer $contextualTimestamp
   *   The timestamp for a optional specific date
   * @return array
   */
  public function sendReminder(array $nids, int $contextualTimestamp): array;

  public function getListOfReminder(int $contextualTimestamp = null): array;
}
