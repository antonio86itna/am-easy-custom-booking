<?php // phpcs:ignore WordPress.Files.FileName.NotLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Vehicles list table.
 *
 * @package AMCB
 */

namespace AMCB\Admin\ListTable;

use WP_List_Table;

/**
 * Vehicles list table.
 */
class Vehicles_Table extends WP_List_Table {
		/**
		 * Prepare list table items.
		 *
		 * @return void
		 */
	public function prepare_items() {
			$this->items           = array();
			$this->_column_headers = array( $this->get_columns(), array(), array() );
	}

		/**
		 * Get list table columns.
		 *
		 * @return array
		 */
	public function get_columns() {
			return array(
				'title' => __( 'Title', 'amcb' ),
			);
	}

		/**
		 * Render admin page.
		 *
		 * @return void
		 */
	public static function render() {
		if ( ! current_user_can( 'amcb_manage_vehicles' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
				wp_die( esc_html__( 'You are not allowed to access this page.', 'amcb' ) );
		}

			$table = new self();
			$table->prepare_items();
		?>
				<div class="wrap">
						<h1 class="wp-heading-inline"><?php echo esc_html__( 'Vehicles', 'amcb' ); ?></h1>
												<a href="<?php echo esc_url( admin_url( 'admin.php?page=amcb-vehicles&action=add' ) ); ?>" class="page-title-action"><?php echo esc_html__( 'Add New', 'amcb' ); ?></a>
						<hr class="wp-header-end">
						<form method="post">
							<?php $table->display(); ?>
						</form>
				</div>
				<?php
	}
}
