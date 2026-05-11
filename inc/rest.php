<?php
/**
 * REST endpoints — bookmodal content fetch.
 *
 * Exposes /wp-json/haunted-tech/v1/book-modal/<id-or-slug> which returns the
 * pre-rendered HTML for a single book, suitable for direct injection into the
 * homepage #book-modal-body. The JS in assets/main.js calls this endpoint
 * when a user clicks any bookshelf spine.
 *
 * The same render pipeline is used by templates/single-book.html, so the
 * standalone book page and the modal show identical content.
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

add_action('rest_api_init', function () {
    register_rest_route('haunted-tech/v1', '/book-modal/(?P<key>[\w-]+)', [
        'methods'             => 'GET',
        'callback'            => 'haunted_tech_rest_book_modal',
        'permission_callback' => '__return_true',
        'args'                => [
            'key' => [
                'description' => 'Numeric post ID or slug of the book post.',
                'type'        => 'string',
                'required'    => true,
            ],
        ],
    ]);
});

function haunted_tech_rest_book_modal(\WP_REST_Request $req) {
    $key  = $req['key'];
    $post = null;

    if (ctype_digit((string)$key)) {
        $post = get_post((int)$key);
    } else {
        $q = get_posts([
            'name'           => $key,
            'post_type'      => 'book',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
        ]);
        if (!empty($q)) $post = $q[0];
    }

    if (!$post || $post->post_type !== 'book' || $post->post_status !== 'publish') {
        return new \WP_Error('not_found', __('Book not found', 'haunted-tech'), ['status' => 404]);
    }

    /* Set up the global post context so get_the_ID() inside the render
     * callbacks resolves to this book. */
    global $wp_query;
    $orig = $wp_query;
    $wp_query = new \WP_Query(['p' => $post->ID, 'post_type' => 'book']);
    $wp_query->the_post();

    $html = ht_render_book_modal_content();

    wp_reset_postdata();
    $wp_query = $orig;

    return rest_ensure_response([
        'id'    => $post->ID,
        'slug'  => $post->post_name,
        'title' => get_the_title($post),
        'url'   => get_permalink($post),
        'html'  => $html,
    ]);
}
