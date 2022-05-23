(function ($, drupalSettings) {

    'use strict';

    Drupal.behaviors.calculationExample = {
      attach: function (context, settings) {
        $(context).find('.js-form-type-total-sum').once('total-sum').append('<strong></strong>')

        $(context).find('.js-form-type-total-sum').each(function () {
          var $element = $(this);
          var $form = $(this).closest('form');
          var min = 0.00;
        
          if(drupalSettings.min_value){
            min = drupalSettings.min_value;
          };

          // Calculate initial sum.
          $('.js-form-type-total-sum strong').text(parseFloat(min).toFixed(2) + ' €');

          // Add event handlers
          $('.js-form-type-number input, .js-form-type-select select, .js-form-type-checkbox input').on('change', function(){
            var sum = parseFloat(min);

            $form.find('.js-form-type-select').each(function() {
              var number = parseFloat($(this).parents('.webform-flexbox').find('.js-form-type-number input').val());
              var optionGroup = $(this).find('option:selected').parent().attr('label');
              var price = optionGroup ? parseFloat($(this).find('select').attr(optionGroup)) : 0.00;

              if(drupalSettings.min_value){
                price = 0.00;
              };

              var print = $(this).parents('.webform-flexbox').find('.js-form-type-checkbox input').prop('checked');
              
              if(print){
                price += 4.00;
              }

              if(number > 0 && price > 0){
                sum += price * number;
              }
            });

            $('.js-form-type-total-sum strong').text(sum.toFixed(2) + ' €');
            $('.js-form-type-total-sum input').val(sum.toFixed(2));
          });
        });
      }
    };

  })(jQuery, drupalSettings);
