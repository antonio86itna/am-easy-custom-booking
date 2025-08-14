<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase,WordPress.Files.FileName.InvalidClassFileName
/**
 * Vehicle availability calculations.
 *
 * @package AMCB
 */

namespace AMCB\Front;

/**
 * Handle vehicle availability ranges.
 */
class Availability {
	/**
	 * Day buckets of occupied vehicles.
	 *
	 * @var array
	 */
	protected $buckets = array();

	/**
	 * Get available vehicles for a date range.
	 *
	 * @param string $start Start date Y-m-d inclusive.
	 * @param string $end   End date Y-m-d exclusive.
	 * @return array List of vehicle IDs available for all days.
	 */
	public function get_available_vehicles( $start, $end ) {
			$this->build_buckets( $start, $end );

			global $wpdb;
			$prefix         = $wpdb->prefix . 'amcb_';
			$vehicles_table = $prefix . 'vehicles';

				$query = 'SELECT id FROM ' . $vehicles_table . ' WHERE status = %s';
				$all   = array_map(
					'intval',
					$wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
						$wpdb->prepare(
							$query, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
							'active'
						)
					)
				);

			$occupied = array();
		foreach ( $this->buckets as $ids ) {
				$occupied = array_merge( $occupied, $ids );
		}
			$occupied = array_unique( $occupied );

			return array_values( array_diff( $all, $occupied ) );
	}

	/**
	 * Check if a vehicle is available for a range.
	 *
	 * @param int    $vehicle_id Vehicle ID.
	 * @param string $start      Start date.
	 * @param string $end        End date.
	 * @return bool True if available.
	 */
	public function is_available( $vehicle_id, $start, $end ) {
		$vehicle_id = (int) $vehicle_id;
		return in_array( $vehicle_id, $this->get_available_vehicles( $start, $end ), true );
	}

	/**
	 * Build day buckets with bookings and manual blocks.
	 *
	 * @param string $start Start date.
	 * @param string $end   End date.
	 */
	protected function build_buckets( $start, $end ) {
		$this->buckets = array();

		$period = new \DatePeriod(
			new \DateTime( $start ),
			new \DateInterval( 'P1D' ),
			new \DateTime( $end )
		);

		foreach ( $period as $day ) {
			$this->buckets[ $day->format( 'Y-m-d' ) ] = array();
		}

		foreach ( $this->get_bookings( $start, $end ) as $row ) {
			$from = ( $row->start_date > $start ) ? $row->start_date : $start;
			$to   = ( $row->end_date < $end ) ? $row->end_date : $end;
			$this->occupy_range( (int) $row->vehicle_id, $from, $to );
		}

		foreach ( $this->get_blocks( $start, $end ) as $block ) {
			$from = ( $block->date_from > $start ) ? $block->date_from : $start;
			$to   = ( $block->date_to < $end ) ? $block->date_to : $end;
			$this->occupy_range( (int) $block->vehicle_id, $from, $to );
		}
	}

	/**
	 * Mark buckets for a vehicle range.
	 *
	 * @param int    $vehicle_id Vehicle ID.
	 * @param string $start      Start date.
	 * @param string $end        End date.
	 */
	protected function occupy_range( $vehicle_id, $start, $end ) {
		$period = new \DatePeriod(
			new \DateTime( $start ),
			new \DateInterval( 'P1D' ),
			new \DateTime( $end )
		);

		foreach ( $period as $day ) {
			$key = $day->format( 'Y-m-d' );
			if ( isset( $this->buckets[ $key ] ) ) {
				$this->buckets[ $key ][] = $vehicle_id;
			}
		}
	}

	/**
	 * Retrieve bookings overlapping a range.
	 *
	 * @param string $start Start date.
	 * @param string $end   End date.
	 * @return array
	 */
	protected function get_bookings( $start, $end ) {
			global $wpdb;
			$prefix              = $wpdb->prefix . 'amcb_';
			$bookings_table      = $prefix . 'bookings';
			$booking_items_table = $prefix . 'booking_items';
			$statuses            = array( 'paid', 'confirmed', 'in_progress' );
			$placeholders        = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );

				$sql = 'SELECT b.start_date, b.end_date, bi.vehicle_id FROM ' . $bookings_table . ' b INNER JOIN ' . $booking_items_table . ' bi ON b.id = bi.booking_id WHERE b.status IN (' . $placeholders . ') AND b.start_date < %s AND b.end_date > %s';

				return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare(
						$sql, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						array_merge( $statuses, array( $end, $start ) )
					)
				);
	}

	/**
	 * Retrieve manual vehicle blocks overlapping a range.
	 *
	 * @param string $start Start date.
	 * @param string $end   End date.
	 * @return array
	 */
	protected function get_blocks( $start, $end ) {
			global $wpdb;
			$table = $wpdb->prefix . 'amcb_vehicle_blocks';

				$sql = 'SELECT vehicle_id, date_from, date_to FROM ' . $table . ' WHERE date_from < %s AND date_to > %s';

				return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare( $sql, $end, $start ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				);
	}
}
