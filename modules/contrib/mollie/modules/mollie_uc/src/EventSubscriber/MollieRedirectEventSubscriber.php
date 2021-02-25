<?php

namespace Drupal\mollie_uc\EventSubscriber;

use Drupal\Core\Url;
use Drupal\mollie\Events\MollieRedirectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class MollieRedirectEventSubscriber.
 *
 * @package Drupal\mollie_uc\EventSubscriber
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
    if ($event->getContext() !== 'mollie_uc') {
      return;
    }

    // Redirect to the complete sale page.
    $url = Url::fromRoute(
      'mollie_uc.complete_sale',
      ['order' => $event->getContextId()]
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
