<?php

namespace Drupal\band_booking_registration;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the registration entity type.
 */
class RegistrationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  //protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      // TODO refuse access if event date has expired.
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view registration');

      case 'update':
        // As we can't get field value from entity, we store entity as registration.
        /** @var RegistrationInterface $registration */
        $registration = $entity;
        $registration_user_id = $registration->get('registration_user_id')->getValue();
        $registration_user_id = $registration_user_id[0]['target_id'] ?? null;

        // Check if account has permission, or if account is user registered.
        return AccessResult::allowedIf(
          (
            $account->hasPermission('edit registration') ||
            $account->hasPermission('administer registration')
          ) ||
          $registration_user_id == $account->id()
        );

      case 'delete':
        return AccessResult::allowedIfHasPermissions(
          $account,
          ['delete registration', 'administer registration'],
          'OR',
        );

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions(
      $account,
      ['create registration', 'administer registration'],
      'OR',
    );
  }

}
