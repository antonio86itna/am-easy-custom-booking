<?php
/**
 * Plugin Name: AM Easy Custom Booking
 * Description: Booking custom per Costabilerent — Totaliweb. Wizard Elementor + Stripe + Mapbox. (Starter skeleton)
 * Version: 0.1.0
 * Author: Totaliweb
 * Text Domain: amcb
 * Domain Path: /languages
 *
 * @package AMCB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Optional Composer autoload (Stripe SDK, etc.).
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

// Simple PSR-4 autoloader (fallback).
spl_autoload_register(
	function ( $class_name ) {
		if ( 0 !== strpos( $class_name, 'AMCB\\' ) ) {
			return;
		}

		$path = __DIR__ . '/src/' . str_replace( 'AMCB\\', '', $class_name ) . '.php';
		$path = str_replace( '\\', '/', $path );

		if ( file_exists( $path ) ) {
			require $path;
		}
	}
);

add_action( 'plugins_loaded', array( 'AMCB\\Plugin', 'init' ) );
register_activation_hook( __FILE__, array( 'AMCB\\Install\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'AMCB\\Install\\Activator', 'deactivate' ) );
