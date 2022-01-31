<?php

namespace Drupal\mollie\Events;

use Drupal\mollie\TransactionInterface;

/**
 * Class MollieTransactionEventBase.
 *
 * @package Drupal\mollie\Events
 */
abstract class MollieTransactionEventBase extends MollieEventBase {

  /**
   * The Mollie transaction.
   *
   * @var \Drupal\mollie\TransactionInterface
   */
  protected $transaction;

  /**
   * HTTP status code to return to Mollie.
   *
   * @var int
   */
  protected $httpStatusCode;

  /**
   * MollieTransactionEventBase constructor.
   *
   * @param string $context
   *   Context that initiated the Mollie transaction this event is
   *   triggered for.
   * @param string $contextId
   *   ID of entity within the context corresponding to the Mollie transaction.
   * @param \Drupal\mollie\TransactionInterface $transaction
   *   The Mollie transaction.
   */
  public function __construct(string $context, string $contextId, TransactionInterface $transaction) {
    parent::__construct($context, $contextId);

    $this->transaction = $transaction;
  }

  /**
   * Returns the Mollie transaction for which the event is occurring.
   *
   * @return \Drupal\mollie\TransactionInterface
   *   The Mollie transaction.
   */
  public function getTransaction(): TransactionInterface {
    return $this->transaction;
  }

  /**
   * Sets the HTTP status code.
   *
   * @param int $httpStatusCode
   *   HTTP status code to return to Mollie.
   */
  public function setHttpStatusCode(int $httpStatusCode): void {
    $this->httpStatusCode = $httpStatusCode;
  }

  /**
   * Returns the HTTP status code.
   *
   * @return int
   *   HTTP status code currently set to return to Mollie.
   */
  public function getHttpStatusCode(): int {
    return $this->httpStatusCode;
  }

}
