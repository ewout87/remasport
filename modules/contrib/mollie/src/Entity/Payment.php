<?php

namespace Drupal\mollie\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Class Payment.
 *
 * @package Drupal\mollie\Entity
 *
 * @ContentEntityType(
 *   id = "mollie_payment",
 *   label = @Translation("Mollie payment"),
 *   handlers = {
 *     "access" = "Drupal\mollie\PaymentAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\mollie\Form\PaymentForm",
 *     },
 *     "list_builder" = "Drupal\mollie\Controller\PaymentListBuilder",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "storage" = "Drupal\mollie\Entity\PaymentStorage",
 *   },
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *   },
 *   links = {
 *     "canonical" = "/admin/content/mollie/payment/{mollie_payment}",
 *     "collection" = "/admin/content/mollie/payments",
 *     "add-form" = "/admin/content/mollie/payment/add",
 *   },
 * )
 */
class Payment extends TransactionBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entityType) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entityType);

    if (isset($fields['id'])) {
      $fields['id']->setDescription(t('The ID of the Mollie payment.'));
    }

    // Payment description.
    $fields['description'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Description'))
      ->setRequired(TRUE)
      ->setDisplayOptions(
        'form',
        [
          'type' => 'string_textfield',
          'weight' => 0,
        ]
      );

    // Payment method.
    $fields['method'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Method'))
      ->setDisplayOptions(
        'form',
        [
          'type' => 'options_select',
          'weight' => 15,
        ]
      );

    // Payment method issuer.
    $fields['issuer'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Issuer'))
      ->setDisplayOptions(
        'form',
        [
          'type' => 'options_select',
          'weight' => 15,
        ]
      );

    // Payment checkout URL.
    $fields['checkout_url'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Checkout URL'));

    return $fields;
  }

  /**
   * Returns the description for the payment.
   *
   * @return string
   *   Description for the payment.
   */
  public function getDescription(): string {
    return $this->get('description')->value;
  }

  /**
   * Returns the payment method for the payment.
   *
   * @return string
   *   Payment method for the payment.
   */
  public function getMethod(): ?string {
    return $this->get('method')->value;
  }

  /**
   * Returns the payment method issuer for the payment.
   *
   * @return string
   *   Payment method issuer for the payment.
   */
  public function getIssuer(): ?string {
    return $this->get('issuer')->value;
  }

  /**
   * Returns the checkout URL for the payment.
   *
   * @return string
   *   Checkout URL for the payment.
   */
  public function getCheckoutUrl(): ?string {
    return $this->get('checkout_url')->value;
  }

}
