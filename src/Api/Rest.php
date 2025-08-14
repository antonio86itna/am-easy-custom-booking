<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase,WordPress.Files.FileName.InvalidClassFileName
/**
 * REST API routes.
 *
 * @package AMCB
 */

namespace AMCB\Api;

use AMCB\Front\Availability;
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
						'methods'             => 'GET',
						'callback'            => function () {
							return array( 'ok' => true );
						},
						'permission_callback' => '__return_true',
					)
				);

				register_rest_route(
					'amcb/v1',
					'/search',
					array(
						'methods'             => 'GET',
						'callback'            => array( __CLASS__, 'search' ),
						'permission_callback' => '__return_true',
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
			}
		);
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
		$table        = $wpdb->prefix . 'amcb_vehicles';
		$placeholders = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );
		$sql          = "SELECT id, name, type, featured, featured_priority FROM $table WHERE id IN ($placeholders) ORDER BY featured DESC, featured_priority DESC, name ASC";

		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare( $sql, $ids ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		);

		$data = array();

		foreach ( $rows as $row ) {
			$data[] = array(
				'id'                => (int) $row->id,
				'name'              => esc_html( $row->name ),
				'type'              => esc_html( $row->type ),
				'featured'          => (int) $row->featured,
				'featured_priority' => (int) $row->featured_priority,
			);
		}

		return $data;
	}
}
