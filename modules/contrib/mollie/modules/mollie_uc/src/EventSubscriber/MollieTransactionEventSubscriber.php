<?php

namespace Drupal\mollie_uc\EventSubscriber;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mollie\Entity\Payment;
use Drupal\mollie\Events\MollieTransactionStatusChangeEvent;
use Drupal\uc_payment\Entity\PaymentReceipt;
use Mollie\Api\Types\PaymentStatus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class MollieTransactionEventSubscriber.
 *
 * @package Drupal\mollie_uc\EventSubscriber
 */
class MollieTransactionEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * MollieTransactionNotificationSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Updates the status of the order when the status of a payment has changed.
   *
   * @param \Drupal\mollie\Events\MollieTransactionStatusChangeEvent $event
   *   Event.
   */
  public function updateOrderStatus(MollieTransactionStatusChangeEvent $event): void {
    // Return if the event context is not this module.
    if ($event->getContext() !== 'mollie_uc') {
      return;
    }

    // Default HTTP status code.
    $httpStatusCode = 200;

    // Fetch the transaction.
    try {
      // Get the transaction and check whether it is a payment.
      $transaction = $event->getTransaction();
      if (!($transaction instanceof Payment)) {
        return;
      }

      switch ($transaction->getStatus()) {
        // For authorized or paid transactions we need to register a payment.
        case PaymentStatus::STATUS_PAID:
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
        case PaymentStatus::STATUS_CANCELED:
        case PaymentStatus::STATUS_EXPIRED:
        case PaymentStatus::STATUS_FAILED:
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
      $httpStatusCode = 500;
    }

    // Set the HTTP status code to return to Mollie.
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
