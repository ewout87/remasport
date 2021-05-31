<?php

namespace Drupal\rema_webform\EventSubscriber;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mollie\Events\MollieNotificationEvent;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RemaMailEventSubscriber.
 */
class RemaMailEventSubscriber implements EventSubscriberInterface {

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
      $webform_submission = $this->entityTypeManager->getStorage('webform_submission')
        ->load($event->getContextId());

      if($transaction->getStatus() == 'paid'){
        /** @var \Drupal\webform\Entity\Webform $webform */
        $webform = $webform_submission->getWebform();
        /** @var \Drupal\webform\Plugin\WebformHandler\EmailWebformHandler $handler */
        $handler_ids = [
          'email',
          'client_email'
        ];

        foreach($handler_ids as $handler_id){
          $handler = $webform->getHandler($handler_id);
          $message = $handler->getMessage($webform_submission);
          $handler->sendMessage($webform_submission, $message);
        }
      }
  
    } catch (InvalidPluginDefinitionException | PluginNotFoundException | EntityStorageException $e) {
      watchdog_exception('rema_webform', $e);
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
