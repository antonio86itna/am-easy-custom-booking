# AGENTS.md — Brief per agenti/AI (Codex) • AM Easy Custom Booking

**Repo:** https://github.com/antonio86itna/am-easy-custom-booking  
**Stato di partenza:** v0.1.0 (skeleton funzionante con shortcode/widget base)  
**Stack:** WordPress plugin custom + Elementor Pro, Stripe, Mapbox, WPML

## North Star
Realizzare un sistema completo di autonoleggio “Costabilerent”:
- Ricerca → Risultati → Checkout wizard 5 step → Pagamento (full/deposito) → Conferma
- Garage **unico** (stock centralizzato); disponibilità per intervallo, anti overbooking
- Dashboard cliente, email template (i18n), PDF voucher + QR, cron/automation, report
- Backend: tabelle custom + REST API first

## Vincoli e standard
- PHP ≥ 8.1 (target 8.3), WordPress 6.5+
- Coding standard: WordPress/WPCS (PHPCS)
- Sicurezza: escaping/sanitization/nonce/capabilities; SQL sempre preparato
- i18n: `__()/_e()` e file `.pot`; compatibilità WPML (wpml-config.xml)
- Niente dipendenze “pesanti”: Stripe SDK via Composer, niente ACF (se non richiesto)
- Cache safe: le pagine dinamiche (risultati/checkout/dashboard) vanno **escluse** dai cache plugin

## Stato attuale (v0.1.0)
- Bootstrap plugin + autoloader
- Shortcode/widget: **Search**, **Results**, **Checkout**, **Dashboard**, **Tariffe** (UI base)
- Admin Settings di base (Stripe/Mapbox/links) — placeholders
- Asset CSS/JS e template email base bilingue
(vedi README della repo per i dettagli e struttura attuale). 

## Dati e tabelle (da implementare nelle prossime versioni)
- `amcb_vehicles (id, name, type, stock_total, featured, featured_priority, …)`
- `amcb_vehicle_prices (vehicle_id, date_from, date_to, price_per_day, …)`
- `amcb_vehicle_blocks (vehicle_id, start_date, end_date, qty, …)`
- `amcb_services (name, charge_type: flat/per_day, price, vehicle_type, active)`
- `amcb_insurances (vehicle_id, name, daily_price, franchise, is_default)`
- `amcb_bookings (booking_code, user_id, email, phone, country, status, payment_mode, totals…)`
- `amcb_booking_items (booking_id, vehicle_id, start_date, end_date, pickup, dropoff, home_delivery, …)`
- `amcb_booking_totals (booking_id, base_total, insurance_total, services_total, discount_total, grand_total)`
- `amcb_coupons (code, type, amount, scope, vehicles_json, date_from/to, min_days, active)`
- `amcb_locations (slug, name, address, lat, lng)`
- `amcb_abandoned (email, name, vehicle_name, start_date, end_date, token, emailed_at, converted_booking_id)`
- `amcb_logs (channel, level, message, context, created_at)`

## Stati prenotazione
`pending → paid → confirmed → in_progress → completed → (refunded|canceled)`  
Extra: `abandoned`, `expired_hold`.

## Piano a step (partire da v0.1.0)

**M1 – Migrazioni + Tools + Dati demo**
- Aggiungi `src/Install/Activator.php` con `dbDelta()` per tutte le tabelle.
- Admin → AMCB → **Tools**: pulsanti “Migrazioni DB” e “Crea dati demo” (Fiat Panda + sedi Ischia).
- Ruoli: `amcb_customer`, `amcb_manager`; cron: `amcb_cron_minutely`, `amcb_cron_hourly`.

**M2 – Availability engine**
- `src/Front/Availability.php`: calcolo unità disponibili per ogni giorno nel range:
  - somma prenotazioni attive (`paid/confirmed/in_progress`) + `vehicle_blocks`
  - `available = stock_total - max(occupancyPerDay)`; >0 ⇒ disponibile.

**M3 – Risultati dinamici**
- `[amcb_results]`: mostra solo i veicoli disponibili per `start_date/end_date` con prezzo/gg (selezione da `vehicle_prices`).
- Ordina: featured desc, featured_priority desc, name asc.

**M4 – Calcolo prezzi e breakdown**
- Implementa calcolo: base (giorni * price/gg) + assicurazione (+/gg) + servizi (flat o per_day * giorni) − sconti lunghi − coupon.
- Salva in `amcb_booking_totals`.

**M5 – Wizard checkout + session/hold (15 minuti)**
- `src/Front/Session.php` via transients + cookie `amcb_sid`.
- Alla conferma step 4: crea **booking** in `pending` + **booking_items**; hold mentale (non detrae stock, ma evita doppia gara con user stesso).
- Abandoned: se non paga entro 1h → email ricordino; hold scade a 15’.

**M6 – Stripe Payment Intents**
- Server: crea Intent (full/deposit), gestisci capture post-webhook.
- Webhook: `payment_intent.succeeded` ⇒ `paid` + genera `booking_code` unico progressivo (`RC-YYYY-000001`).
- Auto-registrazione utente (se email nuova), invio credenziali via email.

**M7 – Email e PDF**
- Template EN/IT; conferma, pre-ritiro, post-riconsegna; invio admin/manager/cliente.
- PDF voucher con QR (codice prenotazione); link in dashboard.

**M8 – Dashboard cliente**
- Elenco prenotazioni (future, passate, stato pagamento), dettaglio, richiesta cancellazione (rispettando policy).
- Flip automatico stato con cron: `confirmed → in_progress` all’avvio, `in_progress → completed` alla riconsegna.

**M9 – Mapbox, Calendario, Report, Coupon**
- Mapbox: sedi/indirizzi; se home delivery, pin su indirizzo cliente.
- Calendario timeline/list (uscite/rientri del giorno).
- Report & CSV, codici sconto.

## Definition of Done per PR
- PHPCS ok (`WordPress` standard)
- Tutto tradotto (`__()/_e()`); nessun testo hardcoded non i18n
- Security review (nonce, caps, esc_*, sanitize_*)
- Niente query non preparate
- README aggiornato; CHANGELOG entry
- Test manuali documentati (vedi README “QA checklist”)

## Convenzioni
- Branch: `feat/*`, `fix/*`, `chore/*`, `docs/*`
- Commit: Conventional Commits (`feat:`, `fix:`, …)
- PHP namespace: `AMCB\\…`
