<?php

namespace Drupal\mollie_uc\Plugin\Ubercart\PaymentMethod;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mollie\Entity\Payment;
use Drupal\mollie\Mollie as MollieService;
use Drupal\uc_order\OrderInterface;
use Drupal\uc_payment\OffsitePaymentMethodPluginInterface;
use Drupal\uc_payment\PaymentMethodPluginBase;
use Mollie\Api\MollieApiClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class Mollie.
 *
 * @package Drupal\mollie_uc\Plugin\Ubercart\PaymentMethod
 *
 * @UbercartPaymentMethod(
 *   id = "mollie",
 *   name = @Translation("Mollie"),
 *   no_ui = FALSE
 * )
 */
class Mollie extends PaymentMethodPluginBase implements OffsitePaymentMethodPluginInterface, ContainerFactoryPluginInterface {

  /**
   * Session service.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * Mollie service.
   *
   * @var \Drupal\mollie\Mollie
   */
  protected $mollie;

  /**
   * Mollie constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   Session service.
   * @param \Drupal\mollie\Mollie $mollie
   *   Mollie service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    SessionInterface $session,
    MollieService $mollie
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->session = $session;
    $this->mollie = $mollie;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('session'),
      $container->get('mollie.mollie')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $formState) {
    $form = parent::buildConfigurationForm($form, $formState);

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
  public function cartDetails(OrderInterface $order, array $form, FormStateInterface $formState) {
    $mollieForm = [];

    // Add payment method selection.
    $mollieForm['method'] = [
      '#type' => 'select',
      '#title' => $this->t('Payment method'),
      '#options' => $this->mollie->getMethods(
        $order->getTotal(),
        $order->getCurrency()
      ),
    ];

    // Add issuer selection for iDEAL.
    if (in_array('ideal', array_keys($mollieForm['method']['#options']))) {
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

      $mollieForm['issuer'] = [
        '#type' => 'select',
        '#title' => $this->t('Issuer'),
        '#options' => $options,
        '#states' => [
          'visible' => [
            'select[name="panes[payment][details][method]"]' => ['value' => 'ideal'],
          ],
        ],
      ];
    }

    return $mollieForm;
  }

  /**
   * {@inheritdoc}
   */
  public function cartProcess(OrderInterface $order, array $form, FormStateInterface $formState) {
    // Store method and issuer in the session to be used in buildRedirectForm().
    $methodKey = ['panes', 'payment', 'details', 'method'];
    if ($formState->hasValue($methodKey)) {
      $this->session->set('mollie_uc.method', $formState->getValue($methodKey));
    }

    $issuerKey = ['panes', 'payment', 'details', 'issuer'];
    if ($formState->hasValue($issuerKey)) {
      $this->session->set('mollie_uc.issuer', $formState->getValue($issuerKey));
    }

    return parent::cartProcess($order, $form, $formState);
  }

  /**
   * {@inheritdoc}
   */
  public function buildRedirectForm(array $form, FormStateInterface $formState, OrderInterface $order) {
    // Initialize the Mollie payment.
    $transaction = Payment::create([
      'amount' => $order->getTotal(),
      'currency' => $order->getCurrency(),
      // TODO: Better description.
      'description' => "Order #{$order->id()}",
      'context' => 'mollie_uc',
      'context_id' => $order->id(),
    ]);

    if ($this->session->has('mollie_uc.method')) {
      $transaction->set('method', $this->session->get('mollie_uc.method'));
    }

    if ($this->session->has('mollie_uc.issuer')) {
      $transaction->set('issuer', $this->session->get('mollie_uc.issuer'));
    }

    try {
      // Create the Mollie payment.
      $transaction->save();

      // Redirect to Mollie.
      $form['#action'] = $transaction->getCheckoutUrl();

      // This form will be nested inside another form so we need to add our
      // own actions.
      $form['actions'] = array('#type' => 'actions');
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Pay off-site'),
      );

      return $form;
    }
    catch (EntityStorageException $e) {
      watchdog_exception('mollie_commerce', $e);
    }
  }

}
