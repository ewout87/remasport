<?php

namespace Drupal\rema_webform\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElementBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a 'size_range' element.
 *
 * @WebformElement(
 *   id = "size_range",
 *   label = @Translation("Size range"),
 *   description = @Translation("Provides a size range element."),
 *   category = @Translation("Rema"),
 * )
 *
 * @see \Drupal\webform_example_element\Element\WebformExampleElement
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class SizeRange extends WebformElementBase {

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    // Here you define your webform element's default properties,
    // which can be inherited.
    //
    // @see \Drupal\webform\Plugin\WebformElementBase::defaultProperties
    // @see \Drupal\webform\Plugin\WebformElementBase::defaultBaseProperties
    return [
        'product' => '',
        'multiple' => '',
        'size' => '',
        'minlength' => '',
        'maxlength' => '',
        'placeholder' => '',
      ] + parent::defineDefaultProperties();
  }

  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, WebformSubmissionInterface $webform_submission = NULL) {
    parent::prepare($element, $webform_submission);
    // Here you can customize the webform element's properties.
    // You can also customize the form/render element's properties via the
    // FormElement.
    //
    // @see \Drupal\webform_example_element\Element\WebformExampleElement::processWebformElementExample
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    // Here you can define and alter a webform element's properties UI.
    // Form element property visibility and default values are defined via
    // ::defaultProperties.
    //
    // @see \Drupal\webform\Plugin\WebformElementBase::form
    // @see \Drupal\webform\Plugin\WebformElement\TextBase::form
    $form['entity_reference'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Entity reference settings'),
      '#weight' => -40,
    ];

    $form['entity_reference']['product'] = array(
      '#type' => 'entity_autocomplete',
      '#title' => t('Product'),
      '#target_type' => 'node',
      '#selection_settings' => ['target_bundles' => ['page']],
      '#tags' => TRUE,
      '#size' => 30,
      '#maxlength' => 1024,
    );

    return $form;
  }
}
