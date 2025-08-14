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
	 * Returns early if data already exists.
	 *
	 * @return void
	 */
	public static function run() {
		global $wpdb;

		$vehicle_table   = $wpdb->prefix . 'amcb_vehicles';
		$price_table     = $wpdb->prefix . 'amcb_vehicle_prices';
		$insurance_table = $wpdb->prefix . 'amcb_insurances';
		$service_table   = $wpdb->prefix . 'amcb_services';
		$location_table  = $wpdb->prefix . 'amcb_locations';

		$has_data = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$vehicle_table}" );

		if ( $has_data > 0 ) {
			esc_html_e( 'Demo data already seeded.', 'amcb' );
			return;
		}

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
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$vehicle_table} WHERE name = %s",
					$vehicle['name']
				)
			);

			if ( ! $exists ) {
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
				$exists = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT id FROM {$price_table} WHERE vehicle_id = %d",
						$vehicle_id
					)
				);

				if ( ! $exists ) {
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
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$insurance_table} WHERE name = %s",
					$insurance['name']
				)
			);

			if ( ! $exists ) {
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
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$service_table} WHERE name = %s",
					$service['name']
				)
			);

			if ( ! $exists ) {
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

		// Locations.
		$locations = array(
			array(
				'name'    => 'Ischia Port',
				'address' => 'Ischia, Italy',
			),
			array(
				'name'    => 'Forio Port',
				'address' => 'Forio, Italy',
			),
		);

		foreach ( $locations as $location ) {
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$location_table} WHERE name = %s",
					$location['name']
				)
			);

			if ( ! $exists ) {
				$wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$location_table} (name, address) VALUES (%s, %s)",
						$location['name'],
						$location['address']
					)
				);
			}
		}

		esc_html_e( 'Demo data seeded.', 'amcb' );
	}
}

DemoSeeder::init();
