<?php
// phpcs:ignoreFile
/**
 * Shortcode handlers.
 *
 * @package amcb
 */

// phpcs:disable WordPress.Files.FileName.NotHyphenatedLowercase,WordPress.Files.FileName.InvalidClassFileName

namespace AMCB\Front;

/**
 * Shortcode handlers.
 */
class Shortcodes {
		/**
		 * Register all shortcodes.
		 *
		 * @return void
		 */
	public static function register() {
			add_shortcode( 'amcb_search', array( __CLASS__, 'search' ) );
			add_shortcode( 'amcb_results', array( __CLASS__, 'results' ) );
			add_shortcode( 'amcb_checkout', array( __CLASS__, 'checkout' ) );
			add_shortcode( 'amcb_dashboard', array( __CLASS__, 'dashboard' ) );
			add_shortcode( 'amcb_tariffe', array( __CLASS__, 'tariffe' ) );
	}

		/**
		 * Render the search form.
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string
		 */
	public static function search( $atts = array() ) {
			wp_enqueue_style( 'amcb-frontend' );
			wp_enqueue_script( 'amcb-frontend' );
			ob_start(); ?>
				<section class="amcb-search-wrap">
						<div class="amcb-card">
						<h2 class="amcb-title"><?php echo esc_html__( 'Rent a car or scooter in Ischia', 'amcb' ); ?></h2>
						<form class="amcb-form" action="<?php echo esc_url( home_url( '/results' ) ); ?>" method="get">
								<div class="amcb-grid">
								<label><?php esc_html_e( 'Pick-up date', 'amcb' ); ?><input type="date" name="start_date" required></label>
								<label><?php esc_html_e( 'Return date', 'amcb' ); ?><input type="date" name="end_date" required></label>
								<label><input type="checkbox" name="home_delivery" value="1"> <?php esc_html_e( 'Request home delivery (free)', 'amcb' ); ?></label>
								<label><?php esc_html_e( 'Pick-up location', 'amcb' ); ?>
										<select name="pickup">
										<option value="ischia-porto">Ischia Porto</option>
										<option value="forio">Forio</option>
										</select>
								</label>
								<label><?php esc_html_e( 'Drop-off location', 'amcb' ); ?>
										<select name="dropoff">
										<option value="ischia-porto">Ischia Porto</option>
										<option value="forio">Forio</option>
										</select>
								</label>
								</div>
								<button class="amcb-btn amcb-btn-primary" type="submit"><?php esc_html_e( 'Search Availability', 'amcb' ); ?></button>
						</form>
						</div>
				</section>
				<?php
				return ob_get_clean();
	}

		/**
		 * Render the results page.
		 *
		 * @return string
		 */
	public static function results() {
			wp_enqueue_style( 'amcb-frontend' );
			ob_start();
		?>
				<section class="amcb-results">
						<h2><?php esc_html_e( 'Available vehicles', 'amcb' ); ?></h2>
						<div class="amcb-vehicles">
						<div class="amcb-vehicle-card">
								<div class="amcb-vehicle-thumb"></div>
								<div class="amcb-vehicle-body">
								<h3>Fiat Panda</h3>
								<p class="amcb-price">€45 / <?php esc_html_e( 'day', 'amcb' ); ?></p>
								<a class="amcb-btn amcb-btn-primary" href="<?php echo esc_url( home_url( '/checkout' ) ); ?>"><?php esc_html_e( 'Book now', 'amcb' ); ?></a>
								</div>
						</div>
						</div>
				</section>
				<?php
				return ob_get_clean();
	}

		/**
		 * Render the checkout wizard.
		 *
		 * @return string
		 */
	public static function checkout() {
			wp_enqueue_style( 'amcb-frontend' );
			wp_enqueue_script( 'amcb-checkout' );
			ob_start();
		?>
				<div class="amcb-checkout-wizard" data-step="1">
						<div class="amcb-steps">
						<span class="on">1</span><span>2</span><span>3</span><span>4</span><span>5</span>
						</div>
						<div class="amcb-step amcb-step-1">
						<h2><?php esc_html_e( 'Step 1: Additional Services', 'amcb' ); ?></h2>
						<label><input type="checkbox" name="child_seat"> <?php esc_html_e( 'Child seat (€5/day)', 'amcb' ); ?></label>
						<label><input type="checkbox" name="gps"> <?php esc_html_e( 'GPS Navigator (€7/day)', 'amcb' ); ?></label>
						<button class="amcb-btn amcb-next"><?php esc_html_e( 'Continue', 'amcb' ); ?></button>
						</div>
						<div class="amcb-step amcb-step-2" hidden>
						<h2><?php esc_html_e( 'Step 2: Insurance', 'amcb' ); ?></h2>
						<label><input type="radio" name="ins" checked> <?php esc_html_e( 'Basic (included)', 'amcb' ); ?></label>
						<label><input type="radio" name="ins"> <?php esc_html_e( 'Premium (variable price/day)', 'amcb' ); ?></label>
						<button class="amcb-btn amcb-prev"><?php esc_html_e( 'Back', 'amcb' ); ?></button>
						<button class="amcb-btn amcb-next"><?php esc_html_e( 'Continue', 'amcb' ); ?></button>
						</div>
						<div class="amcb-step amcb-step-3" hidden>
						<h2><?php esc_html_e( 'Step 3: Customer Details', 'amcb' ); ?></h2>
						<div class="amcb-grid">
								<input type="text" placeholder="<?php esc_attr_e( 'First name', 'amcb' ); ?>">
								<input type="text" placeholder="<?php esc_attr_e( 'Last name', 'amcb' ); ?>">
								<input type="email" placeholder="<?php esc_attr_e( 'Email', 'amcb' ); ?>">
								<input type="tel" placeholder="<?php esc_attr_e( 'Phone', 'amcb' ); ?>">
								<select name="country" required>
								<option value=""><?php esc_html_e( 'Select Country', 'amcb' ); ?></option>
								<option value="IT"><?php esc_html_e( 'Italy', 'amcb' ); ?></option>
								<option value="GB"><?php esc_html_e( 'United Kingdom', 'amcb' ); ?></option>
								<option value="FR"><?php esc_html_e( 'France', 'amcb' ); ?></option>
								<option value="DE"><?php esc_html_e( 'Germany', 'amcb' ); ?></option>
								<option value="US"><?php esc_html_e( 'United States', 'amcb' ); ?></option>
								</select>
						</div>
						<label><input type="checkbox" class="amcb-bill-toggle"> <?php esc_html_e( 'I request an invoice', 'amcb' ); ?></label>
						<div class="amcb-billing" hidden>
								<input type="text" placeholder="<?php esc_attr_e( 'Company name', 'amcb' ); ?>">
								<input type="text" placeholder="<?php esc_attr_e( 'Billing address', 'amcb' ); ?>">
								<input type="text" placeholder="<?php esc_attr_e( 'VAT/Tax ID', 'amcb' ); ?>">
						</div>
						<button class="amcb-btn amcb-prev"><?php esc_html_e( 'Back', 'amcb' ); ?></button>
						<button class="amcb-btn amcb-next"><?php esc_html_e( 'Continue', 'amcb' ); ?></button>
						</div>
						<div class="amcb-step amcb-step-4" hidden>
						<h2><?php esc_html_e( 'Step 4: Summary and Payment', 'amcb' ); ?></h2>
						<label><input type="radio" name="paymode" checked> <?php esc_html_e( 'Pay full amount', 'amcb' ); ?></label>
						<label><input type="radio" name="paymode"> <?php esc_html_e( 'Pay a deposit (30%)', 'amcb' ); ?></label>
						<div class="amcb-terms">
								<label><input type="checkbox" required> <?php esc_html_e( 'I have read and agree to the Privacy Policy and Terms', 'amcb' ); ?></label>
						</div>
						<button class="amcb-btn amcb-prev"><?php esc_html_e( 'Back', 'amcb' ); ?></button>
						<button class="amcb-btn amcb-next"><?php esc_html_e( 'Confirm and Pay', 'amcb' ); ?></button>
						</div>
						<div class="amcb-step amcb-step-5" hidden>
						<h2><?php esc_html_e( 'Step 5: Secure Payment', 'amcb' ); ?></h2>
						<div id="amcb-stripe-element"></div>
						<button class="amcb-btn amcb-btn-success"><?php esc_html_e( 'Pay now', 'amcb' ); ?></button>
						</div>
				</div>
				<?php
				return ob_get_clean();
	}

		/**
		 * Render the user dashboard.
		 *
		 * @return string
		 */
	public static function dashboard() {
			wp_enqueue_style( 'amcb-frontend' );
			ob_start();
		?>
				<h2><?php esc_html_e( 'My Dashboard', 'amcb' ); ?></h2>
				<div class="amcb-dashboard">
						<div class="amcb-booking-card">
						<div class="amcb-booking-title">Fiat Panda – <strong>ISCHIA-971487</strong></div>
						<div class="amcb-booking-dates">02/08/2025 - 04/08/2025</div>
						<span class="amcb-badge success"><?php esc_html_e( 'Confirmed', 'amcb' ); ?></span>
						</div>
				</div>
				<?php
				return ob_get_clean();
	}

		/**
		 * Render the rates table.
		 *
		 * @return string
		 */
	public static function tariffe() {
			wp_enqueue_style( 'amcb-frontend' );
			ob_start();
		?>
				<h2><?php esc_html_e( 'Rates', 'amcb' ); ?></h2>
				<table class="amcb-table">
						<thead><tr><th><?php esc_html_e( 'Vehicle', 'amcb' ); ?></th><th><?php esc_html_e( 'Low Season', 'amcb' ); ?></th><th><?php esc_html_e( 'High Season', 'amcb' ); ?></th></tr></thead>
						<tbody><tr><td>Fiat Panda</td><td>€35</td><td>€60</td></tr></tbody>
				</table>
				<?php
				return ob_get_clean();
	}
}
