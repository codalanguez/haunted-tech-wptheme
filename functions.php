<?php
/**
 * Haunted Tech — theme bootstrap.
 *
 * Registers theme supports, enqueues fonts + main.css + main.js,
 * registers the `hero_update` CPT (the slider data source),
 * declares a "social" nav menu location,
 * and wires custom-logo support so the WP customizer can replace the bundled logo.
 *
 * Books / web novels / chapters CPTs are managed by ACF on this site
 * (see /assets/acf/*.json in this repo for importable definitions).
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

define('HAUNTED_TECH_VERSION', '0.1.0');
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
});

/* ---------------------------------------------------------------------------
 * 2. Enqueue styles & scripts
 * ------------------------------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {
    // Google Fonts (Forum, Inter, Cormorant Garamond, VT323)
    wp_enqueue_style(
        'haunted-tech-fonts',
        'https://fonts.googleapis.com/css2?family=Forum&family=Inter:wght@300;400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=VT323&display=swap',
        [],
        null
    );

    // Font Awesome 6 — for social bar icons
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        '6.5.1'
    );

    // Theme metadata (required by WP — empty body, just header)
    wp_enqueue_style(
        'haunted-tech-style',
        get_stylesheet_uri(),
        [],
        HAUNTED_TECH_VERSION
    );

    // Main stylesheet — all the actual design lives here
    wp_enqueue_style(
        'haunted-tech-main',
        HAUNTED_TECH_URI . '/assets/main.css',
        ['haunted-tech-style'],
        HAUNTED_TECH_VERSION
    );

    // Main JS — hero slider, gallery, lightbox, about modal
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

/**
 * Register the ACF field group for hero_update entries.
 * Each update is one slide on the homepage carousel.
 */
add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) { return; }

    acf_add_local_field_group([
        'key'      => 'group_hero_update',
        'title'    => 'Hero Update',
        'fields'   => [
            [
                'key'           => 'field_hero_type',
                'label'         => 'Update Type',
                'name'          => 'update_type',
                'type'          => 'select',
                'choices'       => [
                    'book'    => 'Book Release (gold)',
                    'chapter' => 'Chapter Drop (red)',
                    'mandate' => 'Mandate / Misc (cyan)',
                ],
                'default_value' => 'mandate',
                'required'      => 1,
                'show_in_rest'  => 1,
            ],
            [
                'key'          => 'field_hero_eyebrow',
                'label'        => 'Eyebrow',
                'name'         => 'eyebrow',
                'type'         => 'text',
                'instructions' => "Small label above the title, e.g. 'New Release · Hardcover'",
                'show_in_rest' => 1,
            ],
            [
                'key'          => 'field_hero_title_first',
                'label'        => 'Title — First Part',
                'name'         => 'title_first',
                'type'         => 'text',
                'instructions' => "First half of the title (e.g. 'HEX'). Plain.",
                'show_in_rest' => 1,
            ],
            [
                'key'          => 'field_hero_title_accent',
                'label'        => 'Title — Accent Part',
                'name'         => 'title_accent',
                'type'         => 'text',
                'instructions' => "Second half (e.g. 'ROSE'). Rendered in gold with extra glow.",
                'show_in_rest' => 1,
            ],
            [
                'key'          => 'field_hero_blurb',
                'label'        => 'Blurb',
                'name'         => 'blurb',
                'type'         => 'textarea',
                'rows'         => 4,
                'show_in_rest' => 1,
            ],
            [
                'key'          => 'field_hero_cta_label',
                'label'        => 'CTA Button Label',
                'name'         => 'cta_label',
                'type'         => 'text',
                'default_value'=> 'Read More',
                'show_in_rest' => 1,
            ],
            [
                'key'          => 'field_hero_cta_link',
                'label'        => 'CTA Link',
                'name'         => 'cta_link',
                'type'         => 'url',
                'show_in_rest' => 1,
            ],
        ],
        'location' => [[
            ['param' => 'post_type', 'operator' => '==', 'value' => 'hero_update'],
        ]],
        'menu_order'   => 0,
        'position'     => 'normal',
        'style'        => 'default',
        'active'       => true,
        'show_in_rest' => 1,
    ]);
});

/* ---------------------------------------------------------------------------
 * 4. Helper: render the site logo (custom logo from customizer, falls back
 *    to the bundled assets/logo.png).
 * ------------------------------------------------------------------------- */
function haunted_tech_logo_url() {
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $src = wp_get_attachment_image_src($custom_logo_id, 'full');
        if ($src) return $src[0];
    }
    return HAUNTED_TECH_URI . '/assets/logo.png';
}

/* ---------------------------------------------------------------------------
 * 5. Helper: fetch the 3 most recent hero_update posts
 * ------------------------------------------------------------------------- */
function haunted_tech_get_hero_slides($limit = 3) {
    return get_posts([
        'post_type'      => 'hero_update',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    ]);
}

/* ---------------------------------------------------------------------------
 * 6. Helper: escape & format the 'accent' span split of a title
 *    Given title_first + title_accent, produces:
 *      <h1 data-text="HEX ROSE">HEX <span class="gold">ROSE</span></h1>
 * ------------------------------------------------------------------------- */
function haunted_tech_render_hero_title($first, $accent) {
    $combined = trim($first . ' ' . $accent);
    printf(
        '<h1 data-text="%s">%s <span class="gold">%s</span></h1>',
        esc_attr($combined),
        esc_html($first),
        esc_html($accent)
    );
}

/* ---------------------------------------------------------------------------
 * 7. Body classes — let us scope block-pattern styles
 * ------------------------------------------------------------------------- */
add_filter('body_class', function ($classes) {
    if (is_front_page()) $classes[] = 'haunted-tech-home';
    return $classes;
});
