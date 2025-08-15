(function ($) {
	$(
		function () {
			var cfg       = window.amcbResults || {};
                        var container = $( '#amcb-results' );
                        var params    = Object.fromEntries( new URLSearchParams( window.location.search ) );
                        var fmt       = new Intl.NumberFormat( undefined, { style: 'currency', currency: 'EUR' } );
			if (params.pickup_date) {
				params.start_date = params.pickup_date;
				delete params.pickup_date;
			}
			if (params.dropoff_date) {
				params.end_date = params.dropoff_date;
				delete params.dropoff_date;
			}
			params._wpnonce = cfg.nonce;
			$.ajax(
				{
					url: cfg.restUrl,
					method: 'GET',
					data: params,
					beforeSend: function (xhr) {
						xhr.setRequestHeader( 'X-WP-Nonce', cfg.nonce );
					}
				}
			).done(
				function (res) {
					if ( ! Array.isArray( res )) {
						return;
					}
					res.sort(
						function (a,b) {
							return a.name.localeCompare( b.name ); }
					);
                                        var html = res.map(
                                                function (v) {
                                                        var price = fmt.format( v.price_per_day );
                                                        return '<div class="amcb-vehicle-card"><div class="amcb-vehicle-body"><h3>' + v.name + ' <span class="amcb-price">' + price + '/day</span></h3></div></div>';
                                                }
                                        ).join( '' );
					container.html( html );
				}
			);
		}
	);
})( jQuery );
