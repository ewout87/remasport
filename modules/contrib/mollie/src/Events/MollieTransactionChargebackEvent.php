<?php

namespace Drupal\mollie\Events;

/**
 * Class MollieTransactionChargebackEvent.
 *
 * @package Drupal\mollie\Events
 */
class MollieTransactionChargebackEvent extends MollieTransactionEventBase {

  const EVENT_NAME = 'mollie.transaction_event.chargeback';

}
