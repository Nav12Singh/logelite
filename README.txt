=== Logelite ===

Contributors: logelite
Requires at least: 6.5
Tested up to: 6.5
Requires PHP: 8.1
WC requires at least: 8.5
WC tested up to: 8.5
Version: 1.0.0
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: e-commerce, custom-menu, translation-ready, threaded-comments

Custom classic WordPress + WooCommerce storefront theme, built from a reference design. No page builders, no full-site editing, no ACF, no premium plugins.

== Description ==

Logelite is a classic (non-block, non-FSE) WordPress theme built specifically as a WooCommerce storefront. Every template, hook, and style is hand-built theme code — there is no page builder, no `theme.json`-driven site editor, and no dependency on ACF or any premium plugin. All customization happens through the WordPress Customizer, the Menus screen, and a small set of theme-specific meta boxes on the product edit screen.

= Minimum requirements =

* WordPress 6.5 or later
* WooCommerce 8.5 or later
* PHP 8.1 or later
* A modern browser (theme JS targets evergreen Chrome/Firefox/Safari/Edge)

= Key features =

* **Header** — mega-menu navigation, logo badge + wordmark + tagline lockup, global promo strip (category dropdown, search, trust badges), live cart fragment with running subtotal.
* **Footer** — five-column layout (brand/address, three widget-driven navigation columns, newsletter signup), brand fields editable from the Customizer.
* **Home page** — hero + sidebar + category-tile row, "Deals of the Day" section driven by real on-sale products with a live countdown and sales-progress bar, "Best Sellers" (ordered by actual popularity), and a closing CTA/newsletter banner row.
* **Shop page** — bordered page header, configurable products-per-row and per-page counts, category filter, price-range slider, attribute filters.
* **Single product page** — three-column layout, variation swatches for color/size-style attributes, "Buy It Now", a Bundle Offer meta box (admin-defined quantity/discount tiers), a feature-icons row, a product FAQ tab (with matching JSON-LD), a delivery/pincode estimator, and a sticky mobile add-to-cart bar.
* **Checkout** — two-column layout built on WooCommerce Blocks (Store API), gift-message and delivery date/slot fields (self-hosted flatpickr date picker, no CDN), a restyled coupon form, and radio-card shipping-method selection.
* **Thank-you page** — redesigned order-summary recap.
* **My Account** — added to the primary navigation menu automatically.
* **Contact page** — dedicated `page-contact.php` template.
* **Motion** — button, product-card, FAQ-accordion, and mobile-nav micro-interactions, all built on `transform`/`opacity` only and gated behind a sitewide `prefers-reduced-motion` contract.
* Fully translatable (text domain `logelite`), no hardcoded/untranslated user-facing strings.
* No CDN assets — every font, icon, and vendor script (e.g. flatpickr) ships locally in `assets/`.

= What this theme deliberately does not do =

* No page builder integration (Elementor, Divi, etc.).
* No full-site editing / block-theme (`theme.json`) support — this is a classic PHP-template theme.
* No bundled ACF or ACF-dependent fields — all custom data uses core meta boxes and the Settings API.
* No premium/third-party plugin requirement beyond WooCommerce itself.

== Installation ==

1. **Install WordPress and WooCommerce first.** Activate WooCommerce *before* activating this theme — several theme features (product meta boxes, checkout fields, the checkout Blocks integration) register against WooCommerce hooks and classes on `init`/`after_setup_theme` and expect the plugin to already be loaded.
2. **Upload the theme.**
   * Via wp-admin: *Appearance → Themes → Add New → Upload Theme*, choose `logelite.zip`, then *Install Now → Activate*.
   * Or via file access: unzip into `wp-content/themes/logelite/`, then activate from *Appearance → Themes*.
3. **Import sample data**, if a `wp_db.sql` has been provided separately (not bundled with the theme itself):
   * WP-CLI: `wp db import wp_db.sql`
   * phpMyAdmin: select the target database → *Import* tab → choose file → *Go*
   Import into a **fresh** WordPress database *before* activating the theme, then re-save permalinks (*Settings → Permalinks → Save Changes*) so WooCommerce's rewrite rules regenerate against the imported page IDs.
4. **Check WooCommerce → Status → Tools** once after import/activation and confirm no "database update needed" notice is showing. Run it if one appears.
5. **Confirm a shipping zone/method is configured** under *WooCommerce → Settings → Shipping* — the shipping-method selection on checkout has nothing to render otherwise.
6. **Confirm the Cart and Checkout pages use WooCommerce Blocks**, not the classic shortcodes — this theme's checkout layout targets the Blocks/Store API markup. These are WooCommerce's own default pages/blocks on a fresh install; only relevant if migrating an existing site.
7. **Set a custom logo and site tagline** under *Appearance → Customize → Site Identity* (optional) — the header falls back to a generated badge + site name + tagline lockup when no logo image is uploaded.
8. **Assign a primary menu** under *Appearance → Menus* (or *Customize → Menus*) to the "Primary" location — "My Account" is appended to it automatically.

== Frequently Asked Questions ==

= Does this theme require WooCommerce? =

Yes. It is a storefront theme built specifically around WooCommerce's hooks, templates, and data — most features have no meaning without it active.

= Can I use a page builder with this theme? =

No official support. Templates are hand-coded classic PHP; a page builder is not part of the intended workflow and hasn't been tested against it.

= Does this theme work with the WordPress Site Editor / block themes? =

No. This is a classic theme by design — no `theme.json`, no full-site editing.

= Where do I report a bug or see what was changed and why? =

See `ASSUMPTIONS.md` in the theme directory — every inferred-rather-than-specified decision, and every fix made along the way, is logged there.

== Changelog ==

= 1.0.0 =
* Initial theme build: template hierarchy, WooCommerce integration, product/checkout customizations, motion/animation pass, and a full design-match rebuild against the supplied reference design, followed by iterative fixes across header, footer, home, shop, product, checkout, thank-you, and account-menu areas.

== Credits ==

* Built for Logelite as a custom theme; no third-party theme framework used.
* Bundled vendor asset: flatpickr (date picker), used under its MIT license, self-hosted in `assets/js/vendor/` and `assets/css/vendor/` — no CDN.

== Resources ==

* `ASSUMPTIONS.md` — full log of inferred decisions and fixes, organized by ticket (T1–T6) and, for the post-rebuild work, by non-sequential "Phase N" entries under a single T7 section.
* `README.md` — developer-facing setup/build notes (linting, known gaps, feature list).
* `phpcs.xml.dist` — WordPress Coding Standards ruleset used by `composer run lint`.
