<?php
/**
 * Register Haunted Tech dynamic blocks.
 *
 * Each block has a server-side render callback (in inc/render-callbacks.php).
 * They appear under their own block category in the editor and can be inserted
 * into any post, page, template, or template part.
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

/* Add a custom block category so our blocks group together in the inserter. */
add_filter('block_categories_all', function ($categories) {
    array_unshift($categories, [
        'slug'  => 'haunted-tech',
        'title' => __('Haunted Tech', 'haunted-tech'),
        'icon'  => null,
    ]);
    return $categories;
});

/* Register all blocks. Each block is server-rendered — it has no front-end
 * editor save() function, just a render_callback returning HTML. */
add_action('init', function () {
    $blocks = [
        ['slug' => 'social-bar',   'title' => 'Social Bar',     'render' => 'ht_render_social_bar',   'icon' => 'share'],
        ['slug' => 'site-header',  'title' => 'Site Header',    'render' => 'ht_render_site_header',  'icon' => 'menu'],
        ['slug' => 'site-footer',  'title' => 'Site Footer',    'render' => 'ht_render_site_footer',  'icon' => 'arrow-down'],
        ['slug' => 'overlays',     'title' => 'CRT Overlays',   'render' => 'ht_render_overlays',     'icon' => 'tablet'],
        ['slug' => 'hero-slider',  'title' => 'Hero Slider',    'render' => 'ht_render_hero_slider',  'icon' => 'slides'],
        ['slug' => 'bookshelf',    'title' => 'Bookshelf',      'render' => 'ht_render_bookshelf',    'icon' => 'book'],
        ['slug' => 'crt-monitor',  'title' => 'CRT Monitor',    'render' => 'ht_render_crt_monitor',  'icon' => 'desktop'],
        ['slug' => 'services',     'title' => 'Services',       'render' => 'ht_render_services',     'icon' => 'awards'],
        ['slug' => 'gallery',      'title' => 'Gallery',        'render' => 'ht_render_gallery',      'icon' => 'format-gallery'],
        ['slug' => 'newsletter',   'title' => 'Newsletter',     'render' => 'ht_render_newsletter',   'icon' => 'email-alt'],
        ['slug' => 'lightbox',     'title' => 'Lightbox',       'render' => 'ht_render_lightbox',     'icon' => 'fullscreen-alt'],
        ['slug' => 'about-modal',  'title' => 'About Modal',    'render' => 'ht_render_about_modal',  'icon' => 'admin-users'],
        ['slug' => 'single-book',         'title' => 'Single Book Layout',      'render' => 'ht_render_single_book',         'icon' => 'book-alt'],
        ['slug' => 'single-webnovel',     'title' => 'Single Web Novel Layout', 'render' => 'ht_render_single_webnovel',     'icon' => 'welcome-write-blog'],
        ['slug' => 'single-chapter',      'title' => 'Single Chapter Layout',   'render' => 'ht_render_single_chapter',      'icon' => 'text-page'],
        /* v0.8 book-page sections + global singletons */
        ['slug' => 'book-excerpt',        'title' => 'Book Excerpt',            'render' => 'ht_render_book_excerpt',        'icon' => 'edit-page'],
        ['slug' => 'book-more-in-series', 'title' => 'Book — More in Series',   'render' => 'ht_render_book_more_in_series', 'icon' => 'book'],
        ['slug' => 'also-by',             'title' => 'Also by (Author)',        'render' => 'ht_render_also_by',             'icon' => 'screenoptions'],
        ['slug' => 'book-modal',          'title' => 'Book Modal (singleton)',  'render' => 'ht_render_book_modal_shell',    'icon' => 'fullscreen-alt'],
        ['slug' => 'webnovel-modal',      'title' => 'Web Novel Modal (singleton)','render' => 'ht_render_webnovel_modal_shell','icon' => 'fullscreen-alt'],
        ['slug' => 'also-by-webnovels',   'title' => 'Also by (Web Novels)',    'render' => 'ht_render_also_by_webnovels',   'icon' => 'admin-page'],
        ['slug' => 'linktree',            'title' => 'Linktree Page',           'render' => 'ht_render_linktree',            'icon' => 'admin-links'],
        ['slug' => 'back-to-top',         'title' => 'Back-to-Top Arrow',       'render' => 'ht_render_back_to_top',         'icon' => 'arrow-up-alt'],
    ];

    foreach ($blocks as $b) {
        register_block_type('haunted-tech/' . $b['slug'], [
            'api_version'     => 3,
            'title'           => $b['title'],
            'category'        => 'haunted-tech',
            'icon'            => $b['icon'],
            'description'     => 'Haunted Tech: ' . $b['title'],
            'supports'        => [
                'html'      => false,
                'reusable'  => false,
                'inserter'  => true,
            ],
            'render_callback' => $b['render'],
            'attributes'      => [
                'limit' => ['type' => 'number'],
            ],
        ]);
    }
});
