<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase,WordPress.Files.FileName.InvalidClassFileName
/**
 * REST API routes.
 *
 * @package AMCB
 */

namespace AMCB\Api;

use AMCB\Front\Availability;
use AMCB\Domain\Pricing;
use WP_Error;
use WP_REST_Request;

/**
 * REST API endpoints.
 */
class Rest {
	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public static function register() {
		add_action(
			'rest_api_init',
			function () {
								register_rest_route(
									'amcb/v1',
									'/ping',
									array(
										'methods'  => 'GET',
										'callback' => function () {
												return array( 'ok' => true );
										},
										'permission_callback' => array( __CLASS__, 'check_permissions' ),
									)
								);

				register_rest_route(
					'amcb/v1',
					'/search',
					array(
						'methods'             => 'GET',
						'callback'            => array( __CLASS__, 'search' ),
						'permission_callback' => array( __CLASS__, 'check_permissions' ),
						'args'                => array(
							'start_date'    => array(
								'required'          => true,
								'sanitize_callback' => 'sanitize_text_field',
							),
							'end_date'      => array(
								'required'          => true,
								'sanitize_callback' => 'sanitize_text_field',
							),
							'pickup'        => array(
								'required'          => true,
								'sanitize_callback' => 'sanitize_text_field',
							),
							'dropoff'       => array(
								'required'          => true,
								'sanitize_callback' => 'sanitize_text_field',
							),
							'home_delivery' => array(
								'required'          => false,
								'sanitize_callback' => 'absint',
								'default'           => 0,
							),
						),
					)
				);
								register_rest_route(
									'amcb/v1',
									'/checkout/price',
									array(
										'methods'  => 'POST',
										'callback' => array( __CLASS__, 'checkout_price' ),
										'permission_callback' => array( __CLASS__, 'check_permissions' ),
										'args'     => array(
											'vehicle_id'   => array(
												'required' => true,
												'sanitize_callback' => 'absint',
											),
											'start_date'   => array(
												'required' => true,
												'sanitize_callback' => 'sanitize_text_field',
											),
											'end_date'     => array(
												'required' => true,
												'sanitize_callback' => 'sanitize_text_field',
											),
											'pickup_time'  => array(
												'sanitize_callback' => 'sanitize_text_field',
											),
											'dropoff_time' => array(
												'sanitize_callback' => 'sanitize_text_field',
											),
											'pickup'       => array(
												'sanitize_callback' => 'absint',
											),
											'dropoff'      => array(
												'sanitize_callback' => 'absint',
											),
											'home_delivery' => array(
												'sanitize_callback' => 'absint',
												'default' => 0,
											),
											'services'     => array(
												'sanitize_callback' => function ( $value ) {
													return array_map( 'absint', (array) $value );
												},
												'default' => array(),
											),
											'insurance_id' => array(
												'sanitize_callback' => 'absint',
											),
											'coupon_code'  => array(
												'sanitize_callback' => 'sanitize_text_field',
											),
											'payment_mode' => array(
												'sanitize_callback' => 'sanitize_text_field',
												'default' => 'full',
											),
											'currency'     => array(
												'sanitize_callback' => 'sanitize_text_field',
												'default' => 'EUR',
											),
										),
									)
								);
								register_rest_route(
									'amcb/v1',
									'/checkout/prepare',
									array(
										'methods'  => 'POST',
										'callback' => array( __CLASS__, 'checkout_prepare' ),
										'permission_callback' => array( __CLASS__, 'check_permissions' ),
										'args'     => array(
											'vehicle_id'   => array(
												'required' => true,
												'sanitize_callback' => 'absint',
											),
											'start_date'   => array(
												'required' => true,
												'sanitize_callback' => 'sanitize_text_field',
											),
											'end_date'     => array(
												'required' => true,
												'sanitize_callback' => 'sanitize_text_field',
											),
											'pickup_time'  => array(
												'sanitize_callback' => 'sanitize_text_field',
											),
											'dropoff_time' => array(
												'sanitize_callback' => 'sanitize_text_field',
											),
											'pickup'       => array(
												'sanitize_callback' => 'absint',
											),
											'dropoff'      => array(
												'sanitize_callback' => 'absint',
											),
											'home_delivery' => array(
												'sanitize_callback' => 'absint',
												'default' => 0,
											),
											'services'     => array(
												'sanitize_callback' => function ( $value ) {
														return array_map( 'absint', (array) $value );
												},
												'default' => array(),
											),
											'insurance_id' => array(
												'sanitize_callback' => 'absint',
											),
											'coupon_code'  => array(
												'sanitize_callback' => 'sanitize_text_field',
											),
											'payment_mode' => array(
												'sanitize_callback' => 'sanitize_text_field',
												'default' => 'full',
											),
											'currency'     => array(
												'sanitize_callback' => 'sanitize_text_field',
												'default' => 'EUR',
											),
										),
									)
								);
			}
		);
	}

		/**
		 * Verify nonce and optional capability.
		 *
		 * @param WP_REST_Request $request    Request.
		 * @param string          $capability Optional capability to check.
		 * @return true|WP_Error
		 */
	public static function check_permissions( WP_REST_Request $request, $capability = '' ) {
			$nonce = $request->get_param( '_wpnonce' );

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
				return new WP_Error( 'amcb_rest_forbidden', __( 'Invalid nonce.', 'amcb' ), array( 'status' => 403 ) );
		}

		if ( $capability && ! current_user_can( $capability ) ) {
				return new WP_Error( 'amcb_rest_forbidden', __( 'Sorry, you are not allowed to do that.', 'amcb' ), array( 'status' => 403 ) );
		}

			return true;
	}

	/**
	 * Validate date string.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return bool
	 */
	protected static function validate_date( $date ) {
		$d = \DateTime::createFromFormat( 'Y-m-d', $date );
		return $d && $d->format( 'Y-m-d' ) === $date;
	}

	/**
	 * Search available vehicles.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return array|WP_Error
	 */
	public static function search( WP_REST_Request $request ) {
		$start_date    = sanitize_text_field( $request->get_param( 'start_date' ) );
		$end_date      = sanitize_text_field( $request->get_param( 'end_date' ) );
		$pickup        = sanitize_text_field( $request->get_param( 'pickup' ) );
		$dropoff       = sanitize_text_field( $request->get_param( 'dropoff' ) );
		$home_delivery = absint( $request->get_param( 'home_delivery' ) );

		if ( ! self::validate_date( $start_date ) || ! self::validate_date( $end_date ) ) {
			return new WP_Error( 'invalid_date', __( 'Invalid date format.', 'amcb' ), array( 'status' => 400 ) );
		}

		if ( $start_date >= $end_date ) {
			return new WP_Error( 'invalid_range', __( 'Start date must be before end date.', 'amcb' ), array( 'status' => 400 ) );
		}

		if ( ! in_array( $home_delivery, array( 0, 1 ), true ) ) {
			return new WP_Error( 'invalid_home_delivery', __( 'Invalid home delivery value.', 'amcb' ), array( 'status' => 400 ) );
		}

		if ( '' === $pickup || '' === $dropoff ) {
			return new WP_Error( 'invalid_location', __( 'Invalid locations.', 'amcb' ), array( 'status' => 400 ) );
		}

		$availability = new Availability();
		$ids          = $availability->get_available_vehicles( $start_date, $end_date );

		if ( empty( $ids ) ) {
			return array();
		}

		global $wpdb;
		$vehicle_table = $wpdb->prefix . 'amcb_vehicles';
		$placeholders  = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );
		$sql           = "SELECT id, name, type, featured, featured_priority FROM {$vehicle_table} WHERE id IN ({$placeholders}) ORDER BY featured DESC, featured_priority DESC, name ASC";

		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare( $sql, $ids ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		);

		$price_table        = $wpdb->prefix . 'amcb_vehicle_prices';
		$price_placeholders = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );
		$price_sql          = "SELECT vehicle_id, price FROM {$price_table} WHERE vehicle_id IN ({$price_placeholders}) AND %s BETWEEN date_from AND date_to ORDER BY vehicle_id ASC, date_from DESC";
		$price_rows         = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare( $price_sql, array_merge( $ids, array( $start_date ) ) ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
		$prices             = array();
		foreach ( $price_rows as $price_row ) {
			if ( ! isset( $prices[ $price_row->vehicle_id ] ) ) {
				$prices[ $price_row->vehicle_id ] = (float) $price_row->price;
			}
		}

		$data = array();

		foreach ( $rows as $row ) {
			$data[] = array(
				'id'                => (int) $row->id,
				'name'              => esc_html( $row->name ),
				'type'              => esc_html( $row->type ),
				'featured'          => (int) $row->featured,
				'featured_priority' => (int) $row->featured_priority,
				'price_per_day'     => isset( $prices[ $row->id ] ) ? (float) $prices[ $row->id ] : 0.0,
			);
		}

		return $data;
	}

	/**
	 * Calculate pricing for checkout.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return array|WP_Error
	 */
	public static function checkout_price( WP_REST_Request $request ) {
		$vehicle_id    = absint( $request->get_param( 'vehicle_id' ) );
		$start_date    = sanitize_text_field( $request->get_param( 'start_date' ) );
		$end_date      = sanitize_text_field( $request->get_param( 'end_date' ) );
		$pickup_time   = sanitize_text_field( $request->get_param( 'pickup_time' ) );
		$dropoff_time  = sanitize_text_field( $request->get_param( 'dropoff_time' ) );
		$pickup        = absint( $request->get_param( 'pickup' ) );
		$dropoff       = absint( $request->get_param( 'dropoff' ) );
		$home_delivery = absint( $request->get_param( 'home_delivery' ) );
		$services      = $request->get_param( 'services' );
		$insurance_id  = absint( $request->get_param( 'insurance_id' ) );
		$coupon_code   = sanitize_text_field( $request->get_param( 'coupon_code' ) );
		$payment_mode  = sanitize_text_field( $request->get_param( 'payment_mode' ) );
		$currency      = sanitize_text_field( $request->get_param( 'currency' ) );

		if ( ! self::validate_date( $start_date ) || ! self::validate_date( $end_date ) ) {
			return new WP_Error( 'invalid_date', __( 'Invalid date format.', 'amcb' ), array( 'status' => 400 ) );
		}

		if ( $start_date >= $end_date ) {
			return new WP_Error( 'invalid_range', __( 'Start date must be before end date.', 'amcb' ), array( 'status' => 400 ) );
		}

		if ( $vehicle_id <= 0 ) {
			return new WP_Error( 'invalid_vehicle', __( 'Invalid vehicle ID.', 'amcb' ), array( 'status' => 400 ) );
		}

		if ( ! in_array( $home_delivery, array( 0, 1 ), true ) ) {
			return new WP_Error( 'invalid_home_delivery', __( 'Invalid home delivery value.', 'amcb' ), array( 'status' => 400 ) );
		}

		if ( ! in_array( $payment_mode, array( 'full', 'deposit' ), true ) ) {
			return new WP_Error( 'invalid_payment_mode', __( 'Invalid payment mode.', 'amcb' ), array( 'status' => 400 ) );
		}

		if ( ! is_array( $services ) ) {
			$services = array();
		} else {
			$services = array_map( 'absint', $services );
		}

		$pricing = new Pricing();
		$result  = $pricing->calculate(
			$vehicle_id,
			$start_date,
			$end_date,
			$pickup_time,
			$dropoff_time,
			$pickup,
			$dropoff,
			$services,
			$insurance_id,
			$coupon_code,
			$payment_mode,
			$currency
		);

		if ( is_wp_error( $result ) ) {
			$code = $result->get_error_code();
			if ( in_array( $code, array( 'NO_RATE_COVERAGE', 'RENTAL_LENGTH_INVALID' ), true ) ) {
				$data = $result->get_error_data();
				if ( ! is_array( $data ) ) {
					$data = array();
				}
				$data['status'] = 422;
				return new WP_Error( $code, $result->get_error_message(), $data );
			}
			return $result;
		}

					return $result;
	}

		/**
		 * Prepare booking and start hold.
		 *
		 * @param WP_REST_Request $request Request.
		 * @return array|WP_Error
		 */
	public static function checkout_prepare( WP_REST_Request $request ) {
			$vehicle_id    = absint( $request->get_param( 'vehicle_id' ) );
			$start_date    = sanitize_text_field( $request->get_param( 'start_date' ) );
			$end_date      = sanitize_text_field( $request->get_param( 'end_date' ) );
			$pickup_time   = sanitize_text_field( $request->get_param( 'pickup_time' ) );
			$dropoff_time  = sanitize_text_field( $request->get_param( 'dropoff_time' ) );
			$pickup        = absint( $request->get_param( 'pickup' ) );
			$dropoff       = absint( $request->get_param( 'dropoff' ) );
			$home_delivery = absint( $request->get_param( 'home_delivery' ) );
			$services      = $request->get_param( 'services' );
			$insurance_id  = absint( $request->get_param( 'insurance_id' ) );
			$coupon_code   = sanitize_text_field( $request->get_param( 'coupon_code' ) );
			$payment_mode  = sanitize_text_field( $request->get_param( 'payment_mode' ) );
			$currency      = sanitize_text_field( $request->get_param( 'currency' ) );

		if ( ! self::validate_date( $start_date ) || ! self::validate_date( $end_date ) ) {
				return new WP_Error( 'invalid_date', __( 'Invalid date format.', 'amcb' ), array( 'status' => 400 ) );
		}

		if ( $start_date >= $end_date ) {
					return new WP_Error( 'invalid_range', __( 'Start date must be before end date.', 'amcb' ), array( 'status' => 400 ) );
		}

		if ( $vehicle_id <= 0 ) {
					return new WP_Error( 'invalid_vehicle', __( 'Invalid vehicle ID.', 'amcb' ), array( 'status' => 400 ) );
		}

		if ( ! in_array( $home_delivery, array( 0, 1 ), true ) ) {
				return new WP_Error( 'invalid_home_delivery', __( 'Invalid home delivery value.', 'amcb' ), array( 'status' => 400 ) );
		}

		if ( ! in_array( $payment_mode, array( 'full', 'deposit' ), true ) ) {
				return new WP_Error( 'invalid_payment_mode', __( 'Invalid payment mode.', 'amcb' ), array( 'status' => 400 ) );
		}

		if ( ! is_array( $services ) ) {
				$services = array();
		} else {
				$services = array_map( 'absint', $services );
		}

				$availability = new Availability();
		if ( ! $availability->is_available( $vehicle_id, $start_date, $end_date ) ) {
				return new WP_Error( 'vehicle_unavailable', __( 'Vehicle not available for selected dates.', 'amcb' ), array( 'status' => 409 ) );
		}

				$pricing = new Pricing();
				$result  = $pricing->calculate(
					$vehicle_id,
					$start_date,
					$end_date,
					$pickup_time,
					$dropoff_time,
					$pickup,
					$dropoff,
					$services,
					$insurance_id,
					$coupon_code,
					$payment_mode,
					$currency
				);

		if ( is_wp_error( $result ) ) {
				$code = $result->get_error_code();
			if ( in_array( $code, array( 'NO_RATE_COVERAGE', 'RENTAL_LENGTH_INVALID' ), true ) ) {
					$data = $result->get_error_data();
				if ( ! is_array( $data ) ) {
						$data = array();
				}
					$data['status'] = 422;
					return new WP_Error( $code, $result->get_error_message(), $data );
			}
				return $result;
		}

			global $wpdb;
			$prefix              = $wpdb->prefix . 'amcb_';
			$bookings_table      = $prefix . 'bookings';
			$booking_items_table = $prefix . 'booking_items';
		$booking_totals          = $prefix . 'booking_totals';

		$hold_expires = gmdate( 'Y-m-d H:i:s', strtotime( current_time( 'mysql', true ) . ' +15 minutes' ) );

		$sql = "INSERT INTO {$bookings_table} (customer_id, status, start_date, end_date, pickup_id, dropoff_id, hold_until) VALUES (%d, %s, %s, %s, %d, %d, %s)";
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare( $sql, 0, 'pending', $start_date, $end_date, $pickup, $dropoff, $hold_expires ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			);
			$booking_id = (int) $wpdb->insert_id;

			$sql_items = "INSERT INTO {$booking_items_table} (booking_id, vehicle_id, days, price) VALUES (%d, %d, %d, %f)";
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare( $sql_items, $booking_id, $vehicle_id, (int) $result['days'], (float) $result['base_total'] ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			);

			$sql_totals = "INSERT INTO {$booking_totals} (booking_id, base_total, services_total, insurance_total, coupon_discount, grand_total, deposit_amount) VALUES (%d, %f, %f, %f, %f, %f, %f)";
						$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
							$wpdb->prepare(
								$sql_totals, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
								$booking_id,
								(float) $result['base_total'],
								(float) $result['services_total'],
								(float) $result['insurance_total'],
								(float) $result['coupon_discount'],
								(float) $result['grand_total'],
								(float) $result['deposit_amount']
							)
						);

			return array(
				'booking_id'      => $booking_id,
				'hold_expires_at' => $hold_expires,
				'totals'          => $result,
			);
	}
}
