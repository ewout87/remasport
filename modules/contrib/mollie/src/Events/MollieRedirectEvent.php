<?php

namespace Drupal\mollie\Events;

use Drupal\Core\Url;

/**
 * Class MollieRedirectEvent.
 *
 * @package Drupal\mollie\Events
 */
class MollieRedirectEvent extends MollieEventBase {

  const EVENT_NAME = 'mollie.redirect_event';

  /**
   * URL to redirect to.
   *
   * @var \Drupal\Core\Url
   */
  protected $redirectUrl;

  /**
   * Set the URL to redirect to.
   *
   * @param \Drupal\Core\Url $redirectUrl
   *   URL to redirect to.
   */
  public function setRedirectUrl(Url $redirectUrl): void {
    $this->redirectUrl = $redirectUrl;
  }

  /**
   * Returns the URL to redirect to.
   *
   * @return \Drupal\Core\Url
   *   URL to redirect to.
   */
  public function getRedirectUrl(): Url {
    return $this->redirectUrl;
  }

}
