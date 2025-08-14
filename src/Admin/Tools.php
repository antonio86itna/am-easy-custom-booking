<?php // phpcs:ignore WordPress.Files.FileName.NotLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Admin tools page.
 *
 * @package AMCB
 */

namespace AMCB\Admin;

use AMCB\Install\Migrations;

/**
 * Admin tools handler.
 */
class Tools {
	/**
	 * Register admin actions.
	 */
	public static function register() {
		add_action( 'admin_post_amcb_run_migrations', array( __CLASS__, 'run_migrations' ) );
		add_action( 'admin_post_amcb_create_demo', array( __CLASS__, 'create_demo' ) );
	}

	/**
	 * Render tools page.
	 */
	public static function render() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Tools', 'amcb' ); ?></h1>
			<?php
			if ( isset( $_GET['amcb_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$notice   = sanitize_key( wp_unslash( $_GET['amcb_notice'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$messages = array(
						'migrations_run' => __( 'Migrations completed.', 'amcb' ),
						'demo_created'   => __( 'Demo data created.', 'amcb' ),
					);

					if ( isset( $messages[ $notice ] ) ) {
						printf(
							'<div class="notice notice-success"><p>%s</p></div>',
							esc_html( $messages[ $notice ] )
						);
					}
			}
			?>
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
		 * Requires the `amcb_manage_tools` capability.
		 */
	public static function run_migrations() {
			check_admin_referer( 'amcb_run_migrations' );

		if ( ! current_user_can( 'amcb_manage_tools' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
				wp_die( esc_html__( 'You are not allowed to perform this action.', 'amcb' ) );
		}

			Migrations::migrate();
			$redirect = add_query_arg(
				'amcb_notice',
				'migrations_run',
				admin_url( 'admin.php?page=amcb-tools' )
			);
			wp_safe_redirect( $redirect );
			exit;
	}

		/**
		 * Seed demo data action.
		 *
		 * Requires the `amcb_manage_tools` capability.
		 */
	public static function create_demo() {
			check_admin_referer( 'amcb_create_demo' );

		if ( ! current_user_can( 'amcb_manage_tools' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
				wp_die( esc_html__( 'You are not allowed to perform this action.', 'amcb' ) );
		}

			do_action( 'amcb_seed_demo' );
			$redirect = add_query_arg(
				'amcb_notice',
				'demo_created',
				admin_url( 'admin.php?page=amcb-tools' )
			);
			wp_safe_redirect( $redirect );
			exit;
	}
}

