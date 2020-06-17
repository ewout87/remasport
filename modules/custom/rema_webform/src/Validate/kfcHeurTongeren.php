<?php

namespace Drupal\rema_webform\Validate;

use Drupal\Core\Field\FieldException;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form API callback. Validate element value.
 */
class kfcHeurTongeren {
    /**
     * Validates given element.
     *
     * @param array              $element      The form element to process.
     * @param FormStateInterface $formState    The form state.
     * @param array              $form The complete form structure.
     */
    public static function validate(array &$element, FormStateInterface $formState, array &$form) {
        $webformKey = $element['#webform_key'];
        $value = $formState->getValue($webformKey);
        $team = $formState->getValue('ploegen');
        $form_state->setError($element, t('test');


        $values = NestedArray::getValue($form_state->getValues(), $element['#parents']);

        if($webformKey == 'ballen'){
            if ($formState->getValue('lidmaatschap') == 'Ik ben al lid') {
                foreach($values as $item){
                    if($item['Maat'] == ''){
                        $error = true;
                    }
                }
            }
        }
        
        if($webformKey == 'kousen_geel'){
            if ($formState->getValue('lidmaatschap') == 'Ik ben al lid' && $team == 'U8-U19') {
                foreach($values as $item){
                    if($item['Maat'] == ''){
                        $error = true;
                    }
                }
            }
        }
        
        if($webformKey == 'kousen_blauw'){
            if ($formState->getValue('lidmaatschap') == 'Ik ben al lid' && $team == 'Keeper') {
                foreach($values as $item){
                    if($item['Maat'] == ''){
                        $error = true;
                    }
                }
            }
        }
        
        if ($error) {
            if (isset($element['#title'])) {
                $tArgs = [
                    '%name' => empty($element['#title']) ? $element['#parents'][0] : $element['#title'],
                    '%team' => $team,
                ];
                $formState->setError(
                    $element,
                    t('%name is required for %team', $tArgs)
                );
            } else {
                $formState->setError($element);
            }
        }
    }
}
