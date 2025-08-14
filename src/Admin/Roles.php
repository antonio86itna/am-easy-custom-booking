<?php // phpcs:ignoreFile
/**
 * Role capability utilities.
 *
 * @package AMCB
 */

namespace AMCB\Admin;

/**
 * Manage plugin capabilities.
 */
class Roles {
    /**
     * Current capabilities version.
     *
     * @var int
     */
    const VERSION = 1;

    /**
     * Ensure capabilities are set for required roles.
     *
     * @return void
     */
    public static function ensure_caps() {
        $caps_version = (int) get_option( 'amcb_caps_version', 0 );

        if ( self::VERSION === $caps_version ) {
            return;
        }

        foreach ( array( 'administrator', 'amcb_manager' ) as $role_name ) {
            $role = get_role( $role_name );

            if ( $role ) {
                $role->add_cap( 'amcb_manage_bookings' );
            }
        }

        update_option( 'amcb_caps_version', self::VERSION );
    }
}

