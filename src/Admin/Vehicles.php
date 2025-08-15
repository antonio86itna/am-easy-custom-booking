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
	 * Render vehicles page.
	 *
	 * @return void
	 */
	public static function render() {
		if ( ! current_user_can( 'amcb_manage_vehicles' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			wp_die( esc_html__( 'You are not allowed to access this page.', 'amcb' ) );
		}

		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'add' === $action ) {
			?>
<div class="wrap">
<h1 class="wp-heading-inline"><?php echo esc_html__( 'Add Vehicle', 'amcb' ); ?></h1>
<hr class="wp-header-end">
<form method="post">
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
}

