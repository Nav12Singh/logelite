# Assumptions

Decisions inferred rather than explicitly specified, logged per CLAUDE.md §11.6.

## inc/setup.php

- `@since` version tags set to `1.0.0` — no version history/style.css exists yet to derive an actual version from.
- `$content_width` default set to `1200` (px) — not specified in the task; matches the `max-width:1320px` container minus typical gutter seen in `design-reference/Logelite Theme.dc.html`, rounded down conventionally.
- Nav menu location labels ("Primary Menu", "Mobile Menu", "Footer Menu") and footer sidebar labels ("Footer Column 1-4") are placeholder translatable strings — not specified, chosen as conventional defaults.
- `wp_page_menu()` fallback is disabled via a `wp_nav_menu_args` filter (applies to `primary`, `mobile`, `footer` locations) rather than per-call `fallback_cb` args in template files, since no template files (header.php, footer.php) exist yet in this task.

## assets/css/_tokens.css and base.css

- Filename `_tokens.css` (leading underscore) was requested explicitly in this task; CLAUDE.md §3 lists the file as `tokens.css`. Kept the underscore as instructed — flagging the discrepancy in case it should be renamed to match §3.
- Root font size assumed to be the browser default `16px` (never set explicitly in `design-reference/`) — used to convert observed px values to rem.
- The reference used ~20 near-identical decorative greys (repeating-gradient image placeholders, hairline dividers). These were consolidated onto a 13-step neutral ramp rather than tokenized 1:1; see mapping table below for which raw hex rolled into which step.
- `--lgl-color-info` (`#1a73e8`) is **not** rendered anywhere in the static comp — it only appears as one of four selectable values in the component's `accent` customizer-style prop (`data-props` on the `<script>` tag). Included as the closest available signal for a future "info" state; no matching background/text pair exists in the reference, so only the base color was defined.
- `--lgl-shadow-sm`, `--lgl-shadow-md`, `--lgl-shadow-lg` are inferred/extrapolated. The reference defines exactly one shadow (`0 8px 24px rgba(20,24,28,.07)`, mapped to `--lgl-shadow-card-hover`); sm/md/lg scale the same hue/opacity proportionally for future use (dropdowns, modals) not present in this static comp.
- `--lgl-z-*` and all `--lgl-ease-*` / `--lgl-dur-*` motion tokens are fully inferred — the reference is static markup with no `z-index`, `transition`, or `animation` declarations anywhere. Values are conventional defaults, added to satisfy CLAUDE.md §8's motion/z-index needs for later interactive components (nav toggle, sticky cart, carousel, FAQ accordion).
- `--lgl-header-h` (`78px`) is derived from the main logo/nav row only (42px logo + 18px top/bottom padding). The utility bar (40px) and the search/promo bar (60px) are separate, explicitly-sized rows and are not folded into this token.
- `--lgl-container-narrow` (`900px`) takes the widest of three "narrow" prose max-widths seen on the product page tabs (900px description, 820px reviews, 720px additional info) since no single narrow-container value repeats.
- Spacing scale normalized to a strict 4px-based ratio (`--lgl-space-1`…`24`). The reference's actual paddings/gaps are hand-picked, not gridded (e.g. `18px`, `22px`, `26px`, `34px`, `38px`, `52px`, `60px` all appear) — components will round to the nearest token rather than the scale reproducing every literal value.

## inc/enqueue.php

- **Fonts are self-hosted, not fetched.** `design-reference/Logelite Theme.dc.html` loads Google Fonts via `<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=Public+Sans:wght@400;500;600;700&display=swap">`. Per the hard "no CDN assets" rule, `_tokens.css` now declares local `@font-face` rules for both families instead. **Action needed:** download the static WOFF2 files and place them at:
  - `assets/fonts/archivo/archivo-500.woff2`, `-600.woff2`, `-700.woff2`, `-800.woff2`
  - `assets/fonts/public-sans/public-sans-400.woff2`, `-500.woff2`, `-600.woff2`, `-700.woff2`

  Until those files exist, text renders in the `Helvetica, Arial, sans-serif` fallback already listed in `--lgl-font-heading` / `--lgl-font-body`.
- `lgl_asset_version()` falls back to `wp_get_theme()->get( 'Version' )` when a referenced asset doesn't exist yet on disk — several handles registered here (`lgl-app`/theme root `style.css`, `lgl-components`, `assets/css/pages/{home,product,checkout,shop}.css`, `assets/js/navigation.js`) point at files not yet created; they are placeholders for upcoming tasks, not 404s to fix now.
- `lgl-components` is registered as a single aggregate `assets/css/components.css`, not the `components/` subfolder shown in CLAUDE.md §3. There's no build step defined anywhere in the project to compile multiple partials into one file, so a single flat file is the only way to give it one enqueued handle. If `components/` should hold multiple files instead, they'll need a manual `@import` or a build step — flagging for a decision.
- `wp_localize_script` i18n strings for `lgl-navigation` (`menuOpenLabel`, `menuCloseLabel`) are placeholder copy — exact strings weren't specified. Object name chosen: `lglNavigation`.
- Nonce action `lgl_nonce` and `admin_url( 'admin-ajax.php' )` are localized onto `lgl-navigation` per the task, even though no navigation-triggered AJAX call exists yet (the delivery estimator AJAX endpoint described in CLAUDE.md §3 lives in `inc/ajax.php`, not here) — wiring is in place for whichever script ends up needing it.
- **`functions.php` does not exist yet.** None of `inc/setup.php`, `inc/enqueue.php`, `inc/customizer.php`, or `inc/woocommerce.php` are actually being `require`d/loaded by WordPress yet — they exist on disk per CLAUDE.md §3's file list but nothing wires them in. This has been true since the first task; flagging it now because there are enough `inc/*` files that it's worth creating `functions.php` soon (it's pure boilerplate per CLAUDE.md §3, no design decision needed).

## header.php, template-parts/header/*, inc/customizer.php, inc/woocommerce.php, assets/js/navigation.js

- Created `inc/customizer.php` ahead of a dedicated customizer task, scoped to exactly one control (`lgl_show_announcement_bar`, a checkbox in the existing "Site Identity" section) so the announcement bar's "Customizer-toggled" requirement has something real to toggle. `lgl_sanitize_checkbox()` lives in this file for now — CLAUDE.md §3 says sanitizers belong in `inc/helpers.php`; move it there once that file exists.
- Announcement-bar content is placeholder: the hotline number is hardcoded from the design reference, and "Sell on Logelite" / "Order Tracking" use `href="#"` (matching the reference's own placeholder anchors). Both need real destinations before launch.
- Account link falls back to `home_url( '/' )` when WooCommerce isn't active, since there's no non-Woo account page defined anywhere in the project.
- The search-toggle panel calls core `get_search_form()`, which renders WordPress's default search form markup since `searchform.php` (listed in CLAUDE.md §3) doesn't exist yet. Swap in a custom override when that file is built — the `id="lgl-header-search"` wrapper and toggle wiring won't need to change.
- Mobile nav and search-panel JS wiring is intentionally minimal ("toggle wiring only" per the task): `aria-expanded`/`hidden` toggling plus Escape-to-close with focus return. It does **not** implement a full focus trap inside the open mobile nav, which CLAUDE.md §9 calls for ("focus trap in off-canvas") — flagging as a follow-up once the mobile nav's final design is built out.
- `assets/css/components.css` is created for the first time in this task (previously just a registered-but-empty handle) and only covers the header. Other components will accumulate in this same file going forward, per the single-file `lgl-components` decision already logged above.
- The WooCommerce cart AJAX fragment key is `.lgl-header__cart-count` — a single `<span>` with no sibling nodes, so `jQuery(key).replaceWith(fragment)` swaps exactly one element safely. The cart-count context for screen readers is carried as that same span's `aria-label` (via `_n()`) rather than a separate visually-hidden node, to avoid a multi-node fragment that would need a different (and messier) replace target.
- Inline SVGs were added for the search and cart icons too, not just the mandated hamburger — kept for visual/markup consistency with the "no CDN, self-hosted" rule; none of the three depend on an icon font.

## inc/class-lgl-mega-walker.php, functions.php, nav.css/navigation.js updates

- **`functions.php` now exists**, superseding the earlier note that it didn't. Its `foreach` array intentionally lists only the `inc/*` files that exist on disk today: `class-lgl-mega-walker`, `setup`, `enqueue`, `customizer`, `woocommerce`. CLAUDE.md §3's full array also includes `helpers`, `template-tags`, `meta-boxes`, `checkout-fields`, `ajax` — those files don't exist yet, and `require_once`-ing a missing file would fatal-error the whole site. **Append each name to the array as its file is created**; don't paste the full §3 array until all nine files exist.
- **`assets/css/components/` vs. `assets/css/components.css` naming clash.** The previous task consolidated the `lgl-components` handle into a single flat `assets/css/components.css` (reasoning: no build step exists to compile multiple partials into one enqueued file). This task's literal file path (`assets/css/components/nav.css`) reintroduces the folder structure from CLAUDE.md §3. Both now coexist: `lgl-components` (flat file, header shell styles) and a new `lgl-nav` handle (`assets/css/components/nav.css`, mega-menu styles), the latter depending on the former. This is structurally inconsistent — worth a decision on whether to migrate everything into `assets/css/components/*.css` (one handle per file) or keep consolidating into the flat file, rather than growing both in parallel.
- Depth-1 mega-panel column headings render as `<a class="lgl-mega__heading">`, not a non-interactive label — every nav menu item has a URL by definition (admin-assigned in the menu editor), so treating the heading as a link preserves that URL/target/rel instead of discarding it. If a heading is meant to be purely decorative text, its URL should just be left empty by whoever builds the menu (an empty `href` is dropped by `lgl_link_attributes()`).
- Depth-2 `<li>` wrappers use only the item's core classes (no extra BEM class) — the visual identity lives entirely on the `<a class="lgl-mega__link">` per the literal spec ("Depth 2 items become plain links").
- `LGL_Mega_Walker` closes any other open panel before opening a new one (keyboard Enter/Space and pointer click on a no-URL trigger). Not explicitly requested, but necessary: `.is-open` is an independent state from `:hover`/`:focus-within`, so without this, a panel opened via keyboard could stay stuck open after focus moves to a different top-level item, showing two panels at once.
- The mobile off-canvas nav (`#lgl-mobile-nav` in `template-parts/header/site-header.php`) is untouched — it still renders with the default `Walker_Nav_Menu` (no mega layout), since only the primary/desktop nav was in scope for `LGL_Mega_Walker`.
- `_lgl_menu_image` is only *read* by the walker (`get_post_meta( $item->ID, '_lgl_menu_image', true )`). There's no admin UI yet to let someone assign an image to a menu item from the Appearance → Menus screen — that meta box belongs in `inc/meta-boxes.php`, which doesn't exist yet. Until it's built, this attachment ID would need to be set manually (e.g. via `update_post_meta()`).

## template-parts/header/nav-mobile.php, inc/class-lgl-mobile-nav-walker.php, assets/js/nav-mobile.js

- **Footer social links omitted.** `design-reference/Logelite Theme.dc.html`'s footer has four unlabeled `44×26` boxes near the copyright line, with no icons, text, or `href`s — they read as placeholder payment-method badges (Visa/Mastercard-style), not social profile links, so the mobile panel's "footer social if the design has them" section was left out entirely rather than inventing Facebook/Instagram/X links that don't exist in the reference. Add a `.lgl-mobile-nav__social` block if real profile URLs turn up later.
- Chose a **second dedicated walker** (`LGL_Mobile_Nav_Walker`) over filtering `walker_nav_menu_start_el`, and documented why directly in the class docblock: the accordion button's `aria-controls` needs a stable `id` on the sub-menu `<ul>`, which no core filter can add (only `nav_menu_submenu_css_class`, classes only); and a global filter would need manual `add_filter`/`remove_filter` scoping around one `wp_nav_menu()` call to avoid leaking into the desktop mega-menu, which a self-contained walker avoids entirely.
- `LGL_Mobile_Nav_Walker`'s sub-menu `id` hand-off (`start_el` pushes, `start_lvl` pops) relies on `wp_nav_menu()` being called **without** a `'depth'` limit for this walker — that's why `template-parts/header/nav-mobile.php` omits `'depth'` entirely. If a depth limit is added later, an item could have `has_children = true` but never get a matching `start_lvl()` call, leaving a stale id to be wrongly consumed by the next parent's sub-menu.
- DOM structure: `#lgl-mobile-nav` wraps `.lgl-mobile-nav__panel` (which carries `role="dialog"`/`aria-modal`/`aria-label`); `.lgl-mobile-nav__backdrop` is a sibling of `#lgl-mobile-nav`, not nested inside it — read literally from the task's "Sibling `<div class="lgl-mobile-nav__backdrop">`" wording. Both are still rendered from inside `<header>` (same location the old inline mobile-nav block occupied) rather than moved to `header.php`, to keep this change scoped to the header template parts.
- Account/cart URL resolution (`wc_get_page_permalink( 'myaccount' )` / `wc_get_cart_url()` with `home_url( '/' )` fallbacks) is now duplicated in both `site-header.php` and `nav-mobile.php`, since `get_template_part()` calls don't share PHP variable scope. Extract to an `inc/template-tags.php` helper (e.g. `lgl_get_account_url()`) once that file exists, to avoid the two copies drifting apart.
- The close-transition timeout fallback in `nav-mobile.js` is `400ms` — comfortably above the `--lgl-dur-base` token (`250ms`) used for the panel's transition, so the fallback only fires if `transitionend` genuinely never arrives (e.g. `display` changed elsewhere, or the tab was backgrounded), not as a normal race with the transition itself.

## searchform.php, search-overlay.php, a11y.js refactor, WooCommerce search restriction

- **Superseded the header's earlier simple search panel.** An earlier task built a lightweight `#lgl-header-search` collapsible panel (bare `get_search_form()`, generic `bindToggle()` in `navigation.js`). This task's full-screen dialog replaces it: `site-header.php`'s search-toggle button now points `aria-controls` at `lgl-search-overlay`, the old inline panel markup and its dead CSS (`.lgl-header__search-panel`) and dead JS (`bindToggle()`, now fully unused after the mobile-nav toggle was also taken over) were removed rather than left dangling.
- `LGL_A11y`'s shared module only extracts the **Tab-cycling trap**, not the full open/close orchestration — the task said "extract the shared trap logic," and the two dialogs' open/close sequencing genuinely differs enough (slide + backdrop + body-scroll-lock for the nav; fade + full-bleed + autofocus-the-input for search) that forcing one shared open/close function would fight both consumers instead of helping.
- `search-overlay.js` didn't exist as a named file in the task — it was added because the overlay needs its own open/close orchestration somewhere, and the established pattern (`nav-mobile.js` for the mobile nav) made a dedicated file the consistent choice over folding it into the general-purpose `navigation.js`.
- The search overlay has no separate backdrop element (unlike the mobile nav) — the whole `#lgl-search-overlay` div IS the dimmed full-screen background, with `.lgl-search-overlay__panel` as a centered child. "Click outside to close" is implemented as `event.target === overlay` (a literal click on the overlay's own background), not a `[data-close]` on the outer element, since the outer element also contains the close button and using `closest('[data-close]')` there would incorrectly match every click inside the panel too.
- Popular searches: a single Customizer text field (`lgl_popular_searches`, comma-separated), parsed with `explode( ',', ... )` + `trim`. No repeater/multi-field UI exists in core Customizer, and building a custom one felt disproportionate to "optional popular-searches links."
- `lgl_sanitize_checkbox()` (added for the announcement-bar setting, task before this one) has the same latent bug `wp_validate_boolean()` is meant to avoid — a naive `(bool)` cast would treat the string `"false"` as true. Left untouched since it's out of this task's scope, but worth consolidating both checkbox settings onto `wp_validate_boolean` in a future cleanup.

## footer.php, template-parts/footer/site-footer.php, template-parts/components/*, inc/template-tags.php

- **Newsletter form is markup only** (`.lgl-footer__newsletter-form`) — no mailing-list service is wired up (no Mailchimp/Klaviyo/custom AJAX endpoint). The `<form>` has no `action`/`method` and will do nothing on submit until a real integration is chosen and added to `inc/ajax.php` (or similar).
- **Payment icons are real, not decorative.** An earlier task guessed the design reference's four blank footer boxes were payment badges (as opposed to social icons) but left them unimplemented. This task renders actual `WC_Payment_Gateway::get_icon()` output for each currently *available* gateway (`WC()->payment_gateways()->get_available_payment_gateways()`) — nothing renders if WooCommerce is inactive or no enabled gateway defines an icon, rather than showing generic placeholder boxes.
- **Social links are a fresh addition**, not derived from the design reference (which has no real social icons/URLs anywhere) — four Customizer URL fields (Facebook/Instagram/X/YouTube) in a new "Footer" section, each rendered only when non-empty. Icons are simplified/abstract inline SVGs, not exact platform logo reproductions.
- `card-product.php`'s "New" badge is a convention, not a WooCommerce or design-reference concept: a product counts as new if `get_date_created()` is within the last 14 days. Badge priority when several conditions are true: out-of-stock > sale > new (only one badge renders).
- `card-product.php` temporarily swaps the global `$product` around `woocommerce_template_loop_add_to_cart()` (saving/restoring the original) so the correct add-to-cart button renders even when the card is used standalone, outside the real Woo loop, since some third-party hooks on that action still read the global instead of their own `$product` argument.
- The design reference's distinct sale-price red (`#e2574c`, used for "DEALS OF THE DAY" pricing) was **never given its own token** in the earlier design-tokens task — only `#c9453a` was tokenized, as `--lgl-color-error` (from the "FREE GIFT" tag). `card.css`'s sale-price (`<ins>`) styling reuses `--lgl-color-error` rather than inventing a new hardcoded hex, since this task's CSS must be tokens-only. Worth deciding later whether sale-price red deserves its own token distinct from the error/validation red.
- `btn.php`'s `attrs` whitelist is intentionally narrow: exact matches for `target`/`rel`/`title`/`download`, plus any `data-*`/`aria-*` prefix. No wildcard, and nothing that could carry executable behavior (e.g. `on*` handlers) is allowed through.
- `btn.php` only ships three icon slugs (`arrow-right`, `cart`, `search`) — an unknown slug silently renders no icon rather than erroring. Extend `lgl_get_button_icon_svg()`'s array as more are needed.
- `breadcrumbs.php` builds its JSON-LD from `wc_get_breadcrumb()` (or a small custom trail) independently of which HTML path renders — so the structured data stays correct even when `woocommerce_breadcrumb()` handles the visible markup.
- **Mixed CSS-loading strategy, by design of this task.** `card.css`/`hero.css`/`button.css`/`breadcrumbs.css`/`footer.css` are pulled into `components.css` via `@import` (as this task asked); `nav.css`/`nav-mobile.css`/`search-form.css`/`search-overlay.css` (earlier tasks) are each their own `wp_enqueue_style()` handle. Both strategies now coexist in the same project — flagged here and in `components.css`'s own docblock in case it should be unified one way or the other later.
- All four `template-parts/components/*` files rely on the WP 5.5+ three-argument `get_template_part( $slug, $name, $args )` form to receive `$args` — the `inc/template-tags.php` wrappers (`lgl_product_card()` etc.) are the only call sites that need to know this.
- Footer nav (`wp_nav_menu( array( 'theme_location' => 'footer', 'depth' => 1, ... ) )`) is wrapped in `has_nav_menu( 'footer' )` so the `<nav>` landmark itself is omitted entirely when no menu is assigned, rather than rendering an empty landmark (the existing `lgl_nav_menu_args` filter already forces `fallback_cb = false`, but that only stops the *contents*, not the wrapper).
- Copyright year uses `wp_date( 'Y' )` (timezone/locale-aware) rather than raw `date()`/`gmdate()`.

### Design tokens

| Token | Value | design-reference selector |
|---|---|---|
| `--lgl-color-brand` | `#0ea292` | logo mark bg; primary buttons/borders; hero banner bg; deals-strip header bg |
| `--lgl-color-brand-strong` | `#0b7f73` | base `<style> a{color:#0b7f73}`; "In stock only" chip text; brand name in spec rows |
| `--lgl-color-brand-subtle` | `#e6f5f3` | category-icon swatch bg; "In stock only" filter chip bg |
| `--lgl-color-accent` | `#22b24c` | base `<style> a:hover{color:#22b24c}`; SHOP THE SALE / ADD TO CART / SUBSCRIBE / PROCEED TO CHECKOUT / PLACE ORDER buttons; cart badge bg |
| `--lgl-neutral-0` | `#ffffff` | all card/header/panel `background:#fff` |
| `--lgl-neutral-50` | `#f7f9fa` | cart-totals panel bg; checkout order-summary bg; quick-order card bg (consolidates `#f6faf8` promo bg) |
| `--lgl-neutral-100` | `#f4f6f7` | `body{background:#f4f6f7}`; outer page wrapper bg |
| `--lgl-neutral-150` | `#f0f3f4` | utility-bar chip bg; header icon-circle bg (consolidates `#f2f5f6` image-placeholder bg) |
| `--lgl-neutral-200` | `#eef2f3` | table-row header bg; progress-bar track bg (consolidates `#e8edee`, `#e3e9ea` placeholder-pattern greys) |
| `--lgl-neutral-250` | `#e7eaec` | most-common `border:1px solid #e7eaec` on cards, header, dividers |
| `--lgl-neutral-300` | `#d6dcde` | unselected shipping/payment radio-dot border (consolidates `#dde3e5` step-divider line) |
| `--lgl-neutral-400` | `#9aa5a9` | dropdown-caret color; faint meta text; footer body copy/links |
| `--lgl-neutral-500` | `#7c8a8d` | "TECH STORE" subtitle; breadcrumb text (consolidates `#8d999c` placeholder-image caption text) |
| `--lgl-neutral-600` | `#5f6b70` | utility-bar text; "Sold 26 / 75" meta; form label text |
| `--lgl-neutral-700` | `#3d474b` | description/spec body copy; review text |
| `--lgl-neutral-800` | `#232a2f` | footer top border (consolidates `#1c2126`, `#1f262b` decorative dark stripe tones) |
| `--lgl-neutral-900` | `#14181c` | `body{color:#14181c}`; all primary headings |
| `--lgl-color-success` | `#1b8f42` | "In stock — ships from Indore" text; "Payment confirmed" text; Delivered order-status text |
| `--lgl-color-success-bg` | `#e8f7ee` | FREE SHIPPING tag bg; Delivered order-status chip bg |
| `--lgl-color-error` | `#c9453a` | FREE GIFT tag text |
| `--lgl-color-error-bg` | `#fdeceb` | FREE GIFT tag bg |
| `--lgl-color-warning` | `#f7b731` | BUY IT NOW button bg |
| `--lgl-color-warning-text` | `#b06b00` | "Processing" order-status chip text |
| `--lgl-color-warning-bg` | `#fff4e5` | "Processing" order-status chip bg |
| `--lgl-color-info` | `#1a73e8` | inferred — `accent` prop option list only, not rendered (see note above) |
| `--lgl-surface-inverse` | `#14181c` | footer bg; pre-order banner bg; "QUICK ORDER 24/7" badge bg |
| `--lgl-surface-inverse-elevated` | `#1f262b` | footer newsletter input bg |
| `--lgl-border-inverse` | `#232a2f` | footer bottom-bar top border |
| `--lgl-font-heading` | `'Archivo', Helvetica, Arial, sans-serif` | `<link>` Google Fonts import; all `font-family:'Archivo'` headings/buttons/prices |
| `--lgl-font-body` | `'Public Sans', Helvetica, Arial, sans-serif` | base `<style> body{font-family:'Public Sans',...}` |
| `--lgl-text-5xl` | `44px` (clamped) | hero heading "Don't miss amazing tech deals" |
| `--lgl-text-4xl` | `34–42px` (clamped) | "Thank you for your order!"; review-score "4.6" |
| `--lgl-text-3xl` | `28–30px` (clamped) | "A healthy leap ahead" banner; "TOTAL PRICE" amount |
| `--lgl-text-2xl` | `24–26px` (clamped) | product title on PDP; "All Products" page heading |
| `--lgl-text-xl` | `20–22px` (clamped) | logo wordmark (21px); newsletter heading (22px) |
| `--lgl-text-lg` | `17–19px` (clamped) | sale price emphasis (17px); "BEST SELLERS" section title (19px) |
| `--lgl-text-md` | `16–18px` (clamped) | "Cart totals" / "Your order" panel headings |
| `--lgl-text-base` | `14–16px` (clamped) | checkout input text; body default |
| `--lgl-text-sm` | `13–14px` (clamped) | card meta text, product-name links |
| `--lgl-text-xs` | `11–12px` (clamped) | eyebrow labels, badges, "Showing 1–10 of 913" |
| `--lgl-weight-extrabold` | `800` | Google Fonts `Archivo:wght@800` import; hero/section headings |
| `--lgl-tracking-widest` | `0.2em` | "LOGELITE TECH DAYS" / "TECH STORE" eyebrow text |
| `--lgl-radius-lg` | `10px` | most-common card/panel `border-radius:10px` |
| `--lgl-radius-md` | `6px` | button/input `border-radius:6px` |
| `--lgl-radius-sm` | `4px` | small badge/tag `border-radius:4px` |
| `--lgl-radius-full` | `999px` | pill chips ("In stock only", brand filter pills) |
| `--lgl-shadow-card-hover` | `0 8px 24px rgba(20,24,28,.07)` | product-card `style-hover` box-shadow |
| `--lgl-container` | `1320px` | every page-section `max-width:1320px` wrapper |
| `--lgl-container-narrow` | `900px` | product-description tab content `max-width:900px` |
| `--lgl-header-h` | `78px` | inferred — 42px logo + 18px top/bottom padding on main nav row |
