<?php

namespace Drupal\mollie\Entity\Query;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\PaymentCollection;

/**
 * Class PaymentQuery.
 *
 * @package Drupal\mollie\Entity\Query
 */
class PaymentQuery extends TransactionQueryBase {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    if ($this->count) {
      return $this->getPaymentsFromMollie()->count();
    }

    return $this->getPaymentIds();
  }

  /**
   * Returns the IDs of the payments for the configured Mollie account.
   *
   * @return array
   *   Array with IDs of the payments for the configured Mollie account.
   */
  protected function getPaymentIds(): array {
    $paymentIds = [];

    $payments = $this->getPaymentsFromMollie();
    foreach ($payments as $payment) {
      /** @var \Mollie\Api\Resources\Payment $payment */
      $paymentIds[$payment->id] = $payment->id;
    }

    return $paymentIds;
  }

  /**
   * Returns the payments for the configured Mollie account.
   *
   * @return \Mollie\Api\Resources\PaymentCollection
   *
   * TODO: Add paging, sorting and parameters.
   * TODO: Only return payments created by this module.
   */
  protected function getPaymentsFromMollie(): PaymentCollection {
    try {
      return $this->mollieApiClient->payments->page();
    }
    catch (ApiException $e) {
      watchdog_exception('mollie', $e);
    }

    return new PaymentCollection($this->mollieApiClient, 0, []);
  }

}
