<?php
/**
 * Haunted Tech — theme bootstrap (FSE block-theme edition).
 *
 * Wires:
 *   - theme supports + menu locations
 *   - asset enqueueing (fonts, main.css, main.js, font-awesome)
 *   - hero_update CPT and its ACF field group
 *   - includes /inc/render-callbacks.php (HTML for each section)
 *   - includes /inc/blocks.php          (registers dynamic blocks)
 *   - includes /inc/patterns.php        (block-pattern compositions)
 *   - includes /inc/gallery-static.php  (placeholder gallery markup)
 *   - helper: site logo URL (custom-logo aware)
 *   - helper: hero slide query
 *
 * Templates: see /templates/*.html and /parts/*.html (the FSE primitives).
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

define('HAUNTED_TECH_VERSION', '0.17.8');
define('HAUNTED_TECH_DIR', get_template_directory());
define('HAUNTED_TECH_URI', get_template_directory_uri());

/* ---------------------------------------------------------------------------
 * 1. Theme supports + menu locations
 * ------------------------------------------------------------------------- */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'script', 'style']);
    add_theme_support('automatic-feed-links');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_theme_support('wp-block-styles');
    add_theme_support('block-templates');
    add_theme_support('block-template-parts');
    add_theme_support('custom-logo', [
        'height'      => 512,
        'width'       => 512,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    /* Hero slide background photo — cropped wide so it reads behind the
     * glitching title regardless of the source image's own aspect ratio. */
    add_image_size('hero_bg', 1600, 1000, true);

    register_nav_menus([
        'primary' => __('Primary Navigation', 'haunted-tech'),
        'social'  => __('Social Links',       'haunted-tech'),
        'footer'  => __('Footer Menu',        'haunted-tech'),
    ]);

    /* Make our main.css available to the block editor too, so blocks render
     * with the same colors/fonts/glitch styles inside the editor preview. */
    add_editor_style('assets/main.css');
});

/* ---------------------------------------------------------------------------
 * 2. Enqueue styles & scripts
 * ------------------------------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {
    // Self-hosted Google Fonts (better performance, no third-party request, GDPR-friendly).
    // Source files live in assets/fonts/. To swap weights, regenerate via a tool like
    // google-webfonts-helper or edit assets/fonts/fonts.css directly.
    wp_enqueue_style(
        'haunted-tech-fonts',
        HAUNTED_TECH_URI . '/assets/fonts/fonts.css',
        [],
        HAUNTED_TECH_VERSION
    );

    // Self-hosted Font Awesome 6.5.1 (Free) — MINIMAL SUBSET.
    // fa-used.css declares only the 19 icons the theme actually uses and points
    // at fa-*-subset.woff2 (subset to just those glyphs, ~4 KB total) instead of
    // the full all.min.css + full webfonts (~273 KB of fonts). To add an icon:
    // add its class in a template, add a ::before rule + its codepoint to the
    // BRANDS/SOLID lists in fa-used.css, then re-run the subset command in that
    // file's header. The full all.min.css + webfonts stay in the repo for that.
    wp_enqueue_style(
        'font-awesome',
        HAUNTED_TECH_URI . '/assets/fontawesome/fa-used.css',
        [],
        HAUNTED_TECH_VERSION
    );

    wp_enqueue_style(
        'haunted-tech-style',
        get_stylesheet_uri(),
        [],
        HAUNTED_TECH_VERSION
    );

    wp_enqueue_style(
        'haunted-tech-main',
        HAUNTED_TECH_URI . '/assets/main.css',
        ['haunted-tech-style'],
        HAUNTED_TECH_VERSION
    );

    // overflow-x:clip has the same visual effect as hidden (no horizontal
    // overflow shown) but does NOT create a scroll container, so the header's
    // `position:sticky;top:0` works correctly in all browsers including Safari.
    // TODO: move this into the body rule in assets/main.css directly.
    wp_add_inline_style( 'haunted-tech-main', 'body{overflow-x:clip}' );

    /* NOT ENQUEUED — see assets/mobile-perf.css.
     *
     * It was enqueued in 0.17.0 after sitting unused in the repo, and it
     * changed how the site looks on every screen size, not just phones:
     * `will-change` on the four mix-blend-mode overlays gave each its own
     * compositing layer and therefore its own stacking context, which changes
     * what `screen` blending composites against — the whole page went milky
     * and grainy. Separately, its reduced-motion block froze .crt-band's
     * animation instead of hiding the band, leaving it parked at full opacity.
     *
     * Both are fixed in the file now, but it stays out of the enqueue until
     * the fix has been confirmed on a real screen. The mobile pass's other
     * wins (nav panel, section padding, tap targets, anchor offsets) live in
     * main.css and are unaffected by this. */

    wp_enqueue_script(
        'haunted-tech-main',
        HAUNTED_TECH_URI . '/assets/main.js',
        [],
        HAUNTED_TECH_VERSION,
        true
    );
});

/* ---------------------------------------------------------------------------
 * 2b. Front-end performance tweaks
 * ------------------------------------------------------------------------- */

/* Scripting gate for the mobile nav.
 *
 * The nav hides itself behind an off-canvas panel below 700px, which is only
 * a good idea if there is JS to open it again. So the CSS that hides it is
 * gated on the ABSENCE of a `no-js` class: with scripting off, the class
 * survives and the menu stays in flow as an ordinary (long) list rather than
 * becoming a button that does nothing.
 *
 * The class is written into <html> server-side rather than added by a script,
 * because the host's optimizer strips unmarked inline <script> tags outright
 * — the first attempt at this used one and it never reached the browser. A
 * class in the markup cannot be stripped by a JS optimizer.
 *
 * Removing it then has two paths, and the redundancy is deliberate: the
 * inline head script carries the optimizer's own opt-out attributes (the ones
 * it uses on its own inline scripts) so the class goes before first paint and
 * nothing flashes; main.js removes it again on the chance that the optimizer
 * changes its mind about those attributes, at the cost of a brief flash
 * rather than a broken menu. */
add_filter('language_attributes', function ($output) {
    return trim($output) . ' class="no-js"';
});

add_action('wp_head', function () {
    echo "<script data-pagespeed-no-defer data-two-no-delay>"
       . "document.documentElement.classList.remove('no-js');"
       . "</script>\n";
}, 0);

/* Preload the hero headline font (Forum, latin subset). It renders the LCP
 * element (.hero h2) on the homepage, so starting its download in the <head>
 * — instead of after the CSS parses — shaves the largest-text render delay.
 * Only this one face is preloaded on purpose: preload ignores unicode-range,
 * so preloading more here would fetch subsets the page may never use. */
add_action('wp_head', function () {
    if (!is_front_page() && !is_home()) { return; }
    $forum_latin = HAUNTED_TECH_URI . '/assets/fonts/6aey4Ky-Vb8Ew8IROpI.woff2';
    printf(
        '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
        esc_url($forum_latin)
    );
}, 1);

/* Disable the WordPress emoji script + styles. The theme uses real Unicode
 * (and Font Awesome) for glyphs, so the ~15 KB wp-emoji-release.min.js and its
 * inline detection script are dead weight on every page load. */
add_action('init', function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    add_filter('tiny_mce_plugins', function ($plugins) {
        return is_array($plugins) ? array_diff($plugins, ['wpemoji']) : [];
    });
    add_filter('wp_resource_hints', function ($urls, $type) {
        if ('dns-prefetch' === $type) {
            $urls = array_filter($urls, function ($u) {
                return is_string($u) ? (strpos($u, 's.w.org') === false) : true;
            });
        }
        return $urls;
    }, 10, 2);
});

/* ---------------------------------------------------------------------------
 * 3. Custom post type: hero_update  (data source for the homepage hero slider)
 * ------------------------------------------------------------------------- */
add_action('init', function () {
    /* hero_update — drives the homepage hero slider */
    register_post_type('hero_update', [
        'label'        => __('Hero Updates', 'haunted-tech'),
        'labels'       => [
            'name'          => __('Hero Updates', 'haunted-tech'),
            'singular_name' => __('Hero Update',  'haunted-tech'),
            'add_new_item'  => __('Add Hero Update', 'haunted-tech'),
            'edit_item'     => __('Edit Hero Update', 'haunted-tech'),
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'rest_base'    => 'hero-updates',
        'menu_icon'    => 'dashicons-megaphone',
        'menu_position'=> 4,
        'supports'     => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'has_archive'  => false,
    ]);

    /* gallery_item — populates the homepage Gallery section's three tabs */
    register_post_type('gallery_item', [
        'label'        => __('Gallery Items', 'haunted-tech'),
        'labels'       => [
            'name'          => __('Gallery Items',     'haunted-tech'),
            'singular_name' => __('Gallery Item',      'haunted-tech'),
            'add_new_item'  => __('Add Gallery Item',  'haunted-tech'),
            'edit_item'     => __('Edit Gallery Item', 'haunted-tech'),
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'rest_base'    => 'gallery-items',
        'menu_icon'    => 'dashicons-format-gallery',
        'menu_position'=> 5,
        'supports'     => ['title', 'thumbnail', 'page-attributes', 'custom-fields'],
        'has_archive'  => false,
    ]);
});

/* Register the ACF field groups for theme-managed CPTs. */
add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) { return; }
    /* ---------- Extra Book fields (v0.8.0) ----------
     * Augments the existing Book field group (imported from book-fields.json)
     * with the modal-era fields: content warnings, discovery links, excerpt.
     * v0.9 adds download_url for reader-magnet titles.
     * These render conditionally — empty fields collapse out of the layout.
     */
    acf_add_local_field_group([
        'key'      => 'group_book_extras',
        'title'    => 'Book — Modal & Discovery',
        'fields'   => [
            ['key'=>'field_book_cw_graphic', 'label'=>'Content Warnings · Graphic',
             'name'=>'content_warnings_graphic', 'type'=>'textarea', 'rows'=>3,
             'instructions'=>'Comma-separated list. These render in the brighter red treatment (top of the list).',
             'show_in_rest'=>1],
            ['key'=>'field_book_cw_standard', 'label'=>'Content Warnings · Standard',
             'name'=>'content_warnings', 'type'=>'textarea', 'rows'=>3,
             'instructions'=>'Comma-separated list. Rendered in muted oxblood-bordered chips.',
             'show_in_rest'=>1],
            ['key'=>'field_book_goodreads', 'label'=>'Goodreads URL',
             'name'=>'goodreads_url', 'type'=>'url', 'show_in_rest'=>1],
            ['key'=>'field_book_bookbub',   'label'=>'BookBub URL',
             'name'=>'bookbub_url',   'type'=>'url', 'show_in_rest'=>1],
            ['key'=>'field_book_storygraph','label'=>'StoryGraph URL',
             'name'=>'storygraph_url','type'=>'url', 'show_in_rest'=>1],
            ['key'=>'field_book_excerpt_eyebrow', 'label'=>'Excerpt Eyebrow',
             'name'=>'excerpt_eyebrow', 'type'=>'text',
             'instructions'=>'Small label above the excerpt heading (e.g. "Chapter One · The Server Where She Buried Him")',
             'show_in_rest'=>1],
            ['key'=>'field_book_excerpt_html', 'label'=>'Excerpt',
             'name'=>'excerpt_html', 'type'=>'wysiwyg', 'tabs'=>'visual', 'toolbar'=>'basic', 'media_upload'=>0,
             'instructions'=>'A short teaser passage (typically 3-6 paragraphs). The first letter gets a drop-cap; a "Continue Reading" CTA appears below.',
             'show_in_rest'=>1],
            ['key'=>'field_book_download_url', 'label'=>'Free Download URL',
             'name'=>'download_url', 'type'=>'url',
             'instructions'=>'For reader-magnet titles (BookFunnel, StoryOrigin, etc.). When set, the book page renders a prominent "Download Free" CTA at the top of the buy-button row, before any paid retailer links. Use your Pretty Link slug for click tracking.',
             'show_in_rest'=>1],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'book']]],
        'menu_order'   => 5,
        'position'     => 'normal',
        'style'        => 'default',
        'active'       => true,
        'show_in_rest' => 1,
    ]);

    /* ---------- Gallery item ---------- */
    acf_add_local_field_group([
        'key'      => 'group_gallery_item',
        'title'    => 'Gallery Item',
        'fields'   => [
            ['key'=>'field_gi_service_tab',  'label'=>'Service Tab',  'name'=>'service_tab',  'type'=>'select',
             'choices'=>['art'=>'Art Commissions','covers'=>'Book Covers','ai'=>'AI Generation'],
             'default_value'=>'art', 'required'=>1, 'show_in_rest'=>1],
            ['key'=>'field_gi_category',     'label'=>'Filter Category','name'=>'category',   'type'=>'text',
             'instructions'=>'Lower-case slug used by the Art Commissions filter chips (portrait, bust, couple, scene, ritual, …). Leave blank for non-art tabs.', 'show_in_rest'=>1],
            ['key'=>'field_gi_tag',          'label'=>'Card Tag',      'name'=>'tag',          'type'=>'text',
             'instructions'=>'Small badge label shown on the card and in the lightbox (e.g. "Portrait", "Bone Frequencies · I", "Chapter Banner").', 'show_in_rest'=>1],
            ['key'=>'field_gi_description',  'label'=>'Description',   'name'=>'description',  'type'=>'textarea', 'rows'=>4,
             'instructions'=>'Long caption shown in the lightbox; first ~18 words also show on the card.', 'show_in_rest'=>1],
            ['key'=>'field_gi_image',        'label'=>'Image',         'name'=>'image',        'type'=>'image',
             'return_format'=>'array', 'preview_size'=>'medium',
             'instructions'=>'Optional. If empty, the post\'s featured image is used; if neither is set, the card shows a gradient placeholder.', 'show_in_rest'=>1],
            ['key'=>'field_gi_aspect_ratio', 'label'=>'Aspect Ratio',  'name'=>'aspect_ratio', 'type'=>'select',
             'choices'=>[
                '1/1'   => '1:1 (square)',
                '3/4'   => '3:4 (portrait)',
                '4/5'   => '4:5 (tall portrait)',
                '2/3'   => '2:3 (book cover)',
                '16/10' => '16:10 (landscape)',
                '16/9'  => '16:9 (wide)',
             ],
             'default_value'=>'3/4', 'show_in_rest'=>1],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'gallery_item']]],
        'menu_order'   => 0,
        'position'     => 'normal',
        'style'        => 'default',
        'active'       => true,
        'show_in_rest' => 1,
    ]);

    /* ---------- Hero update ---------- */
    acf_add_local_field_group([
        'key'      => 'group_hero_update',
        'title'    => 'Hero Update',
        'fields'   => [
            ['key'=>'field_hero_type',         'label'=>'Update Type',          'name'=>'update_type',  'type'=>'select',
             'choices'=>['book'=>'Book Release (gold)','chapter'=>'Chapter Drop (red)','mandate'=>'Mandate / Misc (cyan)'],
             'default_value'=>'mandate', 'required'=>1, 'show_in_rest'=>1],
            ['key'=>'field_hero_eyebrow',      'label'=>'Eyebrow',              'name'=>'eyebrow',      'type'=>'text', 'instructions'=>"Small label above the title", 'show_in_rest'=>1],
            ['key'=>'field_hero_title_first',  'label'=>'Title — First Part',   'name'=>'title_first',  'type'=>'text', 'instructions'=>"First half of the title (plain).",     'show_in_rest'=>1],
            ['key'=>'field_hero_title_accent', 'label'=>'Title — Accent Part',  'name'=>'title_accent', 'type'=>'text', 'instructions'=>"Second half (gold + glitch glow).",    'show_in_rest'=>1],
            ['key'=>'field_hero_blurb',        'label'=>'Blurb',                'name'=>'blurb',        'type'=>'textarea', 'rows'=>4, 'show_in_rest'=>1],
            ['key'=>'field_hero_cta_label',    'label'=>'CTA Button Label',     'name'=>'cta_label',    'type'=>'text', 'default_value'=>'Read More', 'show_in_rest'=>1],
            ['key'=>'field_hero_cta_link',     'label'=>'CTA Link',             'name'=>'cta_link',     'type'=>'url', 'show_in_rest'=>1],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'hero_update']]],
        'menu_order'   => 0,
        'position'     => 'normal',
        'style'        => 'default',
        'active'       => true,
        'show_in_rest' => 1,
    ]);
});

/* ---------------------------------------------------------------------------
 * 4. Helpers: site logo URL + hero slide fetch
 * ------------------------------------------------------------------------- */
function haunted_tech_logo_url() {
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $src = wp_get_attachment_image_src($custom_logo_id, 'full');
        if ($src) return $src[0];
    }
    return HAUNTED_TECH_URI . '/assets/logo.png';
}

function haunted_tech_get_hero_slides($limit = 6) {
    return get_posts([
        'post_type'      => 'hero_update',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    ]);
}

function haunted_tech_render_hero_title($first, $accent) {
    $combined = trim($first . ' ' . $accent);
    printf('<h1 data-text="%s">%s <span class="gold">%s</span></h1>',
        esc_attr($combined), esc_html($first), esc_html($accent));
}

/* ---------------------------------------------------------------------------
 * 5. Default-menu fallback (used when the user hasn't set up Primary)
 * ------------------------------------------------------------------------- */
function haunted_tech_default_primary_menu() {
    echo '<ul>';
    echo '<li><a href="' . esc_url(home_url('/#books'))      . '">Books</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#web-novels')) . '">Web Novels</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#services'))   . '">Services</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#gallery'))    . '">Gallery</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#about'))      . '" data-open-about>About</a></li>';
    echo '</ul>';
}

/**
 * Lantern Serials' lighthouse mark, as inline SVG.
 *
 * Font Awesome 6.5.1 Free has no lantern/lighthouse glyph (fa-tower-observation
 * is the nearest and it reads as a fire tower), and assets/fontawesome is a
 * SUBSET — pulling in a new FA class means regenerating the woff2, which is a
 * lot of ceremony for one icon. So Lantern joins Civitai and Substack on the
 * inline-SVG path instead: no subset rebuild, and it is the actual brand mark
 * rather than an approximation.
 *
 * viewBox is 2:3 (tall). At a flat 1em it reads noticeably smaller than the
 * square glyphs beside it — a narrow shape carries less optical mass at the
 * same height — so `.social-svg--tall` gives it 1.3em of height and lets the
 * width follow. Checked against the Substack mark at 36px and 44px; 1em was
 * visibly light, 1.3em matches.
 *
 * Positive mark, not the brand's knockout plate: rendered as a filled plate
 * with the lighthouse punched out, it turns into a gold blob below ~48px and
 * reads as a badge among ten line glyphs. The plate is the better lockup at
 * poster size and the wrong one here.
 *
 * Holes (windows, the gallery railing) are punched with fill-rule="evenodd",
 * so no shape may overlap another — the parts abut on shared edges by design.
 *
 * @param string $class Class for the <svg> element.
 */
function ht_lantern_mark($class = 'social-svg social-svg--tall') {
    return '<svg class="' . esc_attr($class) . '" width="1em" height="1em" fill="currentColor" '
         . 'aria-hidden="true" focusable="false" style="display:block" viewBox="0 -1.5 24.5 39">'
         /* beam */
         . '<path d="M2.4 .5 14.4 5.6 .2 9.8 1.2 1.4Q1.5 .4 2.4 .5Z"/>'
         /* finial + dome */
         . '<circle cx="17.55" cy=".95" r=".85"/>'
         . '<path d="M14.45 4Q14.45 1.8 17.55 1.8 20.65 1.8 20.65 4Z"/>'
         /* gallery lip under the dome */
         . '<rect x="13.55" y="4" width="8" height=".8" rx=".25"/>'
         /* lantern room + its window */
         . '<path fill-rule="evenodd" d="M14.45 4.8H20.65V7.6H14.45ZM15.15 5.3H19.95V7.1H15.15Z"/>'
         /* balcony + the two railing gaps */
         . '<path fill-rule="evenodd" d="M12.9 7.6H22.2A.6.6 0 0 1 22.8 8.2V10.2A.6.6 0 0 1 22.2 10.8H12.9A.6.6 0 0 1 12.3 10.2V8.2A.6.6 0 0 1 12.9 7.6Z'
         . 'M13.1 8.3H14.6V9.7H13.1ZM20.5 8.3H22V9.7H20.5Z"/>'
         /* tower + its two arched windows */
         . '<path fill-rule="evenodd" d="M13.4 10.8H21.7L23 34H12.1Z'
         . 'M16.8 16.2V14.3A.75.75 0 0 1 18.3 14.3V16.2Z'
         . 'M16.7 23V21.05A.85.85 0 0 1 18.4 21.05V23Z"/>'
         /* plinth */
         . '<rect x="10.9" y="34" width="13.3" height="2" rx=".35"/>'
         . '</svg>';
}

/* ---------------------------------------------------------------------------
 * 6. Walker that renders nav-menu items as Font Awesome icons (used by the
 *    social bar block when the user assigns a menu to the 'social' location).
 * ------------------------------------------------------------------------- */
if (!class_exists('Haunted_Tech_Social_Walker')) {
    class Haunted_Tech_Social_Walker extends Walker_Nav_Menu {
        public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
            $url   = $item->url   ?? '#';
            $label = $item->title ?? '';
            $rel   = self::rel_for($url, $label);
            /* Platforms FA 6.5.1 lacks (Civitai, Substack, Lantern) render as
             * inline SVG; everything else uses a Font Awesome glyph. */
            $svg   = self::svg_for($url, $label);
            $inner = ($svg !== '')
                ? $svg
                : sprintf('<i class="%s"></i>', esc_attr(self::icon_for($url, $label)));
            $output .= sprintf(
                '<li><a href="%s" rel="%s" data-label="%s" aria-label="%s">%s</a></li>',
                esc_url($url), esc_attr($rel), esc_attr($label), esc_attr($label), $inner
            );
        }
        /* Inline-SVG marks for platforms not in Font Awesome 6.5.1 Free. Sized
         * to 1em + fill:currentColor so they match the FA glyphs beside them and
         * tint to var(--gold). Paths are the platforms' official logos (Civitai
         * hexagon frame + C; Substack stacked bars; Lantern lighthouse). */
        public static function svg_for($url, $label = '') {
            $s = strtolower(($url ?: '') . ' ' . ($label ?: ''));
            $open = '<svg class="social-svg" width="1em" height="1em" fill="currentColor" aria-hidden="true" focusable="false" style="display:block" ';
            /* Matches the /go/lantern pretty link and the lanternserials.com
             * host alike — both carry the string. */
            if (strpos($s, 'lantern') !== false) {
                return ht_lantern_mark();
            }
            if (strpos($s, 'civitai') !== false) {
                return $open . 'viewBox="-1 0 22.7 22.7"><path d="M10.2,4.7l5.9,3.4V15l-5.9,3.4L4.2,15V8.1L10.2,4.7 M10.2,1.6l-8.7,5v10l8.7,5l8.7-5v-10C18.8,6.6,10.2,1.6,10.2,1.6z"/><path d="M11.8,12.4l-1.7,1l-1.7-1v-1.9l1.7-1l1.7,1h2.1V9.3l-3.8-2.2L6.4,9.3v4.3l3.8,2.2l3.8-2.2v-1.2H11.8z"/></svg>';
            }
            if (strpos($s, 'substack') !== false) {
                return $open . 'viewBox="0 0 24 24"><path d="M22.539 8.242H1.46V5.406h21.08v2.836zM1.46 10.812V24L12 18.11 22.54 24V10.812H1.46zM22.54 0H1.46v2.836h21.08V0z"/></svg>';
            }
            return '';
        }
        public function end_el(&$output, $item, $depth = 0, $args = null) { /* no-op */ }
        /* Commercial/affiliate outbound links get sponsored+nofollow; genuine
         * social profiles stay followable. Matches Pretty Link slugs + hosts. */
        public static function rel_for($url, $label = '') {
            $s = strtolower(($url ?: '') . ' ' . ($label ?: ''));
            $commercial = ['amazon', 'audible', 'barnes', 'noble', '/go/bn', 'kobo', 'apple', 'bookshop', 'gumroad', 'redbubble', 'etsy', 'ko-fi', 'kofi'];
            foreach ($commercial as $needle) {
                if (strpos($s, $needle) !== false) return 'sponsored nofollow noopener';
            }
            return 'noopener';
        }
        public static function icon_for($url, $label = '') {
            $host = parse_url($url, PHP_URL_HOST) ?: '';
            $map = [
                'patreon.com'    => 'fa-brands fa-patreon',
                'ream.com'       => 'fa-solid fa-book-open-reader',
                'reamstories.com'=> 'fa-solid fa-book-open-reader',
                'substack.com'   => 'fa-solid fa-envelope-open-text',
                'discord.com'    => 'fa-brands fa-discord',
                'discord.gg'     => 'fa-brands fa-discord',
                'github.com'     => 'fa-brands fa-github',
                'instagram.com'  => 'fa-brands fa-instagram',
                'tiktok.com'     => 'fa-brands fa-tiktok',
                'goodreads.com'  => 'fa-brands fa-goodreads-g',
                'amazon.com'     => 'fa-brands fa-amazon',
                'threads.net'    => 'fa-brands fa-threads',
                'twitter.com'    => 'fa-brands fa-x-twitter',
                'x.com'          => 'fa-brands fa-x-twitter',
                /* v0.9 — extra platforms */
                'youtube.com'    => 'fa-brands fa-youtube',
                'facebook.com'   => 'fa-brands fa-facebook',
                'bookbub.com'    => 'fa-solid fa-book-bookmark',
                'civitai.com'    => 'fa-solid fa-palette',
                'redbubble.com'  => 'fa-solid fa-shirt',
            ];
            foreach ($map as $needle => $cls) {
                if (strpos($host, $needle) !== false) return $cls;
            }

            /* v0.9.2 — slug/label fallback. Lets Pretty Link URLs
             * (codalanguez.com/go/<slug>) resolve to brand icons by also
             * checking the URL path and the menu item label for platform
             * keywords. Slugs are intentionally shorter than the host keys
             * (no ".com" suffix) to match path segments. X/Twitter is omitted
             * from this pass since the single letter "x" is too ambiguous —
             * use the host map (twitter.com / x.com) instead. */
            $haystack = strtolower(($url ?: '') . ' ' . ($label ?: ''));
            $slug_map = [
                'patreon'   => 'fa-brands fa-patreon',
                'reamstories' => 'fa-solid fa-book-open-reader',
                'ream'      => 'fa-solid fa-book-open-reader',
                'substack'  => 'fa-solid fa-envelope-open-text',
                'discord'   => 'fa-brands fa-discord',
                'github'    => 'fa-brands fa-github',
                'instagram' => 'fa-brands fa-instagram',
                'tiktok'    => 'fa-brands fa-tiktok',
                'goodreads' => 'fa-brands fa-goodreads-g',
                'amazon'    => 'fa-brands fa-amazon',
                'threads'   => 'fa-brands fa-threads',
                'youtube'   => 'fa-brands fa-youtube',
                'facebook'  => 'fa-brands fa-facebook',
                'bookbub'   => 'fa-solid fa-book-bookmark',
                'civitai'   => 'fa-solid fa-palette',
                'redbubble' => 'fa-solid fa-shirt',
            ];
            foreach ($slug_map as $needle => $cls) {
                if (strpos($haystack, $needle) !== false) return $cls;
            }

            return 'fa-solid fa-link';
        }
    }
}

/* ---------------------------------------------------------------------------
 * 6b. Meta descriptions for the custom post type archives
 *
 * Rank Math has no per-post-type archive description setting, so /book/,
 * /chapter/ and /webnovel/ fall back to "<Label> Archive - <Site>" — around
 * 30 characters against a 150-160 target. These three hub pages are the top
 * of their sections, so they are worth real copy. Falls through untouched
 * for anything that is not one of these archives, and for singles.
 * ------------------------------------------------------------------------- */
add_filter('rank_math/frontend/description', function ($description) {
    if (! is_post_type_archive()) {
        return $description;
    }

    $post_type = get_query_var('post_type');
    if (is_array($post_type)) {
        $post_type = reset($post_type);
    }

    $descriptions = [
        'book'     => 'Every published book by Coda Languez in one place — dark fantasy romance, gothic horror and magic realism, with buy links and content notes for each title.',
        'chapter'  => "Every chapter of Coda Languez's web novels, free to start. Gothic horror, dark romance and fairytale retellings, updating across five serials each week.",
        'webnovel' => "All five of Coda Languez's ongoing web novels — gothic horror, dark romance and fairytale retellings. Start any of them free, new chapters every week.",
    ];

    return $descriptions[$post_type] ?? $description;
});

/* ---------------------------------------------------------------------------
 * 7. Body classes
 * ------------------------------------------------------------------------- */
add_filter('body_class', function ($classes) {
    if (is_front_page()) $classes[] = 'haunted-tech-home';
    return $classes;
});

/* ---------------------------------------------------------------------------
 * 8. Includes — render callbacks, dynamic blocks, patterns
 * ------------------------------------------------------------------------- */
require_once HAUNTED_TECH_DIR . '/inc/customizer.php';
require_once HAUNTED_TECH_DIR . '/inc/render-callbacks.php';
require_once HAUNTED_TECH_DIR . '/inc/blocks.php';
require_once HAUNTED_TECH_DIR . '/inc/patterns.php';
require_once HAUNTED_TECH_DIR . '/inc/rest.php';
require_once HAUNTED_TECH_DIR . '/inc/commission-forms.php';
require_once HAUNTED_TECH_DIR . '/inc/book-schema.php';
require_once HAUNTED_TECH_DIR . '/inc/chapter-schema.php';
require_once HAUNTED_TECH_DIR . '/inc/webnovel-schema.php';
require_once HAUNTED_TECH_DIR . '/inc/redirects.php';
require_once HAUNTED_TECH_DIR . '/inc/links-domain.php';
if (is_admin()) {
    require_once HAUNTED_TECH_DIR . '/inc/onboarding.php';
}
