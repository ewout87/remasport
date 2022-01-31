<?php

namespace Drupal\mollie_webform\EventSubscriber;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\mollie\Events\MollieRedirectEvent;
use Drupal\webform\Plugin\WebformSourceEntityManager;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformMessageManagerInterface;
use Drupal\webform\WebformRequestInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformTokenManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class MollieRedirectEventSubscriber.
 *
 * @package Drupal\mollie_commerce\EventSubscriber
 */
class MollieRedirectEventSubscriber implements EventSubscriberInterface {

  use LoggerChannelTrait;
  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Webform message manager.
   *
   * @var \Drupal\webform\WebformMessageManagerInterface
   */
  protected $messageManager;

  /**
   * Current route match.
   * 
   * @var \Drupal\Core\Routing\RouteMatchInterface 
   */
  protected $routeMatch;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Webform token manager.
   *
   * @var \Drupal\webform\WebformTokenManagerInterface
   */
  protected $tokenManager;

  /**
   * Configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * Webform request handler.
   *
   * @var \Drupal\webform\WebformRequestInterface
   */
  protected $requestHandler;

  /**
   * MollieRedirectEventSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\webform\WebformMessageManagerInterface $messageManager
   *   Webform message manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   Current route match.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack.
   * @param \Drupal\webform\WebformTokenManagerInterface $tokenManager
   *   Webform token manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Configuration factory.
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   Alias manager.
   * @param \Drupal\Core\Path\PathValidatorInterface $pathValidator
   *   Path validator.
   * @param \Drupal\webform\WebformRequestInterface $requestHandler
   *   Webform request handler.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    WebformMessageManagerInterface $messageManager,
    RouteMatchInterface $routeMatch,
    RequestStack $requestStack,
    WebformTokenManagerInterface $tokenManager,
    ConfigFactoryInterface $configFactory,
    AliasManagerInterface $aliasManager,
    PathValidatorInterface $pathValidator,
    WebformRequestInterface $requestHandler
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->messageManager = $messageManager;
    $this->routeMatch = $routeMatch;
    $this->requestStack = $requestStack;
    $this->tokenManager = $tokenManager;
    $this->configFactory = $configFactory;
    $this->aliasManager = $aliasManager;
    $this->pathValidator = $pathValidator;
    $this->requestHandler = $requestHandler;
  }

  /**
   * Sets the redirect URL on the event.
   *
   * @param \Drupal\mollie\Events\MollieRedirectEvent $event
   *   Event.
   */
  public function setRedirectUrl(MollieRedirectEvent $event): void {
    // Return if the event context is not this module.
    if ($event->getContext() !== 'mollie_webform') {
      return;
    }

    // By default we redirect to the homepage.
    $url = Url::fromRoute('<front>');

    try {
      $webformSubmission = $this->entityTypeManager->getStorage('webform_submission')
        ->load($event->getContextId());

      if ($webformSubmission instanceof WebformSubmissionInterface) {
        // Get the confirmation URL as configured for the webform the submission
        // belongs to. This also takes care of setting the configured
        // confirmation message.
        $url = $this->getConfirmationUrl($webformSubmission);
      }
    }
    catch (PluginNotFoundException | InvalidPluginDefinitionException $e) {
      watchdog_exception('mollie_webform', $e);
    }

    $event->setRedirectUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      MollieRedirectEvent::EVENT_NAME => 'setRedirectUrl',
    ];
  }

  /**
   * Returns webform confirmation redirect URL and sets message if applicable.
   * 
   * This is mainly a copy of
   * \Drupal\webform\WebformSubmissionForm::setConfirmation() which
   * unfortunately is not usable outside the
   * \Drupal\webform\WebformSubmissionForm class.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webformSubmission
   *   Webform submission.
   * 
   * @return \Drupal\Core\Url
   *   Webform confirmation redirect URL.
   */
  protected function getConfirmationUrl(WebformSubmissionInterface $webformSubmission): Url {
    $webform = $webformSubmission->getWebform();

    // Get current route name, parameters, and options.
    $routeName = $this->routeMatch->getRouteName();
    $routeParameters = $this->routeMatch->getRawParameters()->all();
    $routeOptions = [];

    // Add current query to route options.
    if (!$webform->getSetting('confirmation_exclude_query')) {
      $query = $this->requestStack->getCurrentRequest()->query->all();
      // Remove Ajax parameters from query.
      unset($query['ajax_form'], $query['_wrapper_format']);
      if ($query) {
        $routeOptions['query'] = $query;
      }
    }

    // Default to displaying a confirmation message on this page when submission
    // is updated or locked (but not just completed).
    $state = $webformSubmission->getState();
    $isUpdated = ($state === WebformSubmissionInterface::STATE_UPDATED);
    $isLocked = ($state === WebformSubmissionInterface::STATE_LOCKED && $webformSubmission->getChangedTime() > $webformSubmission->getCompletedTime());
    $confirmationUpdate = $this->getWebformSetting($webformSubmission, 'confirmation_update');

    if (($isUpdated && !$confirmationUpdate) || $isLocked) {
      $this->messageManager->display(WebformMessageManagerInterface::SUBMISSION_UPDATED);
      return Url::fromRoute($routeName, $routeParameters, $routeOptions);
    }

    // Add token route query options.
    if ($state === WebformSubmissionInterface::STATE_COMPLETED && !$webform->getSetting('confirmation_exclude_token')) {
      $routeOptions['query']['token'] = $webformSubmission->getToken();
    }

    // Handle 'page', 'url', and 'inline' confirmation types.
    $confirmationType = $this->getWebformSetting($webformSubmission, 'confirmation_type');
    switch ($confirmationType) {
      case WebformInterface::CONFIRMATION_PAGE:
        return $this->requestHandler->getUrl($webform, $this->getSourceEntity($webformSubmission), 'webform.confirmation', $routeOptions);

      case WebformInterface::CONFIRMATION_URL:
      case WebformInterface::CONFIRMATION_URL_MESSAGE:
        $confirmationUrl = trim($this->getWebformSetting($webformSubmission, 'confirmation_url', ''));
        // Remove base path from root-relative URL.
        // Only applies for Drupal sites within a sub directory.
        $confirmationUrl = preg_replace('/^' . preg_quote(base_path(), '/') . '/', '/', $confirmationUrl);
        // Get system path.
        $confirmationUrl = $this->aliasManager->getPathByAlias($confirmationUrl);
        // Get redirect URL if internal or valid.
        if (strpos($confirmationUrl, 'internal:') === 0) {
          $redirectUrl = Url::fromUri($confirmationUrl);
        }
        else {
          $redirectUrl = $this->pathValidator->getUrlIfValid($confirmationUrl);
        }
        if ($redirectUrl) {
          if ($confirmationType === WebformInterface::CONFIRMATION_URL_MESSAGE) {
            $this->messageManager->display(WebformMessageManagerInterface::SUBMISSION_CONFIRMATION_MESSAGE);
          }
          return $redirectUrl;
        }
        else {
          $tArgs = [
            '@webform' => $webform->label(),
            '%url' => $this->getWebformSetting($webformSubmission, 'confirmation_url'),
          ];
          // Display warning to use who can update the webform.
          if ($webform->access('update')) {
            $this->messenger()->addWarning($this->t('Confirmation URL %url is not valid.', $tArgs));
          }
          // Log warning.
          $this->getLogger('webform')->warning('@webform: Confirmation URL %url is not valid.', $tArgs);
        }

        // If confirmation URL is invalid display message.
        $this->messageManager->display(WebformMessageManagerInterface::SUBMISSION_CONFIRMATION_MESSAGE);
        $routeOptions['query']['webform_id'] = $webform->id();
        return Url::fromRoute($routeName, $routeParameters, $routeOptions);

      case WebformInterface::CONFIRMATION_INLINE:
        // We do not support the inline confirmation message at this moment.
        // Redirect to the webform itself.
        return Url::fromRoute('entity.webform.canonical', ['webform' => $webform->id()]);

      case WebformInterface::CONFIRMATION_MODAL:
        // We do not support the confirmation modal at this moment. Display the
        // message like a regular confirmation message.
      case WebformInterface::CONFIRMATION_MESSAGE:
        $this->messageManager->display(WebformMessageManagerInterface::SUBMISSION_CONFIRMATION_MESSAGE);
        // Redirect to the webform itself.
        return Url::fromRoute('entity.webform.canonical', ['webform' => $webform->id()]);

      case WebformInterface::CONFIRMATION_NONE:
        return Url::fromRoute('entity.webform.canonical', ['webform' => $webform->id()]);

      case WebformInterface::CONFIRMATION_DEFAULT:
      default:
        $this->messageManager->display(WebformMessageManagerInterface::SUBMISSION_DEFAULT_CONFIRMATION);
        return Url::fromRoute('entity.webform.canonical', ['webform' => $webform->id()]);
    }
  }

  /**
   * Returns a webform submission's webform setting.
   *
   * This is a copy of
   * \Drupal\webform\WebformSubmissionForm::getWebformSetting() which
   * unfortunately is not accessible outside its class.
   *
   * @param string $name
   *   Setting name.
   * @param null|mixed $defaultvalue
   *   Default value.
   *
   * @return mixed
   *   A webform setting.
   */
  protected function getWebformSetting(WebformSubmissionInterface $webformSubmission, $name, $defaultvalue = NULL) {
    $value = $webformSubmission->getWebform()->getSetting($name)
      ?: $this->configFactory->get('webform.settings')->get('settings.default_' . $name)
        ?: NULL;

    if ($value !== NULL) {
      return $this->tokenManager->replace($value, $webformSubmission);
    }
    else {
      return $defaultvalue;
    }
  }

  /**
   * Returns the source entity of a webform submission.
   *
   * This is mainly a copy of a code snippet from
   * \Drupal\webform\WebformSubmissionForm::setEntity().
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webformSubmission
   *   Webform submission.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Source entity of the webform submission. Or null if no source entity
   *   could be determined.
   */
  protected function getSourceEntity(WebformSubmissionInterface $webformSubmission): ?EntityInterface {
    // Get the source entity and allow webform submission to be used as a source
    // entity.
    $sourceEntity = $webformSubmission->getSourceEntity(TRUE) ?: $this->requestHandler->getCurrentSourceEntity(['webform']);
    if ($sourceEntity === $webformSubmission) {
      $sourceEntity = $this->requestHandler->getCurrentSourceEntity(['webform', 'webform_submission']);
    }
    // Handle paragraph source entity.
    if ($sourceEntity && $sourceEntity->getEntityTypeId() === 'paragraph') {
      $sourceEntity = WebformSourceEntityManager::getMainSourceEntity($sourceEntity);
    }

    // Return source entity.
    return $sourceEntity;
  }

}
