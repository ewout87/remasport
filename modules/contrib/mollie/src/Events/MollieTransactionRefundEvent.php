<?php

namespace Drupal\mollie\Events;

/**
 * Class MollieTransactionRefundEvent.
 *
 * @package Drupal\mollie\Events
 */
class MollieTransactionRefundEvent extends MollieTransactionEventBase {

  const EVENT_NAME = 'mollie.transaction_event.refund';

}
