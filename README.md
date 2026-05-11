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

Each book renders as a vertical spine on the shelf. Hover lifts the spine and glitches the title. Click → `single-book.php`.

ACF fields consumed: `subtitle`, `series`, `series_number`, `blurb`, `genre`, `isbn`, `asin`, `page_count`, `publish_date`, `cover`, `buy_amazon`, `buy_bn`, `buy_kobo`, `buy_apple`, `kindle_unlimited`.

If `buy_amazon` is empty but `asin` is set, the theme builds the Amazon URL from the ASIN.

### CRT Monitor — `webnovel` CPT (ACF-registered)

Lists your serialized novels in a terminal-style monitor. Status indicator (●/◯/✓) reflects the `status` ACF field. Chapter count is computed by counting `chapter` posts whose `webnovel` relationship field points to this novel.

### Chapter reading — `chapter` CPT (ACF-registered)

`single-chapter.php` renders the chapter content with optional author's note, content warnings, paywall metadata, and prev/next navigation. Prev/next default to the previous/next chapter by `chapter_number` within the same web novel, unless manually overridden via the `prev_chapter` / `next_chapter` ACF fields.

If the chapter has an `external_read_url` set, an "off-site" callout appears above the body — used when the actual premium content lives on Patreon / Ream / Substack and WordPress is just the catalog page.

### Gallery — currently static

The gallery section is a placeholder. The intended evolution is a `gallery_item` CPT with fields `service_tab` (art|covers|ai), `tag`, `title`, `description`, `image`, `ratio`. The masonry + lightbox + filter chips + pagination JS is already in place — only the data source needs swapping.

### About modal — `about` page

If you create a page with the slug `about`, its post content becomes the bio displayed in the About modal. The page's featured image becomes the portrait. The "About" nav link opens the modal via JS instead of navigating to `/about` (the URL hash still works for direct linking, including `…?#about`).

### Newsletter

Configured via **Appearance → Customize → Haunted Tech → Newsletter**. Paste the embed code from your provider (Mailchimp, ConvertKit, Substack, Beehiiv, etc.) and it replaces the placeholder form inside the "Join the Signal" callout.

If left empty, a non-functional placeholder form renders, plus a small admin-only link nudging you to *Connect your provider*.

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

Sections have stable IDs (`#books`, `#web-novels`, `#services`, `#gallery`, `#newsletter`, `#about`). Nav menu items can target them with `#` URLs. The gallery JS also reads URL hashes like `#gallery-art`, `#gallery-covers`, `#gallery-ai` to deep-link into specific tabs.

---

## Theme options (Appearance → Customize → Haunted Tech)

| Section | Setting | Default | Notes |
|---|---|---|---|
| Newsletter   | Embed code      | empty    | Raw HTML/script from your provider; replaces the placeholder form |
| Hero Slider  | Slide duration  | 5000 ms  | Range 1500–30000; advances each slide after this many ms |
| Hero Slider  | Auto-rotate     | on       | Uncheck to require manual navigation only |

Slider settings are localized to the front-end JS as `window.HauntedTechOpts.{sliderDuration,sliderAutoplay}` — `assets/main.js` reads them on init.

## Roadmap

- [ ] Replace `inc/gallery-static.php` with a `gallery_item` CPT + WP_Query loop
- [x] Block patterns for each section so they're insertable from the block editor
- [x] FSE block theme (templates in `/templates/*.html`, parts in `/parts/*.html`)
- [x] Theme-options panel for newsletter provider config + slider rotation duration
- [x] Self-host the Google Fonts for performance + GDPR (24 woff2 files in `assets/fonts/`)
- [ ] Wire `Connect your provider` admin link into a guided onboarding flow

---

## License

MIT — see `LICENSE`.

## Credits

Theme built collaboratively by Coda Languez and Claude (Anthropic).
Logo: Coda Languez. Portrait placeholder: Coda Languez.
Built atop ACF, Sekura REST Bridge, Font Awesome 6.

---

*"She traded her firewall for a heartbeat."*
