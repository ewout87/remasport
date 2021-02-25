<?php

namespace Drupal\mollie;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Site\Settings;
use Drupal\mollie\Entity\Query\PaymentQuery;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Exceptions\IncompatiblePlatform;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\MethodCollection;

/**
 * Class Mollie.
 *
 * @package Drupal\mollie
 */
class Mollie {

  /**
   * Messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Mollie config validator.
   *
   * @var \Drupal\mollie\MollieConfigValidator
   */
  protected $configValidator;

  /**
   * Mollie API client.
   *
   * @var \Mollie\Api\MollieApiClient
   */
  protected $client;

  /**
   * Mollie constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\mollie\MollieConfigValidator $configValidator
   *   Mollie config validator.
   */
  public function __construct(
    MessengerInterface $messenger,
    ConfigFactoryInterface $configFactory,
    MollieConfigValidator $configValidator
  ) {
    $this->messenger = $messenger;
    $this->configFactory = $configFactory;
    $this->configValidator = $configValidator;
  }

  /**
   * Returns a client for communication with the Mollie API.
   *
   * @return \Mollie\Api\MollieApiClient|null
   *   A client for communication with the Mollie API or NULL if this client
   *   could not be loaded.
   */
  public function getClient(): ?MollieApiClient {
    // Static caching.
    if (isset($this->client)) {
      return $this->client;
    }

    try {
      $client = new MollieApiClient();

      // Add version strings. These are used by Mollie to gather statistics
      // about platform usage.
      $client->addVersionString('Drupal/8.x');
      $client->addVersionString('Drupal/' . \Drupal::VERSION);

      if ($this->useTestMode()) {
        $client->setApiKey(Settings::get('mollie.settings')['test_key']);
      }
      elseif ($this->configValidator->hasLiveApiKey()) {
        $client->setApiKey(Settings::get('mollie.settings')['live_key']);
      }

      $this->client = $client;
      return $this->client;
    }
    catch (IncompatiblePlatform $e) {
      watchdog_exception('mollie', $e);
      $this->messenger
        ->addError(t('This project is not compatible with Mollie API client for PHP.'));
    }
    catch (ApiException $e) {
      watchdog_exception('mollie', $e);
      $this->messenger
        ->addError(t('The Mollie API client for PHP could not be initialized.'));
    }

    return NULL;
  }

  /**
   * Determines whether test mode should be used.
   *
   * @return bool
   *   True if test mode should be used, false otherwise.
   */
  public function useTestMode(): bool {
    return $this->configValidator->hasTestApiKey()
      && $this->configFactory->get('mollie.config')->get('test_mode');
  }

  /**
   * Returns an entity query for a Mollie transaction entity type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type.
   * @param string $conjunction
   *   Conjunction to use for query conditions.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   Entity query object.
   */
  public function get(EntityTypeInterface $entityType, string $conjunction): ?QueryInterface {
    if ($entityType->id() === 'mollie_payment') {
      $client = $this->getClient();
      if ($client instanceof MollieApiClient) {
        return new PaymentQuery(
          $entityType,
          $conjunction,
          ['\Drupal\mollie\Entity\Query'],
          $client
        );
      }
    }

    return NULL;
  }

  /**
   * Returns an array of payment methods available for a given amount.
   *
   * @param float $amount
   *   Amount to be paid.
   * @param string $currency
   *   Currency in which the amount should be paid.
   *
   * @return array
   *   Associative array keyed by payment method ID.
   */
  public function getMethods(float $amount, string $currency): array {
    $methods = [];

    foreach ($this->getMethodsRaw($amount, $currency) as $method) {
      /** @var \Mollie\Api\Resources\Method $method */
      $methods[$method->id] = $method->description;
    }

    return $methods;
  }

  /**
   * Returns a collection of payment methods available for a given amount.
   *
   * @param float $amount
   *   Amount to be paid.
   * @param string $currency
   *   Currency in which the amount should be paid.
   *
   * @return \Mollie\Api\Resources\MethodCollection
   *   Method collection.
   */
  protected function getMethodsRaw(float $amount, string $currency): MethodCollection {
    try {
      return $this->getClient()->methods->allActive(
        [
          'amount' => [
            'value' => number_format($amount, 2),
            'currency' => $currency,
          ]
        ]
      );
    }
    catch (ApiException $e) {
      watchdog_exception('mollie', $e);
    }

    return new MethodCollection(0, []);
  }

}
