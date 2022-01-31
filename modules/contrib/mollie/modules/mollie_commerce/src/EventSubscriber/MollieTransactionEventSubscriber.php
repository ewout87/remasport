<?php

namespace Drupal\mollie_commerce\EventSubscriber;

use Drupal\Core\Url;
use Drupal\mollie\Entity\Payment;
use Drupal\mollie\Events\MollieTransactionStatusChangeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class MollieTransactionEventSubscriber.
 *
 * @package Drupal\mollie_commerce\EventSubscriber
 */
class MollieTransactionEventSubscriber implements EventSubscriberInterface {

  /**
   * HTTP kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * MollieTransactionEventSubscriber constructor.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   HTTP kernel.
   */
  public function __construct(HttpKernelInterface $httpKernel) {
    $this->httpKernel = $httpKernel;
  }

  /**
   * Updates the status of the order when the status of a payment has changed.
   *
   * @param \Drupal\mollie\Events\MollieTransactionStatusChangeEvent $event
   *   Event.
   */
  public function updateOrderStatus(MollieTransactionStatusChangeEvent $event): void {
    // Return if the event context is not this module.
    if ($event->getContext() !== 'mollie_commerce') {
      return;
    }

    // Return if the transaction is not a payment.
    if (!($event->getTransaction() instanceof Payment)) {
      return;
    }

    // We do a sub request to Drupal Commerce to handle payment and order
    // status updates there.
    $url = Url::fromRoute(
      'commerce_payment.notify',
      [
        'commerce_payment_gateway' => 'mollie',
      ]
    );
    $subRequest = Request::create(
      $url->toString(),
      'POST',
      [
        'mollie_transaction_id' => $event->getTransaction()->id(),
        'order_id' => $event->getContextId()
      ]
    );
    try {
      $response = $this->httpKernel
        ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
      $httpStatusCode = $response->getStatusCode();
    }
    catch (\Exception $e) {
      watchdog_exception('mollie', $e);
      $httpStatusCode = 500;
      $event->setHttpStatusCode($httpStatusCode);
    }

    // Set the HTTP code to return to Mollie.
    $event->setHttpStatusCode($httpStatusCode);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      MollieTransactionStatusChangeEvent::EVENT_NAME => 'updateOrderStatus',
    ];
  }

}
