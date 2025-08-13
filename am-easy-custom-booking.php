<?php
/**
 * Plugin Name: AM Easy Custom Booking
 * Description: Booking custom per Costabilerent – Totaliweb.
 * Version:     1.0.0
 * Author:      Totaliweb
 * Text Domain: amcb
 * Domain Path: /languages
 */
if (!defined('ABSPATH')) exit;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/Plugin.php';

add_action('plugins_loaded', ['AMCB\Plugin', 'init']);
register_activation_hook(__FILE__, ['AMCB\Install\Activator', 'activate']);
