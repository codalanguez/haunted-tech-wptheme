<?php
// CLI-only, one-time cache refresh for the links signup release.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__, 4) . '/wp-load.php';
if (get_option('ht_links_signup_cache_v1')) { echo "Links signup cache already refreshed\n"; exit; }
$page = get_page_by_path('links', OBJECT, 'page');
if (!$page) { fwrite(STDERR, "Links page missing\n"); exit(1); }
if (!is_callable(['\TenWebOptimizer\OptimizerAdmin', 'clear_cache'])) {
    fwrite(STDERR, "10Web cache API unavailable; clear the page cache manually\n"); exit(1);
}
clean_post_cache($page->ID);
// Use the provider's public API; preserve critical CSS and all site settings.
if (!\TenWebOptimizer\OptimizerAdmin::clear_cache(false, true)) {
    fwrite(STDERR, "10Web reported cache refresh failure\n"); exit(1);
}
update_option('ht_links_signup_cache_v1', gmdate('c'), false);
echo "10Web cache refreshed for links signup release\n";
