<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase,WordPress.Files.FileName.InvalidClassFileName
/**
 * Pricing domain logic.
 *
 * @package AMCB
 */

namespace AMCB\Domain;

use WP_Error;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handle rental pricing calculations.
 */
class Pricing {
	/**
	 * Calculate pricing breakdown.
	 *
	 * @param int    $vehicle_id    Vehicle ID.
	 * @param string $start_date    Start date (Y-m-d).
	 * @param string $end_date      End date (Y-m-d), end exclusive.
	 * @param string $pickup_time   Pickup time (H:i).
	 * @param string $dropoff_time  Dropoff time (H:i).
	 * @param int    $pickup        Pickup location ID.
	 * @param int    $dropoff       Dropoff location ID.
	 * @param array  $services      Service IDs.
	 * @param int    $insurance_id  Insurance ID.
	 * @param string $coupon_code   Coupon code.
	 * @param string $payment_mode  Payment mode (full|deposit).
	 * @param string $currency      Currency code.
	 * @return array|WP_Error
	 */
	public function calculate( $vehicle_id, $start_date, $end_date, $pickup_time = '', $dropoff_time = '', $pickup = 0, $dropoff = 0, $services = array(), $insurance_id = 0, $coupon_code = '', $payment_mode = 'full', $currency = 'EUR' ) {
		global $wpdb;

		$price_table     = $wpdb->prefix . 'amcb_vehicle_prices';
		$service_table   = $wpdb->prefix . 'amcb_services';
		$insurance_table = $wpdb->prefix . 'amcb_insurances';
		$coupon_table    = $wpdb->prefix . 'amcb_coupons';

		$segments      = array();
		$missing_dates = array();
		$rates_used    = array();
		$base_total    = 0.0;

		$pricing_end = $end_date;

		$late_hour = get_option( 'amcb_late_return_hour', '10:00' );
		if ( ! empty( $dropoff_time ) && strtotime( $dropoff_time ) > strtotime( $late_hour ) ) {
			$pricing_end = gmdate( 'Y-m-d', strtotime( $end_date . ' +1 day' ) );
		}

		$period = new \DatePeriod(
			new \DateTime( $start_date ),
			new \DateInterval( 'P1D' ),
			new \DateTime( $pricing_end )
		);

		$current_segment = null;

		foreach ( $period as $day ) {
			$date = $day->format( 'Y-m-d' );
			$sql  = "SELECT price, min_days, max_days, long_rent_json FROM {$price_table} WHERE vehicle_id = %d AND %s BETWEEN date_from AND date_to ORDER BY date_from DESC LIMIT 1";
			$rate = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare( $sql, $vehicle_id, $date ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				ARRAY_A
			);

			if ( ! $rate ) {
				$missing_dates[] = $date;
				continue;
			}

			$rates_used[] = $rate;

			if ( ! $current_segment || $current_segment['rate'] !== (float) $rate['price'] ) {
				if ( $current_segment ) {
					$segments[] = $current_segment;
				}
				$current_segment = array(
					'start' => $date,
					'end'   => gmdate( 'Y-m-d', strtotime( $date . ' +1 day' ) ),
					'days'  => 1,
					'rate'  => (float) $rate['price'],
					'total' => (float) $rate['price'],
				);
			} else {
				++$current_segment['days'];
				$current_segment['end']   = gmdate( 'Y-m-d', strtotime( $date . ' +1 day' ) );
				$current_segment['total'] = $current_segment['days'] * $current_segment['rate'];
			}

			$base_total += (float) $rate['price'];
		}

		if ( $current_segment ) {
			$segments[] = $current_segment;
		}

		$days = 0;
		foreach ( $segments as $seg ) {
			$days += (int) $seg['days'];
		}

		if ( ! empty( $missing_dates ) ) {
			return new WP_Error(
				'NO_RATE_COVERAGE',
				__( 'No rate coverage for selected dates.', 'amcb' ),
				array(
					'status'        => 422,
					'missing_dates' => $missing_dates,
				)
			);
		}

		// Validate min/max days.
		$min_allowed = null;
		$max_allowed = null;

		foreach ( $rates_used as $rate ) {
			if ( ! empty( $rate['min_days'] ) ) {
				$min_allowed = $min_allowed ? max( $min_allowed, (int) $rate['min_days'] ) : (int) $rate['min_days'];
			}
			if ( ! empty( $rate['max_days'] ) ) {
				$max_allowed = $max_allowed ? min( $max_allowed, (int) $rate['max_days'] ) : (int) $rate['max_days'];
			}
		}

		if ( ( $min_allowed && $days < $min_allowed ) || ( $max_allowed && $days > $max_allowed ) ) {
			return new WP_Error(
				'RENTAL_LENGTH_INVALID',
				__( 'Rental length is not allowed.', 'amcb' ),
				array( 'status' => 422 )
			);
		}

		// Long rent discount.
		$discount_percent = 0.0;
		foreach ( $rates_used as $rate ) {
			if ( empty( $rate['long_rent_json'] ) ) {
				continue;
			}
			$tiers = json_decode( $rate['long_rent_json'], true );
			if ( ! is_array( $tiers ) ) {
				continue;
			}
			foreach ( $tiers as $tier ) {
				if ( $days >= (int) $tier['min_days'] && $discount_percent < (float) $tier['percent'] ) {
					$discount_percent = (float) $tier['percent'];
				}
			}
		}

		$long_rent_discount = round( $base_total * ( $discount_percent / 100 ), 2 );

		// Insurance.
		$insurance_total = 0.0;
		if ( $insurance_id ) {
			$sql             = "SELECT price FROM {$insurance_table} WHERE id = %d";
			$insurance_price = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare( $sql, $insurance_id ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			);
			if ( $insurance_price ) {
				$insurance_total = round( $days * (float) $insurance_price, 2 );
			}
		}

		// Services.
		$services_total = 0.0;
		if ( ! empty( $services ) ) {
			foreach ( $services as $service_id ) {
				$sql     = "SELECT price, per_day FROM {$service_table} WHERE id = %d";
				$service = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare( $sql, $service_id ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					ARRAY_A
				);
				if ( $service ) {
					$services_total += (float) $service['price'] * ( $service['per_day'] ? $days : 1 );
				}
			}
			$services_total = round( $services_total, 2 );
		}

		$subtotal_before_coupon = $base_total - $long_rent_discount + $insurance_total + $services_total;

		// Coupon.
		$coupon_discount = 0.0;
		if ( ! empty( $coupon_code ) ) {
			$sql    = "SELECT discount_type, amount, scope FROM {$coupon_table} WHERE code = %s";
			$coupon = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare( $sql, $coupon_code ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				ARRAY_A
			);

			if ( $coupon ) {
				$discount_base = $subtotal_before_coupon;
				switch ( $coupon['scope'] ) {
					case 'base':
						$discount_base = $base_total - $long_rent_discount;
						break;
					case 'services':
						$discount_base = $services_total;
						break;
					case 'insurance':
						$discount_base = $insurance_total;
						break;
					default:
						break;
				}

				if ( 'percent' === $coupon['discount_type'] ) {
					$coupon_discount = round( $discount_base * ( (float) $coupon['amount'] / 100 ), 2 );
				} else {
					$coupon_discount = min( (float) $coupon['amount'], $discount_base );
				}
			}
		}

		$grand_total = max( $subtotal_before_coupon - $coupon_discount, 0 );

		// Deposit.
		$deposit_amount = 0.0;
		if ( 'deposit' === $payment_mode ) {
			$deposit_percent = (float) get_option( 'amcb_deposit_percent', 30 );
			$deposit_amount  = round( $grand_total * ( $deposit_percent / 100 ), 2 );
		}

		$to_collect = max( $grand_total - $deposit_amount, 0 );

		return array(
			'days'               => $days,
			'segments'           => $segments,
			'base_total'         => round( $base_total, 2 ),
			'long_rent_discount' => $long_rent_discount,
			'insurance_total'    => $insurance_total,
			'services_total'     => $services_total,
			'coupon_discount'    => $coupon_discount,
			'grand_total'        => $grand_total,
			'deposit_amount'     => $deposit_amount,
			'to_collect'         => $to_collect,
			'currency'           => $currency,
		);
	}
}
