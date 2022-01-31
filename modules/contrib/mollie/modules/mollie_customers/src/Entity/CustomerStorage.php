<?php

namespace Drupal\mollie_customers\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Language\LanguageInterface;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\BaseResource;
use Mollie\Api\Resources\Customer as MollieCustomer;

/**
 * Class CustomerStorage.
 *
 * @package Drupal\mollie_customers\Entity
 */
class CustomerStorage extends CustomerStorageBase {

  const RESOURCE_NAME = 'customers';

  /**
   * {@inheritdoc}
   */
  protected function createEntityFromCustomer(BaseResource $customer): ?EntityInterface {
    if ($customer instanceof MollieCustomer) {
      $values = [
        'id' => $customer->id,
        'created' => $customer->createdAt,
        'name' => $customer->name,
        'email' => $customer->email,
        'mode' => $customer->mode,
        'changed' => $this->getCustomerChangedDate($customer),
      ];

      return Customer::create($values);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function createCustomerFromEntity(EntityInterface $entity): void {
    if ($entity instanceof Customer) {
      $values = [
        'name' => $entity->getName(),
        'email' => $entity->getEmail(),
        'locale' => $this->getLocaleByCurrentContentLanguage(),
      ];

      try {
        // Create a customer on the Mollie side.
        $customer = $this->mollieApiClient->customers->create($values);
        // Update the entity with the information added by Mollie.
        // TODO: This might be incomplete. Look for a way to replace $entity.
        $entity->setOriginalId($customer->id);
        $entity->set('id', $customer->id);
      }
      catch (ApiException $e) {
        watchdog_exception('mollie', $e);
        throw new EntityStorageException('An error occurred while creating the customer.');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function deleteCustomerByEntity(EntityInterface $entity): void {
    if ($entity instanceof Customer) {
      try {
        // Delete a customer on the Mollie side.
        $this->mollieApiClient->customers->delete($entity->id());
      }
      catch (ApiException $e) {
        watchdog_exception('mollie', $e);
        throw new EntityStorageException('An error occurred while deleting the customer.');
      }
    }
  }

  /**
   * Returns the date the customer last changed in ISO 8601 format.
   *
   * @param \Mollie\Api\Resources\Customer $customer
   *   Payment.
   *
   * @return string
   *   Date the customer last changed in ISO 8601 format.
   */
  protected function getCustomerChangedDate(MollieCustomer $customer): string {
    $changedDate = $customer->createdAt;

    foreach ($this->getCustomerDateFields() as $dateField) {
      if (property_exists($customer, $dateField) && $customer->{$dateField} > $changedDate) {
        $changedDate = $customer->{$dateField};
      }
    }

    return $changedDate;
  }

  /**
   * Returns the date fields known for customers.
   *
   * @return array
   *   Array of date field names.
   */
  protected function getCustomerDateFields(): array {
    return ['createdAt'];
  }

  /**
   * Returns an ISO locale code based on the current content language.
   *
   * Best effort to get a locale code to pass to Mollie. Drupal does not have
   * a country for (anonymous) visitors by default so we cannot construct the
   * locale from language code and country code. This implementation is based
   * on the locales currently supported by Mollie.
   *
   * @return string
   */
  protected function getLocaleByCurrentContentLanguage(): string {
    $langcode = $this->languageManager
      ->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)
      ->getId();

    switch ($langcode) {
      case 'en':
        return 'en_US';

      case 'ca':
        return 'ca_ES';

      case 'nb':
        return 'nb_NO';

      case 'sv':
        return 'sv_SE';

      case 'da':
        return 'da_DK';

      default:
        $countryCode = strtoupper($langcode);
        return "{$langcode}_{$countryCode}";
    }
  }

}
