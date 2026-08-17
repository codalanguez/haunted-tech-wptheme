<?php
/**
 * Haunted Tech — Book JSON-LD schema for single `webnovel` pages.
 *
 * Investigation (2026-08-17): the five `webnovel` singles emitted
 * BreadcrumbList and nothing else — no entity node at all — despite
 * rank-math-options-titles carrying pt_webnovel_default_rich_snippet =
 * "book". Same shape as the Book CPT before inc/book-schema.php: Rank
 * Math's CPT schema default only lands on posts saved after it was set,
 * so the existing serials never picked it up.
 *
 * The @id is not free choice. inc/chapter-schema.php already emits, on
 * every chapter page:
 *
 *     isPartOf => [ '@type' => 'Book',
 *                   '@id'   => <webnovel permalink> . '#book', ... ]
 *
 * so all 32 chapters were pointing at a node that did not exist anywhere.
 * This module claims exactly that @id, which turns a dangling reference
 * into a resolvable one and makes the chapter -> serial relationship a
 * real graph edge in both directions (`isPartOf` from the chapter,
 * `hasPart` from here).
 *
 * `Book` rather than `CreativeWorkSeries` is deliberate: the @id contract
 * above already says Book, Rank Math's own CPT default says Book, and
 * Book is the type with actual search support. A serial is a book being
 * published in parts.
 *
 * Same integration pattern as inc/book-schema.php and inc/chapter-schema.php:
 * join Rank Math's existing @graph via `rank_math/json_ld` (one <script>,
 * no duplication), stripping any competing node Rank Math emits rather
 * than patching it in place — see the chapter module's note on why
 * patching does not survive. wp_head fallback if Rank Math is inactive.
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Chapters belonging to a webnovel, in reading order.
 *
 * chapter_number is a string and not always a plain integer — "0" for a
 * prologue and "1-3" for a bundled opener are both live on the site — so
 * ordering keys off the first integer found, falling back to publish date.
 *
 * @param int $webnovel_id
 * @return WP_Post[]
 */
function ht_get_webnovel_chapters($webnovel_id) {
    $chapters = get_posts([
        'post_type'      => 'chapter',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => 'webnovel',
        'meta_value'     => (int) $webnovel_id,
        'orderby'        => 'date',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    ]);

    usort($chapters, function ($a, $b) {
        $na = get_post_meta($a->ID, 'chapter_number', true);
        $nb = get_post_meta($b->ID, 'chapter_number', true);
        $ia = preg_match('/\d+/', (string) $na, $m) ? (int) $m[0] : PHP_INT_MAX;
        $ib = preg_match('/\d+/', (string) $nb, $m) ? (int) $m[0] : PHP_INT_MAX;
        if ($ia === $ib) {
            return strcmp($a->post_date, $b->post_date);
        }
        return $ia <=> $ib;
    });

    return $chapters;
}

/**
 * Build the Book schema array for a given webnovel post ID.
 *
 * @param int $post_id
 * @return array|null  Book node, or null if the post isn't a usable webnovel.
 */
function ht_build_webnovel_schema($post_id) {
    $post_id = (int) $post_id;
    if (!$post_id || get_post_type($post_id) !== 'webnovel') {
        return null;
    }

    $permalink = get_permalink($post_id);
    $home      = home_url('/');

    $book = [
        '@type'            => 'Book',
        // Must match inc/chapter-schema.php's isPartOf @id exactly.
        '@id'              => $permalink . '#book',
        'name'             => wp_strip_all_tags(get_the_title($post_id)),
        'url'              => $permalink,
        'inLanguage'       => 'en-US',
        'author'           => ['@id' => $home . '#person'],
        'publisher'        => ['@id' => $home . '#person'],
        'mainEntityOfPage' => ['@id' => $permalink . '#webpage'],
        'datePublished'    => get_the_date('c', $post_id),
        'dateModified'     => get_the_modified_date('c', $post_id),
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

    // --- Genre (comma-separated → array) ---
    $genre = trim((string) get_post_meta($post_id, 'genre', true));
    if ($genre !== '') {
        $parts = array_values(array_filter(array_map('trim', explode(',', $genre))));
        if ($parts) { $book['genre'] = count($parts) === 1 ? $parts[0] : $parts; }
    }

    // --- Tropes → keywords ---
    $tropes = trim((string) get_post_meta($post_id, 'tropes', true));
    if ($tropes !== '') { $book['keywords'] = $tropes; }

    // --- Publication status ("ongoing" / "complete") ---
    $status = trim((string) get_post_meta($post_id, 'status', true));
    if ($status !== '') { $book['creativeWorkStatus'] = ucfirst($status); }

    // --- Substack home: sameAs + a ReadAction, mirroring the Book module ---
    $substack = trim((string) get_post_meta($post_id, 'substack_url', true));
    if ($substack !== '' && filter_var($substack, FILTER_VALIDATE_URL)) {
        $book['sameAs'] = [$substack];
        $book['potentialAction'] = [
            '@type'  => 'ReadAction',
            'target' => $substack,
        ];
    }

    // --- Chapters: hasPart, the reverse edge of chapter-schema's isPartOf ---
    $chapters = ht_get_webnovel_chapters($post_id);
    if ($chapters) {
        $parts     = [];
        $all_free  = true;
        foreach ($chapters as $chapter) {
            $chapter_url = get_permalink($chapter->ID);
            $part = [
                '@type' => 'Article',
                '@id'   => $chapter_url . '#chapterArticle',
                'name'  => wp_strip_all_tags(get_the_title($chapter->ID)),
                'url'   => $chapter_url,
            ];
            $number = get_post_meta($chapter->ID, 'chapter_number', true);
            if ($number !== '' && $number !== null && preg_match('/\d+/', (string) $number, $m)) {
                $part['position'] = (int) $m[0];
            }
            $parts[] = $part;

            if (get_post_meta($chapter->ID, 'access_level', true) !== 'free') {
                $all_free = false;
            }
        }
        // No count property here on purpose: numberOfEpisodes belongs to the
        // series types, not Book, and hasPart already carries the count.
        $book['hasPart'] = $parts;

        // Only asserted when every published chapter really is free — true
        // for the gateway serial, false for the paid ones.
        if ($all_free) { $book['isAccessibleForFree'] = true; }
    }

    /**
     * Let other code tweak the final webnovel Book node.
     * @param array $book
     * @param int   $post_id
     */
    return apply_filters('ht_webnovel_schema', $book, $post_id);
}

/* ---------------------------------------------------------------------------
 * Preferred path: merge into Rank Math's existing @graph, removing any
 * competing entity node first so there is exactly one Book per page.
 * ------------------------------------------------------------------------- */
add_filter('rank_math/json_ld', function ($data, $jsonld) {
    if (!is_singular('webnovel')) { return $data; }

    foreach ((array) $data as $key => $node) {
        if (is_array($node) && isset($node['@type'])) {
            $type = is_array($node['@type']) ? $node['@type'] : [$node['@type']];
            if (array_intersect(['Book', 'Article', 'BlogPosting'], $type)) {
                unset($data[$key]);
            }
        }
    }

    $book = ht_build_webnovel_schema(get_queried_object_id());
    if ($book) { $data['webnovel_book'] = $book; }

    return $data;
}, 20, 2);

/* ---------------------------------------------------------------------------
 * Fallback: if Rank Math isn't handling JSON-LD, print our own <script>.
 * ------------------------------------------------------------------------- */
add_action('wp_head', function () {
    if (!is_singular('webnovel')) { return; }
    if (class_exists('RankMath') || defined('RANK_MATH_VERSION')) { return; }

    $book = ht_build_webnovel_schema(get_queried_object_id());
    if (!$book) { return; }

    $graph = [
        '@context' => 'https://schema.org',
        '@graph'   => [$book],
    ];
    echo "\n<script type=\"application/ld+json\">"
        . wp_json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        . "</script>\n";
}, 20);
