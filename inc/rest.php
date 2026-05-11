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
        'args'                => ['key' => ['type' => 'string', 'required' => true]],
    ]);
    register_rest_route('haunted-tech/v1', '/webnovel-modal/(?P<key>[\w-]+)', [
        'methods'             => 'GET',
        'callback'            => 'haunted_tech_rest_webnovel_modal',
        'permission_callback' => '__return_true',
        'args'                => ['key' => ['type' => 'string', 'required' => true]],
    ]);
});

/**
 * Resolve a numeric ID or slug to a published post of a specific CPT.
 */
function haunted_tech_resolve_post($key, $post_type) {
    if (ctype_digit((string)$key)) {
        $p = get_post((int)$key);
        return ($p && $p->post_type === $post_type && $p->post_status === 'publish') ? $p : null;
    }
    $q = get_posts([
        'name'           => $key,
        'post_type'      => $post_type,
        'posts_per_page' => 1,
        'post_status'    => 'publish',
    ]);
    return !empty($q) ? $q[0] : null;
}

function haunted_tech_rest_webnovel_modal(\WP_REST_Request $req) {
    $post = haunted_tech_resolve_post($req['key'], 'webnovel');
    if (!$post) {
        return new \WP_Error('not_found', __('Web novel not found', 'haunted-tech'), ['status' => 404]);
    }
    global $wp_query;
    $orig = $wp_query;
    $wp_query = new \WP_Query(['p' => $post->ID, 'post_type' => 'webnovel']);
    $wp_query->the_post();
    $html = ht_render_webnovel_modal_content();
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

function haunted_tech_rest_book_modal(\WP_REST_Request $req) {
    $post = haunted_tech_resolve_post($req['key'], 'book');
    if (!$post) {
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
