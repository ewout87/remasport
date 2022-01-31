<?php

namespace Drupal\mollie\Controller;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PaymentListBuilder.
 *
 * @package Drupal\mollie\Controller
 */
class PaymentListBuilder extends EntityListBuilder {

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
  public function buildHeader() {
    $header = [];

    $header['id'] = $this->t('Payment ID');
    $header['mode'] = $this->t('Mode');
    $header['amount'] = $this->t('Amount');
    $header['refunded_amount'] = $this->t('Refunded amount');
    $header['refundable_amount'] = $this->t('Refundable amount');
    $header['captured_amount'] = $this->t('Captured mount');
    $header['charged_back_amount'] = $this->t('Charged back amount');
    $header['status'] = $this->t('Status');
    $header['created'] = $this->t('Created');
    $header['changed'] = $this->t('Changed');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\mollie\Entity\Payment $entity */
    $row = [];

    $row['id'] = Link::fromTextAndUrl($entity->id(), $entity->toUrl());
    $row['mode'] = $entity->getMode();
    $row['amount'] = $this->t(
      '@amount @currency',
      [
        '@amount' => $entity->getAmount(),
        '@currency' => $entity->getCurrency(),
      ]
    );
    $row['refunded_amount'] = $this->t(
      '@amount @currency',
      [
        '@amount' => $entity->getRefundedAmount(),
        '@currency' => $entity->getRefundedCurrency(),
      ]
    );
    $row['refundable_amount'] = $this->t(
      '@amount @currency',
      [
        '@amount' => $entity->getRefundableAmount(),
        '@currency' => $entity->getRefundableCurrency(),
      ]
    );
    $row['captured_amount'] = $this->t(
      '@amount @currency',
      [
        '@amount' => $entity->getCapturedAmount(),
        '@currency' => $entity->getCapturedCurrency(),
      ]
    );
    $row['charged_back_amount'] = $this->t(
      '@amount @currency',
      [
        '@amount' => $entity->getChargedBackAmount(),
        '@currency' => $entity->getChargedBackCurrency(),
      ]
    );
    $row['status'] = $entity->getStatus();
    $row['created'] = $this->getFormattedDate($entity->getCreatedTime());
    $row['changed'] = $this->getFormattedDate($entity->getChangedTime());

    return $row + parent::buildRow($entity);
  }

  /**
   * @param string $date
   *   Date in ISO 8601 format.
   *
   * @return string
   *   Date formatted in medium date format.
   */
  protected function getFormattedDate($date) {
    $dateTime = new \DateTime($date);
    return $this->dateFormatter
      ->format($dateTime->format('U'), 'medium');
  }

}
