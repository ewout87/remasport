<?php

namespace Drupal\mollie\Events;

/**
 * Class MollieTransactionStatusChangeEvent.
 *
 * @package Drupal\mollie\Events
 */
class MollieTransactionStatusChangeEvent extends MollieTransactionEventBase {

  const EVENT_NAME = 'mollie.transaction_event.status_change';

}
