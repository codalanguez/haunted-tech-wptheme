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

```
haunted-tech/
├── style.css                  WP theme metadata header (minimal)
├── theme.json                 design tokens for block editor
├── functions.php              enqueues, CPT, ACF field group, menus, helpers
├── header.php                 <head>, social bar (top), header, CRT overlays
├── footer.php                 lightbox, about modal, footer
├── front-page.php             homepage — assembles every block
├── index.php                  blog fallback
├── page.php                   default page
├── single.php                 default post
├── single-book.php            Book CPT — cover hero + ACF fields + buy links
├── single-webnovel.php        Web Novel CPT — series page with chapter ToC
├── single-chapter.php         Chapter CPT — reading view + prev/next
├── archive.php                generic archive
├── archive-book.php           bookshelf-style listing of all books
├── archive-webnovel.php       CRT-monitor-style listing of all web novels
├── search.php / searchform.php
├── 404.php
├── parts/
│   └── gallery-static.php     placeholder gallery (TODO: gallery_item CPT)
└── assets/
    ├── main.css               all the design CSS (~66 KB)
    ├── main.js                hero slider + gallery + lightbox + about modal
    ├── logo.png               default site logo (512×512)
    └── coda-portrait.png      (drop in your own — used by About modal fallback)
```

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

The form in the homepage is a placeholder. Drop in your provider's embed:

- **Mailchimp** — paste their embed in `front-page.php` inside the `.newsletter-form` slot
- **ConvertKit** — same
- **Substack** — same

A future iteration could expose this as a theme-options setting.

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

## Roadmap

- [ ] Replace static `parts/gallery-static.php` with a `gallery_item` CPT + WP_Query loop
- [ ] Add **block patterns** for each section so they're insertable from the block editor on any page
- [ ] Migrate from classic PHP to FSE block theme (templates in `/templates/*.html`)
- [ ] Theme-options panel for newsletter provider config + slider rotation duration
- [ ] Optional Patreon WordPress plugin integration so `access_level` actually paywalls
- [ ] Self-host the Google Fonts for performance + GDPR

---

## License

MIT — see `LICENSE`.

## Credits

Theme built collaboratively by Coda Languez and Claude (Anthropic).
Logo: Coda Languez. Portrait placeholder: Coda Languez.
Built atop ACF, Sekura REST Bridge, Font Awesome 6.

---

*"She traded her firewall for a heartbeat."*
