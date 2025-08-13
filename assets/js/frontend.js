(function($){
  $(document).on('change','[name=home_delivery]',function(){
    const on = $(this).is(':checked');
    $('[name=pickup],[name=dropoff]').prop('disabled', on);
  });
})(jQuery);
