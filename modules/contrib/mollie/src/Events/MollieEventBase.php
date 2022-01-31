<?php

namespace Drupal\mollie\Events;

/**
 * TODO: Change to \Symfony\Contracts\EventDispatcher\Event once Drupal 8 is
 *   EOL.
 */
use Symfony\Component\EventDispatcher\Event;

/**
 * Class MollieEventBase.
 *
 * @package Drupal\mollie\Events
 */
abstract class MollieEventBase extends Event {

  /**
   * Context that initiated the Mollie transaction this event is triggered for.
   *
   * @var string
   */
  protected $context;

  /**
   * ID of entity within the context corresponding to the Mollie transaction.
   *
   * @var string
   */
  protected $contextId;

  /**
   * MollieEventBase constructor.
   *
   * @param string $context
   *   Context that initiated the Mollie transaction this event is
   *   triggered for.
   * @param string $contextId
   *   ID of entity within the context corresponding to the Mollie transaction.
   */
  public function __construct(string $context, string $contextId) {
    $this->context = $context;
    $this->contextId = $contextId;
  }

  /**
   * Returns the context for this event.
   *
   * @return string
   *   Context that initiated the Mollie transaction this event is
   *   triggered for.
   */
  public function getContext(): string {
    return $this->context;
  }

  /**
   * Returns the context ID for this event.
   *
   * @return string
   *   ID of entity within the context corresponding to the Mollie transaction.
   */
  public function getContextId(): string {
    return $this->contextId;
  }

}
