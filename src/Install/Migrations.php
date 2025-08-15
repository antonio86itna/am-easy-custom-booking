<?php
/**
 * Database migrations for AMCB tables.
 *
 * @package AMCB
 */
// phpcs:ignoreFile
namespace AMCB\Install;

/**
 * Handle database schema migrations for AMCB tables.
 */
class Migrations {
        /**
         * Current database schema version.
         *
         * Versions:
         * - 1.1.0 Initial schema.
         * - 1.2.0 Added hold_until column to bookings table.
         *
         * @var string
         */
        const DB_VERSION = '1.2.0';

	/**
	 * Retrieve SQL table schemas.
	 *
	 * @return array
	 */
	public static function get_table_schemas() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'amcb_';

		$tables = array();

                $tables['vehicles'] = "CREATE TABLE {$prefix}vehicles (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(191) NOT NULL,
            type varchar(50) NOT NULL DEFAULT '',
            status varchar(20) NOT NULL DEFAULT 'active',
            stock_total int unsigned NOT NULL DEFAULT 0,
            featured tinyint(1) NOT NULL DEFAULT 0,
            featured_priority smallint unsigned NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		$tables['vehicle_prices'] = "CREATE TABLE {$prefix}vehicle_prices (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            vehicle_id bigint(20) unsigned NOT NULL,
            date_from date NOT NULL,
            date_to date NOT NULL,
            price decimal(10,2) NOT NULL DEFAULT 0,
            min_days smallint(5) unsigned DEFAULT NULL,
            max_days smallint(5) unsigned DEFAULT NULL,
            long_rent_json text,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY vehicle_id (vehicle_id)
        ) {$charset_collate};";

		$tables['vehicle_blocks'] = "CREATE TABLE {$prefix}vehicle_blocks (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            vehicle_id bigint(20) unsigned NOT NULL,
            date_from date NOT NULL,
            date_to date NOT NULL,
            reason varchar(191) NOT NULL DEFAULT '',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY vehicle_id (vehicle_id)
        ) {$charset_collate};";

		$tables['services'] = "CREATE TABLE {$prefix}services (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(191) NOT NULL,
            price decimal(10,2) NOT NULL DEFAULT 0,
            per_day tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

                $tables['insurances'] = "CREATE TABLE {$prefix}insurances (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            vehicle_id bigint(20) unsigned NOT NULL,
            name varchar(191) NOT NULL,
            price_per_day decimal(10,2) NOT NULL DEFAULT 0,
            description text,
            is_default tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY vehicle_id (vehicle_id)
        ) {$charset_collate};";

                $tables['bookings'] = "CREATE TABLE {$prefix}bookings (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            customer_id bigint(20) unsigned NOT NULL DEFAULT 0,
            status varchar(20) NOT NULL DEFAULT 'pending',
            booking_code varchar(50) NOT NULL DEFAULT '',
            start_date date NOT NULL,
            end_date date NOT NULL,
            pickup_id bigint(20) unsigned NOT NULL DEFAULT 0,
            dropoff_id bigint(20) unsigned NOT NULL DEFAULT 0,
            hold_until datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY customer_id (customer_id),
            KEY booking_code (booking_code)
        ) {$charset_collate};";

		$tables['booking_items'] = "CREATE TABLE {$prefix}booking_items (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            booking_id bigint(20) unsigned NOT NULL,
            vehicle_id bigint(20) unsigned NOT NULL,
            days smallint(5) unsigned NOT NULL DEFAULT 0,
            price decimal(10,2) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY booking_id (booking_id)
        ) {$charset_collate};";

		$tables['booking_totals'] = "CREATE TABLE {$prefix}booking_totals (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            booking_id bigint(20) unsigned NOT NULL,
            base_total decimal(10,2) NOT NULL DEFAULT 0,
            services_total decimal(10,2) NOT NULL DEFAULT 0,
            insurance_total decimal(10,2) NOT NULL DEFAULT 0,
            coupon_discount decimal(10,2) NOT NULL DEFAULT 0,
            grand_total decimal(10,2) NOT NULL DEFAULT 0,
            deposit_amount decimal(10,2) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY booking_id (booking_id)
        ) {$charset_collate};";

		$tables['coupons'] = "CREATE TABLE {$prefix}coupons (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            code varchar(50) NOT NULL,
            amount decimal(10,2) NOT NULL DEFAULT 0,
            type varchar(20) NOT NULL DEFAULT 'flat',
            usage_limit int(11) unsigned NOT NULL DEFAULT 0,
            expires_at datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code)
        ) {$charset_collate};";

		$tables['locations'] = "CREATE TABLE {$prefix}locations (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(191) NOT NULL,
            address varchar(191) NOT NULL DEFAULT '',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		$tables['abandoned'] = "CREATE TABLE {$prefix}abandoned (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            email varchar(191) NOT NULL DEFAULT '',
            payload longtext,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		$tables['logs'] = "CREATE TABLE {$prefix}logs (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            context varchar(50) NOT NULL DEFAULT '',
            message text NOT NULL,
            level varchar(20) NOT NULL DEFAULT 'info',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY context (context)
        ) {$charset_collate};";

		return $tables;
	}

	/**
	 * Run database migrations if needed.
	 */
        public static function migrate() {
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';

                foreach ( self::get_table_schemas() as $sql ) {
                        dbDelta( $sql );
                }

                $installed = get_option( 'amcb_db_version' );
                if ( ! $installed || version_compare( $installed, self::DB_VERSION, '<' ) ) {
                        update_option( 'amcb_db_version', self::DB_VERSION );
                }
        }
}
