<?php
// CLI-only, one-time correction of the historical launch article.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__, 4) . '/wp-load.php';
if (get_option('ht_mechape_launch_article_20261113_v1')) { echo "Launch article already updated\n"; exit; }
$post = get_page_by_path('mechape-local-private-llm-studio', OBJECT, 'post');
if (!$post || (int) $post->ID !== 476) { fwrite(STDERR, "Expected launch article missing\n"); exit(1); }
$blocks = parse_blocks($post->post_content);
$note = '<!-- wp:paragraph --><p><strong>Update, November 13, 2026:</strong> MechApe now has two editions. The full Windows desktop app is <strong>US$29 once</strong>; version <strong>2.0.2</strong> is available as a signed installer, with updates within 2.x included. <strong>MechApe for Obsidian</strong> is the separate free, open-source community edition. Cloud/provider API charges are separate. The original launch notes below are historical; this update does not revoke any MIT permissions granted for earlier releases. For current downloads, requirements, and pricing, visit the <a href="https://codalanguez.com/mechape/">MechApe page</a>.</p><!-- /wp:paragraph -->';
$quick = '<!-- wp:paragraph --><p>For Windows, get the signed desktop installer from the <a href="https://codalanguez.com/mechape/">MechApe page</a>, extract the ZIP, and follow <code>START-HERE.txt</code>. No source checkout or Node.js setup is needed for the installer. For the free edition, choose MechApe for Obsidian on the same page.</p><!-- /wp:paragraph -->';
$output = parse_blocks($note);
$in_quick = false;
$quick_found = false;
$lab_found = false;
$source_found = false;
foreach ($blocks as $block) {
    $html = $block['innerHTML'] ?? '';
    if ($block['blockName'] === 'core/heading' && strpos($html, 'Quick start') !== false) {
        $output[] = $block;
        $output = array_merge($output, parse_blocks($quick));
        $in_quick = true;
        $quick_found = true;
        continue;
    }
    if ($in_quick) {
        if ($block['blockName'] !== 'core/image') { continue; }
        $in_quick = false;
    }
    $serialized = serialize_block($block);
    if (strpos($serialized, 'go straight to the source') !== false) {
        $output = array_merge($output, parse_blocks('<!-- wp:paragraph --><p>You can tour the current editions and find their downloads on the <a href="https://codalanguez.com/mechape/">MechApe page</a>.</p><!-- /wp:paragraph -->'));
        $source_found = true;
        continue;
    }
    $old = 'Everything in the Lab is free, open source, and slightly haunted.';
    if (strpos($serialized, $old) !== false) {
        $serialized = str_replace($old, 'The Lab includes both free community tools and paid desktop software. Still slightly haunted.', $serialized);
        $lab_found = true;
    }
    if ($block['blockName'] === 'core/buttons') {
        $serialized = str_replace('https://github.com/codalanguez/MechApe', 'https://codalanguez.com/mechape/', $serialized);
        $serialized = str_replace('Get it on GitHub', 'Compare editions &amp; downloads', $serialized);
    }
    $output = array_merge($output, parse_blocks($serialized));
}
if (!$quick_found || $in_quick || !$lab_found || !$source_found) { fwrite(STDERR, "Article structure changed; no update made\n"); exit(1); }
$content = serialize_blocks($output);
foreach (['git clone', 'Node.js 18+', 'Start MechApe Desktop.cmd', 'Everything in the Lab is free'] as $obsolete) {
    if (strpos($content, $obsolete) !== false) { fwrite(STDERR, "Obsolete instructions remain\n"); exit(1); }
}
wp_save_post_revision($post->ID);
kses_remove_filters();
$result = wp_update_post(wp_slash(['ID' => $post->ID, 'post_content' => $content]), true);
if (is_wp_error($result)) { fwrite(STDERR, $result->get_error_message()); exit(1); }
clean_post_cache($post->ID);
if (get_post($post->ID)->post_content !== $content) { fwrite(STDERR, "Stored content verification failed\n"); exit(1); }
update_option('ht_mechape_launch_article_20261113_v1', gmdate('c'), false);
if (is_callable(["\\TenWebOptimizer\\OptimizerAdmin", "clear_cache"])) {
    \TenWebOptimizer\OptimizerAdmin::clear_cache(false, true);
}
echo "Updated historical MechApe launch article 476; original launch context and images preserved\n";
