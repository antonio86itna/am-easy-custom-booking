# AM Easy Custom Booking (Totaliweb) — Developer Guide (v1.3)

**Baseline:** v0.1.0 (starter). We will evolve to v0.2.0 with incremental PRs.

Repo: https://github.com/antonio86itna/am-easy-custom-booking

## Goals
Custom WordPress booking plugin for **Costabilerent** (car & scooter).
- Search → Results → 5‑step Checkout → Stripe (full/deposit) → Confirmation
- **Single garage** inventory (central stock, no per‑location stock)
- Customer Dashboard, i18n email templates, PDF voucher with QR
- Mapbox pickup points, Coupons, Calendar, Reports
- REST‑first backend

## Requirements
- WordPress 6.5+, PHP 8.1–8.3
- Elementor Pro
- Optional: Composer (Stripe/Dompdf/QR), WPML

## Quick install
1. Copy `am-easy-custom-booking` into `wp-content/plugins/` and activate it.
2. Settings → **AMCB**: set Stripe/Mapbox keys, deposit %, policy links.
3. Pages:
   - Home: `[amcb_search]` (or Elementor widget **AMCB – Search**)
   - Results: `[amcb_results]`
   - Checkout: `[amcb_checkout]`
   - Dashboard: `[amcb_dashboard]`
   - Rates: `[amcb_tariffe]`

## Upgrade

Deactivate and reactivate the plugin after updating to rerun database migrations. This refreshes the `amcb_db_version` option and applies schema changes (e.g. DB **1.2.0** adds a `hold_until` column to `amcb_bookings` for session holds).

## Coding standards & i18n
- WPCS/PHPCS:  
  `phpcs -p --standard=WordPress --extensions=php am-easy-custom-booking.php src`
- **Default language is English** (frontend + backend). Wrap **all** strings with `__()`/`_e()` (domain: `amcb`).  
  Translations via `.po/.mo` or WPML String Translation.

## Dynamic pages and caching
Exclude **Results**, **Checkout**, **Dashboard** from page/fragment caching.

## Seasonal & Custom Pricing (server‑side)
**Inputs**: `vehicle_id`, `start_date`, `end_date` (end exclusive), `pickup`, `dropoff`, `home_delivery`, `services[]`, `insurance_id`, `coupon_code?`, `payment_mode=full|deposit`, `currency=EUR`.

**Day count**: number of calendar days in `[start_date, end_date)`.  
If **Late Return Rule** is enabled and `dropoff_time > 10:00`, add **+1** day (pricing only).

**Seasonal rates** (`amcb_vehicle_prices`): per‑day ranges `[date_from, date_to]` with `price_per_day` (+ optional `min_days`, `max_days`, `long_rent_json`).  
For **each day** in the range pick the matching rate (most specific by `date_from`).  
If any day has **no coverage** → error `NO_RATE_COVERAGE`.  
**Base total** = **sum of daily prices** (spanning multiple seasons if needed).

**Vehicle limits**: validate duration (min/max) for all segments spanned.

**Long‑rent discounts**: `long_rent_json` (e.g. `[{"min_days":3,"percent":5},{"min_days":7,"percent":10}]`). Apply the **highest** matching tier on **base_total** only.

**Insurance**: `days * daily_price` of the selected (or default) vehicle insurance.

**Services**: sum of `flat` + `per_day * days`.

**Coupons**: scope‐aware (`all|auto|scooter|vehicles|dates`); apply on  
`subtotal_before_coupon = base_total - long_rent_discount + insurance_total + services_total`.

**Grand total / deposit**:  
`grand_total = subtotal_before_coupon - coupon_discount` (min 0).  
Deposit mode: `deposit_amount = round(grand_total * deposit_percent/100, 2)`, `to_collect = grand_total - deposit_amount`.

**Roadmap to v0.2.0 (PR sequence)**

Migrations + Tools + Demo (dbDelta, roles, cron, demo data)

Availability engine (date‑range overlap & central stock)

Results (only available vehicles; seasonal daily price)

Pricing (base + insurance + add‑ons + long‑rent discounts + coupons)

Checkout + 15' session hold (pending booking; abandoned capture)

Stripe Payment Intents (full/deposit) + Webhook

PDF voucher + QR (uploads; link in dashboard)

Cron & Emails (pre‑pickup reminders, status flip, post‑return)

Mapbox, Calendar, Reports/CSV, Coupons UI

Working with ChatGPT Codex
Use the Manual bootstrap script (Composer, WP‑CLI, PHPCS, i18n command).

Keep PRs small and reviewable. Run PHPCS before every commit.

Use Conventional Commits (feat:, fix:, docs:, …).

QA checklist per PR
 PHPCS passes

 i18n complete (English default)

 Security (nonce/caps/escape/sanitize); prepared SQL

 README/AGENTS updated

 Manual flow test: search → results → checkout UI

## Progress (towards v0.2.0)
- [x] PR‑1: Migrations + Tools + Demo + Roles + Cron
- [x] PR‑2: Availability engine + Results
- [x] PR‑3: REST pricing breakdown + Checkout summary
- [x] PR‑4: Admin UI shell (menu, settings, stubs)
- [x] HF: Demo & Diagnostics + Widgets + Search binding
- [ ] HF: Quick Add Vehicle (admin stub)
- [ ] PR‑5: Checkout 15' session hold + /checkout/prepare
- [ ] PR‑6: Stripe Payment Intents (+ webhook)
- [ ] PR‑7: PDF voucher with QR
- [ ] PR‑8: Cron automations + email templates

**Output example**
```json
{
  "days": 3,
  "segments": [
    {"from":"2025-08-02","to":"2025-08-03","daily":60,"days":1,"total":60},
    {"from":"2025-08-03","to":"2025-08-04","daily":60,"days":1,"total":60},
    {"from":"2025-08-04","to":"2025-08-05","daily":40,"days":1,"total":40}
  ],
  "base_total": 160,
  "long_rent_discount": 8,
  "insurance_total": 24,
  "services_total": 10,
  "coupon_discount": 0,
  "grand_total": 186,
  "deposit_amount": 55.8,
  "to_collect": 130.2,
  "currency": "EUR"
}
