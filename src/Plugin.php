<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase,WordPress.Files.FileName.InvalidClassFileName
/**
 * Core plugin bootstrap.
 *
 * @package AMCB
 */

namespace AMCB;

use AMCB\Admin\Menu;
use AMCB\Admin\Roles;
use AMCB\Admin\Settings;
use AMCB\Admin\Tools;
use AMCB\Admin\Vehicles;
use AMCB\Api\Rest;
use AMCB\Front\Shortcodes;
use AMCB\Install\DemoSeeder;

/**
 * Main plugin class.
 */
class Plugin {

	/**
	 * Initialize plugin.
	 *
	 * @return void
	 */
	public static function init() {
		load_plugin_textdomain( 'amcb', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages' );

		add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) ); // phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval
		add_action( 'amcb_cron_minutely', array( __CLASS__, 'cron_minutely' ) );
		add_action( 'amcb_cron_hourly', array( __CLASS__, 'cron_hourly' ) );

		// Assets.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );

		// Shortcodes.
		Shortcodes::register();

		// Elementor.
		add_action( 'elementor/widgets/register', array( 'AMCB\\Elementor\\Plugin', 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( 'AMCB\\Elementor\\Plugin', 'register_category' ) );

		// REST.
		Rest::register();

		// Admin.
		if ( is_admin() ) {
			DemoSeeder::init();
						Menu::register();
						Vehicles::register();
						Tools::register();
			add_action( 'admin_init', array( Settings::class, 'settings' ) );
			add_action( 'admin_init', array( Roles::class, 'ensure_caps' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		}
	}

	/**
	 * Register frontend assets.
	 *
	 * @return void
	 */
	public static function assets() {
		$ver = '0.1.0';
		wp_register_style( 'amcb-frontend', plugins_url( '../assets/css/frontend.css', __FILE__ ), array(), $ver );
		wp_register_script( 'amcb-frontend', plugins_url( '../assets/js/frontend.js', __FILE__ ), array( 'jquery' ), $ver, true );
		wp_register_script(
			'amcb-checkout',
			plugins_url( '../assets/js/checkout.js', __FILE__ ),
			array( 'jquery', 'wp-i18n' ),
			$ver,
			true
		);
		wp_localize_script(
			'amcb-checkout',
			'amcbCheckout',
			array(
				'restUrl'    => esc_url_raw( rest_url( 'amcb/v1/checkout/price' ) ),
				'prepareUrl' => esc_url_raw( rest_url( 'amcb/v1/checkout/prepare' ) ),
				'nonce'      => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Register admin assets.
	 *
	 * @return void
	 */
	public static function admin_assets() {
		$screen = get_current_screen();
		if ( false === strpos( $screen->id, 'amcb' ) ) {
			return;
		}

		$ver = '0.1.0';
		wp_enqueue_style(
			'amcb-admin',
			plugins_url( '../assets/css/admin.css', __FILE__ ),
			array(),
			$ver
		);
	}

	/**
	 * Register cron schedules.
	 *
	 * @param  array $schedules Schedules.
	 * @return array
	 */
	public static function cron_schedules( $schedules ) {
		$schedules['amcb_minutely'] = array(
			'interval' => MINUTE_IN_SECONDS, // phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval
		'display'      => __( 'Every Minute', 'amcb' ),
		);

		return $schedules;
	}

		/**
		 * Minutely cron tasks.
		 *
		 * @return void
		 */
	public static function cron_minutely() {
		global $wpdb;

		$pending = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}amcb_bookings WHERE status = %s AND hold_until < NOW()",
				'pending'
			)
		);

		if ( empty( $pending ) ) {
			return;
		}

		$updated = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}amcb_bookings SET status = %s WHERE status = %s AND hold_until < NOW()",
				'expired_hold',
				'pending'
			)
		);

		if ( $updated ) {
			error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				sprintf(
				/* translators: %d: number of expired bookings. */
					__( 'Expired %d pending bookings due to hold timeout.', 'amcb' ),
					$updated
				)
			);
		}
	}

	/**
	 * Hourly cron tasks.
	 *
	 * @return void
	 */
	public static function cron_hourly() {
		// Placeholder for hourly cron tasks.
	}
}
