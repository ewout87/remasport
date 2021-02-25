<?php

namespace Drupal\mollie\Events;

/**
 * Class MollieNotificationEvent.
 *
 * @package Drupal\mollie\Events
 */
class MollieNotificationEvent extends MollieEventBase {

  const EVENT_NAME = 'mollie.notification_event';

  /**
   * HTTP status code to return.
   *
   * @var int
   */
  protected $httpCode;

  /**
   * ID of Mollie transaction.
   *
   * @var string
   */
  protected $transactionId;

  /**
   * MollieNotificationEvent constructor.
   *
   * @param string $context
   *   Context that initiated the Mollie transaction this event is
   *   triggered for.
   * @param string $contextId
   *   ID of entity within the context corresponding to the Mollie transaction.
   * @param string $transactionId
   *   ID of Mollie transaction.
   */
  public function __construct(string $context, string $contextId, string $transactionId) {
    parent::__construct($context, $contextId);

    $this->transactionId = $transactionId;
  }

  /**
   * Sets the HTTP status code.
   *
   * @param int $httpCode
   *   HTTP status code to return.
   */
  public function setHttpCode(int $httpCode): void {
    $this->httpCode = $httpCode;
  }

  /**
   * Returns the HTTP status code.
   *
   * @return int
   *   HTTP status code to return.
   */
  public function getHttpCode(): int {
    return $this->httpCode;
  }

  /**
   * Returns the ID of the Mollie transaction.
   *
   * @return string
   *   ID of Mollie transaction.
   */
  public function getTransactionId(): string {
    return $this->transactionId;
  }

}
