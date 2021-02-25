<?php

namespace Drupal\mollie\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mollie\TransactionInterface;

/**
 * Class TransactionBase.
 *
 * @package Drupal\mollie\Entity
 */
abstract class TransactionBase extends ContentEntityBase implements TransactionInterface {

  use EntityChangedTrait;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entityType) {
    $fields = [];

    // Transaction ID.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setReadOnly(TRUE);

    // Transaction created date.
    $fields['created'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Created'))
      ->setReadOnly(TRUE);

    // Transaction changed time.
    $fields['changed'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Changed'))
      ->setReadOnly(TRUE);

    // Transaction status.
    $fields['status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Status'))
      ->setReadOnly(TRUE);

    // Transaction amount.
    $fields['amount'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Amount'))
      ->setRequired(TRUE)
      ->setDisplayOptions(
        'form',
        [
          'type' => 'string_textfield',
          'weight' => 10,
        ]
      );

    // Transaction currency.
    $fields['currency'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Currency'))
      ->setRequired(TRUE)
      ->setSetting(
        'allowed_values',
        ['EUR' => 'Euro', 'USD' => 'USD']
      )
      ->setDisplayOptions(
        'form',
        [
          'type' => 'options_select',
          'weight' => 5,
        ]
      );

    // Transaction mode.
    $fields['mode'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Mode'))
      ->setReadOnly(TRUE);

    // Transaction context.
    $fields['context'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Context'))
      ->setDescription(t('Type of context that requested the transaction.'));

    // Transaction context ID.
    $fields['context_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Context ID'))
      ->setDescription(t('ID of the context that requested the transaction.'));

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
  public function getStatus(): string {
    return $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getAmount(): float {
    return $this->get('amount')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrency(): string {
    return $this->get('currency')->value;
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
  public function getContext(): string {
    return $this->get('context')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextId(): string {
    return $this->get('context_id')->value;
  }

}
