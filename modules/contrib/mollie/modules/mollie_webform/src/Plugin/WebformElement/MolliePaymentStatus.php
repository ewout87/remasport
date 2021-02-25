<?php

namespace Drupal\mollie_webform\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElement\Value;

/**
 * Provides a 'mollie_payment_status' element.
 *
 * We define a custom element so that we can identify the element in the webform
 * independent of the (machine) name it gets in the form.
 *
 * @WebformElement(
 *   id = "mollie_payment_status",
 *   label = @Translation("Mollie payment status"),
 *   description = @Translation("Element to store the status of a Mollie payment. Should be used in combination with the Mollie payment handler."),
 *   category = @Translation("Mollie"),
 * )
 */
class MolliePaymentStatus extends Value {

}
