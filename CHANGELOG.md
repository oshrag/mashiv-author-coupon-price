# Changelog

All notable changes to this plugin will be documented in this file.

## [1.0.0] - 2026-06-01

### Added

- Created the `Mashiv Author Coupon Price` plugin.
- Added a custom checkbox field to WooCommerce coupon settings:
  - "קופון סופר - מחיר 30 ₪"
- Saved the checkbox value as coupon meta:
  - `_mashiv_author_coupon_price_30`
- Added cart price logic for marked coupons.
- When a marked coupon is applied, eligible coupon products are set to a fixed unit price of 30 NIS.
- Added support for both parent product IDs and variation IDs.
- Left WooCommerce built-in coupon restrictions responsible for:
  - allowed email addresses
  - product restrictions
  - coupon validation
