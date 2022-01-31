<?php

namespace Drupal\mollie_customers;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Interface CustomerInterface.
 *
 * @package Drupal\mollie_customers
 */
interface CustomerInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Returns the time at which the customer was created.
   *
   * @return string
   *   The ISO 8601 representation of the time the customer was created.
   */
  public function getCreatedTime(): string;

  /**
   * Returns full name of the customer as provided when customer was created.
   *
   * @return string|null
   *   The name of the customer or null if the name is not set
   */
  public function getName(): ?string;

  /**
   * Returns email address of customer as provided when customer was created.
   *
   * @return string|null
   *   The email of the customer or null if the email address is not set.
   */
  public function getEmail(): ?string;

  /**
   * Returns the mode used to create this customer.
   *
   * @return string
   *   The mode the customer was created in.
   */
  public function getMode(): string;

}
