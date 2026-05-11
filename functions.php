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

define('HAUNTED_TECH_VERSION', '0.5.0');
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

    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
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
});

/* Register the ACF field group for hero_update entries. */
add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) { return; }

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
            $icon  = self::icon_for($url);
            $output .= sprintf(
                '<li><a href="%s" data-label="%s" aria-label="%s"><i class="%s"></i></a></li>',
                esc_url($url), esc_attr($label), esc_attr($label), esc_attr($icon)
            );
        }
        public function end_el(&$output, $item, $depth = 0, $args = null) { /* no-op */ }
        public static function icon_for($url) {
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
            ];
            foreach ($map as $needle => $cls) {
                if (strpos($host, $needle) !== false) return $cls;
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
