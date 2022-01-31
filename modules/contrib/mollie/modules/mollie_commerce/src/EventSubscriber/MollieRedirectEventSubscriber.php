<?php

namespace Drupal\mollie_commerce\EventSubscriber;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\mollie\Events\MollieRedirectEvent;
use Mollie\Api\Types\PaymentStatus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class MollieRedirectEventSubscriber.
 *
 * @package Drupal\mollie_commerce\EventSubscriber
 */
class MollieRedirectEventSubscriber implements EventSubscriberInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * HTTP kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * MollieRedirectEventSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   HTTP kernel.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, HttpKernelInterface $httpKernel) {
    $this->entityTypeManager = $entityTypeManager;
    $this->httpKernel = $httpKernel;
  }

  /**
   * Sets the redirect URL on the event.
   *
   * @param \Drupal\mollie\Events\MollieRedirectEvent $event
   *   Event.
   */
  public function setRedirectUrl(MollieRedirectEvent $event): void {
    // Return if the event context is not this module.
    if ($event->getContext() !== 'mollie_commerce') {
      return;
    }

    // By default, redirect to the return URL in Drupal Commerce.
    $routeName = 'commerce_payment.checkout.return';
    try {
      /** @var \Drupal\mollie\TransactionInterface[] $payments */
      $payments = $this->entityTypeManager->getStorage('mollie_payment')
        ->loadByProperties([
          'context' => $event->getContext(),
          'context_id' => $event->getContextId()
        ]);
      // We can assume only one payment for each combination of context and
      // context ID.
      $payment = reset($payments);

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
          'mollie_transaction_id' => $payment->id(),
          'order_id' => $event->getContextId()
        ]
      );
      try {
        $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
      }
      catch (\Exception $e) {
        watchdog_exception('mollie', $e);
      }

      $failed_statuses = [
        PaymentStatus::STATUS_CANCELED,
        PaymentStatus::STATUS_EXPIRED,
        PaymentStatus::STATUS_FAILED,
      ];
      if (!$payment || in_array($payment->getStatus(), $failed_statuses, TRUE)) {
        // For 'failed' statuses redirect to the cancel URL in Drupal Commerce.
        $routeName = 'commerce_payment.checkout.cancel';
      }
    } catch (PluginNotFoundException | InvalidPluginDefinitionException $e) {
      watchdog_exception('mollie_commerce', $e);
      // Redirect to the cancel URL in Drupal Commerce if we could not load
      // a mollie_payment entity for this event.
      $routeName = 'commerce_payment.checkout.cancel';
    }

    // Hand over control back to Drupal Commerce.
    $url = Url::fromRoute(
      $routeName,
      ['commerce_order' => $event->getContextId(), 'step' => 'payment']
    );

    $event->setRedirectUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      MollieRedirectEvent::EVENT_NAME => 'setRedirectUrl',
    ];
  }

}
