/**
 * @file
 * JavaScript behaviors for Select2 integration.
 */

(function ($, Drupal) {

  'use strict';

  // @see https://select2.github.io/options.html
  Drupal.webform = Drupal.webform || {};
  Drupal.webform.select2 = Drupal.webform.select2 || {};
  Drupal.webform.select2.options = Drupal.webform.select2.options || {};
  Drupal.webform.select2.options.width = Drupal.webform.select2.options.width || '100%';
  Drupal.webform.select2.options.widthInline = Drupal.webform.select2.options.widthInline || '50%';

  /**
   * Initialize Select2 support.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformSelect2 = {
    attach: function (context) {
      if (!$.fn.select2) {
        return;
      }

      $(context)
        .find('select.js-webform-select2, .js-webform-select2 select')
        .once('webform-select2')
        .each(function () {
          var $select = $(this);

          var options = {};
          if ($select.parents('.webform-element--title-inline').length) {
            options.width = Drupal.webform.select2.options.widthInline;
          }
          options = $.extend(options, Drupal.webform.select2.options);
          if ($select.data('placeholder')) {
            options.placeholder = $select.data('placeholder');
            if (!$select.prop('multiple')) {
              // Allow single option to be deselected.
              options.allowClear = true;
            }
          }
          if ($select.data('limit')) {
            options.maximumSelectionLength = $select.data('limit');
          }

          // Remove required attribute from IE11 which breaks
          // HTML5 clientside validation.
          // @see https://github.com/select2/select2/issues/5114
          if (window.navigator.userAgent.indexOf('Trident/') !== false
            && $select.attr('multiple')
            && $select.attr('required')) {
            $select.removeAttr('required');
          }

          $select.select2(options);
        });

    }
  };

  /**
   * ISSUE:
   * Hiding/showing element via #states API cause select2 dropdown to appear in the wrong position.
   *
   * WORKAROUND:
   * Close (aka hide) select2 dropdown when #states API hides or shows an element.
   *
   * Steps to reproduce:
   * - Add custom 'Submit button(s)'
   * - Hide submit button
   * - Save
   * - Open 'Submit button(s)' dialog
   *
   * Dropdown body is positioned incorrectly when dropdownParent isn't statically positioned.
   * @see https://github.com/select2/select2/issues/3303
   */
  $(function () {
    if ($.fn.select2) {
      $(document).on('state:visible state:visible-slide', function (e) {
        $('select.select2-hidden-accessible').select2('close');
      });
    }

    // Select2 search broken inside jQuery UI 1.10.x modal Dialog.
    // @see https://github.com/select2/select2/issues/1246
    if ($.ui && $.ui.dialog && $.ui.dialog.prototype._allowInteraction) {
      var ui_dialog_interaction = $.ui.dialog.prototype._allowInteraction;
      $.ui.dialog.prototype._allowInteraction = function (e) {
        if ($(e.target).closest('.select2-dropdown').length) {
          return true;
        }
        return ui_dialog_interaction.apply(this, arguments);
      };
    }
  });


})(jQuery, Drupal);
;
/**
 * @file
 * JavaScript behaviors for admin pages.
 */

(function ($, Drupal, debounce) {

  'use strict';

  /**
   * Filter webform autocomplete handler.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformFilterAutocomplete = {
    attach: function (context) {
      $('.webform-filter-form input.form-autocomplete', context).once('webform-autocomplete')
        .each(function () {
          // If input value is an autocomplete match, reset the input to its
          // default value.
          if (/\(([^)]+)\)$/.test(this.value)) {
            this.value = this.defaultValue;
          }

          // From: http://stackoverflow.com/questions/5366068/jquery-ui-autocomplete-submit-onclick-result
          $(this).bind('autocompleteselect', function (event, ui) {
            if (ui.item) {
              $(this).val(ui.item.value);
              this.form.submit();
            }
          });
        });
    }
  };

  /**
   * Allow table rows to be hyperlinked.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformTableRowHref = {
    attach: function (context) {
      // Only attach the click event handler to the entire table and determine
      // which row triggers the event.
      $('.webform-results-table', context).once('webform-results-table').click(function (event) {
        if (event.target.tagName === 'A' || event.target.tagName === 'BUTTON') {
          return true;
        }

        if ($(event.target).parents('a[href]').length) {
          return true;
        }

        var $tr = $(event.target).parents('tr[data-webform-href]');
        if (!$tr.length) {
          return true;
        }

        window.location = $tr.attr('data-webform-href');
        return false;
      });
    }
  };

})(jQuery, Drupal, Drupal.debounce);
;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, debounce, displace) {
  Drupal.offCanvas = {
    position: null,

    minimumHeight: 30,

    minDisplaceWidth: 768,

    $mainCanvasWrapper: $('[data-off-canvas-main-canvas]'),

    isOffCanvas: function isOffCanvas($element) {
      return $element.is('#drupal-off-canvas');
    },
    removeOffCanvasEvents: function removeOffCanvasEvents($element) {
      $element.off('.off-canvas');
      $(document).off('.off-canvas');
      $(window).off('.off-canvas');
    },
    beforeCreate: function beforeCreate(_ref) {
      var settings = _ref.settings,
          $element = _ref.$element;

      Drupal.offCanvas.removeOffCanvasEvents($element);

      $('body').addClass('js-off-canvas-dialog-open');

      settings.position = {
        my: 'left top',
        at: Drupal.offCanvas.getEdge() + ' top',
        of: window
      };

      var position = settings.drupalOffCanvasPosition;
      var height = position === 'side' ? $(window).height() : settings.height;
      var width = position === 'side' ? settings.width : '100%';
      settings.height = height;
      settings.width = width;
    },
    beforeClose: function beforeClose(_ref2) {
      var $element = _ref2.$element;

      $('body').removeClass('js-off-canvas-dialog-open');

      Drupal.offCanvas.removeOffCanvasEvents($element);
      Drupal.offCanvas.resetPadding();
    },
    afterCreate: function afterCreate(_ref3) {
      var $element = _ref3.$element,
          settings = _ref3.settings;

      var eventData = { settings: settings, $element: $element, offCanvasDialog: this };

      $element.on('dialogContentResize.off-canvas', eventData, Drupal.offCanvas.handleDialogResize).on('dialogContentResize.off-canvas', eventData, Drupal.offCanvas.bodyPadding);

      Drupal.offCanvas.getContainer($element).attr('data-offset-' + Drupal.offCanvas.getEdge(), '');

      $(window).on('resize.off-canvas', eventData, debounce(Drupal.offCanvas.resetSize, 100)).trigger('resize.off-canvas');
    },
    render: function render(_ref4) {
      var settings = _ref4.settings;

      $('.ui-dialog-off-canvas, .ui-dialog-off-canvas .ui-dialog-titlebar').toggleClass('ui-dialog-empty-title', !settings.title);
    },
    handleDialogResize: function handleDialogResize(event) {
      var $element = event.data.$element;
      var $container = Drupal.offCanvas.getContainer($element);

      var $offsets = $container.find('> :not(#drupal-off-canvas, .ui-resizable-handle)');
      var offset = 0;

      $element.css({ height: 'auto' });
      var modalHeight = $container.height();

      $offsets.each(function (i, e) {
        offset += $(e).outerHeight();
      });

      var scrollOffset = $element.outerHeight() - $element.height();
      $element.height(modalHeight - offset - scrollOffset);
    },
    resetSize: function resetSize(event) {
      var $element = event.data.$element;
      var container = Drupal.offCanvas.getContainer($element);
      var position = event.data.settings.drupalOffCanvasPosition;

      if (Drupal.offCanvas.position && Drupal.offCanvas.position !== position) {
        container.removeAttr('data-offset-' + Drupal.offCanvas.position);
      }

      if (position === 'top') {
        $element.css('min-height', Drupal.offCanvas.minimumHeight + 'px');
      }

      displace();

      var offsets = displace.offsets;

      var topPosition = position === 'side' && offsets.top !== 0 ? '+' + offsets.top : '';
      var adjustedOptions = {
        position: {
          my: Drupal.offCanvas.getEdge() + ' top',
          at: Drupal.offCanvas.getEdge() + ' top' + topPosition,
          of: window
        }
      };

      var height = position === 'side' ? $(window).height() - (offsets.top + offsets.bottom) + 'px' : event.data.settings.height;
      container.css({
        position: 'fixed',
        height: height
      });

      $element.dialog('option', adjustedOptions).trigger('dialogContentResize.off-canvas');

      Drupal.offCanvas.position = position;
    },
    bodyPadding: function bodyPadding(event) {
      var position = event.data.settings.drupalOffCanvasPosition;
      if (position === 'side' && $('body').outerWidth() < Drupal.offCanvas.minDisplaceWidth) {
        return;
      }
      Drupal.offCanvas.resetPadding();
      var $element = event.data.$element;
      var $container = Drupal.offCanvas.getContainer($element);
      var $mainCanvasWrapper = Drupal.offCanvas.$mainCanvasWrapper;

      var width = $container.outerWidth();
      var mainCanvasPadding = $mainCanvasWrapper.css('padding-' + Drupal.offCanvas.getEdge());
      if (position === 'side' && width !== mainCanvasPadding) {
        $mainCanvasWrapper.css('padding-' + Drupal.offCanvas.getEdge(), width + 'px');
        $container.attr('data-offset-' + Drupal.offCanvas.getEdge(), width);
        displace();
      }

      var height = $container.outerHeight();
      if (position === 'top') {
        $mainCanvasWrapper.css('padding-top', height + 'px');
        $container.attr('data-offset-top', height);
        displace();
      }
    },
    getContainer: function getContainer($element) {
      return $element.dialog('widget');
    },
    getEdge: function getEdge() {
      return document.documentElement.dir === 'rtl' ? 'left' : 'right';
    },
    resetPadding: function resetPadding() {
      Drupal.offCanvas.$mainCanvasWrapper.css('padding-' + Drupal.offCanvas.getEdge(), 0);
      Drupal.offCanvas.$mainCanvasWrapper.css('padding-top', 0);
      displace();
    }
  };

  Drupal.behaviors.offCanvasEvents = {
    attach: function attach() {
      $(window).once('off-canvas').on({
        'dialog:beforecreate': function dialogBeforecreate(event, dialog, $element, settings) {
          if (Drupal.offCanvas.isOffCanvas($element)) {
            Drupal.offCanvas.beforeCreate({ dialog: dialog, $element: $element, settings: settings });
          }
        },
        'dialog:aftercreate': function dialogAftercreate(event, dialog, $element, settings) {
          if (Drupal.offCanvas.isOffCanvas($element)) {
            Drupal.offCanvas.render({ dialog: dialog, $element: $element, settings: settings });
            Drupal.offCanvas.afterCreate({ $element: $element, settings: settings });
          }
        },
        'dialog:beforeclose': function dialogBeforeclose(event, dialog, $element) {
          if (Drupal.offCanvas.isOffCanvas($element)) {
            Drupal.offCanvas.beforeClose({ dialog: dialog, $element: $element });
          }
        }
      });
    }
  };
})(jQuery, Drupal, Drupal.debounce, Drupal.displace);;
/**
 * @file
 * JavaScript behaviors for webform off-canvas dialogs.
 *
 * @see misc/dialog/off-canvas.js
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Attaches webform off-canvas behaviors.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches event listeners to window for off-canvas dialogs.
   */
  Drupal.behaviors.webformOffCanvasEvents = {
    attach: function () {
      // Resize seven.theme tabs when off-canvas dialog opened and closed.
      // @see core/themes/seven/js/nav-tabs.js
      $(window).once('webform-off-canvas').on({
        'dialog:aftercreate': function (event, dialog, $element, settings) {
          if (Drupal.offCanvas.isOffCanvas($element)) {
            $(window).trigger('resize.tabs');
          }
        },
        'dialog:afterclose': function (event, dialog, $element, settings) {
          if (Drupal.offCanvas.isOffCanvas($element)) {
            $(window).trigger('resize.tabs');
          }
        }
      });
    }
  };

  // Append .ckeditor-off-canvas-reset to document to disable ckeditor reset.
  // @see webform_css_alter()
  // @see web/core/modules/ckeditor/js/ckeditor.off-canvas-css-reset.es6.js
  $(document.body).append('<style id="ckeditor-off-canvas-reset"></style>');

})(jQuery, Drupal);
;
/**
 * @file
 * Dropbutton feature.
 */

(function ($, Drupal) {

  'use strict';

  // Make sure that dropButton behavior exists.
  if (!Drupal.behaviors.tableDrag) {
    return;
  }

  $(function () {
    $('head').append('<style type="text/css">.webform-tabledrag-hide {display: table-cell;}</style>');
  });

})(jQuery, Drupal);
;
/**
 * @file
 * JavaScript behaviors for Webform UI.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Remove .button-primary class from .action-links .button-secondary.
   *
   * The seven.theme adds the .button-primary class to all actions.
   *
   * @type {Drupal~behavior}
   *
   * @see webform_ui_preprocess_menu_local_action()
   * @see seven_preprocess_menu_local_action()
   * @see webform_ui.module.css
   */
  Drupal.behaviors.webformUiElementsActionsSecondary = {
    attach: function (context, settings) {
      $(context).find('.action-links .button--secondary').once('webform-ui-elements-webform-actions-secondary').each(function () {
        $(this).removeClass('button--primary');
      });
    }
  };

  /**
   * Adds keyboard support to the form builder.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformUiElementsKeyboard = {
    attach: function (context, settings) {
      var $table = $(context)
        .find('.webform-ui-elements-table')
        .once('webform-ui-elements-keyboard');

      // Disable autosubmit when Enter is pressed on 'Required' checkboxes.
      $table.find('td input:checkbox')
        .on('keyup keypress', function (e) {
          if (e.which === 13) {
            e.preventDefault();
            return false;
          }
        });

      // Move keyboard focus up (38) or down (40).
      $table.find('td:first-child a:not(.tabledrag-handle), td input:checkbox, td .webform-dropbutton li.dropbutton-action a, td .webform-dropbutton button')
        .on('keydown', function (event) {
          if (event.which === 38 || event.which === 40) {
            var $cell = $(this).closest('td');
            var $row = $cell.parent();
            var direction = (event.which === 38) ? 'prev' : 'next';
            var index = $cell.index();
            var tagName = this.tagName;
            while ($row[direction]().length) {
              $row = $row[direction]();
              $cell = $row.find('td').eq(index).find(tagName);
              if ($cell.length) {
                $cell.focus();
                break;
              }
            }
            event.preventDefault();
          }
        });

      // Move keyboard focus left (37) or right (39).
      $table.find('td a:not(.tabledrag-handle), td input, td select, td button')
        .on('keydown', function (event) {
          if (event.which === 37 || event.which === 39) {
            var $cell = $(this).closest('td');
            var direction = (event.which === 37) ? 'prev' : 'next';
            var $focus;


            // Move keyboard focus within operations dropbutton.
            if ($(this).closest('.webform-dropbutton').length) {
              if (direction === 'next' &&
                this.tagName === 'A' &&
                $(this).parent('.dropbutton-action').length) {
                $cell.find('button').focus();
                event.preventDefault();
                return;
              }
              else if (direction === 'prev' && this.tagName === 'BUTTON') {
                $cell.find('a').focus();
                event.preventDefault();
                return;
              }
            }

            while ($cell.length) {
              $cell = $cell[direction]();
              $focus = $cell.find('a:visible, input:visible, select:visible');
              if ($focus.length) {
                $focus.focus();
                event.preventDefault();
                return;
              }
            }
          }

        });
    }
  };

  /**
   * Monitor the element's key (aka machine name).
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformUiElementKey = {
    attach: function (context) {
      if (!drupalSettings.webform_ui ||
        !drupalSettings.webform_ui.reserved_keys ||
        !$(context).find(':input[name="key"]').length) {
        return;
      }

      // Monitor the machine name and display a warning when a reserved word is
      // being used.
      // There is no way to capture changes to the key val.
      // @see core/misc/machine-name.js.
      var currentKey;
      setInterval(function () {
        var value = $(':input[name="key"]').val();
        if (value === currentKey) {
          return;
        }
        currentKey = value;

        if ($.inArray(value, drupalSettings.webform_ui.reserved_keys) !== -1) {
          // Customize and display the warning message.
          $('[data-drupal-selector="edit-key-warning"]').show();
          $('#webform-ui-reserved-key-warning').html(
            Drupal.t("Please avoid using the reserved word '@key' as the element's key.", {'@key': value})
          );
        }
        else {
          // Hide the warning message.
          $('[data-drupal-selector="edit-key-warning"]').hide();
        }

      }, 300);
    }
  };

})(jQuery, Drupal, drupalSettings);
;
/**
 * @file
 * JavaScript behaviors for details element.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.webform = Drupal.webform || {};
  Drupal.webform.detailsToggle = Drupal.webform.detailsToggle || {};
  Drupal.webform.detailsToggle.options = Drupal.webform.detailsToggle.options || {};

  /**
   * Attach handler to toggle details open/close state.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformDetailsToggle = {
    attach: function (context) {
      $('.js-webform-details-toggle', context).once('webform-details-toggle').each(function () {
        var $form = $(this);
        var $tabs = $form.find('.webform-tabs');

        // Get only the main details elements and ignore all nested details.
        var selector = ($tabs.length) ? '.webform-tab' : '.js-webform-details-toggle';
        var $details = $form.find('details').filter(function () {
          var $parents = $(this).parentsUntil(selector);
          return ($parents.find('details').length === 0);
        });

        // Toggle is only useful when there are two or more details elements.
        if ($details.length < 2) {
          return;
        }

        var options = $.extend({
          button: '<button type="button" class="webform-details-toggle-state"></button>'
        }, Drupal.webform.detailsToggle.options);

        // Create toggle buttons.
        var $toggle = $(options.button)
          .attr('title', Drupal.t('Toggle details widget state.'))
          .on('click', function (e) {
            var open;
            if (Drupal.webform.detailsToggle.isFormDetailsOpen($form)) {
              $form.find('details').removeAttr('open');
              open = 0;
            }
            else {
              $form.find('details').attr('open', 'open');
              open = 1;
            }
            Drupal.webform.detailsToggle.setDetailsToggleLabel($form);

            // Set the saved states for all the details elements.
            // @see webform.element.details.save.js
            if (Drupal.webformDetailsSaveGetName) {
              $form.find('details').each(function () {
                var name = Drupal.webformDetailsSaveGetName($(this));
                if (name) {
                  localStorage.setItem(name, open);
                }
              });
            }
          })
          .wrap('<div class="webform-details-toggle-state-wrapper"></div>')
          .parent();

        if ($tabs.length) {
          // Add toggle state before the tabs.
          $tabs.find('.item-list:first-child').eq(0).before($toggle);
        }
        else {
          // Add toggle state link to first details element.
          $details.eq(0).before($toggle);
        }

        Drupal.webform.detailsToggle.setDetailsToggleLabel($form);
      });
    }
  };

  /**
   * Determine if a webform's details are all opened.
   *
   * @param {jQuery} $form
   *   A webform.
   *
   * @return {boolean}
   *   TRUE if a webform's details are all opened.
   */
  Drupal.webform.detailsToggle.isFormDetailsOpen = function ($form) {
    return ($form.find('details[open]').length === $form.find('details').length);
  };

  /**
   * Set a webform's details toggle state widget label.
   *
   * @param {jQuery} $form
   *   A webform.
   */
  Drupal.webform.detailsToggle.setDetailsToggleLabel = function ($form) {
    var isOpen = Drupal.webform.detailsToggle.isFormDetailsOpen($form);

    var label = (isOpen) ? Drupal.t('Collapse all') : Drupal.t('Expand all');
    $form.find('.webform-details-toggle-state').html(label);

    var text = (isOpen) ? Drupal.t('All details have been expanded.') : Drupal.t('All details have been collapsed.');
    Drupal.announce(text);
  };

})(jQuery, Drupal);
;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, window) {
  function TableResponsive(table) {
    this.table = table;
    this.$table = $(table);
    this.showText = Drupal.t('Show all columns');
    this.hideText = Drupal.t('Hide lower priority columns');

    this.$headers = this.$table.find('th');

    this.$link = $('<button type="button" class="link tableresponsive-toggle"></button>').attr('title', Drupal.t('Show table cells that were hidden to make the table fit within a small screen.')).on('click', $.proxy(this, 'eventhandlerToggleColumns'));

    this.$table.before($('<div class="tableresponsive-toggle-columns"></div>').append(this.$link));

    $(window).on('resize.tableresponsive', $.proxy(this, 'eventhandlerEvaluateColumnVisibility')).trigger('resize.tableresponsive');
  }

  Drupal.behaviors.tableResponsive = {
    attach: function attach(context, settings) {
      var $tables = $(context).find('table.responsive-enabled').once('tableresponsive');
      if ($tables.length) {
        var il = $tables.length;
        for (var i = 0; i < il; i++) {
          TableResponsive.tables.push(new TableResponsive($tables[i]));
        }
      }
    }
  };

  $.extend(TableResponsive, {
    tables: []
  });

  $.extend(TableResponsive.prototype, {
    eventhandlerEvaluateColumnVisibility: function eventhandlerEvaluateColumnVisibility(e) {
      var pegged = parseInt(this.$link.data('pegged'), 10);
      var hiddenLength = this.$headers.filter('.priority-medium:hidden, .priority-low:hidden').length;

      if (hiddenLength > 0) {
        this.$link.show().text(this.showText);
      }

      if (!pegged && hiddenLength === 0) {
        this.$link.hide().text(this.hideText);
      }
    },
    eventhandlerToggleColumns: function eventhandlerToggleColumns(e) {
      e.preventDefault();
      var self = this;
      var $hiddenHeaders = this.$headers.filter('.priority-medium:hidden, .priority-low:hidden');
      this.$revealedCells = this.$revealedCells || $();

      if ($hiddenHeaders.length > 0) {
        $hiddenHeaders.each(function (index, element) {
          var $header = $(this);
          var position = $header.prevAll('th').length;
          self.$table.find('tbody tr').each(function () {
            var $cells = $(this).find('td').eq(position);
            $cells.show();

            self.$revealedCells = $().add(self.$revealedCells).add($cells);
          });
          $header.show();

          self.$revealedCells = $().add(self.$revealedCells).add($header);
        });
        this.$link.text(this.hideText).data('pegged', 1);
      } else {
          this.$revealedCells.hide();

          this.$revealedCells.each(function (index, element) {
            var $cell = $(this);
            var properties = $cell.attr('style').split(';');
            var newProps = [];

            var match = /^display\s*:\s*none$/;
            for (var i = 0; i < properties.length; i++) {
              var prop = properties[i];
              prop.trim();

              var isDisplayNone = match.exec(prop);
              if (isDisplayNone) {
                continue;
              }
              newProps.push(prop);
            }

            $cell.attr('style', newProps.join(';'));
          });
          this.$link.text(this.showText).data('pegged', 0);

          $(window).trigger('resize.tableresponsive');
        }
    }
  });

  Drupal.TableResponsive = TableResponsive;
})(jQuery, Drupal, window);;
/**
 * @file
 * JavaScript behaviors for help.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Handles disabling help dialog for mobile devices.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for disabling help dialog for mobile devices.
   */
  Drupal.behaviors.webformHelpDialog = {
    attach: function (context) {
      $(context).find('.button-webform-play').once('webform-help-dialog').on('click', function (event) {
        if ($(window).width() < 768) {
          event.stopImmediatePropagation();
        }
      }).each(function () {
        // Must make sure that this click event handler is execute first and
        // before the Ajax dialog handler.
        // @see http://stackoverflow.com/questions/2360655/jquery-event-handlers-always-execute-in-order-they-were-bound-any-way-around-t
        var handlers = $._data(this, 'events')['click'];
        var handler = handlers.pop();
        // Move it at the beginning.
        handlers.splice(0, 0, handler);
      });
    }
  };

})(jQuery, Drupal);
;
window.matchMedia||(window.matchMedia=function(){"use strict";var e=window.styleMedia||window.media;if(!e){var t=document.createElement("style"),i=document.getElementsByTagName("script")[0],n=null;t.type="text/css";t.id="matchmediajs-test";i.parentNode.insertBefore(t,i);n="getComputedStyle"in window&&window.getComputedStyle(t,null)||t.currentStyle;e={matchMedium:function(e){var i="@media "+e+"{ #matchmediajs-test { width: 1px; } }";if(t.styleSheet){t.styleSheet.cssText=i}else{t.textContent=i}return n.width==="1px"}}}return function(t){return{matches:e.matchMedium(t||"all"),media:t||"all"}}}());
;
(function(){if(window.matchMedia&&window.matchMedia("all").addListener){return false}var e=window.matchMedia,i=e("only all").matches,n=false,t=0,a=[],r=function(i){clearTimeout(t);t=setTimeout(function(){for(var i=0,n=a.length;i<n;i++){var t=a[i].mql,r=a[i].listeners||[],o=e(t.media).matches;if(o!==t.matches){t.matches=o;for(var s=0,l=r.length;s<l;s++){r[s].call(window,t)}}}},30)};window.matchMedia=function(t){var o=e(t),s=[],l=0;o.addListener=function(e){if(!i){return}if(!n){n=true;window.addEventListener("resize",r,true)}if(l===0){l=a.push({mql:o,listeners:s})}s.push(e)};o.removeListener=function(e){for(var i=0,n=s.length;i<n;i++){if(s[i]===e){s.splice(i,1)}}};return o}})();
;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, drupalSettings) {
  var activeItem = Drupal.url(drupalSettings.path.currentPath);

  $.fn.drupalToolbarMenu = function () {
    var ui = {
      handleOpen: Drupal.t('Extend'),
      handleClose: Drupal.t('Collapse')
    };

    function toggleList($item, switcher) {
      var $toggle = $item.children('.toolbar-box').children('.toolbar-handle');
      switcher = typeof switcher !== 'undefined' ? switcher : !$item.hasClass('open');

      $item.toggleClass('open', switcher);

      $toggle.toggleClass('open', switcher);

      $toggle.find('.action').text(switcher ? ui.handleClose : ui.handleOpen);
    }

    function toggleClickHandler(event) {
      var $toggle = $(event.target);
      var $item = $toggle.closest('li');

      toggleList($item);

      var $openItems = $item.siblings().filter('.open');
      toggleList($openItems, false);
    }

    function linkClickHandler(event) {
      if (!Drupal.toolbar.models.toolbarModel.get('isFixed')) {
        Drupal.toolbar.models.toolbarModel.set('activeTab', null);
      }

      event.stopPropagation();
    }

    function initItems($menu) {
      var options = {
        class: 'toolbar-icon toolbar-handle',
        action: ui.handleOpen,
        text: ''
      };

      $menu.find('li > a').wrap('<div class="toolbar-box">');

      $menu.find('li').each(function (index, element) {
        var $item = $(element);
        if ($item.children('ul.toolbar-menu').length) {
          var $box = $item.children('.toolbar-box');
          options.text = Drupal.t('@label', {
            '@label': $box.find('a').text()
          });
          $item.children('.toolbar-box').append(Drupal.theme('toolbarMenuItemToggle', options));
        }
      });
    }

    function markListLevels($lists, level) {
      level = !level ? 1 : level;
      var $lis = $lists.children('li').addClass('level-' + level);
      $lists = $lis.children('ul');
      if ($lists.length) {
        markListLevels($lists, level + 1);
      }
    }

    function openActiveItem($menu) {
      var pathItem = $menu.find('a[href="' + window.location.pathname + '"]');
      if (pathItem.length && !activeItem) {
        activeItem = window.location.pathname;
      }
      if (activeItem) {
        var $activeItem = $menu.find('a[href="' + activeItem + '"]').addClass('menu-item--active');
        var $activeTrail = $activeItem.parentsUntil('.root', 'li').addClass('menu-item--active-trail');
        toggleList($activeTrail, true);
      }
    }

    return this.each(function (selector) {
      var $menu = $(this).once('toolbar-menu');
      if ($menu.length) {
        $menu.on('click.toolbar', '.toolbar-box', toggleClickHandler).on('click.toolbar', '.toolbar-box a', linkClickHandler);

        $menu.addClass('root');
        initItems($menu);
        markListLevels($menu);

        openActiveItem($menu);
      }
    });
  };

  Drupal.theme.toolbarMenuItemToggle = function (options) {
    return '<button class="' + options.class + '"><span class="action">' + options.action + '</span> <span class="label">' + options.text + '</span></button>';
  };
})(jQuery, Drupal, drupalSettings);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, drupalSettings) {
  var options = $.extend({
    breakpoints: {
      'toolbar.narrow': '',
      'toolbar.standard': '',
      'toolbar.wide': ''
    }
  }, drupalSettings.toolbar, {
    strings: {
      horizontal: Drupal.t('Horizontal orientation'),
      vertical: Drupal.t('Vertical orientation')
    }
  });

  Drupal.behaviors.toolbar = {
    attach: function attach(context) {
      if (!window.matchMedia('only screen').matches) {
        return;
      }

      $(context).find('#toolbar-administration').once('toolbar').each(function () {
        var model = new Drupal.toolbar.ToolbarModel({
          locked: JSON.parse(localStorage.getItem('Drupal.toolbar.trayVerticalLocked')),
          activeTab: document.getElementById(JSON.parse(localStorage.getItem('Drupal.toolbar.activeTabID'))),
          height: $('#toolbar-administration').outerHeight()
        });

        Drupal.toolbar.models.toolbarModel = model;

        Object.keys(options.breakpoints).forEach(function (label) {
          var mq = options.breakpoints[label];
          var mql = window.matchMedia(mq);
          Drupal.toolbar.mql[label] = mql;

          mql.addListener(Drupal.toolbar.mediaQueryChangeHandler.bind(null, model, label));

          Drupal.toolbar.mediaQueryChangeHandler.call(null, model, label, mql);
        });

        Drupal.toolbar.views.toolbarVisualView = new Drupal.toolbar.ToolbarVisualView({
          el: this,
          model: model,
          strings: options.strings
        });
        Drupal.toolbar.views.toolbarAuralView = new Drupal.toolbar.ToolbarAuralView({
          el: this,
          model: model,
          strings: options.strings
        });
        Drupal.toolbar.views.bodyVisualView = new Drupal.toolbar.BodyVisualView({
          el: this,
          model: model
        });

        model.trigger('change:isFixed', model, model.get('isFixed'));
        model.trigger('change:activeTray', model, model.get('activeTray'));

        var menuModel = new Drupal.toolbar.MenuModel();
        Drupal.toolbar.models.menuModel = menuModel;
        Drupal.toolbar.views.menuVisualView = new Drupal.toolbar.MenuVisualView({
          el: $(this).find('.toolbar-menu-administration').get(0),
          model: menuModel,
          strings: options.strings
        });

        Drupal.toolbar.setSubtrees.done(function (subtrees) {
          menuModel.set('subtrees', subtrees);
          var theme = drupalSettings.ajaxPageState.theme;
          localStorage.setItem('Drupal.toolbar.subtrees.' + theme, JSON.stringify(subtrees));

          model.set('areSubtreesLoaded', true);
        });

        Drupal.toolbar.views.toolbarVisualView.loadSubtrees();

        $(document).on('drupalViewportOffsetChange.toolbar', function (event, offsets) {
          model.set('offsets', offsets);
        });

        model.on('change:orientation', function (model, orientation) {
          $(document).trigger('drupalToolbarOrientationChange', orientation);
        }).on('change:activeTab', function (model, tab) {
          $(document).trigger('drupalToolbarTabChange', tab);
        }).on('change:activeTray', function (model, tray) {
          $(document).trigger('drupalToolbarTrayChange', tray);
        });

        if (Drupal.toolbar.models.toolbarModel.get('orientation') === 'horizontal' && Drupal.toolbar.models.toolbarModel.get('activeTab') === null) {
          Drupal.toolbar.models.toolbarModel.set({
            activeTab: $('.toolbar-bar .toolbar-tab:not(.home-toolbar-tab) a').get(0)
          });
        }

        $(window).on({
          'dialog:aftercreate': function dialogAftercreate(event, dialog, $element, settings) {
            var $toolbar = $('#toolbar-bar');
            $toolbar.css('margin-top', '0');

            if (settings.drupalOffCanvasPosition === 'top') {
              var height = Drupal.offCanvas.getContainer($element).outerHeight();
              $toolbar.css('margin-top', height + 'px');

              $element.on('dialogContentResize.off-canvas', function () {
                var newHeight = Drupal.offCanvas.getContainer($element).outerHeight();
                $toolbar.css('margin-top', newHeight + 'px');
              });
            }
          },
          'dialog:beforeclose': function dialogBeforeclose() {
            $('#toolbar-bar').css('margin-top', '0');
          }
        });
      });
    }
  };

  Drupal.toolbar = {
    views: {},

    models: {},

    mql: {},

    setSubtrees: new $.Deferred(),

    mediaQueryChangeHandler: function mediaQueryChangeHandler(model, label, mql) {
      switch (label) {
        case 'toolbar.narrow':
          model.set({
            isOriented: mql.matches,
            isTrayToggleVisible: false
          });

          if (!mql.matches || !model.get('orientation')) {
            model.set({ orientation: 'vertical' }, { validate: true });
          }
          break;

        case 'toolbar.standard':
          model.set({
            isFixed: mql.matches
          });
          break;

        case 'toolbar.wide':
          model.set({
            orientation: mql.matches && !model.get('locked') ? 'horizontal' : 'vertical'
          }, { validate: true });

          model.set({
            isTrayToggleVisible: mql.matches
          });
          break;

        default:
          break;
      }
    }
  };

  Drupal.theme.toolbarOrientationToggle = function () {
    return '<div class="toolbar-toggle-orientation"><div class="toolbar-lining">' + '<button class="toolbar-icon" type="button"></button>' + '</div></div>';
  };

  Drupal.AjaxCommands.prototype.setToolbarSubtrees = function (ajax, response, status) {
    Drupal.toolbar.setSubtrees.resolve(response.subtrees);
  };
})(jQuery, Drupal, drupalSettings);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function (Backbone, Drupal) {
  Drupal.toolbar.MenuModel = Backbone.Model.extend({
    defaults: {
      subtrees: {}
    }
  });
})(Backbone, Drupal);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function (Backbone, Drupal) {
  Drupal.toolbar.ToolbarModel = Backbone.Model.extend({
    defaults: {
      activeTab: null,

      activeTray: null,

      isOriented: false,

      isFixed: false,

      areSubtreesLoaded: false,

      isViewportOverflowConstrained: false,

      orientation: 'horizontal',

      locked: false,

      isTrayToggleVisible: true,

      height: null,

      offsets: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      }
    },

    validate: function validate(attributes, options) {
      if (attributes.orientation === 'horizontal' && this.get('locked') && !options.override) {
        return Drupal.t('The toolbar cannot be set to a horizontal orientation when it is locked.');
      }
    }
  });
})(Backbone, Drupal);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, Backbone) {
  Drupal.toolbar.BodyVisualView = Backbone.View.extend({
    initialize: function initialize() {
      this.listenTo(this.model, 'change:activeTray ', this.render);
      this.listenTo(this.model, 'change:isFixed change:isViewportOverflowConstrained', this.isToolbarFixed);
    },
    isToolbarFixed: function isToolbarFixed() {
      var isViewportOverflowConstrained = this.model.get('isViewportOverflowConstrained');
      $('body').toggleClass('toolbar-fixed', isViewportOverflowConstrained || this.model.get('isFixed'));
    },
    render: function render() {
      $('body').toggleClass('toolbar-tray-open', !!this.model.get('activeTray'));
    }
  });
})(jQuery, Drupal, Backbone);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Backbone, Drupal) {
  Drupal.toolbar.MenuVisualView = Backbone.View.extend({
    initialize: function initialize() {
      this.listenTo(this.model, 'change:subtrees', this.render);
    },
    render: function render() {
      var _this = this;

      var subtrees = this.model.get('subtrees');

      Object.keys(subtrees || {}).forEach(function (id) {
        _this.$el.find('#toolbar-link-' + id).once('toolbar-subtrees').after(subtrees[id]);
      });

      if ('drupalToolbarMenu' in $.fn) {
        this.$el.children('.toolbar-menu').drupalToolbarMenu();
      }
    }
  });
})(jQuery, Backbone, Drupal);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function (Backbone, Drupal) {
  Drupal.toolbar.ToolbarAuralView = Backbone.View.extend({
    initialize: function initialize(options) {
      this.strings = options.strings;

      this.listenTo(this.model, 'change:orientation', this.onOrientationChange);
      this.listenTo(this.model, 'change:activeTray', this.onActiveTrayChange);
    },
    onOrientationChange: function onOrientationChange(model, orientation) {
      Drupal.announce(Drupal.t('Tray orientation changed to @orientation.', {
        '@orientation': orientation
      }));
    },
    onActiveTrayChange: function onActiveTrayChange(model, tray) {
      var relevantTray = tray === null ? model.previous('activeTray') : tray;

      if (!relevantTray) {
        return;
      }
      var action = tray === null ? Drupal.t('closed') : Drupal.t('opened');
      var trayNameElement = relevantTray.querySelector('.toolbar-tray-name');
      var text = void 0;
      if (trayNameElement !== null) {
        text = Drupal.t('Tray "@tray" @action.', {
          '@tray': trayNameElement.textContent,
          '@action': action
        });
      } else {
        text = Drupal.t('Tray @action.', { '@action': action });
      }
      Drupal.announce(text);
    }
  });
})(Backbone, Drupal);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, drupalSettings, Backbone) {
  Drupal.toolbar.ToolbarVisualView = Backbone.View.extend({
    events: function events() {
      var touchEndToClick = function touchEndToClick(event) {
        event.preventDefault();
        event.target.click();
      };

      return {
        'click .toolbar-bar .toolbar-tab .trigger': 'onTabClick',
        'click .toolbar-toggle-orientation button': 'onOrientationToggleClick',
        'touchend .toolbar-bar .toolbar-tab .trigger': touchEndToClick,
        'touchend .toolbar-toggle-orientation button': touchEndToClick
      };
    },
    initialize: function initialize(options) {
      this.strings = options.strings;

      this.listenTo(this.model, 'change:activeTab change:orientation change:isOriented change:isTrayToggleVisible', this.render);
      this.listenTo(this.model, 'change:mqMatches', this.onMediaQueryChange);
      this.listenTo(this.model, 'change:offsets', this.adjustPlacement);
      this.listenTo(this.model, 'change:activeTab change:orientation change:isOriented', this.updateToolbarHeight);

      this.$el.find('.toolbar-tray .toolbar-lining').append(Drupal.theme('toolbarOrientationToggle'));

      this.model.trigger('change:activeTab');
    },
    updateToolbarHeight: function updateToolbarHeight() {
      var toolbarTabOuterHeight = $('#toolbar-bar').find('.toolbar-tab').outerHeight() || 0;
      var toolbarTrayHorizontalOuterHeight = $('.is-active.toolbar-tray-horizontal').outerHeight() || 0;
      this.model.set('height', toolbarTabOuterHeight + toolbarTrayHorizontalOuterHeight);

      $('body').css({
        'padding-top': this.model.get('height')
      });

      this.triggerDisplace();
    },
    triggerDisplace: function triggerDisplace() {
      _.defer(function () {
        Drupal.displace(true);
      });
    },
    render: function render() {
      this.updateTabs();
      this.updateTrayOrientation();
      this.updateBarAttributes();

      $('body').removeClass('toolbar-loading');

      if (this.model.changed.orientation === 'vertical' || this.model.changed.activeTab) {
        this.loadSubtrees();
      }

      return this;
    },
    onTabClick: function onTabClick(event) {
      if (event.currentTarget.hasAttribute('data-toolbar-tray')) {
        var activeTab = this.model.get('activeTab');
        var clickedTab = event.currentTarget;

        this.model.set('activeTab', !activeTab || clickedTab !== activeTab ? clickedTab : null);

        event.preventDefault();
        event.stopPropagation();
      }
    },
    onOrientationToggleClick: function onOrientationToggleClick(event) {
      var orientation = this.model.get('orientation');

      var antiOrientation = orientation === 'vertical' ? 'horizontal' : 'vertical';
      var locked = antiOrientation === 'vertical';

      if (locked) {
        localStorage.setItem('Drupal.toolbar.trayVerticalLocked', 'true');
      } else {
        localStorage.removeItem('Drupal.toolbar.trayVerticalLocked');
      }

      this.model.set({
        locked: locked,
        orientation: antiOrientation
      }, {
        validate: true,
        override: true
      });

      event.preventDefault();
      event.stopPropagation();
    },
    updateTabs: function updateTabs() {
      var $tab = $(this.model.get('activeTab'));

      $(this.model.previous('activeTab')).removeClass('is-active').prop('aria-pressed', false);

      $(this.model.previous('activeTray')).removeClass('is-active');

      if ($tab.length > 0) {
        $tab.addClass('is-active').prop('aria-pressed', true);
        var name = $tab.attr('data-toolbar-tray');

        var id = $tab.get(0).id;
        if (id) {
          localStorage.setItem('Drupal.toolbar.activeTabID', JSON.stringify(id));
        }

        var $tray = this.$el.find('[data-toolbar-tray="' + name + '"].toolbar-tray');
        if ($tray.length) {
          $tray.addClass('is-active');
          this.model.set('activeTray', $tray.get(0));
        } else {
          this.model.set('activeTray', null);
        }
      } else {
        this.model.set('activeTray', null);
        localStorage.removeItem('Drupal.toolbar.activeTabID');
      }
    },
    updateBarAttributes: function updateBarAttributes() {
      var isOriented = this.model.get('isOriented');
      if (isOriented) {
        this.$el.find('.toolbar-bar').attr('data-offset-top', '');
      } else {
        this.$el.find('.toolbar-bar').removeAttr('data-offset-top');
      }

      this.$el.toggleClass('toolbar-oriented', isOriented);
    },
    updateTrayOrientation: function updateTrayOrientation() {
      var orientation = this.model.get('orientation');

      var antiOrientation = orientation === 'vertical' ? 'horizontal' : 'vertical';

      $('body').toggleClass('toolbar-vertical', orientation === 'vertical').toggleClass('toolbar-horizontal', orientation === 'horizontal');

      var removeClass = antiOrientation === 'horizontal' ? 'toolbar-tray-horizontal' : 'toolbar-tray-vertical';
      var $trays = this.$el.find('.toolbar-tray').removeClass(removeClass).addClass('toolbar-tray-' + orientation);

      var iconClass = 'toolbar-icon-toggle-' + orientation;
      var iconAntiClass = 'toolbar-icon-toggle-' + antiOrientation;
      var $orientationToggle = this.$el.find('.toolbar-toggle-orientation').toggle(this.model.get('isTrayToggleVisible'));
      $orientationToggle.find('button').val(antiOrientation).attr('title', this.strings[antiOrientation]).text(this.strings[antiOrientation]).removeClass(iconClass).addClass(iconAntiClass);

      var dir = document.documentElement.dir;
      var edge = dir === 'rtl' ? 'right' : 'left';

      $trays.removeAttr('data-offset-left data-offset-right data-offset-top');

      $trays.filter('.toolbar-tray-vertical.is-active').attr('data-offset-' + edge, '');

      $trays.filter('.toolbar-tray-horizontal.is-active').attr('data-offset-top', '');
    },
    adjustPlacement: function adjustPlacement() {
      var $trays = this.$el.find('.toolbar-tray');
      if (!this.model.get('isOriented')) {
        $trays.removeClass('toolbar-tray-horizontal').addClass('toolbar-tray-vertical');
      }
    },
    loadSubtrees: function loadSubtrees() {
      var $activeTab = $(this.model.get('activeTab'));
      var orientation = this.model.get('orientation');

      if (!this.model.get('areSubtreesLoaded') && typeof $activeTab.data('drupal-subtrees') !== 'undefined' && orientation === 'vertical') {
        var subtreesHash = drupalSettings.toolbar.subtreesHash;
        var theme = drupalSettings.ajaxPageState.theme;
        var endpoint = Drupal.url('toolbar/subtrees/' + subtreesHash);
        var cachedSubtreesHash = localStorage.getItem('Drupal.toolbar.subtreesHash.' + theme);
        var cachedSubtrees = JSON.parse(localStorage.getItem('Drupal.toolbar.subtrees.' + theme));
        var isVertical = this.model.get('orientation') === 'vertical';

        if (isVertical && subtreesHash === cachedSubtreesHash && cachedSubtrees) {
          Drupal.toolbar.setSubtrees.resolve(cachedSubtrees);
        } else if (isVertical) {
            localStorage.removeItem('Drupal.toolbar.subtreesHash.' + theme);
            localStorage.removeItem('Drupal.toolbar.subtrees.' + theme);

            Drupal.ajax({ url: endpoint }).execute();

            localStorage.setItem('Drupal.toolbar.subtreesHash.' + theme, subtreesHash);
          }
      }
    }
  });
})(jQuery, Drupal, drupalSettings, Backbone);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, drupalSettings) {
  var pathInfo = drupalSettings.path;
  var escapeAdminPath = sessionStorage.getItem('escapeAdminPath');
  var windowLocation = window.location;

  if (!pathInfo.currentPathIsAdmin && !/destination=/.test(windowLocation.search)) {
    sessionStorage.setItem('escapeAdminPath', windowLocation);
  }

  Drupal.behaviors.escapeAdmin = {
    attach: function attach() {
      var $toolbarEscape = $('[data-toolbar-escape-admin]').once('escapeAdmin');
      if ($toolbarEscape.length && pathInfo.currentPathIsAdmin) {
        if (escapeAdminPath !== null) {
          $toolbarEscape.attr('href', escapeAdminPath);
        } else {
          $toolbarEscape.text(Drupal.t('Home'));
        }
      }
    }
  };
})(jQuery, Drupal, drupalSettings);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, drupalSettings) {
  function mapTextContentToAjaxResponse(content) {
    if (content === '') {
      return false;
    }

    try {
      return JSON.parse(content);
    } catch (e) {
      return false;
    }
  }

  function bigPipeProcessPlaceholderReplacement(index, placeholderReplacement) {
    var placeholderId = placeholderReplacement.getAttribute('data-big-pipe-replacement-for-placeholder-with-id');
    var content = this.textContent.trim();

    if (typeof drupalSettings.bigPipePlaceholderIds[placeholderId] !== 'undefined') {
      var response = mapTextContentToAjaxResponse(content);

      if (response === false) {
        $(this).removeOnce('big-pipe');
      } else {
        var ajaxObject = Drupal.ajax({
          url: '',
          base: false,
          element: false,
          progress: false
        });

        ajaxObject.success(response, 'success');
      }
    }
  }

  var interval = drupalSettings.bigPipeInterval || 50;

  var timeoutID = void 0;

  function bigPipeProcessDocument(context) {
    if (!context.querySelector('script[data-big-pipe-event="start"]')) {
      return false;
    }

    $(context).find('script[data-big-pipe-replacement-for-placeholder-with-id]').once('big-pipe').each(bigPipeProcessPlaceholderReplacement);

    if (context.querySelector('script[data-big-pipe-event="stop"]')) {
      if (timeoutID) {
        clearTimeout(timeoutID);
      }
      return true;
    }

    return false;
  }

  function bigPipeProcess() {
    timeoutID = setTimeout(function () {
      if (!bigPipeProcessDocument(document)) {
        bigPipeProcess();
      }
    }, interval);
  }

  bigPipeProcess();

  $(window).on('load', function () {
    if (timeoutID) {
      clearTimeout(timeoutID);
    }
    bigPipeProcessDocument(document);
  });
})(jQuery, Drupal, drupalSettings);;
