<?php
// phpcs:ignoreFile
/**
 * Demo data seeder for AMCB tables.
 *
 * @package AMCB
 */


namespace AMCB\Install;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Seed demo data for development purposes.
 */
class DemoSeeder {
	/**
	 * Register seeder hook.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'amcb_seed_demo', array( __CLASS__, 'run' ) );
	}

	/**
	 * Run the demo seeder.
	 *
         * Inserts demo vehicles, prices, insurances, services and locations.
         * Safe to run multiple times without creating duplicates.
	 *
	 * @return void
	 */
	public static function run() {
		global $wpdb;

                $vehicle_table   = $wpdb->prefix . 'amcb_vehicles';
                $price_table     = $wpdb->prefix . 'amcb_vehicle_prices';
                $block_table     = $wpdb->prefix . 'amcb_vehicle_blocks';
                $insurance_table = $wpdb->prefix . 'amcb_insurances';
                $service_table   = $wpdb->prefix . 'amcb_services';
                $coupon_table    = $wpdb->prefix . 'amcb_coupons';
                $location_table  = $wpdb->prefix . 'amcb_locations';

		// Vehicles.
		$vehicles = array(
			array(
				'name' => 'Fiat 500',
				'type' => 'car',
			),
			array(
				'name' => 'Piaggio Vespa',
				'type' => 'scooter',
			),
		);

               foreach ( $vehicles as $vehicle ) {
                       $exists = (int) $wpdb->get_var(
                               $wpdb->prepare(
                                       "SELECT COUNT(*) FROM {$vehicle_table} WHERE name = %s",
                                       $vehicle['name']
                               )
                       );

                       if ( 0 === $exists ) {
                               $wpdb->query(
                                       $wpdb->prepare(
                                               "INSERT INTO {$vehicle_table} (name, type, status) VALUES (%s, %s, 'active')",
                                               $vehicle['name'],
                                               $vehicle['type']
                                       )
                               );
                       }
               }

		// Vehicle prices.
		foreach ( $vehicles as $index => $vehicle ) {
			$vehicle_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$vehicle_table} WHERE name = %s",
					$vehicle['name']
				)
			);

                       if ( $vehicle_id ) {
                               $exists = (int) $wpdb->get_var(
                                       $wpdb->prepare(
                                               "SELECT COUNT(*) FROM {$price_table} WHERE vehicle_id = %d",
                                               $vehicle_id
                                       )
                               );

                               if ( 0 === $exists ) {
                                       $wpdb->query(
                                               $wpdb->prepare(
                                                       "INSERT INTO {$price_table} (vehicle_id, date_from, date_to, price) VALUES (%d, %s, %s, %f)",
                                                       $vehicle_id,
                                                       gmdate( 'Y-m-d' ),
                                                       gmdate( 'Y-m-d', strtotime( '+1 year' ) ),
                                                       $index ? 30.00 : 40.00
                                               )
                                       );
                               }
                       }
               }

		// Insurances.
		$insurances = array(
			array(
				'name'        => 'Basic Coverage',
				'price'       => 10.0,
				'description' => 'Basic insurance coverage.',
			),
			array(
				'name'        => 'Full Coverage',
				'price'       => 20.0,
				'description' => 'Full insurance coverage.',
			),
		);

               foreach ( $insurances as $insurance ) {
                       $exists = (int) $wpdb->get_var(
                               $wpdb->prepare(
                                       "SELECT COUNT(*) FROM {$insurance_table} WHERE name = %s",
                                       $insurance['name']
                               )
                       );

                       if ( 0 === $exists ) {
                               $wpdb->query(
                                       $wpdb->prepare(
                                               "INSERT INTO {$insurance_table} (name, price, description) VALUES (%s, %f, %s)",
                                               $insurance['name'],
                                               $insurance['price'],
                                               $insurance['description']
                                       )
                               );
                       }
               }

		// Services.
		$services = array(
			array(
				'name'    => 'Helmet',
				'price'   => 5.0,
				'per_day' => 0,
			),
			array(
				'name'    => 'Child Seat',
				'price'   => 3.0,
				'per_day' => 1,
			),
		);

               foreach ( $services as $service ) {
                       $exists = (int) $wpdb->get_var(
                               $wpdb->prepare(
                                       "SELECT COUNT(*) FROM {$service_table} WHERE name = %s",
                                       $service['name']
                               )
                       );

                       if ( 0 === $exists ) {
                               $wpdb->query(
                                       $wpdb->prepare(
                                               "INSERT INTO {$service_table} (name, price, per_day) VALUES (%s, %f, %d)",
                                               $service['name'],
                                               $service['price'],
                                               $service['per_day']
                                       )
                               );
                       }
               }

               // Vehicle blocks.
               $blocks = array(
                       array(
                               'vehicle_name' => 'Fiat 500',
                               'date_from'    => gmdate( 'Y-m-d', strtotime( '+2 days' ) ),
                               'date_to'      => gmdate( 'Y-m-d', strtotime( '+5 days' ) ),
                               'reason'       => 'Maintenance',
                       ),
               );

               foreach ( $blocks as $block ) {
                       $vehicle_id = $wpdb->get_var(
                               $wpdb->prepare(
                                       "SELECT id FROM {$vehicle_table} WHERE name = %s",
                                       $block['vehicle_name']
                               )
                       );

                       if ( $vehicle_id ) {
                               $exists = (int) $wpdb->get_var(
                                       $wpdb->prepare(
                                               "SELECT COUNT(*) FROM {$block_table} WHERE vehicle_id = %d AND date_from = %s AND date_to = %s",
                                               $vehicle_id,
                                               $block['date_from'],
                                               $block['date_to']
                                       )
                               );

                               if ( 0 === $exists ) {
                                       $wpdb->query(
                                               $wpdb->prepare(
                                                       "INSERT INTO {$block_table} (vehicle_id, date_from, date_to, reason) VALUES (%d, %s, %s, %s)",
                                                       $vehicle_id,
                                                       $block['date_from'],
                                                       $block['date_to'],
                                                       $block['reason']
                                               )
                                       );
                               }
                       }
               }

               // Coupons.
               $coupons = array(
                       array(
                               'code'        => 'WELCOME10',
                               'amount'      => 10.0,
                               'type'        => 'flat',
                               'usage_limit' => 100,
                       ),
               );

               foreach ( $coupons as $coupon ) {
                       $exists = (int) $wpdb->get_var(
                               $wpdb->prepare(
                                       "SELECT COUNT(*) FROM {$coupon_table} WHERE code = %s",
                                       $coupon['code']
                               )
                       );

                       if ( 0 === $exists ) {
                               $wpdb->query(
                                       $wpdb->prepare(
                                               "INSERT INTO {$coupon_table} (code, amount, type, usage_limit, expires_at) VALUES (%s, %f, %s, %d, NULL)",
                                               $coupon['code'],
                                               $coupon['amount'],
                                               $coupon['type'],
                                               $coupon['usage_limit']
                                       )
                               );
                       }
               }

                // Locations.
                $locations = array(
                        array(
                                'name'    => 'Ischia Porto',
                                'address' => 'Ischia, Italy',
                        ),
                        array(
                                'name'    => 'Forio',
                                'address' => 'Forio, Italy',
                        ),
                );

               foreach ( $locations as $location ) {
                       $exists = (int) $wpdb->get_var(
                               $wpdb->prepare(
                                       "SELECT COUNT(*) FROM {$location_table} WHERE name = %s",
                                       $location['name']
                               )
                       );

                       if ( 0 === $exists ) {
                               $wpdb->query(
                                       $wpdb->prepare(
                                               "INSERT INTO {$location_table} (name, address) VALUES (%s, %s)",
                                               $location['name'],
                                               $location['address']
                                       )
                               );
                       }
               }

                return;
        }
}
