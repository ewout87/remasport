<?php

namespace Drupal\mollie_commerce\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\commerce_price\Price;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Mollie.
 *
 * @package Drupal\commerce_mollie\Plugin\Commerce\PaymentGateway
 *
 * @CommercePaymentGateway(
 *   id = "mollie",
 *   label = @Translation("Mollie"),
 *   display_label = @Translation("Mollie"),
 *   modes = {
 *     "mollie" = @Translation("Determined by Mollie for Drupal configuration"),
 *   },
 *   forms = {
 *     "offsite-payment" = "Drupal\mollie_commerce\PluginForm\OffsiteRedirect\MolliePaymentOffsiteForm",
 *   },
 * )
 */
class Mollie extends OffsitePaymentGatewayBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $formState) {
    $form = parent::buildConfigurationForm($form, $formState);

    $message = $this->t('Mollie is configured in the Mollie for Drupal module.');
    $form['mollie_message'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $message,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function onNotify(Request $request) {
    try {
      // Fetch the Mollie transaction.
      /** @var \Drupal\mollie\Entity\Payment $transaction */
      $transaction = $this->entityTypeManager->getStorage('mollie_payment')
        ->load($request->get('mollie_transaction_id'));

      // Fetch the Commerce order.
      /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
      $order = $this->entityTypeManager->getStorage('commerce_order')
        ->load($request->get('order_id'));

      // Fetch the Commerce payment corresponding to the Mollie transaction.
      $payments = $this->entityTypeManager->getStorage('commerce_payment')
        ->loadByProperties([
          'order_id' => $request->get('order_id'),
          'remote_id' => $request->get('mollie_transaction_id'),
        ]);
      // We assume only one Commerce payment per Mollie transaction per order.
      $payment = reset($payments);

      if (!$payment) {
        // Create a new Commerce payment if none exists yet.
        /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
        $payment = $this->entityTypeManager->getStorage('commerce_payment')
          ->create([
            'amount' => new Price((string) $transaction->getAmount(), $transaction->getCurrency()),
            'payment_gateway' => $this->parentEntity->id(),
            'order_id' => $order->id(),
            'test' => $this->getMode() === 'test',
            'remote_id' => $transaction->id(),
          ]);
      }

      // Update payment status.
      $payment->setRemoteState($transaction->getStatus());
      $payment->setState($this->getStateByMollieTransactionStatus(
        $transaction->getStatus()
      ));

      // Save the payment.
      $payment->save();
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException | EntityStorageException $e) {
      watchdog_exception('mollie_commerce', $e);
    }
  }

  /**
   * Returns the Commerce payment state corresponding to a Mollie status.
   *
   * @param string $mollieStatus
   *   Status of the Mollie transaction.
   *
   * @return string
   *   Corresponding state for the Commerce payment.
   */
  protected function getStateByMollieTransactionStatus(string $mollieStatus): string {
    switch ($mollieStatus) {
      case 'pending':
        return 'pending';

      case 'authorized':
        return 'authorization';

      case 'paid':
        return 'completed';

      case 'canceled':
      case 'expired':
      case 'failed':
        return 'voided';

      default:
        return 'new';
    }
  }

}
