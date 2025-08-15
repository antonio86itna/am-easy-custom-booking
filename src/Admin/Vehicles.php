<?php // phpcs:ignore WordPress.Files.FileName.NotLowercase,WordPress.Files.FileName.InvalidClassFileName
/**
 * Vehicles admin page.
 *
 * @package AMCB
 */

namespace AMCB\Admin;

use AMCB\Admin\ListTable\Vehicles_Table;

/**
 * Vehicles page handler.
 */
class Vehicles {

		/**
		 * Register admin actions.
		 *
		 * @return void
		 */
	public static function register() {
			add_action( 'admin_post_amcb_add_vehicle', array( __CLASS__, 'add_vehicle' ) );
	}

		/**
		 * Render vehicles page.
		 *
		 * @return void
		 */
	public static function render() {
		if ( ! current_user_can( 'amcb_manage_vehicles' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
				wp_die( esc_html__( 'You are not allowed to access this page.', 'amcb' ) );
		}

		if ( isset( $_GET['amcb_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$notice   = sanitize_key( wp_unslash( $_GET['amcb_notice'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$messages = array(
						'vehicle_added' => __( 'Vehicle added.', 'amcb' ),
					);

					if ( isset( $messages[ $notice ] ) ) {
						printf(
							'<div class="notice notice-success"><p>%s</p></div>',
							esc_html( $messages[ $notice ] )
						);
					}
		}

			$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'add' === $action ) {
			?>
<div class="wrap">
<h1 class="wp-heading-inline"><?php echo esc_html__( 'Add Vehicle', 'amcb' ); ?></h1>
<hr class="wp-header-end">
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'amcb_add_vehicle' ); ?>
<input type="hidden" name="action" value="amcb_add_vehicle" />
<table class="form-table">
<tr>
<th scope="row"><label for="amcb_name"><?php esc_html_e( 'Name', 'amcb' ); ?></label></th>
<td><input type="text" name="name" id="amcb_name" class="regular-text" /></td>
</tr>
<tr>
<th scope="row"><label for="amcb_type"><?php esc_html_e( 'Type', 'amcb' ); ?></label></th>
<td>
<select name="type" id="amcb_type">
<option value="car"><?php echo esc_html__( 'Car', 'amcb' ); ?></option>
<option value="scooter"><?php echo esc_html__( 'Scooter', 'amcb' ); ?></option>
</select>
</td>
</tr>
<tr>
<th scope="row"><label for="amcb_stock_total"><?php esc_html_e( 'Stock total', 'amcb' ); ?></label></th>
<td><input type="number" name="stock_total" id="amcb_stock_total" /></td>
</tr>
<tr>
<th scope="row"><label for="amcb_featured"><?php esc_html_e( 'Featured', 'amcb' ); ?></label></th>
<td><input type="checkbox" name="featured" id="amcb_featured" value="1" /></td>
</tr>
<tr>
<th scope="row"><label for="amcb_featured_priority"><?php esc_html_e( 'Featured priority', 'amcb' ); ?></label></th>
<td><input type="number" name="featured_priority" id="amcb_featured_priority" /></td>
</tr>
			<?php for ( $i = 1; $i <= 2; $i++ ) : ?>
<tr>
<th scope="row">
						<?php
						/* translators: %d: season number */
						echo esc_html( sprintf( __( 'Season %d', 'amcb' ), $i ) );
						?>
</th>
<td>
<label>
						<?php esc_html_e( 'From', 'amcb' ); ?>
<input type="date" name="season[<?php echo (int) $i; ?>][date_from]" />
</label>
<label>
						<?php esc_html_e( 'To', 'amcb' ); ?>
<input type="date" name="season[<?php echo (int) $i; ?>][date_to]" />
</label>
<label>
						<?php esc_html_e( 'Price per day', 'amcb' ); ?>
<input type="number" step="0.01" name="season[<?php echo (int) $i; ?>][price_per_day]" />
</label>
</td>
</tr>
			<?php endfor; ?>
<tr>
<th scope="row"><label for="amcb_premium_insurance"><?php esc_html_e( 'Premium insurance price', 'amcb' ); ?></label></th>
<td><input type="number" step="0.01" name="premium_insurance_price" id="amcb_premium_insurance" /></td>
</tr>
</table>
			<?php submit_button( __( 'Save Vehicle', 'amcb' ) ); ?>
</form>
</div>
						<?php
						return;
		}

				Vehicles_Table::render();
	}

		/**
		 * Handle vehicle addition.
		 *
		 * @return void
		 */
	public static function add_vehicle() {
			check_admin_referer( 'amcb_add_vehicle' );

		if ( ! current_user_can( 'amcb_manage_vehicles' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
				wp_die( esc_html__( 'You are not allowed to perform this action.', 'amcb' ) );
		}

			$name              = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
			$type              = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
			$stock_total       = isset( $_POST['stock_total'] ) ? absint( $_POST['stock_total'] ) : 0;
			$featured          = isset( $_POST['featured'] ) ? 1 : 0;
			$featured_priority = isset( $_POST['featured_priority'] ) ? absint( $_POST['featured_priority'] ) : 0;
			$premium_price     = isset( $_POST['premium_insurance_price'] ) ? (float) $_POST['premium_insurance_price'] : 0.0;

		$seasons_input = isset( $_POST['season'] ) ? wp_unslash( $_POST['season'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$seasons   = array();

		for ( $i = 1; $i <= 2; $i++ ) {
			if ( empty( $seasons_input[ $i ] ) ) {
				continue;
			}

					$date_from = isset( $seasons_input[ $i ]['date_from'] ) ? sanitize_text_field( $seasons_input[ $i ]['date_from'] ) : '';
					$date_to   = isset( $seasons_input[ $i ]['date_to'] ) ? sanitize_text_field( $seasons_input[ $i ]['date_to'] ) : '';
					$price     = isset( $seasons_input[ $i ]['price_per_day'] ) ? $seasons_input[ $i ]['price_per_day'] : '';

			if ( empty( $date_from ) || empty( $date_to ) || false === strtotime( $date_from ) || false === strtotime( $date_to ) || strtotime( $date_from ) > strtotime( $date_to ) ) {
				wp_die( esc_html__( 'Invalid date range.', 'amcb' ) );
			}

			if ( ! is_numeric( $price ) ) {
					wp_die( esc_html__( 'Invalid price.', 'amcb' ) );
			}

						$seasons[] = array(
							'date_from' => $date_from,
							'date_to'   => $date_to,
							'price'     => (float) $price,
						);
		}

			global $wpdb;
			$vehicle_table   = $wpdb->prefix . 'amcb_vehicles';
			$price_table     = $wpdb->prefix . 'amcb_vehicle_prices';
			$insurance_table = $wpdb->prefix . 'amcb_insurances';

			$wpdb->insert(
				$vehicle_table,
				array(
					'name'              => $name,
					'type'              => $type,
					'stock_total'       => $stock_total,
					'featured'          => $featured,
					'featured_priority' => $featured_priority,
				),
				array(
					'%s',
					'%s',
					'%d',
					'%d',
					'%d',
				)
			);

			$vehicle_id = (int) $wpdb->insert_id;

		foreach ( $seasons as $season ) {
			$wpdb->insert(
				$price_table,
				array(
					'vehicle_id' => $vehicle_id,
					'date_from'  => $season['date_from'],
					'date_to'    => $season['date_to'],
					'price'      => $season['price'],
				),
				array(
					'%d',
					'%s',
					'%s',
					'%f',
				)
			);
		}

			$wpdb->insert(
				$insurance_table,
				array(
					'vehicle_id' => $vehicle_id,
					'name'       => 'Basic',
					'price'      => 0,
					'is_default' => 1,
				),
				array(
					'%d',
					'%s',
					'%f',
					'%d',
				)
			);

			$wpdb->insert(
				$insurance_table,
				array(
					'vehicle_id' => $vehicle_id,
					'name'       => 'Premium',
					'price'      => $premium_price,
					'is_default' => 0,
				),
				array(
					'%d',
					'%s',
					'%f',
					'%d',
				)
			);

			$redirect = add_query_arg(
				array(
					'page'        => 'amcb-vehicles',
					'amcb_notice' => 'vehicle_added',
				),
				admin_url( 'admin.php' )
			);
			wp_safe_redirect( $redirect );
			exit;
	}
}

