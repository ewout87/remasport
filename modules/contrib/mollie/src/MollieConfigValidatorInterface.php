<?php

namespace Drupal\mollie;

/**
 * Interface MollieConfigValidatorInterface.
 *
 * @package Drupal\mollie
 */
interface MollieConfigValidatorInterface {

  /**
   * Determines whether a live API key is configured.
   *
   * @return bool
   *   True if a live API key is configured, false otherwise.
   */
  public function hasLiveApiKey(): bool;

  /**
   * Determines whether a test API key is configured.
   *
   * @return bool
   *   True if a test API key is configured, false otherwise.
   */
  public function hasTestApiKey(): bool;

  /**
   * Determines whether an organisation access token is configured.
   *
   * @return bool
   *   True if an organisation access token is configured, false otherwise.
   */
  public function hasOrganisationAccessToken(): bool;

}
