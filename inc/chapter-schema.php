<?php
/**
 * Haunted Tech — chapter-page JSON-LD enrichment.
 *
 * Investigation (2026-08-03): Rank Math already auto-applies Article schema
 * to the `chapter` CPT (rank-math-options-titles: pt_chapter_default_rich_snippet
 * = "article"), verified live in the browser DOM on
 * /chapter/custodian-the-first-sin/. So Article schema itself was never
 * missing. What's missing is the one property that actually matters for a
 * chapter: `isPartOf` pointing at the parent webnovel. Rank Math's default
 * wires Article.isPartOf to the page's own WebPage node instead — accurate,
 * but not useful. This file overwrites that with a reference to the parent
 * webnovel (via the `webnovel` ACF field, a plain post ID), and adds
 * `position` from `chapter_number` since it's sitting right there.
 *
 * Same integration pattern as inc/book-schema.php: join Rank Math's existing
 * @graph via the `rank_math/json_ld` filter (no second <script>), with a
 * wp_head fallback if Rank Math is ever inactive.
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Build the isPartOf + position enrichment for a chapter's Article node.
 *
 * @param int $post_id
 * @return array  ['isPartOf' => array|null, 'position' => int|null]
 */
function ht_build_chapter_enrichment($post_id) {
    $post_id     = (int) $post_id;
    $webnovel_id = (int) get_post_meta($post_id, 'webnovel', true);
    $isPartOf    = null;

    if ($webnovel_id && get_post_type($webnovel_id) === 'webnovel') {
        $isPartOf = [
            '@type' => 'Book',
            '@id'   => get_permalink($webnovel_id) . '#book',
            'name'  => wp_strip_all_tags(get_the_title($webnovel_id)),
            'url'   => get_permalink($webnovel_id),
        ];
    }

    $chapter_number = get_post_meta($post_id, 'chapter_number', true);
    $position = ($chapter_number !== '' && $chapter_number !== null) ? (int) $chapter_number : null;

    return ['isPartOf' => $isPartOf, 'position' => $position];
}

/* ---------------------------------------------------------------------------
 * Preferred path: enrich the Article node Rank Math already put in the graph.
 * ------------------------------------------------------------------------- */
add_filter('rank_math/json_ld', function ($data, $jsonld) {
    if (!is_singular('chapter')) { return $data; }

    $enrich = ht_build_chapter_enrichment(get_queried_object_id());
    if (!$enrich['isPartOf'] && !$enrich['position']) { return $data; }

    foreach ($data as $key => $node) {
        if (is_array($node) && isset($node['@type'])) {
            $type = is_array($node['@type']) ? $node['@type'] : [$node['@type']];
            if (in_array('Article', $type, true)) {
                if ($enrich['isPartOf']) { $data[$key]['isPartOf'] = $enrich['isPartOf']; }
                if ($enrich['position']) { $data[$key]['position'] = $enrich['position']; }
            }
        }
    }

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

    $enrich = ht_build_chapter_enrichment($post_id);

    $article = [
        '@type'         => 'Article',
        'headline'      => wp_strip_all_tags(get_the_title($post_id)),
        'description'   => wp_strip_all_tags(get_the_excerpt($post_id)),
        'datePublished' => get_the_date('c', $post_id),
        'dateModified'  => get_the_modified_date('c', $post_id),
        'url'           => get_permalink($post_id),
        'inLanguage'    => 'en-US',
    ];
    if ($enrich['isPartOf']) { $article['isPartOf'] = $enrich['isPartOf']; }
    if ($enrich['position']) { $article['position'] = $enrich['position']; }

    $graph = [
        '@context' => 'https://schema.org',
        '@graph'   => [$article],
    ];
    echo "\n<script type=\"application/ld+json\">"
        . wp_json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        . "</script>\n";
}, 20);
