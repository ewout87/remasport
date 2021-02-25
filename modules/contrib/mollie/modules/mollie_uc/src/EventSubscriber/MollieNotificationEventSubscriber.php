<?php

namespace Drupal\mollie_uc\EventSubscriber;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mollie\Events\MollieNotificationEvent;
use Drupal\uc_payment\Entity\PaymentReceipt;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class MollieNotificationEventSubscriber.
 *
 * @package Drupal\mollie_uc\EventSubscriber
 */
class MollieNotificationEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * MollieNotificationEventSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Updates the status of an order.
   *
   * @param \Drupal\mollie\Events\MollieNotificationEvent $event
   *   Event.
   */
  public function updateOrderStatus(MollieNotificationEvent $event): void {
    // Return if the event context is not this module.
    if ($event->getContext() !== 'mollie_uc') {
      return;
    }

    // Default HTTP code.
    $httpCode = 200;

    // Fetch the transaction.
    try {
      /** @var \Drupal\mollie\Entity\Payment $transaction */
      $transaction = $this->entityTypeManager->getStorage('mollie_payment')
        ->load($event->getTransactionId());

      switch ($transaction->getStatus()) {
        // For authorized or paid transactions we need to register a payment.
        // TODO: Check if paid can be registered after authorized.
        case 'authorized':
        case 'paid':
          // Load the order.
          /** @var \Drupal\uc_order\Entity\Order $order */
          $order = $this->entityTypeManager->getStorage('uc_order')
            ->load($event->getContextId());

          // Create a new Ubercart payment.
          $payment = PaymentReceipt::create([
            'order_id' => $event->getContextId(),
            'method' => 'mollie',
            'amount' => $order->getTotal(),
            'currency' => $order->getCurrency(),
          ]);
          $payment->save();
          break;

        // Register that the transaction was not successful.
        case 'canceled':
        case 'expired':
        case 'failed':
          uc_order_comment_save(
            $event->getContextId(),
            0,
            $this->t(
              'Payment @status in Mollie.',
              ['@status' => $transaction->getStatus()]
            ),
            'order'
          );
          break;

        // Do nothing for other statuses.
        default:
          break;
      }
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException | EntityStorageException $e) {
      watchdog_exception('mollie_uc', $e);
      $httpCode = 500;
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
