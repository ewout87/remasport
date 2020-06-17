
(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.tokenTree = {
    attach: function (context, settings) {
      $('table.token-tree', context).once('token-tree').each(function () {
        $(this).treetable({ expandable: true });
      });
    }
  };

  Drupal.behaviors.tokenInsert = {
    attach: function (context, settings) {
      // Keep track of which textfield was last selected/focused.
      $('textarea, input[type="text"]', context).focus(function () {
        drupalSettings.tokenFocusedField = this;
      });

      $('.token-click-insert .token-key', context).once('token-click-insert').each(function () {
        var newThis = $('<a href="javascript:void(0);" title="' + Drupal.t('Insert this token into your form') + '">' + $(this).html() + '</a>').click(function () {
          var content = this.text;

          // Always work in normal text areas that currently have focus.
          if (drupalSettings.tokenFocusedField && (drupalSettings.tokenFocusedField.tokenDialogFocus || drupalSettings.tokenFocusedField.tokenHasFocus)) {
            insertAtCursor(drupalSettings.tokenFocusedField, content);
          }
          // Direct tinyMCE support.
          else if (typeof(tinyMCE) != 'undefined' && tinyMCE.activeEditor) {
            tinyMCE.activeEditor.execCommand('mceInsertContent', false, content);
          }
          // Direct CKEditor support. Only works if the field currently has focus,
          // which is unusual since the dialog is open.
          else if (typeof(CKEDITOR) != 'undefined' && CKEDITOR.currentInstance) {
            CKEDITOR.currentInstance.insertHtml(content);
          }
          // Direct CodeMirror support.
          else if (typeof(CodeMirror) != 'undefined' && drupalSettings.tokenFocusedField && $(drupalSettings.tokenFocusedField).parents('.CodeMirror').length) {
            var editor = $(drupalSettings.tokenFocusedField).parents('.CodeMirror')[0].CodeMirror;
            editor.replaceSelection(content);
            editor.focus();
          }
          // WYSIWYG support, should work in all editors if available.
          else if (Drupal.wysiwyg && Drupal.wysiwyg.activeId) {
            Drupal.wysiwyg.instances[Drupal.wysiwyg.activeId].insert(content)
          }
          // CKeditor module support.
          else if (typeof(CKEDITOR) != 'undefined' && typeof(Drupal.ckeditorActiveId) != 'undefined') {
            CKEDITOR.instances[Drupal.ckeditorActiveId].insertHtml(content);
          }
          else if (drupalSettings.tokenFocusedField) {
            insertAtCursor(drupalSettings.tokenFocusedField, content);
          }
          else {
            alert(Drupal.t('First click a text field to insert your tokens into.'));
          }

          return false;
        });
        $(this).html(newThis);
      });

      function insertAtCursor(editor, content) {
        // Record the current scroll position.
        var scroll = editor.scrollTop;

        // IE support.
        if (document.selection) {
          editor.focus();
          var sel = document.selection.createRange();
          sel.text = content;
        }

        // Mozilla/Firefox/Netscape 7+ support.
        else if (editor.selectionStart || editor.selectionStart == '0') {
          var startPos = editor.selectionStart;
          var endPos = editor.selectionEnd;
          editor.value = editor.value.substring(0, startPos) + content + editor.value.substring(endPos, editor.value.length);
        }

        // Fallback, just add to the end of the content.
        else {
          editor.value += content;
        }

        // Ensure the textarea does not unexpectedly scroll.
        editor.scrollTop = scroll;
      }
    }
  };

})(jQuery, Drupal, drupalSettings);
;
/**
 * @file
 * JavaScript behaviors for roles element integration.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Enhance roles element.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformRoles = {
    attach: function (context) {
      $(context).find('.js-webform-roles-role[value="authenticated"]').once('webform-roles').each(function () {
        var $authenticated = $(this);
        var $checkboxes = $authenticated.parents('.form-checkboxes').find('.js-webform-roles-role').filter(function () {
          return ($(this).val() !== 'anonymous' && $(this).val() !== 'authenticated');
        });

        $authenticated.on('click', function () {
          if ($authenticated.is(':checked')) {
            $checkboxes.prop('checked', true).attr('disabled', true);
          }
          else {
            $checkboxes.prop('checked', false).removeAttr('disabled');
          }
        });

        if ($authenticated.is(':checked')) {
          $checkboxes.prop('checked', true).attr('disabled', true);
        }
      });
    }
  };

})(jQuery, Drupal);
;
/**
 * @file
 * JavaScript behaviors for element #states.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  var isChrome = (/chrom(e|ium)/.test(window.navigator.userAgent.toLowerCase()));

  /**
   * Element #states builder.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformElementStates = {
    attach: function (context) {
      $(context).find('.webform-states-table--condition').once('webform-element-states-condition').each(function () {
        var $condition = $(this);
        var $selector = $condition.find('.webform-states-table--selector select');
        var $value = $condition.find('.webform-states-table--value input');
        var $trigger = $condition.find('.webform-states-table--trigger select');

        // Initialize autocompletion.
        $value.autocomplete({minLength: 0}).on('focus', function () {
          $value.autocomplete('search', '');
        });

        // Initialize trigger and selector.
        $trigger.on('change', function () {$selector.change();});

        $selector.on('change', function () {
          var selector = $selector.val();
          var sourceKey = drupalSettings.webformElementStates.selectors[selector];
          var source = drupalSettings.webformElementStates.sources[sourceKey];
          var notPattern = ($trigger.val().indexOf('pattern') === -1);
          if (source && notPattern) {
            // Enable autocompletion.
            $value
              .autocomplete('option', 'source', source)
              .addClass('form-autocomplete');
          }
          else {
            // Disable autocompletion.
            $value
              .autocomplete('option', 'source', [])
              .removeClass('form-autocomplete');
          }
          // Always disable browser auto completion.
          $value.attr('autocomplete', (isChrome ? 'chrome-off-' + Math.floor(Math.random() * 100000000) : 'off'));
        }).change();
      });

      // If the states:state is required or optional the required checkbox
      // should be checked and disabled.
      var $state = $(context).find('.webform-states-table--state select');
      if ($state.length) {
        $state.once('webform-element-states-state')
          .on('change', toggleRequiredCheckbox);
        toggleRequiredCheckbox();
      }
    }
  };

  /**
   * Track required checked state.
   *
   * @type {null|boolean}
   */
  var requiredChecked = null;

  /**
   * Toggle the required checkbox when states:state is required or optional.
   */
  function toggleRequiredCheckbox() {
    var $input = $('input[name="properties[required]"]');
    if (!$input.length) {
      return;
    }

    // Determine if any states:state is required or optional.
    var required = false;
    $('.webform-states-table--state select').each(function () {
      var value = $(this).val();
      if (value === 'required' || value === 'optional') {
        required = true;
      }
    });

    if (required) {
      requiredChecked = $input.prop('checked');
      $input.attr('disabled', true);
      $input.prop('checked', true);
    }
    else {
      $input.attr('disabled', false);
      if (requiredChecked !== null) {
        $input.prop('checked', requiredChecked);
        requiredChecked = null;
      }
    }
    $input.change();
  }

})(jQuery, Drupal, drupalSettings);
;
