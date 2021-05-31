<?php 
namespace Drupal\rema_webform\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Emails a webform submission.
 *
 * @WebformHandler(
 *   id = "local_email",
 *   label = @Translation("Local email"),
 *   category = @Translation("Notification"),
 *   description = @Translation("Sends a webform submission to a different email address per language."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class RemaEmailWebformHandler extends EmailWebformHandler {

  public function sendMessage(WebformSubmissionInterface $webform_submission, array $message) {

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    switch($language) {

      case 'nl':
        $recipient = 'info@example.nl';
        break;

      case 'ru':
        $recipient = 'info@example.ru';
        break;

      case 'en':
      default:
        $recipient = 'info@example.com';
        break;

    }

    $message['to_mail'] = $recipient;

    parent::sendMessage($webform_submission, $message);
  }

}
