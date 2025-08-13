# AM Easy Custom Booking (Totaliweb)

**Stato**: Starter skeleton v0.1.0 – WordPress plugin per autonoleggi (Costabilerent).  
Frontend a **widget/shortcode Elementor**, disponibilità con garage unico, Stripe (Payment Intents) e Mapbox.

## Requisiti
- WordPress 6.5+, PHP 8.1+ (ok 8.3)
- Elementor Pro
- (Opzionale) Composer per `stripe/stripe-php`
- WPML se multilingua

## Installazione rapida
1. Carica la cartella `am-easy-custom-booking` in `wp-content/plugins/` (oppure usa lo ZIP rilasciato).
2. Attiva il plugin in WP.
3. In **Impostazioni → AMCB** imposta: chiavi Stripe, token Mapbox, percentuale deposito, link Privacy/Termini.
4. Crea le pagine e inserisci i widget/shortcode:
   - Home: `[amcb_search]`
   - Risultati: `[amcb_results]`
   - Checkout: `[amcb_checkout]`
   - Dashboard: `[amcb_dashboard]`
   - Tariffe: `[amcb_tariffe]`
5. (Facoltativo) `composer require stripe/stripe-php` nella cartella del plugin.

## Widget Elementor
- **AMCB – Search** (categoria “AMCB (Totaliweb)”) – rende il form di ricerca.
  Gli altri widget arriveranno nelle versioni successive; sono già disponibili come shortcode.

## Note sul checkout
- Nel passo 3 è incluso il **campo Paese** (obbligatorio) per localizzazione delle email e contenuti.
- Le checkbox di consenso privacy/termini sono nello step 4.

## Struttura
```
src/            Codice PHP (namespaces AMCB\*)
assets/         CSS/JS
templates/      Email HTML (it/en)
languages/      .pot
wpml-config.xml WPML mapping opzioni
```
## Roadmap essenziale
- Tabelle custom e algoritmo disponibilità
- Endpoint REST e webhook Stripe
- PDF voucher + QR
- Calendario/timeline e report
- Email per ogni evento + abandoned

## Licenza
Proprietario – © 2025 Totaliweb. Uso interno Costabilerent.
