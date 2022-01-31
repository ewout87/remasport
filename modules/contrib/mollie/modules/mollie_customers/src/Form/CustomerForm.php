<?php

namespace Drupal\mollie_customers\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mollie\Mollie;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CustomerForm.
 *
 * @package Drupal\mollie_customers\Form
 */
class CustomerForm extends ContentEntityForm {

  /**
   * UUID generator.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  /**
   * Mollie API client.
   *
   * @var \Drupal\mollie\Mollie
   */
  protected $mollieApiClient;

  /**
   * PaymentForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository service.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   UUID generator.
   * @param \Drupal\mollie\Mollie $mollieApiClient
   *   Mollie API client.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(
    EntityRepositoryInterface $entityRepository,
    UuidInterface $uuid,
    Mollie $mollieApiClient,
    EntityTypeBundleInfoInterface $entityTypeBundleInfo = NULL,
    TimeInterface $time = NULL
  ) {
    parent::__construct($entityRepository, $entityTypeBundleInfo, $time);

    $this->uuid = $uuid;
    $this->mollieApiClient = $mollieApiClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('uuid'),
      $container->get('mollie.mollie'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $formState) {
    parent::submitForm($form, $formState);
  }

}
