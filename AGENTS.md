# AGENTS.md — Operational Brief (Codex‑ready)

Repo: https://github.com/antonio86itna/am-easy-custom-booking  
Baseline: **v0.1.0** (starter UI). Target next release: **v0.2.0**.

## Mission
Develop and maintain the **AM Easy Custom Booking** plugin for Costabilerent, following WordPress standards for security, performance, and excellent UX. Write modern, testable, and modular code.

## Architecture (snapshot)
- Custom WP Plugin (namespace `AMCB\*`), front pages managed by **shortcodes + Elementor widgets**.
- Data: custom tables for vehicles, prices, reservations, services, insurance, locations, coupons, logs.
- 5-step checkout wizard. Payments with Stripe Payment Intents (full or % deposit).
- User session with 15-minute soft-hold on availability.
- Mapbox for locations or home delivery (limited autocomplete for Ischia).
- Multilingual HTML email (EN/IT); PDF voucher with QR code (to be implemented).
- WPML-ready and cache-safe (no cache on search/results/checkout/dashboard).

## Reservation states
`pending → paid → confirmed → in_progress → completed` (+ `canceled`, `refunded`, `abandoned`, `expired_hold`).

## Shortcode/Key Widget
- `[amcb_search]` – Search form.
- `[amcb_results]` – List of available vehicles.
- `[amcb_checkout]` – Wizard.
- `[amcb_dashboard]` – Customer area.
- `[amcb_tariffe]` – Pricing table.
Each shortcode must have an equivalent **Elementor Widget**.

## Fundamental Rules
- **Single Garage**: Centralized inventory. Availability calculated per day over a range of dates, including blocks and active reservations.
- **Customer Country Required** (step 3) for dynamic localization.
- **GDPR**: Checkboxes for privacy, general terms, and rental conditions.
- **Cache**: Avoid caching on dynamic views; use `DONOTCACHEPAGE` when necessary.
- **Security**: Nonces, capability checks, escaping, prepared statements.
- **i18n**: All strings with `__()`; email and options mapped in `wpml-config.xml`.
- **Branding**: Totaliweb in admin, email footer, and code.

## North Star
A complete booking system for Costabilerent:
- Search → Results → 5‑step Checkout → Stripe (full/deposit) → Confirmation
- **Single garage** (central inventory); no per‑location stock
- Customer dashboard, i18n template emails, PDF voucher with QR, cron/automation, report
- REST‑first; Elementor widgets; WPML compatible

## Language & i18n
- Default language: **English** (both backend and frontend).
- All strings must use `__()` / `_e()` with domain `amcb`.
- Translations: `.po/.mo` or WPML String Translation.
- Keep `wpml-config.xml` updated to expose plugin options.

## Data model (custom tables)
- `amcb_vehicles`, `amcb_vehicle_prices`, `amcb_vehicle_blocks`
- `amcb_services`, `amcb_insurances`
- `amcb_bookings`, `amcb_booking_items`, `amcb_booking_totals`
- `amcb_coupons`, `amcb_locations`, `amcb_abandoned`, `amcb_logs`

## Booking states
`pending → paid → confirmed → in_progress → completed`  
Extra: `canceled`, `refunded`, `abandoned`, `expired_hold`.

## REST endpoints (v0.2.0)
- `GET  /amcb/v1/search?start_date&end_date&pickup&dropoff&home_delivery=0|1`
- `POST /amcb/v1/checkout/price`
- `POST /amcb/v1/checkout/prepare`
- `POST /amcb/v1/checkout/intent`
- `POST /amcb/v1/stripe/webhook`
- `GET  /amcb/v1/bookings/me` (auth)
- `GET  /amcb/v1/bookings/{id}/voucher` (auth)

## Pricing rules (server‑side) — Seasonal & Custom Rates
Inputs
vehicle_id, start_date, end_date (+ opzionale pickup_time, dropoff_time)
pickup, dropoff, home_delivery (boolean, free)
services[] (IDs), insurance_id (or “default”), coupon_code?
payment_mode = full | deposit
currency = EUR (default)
Day count
days = number of calendar days in [start_date, end_date) → end exclusive.
If “Late Return Rule” is enabled and dropoff_time > 10:00, add +1 day for pricing purposes only.
Seasonal price selection (per‑day)
selection (per-day)
Rates live in amcb_vehicle_prices as a range [date_from, date_to] with price_per_day + optional min_days, max_days, long_rent_json.
For each day in the period:
Select the rate line with date_from <= day <= date_to
If multiple lines match, use the one with the most recent (more specific) date_from.
No coverage rule: If a day has no rate coverage → NO_RATE_COVERAGE error. Vehicle search/display must hide the vehicle or display a consistent message.
Base total = sum of all daily price_per_days (not a single rate * days). In practice, if the range spans multiple seasons, the total reflects the mix.
Vehicle limits
Validate duration limits (min/max days). If limits are defined by season (columns in vehicle_prices), the booking is valid only if the entire range meets the limits of all segments crossed.
If you prefer global limits per vehicle, we will add vehicles.rent_min_days/rent_max_days (backlog) in the future.
days = diff(end, start)
base = days * seasonal daily price
insurance = days * selected daily insurance
services = sum(flat + per_day * days)
long-rent discounts + coupons
grand_total = base + insurance + services - discounts

## Data and tables (to be implemented in future versions)
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

## Immediate TODOs
1. Implement tables with `dbDelta` and migrations (Install/Activator).
2. Build Availability with efficient SQL (range overlap).
3. Register additional Elementor widgets (Results, Checkout, Dashboard, Rates).
4. Add complete Settings (depot, policies, locations, mapbox).
5. Stripe integration (intent, webhook) and unique booking_code generation.
6. Sessions + 15-minute timer and auto-expire.
7. “Abandoned” email after 1 hour (tracking attribution).

## Milestones (create small PRs)
1. **PR‑1:** Migrations (dbDelta) + Admin Tools + Demo + roles + cron
2. **PR‑2:** Availability engine (range overlap) + Results shows only available
3. **PR‑3:** Elementor widgets (Results, Checkout, Dashboard, Rates)
4. **PR‑4:** REST + pricing breakdown + 15' session hold (transients)
5. **PR‑5:** Stripe (Payment Intents full/deposit) + webhook (paid→confirmed)
6. **PR‑6:** PDF voucher + QR (Dompdf + QR lib) + link in dashboard
7. **PR‑7:** Cron automations + email templates (EN/IT) + status flips

## Definition of Done
- PHPCS passes; i18n complete; prepared SQL only
- Security: nonce/capabilities, sanitize input, escape output
- Tests: manual flow + README/AGENTS updated
- No cache issues on dynamic pages

## Conventions
- Branches: `feat/*`, `fix/*`, `chore/*`, `docs/*`
- Conventional commits
- Namespace: `AMCB\*`
