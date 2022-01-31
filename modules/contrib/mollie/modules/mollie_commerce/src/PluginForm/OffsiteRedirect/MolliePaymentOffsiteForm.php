<?php

namespace Drupal\mollie_commerce\PluginForm\OffsiteRedirect;

use Drupal\commerce\Response\NeedsRedirectException;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mollie\Entity\Payment;
use Drupal\mollie\Mollie;
use Mollie\Api\MollieApiClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MolliePaymentOffsiteForm.
 *
 * @package Drupal\mollie_commerce\PluginForm\OffsiteRedirect
 */
class MolliePaymentOffsiteForm extends PaymentOffsiteForm implements ContainerInjectionInterface {

  /**
   * Mollie service.
   * 
   * @var \Drupal\mollie\Mollie 
   */
  protected $mollie;

  /**
   * MolliePaymentOffsiteForm constructor.
   *
   * @param \Drupal\mollie\Mollie $mollie
   *   Mollie service.
   */
  public function __construct(Mollie $mollie) {
    $this->mollie = $mollie;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mollie.mollie')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $formState) {
    $form = parent::buildConfigurationForm($form, $formState);

    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;

    // Add payment method selection.
    $form['method'] = [
      '#type' => 'select',
      '#title' => $this->t('Payment method'),
      '#options' => $this->mollie->getMethods(
        $payment->getAmount()->getNumber(),
        $payment->getAmount()->getCurrencyCode()
      ),
    ];

    // Add issuer selection for iDEAL.
    if (in_array('ideal', array_keys($form['method']['#options']))) {
      $issuers = [];
      $client = $this->mollie->getClient();
      if ($client instanceof MollieApiClient) {
        // TODO: Create separate client method to fetch issuers.
        $issuers = $client->methods->get('ideal', [
          'include' => 'issuers',
        ])->issuers;
      }

      $options = [];
      foreach ($issuers as $issuer) {
        $options[$issuer->id] = $issuer->name;
      }

      $form['issuer'] = [
        '#type' => 'select',
        '#title' => $this->t('Issuer'),
        '#options' => $options,
        '#states' => [
          'visible' => [
            'select[name="payment_process[offsite_payment][method]"]' => ['value' => 'ideal'],
          ],
        ],
      ];
    }

    $form['actioms'] = [
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Pay off-site'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\commerce\Response\NeedsRedirectException
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $formState) {
    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;
    // Initialize the Mollie payment.
    $transaction = Payment::create([
      'amount' => $payment->getAmount()->getNumber(),
      'currency' => $payment->getAmount()->getCurrencyCode(),
      // TODO: Better description.
      'description' => "Order #{$payment->getOrder()->id()}",
      'context' => 'mollie_commerce',
      'context_id' => $payment->getOrderId(),
    ]);

    $methodKey = ['payment_process', 'offsite_payment', 'method'];
    if ($formState->hasValue($methodKey)) {
      $transaction->set('method', $formState->getValue($methodKey));
    }

    $issuerKey = ['payment_process', 'offsite_payment', 'issuer'];
    if ($formState->hasValue($issuerKey)) {
      $transaction->set('issuer', $formState->getValue($issuerKey));
    }

    try {
      // Create the Mollie payment.
      $transaction->save();

      // Store the Commerce payment.
      $payment->setRemoteId($transaction->id());
      $payment->save();

      // Redirect to Mollie.
      throw new NeedsRedirectException($transaction->getCheckoutUrl());
    }
    catch (EntityStorageException $e) {
      watchdog_exception('mollie_commerce', $e);
    }
  }

}
