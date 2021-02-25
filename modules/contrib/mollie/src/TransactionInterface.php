<?php

namespace Drupal\mollie;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Interface TransactionInterface.
 *
 * @package Drupal\mollie
 */
interface TransactionInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Returns the time the transaction was created.
   *
   * @return string
   *   ISO 8601 representation of the time the transaction was created..
   */
  public function getCreatedTime(): string;

  /**
   * Returns the status of the transaction.
   *
   * @return string
   *   Status of the transaction.
   */
  public function getStatus(): string;

  /**
   * Returns the amount of the transaction.
   *
   * @return float
   *   Amount of the transaction.
   */
  public function getAmount(): float;

  /**
   * Returns the currency of the transaction.
   *
   * @return string
   *   Currency of the transaction.
   */
  public function getCurrency(): string;

  /**
   * Returns the mode the transaction was executed in.
   *
   * @return string
   *   Mode the transaction was executed in.
   */
  public function getMode(): string;

  /**
   * Returns the type of context that requested the transaction.
   *
   * @return string
   *   Type of context that requested the transaction.
   */
  public function getContext(): string;

  /**
   * Returns the ID of the context that requested the transaction.
   *
   * @return string
   *   ID of the context that requested the transaction.
   */
  public function getContextId(): string;

}
