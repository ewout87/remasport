<?php

namespace Drupal\mollie_webform\Plugin\WebformHandler;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @WebformHandler(
 *   id = "mollie_payment_handler",
 *   label = @Translation("Mollie payment"),
 *   category = @Translation("Mollie"),
 *   description = @Translation("Creates a Mollie payment from submitted form data."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 * )
 */
class MolliePaymentHandler extends WebformHandlerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $class = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $class->entityTypeManager = $container->get('entity_type.manager');
    return $class;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['currency'] = [
      '#type' => 'select',
      '#title' => $this->t('Currency'),
      '#required' => TRUE,
      '#options' => ['EUR' => 'Euro', 'USD' => 'USD'],
      '#default_value' => $this->getConfiguration()['settings']['currency'] ?? '',
    ];

    // Build an options array for the elements on the webform.
    $elements = $this->getWebform()->getElementsDecodedAndFlattened();
    $options = [];
    foreach ($elements as $key => $element) {
      $options[$key] = $element['#title'];
    }

    $form['amount_element'] = [
      '#type' => 'select',
      '#title' => $this->t('Amount element'),
      '#description' => $this->t('Form element holding the amount for the payment.'),
      '#required' => TRUE,
      '#options' => $options,
      '#default_value' => $this->getConfiguration()['settings']['amount_element'] ?? '',
    ];

    $form['method_element'] = [
      '#type' => 'select',
      '#title' => $this->t('Payment method element'),
      '#description' => $this->t('Form element holding the payment method chosen in the webform. Leaving empty will allow choosing payment method in Mollie.'),
      '#required' => FALSE,
      '#options' => ['' => $this->t('None')] + $options,
      '#default_value' => $this->getConfiguration()['settings']['method_element'] ?? '',
    ];

    $form['description_element'] = [
      '#type' => 'select',
      '#title' => $this->t('Description element'),
      '#description' => $this->t('(optional) Form element holding the description for the payment. Defaults to "[form title] #[submission id]".'),
      '#options' => ['' => $this->t('None')] + $options,
      '#default_value' => $this->getConfiguration()['settings']['description_element'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['currency'] = $form_state->getValue('currency');
    $this->configuration['amount_element'] = $form_state->getValue('amount_element');
    $this->configuration['description_element'] = $form_state->getValue('description_element');
    $this->configuration['method_element'] = $form_state->getValue('method_element');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return [
      '#theme' => 'item_list',
      '#items' => [
        $this->t('Currency: %currency', ['%currency' => $this->getConfiguration()['settings']['currency']]),
        $this->t('Amount element: %amount_element', ['%amount_element' => $this->getConfiguration()['settings']['amount_element']]),
        $this->t('Description element: %description_element', ['%description_element' => $this->getConfiguration()['settings']['description_element']]),
        $this->t('Payment method element: %method_element', ['%method_element' => $this->getConfiguration()['settings']['method_element']]),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function confirmForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $description = $this->getWebform()->label() . ' #' . $webform_submission->id();
    if ($this->getConfiguration()['settings']['description_element']) {
      $submittedDescription = $webform_submission->getElementData($this->getConfiguration()['settings']['description_element']);
      $description = !empty($submittedDescription) ? $submittedDescription : $description;
    }

    $transaction = $this->entityTypeManager->getStorage('mollie_payment')->create(
      [
        'amount' => (float) $webform_submission->getElementData($this->getConfiguration()['settings']['amount_element']),
        'currency' => $this->getConfiguration()['settings']['currency'],
        'description' => $description,
        'context' => 'mollie_webform',
        'context_id' => $webform_submission->id(),
        'method' => $webform_submission->getElementData($this->getConfiguration()['settings']['method_element']) ?? [],
      ]
    );
    try {
      // Create the Mollie payment.
      $transaction->save();

      // Redirect to Mollie.
      $response = new TrustedRedirectResponse($transaction->getCheckoutUrl(), '303');
      $form_state->setResponse($response);
    }
    catch (EntityStorageException $e) {
      watchdog_exception('mollie', $e);
    }
  }

}
