<?php // phpcs:ignore WordPress.Files.FileName.NotLowercase,WordPress.Files.FileName.InvalidClassFileName
/**
 * Admin menu registration.
 *
 * @package AMCB
 */

namespace AMCB\Admin;

use AMCB\Admin\Settings;
use AMCB\Admin\Tools;

/**
 * Admin menu handler.
 */
class Menu {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
	}

	/**
	 * Add admin menu and submenus.
	 *
	 * @return void
	 */
	public static function menu() {
		add_menu_page(
			__( 'AMCB', 'amcb' ),
			__( 'AMCB', 'amcb' ),
			'amcb_manage_dashboard',
			'amcb-dashboard',
			array( __CLASS__, 'render_page' ),
			'dashicons-car',
			56
		);

		add_submenu_page(
			'amcb-dashboard',
			__( 'Dashboard', 'amcb' ),
			__( 'Dashboard', 'amcb' ),
			'amcb_manage_dashboard',
			'amcb-dashboard',
			array( __CLASS__, 'render_page' )
		);

		$sections = array(
			'bookings'   => __( 'Bookings', 'amcb' ),
			'vehicles'   => __( 'Vehicles', 'amcb' ),
			'prices'     => __( 'Prices', 'amcb' ),
			'blocks'     => __( 'Blocks', 'amcb' ),
			'services'   => __( 'Services', 'amcb' ),
			'insurances' => __( 'Insurances', 'amcb' ),
			'coupons'    => __( 'Coupons', 'amcb' ),
			'locations'  => __( 'Locations', 'amcb' ),
		);

		foreach ( $sections as $slug => $label ) {
			add_submenu_page(
				'amcb-dashboard',
				$label,
				$label,
				"amcb_manage_{$slug}",
				"amcb-{$slug}",
				array( __CLASS__, 'render_page' )
			);
		}

		add_submenu_page(
			'amcb-dashboard',
			__( 'Tools', 'amcb' ),
			__( 'Tools', 'amcb' ),
			'amcb_manage_tools',
			'amcb-tools',
			array( Tools::class, 'render' )
		);

		add_submenu_page(
			'amcb-dashboard',
			__( 'Settings', 'amcb' ),
			__( 'Settings', 'amcb' ),
			'amcb_manage_settings',
			'amcb-settings',
			array( Settings::class, 'render' )
		);

		remove_submenu_page( 'amcb-dashboard', 'amcb-dashboard' );
	}

	/**
	 * Render stub page.
	 *
	 * @return void
	 */
	public static function render_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		</div>
		<?php
	}
}
