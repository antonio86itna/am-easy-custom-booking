<?php // phpcs:ignore WordPress.Files.FileName.NotLowercase,WordPress.Files.FileName.InvalidClassFileName
/**
 * Settings page.
 *
 * @package AMCB
 */

namespace AMCB\Admin;

/**
 * Settings handler.
 */
class Settings {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'settings' ) );
		Tools::register();
	}

	/**
	 * Add settings menu.
	 *
	 * @return void
	 */
	public static function menu() {
		// Only booking managers can access the settings pages.
		add_menu_page(
			__( 'AMCB', 'amcb' ),
			__( 'AMCB', 'amcb' ),
			'amcb_manage_bookings', // phpcs:ignore WordPress.WP.Capabilities.Unknown
			'amcb-settings',
			array( __CLASS__, 'render' ),
			'dashicons-car',
			56
		);

		add_submenu_page(
			'amcb-settings',
			__( 'Tools', 'amcb' ),
			__( 'Tools', 'amcb' ),
			'amcb_manage_bookings', // phpcs:ignore WordPress.WP.Capabilities.Unknown
			'amcb-tools',
			array( Tools::class, 'render' )
		);
	}

	/**
	 * Determine current tab.
	 *
	 * @return string
	 */
	public static function current_tab() {
		return isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Register settings sections and fields.
	 *
	 * @return void
	 */
	public static function settings() {
		register_setting(
			'amcb',
			'amcb_options',
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
			)
		);

		add_settings_section( 'amcb_general', __( 'General Settings', 'amcb' ), '__return_false', 'amcb-general' );

		add_settings_section( 'amcb_payments', __( 'Payment Settings', 'amcb' ), '__return_false', 'amcb-payments' );
		add_settings_field(
			'stripe_pk',
			__( 'Stripe Publishable Key', 'amcb' ),
			array( __CLASS__, 'text' ),
			'amcb-payments',
			'amcb_payments',
			array( 'key' => 'stripe_pk' )
		);
		add_settings_field(
			'stripe_sk',
			__( 'Stripe Secret Key', 'amcb' ),
			array( __CLASS__, 'text' ),
			'amcb-payments',
			'amcb_payments',
			array( 'key' => 'stripe_sk' )
		);
		add_settings_field(
			'deposit_percent',
			__( 'Deposit %', 'amcb' ),
			array( __CLASS__, 'number' ),
			'amcb-payments',
			'amcb_payments',
			array(
				'key'     => 'deposit_percent',
				'min'     => 0,
				'max'     => 100,
				'step'    => 1,
				'default' => 30,
			)
		);

		add_settings_section( 'amcb_maps', __( 'Map Settings', 'amcb' ), '__return_false', 'amcb-maps' );
		add_settings_field(
			'mapbox_token',
			__( 'Mapbox Token', 'amcb' ),
			array( __CLASS__, 'text' ),
			'amcb-maps',
			'amcb_maps',
			array( 'key' => 'mapbox_token' )
		);

		add_settings_section( 'amcb_legal', __( 'Legal Settings', 'amcb' ), '__return_false', 'amcb-legal' );
		add_settings_field(
			'links',
			__( 'Policy Links', 'amcb' ),
			array( __CLASS__, 'links' ),
			'amcb-legal',
			'amcb_legal'
		);
	}

	/**
	 * Sanitize options.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$output = array();

		if ( isset( $input['stripe_pk'] ) ) {
			$output['stripe_pk'] = sanitize_text_field( $input['stripe_pk'] );
		}

		if ( isset( $input['stripe_sk'] ) ) {
			$output['stripe_sk'] = sanitize_text_field( $input['stripe_sk'] );
		}

		if ( isset( $input['deposit_percent'] ) ) {
			$output['deposit_percent'] = max( 0, min( 100, absint( $input['deposit_percent'] ) ) );
		}

		if ( isset( $input['mapbox_token'] ) ) {
			$output['mapbox_token'] = sanitize_text_field( $input['mapbox_token'] );
		}

		if ( isset( $input['privacy_url'] ) ) {
			$output['privacy_url'] = esc_url_raw( $input['privacy_url'] );
		}

		if ( isset( $input['terms_url'] ) ) {
			$output['terms_url'] = esc_url_raw( $input['terms_url'] );
		}

		if ( isset( $input['rental_terms_url'] ) ) {
			$output['rental_terms_url'] = esc_url_raw( $input['rental_terms_url'] );
		}

		return $output;
	}

	/**
	 * Render text input.
	 *
	 * @param array $args Field args.
	 * @return void
	 */
	public static function text( $args ) {
		$opt = get_option( 'amcb_options', array() );
		$key = $args['key'];
		$val = isset( $opt[ $key ] ) ? $opt[ $key ] : '';
		printf(
			'<input type="text" class="regular-text" name="amcb_options[%1$s]" value="%2$s" />',
			esc_attr( $key ),
			esc_attr( $val )
		);
	}

	/**
	 * Render number input.
	 *
	 * @param array $args Field args.
	 * @return void
	 */
	public static function number( $args ) {
		$opt = get_option( 'amcb_options', array() );
		$key = $args['key'];
		$val = isset( $opt[ $key ] ) ? $opt[ $key ] : ( $args['default'] ?? '' );
		printf(
			'<input type="number" name="amcb_options[%1$s]" value="%2$s" min="%3$d" max="%4$d" step="%5$s" />',
			esc_attr( $key ),
			esc_attr( $val ),
			isset( $args['min'] ) ? (int) $args['min'] : 0,
			isset( $args['max'] ) ? (int) $args['max'] : 100,
			isset( $args['step'] ) ? esc_attr( $args['step'] ) : 1
		);
	}

	/**
	 * Render policy link inputs.
	 *
	 * @return void
	 */
	public static function links() {
		$opt    = get_option( 'amcb_options', array() );
		$fields = array(
			'privacy_url'      => __( 'Privacy URL', 'amcb' ),
			'terms_url'        => __( 'Terms URL', 'amcb' ),
			'rental_terms_url' => __( 'Rental Conditions URL', 'amcb' ),
		);

		foreach ( $fields as $k => $label ) {
			$val = isset( $opt[ $k ] ) ? $opt[ $k ] : '';
			printf(
				'<p><label>%1$s<br><input type="url" class="regular-text" name="amcb_options[%2$s]" value="%3$s" /></label></p>',
				esc_html( $label ),
				esc_attr( $k ),
				esc_attr( $val )
			);
		}
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public static function render() {
		$tab = self::current_tab();
		?>
<div class="wrap">
<h1><?php esc_html_e( 'AM Easy Custom Booking', 'amcb' ); ?></h1>
<h2 class="nav-tab-wrapper">
		<?php
		$tabs = array(
			'general'  => __( 'General', 'amcb' ),
			'payments' => __( 'Payments', 'amcb' ),
			'maps'     => __( 'Maps', 'amcb' ),
			'legal'    => __( 'Legal', 'amcb' ),
		);
		foreach ( $tabs as $slug => $label ) {
			$active = $tab === $slug ? ' nav-tab-active' : '';
			printf(
				'<a href="%1$s" class="nav-tab%3$s">%2$s</a>',
				esc_url( admin_url( 'admin.php?page=amcb-settings&tab=' . $slug ) ),
				esc_html( $label ),
				esc_attr( $active )
			);
		}
		?>
</h2>
		<?php settings_errors(); ?>
<form method="post" action="options.php">
		<?php
		settings_fields( 'amcb' );
		do_settings_sections( 'amcb-' . $tab );
		submit_button();
		?>
</form>
</div>
		<?php
	}
}

