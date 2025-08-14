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
    const VERSION = 2;

    /**
     * Capabilities handled by the plugin.
     *
     * @var string[]
     */
    const CAPABILITIES = array(
        'amcb_manage_bookings',
        'amcb_manage_vehicles',
        'amcb_manage_prices',
        'amcb_manage_blocks',
        'amcb_manage_services',
        'amcb_manage_insurances',
        'amcb_manage_coupons',
        'amcb_manage_locations',
        'amcb_manage_tools',
        'amcb_manage_settings',
    );

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

        $caps = self::CAPABILITIES;

        foreach ( array( 'administrator', 'amcb_manager' ) as $role_name ) {
            $role = get_role( $role_name );

            if ( $role ) {
                $role->add_cap( 'amcb_manage_dashboard' );

                foreach ( $caps as $cap ) {
                    if ( 'amcb_manager' === $role_name && 'amcb_manage_settings' === $cap ) {
                        continue;
                    }

                    $role->add_cap( $cap );
                }
            }
        }

        update_option( 'amcb_caps_version', self::VERSION );
    }
}

