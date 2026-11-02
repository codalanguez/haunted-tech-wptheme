<?php
// Idempotent, CLI-only release copy migration. Preserve all unrelated page content.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__, 4) . '/wp-load.php';
if (get_option('ht_mechape_sales_open_v1')) { echo "MechApe sales migration already complete\n"; exit; }
$page = get_page_by_path('mechape', OBJECT, 'page');
if (!$page) { fwrite(STDERR, "MechApe page missing\n"); exit(1); }
$changes = json_decode(file_get_contents(__DIR__ . '/mechape-sales-copy.json'), true);
if (!is_array($changes)) { fwrite(STDERR, "Invalid release copy\n"); exit(1); }
$content = $page->post_content;
foreach ($changes as $old => $new) {
    if (strpos($content, $old) !== false) {
        $content = str_replace($old, $new, $content);
    } elseif (strpos($content, $new) === false) {
        fwrite(STDERR, "Page changed; expected release text missing: " . substr($old, 0, 90) . "\n"); exit(1);
    }
}
$excerpt = 'MechApe Desktop 2.0.0 for Windows is available now: US$29 once for version 2.x, one active device, and a 30-day refund period. The separate MechApe for Obsidian plugin remains free.';
if ($content !== $page->post_content || $page->post_excerpt !== $excerpt) {
    wp_save_post_revision($page->ID);
    // Preserve the existing trusted HTML block's inline styles/schema in CLI.
    kses_remove_filters();
    $result = wp_update_post(wp_slash(array('ID' => $page->ID, 'post_content' => $content, 'post_excerpt' => $excerpt)), true);
    if (is_wp_error($result)) { fwrite(STDERR, $result->get_error_message()); exit(1); }
    clean_post_cache($page->ID);
}
$stored = get_post($page->ID)->post_content;
if (strpos($stored, 'https://payhip.com/b/OAadS') === false || strpos($stored, 'Desktop sales — not open yet') !== false) { exit(1); }
echo "MechApe page updated and purchase link verified; page ID " . $page->ID . "\n";
update_option('ht_mechape_sales_open_v1', gmdate('c'), false);
