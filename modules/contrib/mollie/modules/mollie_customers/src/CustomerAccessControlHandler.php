<?php

namespace Drupal\mollie_customers;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Class CustomerAccessControlHandler.
 *
 * @package Drupal\mollie_customers
 */
class CustomerAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view mollie_customer entity');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete mollie_customer entity');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entityBundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'create mollie_customer entity');
  }

}
