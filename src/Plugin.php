<?php
namespace AMCB;

class Plugin {
    public static function init() {
        load_plugin_textdomain('amcb', false, dirname(plugin_basename(__FILE__)) . '/../languages');

        // Assets
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets']);
        // Shortcodes
        Front\Shortcodes::register();
        // Elementor
        add_action('elementor/widgets/register', ['AMCB\Elementor\Plugin', 'register_widgets']);
        add_action('elementor/elements/categories_registered', ['AMCB\Elementor\Plugin', 'register_category']);
        // REST
        Api\Rest::register();
        // Admin
        if (is_admin()) {
            Admin\Settings::register();
        }
    }

    public static function assets() {
        $ver = '0.1.0';
        wp_register_style('amcb-frontend', plugins_url('../assets/css/frontend.css', __FILE__), [], $ver);
        wp_register_script('amcb-frontend', plugins_url('../assets/js/frontend.js', __FILE__), ['jquery'], $ver, true);
        wp_register_script('amcb-checkout', plugins_url('../assets/js/checkout.js', __FILE__), ['jquery'], $ver, true);
    }
}
