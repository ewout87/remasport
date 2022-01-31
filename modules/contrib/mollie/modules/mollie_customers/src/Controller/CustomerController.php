<?php

namespace Drupal\mollie_customers\Controller;

use Drupal\mollie\Controller\MollieEntityBaseController;
use Drupal\mollie_customers\CustomerInterface;

/**
 * Class CustomerController.
 *
 * @package Drupal\mollie_customers\Controller
 */
class CustomerController extends MollieEntityBaseController {

  /**
   * Returns the title for a customer entity.
   *
   * @param \Drupal\mollie_customers\CustomerInterface $mollie_customer
   *   Customer entity.
   *
   * @return string
   *   Title for the customer entity.
   */
  public function customerTitle(CustomerInterface $mollie_customer): string {
    return $this->entityTitle($mollie_customer);
  }

}
