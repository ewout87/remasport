<?php

namespace Drupal\mollie\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mollie\Mollie;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PaymentForm.
 *
 * @package Drupal\mollie\Form
 */
class PaymentForm extends ContentEntityForm {

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

    // Get the available payment methods if we have an amount and currency.
    if ($form_state->hasValue('amount')
      && $form_state->hasValue('currency')) {
      $form['method']['widget']['#options'] = \Drupal::service('mollie.mollie')
        ->getMethods(
          $form_state->getValue('amount')[0]['value'],
          $form_state->getValue('currency')[0]['value']
        );
    }

    // Wrap the method field for ajaxification.
    $form['method'] += [
      '#prefix' => '<div id="available-payment-methods">',
      '#suffix' => '</div>',
    ];

    // Ajaxify the amount field.
    $form['amount']['widget'][0]['value']['#ajax'] = [
      'callback' => [static::class, 'updateMethods'],
      'wrapper' => 'available-payment-methods',
      // Prevent auto focus on the field after ajax call. This can probably be
      // removed once #2627788 lands.
      // @see https://www.drupal.org/project/drupal/issues/2627788
      'disable-refocus' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $formState) {
    // Set the context and the context ID for the payment.
    $formState->setValue('context', 'form');
    $formState->setValue('context_id', $this->uuid->generate());

    parent::submitForm($form, $formState);
  }

  /**
   * Returns a renderable array for the method form element.
   *
   * @param $form
   *   Renderable array for the form as build by the form builder.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   *
   * @return array
   *   Renderable array for the method form element.
   */
  public function updateMethods($form, FormStateInterface $form_state): array {
    return $form['method'];
  }

}
