<?php

namespace Drupal\mollie\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\mollie\Events\MollieRedirectEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class RedirectController.
 *
 * @package Drupal\mollie\Controller
 */
class RedirectController extends ControllerBase {

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * RedirectController constructor.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event dispatcher.
   */
  public function __construct(EventDispatcherInterface $eventDispatcher) {
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_dispatcher')
    );
  }

  /**
   * Redirects the visitor to the appropriate place after payment.
   *
   * @param string $context
   *   The type of context that requested the payment.
   * @param string $context_id
   *   The ID of the context that requested the payment.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Response that redirects to the appropriate place.
   */
  public function paymentRedirect(string $context, string $context_id): RedirectResponse {
    // Redirect to the payment collection by default.
    $url = Url::fromRoute('entity.mollie_payment.collection');

    $event = new MollieRedirectEvent($context, $context_id);
    // Set the default redirect URL.
    $event->setRedirectUrl($url);

    // Dispatch event.
    $this->eventDispatcher->dispatch(MollieRedirectEvent::EVENT_NAME, $event);

    return new RedirectResponse($event->getRedirectUrl()->toString());
  }

}
