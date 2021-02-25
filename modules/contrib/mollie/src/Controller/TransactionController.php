<?php

namespace Drupal\mollie\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\mollie\Entity\Payment;

/**
 * Class TransactionController.
 *
 * @package Drupal\mollie\Controller
 */
class TransactionController extends ControllerBase {

  /**
   * Returns the title for a payment.
   *
   * @param \Drupal\mollie\Entity\Payment $mollie_payment
   *   Payment.
   *
   * @return string
   *   Title for the payment.
   */
  public function paymentTitle(Payment $mollie_payment): string {
    return $this->transactionTitle($mollie_payment);
  }

  /**
   * Returns the title for an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   *
   * @return string
   *   Title for the entity.
   */
  protected function transactionTitle(EntityInterface $entity): string {
    return $this->t(
      '@type @label',
      [
        '@type' => $entity->getEntityType()->getLabel(),
        '@label' => $entity->label(),
      ]
    );
  }

}
