<?php

namespace Drupal\mollie;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TransactionPermissions.
 *
 * @package Drupal\mollie
 */
class TransactionPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * TransactionPermissions constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns permissions for operations on mollie_payment entities.
   *
   * @return array
   *   Array with permissions.
   */
  public function paymentPermissions(): array {
    try {
      $entityType = $this->entityTypeManager
        ->getStorage('mollie_payment')
        ->getEntityType();
      return $this->transactionPermissions($entityType);
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      watchdog_exception('mollie', $e);
    }

    return [];
  }

  /**
   * Returns permissions for operations on entities of a given type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   Entity type.
   *
   * @return array
   *   Array with permissions.
   */
  protected function transactionPermissions(EntityTypeInterface $entityType): array {
    $permissions = [];

    foreach ($this->getAvailableOperations($entityType) as $operation) {
      $permissions["$operation {$entityType->id()} entities"] = [
        'title' => ucfirst(
          $this->t('@operation @label entities', [
            '@operation' =>  $operation,
            '@label' => $entityType->getLabel()
          ])
        ),
      ];
    }

    return $permissions;
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
    return ['create', 'view'];
  }

}
