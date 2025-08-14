<?php
/**
 * Plugin activation and deactivation handler.
 *
 * @package AMCB
 */
// phpcs:ignoreFile

namespace AMCB\Install;

/**
 * Handle plugin activation and deactivation.
 */
class Activator {
    /**
     * Run on plugin activation.
     *
     * Creates custom roles:
     * - `amcb_customer` — basic read access.
     * - `amcb_manager` — adds `amcb_manage_bookings` capability for booking management.
     */
    public static function activate() {
        Migrations::migrate();

        // Basic customer role. Only grants read access.
        add_role(
            'amcb_customer',
            __( 'Booking Customer', 'amcb' ),
            array(
                'read' => true,
            )
        );

        // Manager role. Can manage plugin bookings and settings.
        add_role(
            'amcb_manager',
            __( 'Booking Manager', 'amcb' ),
            array(
                'read'                 => true,
                'amcb_manage_bookings' => true,
            )
        );
    }

    /**
     * Run on plugin deactivation.
     *
     * Removes custom roles.
     */
    public static function deactivate() {
        // Cleanup scheduled events if any.

        remove_role( 'amcb_customer' );
        remove_role( 'amcb_manager' );
    }
}

