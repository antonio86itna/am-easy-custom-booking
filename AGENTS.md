# AGENTS.md — Operational Brief (Codex‑ready)

Repo: https://github.com/antonio86itna/am-easy-custom-booking  
Baseline: **v0.1.0** (starter UI). Target next release: **v0.2.0**.

## North Star
A complete booking system for Costabilerent:
- Search → Results → 5‑step Checkout → Stripe (full/deposit) → Confirmation
- **Single garage** (central inventory); no per‑location stock
- Customer dashboard, i18n emails, PDF voucher with QR
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

## Pricing rules (server‑side)
days = diff(end, start)
base = days * seasonal daily price
insurance = days * selected daily insurance
services = sum(flat + per_day * days)
long-rent discounts + coupons
grand_total = base + insurance + services - discounts

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
