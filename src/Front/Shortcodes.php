<?php
namespace AMCB\Front;

class Shortcodes {
    public static function register() {
        add_shortcode('amcb_search', [__CLASS__, 'search']);
        add_shortcode('amcb_results', [__CLASS__, 'results']);
        add_shortcode('amcb_checkout', [__CLASS__, 'checkout']);
        add_shortcode('amcb_dashboard', [__CLASS__, 'dashboard']);
        add_shortcode('amcb_tariffe', [__CLASS__, 'tariffe']);
    }
    public static function search($atts=[]) {
        wp_enqueue_style('amcb-frontend'); wp_enqueue_script('amcb-frontend');
        ob_start(); ?>
        <section class="amcb-search-wrap">
          <div class="amcb-card">
            <h2 class="amcb-title"><?php echo esc_html__('Noleggia un auto o scooter a Ischia','amcb'); ?></h2>
            <form class="amcb-form" action="<?php echo esc_url( home_url('/risultati') ); ?>" method="get">
              <div class="amcb-grid">
                <label><?php _e('Data di ritiro','amcb'); ?><input type="date" name="start_date" required></label>
                <label><?php _e('Data di riconsegna','amcb'); ?><input type="date" name="end_date" required></label>
                <label><input type="checkbox" name="home_delivery" value="1"> <?php _e('Richiedi consegna a domicilio (gratuita)','amcb'); ?></label>
                <label><?php _e('Sede di ritiro','amcb'); ?>
                  <select name="pickup">
                    <option value="ischia-porto">Ischia Porto</option>
                    <option value="forio">Forio</option>
                  </select>
                </label>
                <label><?php _e('Sede di riconsegna','amcb'); ?>
                  <select name="dropoff">
                    <option value="ischia-porto">Ischia Porto</option>
                    <option value="forio">Forio</option>
                  </select>
                </label>
              </div>
              <button class="amcb-btn amcb-btn-primary" type="submit"><?php _e('Cerca Disponibilità','amcb'); ?></button>
            </form>
          </div>
        </section>
        <?php return ob_get_clean();
    }
    public static function results() {
        wp_enqueue_style('amcb-frontend');
        ob_start(); ?>
        <section class="amcb-results">
          <h2><?php _e('Veicoli disponibili','amcb'); ?></h2>
          <div class="amcb-vehicles">
            <div class="amcb-vehicle-card">
              <div class="amcb-vehicle-thumb"></div>
              <div class="amcb-vehicle-body">
                <h3>Fiat Panda</h3>
                <p class="amcb-price">€45 / <?php _e('giorno','amcb'); ?></p>
                <a class="amcb-btn amcb-btn-primary" href="<?php echo esc_url( home_url('/checkout') ); ?>"><?php _e('Prenota','amcb'); ?></a>
              </div>
            </div>
          </div>
        </section>
        <?php return ob_get_clean();
    }
    public static function checkout() {
        wp_enqueue_style('amcb-frontend'); wp_enqueue_script('amcb-checkout');
        ob_start(); ?>
        <div class="amcb-checkout-wizard" data-step="1">
          <div class="amcb-steps">
            <span class="on">1</span><span>2</span><span>3</span><span>4</span><span>5</span>
          </div>
          <div class="amcb-step amcb-step-1">
            <h2><?php _e('Step 1: Servizi Aggiuntivi','amcb'); ?></h2>
            <label><input type="checkbox" name="child_seat"> <?php _e('Seggiolino per bambini (€5/giorno)','amcb'); ?></label>
            <label><input type="checkbox" name="gps"> <?php _e('GPS Navigatore (€7/giorno)','amcb'); ?></label>
            <button class="amcb-btn amcb-next"><?php _e('Continua','amcb'); ?></button>
          </div>
          <div class="amcb-step amcb-step-2" hidden>
            <h2><?php _e('Step 2: Assicurazione','amcb'); ?></h2>
            <label><input type="radio" name="ins" checked> <?php _e('Base (inclusa)','amcb'); ?></label>
            <label><input type="radio" name="ins"> <?php _e('Premium (prezzo/gg variabile)','amcb'); ?></label>
            <button class="amcb-btn amcb-prev"><?php _e('Indietro','amcb'); ?></button>
            <button class="amcb-btn amcb-next"><?php _e('Continua','amcb'); ?></button>
          </div>
          <div class="amcb-step amcb-step-3" hidden>
            <h2><?php _e('Step 3: Dati Cliente','amcb'); ?></h2>
            <div class="amcb-grid">
              <input type="text" placeholder="<?php esc_attr_e('Nome','amcb'); ?>">
              <input type="text" placeholder="<?php esc_attr_e('Cognome','amcb'); ?>">
              <input type="email" placeholder="Email">
              <input type="tel" placeholder="<?php esc_attr_e('Telefono','amcb'); ?>">
              <select name="country" required>
                <option value=""><?php esc_html_e('Seleziona Paese','amcb'); ?></option>
                <option value="IT">Italia</option>
                <option value="GB">United Kingdom</option>
                <option value="FR">France</option>
                <option value="DE">Deutschland</option>
                <option value="US">United States</option>
              </select>
            </div>
            <label><input type="checkbox" class="amcb-bill-toggle"> <?php _e('Richiedo fattura','amcb'); ?></label>
            <div class="amcb-billing" hidden>
              <input type="text" placeholder="<?php esc_attr_e('Ragione sociale','amcb'); ?>">
              <input type="text" placeholder="<?php esc_attr_e('Indirizzo fatturazione','amcb'); ?>">
              <input type="text" placeholder="<?php esc_attr_e('VAT/Tax ID','amcb'); ?>">
            </div>
            <button class="amcb-btn amcb-prev"><?php _e('Indietro','amcb'); ?></button>
            <button class="amcb-btn amcb-next"><?php _e('Continua','amcb'); ?></button>
          </div>
          <div class="amcb-step amcb-step-4" hidden>
            <h2><?php _e('Step 4: Riepilogo e Pagamento','amcb'); ?></h2>
            <label><input type="radio" name="paymode" checked> <?php _e('Paga intero importo','amcb'); ?></label>
            <label><input type="radio" name="paymode"> <?php _e('Paga un deposito (30%)','amcb'); ?></label>
            <div class="amcb-terms">
              <label><input type="checkbox" required> <?php _e('Ho letto e accetto la Privacy Policy e i Termini','amcb'); ?></label>
            </div>
            <button class="amcb-btn amcb-prev"><?php _e('Indietro','amcb'); ?></button>
            <button class="amcb-btn amcb-next"><?php _e('Conferma e Paga','amcb'); ?></button>
          </div>
          <div class="amcb-step amcb-step-5" hidden>
            <h2><?php _e('Step 5: Pagamento Sicuro','amcb'); ?></h2>
            <div id="amcb-stripe-element"></div>
            <button class="amcb-btn amcb-btn-success"><?php _e('Paga ora','amcb'); ?></button>
          </div>
        </div>
        <?php return ob_get_clean();
    }
    public static function dashboard() {
        wp_enqueue_style('amcb-frontend');
        ob_start(); ?>
        <h2><?php _e('La Mia Area Personale','amcb'); ?></h2>
        <div class="amcb-dashboard">
          <div class="amcb-booking-card">
            <div class="amcb-booking-title">Fiat Panda – <strong>ISCHIA-971487</strong></div>
            <div class="amcb-booking-dates">02/08/2025 - 04/08/2025</div>
            <span class="amcb-badge success"><?php _e('Confermata','amcb'); ?></span>
          </div>
        </div>
        <?php return ob_get_clean();
    }
    public static function tariffe() {
        wp_enqueue_style('amcb-frontend');
        ob_start(); ?>
        <h2><?php _e('Tariffe','amcb'); ?></h2>
        <table class="amcb-table">
          <thead><tr><th><?php _e('Veicolo','amcb'); ?></th><th><?php _e('Bassa Stagione','amcb'); ?></th><th><?php _e('Alta Stagione','amcb'); ?></th></tr></thead>
          <tbody><tr><td>Fiat Panda</td><td>€35</td><td>€60</td></tr></tbody>
        </table>
        <?php return ob_get_clean();
    }
}
