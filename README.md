# AM Easy Custom Booking (Totaliweb) — Dev Guide

Plugin WordPress personalizzato per **Costabilerent**.  
Base: v0.1.0 con shortcode/widget e UI; sviluppo a step verso il sistema completo.

Repo: https://github.com/antonio86itna/am-easy-custom-booking

## Requisiti
- WordPress 6.5+, PHP 8.1–8.3
- Elementor Pro
- (Opz.) Composer (Stripe SDK), WPML

## Installazione rapida
1. Copia la cartella `am-easy-custom-booking` in `wp-content/plugins/`.
2. Attiva il plugin.
3. Impostazioni → **AMCB**: chiavi Stripe/Mapbox, deposito %, link policy.
4. Pagine:
   - Home: `[amcb_search]`
   - Risultati: `[amcb_results]`
   - Checkout: `[amcb_checkout]`
   - Dashboard: `[amcb_dashboard]`
   - Tariffe: `[amcb_tariffe]`

> La v0.1.0 fornisce UI di base e hook per evolvere. Il piano di lavoro è in **AGENTS.md**.

## Struttura
am-easy-custom-booking.php # bootstrap plugin
src/ # codice PHP (namespaces AMCB*)
assets/css|js/ # frontend
templates/emails/{it,en}/ # email HTML
languages/ # i18n .pot
wpml-config.xml # mapping WPML

## Lint e standard
- PHPCS (WordPress): `phpcs -p --standard=WordPress --extensions=php am-easy-custom-booking.php src`
- EditorConfig suggerito:
root = true
[*]
end_of_line = lf
insert_final_newline = true
charset = utf-8
indent_style = space
indent_size = 4

## Roadmap operativa (step principali)
1. **Migrazioni + Tools + Demo**
2. **Availability** (garage unico, overlap per giorni)
3. **Risultati** (solo disponibili + prezzo/gg da stagionalità)
4. **Calcolo prezzi** completo (servizi, assicurazione, sconti, coupon)
5. **Checkout wizard** con session/hold 15'
6. **Stripe** (Payment Intents, deposito %)
7. **Email + PDF** voucher con QR
8. **Dashboard cliente** + Cron flip stati e cancellazioni policy
9. **Mapbox** (sedi e consegna a domicilio), **Calendario**, **Report/CSV**, **Coupon**

## QA checklist (per ogni PR)
- [ ] PHPCS OK
- [ ] i18n completo
- [ ] Sicurezza (nonce/cap/escaping)
- [ ] Query preparate
- [ ] Documentazione aggiornata (README/AGENTS)
- [ ] Test manuale di flusso: ricerca → risultati → checkout UI

## Note
- Pagine dinamiche (risultati/checkout/dashboard) vanno escluse dal caching
- Il campo **Paese** del cliente (Step 3) è obbligatorio e guiderà la localizzazione notifiche/dashboard
