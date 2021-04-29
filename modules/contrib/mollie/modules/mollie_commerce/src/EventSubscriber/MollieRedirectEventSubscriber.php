<?php

namespace Drupal\mollie_commerce\EventSubscriber;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\mollie\Events\MollieRedirectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
   * MollieRedirectEventSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
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

    // By default redirect to the return URL in Drupal Commerce.
    $routeNme = 'commerce_payment.checkout.return';
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

      if (!$payment || in_array($payment->getStatus(), ['canceled', 'expired', 'failed'], TRUE)) {
        // For 'failed' statuses redirect to the cancel URL in Drupal Commerce.
        $routeNme = 'commerce_payment.checkout.cancel';
      }
    } catch (PluginNotFoundException | InvalidPluginDefinitionException $e) {
      watchdog_exception('mollie_commerce', $e);
      // Redirect to the cancel URL in Drupal Commerce if we could not load
      // a mollie_payment entity for this event.
      $routeNme = 'commerce_payment.checkout.cancel';
    }

    // Hand over control back to Drupal Commerce.
    $url = Url::fromRoute(
      $routeNme,
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
