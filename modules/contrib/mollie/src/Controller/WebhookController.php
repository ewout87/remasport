<?php

namespace Drupal\mollie\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mollie\Events\MollieNotificationEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class WebhookController.
 *
 * @package Drupal\mollie\Controller
 */
class WebhookController extends ControllerBase {

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * RedirectController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event dispatcher.
   */
  public function __construct(
    RequestStack $requestStack,
    EventDispatcherInterface $eventDispatcher
  ) {
    $this->requestStack = $requestStack;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * Updates the status of a payment.
   *
   * @param string $context
   *   The type of context that requested the payment.
   * @param string $context_id
   *   The ID of the context that requested the payment.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Response that redirects to the appropriate place.
   */
  public function invokeHook(string $context, string $context_id): Response {
    // Create an event to dispatch notification handling to sub modules.
    $event = new MollieNotificationEvent(
      $context,
      $context_id,
      $this->requestStack->getCurrentRequest()->get('id')
    );
    // Set the default HTTP code.
    $event->setHttpCode(200);

    // Dispatch event.
    $this->eventDispatcher
      ->dispatch(MollieNotificationEvent::EVENT_NAME, $event);

    return new Response('', $event->getHttpCode());
  }

}
