# AGENTS.md — Brief operativo per agenti AI (ChatGPT / Codex)

## Missione
Sviluppare e mantenere il plugin **AM Easy Custom Booking** per Costabilerent, seguendo standard WordPress, sicurezza, performance e UX eccellente. Scrivere codice moderno, testabile e modulare.

## Architettura (snapshot)
- WP Plugin custom (namespace `AMCB\*`), pagine front gestite da **shortcode + widget Elementor**.
- Dati: tabelle custom per veicoli, prezzi, prenotazioni, servizi, assicurazioni, sedi, coupon, log.
- Checkout wizard 5 step. Pagamenti con Stripe Payment Intents (full o deposito %).
- Sessione utente con soft-hold 15 min sulle disponibilità.
- Mapbox per sedi o consegna a domicilio (autocomplete limitato Ischia).
- Email HTML multilanguage (EN/IT); PDF voucher con QR (da implementare).
- WPML-ready e cache-safe (no-cache su search/results/checkout/dashboard).

## Stati prenotazione
`pending → paid → confirmed → in_progress → completed` (+ `canceled`, `refunded`, `abandoned`, `expired_hold`).

## Shortcode/Widget chiave
- `[amcb_search]` – Form ricerca.
- `[amcb_results]` – Lista veicoli disponibili.
- `[amcb_checkout]` – Wizard.
- `[amcb_dashboard]` – Area cliente.
- `[amcb_tariffe]` – Tabella prezzi.
Ogni shortcode deve avere equivalente **Widget Elementor**.

## Regole fondamentali
- **Garage unico**: stock centralizzato. Disponibilità calcolata per giorno su range date, includendo blocchi e prenotazioni attive.
- **Paese cliente obbligatorio** (step 3) per localizzazione dinamica.
- **GDPR**: checkbox per privacy, termini generali, condizioni noleggio.
- **Cache**: evitare caching su viste dinamiche; usare `DONOTCACHEPAGE` quando serve.
- **Sicurezza**: nonce, capability checks, escaping, prepared statements.
- **i18n**: tutte le stringhe con `__()`; email e opzioni mappate in `wpml-config.xml`.
- **Branding**: Totaliweb in admin, email footer, e codice.

## Stile & qualità
- PSR-4 + WordPress Coding Standards (PHPCS).
- Niente query N+1. Usare indici e query parametrizzate.
- Ogni PR deve includere: descrizione, screenshot/gif, note QA manuale.

## Branching & commit
- `main`: stabile. `develop`: integrazione.
- Feature branch `feat/*`, fix `fix/*`.
- Conventional commits: `feat:`, `fix:`, `chore:`, `refactor:`, `docs:`, `perf:`.

## TODO immediati (per gli agenti)
1. Implementare tabelle con `dbDelta` e migrazioni (Install/Activator).
2. Costruire Availability con SQL efficiente (range overlap).
3. Registrare altri Widget Elementor (Results, Checkout, Dashboard, Tariffe).
4. Aggiungere Settings completi (deposito, policy, sedi, mapbox).
5. Integrazione Stripe (intent, webhook) e generazione booking_code univoco.
6. Sessioni + timer 15 min e auto-expire.
7. Email per “abandoned” dopo 1h (tracking attribution).
