(function ($) {

    'use strict';

    Drupal.behaviors.calculationExample = {
      attach: function (context, settings) {
        $(context).find('.js-form-type-total-sum').once('total-sum').append('<strong></strong>')

        $(context).find('.js-form-type-total-sum').each(function () {
          var element = $(this);
          var form = $(this).closest('form');
          var min = 0;
        
          if(settings.min_value){
            min = settings.min_value
          };
          // Calculate initial sum.
          $('.js-form-type-total-sum input').val(parseFloat(min).toFixed(2));
          $('.js-form-type-total-sum strong').text(parseFloat(min).toFixed(2) + ' €');

          // Add event handlers
          if(!settings.min_value){
            $('.js-form-type-number input, .js-form-type-select select').on('change', function(){
              var sum = parseFloat(min);
  
              form.find('.js-form-type-number').each(function() {
                var number = $(this).find('input').val();
                var price = $(this).parent().find('.js-form-type-select option:selected').val();
  
                if(number.length > 0 && price.length > 0){
                  sum += parseFloat(price)*parseFloat(number);
                }
              });
  
              $('.js-form-type-total-sum strong').text(sum.toFixed(2) + ' €');
              $('.js-form-type-total-sum input').val(sum.toFixed(2));
            });
          }
        });
      }
    };

  })(jQuery);
