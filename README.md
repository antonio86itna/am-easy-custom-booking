# AM Easy Custom Booking (Totaliweb) — Developer Guide (v1.2)

**Baseline:** v0.1.0 (starter). We will evolve to v0.2.0 with incremental PRs.

Repo: https://github.com/antonio86itna/am-easy-custom-booking

## Goals
Custom WordPress booking plugin for **Costabilerent** (car & scooter).
- Search → Results → 5‑step Checkout → Stripe (full/deposit) → Confirmation
- **Single garage** inventory (central stock, no per‑location stock)
- Customer Dashboard, Email templates (i18n), PDF voucher with QR
- Mapbox pickup points, Coupons, Calendar, Reports
- REST‑first backend

## Requirements
- WordPress 6.5+, PHP 8.1–8.3
- Elementor Pro
- Optional: Composer (for Stripe/Dompdf/QR), WPML

## Quick install
1. Copy `am-easy-custom-booking` into `wp-content/plugins/` and activate it.
2. Settings → **AMCB**: set Stripe/Mapbox keys, deposit %, policy links.
3. Pages:
   - Home: `[amcb_search]` (or Elementor widget **AMCB – Search**)
   - Results: `[amcb_results]`
   - Checkout: `[amcb_checkout]`
   - Dashboard: `[amcb_dashboard]`
   - Rates: `[amcb_tariffe]`

## Coding standards
- WPCS/PHPCS:  
  `phpcs -p --standard=WordPress --extensions=php am-easy-custom-booking.php src`
- i18n: English is the **default** language. Wrap all strings with `__()`/`_e()` and domain `amcb`. Translations via `.po/.mo` or WPML.

## Dynamic pages and caching
Exclude **Results**, **Checkout**, **Dashboard** from any page‑cache/fragment‑cache.

## Roadmap to v0.2.0 (PR sequence)
1. **Migrations + Tools + Demo** (dbDelta, roles, cron, demo data)
2. **Availability engine** (date‑range overlap & central stock)
3. **Results** (only available vehicles; seasonal daily price)
4. **Pricing** (base + insurance + add‑ons + long‑rent discounts + coupons)
5. **Checkout + 15' session hold** (pending booking; abandoned capture)
6. **Stripe Payment Intents** (full/deposit) + **Webhook**
7. **PDF voucher + QR** (stored in uploads; link in dashboard)
8. **Cron & Emails** (pre‑pickup reminders, status flip, post‑return)
9. **Mapbox**, **Calendar**, **Reports/CSV**, **Coupons UI**

## QA checklist per PR
- [ ] PHPCS passes
- [ ] i18n complete (English default)
- [ ] Security (nonce/caps/escape/sanitize); prepared SQL
- [ ] README/AGENTS updated
- [ ] Manual flow test: search → results → checkout UI
