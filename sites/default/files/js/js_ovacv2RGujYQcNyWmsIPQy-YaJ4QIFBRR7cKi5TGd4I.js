(function ($) {
  
    'use strict';
  
    Drupal.behaviors.calculationExample = {
      attach: function (context) {
        $(context).find('.js-form-type-total-sum').once('total-sum').append('<strong>0</strong>')

        $(context).find('.js-form-type-total-sum').each(function () {
          var $element = $(this);
          var $form = $(this).closest('form');
          // Calculate initial sum.
          sum($element);
          // Add event handlers
          $form.find('.js-webform-computed-wrapper :input[type=hidden]').on('change', sum);
        });
      }
    };
  
    function sum() {
      var sum = 0;

      $('.js-webform-computed-wrapper').find(':input[type=hidden]').each(function() {
        sum += parseFloat($(this).val());
      });
 
      $('.js-form-type-total-sum strong').text(sum.toFixed(2));
      $('.js-form-type-total-sum input').val(sum.toFixed(2));
    }
  
  })(jQuery);;
