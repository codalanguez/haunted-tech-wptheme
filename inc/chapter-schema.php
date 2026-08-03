<?php
/**
 * Haunted Tech — chapter-page JSON-LD enrichment.
 *
 * Investigation (2026-08-03): Rank Math already auto-applies Article schema
 * to the `chapter` CPT (rank-math-options-titles: pt_chapter_default_rich_snippet
 * = "article"), verified live in the browser DOM on
 * /chapter/custodian-the-first-sin/. So Article schema itself was never
 * missing. What's missing is the one property that actually matters for a
 * chapter: `isPartOf` pointing at the parent webnovel, not the page's own
 * WebPage node.
 *
 * First attempt patched Rank Math's own Article node's `isPartOf` in place
 * via the `rank_math/json_ld` filter. Deployed, cache-busted, verified live:
 * it did not stick — `position` (a property Rank Math doesn't know about)
 * came through fine, but `isPartOf` kept reverting to the WebPage @id. That
 * points at Rank Math re-asserting isPartOf on its own Article/BlogPosting
 * node in a later, internal step the `rank_math/json_ld` filter can't reach.
 * book-schema.php sidesteps the identical problem for Book pages by fully
 * removing Rank Math's node and inserting a fresh one under a new @id rather
 * than editing Rank Math's in place — so this does the same: strip Rank
 * Math's Article node and insert our own (same essential fields, correct
 * isPartOf + position), same as the Book pattern.
 *
 * Same integration pattern as inc/book-schema.php otherwise: join Rank
 * Math's existing @graph via the `rank_math/json_ld` filter (no second
 * <script>), with a wp_head fallback if Rank Math is ever inactive.
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Build the complete Article node for a chapter post.
 *
 * @param int $post_id
 * @return array|null
 */
function ht_build_chapter_schema($post_id) {
    $post_id = (int) $post_id;
    if (!$post_id || get_post_type($post_id) !== 'chapter') { return null; }

    $permalink = get_permalink($post_id);
    $home      = home_url('/');

    $article = [
        '@type'           => 'Article',
        '@id'             => $permalink . '#chapterArticle',
        'headline'        => wp_strip_all_tags(get_the_title($post_id)),
        'name'            => wp_strip_all_tags(get_the_title($post_id)),
        'description'     => wp_strip_all_tags(get_the_excerpt($post_id)),
        'datePublished'   => get_the_date('c', $post_id),
        'dateModified'    => get_the_modified_date('c', $post_id),
        'url'             => $permalink,
        'inLanguage'      => 'en-US',
        'author'          => ['@id' => $home . '#person'],
        'publisher'       => ['@id' => $home . '#person'],
        'mainEntityOfPage' => ['@id' => $permalink . '#webpage'],
    ];

    $webnovel_id = (int) get_post_meta($post_id, 'webnovel', true);
    if ($webnovel_id && get_post_type($webnovel_id) === 'webnovel') {
        $article['isPartOf'] = [
            '@type' => 'Book',
            '@id'   => get_permalink($webnovel_id) . '#book',
            'name'  => wp_strip_all_tags(get_the_title($webnovel_id)),
            'url'   => get_permalink($webnovel_id),
        ];
    }

    $chapter_number = get_post_meta($post_id, 'chapter_number', true);
    if ($chapter_number !== '' && $chapter_number !== null) {
        $article['position'] = (int) $chapter_number;
    }

    return apply_filters('ht_chapter_schema', $article, $post_id);
}

/* ---------------------------------------------------------------------------
 * Preferred path: strip Rank Math's own Article/BlogPosting node for this
 * page and insert ours instead. Patching isPartOf on Rank Math's node in
 * place (the first attempt) didn't survive — Rank Math reasserts isPartOf
 * on its own Article node after the json_ld filter chain runs. A fresh node
 * under a different @id isn't something that later step recognizes as
 * "its" node, so it's left alone — same reason book-schema.php replaces
 * rather than patches.
 * ------------------------------------------------------------------------- */
add_filter('rank_math/json_ld', function ($data, $jsonld) {
    if (!is_singular('chapter')) { return $data; }

    foreach ((array) $data as $key => $node) {
        if (is_array($node) && isset($node['@type'])) {
            $type = is_array($node['@type']) ? $node['@type'] : [$node['@type']];
            if (array_intersect(['Article', 'BlogPosting'], $type)) {
                unset($data[$key]);
            }
        }
    }

    $article = ht_build_chapter_schema(get_queried_object_id());
    if ($article) { $data['chapter_article'] = $article; }

    return $data;
}, 20, 2);

/* ---------------------------------------------------------------------------
 * Fallback: if Rank Math isn't handling JSON-LD, print a minimal Article
 * of our own so a chapter page is never left with zero structured data.
 * ------------------------------------------------------------------------- */
add_action('wp_head', function () {
    if (!is_singular('chapter')) { return; }
    if (class_exists('RankMath') || defined('RANK_MATH_VERSION')) { return; }

    $post_id = get_queried_object_id();
    if (!$post_id) { return; }

    $article = ht_build_chapter_schema($post_id);
    if (!$article) { return; }

    $graph = [
        '@context' => 'https://schema.org',
        '@graph'   => [$article],
    ];
    echo "\n<script type=\"application/ld+json\">"
        . wp_json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        . "</script>\n";
}, 20);
