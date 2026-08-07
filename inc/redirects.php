<?php
/**
 * redirects.php — permanent redirects for paths this site has retired.
 *
 * WordPress already remembers a changed slug in the `_wp_old_slug` postmeta,
 * but `wp_old_slug_redirect()` only ever fires for *posts*: it bails unless
 * `get_query_var('name')` is set, and a page request populates `pagename`
 * instead. So a renamed page falls through to `redirect_guess_404_permalink()`,
 * which matches on a `post_name LIKE 'slug%'` prefix and happily lands the
 * visitor on whatever unrelated post happens to sort first. Renaming
 * /monkii/ to /mechape/ sent every old link to the 2026 announcement post,
 * which is how this came up.
 *
 * The map below is therefore explicit rather than clever. Exact path match,
 * 301, query string preserved.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retired path => current path. Both sides are site-root-relative and are
 * compared without a trailing slash, so "/monkii" and "/monkii/" both match.
 */
function haunted_tech_retired_paths() {
    return [
        // renamed 2026-08, when MONKII became MechApe
        '/monkii'                          => '/mechape/',
        '/monkii-local-private-llm-studio' => '/mechape-local-private-llm-studio/',
    ];
}

/**
 * Run before redirect_canonical() (which is registered at the default
 * priority 10 and carries the 404 guesser) so an exact match here always
 * wins over a fuzzy prefix guess.
 */
add_action('template_redirect', 'haunted_tech_redirect_retired_paths', 1);
function haunted_tech_redirect_retired_paths() {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $request = wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (!is_string($request) || $request === '') {
        return;
    }

    // normalise: strip any subdirectory install prefix, then the trailing slash
    $home_path = wp_parse_url(home_url('/'), PHP_URL_PATH) ?: '/';
    if ($home_path !== '/' && strpos($request, $home_path) === 0) {
        $request = substr($request, strlen($home_path) - 1);
    }
    $path = '/' . trim($request, '/');

    $map = haunted_tech_retired_paths();
    if (!isset($map[$path])) {
        return;
    }

    $target = home_url($map[$path]);

    // carry the query string across so campaign tags and cache-busters survive
    $query = wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
    if ($query) {
        $target .= (strpos($target, '?') === false ? '?' : '&') . $query;
    }

    wp_safe_redirect($target, 301);
    exit;
}
