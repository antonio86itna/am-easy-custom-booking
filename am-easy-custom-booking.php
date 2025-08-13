<?php
/**
 * Plugin Name: AM Easy Custom Booking
 * Description: Booking custom per Costabilerent — Totaliweb. Wizard Elementor + Stripe + Mapbox. (Starter skeleton)
 * Version: 0.1.0
 * Author: Totaliweb
 * Text Domain: amcb
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) { exit; }

// Optional Composer autoload (Stripe SDK, etc.)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

// Simple PSR-4 autoloader (fallback)
spl_autoload_register(function($class){
    if (strpos($class, 'AMCB\\') !== 0) return;
    $path = __DIR__ . '/src/' . str_replace('AMCB\\', '', $class) . '.php';
    $path = str_replace('\\', '/', $path);
    if (file_exists($path)) require $path;
});

add_action('plugins_loaded', ['AMCB\Plugin', 'init']);
register_activation_hook(__FILE__, ['AMCB\Install\Activator', 'activate']);
register_deactivation_hook(__FILE__, ['AMCB\Install\Activator', 'deactivate']);
