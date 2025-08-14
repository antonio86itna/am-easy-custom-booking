<?php // phpcs:ignore WordPress.Files.FileName.NotLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Diagnostics admin page.
 *
 * @package AMCB
 */

namespace AMCB\Admin;

/**
 * Diagnostics page handler.
 */
class Diagnostics {
	/**
	 * Render diagnostics page.
	 *
	 * @return void
	 */
	public static function render() {
		global $wpdb;

		$tables = array(
			'amcb_vehicles'       => __( 'Vehicles', 'amcb' ),
			'amcb_vehicle_prices' => __( 'Vehicle Prices', 'amcb' ),
			'amcb_insurances'     => __( 'Insurances', 'amcb' ),
			'amcb_services'       => __( 'Services', 'amcb' ),
			'amcb_locations'      => __( 'Locations', 'amcb' ),
		);

		$counts   = array();
		$warnings = array();

		foreach ( $tables as $table => $label ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$count            = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}{$table}" );
			$counts[ $table ] = $count;
			if ( 0 === $count ) {
				$warnings[] = sprintf(
				/* translators: %s: table label */
					__( '%s table has no rows.', 'amcb' ),
					$label
				);
			}
		}
		?>
<div class="wrap">
<h1><?php echo esc_html__( 'Diagnostics', 'amcb' ); ?></h1>
		<?php foreach ( $warnings as $warning ) : ?>
<div class="notice notice-warning"><p><?php echo esc_html( $warning ); ?></p></div>
<?php endforeach; ?>
<table class="widefat striped">
<thead>
<tr>
<th><?php esc_html_e( 'Table', 'amcb' ); ?></th>
<th><?php esc_html_e( 'Rows', 'amcb' ); ?></th>
</tr>
</thead>
<tbody>
		<?php foreach ( $tables as $table => $label ) : ?>
<tr>
<td><?php echo esc_html( $label ); ?></td>
<td><?php echo esc_html( $counts[ $table ] ); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
		<?php
	}
}

