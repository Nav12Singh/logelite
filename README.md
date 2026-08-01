# Logelite

Custom classic WordPress + WooCommerce storefront theme. Built to the rules in `CLAUDE.md` — no page builders, no FSE, no ACF, no premium plugins.

Originally built ticket-by-ticket (T1–T6), then fully rebuilt end-to-end (**T7**) to match a supplied HTML/CSS design reference exactly, and iterated further since with a series of design/QA fix passes (header, footer, home, shop, product, checkout, thank-you, account menu). The design reference file itself was supplied out of band during that work and was never committed to this repo.

## Requirements

- WordPress 6.5+
- WooCommerce 8.5+
- PHP 8.1+

## Install

1. **Install WordPress and WooCommerce first**, on a host meeting the versions above. Activate WooCommerce before activating this theme — several theme features (product meta boxes, checkout fields, checkout Blocks integration) register against WooCommerce hooks/classes on `init`/`after_setup_theme` and expect the plugin to already be loaded.
2. **Upload the theme.** In wp-admin: *Appearance → Themes → Add New → Upload Theme*, choose `logelite.zip`, then *Install Now → Activate*. (Or unzip into `wp-content/themes/logelite/` directly if you have file access.)
3. **Import sample data.** ⚠️ Not included in this delivery — see "Known gaps" below. If a `wp_db.sql` is provided separately, import it with either:
   - WP-CLI: `wp db import wp_db.sql`
   - phpMyAdmin: select the target database → *Import* tab → choose file → *Go*

   Either way, import it into a **fresh** WordPress database *before* activating the theme, then flush permalinks afterward (*Settings → Permalinks → Save Changes*, no changes needed) so WooCommerce's rewrite rules regenerate against the imported page IDs.
4. **Run through WooCommerce → Status → Tools** once after import/activation and confirm no "database update needed" notice is showing. If one appears, run it.
5. Confirm a default shipping zone/method exists (*WooCommerce → Settings → Shipping*) — the theme's shipping-method reskin has nothing to style if no method is configured.
6. Checkout uses WooCommerce Blocks (Store API) rather than the classic shortcode — confirm the Checkout and Cart pages contain the `[woocommerce_checkout]`/Cart blocks WooCommerce created on install, not the classic shortcodes.

## Features

- **Header** — mega-menu nav, logo badge + wordmark + tagline lockup, global promo strip (categories/search/trust badges), cart fragment with live subtotal.
- **Footer** — 5-column grid (brand/address, 3 widget nav columns, newsletter), Customizer-editable brand fields.
- **Home** — hero + sidebar + category-tiles row, "Deals of the Day" (real on-sale products, countdown, sales progress), "Best Sellers" (popularity-ordered), CTA/newsletter banner row.
- **Shop** — bordered hero, configurable grid columns/per-page, category filter, price-range slider, attribute filters.
- **Product** — 3-column layout, variation swatches, Buy It Now, Bundle Offer meta box, feature-icons row, product FAQ tab, delivery/pincode estimator, sticky mobile add-to-cart bar.
- **Checkout** — two-column layout on WooCommerce Blocks (Store API), gift message + delivery date/slot fields (self-hosted flatpickr), restyled coupon form, shipping-method radio cards.
- **Thank-you page** — order summary recap.
- **Account menu** — My Account link added to primary nav.
- **Contact page** template.
- Motion tokens with a `prefers-reduced-motion` contract; button/card/FAQ/nav micro-interactions.

## Assumptions

Every inferred-rather-than-specified decision made across this project is logged in **[`ASSUMPTIONS.md`](ASSUMPTIONS.md)**, not duplicated here. That file is the single source of truth; this README intentionally just points to it rather than keeping a second copy that would drift out of sync.

It's organized in two parts:
- **T1–T6**, chronological, one section per ticket — the original build.
- **T7**, one large section covering the full design-match rebuild and every fix pass since — internally broken into non-sequential **"Phase N"** entries (currently up to Phase 56) rather than further ticket numbers. Phase numbers are reused/interleaved across unrelated feature areas, so `grep -n "Phase 12"` etc. is more useful than reading top-to-bottom for a specific feature.

## Development

```
composer install
composer run lint       # phpcs --standard=phpcs.xml.dist
composer run lint:fix   # phpcbf --standard=phpcs.xml.dist
```

`phpcs.xml.dist` uses `WordPress-Extra` + `WordPress-Docs`, PHP compatibility checked against 8.1+ (this theme's declared minimum, per `style.css`'s `Requires PHP` header), and enforces the `lgl_`/`LGL_` prefix.

**Caveat, stated plainly:** no `php`/`phpcs`/`composer` binary has been available in the environment this theme was built in, for any ticket. Every file has been hand-formatted to WPCS conventions and manually/`grep`-audited for common violations (missing `ABSPATH` guards, unescaped output, sanitizer mismatches, undefined function calls — see `ASSUMPTIONS.md`'s T6 and "Post-T6" entries for real bugs that audit process actually found and fixed). `composer run lint` has never actually been executed against this codebase. **Run it before your first release.**

## Known gaps (read before demoing)

- **No `wp_db.sql` is included.** Producing one needs a working WooCommerce database to export from (`wp db export` or `mysqldump`), which hasn't been available. A site with sample products (all four WooCommerce product types, at least one variable product with a color/size-style attribute pair to exercise the swatch selector, a Bundle Offer meta box tier on a couple of products, and a `free-shipping`-slugged shipping class on a few others), at least two active coupons, and a configured shipping zone needs to be built and exported separately before this theme can be evaluated with realistic data.
- **`composer run lint` has never been run** — see "Development" above. Treat WPCS compliance as hand-audited, not tool-verified, until this happens.
- **Most visual QA has been code-reading + user-submitted screenshots, not a live browser check.** One exception: the header/nav pass (`header-fixes`) was verified live via Playwright at 375/768/1024/1440px. Earlier and unrelated areas have not had the same treatment — a full responsive/keyboard pass per CLAUDE.md's Definition of Done is still outstanding.
- **The theme zip has not been activation-tested end-to-end** (fresh WP + WooCommerce + this theme + sample data, confirmed working) — no second WordPress environment has been available to test against.
- A previously-reported "blank front-end page on every request" bug (undefined-function fatal in the breadcrumbs helper) **has been found and fixed** — see ASSUMPTIONS.md's "Post-T6" entry — but per the point above, that fix was confirmed via a real page load at the time, not re-verified since.

## Ticket effort, T3.0 → T6

Not wall-clock hours — this was built by an AI agent across a single long session, not tracked against a timesheet, and reporting fabricated hour counts would be worse than not reporting a number at all. What follows is a relative size/complexity rating per ticket instead, useful for the same purpose (spotting which areas got the most/least scrutiny):

| Ticket | Scope | Relative size |
|---|---|---|
| T3.0 | Meta-box/repeater/nonce foundation, icon whitelist | M |
| T3.1 | Feature icons: Settings API page + per-product override | L |
| T3.2 | Delivery estimator: REST route + rate limiting + zone lookup | L |
| T3.3 | Sticky cart bar: 4 product types, one jQuery bridge | L |
| T3.4 | Product FAQ: repeater, tab/section placement, JSON-LD | L |
| audit | Full T3 hook/security/product-type audit — found & fixed 2 real bugs | M |
| T4.0 | Two-column checkout layout | M |
| T4.1 | Custom checkout fields: render/validate/persist | L |
| T4.2 | Checkout-field display: admin/email/thank-you, one shared formatter | M |
| T4.3 | Coupon form restyle | S |
| T4.4 | Shipping-method radio-card reskin | S |
| T5.0 | Motion tokens + reduced-motion contract | S |
| T5.1 | Hero entrance animation — found & fixed an opacity conflict | S |
| T5.2 | Product card hover — found & fixed an `overflow:hidden` conflict | M |
| T5.3 | Button ripple/gradient/arrow micro-interactions | M |
| T5.4 | FAQ smooth expand/collapse — found & fixed a reduced-motion bug | M |
| T5.5 | Sticky cart slide — mostly confirmation of prior work | S |
| T5.6 | Mobile menu blur backdrop — mostly confirmation of prior work | S |
| T6 | Quality gate — found & fixed 2 missing-file-level critical bugs, 3 escaping bugs, 1 sitewide-CSS leak | L |

S/M/L = small/medium/large relative to the others in this list, not an absolute estimate. T7 (the design-match rebuild and everything since) is not scored the same way — see its Phase entries in `ASSUMPTIONS.md` directly.
