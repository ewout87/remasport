<?php

namespace Drupal\rema_webform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Entity\Webform;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;


/**
 * Implements an example form.
 */
class DownloadForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'download_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $export_options = [
        'exporter' => 'delimited',
  
        'delimiter' => ',',
        'multiple_delimiter' => ';',
        'excel' => FALSE,
  
        'file_name' => 'submission-[webform_submission:serial]',
        'archive_type' => 'tar',
  
        'header_format' => 'label',
        'header_prefix' => TRUE,
        'header_prefix_label_delimiter' => ': ',
        'header_prefix_key_delimiter' => '__',
        'excluded_columns' => [
          'uuid' => 'uuid',
          'token' => 'token',
          'webform_id' => 'webform_id',
        ],
  
        'entity_type' => '',
        'entity_id' => '',
        'range_type' => 'all',
        'range_latest' => '',
        'range_start' => '',
        'range_end' => '',
        'uid' => '',
        'order' => 'asc',
        'state' => 'all',
        'locked' => '',
        'sticky' => '',
        'download' => TRUE,
        'files' => FALSE,
        'attachments' => FALSE,
      ];

    $form['export']['download']['range_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Limit to'),
        '#options' => [
          'all' => $this->t('All'),
          'latest' => $this->t('Latest'),
          'uid' => $this->t('Submitted by'),
          'serial' => $this->t('Submission number'),
          'sid' => $this->t('Submission ID'),
          'date' => $this->t('Created date'),
          'date_completed' => $this->t('Completed date'),
          'date_changed' => $this->t('Changed date'),
        ],
        '#default_value' => $export_options['range_type'],
    ];
    $form['export']['download']['latest'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['container-inline']],
        '#states' => [
          'visible' => [
            ':input[name="range_type"]' => ['value' => 'latest'],
          ],
        ],
        'range_latest' => [
          '#type' => 'number',
          '#title' => $this->t('Number of submissions'),
          '#min' => 1,
          '#default_value' => $export_options['range_latest'],
        ],
    ];
    $form['export']['download']['submitted_by'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['container-inline']],
        '#states' => [
          'visible' => [
            ':input[name="range_type"]' => ['value' => 'uid'],
          ],
        ],
        'uid' => [
          '#type' => 'entity_autocomplete',
          '#title' => $this->t('User'),
          '#target_type' => 'user',
          '#default_value' => $export_options['uid'],
          '#states' => [
            'visible' => [
              ':input[name="range_type"]' => ['value' => 'uid'],
            ],
          ],
        ],
    ];
    $ranges = [
        'serial' => ['#type' => 'number'],
        'sid' => ['#type' => 'number'],
        'date' => ['#type' => 'date'],
        'date_completed' => ['#type' => 'date'],
        'date_changed' => ['#type' => 'date'],
      ];
      foreach ($ranges as $key => $range_element) {
        $form['export']['download'][$key] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['container-inline']],
          '#tree' => TRUE,
          '#states' => [
            'visible' => [
              ':input[name="range_type"]' => ['value' => $key],
            ],
          ],
        ];
        $form['export']['download'][$key]['range_start'] = $range_element + [
          '#title' => $this->t('From'),
          '#parents' => [$key, 'range_start'],
          '#default_value' => $export_options['range_start'],
        ];
        $form['export']['download'][$key]['range_end'] = $range_element + [
          '#title' => $this->t('To'),
          '#parents' => [$key, 'range_end'],
          '#default_value' => $export_options['range_end'],
        ];
      }
      $form['export']['download']['order'] = [
        '#type' => 'select',
        '#title' => $this->t('Order'),
        '#description' => $this->t('Order submissions by ascending (oldest first) or descending (newest first).'),
        '#options' => [
          'asc' => $this->t('Sort ascending'),
          'desc' => $this->t('Sort descending'),
        ],
        '#default_value' => $export_options['order'],
        '#states' => [
          'visible' => [
            ':input[name="range_type"]' => ['!value' => 'latest'],
          ],
        ],
      ];
      $form['export']['download']['sticky'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Starred/flagged submissions'),
        '#description' => $this->t('If checked, only starred/flagged submissions will be downloaded. If unchecked, all submissions will downloaded.'),
        '#return_value' => TRUE,
        '#default_value' => $export_options['sticky'],
      ];

      
      $form['download'] = [
        '#type' => 'submit',
        '#value' => $this->t('Download'),
        '#ajax' => [
          'callback' => '::generatePdf',
          'progress' => [
            'type' => 'throbber',
            'message' => $this->t('Generating pdf...'),
          ],
        ],
        '#name' => 'download_orders',
      ];

      /*
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Download'),
      ];
      */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $webform = $node->get('webform')->getValue();
      if (!$webform) {
        $form_state->setErrorByName('webform', $this->t('There is no webform attached to this page'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function generatePdf(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $node = \Drupal::routeMatch()->getParameter('node');
    $pdf = [];
    if ($node instanceof NodeInterface) {
      $webform_id = $node->get('webform')->getValue()[0]['target_id'];
      $webform = Webform::load($webform_id);
      if ($webform->hasSubmissions()) {
        $query = \Drupal::entityTypeManager()->getStorage('webform_submission')->getQuery()
          ->condition('webform_id', $webform_id);
        $result = $query->execute();
        $submissions = [];
        foreach ($result as $item) {
          $submission = WebformSubmission::load($item);
          $sid = $submission->get('sid')->getValue()[0]['value'];
          $submissions[] = [
            'data' => $submission->getData(),
            'sid' => $sid,
          ];
        }
        $pdf = \Drupal::service('rema_webform.print_orders')->generatePdf($submissions, $webform_id, $node);
      }
    }
    
    $response->addCommand(new RedirectCommand($pdf));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }
}
