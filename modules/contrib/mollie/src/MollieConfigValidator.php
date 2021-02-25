<?php

namespace Drupal\mollie;

use Drupal\Core\Site\Settings;

/**
 * Class MollieConfigValidator.
 *
 * @package Drupal\mollie
 */
class MollieConfigValidator implements MollieConfigValidatorInterface {

  /**
   * {@inheritdoc}
   */
  public function hasLiveApiKey(): bool {
    return $this->hasSetting('live_key');
  }

  /**
   * {@inheritdoc}
   */
  public function hasTestApiKey(): bool {
    return $this->hasSetting('test_key');
  }

  /**
   * {@inheritdoc}
   */
  public function hasOrganisationAccessToken(): bool {
    return $this->hasSetting('access_token');
  }

  /**
   * Determines whether a certain Mollie setting is configured.
   *
   * @param string $name
   *   The name of the setting to check.
   *
   * @return bool
   *   True of the setting with the given name is set and not empty, false
   *   otherwise.
   */
  protected function hasSetting(string $name): bool {
    $mollieSettings = Settings::get('mollie.settings');

    return isset($mollieSettings[$name]) && !empty($mollieSettings[$name]);
  }

}
