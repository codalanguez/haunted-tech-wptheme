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

define('HAUNTED_TECH_VERSION', '0.11.2');
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

    // Self-hosted Font Awesome 6.5.1 (Free). 1 css + 8 webfont files in
    // assets/fontawesome/. ttf files are leftover fallbacks; browsers prefer
    // woff2. Total bundle ~1 MB, but unicode-range gating + browser cache
    // means a typical page costs ~150 KB on first load and ~0 KB thereafter.
    wp_enqueue_style(
        'font-awesome',
        HAUNTED_TECH_URI . '/assets/fontawesome/all.min.css',
        [],
        '6.5.1'
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

    wp_enqueue_script(
        'haunted-tech-main',
        HAUNTED_TECH_URI . '/assets/main.js',
        [],
        HAUNTED_TECH_VERSION,
        true
    );
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
        'supports'     => ['title', 'editor', 'custom-fields'],
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

function haunted_tech_get_hero_slides($limit = 3) {
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

/* ---------------------------------------------------------------------------
 * 6. Walker that renders nav-menu items as Font Awesome icons (used by the
 *    social bar block when the user assigns a menu to the 'social' location).
 * ------------------------------------------------------------------------- */
if (!class_exists('Haunted_Tech_Social_Walker')) {
    class Haunted_Tech_Social_Walker extends Walker_Nav_Menu {
        public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
            $url   = $item->url   ?? '#';
            $label = $item->title ?? '';
            $icon  = self::icon_for($url, $label);
            $output .= sprintf(
                '<li><a href="%s" data-label="%s" aria-label="%s"><i class="%s"></i></a></li>',
                esc_url($url), esc_attr($label), esc_attr($label), esc_attr($icon)
            );
        }
        public function end_el(&$output, $item, $depth = 0, $args = null) { /* no-op */ }
        public static function icon_for($url, $label = '') {
            $host = parse_url($url, PHP_URL_HOST) ?: '';
            $map = [
                'patreon.com'    => 'fa-brands fa-patreon',
                'ream.com'       => 'fa-solid fa-book-open-reader',
                'reamstories.com'=> 'fa-solid fa-book-open-reader',
                'substack.com'   => 'fa-solid fa-envelope-open-text',
                'discord.com'    => 'fa-brands fa-discord',
                'discord.gg'     => 'fa-brands fa-discord',
                'bsky.app'       => 'fa-brands fa-bluesky',
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
                'bluesky'   => 'fa-brands fa-bluesky',
                'bsky'      => 'fa-brands fa-bluesky',
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
if (is_admin()) {
    require_once HAUNTED_TECH_DIR . '/inc/onboarding.php';
}
