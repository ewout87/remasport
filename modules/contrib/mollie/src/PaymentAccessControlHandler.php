<?php

namespace Drupal\mollie;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Class PaymentAccessControlHandler.
 *
 * @package Drupal\mollie
 */
class PaymentAccessControlHandler extends TransactionAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entityBundle = NULL) {
    if ($account->hasPermission('create mollie_payment entities')) {
      return AccessResult::allowed();
    }

    return parent::checkCreateAccess($account, $context, $entityBundle);
  }

}
