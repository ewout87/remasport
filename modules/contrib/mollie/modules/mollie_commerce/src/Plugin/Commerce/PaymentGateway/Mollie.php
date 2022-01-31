<?php

namespace Drupal\mollie_commerce\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\commerce_price\MinorUnitsConverterInterface;
use Drupal\commerce_price\Price;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Mollie\Api\Types\PaymentStatus;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Mollie.
 *
 * @package Drupal\commerce_mollie\Plugin\Commerce\PaymentGateway
 *
 * @CommercePaymentGateway(
 *   id = "mollie",
 *   label = @Translation("Mollie"),
 *   display_label = @Translation("Mollie"),
 *   modes = {
 *     "test" = @Translation("Test"),
 *     "live" = @Translation("Live"),
 *   },
 *   forms = {
 *     "offsite-payment" = "Drupal\mollie_commerce\PluginForm\OffsiteRedirect\MolliePaymentOffsiteForm",
 *   },
 * )
 */
class Mollie extends OffsitePaymentGatewayBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new PaymentGatewayBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_payment\PaymentTypeManager $payment_type_manager
   *   The payment type manager.
   * @param \Drupal\commerce_payment\PaymentMethodTypeManager $payment_method_type_manager
   *   The payment method type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   * @param \Drupal\commerce_price\MinorUnitsConverterInterface $minor_units_converter
   *   The minor units converter.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, PaymentTypeManager $payment_type_manager, PaymentMethodTypeManager $payment_method_type_manager, TimeInterface $time, MinorUnitsConverterInterface $minor_units_converter = NULL, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $payment_type_manager, $payment_method_type_manager, $time, $minor_units_converter);

    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.commerce_payment_type'),
      $container->get('plugin.manager.commerce_payment_method_type'),
      $container->get('datetime.time'),
      $container->get('commerce_price.minor_units_converter'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $formState) {
    $form = parent::buildConfigurationForm($form, $formState);

    // Do not show the 'mode' field as we inherit the mode from the Mollie for
    // Drupal configuration.
    $form['mode']['#access'] = FALSE;

    $message = $this->t('Mollie is configured in the Mollie for Drupal module.');
    $form['mollie_message'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $message,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function onCancel(OrderInterface $order, Request $request) {
    $this->messenger()->addMessage($this->t('You have canceled the payment or the payment failed. You may resume the checkout process here when you are ready.'));
  }

  /**
   * {@inheritdoc}
   */
  public function onNotify(Request $request) {
    try {
      // Fetch the Mollie transaction.
      /** @var \Drupal\mollie\Entity\Payment $transaction */
      $transaction = $this->entityTypeManager->getStorage('mollie_payment')
        ->load($request->get('mollie_transaction_id'));

      // Fetch the Commerce order.
      /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
      $order = $this->entityTypeManager->getStorage('commerce_order')
        ->load($request->get('order_id'));

      // Fetch the Commerce payment corresponding to the Mollie transaction.
      $payments = $this->entityTypeManager->getStorage('commerce_payment')
        ->loadByProperties([
          'order_id' => $request->get('order_id'),
          'remote_id' => $request->get('mollie_transaction_id'),
        ]);
      // We assume only one Commerce payment per Mollie transaction per order.
      $payment = reset($payments);

      if (!$payment) {
        // Create a new Commerce payment if none exists yet.
        /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
        $payment = $this->entityTypeManager->getStorage('commerce_payment')
          ->create([
            'amount' => new Price((string) $transaction->getAmount(), $transaction->getCurrency()),
            'payment_gateway' => $this->parentEntity->id(),
            'order_id' => $order->id(),
            'test' => $this->getMode() === 'test',
            'remote_id' => $transaction->id(),
          ]);
      }

      // Update payment status.
      $payment->setRemoteState($transaction->getStatus());
      $payment->setState($this->getStateByMollieTransactionStatus(
        $transaction->getStatus()
      ));

      // Save the payment.
      $payment->save();
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException | EntityStorageException $e) {
      watchdog_exception('mollie_commerce', $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMode() {
    return $this->configFactory->get('mollie.config')->get('test_mode') ? 'test' : 'live';
  }

  /**
   * Returns the Commerce payment state corresponding to a Mollie status.
   *
   * @param string $mollieStatus
   *   Status of the Mollie transaction.
   *
   * @return string
   *   Corresponding state for the Commerce payment.
   */
  protected function getStateByMollieTransactionStatus(string $mollieStatus): string {
    switch ($mollieStatus) {
      case PaymentStatus::STATUS_PENDING:
        return 'pending';

      case PaymentStatus::STATUS_AUTHORIZED:
        return 'authorization';

      case PaymentStatus::STATUS_PAID:
        return 'completed';

      case PaymentStatus::STATUS_CANCELED:
      case PaymentStatus::STATUS_EXPIRED:
      case PaymentStatus::STATUS_FAILED:
        return 'voided';

      default:
        return 'new';
    }
  }

}
