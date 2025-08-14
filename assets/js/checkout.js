(function($){
  function goto(step){
    $('.amcb-step').attr('hidden', true);
    $('.amcb-step-'+step).attr('hidden', false);
    $('.amcb-steps span').removeClass('on').slice(0, step).addClass('on');
    $('.amcb-checkout-wizard').attr('data-step', step);
    if(step === 4){ updateSummary(); }
    window.scrollTo({top:0,behavior:'smooth'});
  }
  function getContext(){
    return $.extend({}, window.amcbCheckout?.context || {}, {
      payment_mode: $('input[name="paymode"]:checked').val() || 'full'
    });
  }
  function updateSummary(){
    const ctx = getContext();
    ctx._wpnonce = amcbCheckout.nonce;
    fetch(amcbCheckout.restUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(ctx)
    }).then(r => r.json()).then(data => {
      const wrap = $('#amcb-summary');
      wrap.find('.amcb-full').text(data.grand_total || '');
      if(ctx.payment_mode === 'deposit'){
        wrap.find('.amcb-deposit').text(data.deposit_amount || '');
        wrap.find('.amcb-to-collect').text(data.to_collect || '');
        wrap.find('.amcb-deposit-line, .amcb-to-collect-line').removeAttr('hidden');
      } else {
        wrap.find('.amcb-deposit-line, .amcb-to-collect-line').attr('hidden', true);
      }
    }).catch(()=>{});
  }
  $(document).on('click','.amcb-next',function(e){ e.preventDefault();
    const step = parseInt($('.amcb-checkout-wizard').attr('data-step'),10) + 1; goto(step);
  });
  $(document).on('click','.amcb-prev',function(e){ e.preventDefault();
    const step = Math.max(1, parseInt($('.amcb-checkout-wizard').attr('data-step'),10) - 1); goto(step);
  });
  $(document).on('change','.amcb-bill-toggle',function(){ $('.amcb-billing').attr('hidden', !this.checked); });
  $(document).on('change','input[name="paymode"]',updateSummary);
})(jQuery);
