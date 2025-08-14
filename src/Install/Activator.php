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
     */
    public static function activate() {
        Migrations::migrate();
    }

    /**
     * Run on plugin deactivation.
     */
    public static function deactivate() {
        // Cleanup scheduled events if any.
    }
}
