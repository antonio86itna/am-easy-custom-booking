<?php
// phpcs:ignoreFile
namespace AMCB\Admin;

class Settings {
    public static function register() {
        add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'settings' ] );
        Tools::register();
    }

    public static function menu() {
        add_menu_page( 'AMCB', 'AMCB', 'manage_options', 'amcb-settings', [ __CLASS__, 'render' ], 'dashicons-car', 56 );
        add_submenu_page( 'amcb-settings', __( 'Tools', 'amcb' ), __( 'Tools', 'amcb' ), 'manage_options', 'amcb-tools', [ Tools::class, 'render' ] );
    }
    public static function settings() {
        register_setting('amcb', 'amcb_options');
        add_settings_section('amcb_main', __('Main Settings','amcb'), '__return_false', 'amcb');
        add_settings_field('stripe_pk', 'Stripe Publishable Key', [__CLASS__, 'text'], 'amcb', 'amcb_main', ['key'=>'stripe_pk']);
        add_settings_field('stripe_sk', 'Stripe Secret Key', [__CLASS__, 'text'], 'amcb', 'amcb_main', ['key'=>'stripe_sk']);
        add_settings_field('deposit_percent', __('Deposit %','amcb'), [__CLASS__, 'number'], 'amcb', 'amcb_main', ['key'=>'deposit_percent', 'min'=>0, 'max'=>100, 'step'=>1, 'default'=>30]);
        add_settings_field('mapbox_token', 'Mapbox Token', [__CLASS__, 'text'], 'amcb', 'amcb_main', ['key'=>'mapbox_token']);
        add_settings_field('links', __('Policy Links','amcb'), [__CLASS__, 'links'], 'amcb', 'amcb_main');
    }
    public static function text($args){
        $opt = get_option('amcb_options', []);
        $key = $args['key'];
        $val = isset($opt[$key]) ? esc_attr($opt[$key]) : '';
        echo '<input type="text" class="regular-text" name="amcb_options['.$key.']" value="'.$val.'" />';
    }
    public static function number($args){
        $opt = get_option('amcb_options', []);
        $key = $args['key'];
        $val = isset($opt[$key]) ? esc_attr($opt[$key]) : ($args['default'] ?? '');
        printf('<input type="number" name="amcb_options[%s]" value="%s" min="%d" max="%d" step="%s" />',
            $key, $val, $args['min']??0, $args['max']??100, $args['step']??1);
    }
    public static function links(){
        $opt = get_option('amcb_options', []);
        $fields = ['privacy_url'=>'Privacy URL','terms_url'=>'Terms URL','rental_terms_url'=>'Rental Conditions URL'];
        foreach ($fields as $k=>$label){
            $val = isset($opt[$k]) ? esc_attr($opt[$k]) : '';
            echo '<p><label>'.$label.'<br><input type="url" class="regular-text" name="amcb_options['.$k.']" value="'.$val.'" /></label></p>';
        }
    }
    public static function render() { ?>
        <div class="wrap">
          <h1>AM Easy Custom Booking</h1>
          <form method="post" action="options.php">
            <?php settings_fields('amcb'); do_settings_sections('amcb'); submit_button(); ?>
          </form>
        </div>
    <?php }
}
