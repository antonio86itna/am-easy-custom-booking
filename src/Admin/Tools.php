<?php
// phpcs:ignoreFile
namespace AMCB\Admin;

use AMCB\Install\Migrations;

class Tools {
    public static function register() {
        add_action( 'admin_post_amcb_run_migrations', [ __CLASS__, 'run_migrations' ] );
        add_action( 'admin_post_amcb_create_demo', [ __CLASS__, 'create_demo' ] );
    }

    public static function render() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__( 'Tools', 'amcb' ); ?></h1>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'amcb_run_migrations' ); ?>
                <input type="hidden" name="action" value="amcb_run_migrations" />
                <?php submit_button( __( 'Run Migrations', 'amcb' ), 'primary', 'submit', false ); ?>
            </form>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:20px;">
                <?php wp_nonce_field( 'amcb_create_demo' ); ?>
                <input type="hidden" name="action" value="amcb_create_demo" />
                <?php submit_button( __( 'Create Demo', 'amcb' ), 'secondary', 'submit', false ); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Run migrations action.
     *
     * Requires the `amcb_manage_bookings` capability.
     */
    public static function run_migrations() {
        if ( ! current_user_can( 'amcb_manage_bookings' ) ) {
            wp_die( esc_html__( 'You are not allowed to perform this action.', 'amcb' ) );
        }
        check_admin_referer( 'amcb_run_migrations' );
        Migrations::migrate();
        wp_safe_redirect( admin_url( 'admin.php?page=amcb-tools' ) );
        exit;
    }

    /**
     * Seed demo data action.
     *
     * Requires the `amcb_manage_bookings` capability.
     */
    public static function create_demo() {
        if ( ! current_user_can( 'amcb_manage_bookings' ) ) {
            wp_die( esc_html__( 'You are not allowed to perform this action.', 'amcb' ) );
        }
        check_admin_referer( 'amcb_create_demo' );
        do_action( 'amcb_seed_demo' );
        wp_safe_redirect( admin_url( 'admin.php?page=amcb-tools' ) );
        exit;
    }
}

