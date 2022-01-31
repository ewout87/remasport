<?php

namespace Drupal\mollie_customers;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mollie\MollieEntityBasePermissions;

/**
 * Class CustomerPermissions.
 *
 * @package Drupal\mollie_customers
 */
class CustomerPermissions extends MollieEntityBasePermissions {

  /**
   * Returns permissions for operations on mollie_customer entities.
   *
   * @return array
   *   Array with permissions.
   */
  public function customerPermissions(): array {
    try {
      $entityType = $this->entityTypeManager
        ->getStorage('mollie_customer')
        ->getEntityType();
      return $this->entityBasePermissions($entityType);
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      watchdog_exception('mollie', $e);
    }

    return [];
  }

  /**
   * Returns available operations on entities of a given type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   Entity type.
   *
   * @return array
   *   Array with operations.
   */
  protected function getAvailableOperations(EntityTypeInterface $entityType): array {
    return array_merge(parent::getAvailableOperations($entityType), ['delete']);
  }

}
