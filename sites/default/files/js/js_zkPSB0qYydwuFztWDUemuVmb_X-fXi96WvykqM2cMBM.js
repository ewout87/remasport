/**
 * @file
 * JavaScript behaviors for details element.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Attach handler to details with invalid inputs.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformDetailsInvalid = {
    attach: function (context) {
      $('details :input', context).on('invalid', function () {
        $(this).parents('details:not([open])').children('summary').click();

        // Synd details toggle label.
        if (Drupal.webform && Drupal.webform.detailsToggle) {
          Drupal.webform.detailsToggle.setDetailsToggleLabel($(this.form));
        }
      });
    }
  };

})(jQuery, Drupal);
;
/**
 * @file
 * JavaScript behaviors for radio buttons.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Adds HTML5 validation to required radios buttons.
   *
   * @type {Drupal~behavior}
   *
   * @see Issue #2856795: If radio buttons are required but not filled form is nevertheless submitted.
   */
  Drupal.behaviors.webformRadiosRequired = {
    attach: function (context) {
      $('.js-webform-type-radios, .js-webform-type-webform-radios-other', context).each(function () {
        var $element = $(this);
        var $radios = $element.find('input[type="radio"]');
        if ($element.hasClass('required')) {
          $radios.attr({'required': 'required', 'aria-required': 'true'});
        }
        // Copy clientside_validation.module's message to the radio buttons.
        if ($element.attr('data-msg-required')) {
          $radios.attr({'data-msg-required': $element.attr('data-msg-required')});
        }
      });
    }
  };

})(jQuery, Drupal);
;
/**
 * @file
 * JavaScript behaviors for options elements.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Attach handlers to options buttons element.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformOptionsButtons = {
    attach: function (context) {
      // Place <input> inside of <label> before the label.
      $(context).find('label.webform-options-display-buttons-label > input[type="checkbox"], label.webform-options-display-buttons-label > input[type="radio"]').each(function () {
        var $input = $(this);
        var $label = $input.parent();
        $input.detach().insertBefore($label);
      });
    }
  };


})(jQuery, Drupal);
;
/**
 * @file
 * JavaScript behaviors for webform wizard.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Link the wizard's previous pages.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Links the wizard's previous pages.
   */
  Drupal.behaviors.webformWizardPagesLink = {
    attach: function (context) {
      $('.js-webform-wizard-pages-links', context).once('webform-wizard-pages-links').each(function () {
        var $pages = $(this);
        var $form = $pages.closest('form');

        var hasProgressLink = $pages.data('wizard-progress-link');
        var hasPreviewLink = $pages.data('wizard-preview-link');

        $pages.find('.js-webform-wizard-pages-link').each(function () {
          var $button = $(this);
          var title = $button.attr('title');
          var page = $button.data('webform-page');

          // Link progress marker and title.
          if (hasProgressLink) {
            var $progress = $form.find('.webform-progress [data-webform-page="' + page + '"]');
            $progress.find('.progress-marker, .progress-title')
              .attr({
                'role': 'link',
                'title': title,
                'aria-label': title
              })
              .click(function () {
                $button.click();
              });
            // Only allow the marker to be tabbable.
            $progress.find('.progress-marker').attr('tabindex', 0);
          }

          // Move button to preview page div container with [data-webform-page].
          // @see \Drupal\webform\Plugin\WebformElement\WebformWizardPage::formatHtmlItem
          if (hasPreviewLink) {
            $form
              .find('.webform-preview [data-webform-page="' + page + '"]')
              .append($button)
              .show();
          }
        });
      });
    }
  };

})(jQuery, Drupal);
;
