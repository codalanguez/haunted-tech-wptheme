<?php
/**
 * Haunted Tech — Book JSON-LD schema for single `book` pages.
 *
 * Emits schema.org/Book structured data for every Book, built from the
 * theme's existing ACF fields (isbn, asin, cover, publish_date, page_count,
 * genre, buy links, discovery links). Covers all existing AND future books
 * automatically — no per-post editing.
 *
 * Integration:
 *   - Preferred: joins Rank Math's existing JSON-LD @graph via the
 *     `rank_math/json_ld` filter (single <script>, no duplication). It also
 *     removes any Book node Rank Math itself would emit for the CPT default,
 *     so there is never a duplicate Book.
 *   - Fallback: if Rank Math is inactive, prints its own <script> on wp_head.
 *
 * Install (pick one):
 *   A) Theme include (matches this theme's convention) — commit this file at
 *      inc/book-schema.php and add to the "8. Includes" block in functions.php,
 *      alongside the other require_once lines:
 *          require_once HAUNTED_TECH_DIR . '/inc/book-schema.php';
 *   B) Update-safe — drop this file at wp-content/mu-plugins/book-schema.php
 *      (auto-loaded, survives theme updates). No functions.php edit needed.
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Build the Book schema array for a given book post ID.
 *
 * @param int $post_id
 * @return array|null  Book node, or null if the post isn't a usable book.
 */
function ht_build_book_schema($post_id) {
    $post_id = (int) $post_id;
    if (!$post_id || get_post_type($post_id) !== 'book') {
        return null;
    }

    $permalink = get_permalink($post_id);
    $home      = home_url('/');

    // --- Author (inline Person — the book page's graph has no #person node) ---
    $author = [
        '@type' => 'Person',
        'name'  => 'Coda Languez',
        'url'   => $home,
    ];

    $book = [
        '@type'      => 'Book',
        '@id'        => $permalink . '#book',
        'name'       => wp_strip_all_tags(get_the_title($post_id)),
        'author'     => $author,
        'url'        => $permalink,
        'inLanguage' => 'en-US',
    ];

    // --- Description: prefer blurb, then tagline, then excerpt ---
    $desc = get_post_meta($post_id, 'blurb', true);
    if (!$desc) { $desc = get_post_meta($post_id, 'tagline', true); }
    if (!$desc) { $desc = get_the_excerpt($post_id); }
    if ($desc) { $book['description'] = wp_strip_all_tags($desc); }

    // --- Cover image ---
    $cover_id = get_post_meta($post_id, 'cover', true);
    if (!$cover_id) { $cover_id = get_post_thumbnail_id($post_id); }
    if ($cover_id) {
        $img = wp_get_attachment_image_url((int) $cover_id, 'large');
        if ($img) { $book['image'] = $img; }
    }

    // --- ISBN ---
    $isbn = trim((string) get_post_meta($post_id, 'isbn', true));
    if ($isbn !== '') { $book['isbn'] = $isbn; }

    // --- Page count ---
    $pages = (int) get_post_meta($post_id, 'page_count', true);
    if ($pages > 0) { $book['numberOfPages'] = $pages; }

    // --- Date published (stored as Ymd, e.g. 20230109) ---
    $raw_date = trim((string) get_post_meta($post_id, 'publish_date', true));
    if ($raw_date !== '') {
        $d = DateTime::createFromFormat('Ymd', $raw_date);
        if ($d instanceof DateTime) {
            $book['datePublished'] = $d->format('Y-m-d');
        } else {
            $ts = strtotime($raw_date);
            if ($ts) { $book['datePublished'] = gmdate('Y-m-d', $ts); }
        }
    }

    // --- Genre (comma-separated → array) ---
    $genre = trim((string) get_post_meta($post_id, 'genre', true));
    if ($genre !== '') {
        $parts = array_values(array_filter(array_map('trim', explode(',', $genre))));
        if ($parts) { $book['genre'] = count($parts) === 1 ? $parts[0] : $parts; }
    }

    // --- sameAs (discovery links) ---
    $same = [];
    foreach (['goodreads_url', 'storygraph_url', 'bookbub_url'] as $k) {
        $u = trim((string) get_post_meta($post_id, $k, true));
        if ($u !== '' && filter_var($u, FILTER_VALIDATE_URL)) { $same[] = $u; }
    }
    if ($same) { $book['sameAs'] = array_values(array_unique($same)); }

    // --- workExample: a concrete edition + a ReadAction to the buy link ---
    $amazon = trim((string) get_post_meta($post_id, 'buy_amazon', true));
    $asin   = trim((string) get_post_meta($post_id, 'asin', true));
    if ($amazon !== '' || $asin !== '') {
        $ku = get_post_meta($post_id, 'kindle_unlimited', true);
        $example = [
            '@type'      => 'Book',
            'bookFormat' => 'https://schema.org/EBook',
            'inLanguage' => 'en-US',
        ];
        if ($isbn !== '') { $example['isbn'] = $isbn; }
        if ($amazon !== '') {
            $example['potentialAction'] = [
                '@type'       => 'ReadAction',
                'target'      => $amazon,
                'expectsAcceptanceOf' => [
                    '@type'    => 'Offer',
                    'category' => $ku ? 'subscription' : 'purchase',
                    'availability' => 'https://schema.org/InStock',
                ],
            ];
        }
        $book['workExample'] = $example;
    }

    /**
     * Let other code tweak the final Book node.
     * @param array $book
     * @param int   $post_id
     */
    return apply_filters('ht_book_schema', $book, $post_id);
}

/* ---------------------------------------------------------------------------
 * Preferred path: merge into Rank Math's existing @graph.
 * Rank Math flattens each top-level entry of $data into the @graph array, so
 * adding $data['book'] appends our Book node alongside the BreadcrumbList.
 * ------------------------------------------------------------------------- */
add_filter('rank_math/json_ld', function ($data, $jsonld) {
    if (!is_singular('book')) { return $data; }

    // Remove any Book/Article node Rank Math emits on book pages (from the CPT
    // schema default — whether that default is "Book" or the "Article" stopgap),
    // so our Book node is the single source of truth and nothing duplicates.
    // BreadcrumbList, WebPage, Person, Organization, etc. are left intact.
    foreach ((array) $data as $key => $node) {
        if (is_array($node) && isset($node['@type'])) {
            $type = is_array($node['@type']) ? $node['@type'] : [$node['@type']];
            if (array_intersect(['Book', 'Article', 'BlogPosting'], $type)) {
                unset($data[$key]);
            }
        }
    }

    $book = ht_build_book_schema(get_queried_object_id());
    if ($book) { $data['book'] = $book; }

    return $data;
}, 20, 2);

/* ---------------------------------------------------------------------------
 * Fallback: if Rank Math isn't handling JSON-LD, print our own <script>.
 * ------------------------------------------------------------------------- */
add_action('wp_head', function () {
    if (!is_singular('book')) { return; }
    // Rank Math active → the filter above already handled it.
    if (class_exists('RankMath') || defined('RANK_MATH_VERSION')) { return; }

    $book = ht_build_book_schema(get_queried_object_id());
    if (!$book) { return; }

    $graph = [
        '@context' => 'https://schema.org',
        '@graph'   => [$book],
    ];
    echo "\n<script type=\"application/ld+json\">"
        . wp_json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        . "</script>\n";
}, 20);
