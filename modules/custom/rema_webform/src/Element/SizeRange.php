<?php

namespace Drupal\rema_webform\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Select;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Provides a 'size_range'.
 *
 * Webform elements are just wrappers around form elements, therefore every
 * webform element must have correspond FormElement.
 *
 * Below is the definition for a custom 'webform_example_element' which just
 * renders a simple text field.
 *
 * @FormElement("size_range")
 *
 * @see \Drupal\Core\Render\Element\FormElement
 * @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21FormElement.php/class/FormElement
 * @see \Drupal\Core\Render\Element\RenderElement
 * @see https://api.drupal.org/api/drupal/namespace/Drupal%21Core%21Render%21Element
 * @see \Drupal\webform_example_element\Element\WebformExampleElement
 */
class SizeRange extends Select {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#size' => 60,
      '#process' => [
        [$class, 'processWebformElementExample'],
        [$class, 'processAjaxForm'],
      ],
      '#element_validate' => [
        [$class, 'validateWebformExampleElement'],
      ],
      '#pre_render' => [
        [$class, 'preRenderWebformExampleElement'],
      ],
      '#theme' => 'input__webform_example_element',
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * Webform element validation handler for #type 'size_range'.
   */
  public static function validateSizeRange(&$element, FormStateInterface $form_state, &$complete_form) {
    // Here you can add custom validation logic.
  }

  /**
   * {@inheritdoc}
   */
  public static function processSelect(&$element, FormStateInterface $form_state, &$complete_form) {
    $element = parent::processSelect($element, $form_state, $complete_form);

    $product = Node::load($element['#product']);

    $options = [];

    if($product instanceof NodeInterface){
      $sizes = $product->get('field_product_sizes')->getValue();

      foreach($sizes as $size){
        $size = Node::load($size[0]['target_id']);

        if($size instanceof NodeInterface){
          $price = $size->get('field_size_price')->getValue();

          $options[$price[0]['value']] = $price->getTitle();
        }
      }
    }

    if(array_key_exists($price[0]['value'], $options)){
      $options[$price[0]['value'].' '] = $price->getTitle();
    }

    // Must convert this element['#type'] to a 'select' to prevent
    // "Illegal choice %choice in %name element" validation error.
    // @see \Drupal\Core\Form\FormValidator::performRequiredValidation
    $element['#type'] = 'select';

    // Add class.
    $element['#attributes']['class'][] = 'product-select';

    return $element;
  }


  /**
   * Prepares a #type 'email_multiple' render element for theme_element().
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for theme_element().
   */
  public static function preRenderSizeRange(array $element) {
    $element['#attributes']['type'] = 'select';
    Element::setAttributes($element, ['id', 'name', 'value', 'size', 'maxlength', 'placeholder']);
    static::setAttributes($element, ['form-text', 'webform-example-element']);
    return $element;
  }

}
