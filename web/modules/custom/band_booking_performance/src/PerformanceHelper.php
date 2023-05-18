<?php

namespace Drupal\band_booking_performance;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\Entity\Node;

/**
 * Service to provide ....
 */
class PerformanceHelper implements PerformanceHelperInterface {

  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * @param EntityTypeManagerInterface $entity_type_manager
   * @param TranslationInterface $string_translation
   * @param MessengerInterface $messenger
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    TranslationInterface $string_translation,
    MessengerInterface $messenger
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public function sendReminder(array $nids = [], int $contextualTimestamp = null): array {
    $infos = [];
    // TODO rename function to get list of ...
    // Or add a function to manage list.

    // TODO : there is a offset between the date entered inside the BO and the date appearing : 2h as the gap between UTC and france.
    $node = Node::load(5);
    $field_relaunch = $node->get('field_relaunch');
    $field_date = $node->get('field_date')->getValue();
    $field_date_non_utc = $node->get('field_date_non_utc')->getValue();

    // If nodes are not specified.
    if (empty($nids)) {
      $nids = $this->getListOfReminder($contextualTimestamp);
    }

    // TODO Ensure date of performance is not passed.

    //$query->condition('roles', $allowed_roles, 'IN');
    /*
    if (!empty($registeredUsersId)) {
      $query->condition('uid', $registeredUsersId, 'NOT IN');
    }
    */

    // get nodes and load them.

    // From nodes, get users

    // Foreach nodes, get users and record them.
    // Prepare array node id => users id and record needed informations.

    return [];
  }

  public function getListOfReminder(int $contextualTimestamp = null): array {
    /*
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', 'performance');

    // Ensure event is coming.
    $date = time();

    // TODO improve.
    // Won't work fot the moment because of wrong UTC time (-2h) coming from
    // node.
    // $min_day = date('Y-m-d 00:00:00', $date);
    // So the trick is -1 day.
    $min_day = date('Y-m-d 00:00:00', strtotime('-1 day', $date));
    $query->condition('field_date', $min_day, '>=');

    // If date is specified, get nodes for the date of reminder; othewise get
    // every nid after current day.
    $date = $contextualTimestamp ?? time();
    $day = date('Y-m-d', $date);
    $query->condition('field_relaunch.value', $day, '=');
    //return $query->execute();
    */

    // Other solution, with database().
    $connection = \Drupal::database();
    $query2 = $connection->select('node', 'n');
    //n.title.

    // Ensure type is performance.
    $query2->where('n.type = :type', [
      ':type' => 'performance',
    ]);

    // Ensure status is 1.
    $query2->leftjoin('node_field_data', 'd', 'd.nid = n.nid');
    $query2->where('d.status = 1');

    // Only node with relaunch for the date.
    $day = date('Y-m-d', $contextualTimestamp ?? time());
    $query2->leftjoin('node__field_relaunch', 'r', 'r.entity_id = n.nid');
    $query2->where('r.field_relaunch_value = :day', [
      ':day' => $day,
    ]);

    // Ensure event is coming.
    $current_date = time();
    // TODO remove, this is done to simulate a former date.
    $current_date = strtotime("-1 day");// -> relances d'hier.
    // Won't work fot the moment because of wrong UTC time (-2h) coming from
    // node.
    $min_day = date('Y-m-d', $current_date);
    // So the trick is -1 day.
    //$min_day = date('Y-m-d 00:00:00', strtotime('-1 day', $current_date));

    $query2->leftjoin('node__field_date_non_utc', 'dt', 'dt.entity_id = n.nid');
    $query2->where('dt.field_date_non_utc_value >= :min_day', [
      ':min_day' => $min_day,
    ]);

    // Get necessary fields.
    $query2->fields('n', ['nid']);

    return $query2->execute()->fetchAllKeyed(0, 0);
  }

}
