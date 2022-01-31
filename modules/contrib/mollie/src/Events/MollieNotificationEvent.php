<?php

namespace Drupal\mollie\Events;

/**
 * Class MollieNotificationEvent.
 *
 * @package Drupal\mollie\Events
 *
 * @deprecated Deprecated as of Mollie for Drupal 2.1.0 and will be removed in
 *   Mollie for Drupal 3.0.0. Use MollieTransactionStatusChangeEvent instead.
 */
class MollieNotificationEvent extends MollieTransactionStatusChangeEvent {

  const EVENT_NAME = 'mollie.notification_event';

  /**
   * Event types.
   */
  const STATUS_CHANGE_EVENT = 'status_change';
  const REFUND_EVENT = 'refund';
  const CHARGEBACK_EVENT = 'chargeback';

  /**
   * ID of Mollie transaction.
   *
   * @var string
   */
  protected $transactionId;

  /**
   * The type of event occurring.
   *
   * @var string
   */
  protected $eventType;

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
   * @param string $eventType
   *   The type of event occurring.
   */
  public function __construct(string $context, string $contextId, string $transactionId, string $eventType) {
    /** @var \Drupal\mollie\TransactionInterface $transaction */
    $transaction = \Drupal::entityTypeManager()->getStorage('mollie_payment')->load($transactionId);
    parent::__construct($context, $contextId, $transaction);

    $this->transactionId = $transactionId;
    $this->eventType = $eventType;
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

  /**
   * Returns the type of event occurring.
   *
   * @return string
   *   The type of event occurring.
   */
  public function getEventType(): string {
    return $this->eventType;
  }

  /**
   * Sets the HTTP status code.
   *
   * @param int $httpCode
   *   HTTP status code to return.
   *
   * @deprecated Deprecated as of Mollie for Drupal 2.1.0 and will be removed in
   *   Mollie for Drupal 3.0.0. Use setHttpStatusCode() instead.
   */
  public function setHttpCode(int $httpCode): void {
    parent::setHttpStatusCode($httpCode);
  }

  /**
   * Returns the HTTP status code.
   *
   * @return int
   *   HTTP status code to return.
   *
   * @deprecated Deprecated as of Mollie for Drupal 2.1.0 and will be removed in
   *   Mollie for Drupal 3.0.0. Use getHttpStatusCode() instead.
   */
  public function getHttpCode(): int {
    return parent::getHttpStatusCode();
  }

}
