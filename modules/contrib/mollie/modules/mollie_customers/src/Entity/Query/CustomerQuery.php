<?php

namespace Drupal\mollie_customers\Entity\Query;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\CustomerCollection;

/**
 * Class CustomerQuery.
 *
 * @package Drupal\mollie_customers\Entity\Query
 */
class CustomerQuery extends CustomerQueryBase {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    if ($this->count) {
      return $this->getCustomersFromMollie()->count();
    }

    return $this->getCustomerIds();
  }

  /**
   * Returns the IDs of the customers for the configured Mollie account.
   *
   * @return array
   *   Array with IDs of the customers for the configured Mollie account.
   */
  protected function getCustomerIds(): array {
    $customerIds = [];

    $customers = $this->getCustomersFromMollie();
    foreach ($customers as $customer) {
      /** @var \Mollie\Api\Resources\Customer $customer */
      $customerIds[$customer->id] = $customer->id;
    }

    return $customerIds;
  }

  /**
   * Returns the customers for the configured Mollie account.
   *
   * @return \Mollie\Api\Resources\CustomerCollection
   *
   * TODO: Add paging, sorting and parameters.
   * TODO: Optionally only return customers created by this module.
   */
  protected function getCustomersFromMollie(): CustomerCollection {
    try {
      return $this->mollieApiClient->customers->page();
    }
    catch (ApiException $e) {
      watchdog_exception('mollie', $e);
    }

    $_links = new \stdClass();
    return new CustomerCollection($this->mollieApiClient, 0, $_links);
  }

}
