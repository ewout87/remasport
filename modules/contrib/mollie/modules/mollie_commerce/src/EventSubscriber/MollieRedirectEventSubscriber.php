<?php

namespace Drupal\mollie_commerce\EventSubscriber;

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

    // Hand over control back to Drupal Commerce.
    $url = Url::fromRoute(
      'commerce_payment.checkout.return',
      [
        'commerce_order' => $event->getContextId(),
        'step' => 'payment',
      ]
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
