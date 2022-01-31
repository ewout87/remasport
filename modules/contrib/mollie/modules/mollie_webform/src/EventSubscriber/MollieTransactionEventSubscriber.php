<?php

namespace Drupal\mollie_webform\EventSubscriber;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mollie\Entity\Payment;
use Drupal\mollie\Events\MollieTransactionStatusChangeEvent;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class MollieTransactionEventSubscriber.
 */
class MollieTransactionEventSubscriber implements EventSubscriberInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * MollieTransactionEventSubscriber constructor.
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
    if ($event->getContext() !== 'mollie_webform') {
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

      // Load the webform and save the payment status.
      /** @var \Drupal\webform\WebformSubmissionInterface $submission */
      $submission = $this->entityTypeManager->getStorage('webform_submission')
        ->load($event->getContextId());
      $submission->setElementData(
        $this->getMolliePaymentStatusElementNameFromWebformSubmission($submission),
        $transaction->getStatus()
      );
      $submission->resave();
    } catch (InvalidPluginDefinitionException | PluginNotFoundException | EntityStorageException $e) {
      watchdog_exception('mollie_webform', $e);
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

  /**
   * Returns the machine name of the status element on a webform by submission.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $submission
   *   A submission submitted for the webform.
   *
   * @return string|null
   *   The machine name of the Mollie transaction status element. Or null when
   *   na such element was found on the webform.
   */
  protected function getMolliePaymentStatusElementNameFromWebformSubmission(WebformSubmissionInterface $submission): ?string {
    $elements = $submission->getWebform()->getElementsDecoded();
    foreach ($elements as $name => $element) {
      if ('mollie_payment_status' === $element['#type']) {
        // We assume that there is only one element for the Mollie payment
        // status. If there happen to be more the transactions status is stored
        // in the first one returned by
        // \Drupal\webform\WebformInterface::getElementsDecoded().
        return $name;
      }
    }

    return NULL;
  }

}
