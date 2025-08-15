
---

## ✅ AGENTS.md (v1.3) — sostituisci integralmente con questo

```markdown
# AGENTS.md — Operational Brief (Codex‑ready, v1.3)

Repo: https://github.com/antonio86itna/am-easy-custom-booking  
Baseline: **v0.1.0** (starter UI). Target next release: **v0.2.0**.

## Mission
Deliver a complete booking system for Costabilerent (cars/scooters) with a **single garage**. Follow WordPress standards (security, performance, i18n), Elementor widgets, REST‑first, Stripe, Mapbox, WPML.

## Language & i18n
- Default language: **English** (frontend + backend).
- Wrap every string with `__()` / `_e()` (domain `amcb`).
- Provide translations via `.po/.mo` or WPML String Translation.
- Keep `wpml-config.xml` in sync to expose plugin options.

## Architecture
- Custom WP plugin (`AMCB\*`), frontend via **shortcodes + Elementor widgets**.
- 5‑step checkout wizard; 15‑minute session hold (transients).
- REST API for search/pricing/checkout/payment.
- Email HTML templates (EN/IT); PDF voucher with QR (saved in uploads).
- Mapbox for locations and home delivery pin (Ischia only, limited geofencing).
- Cache‑safe: Results/Checkout/Dashboard must not be cached.

## Data model (custom tables)
`amcb_vehicles`, `amcb_vehicle_prices`, `amcb_vehicle_blocks`,  
`amcb_services`, `amcb_insurances`,  
`amcb_bookings`, `amcb_booking_items`, `amcb_booking_totals`,  
`amcb_coupons`, `amcb_locations`, `amcb_abandoned`, `amcb_logs`.

## Booking states
`pending → paid → confirmed → in_progress → completed` (+ `canceled`, `refunded`, `abandoned`, `expired_hold`).

## REST endpoints (v0.2.0)
- `GET  /amcb/v1/search?start_date&end_date&pickup&dropoff&home_delivery=0|1`
- `POST /amcb/v1/checkout/price` → **seasonal segmented** breakdown (see rules)
- `POST /amcb/v1/checkout/prepare` → create pending booking + start hold
- `POST /amcb/v1/checkout/intent` → Stripe Payment Intent (full/deposit)
- `POST /amcb/v1/stripe/webhook` → intent success ⇒ `paid→confirmed` + `booking_code`
- `GET  /amcb/v1/bookings/me` (auth)
- `GET  /amcb/v1/bookings/{id}/voucher` (auth) → PDF

### Pricing rules (server‑side) — Seasonal & Custom Rates
**Inputs**: `vehicle_id`, `start_date`, `end_date` (end exclusive), optional `pickup_time/dropoff_time`, `pickup`, `dropoff`, `home_delivery`, `services[]`, `insurance_id/default`, `coupon_code?`, `payment_mode=full|deposit`, `currency=EUR`.

**Day count**: calendar days in `[start_date, end_date)`; if **Late Return Rule** and `dropoff_time > 10:00` add **+1** day (pricing only).

**Seasonal rates** (`amcb_vehicle_prices`):
- For each day, pick the matching `[date_from, date_to]` rate. If multiple match, use the most specific (`date_from` latest).
- No coverage for any day ⇒ error `NO_RATE_COVERAGE` (422), include `missing_dates:[]`.
- `base_total` = sum of **daily** prices across seasons (not a single rate * days).
- Validate **min/max days** (per‑season or global).

**Long‑rent discounts**: single highest tier (from `long_rent_json`) applied on **base_total**.

**Insurance**: `insurance_total = days * daily_price`.

**Services**: `services_total = Σ(flat) + Σ(per_day * days)`.

**Coupons**: scope‐aware; apply on  
`subtotal_before_coupon = base_total - long_rent_discount + insurance_total + services_total`.

**Grand total & deposit**:
`grand_total = subtotal_before_coupon - coupon_discount` (≥ 0).  
If `payment_mode=deposit`, compute `deposit_amount` from plugin setting; `to_collect = grand_total - deposit_amount`.

**Output** must include: `days`, `segments[]`, `base_total`, `long_rent_discount`, `insurance_total`, `services_total`, `coupon_discount`, `grand_total`, `deposit_amount`, `to_collect`, `currency`.

## Milestones (small PRs)
1. **PR‑1:** Migrations (dbDelta) + Admin Tools + Demo + roles + cron
2. **PR‑2:** Availability engine (range overlap) + Results (only available)
3. **PR‑3:** Elementor widgets (Results, Checkout, Dashboard, Rates)
4. **PR‑4:** REST + pricing breakdown + 15' session hold
5. **PR‑5:** Stripe Payment Intents (full/deposit) + webhook (paid→confirmed + booking_code)
6. **PR‑6:** PDF voucher + QR (Dompdf + QR lib) + dashboard link
7. **PR‑7:** Checkout session hold + /prepare — ⏳ next
8. **PR‑8:** Cron automations + email templates + status flips

## Operational notes
- Concurrency: do a final availability check on /checkout/prepare and recheck before confirming after webhook if hold expired.
- Stripe idempotency: use a deterministic key per booking and amount ("amcb:pi:booking:{id}:{amount}").

## Definition of Done
- PHPCS passes (WordPress standard), prepared SQL only
- i18n complete (English default), no hard‑coded untranslated strings
- Nonce/capabilities for sensitive actions; sanitize input, escape output
- Manual E2E test (search → results → checkout UI)
- README/AGENTS updated with changes

## Conventions
- Branches: `feat/*`, `fix/*`, `chore/*`, `docs/*`
- Conventional commits
- Namespace: `AMCB\*`
