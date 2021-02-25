<?php

namespace Drupal\mollie\Entity\Query;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryBase;
use Mollie\Api\MollieApiClient;

/**
 * Class TransactionQueryBase.
 *
 * @package Drupal\mollie\Entity\Query
 */
abstract class TransactionQueryBase extends QueryBase {

  /**
   * Mollie API client.
   *
   * @var \Mollie\Api\MollieApiClient
   */
  protected $mollieApiClient;

  /**
   * TransactionQuery constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition.
   * @param string $conjunction
   *   - AND: all of the conditions on the query need to match.
   *   - OR: at least one of the conditions on the query need to match.
   * @param array $namespaces
   *   List of potential namespaces of the classes belonging to this query.
   * @param \Mollie\Api\MollieApiClient $mollieApiClient
   *   Mollie API client.
   */
  public function __construct(
    EntityTypeInterface $entityType,
    string $conjunction,
    array $namespaces,
    MollieApiClient $mollieApiClient
  ) {
    parent::__construct($entityType, $conjunction, $namespaces);

    $this->mollieApiClient = $mollieApiClient;
  }

}
