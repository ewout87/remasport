<?php

namespace Drupal\mollie_customers\Controller;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CustomerListBuilder.
 *
 * @package Drupal\mollie_customers\Controller
 */
class CustomerListBuilder extends EntityListBuilder {

  /**
   * Date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new EntityListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   Date formatter.
   */
  public function __construct(
    EntityTypeInterface $entityType,
    EntityStorageInterface $storage,
    DateFormatterInterface $dateFormatter
  ) {
    parent::__construct($entityType, $storage);

    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entityType) {
    return new static(
      $entityType,
      $container->get('entity_type.manager')->getStorage($entityType->id()),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header = [];

    $header['id'] = $this->t('Customer ID');
    $header['mode'] = $this->t('Mode');
    $header['name'] = $this->t('Name');
    $header['email'] = $this->t('Email');
    $header['created'] = $this->t('Created');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\mollie_customers\CustomerInterface $entity */
    $row = [];

    $row['id'] = Link::fromTextAndUrl($entity->id(), $entity->toUrl());
    $row['mode'] = $entity->getMode();
    $row['name'] = $entity->getName();
    $row['email'] = $entity->getEmail();
    $row['created'] = $this->getFormattedDate($entity->getCreatedTime());

    return $row + parent::buildRow($entity);
  }

  /**
   * @param string $date
   *   Date in ISO 8601 format.
   *
   * @return string
   *   Date formatted in medium date format.
   */
  protected function getFormattedDate(string $date): string {
    $dateTime = new \DateTime($date);
    return $this->dateFormatter
      ->format($dateTime->format('U'), 'medium');
  }

}
