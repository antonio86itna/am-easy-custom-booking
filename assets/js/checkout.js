(function($){
  function goto(step){
    $('.amcb-step').attr('hidden', true);
    $('.amcb-step-'+step).attr('hidden', false);
    $('.amcb-steps span').removeClass('on').slice(0, step).addClass('on');
    $('.amcb-checkout-wizard').attr('data-step', step);
    window.scrollTo({top:0,behavior:'smooth'});
  }
  $(document).on('click','.amcb-next',function(e){ e.preventDefault();
    const step = parseInt($('.amcb-checkout-wizard').attr('data-step'),10) + 1; goto(step);
  });
  $(document).on('click','.amcb-prev',function(e){ e.preventDefault();
    const step = Math.max(1, parseInt($('.amcb-checkout-wizard').attr('data-step'),10) - 1); goto(step);
  });
  $(document).on('change','.amcb-bill-toggle',function(){ $('.amcb-billing').attr('hidden', !this.checked); });
})(jQuery);
