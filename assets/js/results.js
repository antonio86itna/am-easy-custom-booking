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
                                        var html = res.map(
                                                function (v) {
                                                        var price = fmt.format( v.price_per_day );
                                                        var query = {
                                                                vehicle_id: v.id,
                                                                start_date: params.start_date,
                                                                end_date: params.end_date,
                                                                pickup: params.pickup,
                                                                dropoff: params.dropoff,
                                                        };
                                                        if ( params.home_delivery ) {
                                                                query.home_delivery = params.home_delivery;
                                                        }
                                                        var url = cfg.checkoutUrl + '?' + $.param( query );
                                                        return '<a class="amcb-vehicle-card" href="' + url + '"><div class="amcb-vehicle-body"><h3>' + v.name + ' <span class="amcb-price">' + price + '/day</span></h3></div></a>';
                                                }
                                        ).join( '' );
					container.html( html );
				}
			);
		}
	);
})( jQuery );
