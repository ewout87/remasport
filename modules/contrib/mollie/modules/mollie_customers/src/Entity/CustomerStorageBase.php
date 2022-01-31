<?php

namespace Drupal\mollie_customers\Entity;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\ContentEntityStorageBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\mollie\Mollie;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\BaseResource;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CustomerStorageBase.
 *
 * @package Drupal\mollie_customers\Entity
 */
abstract class CustomerStorageBase extends ContentEntityStorageBase {

  const RESOURCE_NAME = '';

  /**
   * Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Mollie API client.
   *
   * @var \Mollie\Api\MollieApiClient|null
   */
  protected $mollieApiClient;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * TransactionStorageBase constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to be used.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   * @param \Drupal\mollie\Mollie $mollieConnector
   *   Mollie connector.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language manager.
   * @param \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface|null $memoryCache
   *   The memory cache backend.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info.
   */
  public function __construct(
    EntityTypeInterface $entityType,
    EntityFieldManagerInterface $entityFieldManager,
    CacheBackendInterface $cache,
    MessengerInterface $messenger,
    Mollie $mollieConnector,
    ConfigFactoryInterface $configFactory,
    LanguageManagerInterface $languageManager,
    MemoryCacheInterface $memoryCache,
    EntityTypeBundleInfoInterface $entityTypeBundleInfo
  ) {
    parent::__construct($entityType, $entityFieldManager, $cache, $memoryCache, $entityTypeBundleInfo);

    $this->messenger = $messenger;
    $this->mollieApiClient = $mollieConnector->getClient();
    $this->configFactory = $configFactory;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entityType) {
    return new static(
      $entityType,
      $container->get('entity_field.manager'),
      $container->get('cache.entity'),
      $container->get('messenger'),
      $container->get('mollie.mollie'),
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('entity.memory_cache'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * Static cache for entities.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $loadedEntities = [];

  /**
   * {@inheritdoc}
   */
  public function doLoadMultiple(array $ids = NULL): array {
    $entities = [];

    foreach ($ids as $id) {
      // Static caching.
      if (isset($this->loadedEntities[$id])) {
        $entities[$id] = $this->loadedEntities[$id];
        continue;
      }

      try {
        if (empty(static::RESOURCE_NAME)) {
          throw new ApiException('The resource name is invalid.');
        }

        $customerBase = $this->mollieApiClient->{static::RESOURCE_NAME}->get($id);

        $entity = $this->createEntityFromCustomer($customerBase);
        if (!is_null($entity)) {
          $this->loadedEntities[$id] = $entity;
          $entities[$id] = $this->loadedEntities[$id];
        }
      }
      catch (ApiException $e) {
        watchdog_exception('mollie', $e);
      }
    }

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function has($id, EntityInterface $entity) {
    // TODO: Implement has() method.
  }

  /**
   * {@inheritdoc}
   */
  public function purgeFieldItems(ContentEntityInterface $entity, FieldDefinitionInterface $field_definition) {
    // TODO: Implement purgeFieldItems() method.
  }

  /**
   * {@inheritdoc}
   */
  public function doDeleteRevisionFieldItems(ContentEntityInterface $revision) {
    // TODO: Implement doDeleteRevisionFieldItems() method.
  }

  /**
   * {@inheritdoc}
   */
  public function countFieldData($storage_definition, $as_bool = FALSE) {
    // TODO: Implement countFieldData() method.
  }

  /**
   * {@inheritdoc}
   */
  public function readFieldItemsToPurge(FieldDefinitionInterface $field_definition, $batch_size) {
    // TODO: Implement readFieldItemsToPurge() method.
  }

  /**
   * BC for Drupal 8.
   *
   * In Drupal 9 this is superseded by doLoadMultipleRevisionsFieldItems().
   * @see https://www.drupal.org/project/drupal/issues/1730874
   * @see https://www.drupal.org/project/drupal/issues/3069696
   */
  public function doLoadRevisionFieldItems($revision_id) {
    // TODO: Implement doLoadRevisionFieldItems() method.
  }

  /**
   * {@inheritdoc}
   */
  protected function doLoadMultipleRevisionsFieldItems($revision_ids) {
    // TODO: Implement doLoadMultipleRevisionsFieldItems() method.
  }

  /**
   * {@inheritdoc}
   */
  public function doDeleteFieldItems($entities) {
    foreach ($entities as $entity) {
      $this->deleteCustomerByEntity($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function doSaveFieldItems(ContentEntityInterface $entity, array $names = []) {
    $this->createCustomerFromEntity($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryServiceName() {
    return 'mollie.mollie';
  }

  /**
   * Returns an entity created from the data in a customer base object.
   *
   * This method should be implemented by storage classes for specific
   * customer base types.
   *
   * @param \Mollie\Api\Resources\BaseResource $customer
   *   Transaction object.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   */
  abstract protected function createEntityFromCustomer(BaseResource $customer): ?EntityInterface;

  /**
   * Creates a customer base object from an entity.
   *
   * This method should be implemented by storage classes for specific
   * customer base types.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  abstract protected function createCustomerFromEntity(EntityInterface $entity): void;

  /**
   * Deletes a customer base object from an entity.
   *
   * This method should be implemented by storage classes for specific
   * customer base types.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  abstract protected function deleteCustomerByEntity(EntityInterface $entity): void;

}
