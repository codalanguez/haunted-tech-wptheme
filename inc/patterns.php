<?php
/**
 * Register block patterns + a "Haunted Tech" pattern category.
 *
 * Patterns are pre-arranged block compositions the user can insert from the
 * block editor. We use them to bundle individual Haunted Tech blocks into
 * common section layouts.
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

add_action('init', function () {
    register_block_pattern_category('haunted-tech', [
        'label' => __('Haunted Tech', 'haunted-tech'),
    ]);

    register_block_pattern('haunted-tech/full-homepage', [
        'title'       => __('Full Homepage', 'haunted-tech'),
        'description' => __('All sections in the standard order: hero slider, bookshelf, CRT monitor, services, gallery, newsletter.', 'haunted-tech'),
        'categories'  => ['haunted-tech'],
        'content'     => '<!-- wp:haunted-tech/hero-slider /-->'
                       . '<!-- wp:haunted-tech/bookshelf /-->'
                       . '<!-- wp:haunted-tech/crt-monitor /-->'
                       . '<!-- wp:haunted-tech/services /-->'
                       . '<!-- wp:haunted-tech/gallery /-->'
                       . '<!-- wp:haunted-tech/newsletter /-->',
    ]);

    register_block_pattern('haunted-tech/books-and-novels', [
        'title'       => __('Books + Web Novels', 'haunted-tech'),
        'description' => __('Bookshelf above the CRT monitor.', 'haunted-tech'),
        'categories'  => ['haunted-tech'],
        'content'     => '<!-- wp:haunted-tech/bookshelf /-->'
                       . '<!-- wp:haunted-tech/crt-monitor /-->',
    ]);

    register_block_pattern('haunted-tech/services-and-gallery', [
        'title'       => __('Services + Gallery', 'haunted-tech'),
        'description' => __('Service cards followed by the tabbed gallery.', 'haunted-tech'),
        'categories'  => ['haunted-tech'],
        'content'     => '<!-- wp:haunted-tech/services /-->'
                       . '<!-- wp:haunted-tech/gallery /-->',
    ]);
});
