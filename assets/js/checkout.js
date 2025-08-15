(function($){
  const { __ } = wp.i18n;

  let holdTimer = null;

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
  function startHoldCountdown(expiresAt){
    const expiry = new Date(expiresAt).getTime();

    if(!$('#amcb-summary .amcb-hold-timer').length){
      $('#amcb-summary').append('<p class="amcb-hold-timer">'+ __("Time remaining",'amcb') +': <span></span></p>');
    }
    if(!$('.amcb-step-5 .amcb-hold-timer').length){
      $('.amcb-step-5').prepend('<p class="amcb-hold-timer">'+ __("Time remaining",'amcb') +': <span></span></p>');
    }

    const spans = $('.amcb-hold-timer span');

    function tick(){
      const diff = Math.max(0, Math.floor((expiry - Date.now()) / 1000));
      const min = String(Math.floor(diff / 60)).padStart(2,'0');
      const sec = String(diff % 60).padStart(2,'0');
      spans.text(min+':'+sec);
      if(diff <= 0){
        clearInterval(holdTimer);
        spans.text(__('Expired','amcb'));
        $('.amcb-btn-success').prop('disabled', true);
      }
    }

    tick();
    holdTimer = setInterval(tick,1000);
  }

  $(document).on('click','.amcb-next',function(e){ e.preventDefault();
    const step = parseInt($('.amcb-checkout-wizard').attr('data-step'),10);
    if(step === 4){
      const ctx = getContext();
      ctx._wpnonce = amcbCheckout.nonce;
      fetch(amcbCheckout.prepareUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(ctx)
      }).then(r => {
        if(r.status === 422){
          return r.json().then(data => Promise.reject(data));
        }
        return r.json();
      }).then(data => {
        if(data.hold_expires_at){ startHoldCountdown(data.hold_expires_at); }
        goto(step + 1);
      }).catch(err => {
        let msg = err?.message;
        if(err?.code === 'NO_AVAILABILITY'){
          msg = __('Selected vehicle unavailable for chosen dates.','amcb');
        } else if(err?.code === 'LENGTH_ERROR'){
          msg = __('Rental length outside allowed range.','amcb');
        }
        if(!msg){
          msg = __('Selected vehicle unavailable or rental length invalid.','amcb');
        }
        alert(msg);
      });
      return;
    }
    goto(step + 1);
  });
  $(document).on('click','.amcb-prev',function(e){ e.preventDefault();
    const step = Math.max(1, parseInt($('.amcb-checkout-wizard').attr('data-step'),10) - 1); goto(step);
  });
  $(document).on('change','.amcb-bill-toggle',function(){ $('.amcb-billing').attr('hidden', !this.checked); });
  $(document).on('change','input[name="paymode"]',updateSummary);
})(jQuery);
