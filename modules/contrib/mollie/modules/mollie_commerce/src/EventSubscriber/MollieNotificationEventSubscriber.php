<?php

namespace Drupal\mollie_commerce\EventSubscriber;

use Drupal\Core\Url;
use Drupal\mollie\Events\MollieNotificationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class MollieNotificationEventSubscriber.
 *
 * @package Drupal\mollie_commerce\EventSubscriber
 */
class MollieNotificationEventSubscriber implements EventSubscriberInterface {

  /**
   * HTTP kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * MollieNotificationEventSubscriber constructor.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   HTTP kernel.
   */
  public function __construct(HttpKernelInterface $httpKernel) {
    $this->httpKernel = $httpKernel;
  }

  /**
   * Updates the status of an order.
   *
   * @param \Drupal\mollie\Events\MollieNotificationEvent $event
   *   Event.
   */
  public function updateOrderStatus(MollieNotificationEvent $event): void {
    // Return if the event context is not this module.
    if ($event->getContext() !== 'mollie_commerce') {
      return;
    }

    // Default HTTP code.
    $httpCode = 200;

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
        'mollie_transaction_id' => $event->getTransactionId(),
        'order_id' => $event->getContextId()
      ]
    );
    try {
      $response = $this->httpKernel
        ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
      $httpCode = $response->getStatusCode();
    }
    catch (\Exception $e) {
      watchdog_exception('mollie', $e);
      $httpCode = 500;
      $event->setHttpCode($httpCode);
    }

    // Set the HTTP code to return to Mollie.
    $event->setHttpCode($httpCode);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      MollieNotificationEvent::EVENT_NAME => 'updateOrderStatus',
    ];
  }

}
