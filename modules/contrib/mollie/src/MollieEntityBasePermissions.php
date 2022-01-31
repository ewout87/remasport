<?php

namespace Drupal\mollie;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MollieEntityBasePermissions.
 *
 * @package Drupal\mollie
 */
class MollieEntityBasePermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CustomerPermissions constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   */
  protected function __construct(EntityTypeManagerInterface $entityTypeManager) {
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
   * Returns permissions for operations on entities of a given type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   Entity type.
   *
   * @return array
   *   Array with permissions.
   */
  protected function entityBasePermissions(EntityTypeInterface $entityType): array {
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
