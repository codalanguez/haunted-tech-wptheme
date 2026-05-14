# Haunted Tech

A cyber-deco WordPress theme built for [codalanguez.com](https://codalanguez.com) — dark romance, web novels, art commissions, and morally ambiguous protagonists.

High-contrast black + vivid gold + oxblood red with art-deco frames, animated CRT scanlines, live glitch effects on every heading, an auto-rotating hero update slider, a 3D-spine bookshelf, a working CRT-monitor terminal listing your web novels, a tabbed masonry gallery with lightbox, an about modal, and a sticky-header subscribe CTA.

---

## Requirements

| Dependency | Version | Notes |
|---|---|---|
| WordPress     | 6.4+ | tested up to 6.6 |
| PHP           | 7.4+ | 8.x recommended |
| **ACF** (Advanced Custom Fields) | 6.x | required — manages the Book / Web Novel / Chapter post types and their fields |
| **Sekura REST Bridge for ACF** (or **ACF to REST API**) | any | required for writing ACF fields via REST (used by the bundled Claude MCP workflow) |

Install ACF + the REST bridge before activating this theme. The theme registers its own `Hero Update` CPT — no separate plugin needed for that.

---

## Quick install

1. Zip the `haunted-tech` folder (or `git clone` it into `wp-content/themes/`).
2. **WP Admin → Appearance → Themes → Add New → Upload Theme** → choose the zip.
3. Activate.
4. **Appearance → Customize → Site Identity** → upload your own logo (PNG/SVG, square). The bundled `assets/logo.png` is the Coda Languez medallion; the customizer override takes precedence.
5. **WP Admin → Menus** → create three menus and assign locations:
   - **Primary** — Books / Web Novels / Services / Gallery / About
   - **Social** — Custom Links to Patreon, Ream, Substack, Discord, etc. (icons are picked automatically from the URL host)
   - **Footer** — secondary links
6. **WP Admin → Hero Updates → Add New** — publish 1–3 hero updates. The three most recent appear in the homepage carousel.
7. Create a page with slug `about` to populate the About modal (post content becomes the bio; featured image becomes the portrait).

---

## Architecture

This is a **Full Site Editing (FSE) block theme**. Every section of the site is exposed as a server-side-rendered **dynamic block** (a PHP render callback that returns HTML), and templates are HTML block markup in `/templates/` that the Site Editor can open and rearrange.

```
haunted-tech/
├── style.css                       WP theme-metadata header (minimal)
├── theme.json                      design tokens + custom templates + template parts
├── functions.php                   theme supports, enqueues, CPT, ACF, helpers, includes
├── searchform.php                  classic search form (still used by `get_search_form()`)
│
├── inc/                            PHP includes
│   ├── render-callbacks.php          render functions for every block
│   ├── blocks.php                    register_block_type() for each dynamic block
│   ├── patterns.php                  register_block_pattern() compositions
│   └── gallery-static.php            placeholder gallery markup (TODO: gallery_item CPT)
│
├── parts/                          FSE template parts (HTML block markup)
│   ├── header.html                   overlays + social-bar + site-header
│   └── footer.html                   lightbox + about-modal + site-footer
│
├── templates/                      FSE block templates
│   ├── front-page.html               homepage — composes all section blocks
│   ├── index.html                    blog fallback
│   ├── page.html                     default page
│   ├── single.html                   default post
│   ├── single-book.html              wraps the haunted-tech/single-book block
│   ├── single-webnovel.html          wraps the haunted-tech/single-webnovel block
│   ├── single-chapter.html           wraps the haunted-tech/single-chapter block
│   ├── archive.html                  generic archive
│   ├── archive-book.html             bookshelf-style listing
│   ├── archive-webnovel.html         CRT-monitor-style listing
│   ├── search.html
│   └── 404.html
│
└── assets/
    ├── main.css                    all the design CSS (~66 KB) — also loaded as editor-style
    ├── main.js                     hero slider + gallery + lightbox + about modal
    ├── logo.png                    default site logo (512×512)
    └── coda-portrait.png           (drop in your own — used by About modal fallback)
```

### Block catalogue

All blocks live under the **Haunted Tech** category in the block inserter:

| Block | Purpose | Dynamic source |
|---|---|---|
| `haunted-tech/social-bar`     | Icon row above the header | Social menu (fallback to defaults) |
| `haunted-tech/site-header`    | Logo + nav + Subscribe CTA | Primary menu |
| `haunted-tech/site-footer`    | Footer logo + links + copyright | Footer menu |
| `haunted-tech/overlays`       | CRT scanline band + static burst | static |
| `haunted-tech/hero-slider`    | 3-slide rotating hero | `hero_update` CPT |
| `haunted-tech/bookshelf`      | Spine grid of published books | `book` CPT |
| `haunted-tech/crt-monitor`    | Terminal-style web novel list | `webnovel` CPT (+ chapter counts) |
| `haunted-tech/services`       | Three service cards | static |
| `haunted-tech/gallery`        | Tabbed masonry with lightbox | static (TODO: `gallery_item` CPT) |
| `haunted-tech/newsletter`     | Subscribe callout | placeholder form |
| `haunted-tech/lightbox`       | Singleton gallery enlarger | populated by JS |
| `haunted-tech/about-modal`    | Singleton bio modal | "about" page (post_content + featured image) |
| `haunted-tech/single-book`    | Bespoke single-book layout | current queried post |
| `haunted-tech/single-webnovel`| Bespoke single-webnovel layout | current queried post |
| `haunted-tech/single-chapter` | Bespoke single-chapter reader layout | current queried post |
| `haunted-tech/linktree`       | Linktree-style bio-link page: avatar + bio + stacked books, web novels, social | `book` + `webnovel` CPTs + social menu |

### Block patterns

Pre-built compositions in the inserter (Patterns → Haunted Tech):

- **Full Homepage** — hero + bookshelf + CRT + services + gallery + newsletter
- **Books + Web Novels** — bookshelf + CRT
- **Services + Gallery** — services + gallery

Drop any of these into a page in two clicks.

### Editing the site

After activation, **Appearance → Editor (beta)** opens the Site Editor. From there you can:

- Rearrange any section by drag-dropping our blocks
- Edit the `header` and `footer` template parts (e.g. swap the Subscribe CTA's destination)
- Override per-post templates (e.g. give one specific book a custom-themed page)
- Tweak design tokens (colors, fonts) via the Styles panel — they sync from `theme.json`

---

## What lives where

### Hero slider — `hero_update` CPT

Each homepage hero slide is one post of type `hero_update`. Fields (ACF):

| Field | Use |
|---|---|
| `update_type` | `book` (gold accent) · `chapter` (red) · `mandate` (cyan) |
| `eyebrow`     | Small label above title — "New Release · Hardcover" |
| `title_first` | Plain first half of the title |
| `title_accent`| Second half, rendered in gold with extra glow |
| `blurb`       | Body copy |
| `cta_label`   | Button text |
| `cta_link`    | Button URL |

The three most recent updates appear in the slider, sorted DESC by publish date. Auto-rotation is 5 s per slide, pauses on hover.

### Bookshelf — `book` CPT (ACF-registered)

Each book renders as a vertical spine on the shelf. Hover lifts the spine and glitches the title. **Click opens a modal overlay** (the whole single-book layout slides in over the homepage) — the standalone `/books/<slug>/` page still works for direct links + SEO and contains identical content.

ACF fields consumed:

| Field | Use |
|---|---|
| `subtitle`, `series`, `series_number` | hero meta + cover engraving |
| `blurb`, `genre`, `isbn`, `asin`, `page_count`, `publish_date`, `cover`, `kindle_unlimited` | hero details |
| `buy_amazon`, `buy_bn`, `buy_kobo`, `buy_apple` | retailer buttons |
| `goodreads_url`, `bookbub_url`, `storygraph_url` *(v0.8)* | "Find online" discovery row |
| `content_warnings_graphic`, `content_warnings` *(v0.8)* | collapsible warning panel (comma-separated lists; graphic items render in brighter red) |
| `excerpt_eyebrow`, `excerpt_html` *(v0.8)* | "Chapter One" preview with drop-cap |
| `download_url` *(v0.9)* | When set, the buy row renders a primary "Download Free" CTA (gold-filled, `buy-btn-download` class) before the paid retailer buttons. Use a Pretty Link for click tracking. |

If `buy_amazon` is empty but `asin` is set, the theme builds the Amazon URL from the ASIN. **Every field is conditional** — empty values collapse out of the layout entirely (no empty labels, no broken buttons).

### Book Modal *(v0.8)*

A site-wide singleton (`haunted-tech/book-modal` block, lives in the footer template part). Click any book spine, "Also by" card, or "More in series" sibling and the modal fetches that book's HTML via `/wp-json/haunted-tech/v1/book-modal/<slug>` and injects it. URL updates to `#book-<slug>` (shareable + back/forward works). Esc, ×, or backdrop click closes.

Same content shape as the standalone `/books/<slug>/` page — composed of:

1. Book hero (`haunted-tech/single-book`)
2. Excerpt (`haunted-tech/book-excerpt`)
3. More in this Series (`haunted-tech/book-more-in-series`)
4. Also by *Author* (`haunted-tech/also-by`)

Sections 2–4 each return `''` if their data is empty, so a brand-new book with just a blurb still renders cleanly.

### Back to Top *(v0.8)*

A floating gold-bordered arrow in the bottom-right corner, also a footer singleton. Fades in after the user scrolls past 600 px, smooth-scrolls to `#top` on click.

### CRT Monitor — `webnovel` CPT (ACF-registered)

Lists your serialized novels in a terminal-style monitor. Status indicator (●/◯/✓) reflects the `status` ACF field. Chapter count is computed by counting `chapter` posts whose `webnovel` relationship field points to this novel.

### Chapter reading — `chapter` CPT (ACF-registered)

`single-chapter.php` renders the chapter content with optional author's note, content warnings, paywall metadata, and prev/next navigation. Prev/next default to the previous/next chapter by `chapter_number` within the same web novel, unless manually overridden via the `prev_chapter` / `next_chapter` ACF fields.

If the chapter has an `external_read_url` set, an "off-site" callout appears above the body — used when the actual premium content lives on Patreon / Ream / Substack and WordPress is just the catalog page.

### Gallery — `gallery_item` CPT

Each tile is a post of type `gallery_item`. Fields (ACF, registered in PHP — no import needed):

| Field | Use |
|---|---|
| `service_tab`   | Which tab the tile lands on (`art` / `covers` / `ai`) |
| `category`      | Lower-case slug for the Art Commissions filter chips (`portrait`, `bust`, `couple`, `scene`, `ritual`, …). Leave blank for non-art tabs. |
| `tag`           | Small badge label shown on the card and in the lightbox (e.g. "Portrait", "Bone Frequencies · I", "Chapter Banner") |
| `description`   | Long caption for the lightbox; first ~18 words also show on the card |
| `image`         | The artwork. Falls back to the post's featured image, then to a gradient placeholder if neither is set. |
| `aspect_ratio`  | Tile shape: 1:1 / 3:4 / 4:5 / 2:3 / 16:10 / 16:9 |

Order tiles via the standard *Page Attributes → Order* field; ties break by date DESC.

Filter chips on the **Art Commissions** tab are derived automatically from the unique `category` values across that tab's items. Pagination caps at 9 tiles per page; client-side pagination via the existing JS handles the rest.

If you have **zero** `gallery_item` posts, the section gracefully falls back to the static placeholder shipped with the theme — so the homepage never looks broken.

### About modal — `about` page

If you create a page with the slug `about`, its post content becomes the bio displayed in the About modal. The page's featured image becomes the portrait. The "About" nav link opens the modal via JS instead of navigating to `/about` (the URL hash still works for direct linking, including `…?#about`).

### Linktree page — `haunted-tech/linktree` block *(v0.9.1)*

A single-block dynamic page for social-bio-link use (Instagram bio, TikTok bio, email signature, business cards). Drop the **Linktree Page** block on any WP page and it renders, in order:

- **Avatar** — site custom-logo (or `assets/logo.png` fallback), 120 px circle with gold ring + glow
- **Site name** — Forum-serif, gold, with the existing glitch tear animation
- **Bio** — pulled from Settings → General → Tagline (`blogdescription`)
- **Books** — every published `book` post as a stacked tile (cover, series eyebrow, title, 14-word tagline). Books with `download_url` set show a gold **"Free Download"** eyebrow instead of the series name.
- **Web Novels** — every published `webnovel` post (cover, status eyebrow, title, tagline)
- **Follow** — `social` nav menu rendered through `Haunted_Tech_Social_Walker`, so all auto-mapped brand icons (YouTube, Facebook, BookBub, Civitai, Redbubble, etc.) light up here too

All sections are conditional — empty CPTs or an unassigned social menu collapse out of the layout. Mobile-friendly 640 px column max-width; cards reflow at ≤480 px.

Suggested page slug: `/links`. Set the page as a draft, drop a single `haunted-tech/linktree` block in the body, publish, then use `https://yoursite.com/links` as your social-bio destination.

### Newsletter

Configured via **Appearance → Customize → Haunted Tech → Newsletter**. Pick a provider:

| Provider | What you provide | What renders |
|---|---|---|
| Placeholder | nothing | Non-functional demo form + admin-only "Connect your provider" link |
| **Substack** | your Substack URL (e.g. `https://yourname.substack.com`) | Substack's official iframe widget — handles double-opt-in, confirmation, etc. |
| Custom embed | raw HTML/script from any other provider | Inserted as-is inside the callout (Mailchimp, ConvertKit, Beehiiv, ...) |

The Substack URL field tolerates trailing slashes and accidental `/embed` suffixes — the theme normalizes the URL before building the iframe.

---

## Custom logo

The Customizer's "Site Identity → Logo" setting is wired through `haunted_tech_logo_url()`. If set, it replaces:

- Header logo (96 px desktop, 64 px mobile)
- Footer logo (80 px centered)
- Browser favicon
- Hero watermark (720 px, slow rotation, opacity pulse)
- Lightbox brand mark (top center)

---

## Anchor / deep linking

Every meaningful spot on the homepage has a stable `id`. Use any of them as a Custom Link URL in **Appearance → Menus**, in any Button block, or as a CTA.

| Anchor | Where it lands |
|---|---|
| `#top` / `#hero` | Top of the hero slider (use `#top` for a "back to top" button) |
| `#books` | Bookshelf section |
| `#book-<slug>` | A specific book's spine on the shelf (slug = the book's post slug) |
| `#web-novels` | CRT-monitor web novels section |
| `#webnovel-<slug>` | A specific web novel row in the CRT monitor |
| `#services` | Services section |
| `#service-art` / `#service-covers` / `#service-ai` | The individual service cards |
| `#gallery` | Gallery section (lands on whichever tab is currently active) |
| `#gallery-art` / `#gallery-covers` / `#gallery-ai` | Gallery section with a specific tab pre-selected (the JS reads the hash) |
| `#newsletter` | "Join the Signal" newsletter callout |
| `#about` | Opens the About modal instead of scrolling (handled by JS) |
| `#footer` | Site footer |

CSS `scroll-margin-top: 140px` is applied to all of these so they clear the sticky social bar + header on landing.

A read-only **Anchors Reference** section also lives inside *Appearance → Customize → Haunted Tech* — same list, in case you don't want to flip back to the README while wiring up menus.

---

## Theme options (Appearance → Customize → Haunted Tech)

| Section | Setting | Default | Notes |
|---|---|---|---|
| Anchors Reference | (read-only)  | n/a      | Cheat-sheet of every in-page anchor URL the theme exposes |
| Newsletter   | Provider         | placeholder | placeholder / substack / embed |
| Newsletter   | Substack URL     | empty    | Used when provider = substack; theme builds the iframe |
| Newsletter   | Custom embed     | empty    | Used when provider = embed; raw HTML/script |
| Hero Slider  | Slide duration   | 5000 ms  | Range 1500–30000; advances each slide after this many ms |
| Hero Slider  | Auto-rotate      | on       | Uncheck to require manual navigation only |

Slider settings are localized to the front-end JS as `window.HauntedTechOpts.{sliderDuration,sliderAutoplay}` — `assets/main.js` reads them on init.

## Roadmap

- [x] Replace `inc/gallery-static.php` with a `gallery_item` CPT + WP_Query loop (still kept as fallback for empty state)
- [x] Block patterns for each section so they're insertable from the block editor
- [x] FSE block theme (templates in `/templates/*.html`, parts in `/parts/*.html`)
- [x] Theme-options panel for newsletter provider config + slider rotation duration
- [x] Self-host the Google Fonts for performance + GDPR (24 woff2 files in `assets/fonts/`)
- [x] **v0.8** — Book modal SPA + back-to-top + book single overhaul
- [x] **v0.9** — Web novel modal (parity with book modal, REST-fetched, hash-routed at `#webnovel-<slug>`)
- [x] **v0.9** — Author byline on book hero (links to About modal)
- [x] **v0.9** — Self-host Font Awesome 6.5.1 Free (`assets/fontawesome/`)
- [x] **v0.9** — Onboarding admin notice with 7-step setup checklist (`inc/onboarding.php`)
- [x] **v0.9** — `download_url` book field + `.buy-btn-download` primary-CTA styling (free-download support for reader magnets)
- [x] **v0.9** — Extra social-bar brand icons (YouTube, Facebook, BookBub, Civitai, Redbubble)
- [x] **v0.9.1** — Linktree page block (avatar + bio + stacked Books / Web Novels / Follow)

## Setup checklist (admin notice)

After activation, an admin notice at the top of every WP admin screen guides you through the 8-step setup. Each row is either a green ✓ (done) or a red ◆ with a "Set up" button that deep-links to the right page:

| # | Step | Where to fix |
|---|---|---|
| 1 | Upload your site logo | Customizer → Site Identity |
| 2 | Set up the Primary menu | Appearance → Menus |
| 3 | Set up the Social menu | Appearance → Menus → Social |
| 4 | Connect a newsletter provider | Customizer → Haunted Tech → Newsletter |
| 5 | Publish your first Hero Update | Hero Updates → Add New |
| 6 | Publish your first Book | Books → Add New |
| 7 | Create an About page (slug `about`) | Pages → Add New |
| 8 | Create a Links page (slug `links`, contains the Linktree block) | Pages → Add New |

A progress bar shows N of 8 done. Once all 8 are done, the notice hides automatically. Dismissible per user — the Dismiss link sets a `user_meta` flag so each editor sees the checklist on their first visit but can hide it afterward. On theme reactivation, dismiss flags reset across all users.

The Onboarding renderer lives in `inc/onboarding.php` and only loads in admin (gated behind `is_admin()`).

---

## License

MIT — see `LICENSE`.

## Credits

Theme built collaboratively by Coda Languez and Claude (Anthropic).
Logo: Coda Languez. Portrait placeholder: Coda Languez.
Built atop ACF, Sekura REST Bridge, Font Awesome 6.

---

*"She traded her firewall for a heartbeat."*
