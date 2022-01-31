<?php

namespace Drupal\mollie_customers\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mollie_customers\CustomerInterface;

/**
 * Class Customer.
 *
 * @package Drupal\mollie_customers\Entity
 *
 * @ContentEntityType(
 *   id = "mollie_customer",
 *   label = @Translation("Mollie customer"),
 *   handlers = {
 *     "access" = "Drupal\mollie_customers\CustomerAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\mollie_customers\Form\CustomerForm",
 *       "delete" = "Drupal\mollie_customers\Form\CustomerDeleteForm",
 *     },
 *     "list_builder" = "Drupal\mollie_customers\Controller\CustomerListBuilder",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "storage" = "Drupal\mollie_customers\Entity\CustomerStorage",
 *   },
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *   },
 *   links = {
 *     "canonical" = "/admin/content/mollie/customer/{mollie_customer}",
 *     "collection" = "/admin/content/mollie/customers",
 *     "add-form" = "/admin/content/mollie/customer/add",
 *     "delete-form" = "/admin/content/mollie/customer/{mollie_customer}/delete",
 *   },
 * )
 */
class Customer extends ContentEntityBase implements CustomerInterface {

  use EntityChangedTrait;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entityType) {
    $fields = [];

    // Customer ID.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The customer’s unique identifier.'))
      ->setReadOnly(TRUE);

    // Customer created date.
    $fields['created'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Created'))
      ->setDescription(t('The customer’s date and time of creation, in ISO 8601 format.'))
      ->setReadOnly(TRUE);

    // Customer name.
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The full name of the customer as provided when the customer was created.'))
      ->setRequired(TRUE)
      ->setDisplayOptions(
        'form',
        [
          'type' => 'string_textfield',
          'weight' => 1,
        ]
      )
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 0,
      ]);

    // Customer email.
    $fields['email'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Email'))
      ->setDescription(t('The email address of the customer as provided when the customer was created.'))
      ->setRequired(TRUE)
      ->setDisplayOptions(
        'form',
        [
          'type' => 'string_textfield',
          'weight' => 1,
        ]
      )
      ->setDisplayOptions(
        'view',
        [
          'label' => 'inline',
          'type' => 'email',
          'weight' => 0,
        ]
      );

    // Customer changed time.
    $fields['changed'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Changed'))
      ->setReadOnly(TRUE);

    // Customer mode.
    $fields['mode'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Mode'))
      ->setDescription(t('The mode used to create this customer.'))
      ->setReadOnly(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function isTranslatable() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime(): string {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): ?string {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmail(): ?string {
    return $this->get('email')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getMode(): string {
    return $this->get('mode')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    /** @var \Drupal\mollie_customers\Entity\CustomerStorage $customerStorage */
    $customerStorage = \Drupal::service('entity_type.manager')->getStorage('mollie_customer');
    $customerStorage->delete([$this->id() => $this]);
  }

}
