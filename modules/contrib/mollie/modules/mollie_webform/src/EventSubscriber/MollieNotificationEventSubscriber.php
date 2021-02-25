<?php

namespace Drupal\mollie_webform\EventSubscriber;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mollie\Events\MollieNotificationEvent;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class MollieNotificationEventSubscriber.
 */
class MollieNotificationEventSubscriber implements EventSubscriberInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

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
    if ($event->getContext() !== 'mollie_webform') {
      return;
    }

    // Default HTTP code.
    $httpCode = 200;

    // Fetch the transaction.
    try {
      /** @var \Drupal\mollie\Entity\Payment $transaction */
      $transaction = $this->entityTypeManager->getStorage('mollie_payment')
        ->load($event->getTransactionId());

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
      if ($element['#type'] === 'mollie_payment_status') {
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
