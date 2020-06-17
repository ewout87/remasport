/**
 * @file
 * JavaScript behaviors for composite element builder.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Initialize composite element builder.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformElementComposite = {
    attach: function (context) {
      $('[data-composite-types]').once('webform-composite-types').each(function () {
        var $element = $(this);
        var $type = $element.closest('tr').find('.js-webform-composite-type');

        var types = $element.attr('data-composite-types').split(',');
        var required = $element.attr('data-composite-required');

        $type.on('change', function () {
          if ($.inArray($(this).val(), types) === -1) {
            $element.hide();
            if (required) {
              $element.removeAttr('required aria-required');
            }
          }
          else {
            $element.show();
            if (required) {
              $element.attr({'required': 'required', 'aria-required': 'true'});
            }
          }
        }).change();
      });
    }
  };

})(jQuery, Drupal);
;
/**
 * @file
 * JavaScript behaviors for multiple element.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Move show weight to after the table.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformMultipleTableDrag = {
    attach: function (context, settings) {
      for (var base in settings.tableDrag) {
        if (settings.tableDrag.hasOwnProperty(base)) {
          $(context).find('.js-form-type-webform-multiple #' + base).once('webform-multiple-table-drag').each(function () {
            var $tableDrag = $(this);
            var $toggleWeight = $tableDrag.prev().prev('.tabledrag-toggle-weight-wrapper');
            if ($toggleWeight.length) {
              $toggleWeight.addClass('webform-multiple-tabledrag-toggle-weight');
              $tableDrag.after($toggleWeight);
            }
          });
        }
      }
    }
  };

  /**
   * Submit multiple add number input value when enter is pressed.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformMultipleAdd = {
    attach: function (context, settings) {
      $(context).find('.js-webform-multiple-add').once('webform-multiple-add').each(function () {
        var $submit = $(this).find('input[type="submit"], button');
        var $number = $(this).find('input[type="number"]');
        $number.keyup(function (event) {
          if (event.which === 13) {
            // Note: Mousedown is the default trigger for Ajax events.
            // @see Drupal.Ajax.
            $submit.mousedown();
          }
        });
      });
    }
  };

})(jQuery, Drupal);
;
