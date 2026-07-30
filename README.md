# Logelite

Custom classic WordPress + WooCommerce storefront theme. Built to the rules in `CLAUDE.md` — no page builders, no FSE, no ACF, no premium plugins.

## Requirements

- WordPress 6.5+
- WooCommerce 8.5+
- PHP 8.1+

## Install

1. **Install WordPress and WooCommerce first**, on a host meeting the versions above. Activate WooCommerce before activating this theme — several theme features (product meta boxes, checkout fields, the delivery-estimator REST route) register against WooCommerce hooks/classes on `init`/`after_setup_theme` and expect the plugin to already be loaded.
2. **Upload the theme.** In wp-admin: *Appearance → Themes → Add New → Upload Theme*, choose `logelite.zip`, then *Install Now → Activate*. (Or unzip into `wp-content/themes/logelite/` directly if you have file access.)
3. **Import sample data.** ⚠️ Not included in this delivery — see "Known gaps" below. If a `wp_db.sql` is provided separately, import it with either:
   - WP-CLI: `wp db import wp_db.sql`
   - phpMyAdmin: select the target database → *Import* tab → choose file → *Go*

   Either way, import it into a **fresh** WordPress database *before* activating the theme, then flush permalinks afterward (*Settings → Permalinks → Save Changes*, no changes needed) so WooCommerce's rewrite rules regenerate against the imported page IDs.
4. **Run through WooCommerce → Status → Tools** once after import/activation and confirm no "database update needed" notice is showing. If one appears, run it — a partially-run WooCommerce install (missing custom tables) is a known failure mode; see "Known gaps."
5. Confirm a default shipping zone/method exists (*WooCommerce → Settings → Shipping*) — the theme's shipping-method reskin (T4.4) has nothing to style if no method is configured.

## Assumptions

Every inferred-rather-than-specified decision made across this project — the Indian-pincode-format assumption, no-holiday-calendar simplification for delivery dates, Settings API chosen over the Customizer for global feature icons, `max-height` chosen over JS footer-collision detection for the sticky checkout aside, and everything else — is logged in **[`ASSUMPTIONS.md`](ASSUMPTIONS.md)**, not duplicated here. That file is the single source of truth; this README intentionally just points to it rather than keeping a second copy that would drift out of sync. It's organized chronologically by ticket (T1 → T6), newest at the bottom, and `grep`-able by ticket number if you're looking for a specific one's reasoning.

## Development

```
composer install
composer run lint       # phpcs --standard=phpcs.xml.dist
composer run lint:fix   # phpcbf --standard=phpcs.xml.dist
```

`phpcs.xml.dist` uses `WordPress-Extra` + `WordPress-Docs`, PHP compatibility checked against 8.1+ (this theme's declared minimum, per `style.css`'s `Requires PHP` header), and enforces the `lgl_`/`LGL_` prefix.

**Caveat, stated plainly:** no `php`/`phpcs`/`composer` binary was available in the environment this theme was built in, for any ticket, including this one. Every file has been hand-formatted to WPCS conventions and manually/`grep`-audited for common violations (missing `ABSPATH` guards, unescaped output, sanitizer mismatches — see `ASSUMPTIONS.md`'s T6 entry for the specific bugs that audit actually found and fixed), but `composer run lint` has never actually been executed against this codebase. Run it before your first release.

## Known gaps (read before demoing)

- **A live front-end bug is currently unresolved.** On the development site this theme was built against, every front-end page (home, shop) returns a blank response, while wp-admin, the REST API, and the RSS feed all work normally — isolating the problem to WordPress's theme-template-loading path specifically. The site's PHP error log also shows a WooCommerce database error (a missing custom table) logged during a recent plugin activation, suggesting an incomplete WooCommerce install may be a contributing factor. Full diagnostic notes are in `ASSUMPTIONS.md`'s T6 entry. **Do not consider this theme demo-ready until this is resolved and a real page load has been confirmed** — everything else in this README describes intent, not a verified end-to-end result.
- **No `wp_db.sql` is included.** Producing one needs a working WooCommerce database to export from (`wp db export` or `mysqldump`); neither was available. A site with sample products (all four WooCommerce product types), FAQ/feature-icon meta on a few of them, at least two active coupons, and a configured shipping zone needs to be built and exported separately before this theme can be evaluated with realistic data.
- **No `/screenshots` directory.** Capturing them needs a working, rendered front end (see the first point) plus a browser — neither was available here.
- **The theme zip has not been activation-tested end-to-end** (fresh WP + WooCommerce + this theme + sample data, confirmed working) for the same reason: no second WordPress environment was available to test against, and the primary one has the unresolved issue above.

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
| T6 | Quality gate — found & fixed 2 missing-file-level critical bugs, 3 escaping bugs, 1 sitewide-CSS leak; live front-end bug found but unresolved | L |

S/M/L = small/medium/large relative to the others in this list, not an absolute estimate.
