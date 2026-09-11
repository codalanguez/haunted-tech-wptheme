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
        'description' => __('Reader-first homepage: featured serial, reader doors, serial archive, newsletter, books, studio, gallery and lab.', 'haunted-tech'),
        'categories'  => ['haunted-tech'],
        'content'     => '<!-- wp:haunted-tech/featured-serial /-->'
                       . '<!-- wp:haunted-tech/serial-doors {"limit":3} /-->'
                       . '<!-- wp:haunted-tech/latest-episodes {"limit":6} /-->'
                       . '<!-- wp:haunted-tech/newsletter /-->'
                       . '<!-- wp:haunted-tech/bookshelf /-->'
                       . '<!-- wp:haunted-tech/services /-->'
                       . '<!-- wp:haunted-tech/gallery /-->'
                       . '<!-- wp:haunted-tech/lab /-->',
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
