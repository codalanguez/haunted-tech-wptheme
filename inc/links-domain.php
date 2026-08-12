<?php
/**
 * links-domain.php — serve the /links/ bio page on its own subdomain.
 *
 * The bio page is the one URL that gets typed into profile fields by hand, so
 * it wants to be short: links.codalanguez.com rather than
 * codalanguez.com/links/. The subdomain is a plain alias of the same
 * WordPress install (same docroot, same database) — there is no multisite and
 * no second copy of anything.
 *
 * That takes three things, and only the third one lives here:
 *
 *   1. DNS — a CNAME for `links` pointing at the site host.
 *   2. The host panel — add the subdomain to the site so its vhost answers
 *      and a TLS certificate is issued for it.
 *   3. This file — WordPress otherwise 301s every request on an unrecognised
 *      host straight back to the canonical `home_url()`, so the subdomain
 *      would just bounce to codalanguez.com and the vanity URL would never
 *      be seen.
 *
 * Until 1 and 2 exist, nothing below ever fires: every hook returns early
 * unless the incoming Host header actually matches. The file is inert, not
 * conditional on being "switched on".
 *
 * Deliberate asymmetries:
 *   - Only the bio page itself lives on the subdomain. Every outbound link it
 *     renders still goes to codalanguez.com, because home_url() is left
 *     alone — the subdomain is a doorway, not a mirror.
 *   - Asset URLs, though, ARE rewritten to the subdomain (see below): the
 *     self-hosted woff2 fonts are subject to CORS, and cross-origin font
 *     loads fail without an Access-Control-Allow-Origin header we do not
 *     control. Same docroot, so /wp-content/... resolves identically on
 *     either host.
 *   - Rank Math still emits the canonical as https://codalanguez.com/links/,
 *     because the post being rendered is genuinely that page. The subdomain
 *     therefore never competes with it in search.
 *
 * Note: auth cookies are set host-only for the canonical domain, so an
 * editor browsing the subdomain is logged out there. That is fine for a
 * public bio page — edit it at codalanguez.com/links/.
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

/**
 * The vanity host. Filterable so a staging clone can point somewhere else
 * (or switch it off entirely by returning an empty string).
 */
function haunted_tech_links_host() {
    return (string) apply_filters('haunted_tech_links_host', 'links.codalanguez.com');
}

/** The slug of the page the vanity host serves. */
function haunted_tech_links_slug() {
    return (string) apply_filters('haunted_tech_links_slug', 'links');
}

/** Is *this* request arriving on the vanity host? */
function haunted_tech_is_links_host() {
    static $is = null;
    if ($is !== null) {
        return $is;
    }

    $configured = strtolower(haunted_tech_links_host());
    $incoming   = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';

    if ($configured === '' || $incoming === '') {
        return $is = false;
    }

    // Host headers can carry a port; the configured value never does.
    $incoming = preg_replace('/:\d+$/', '', $incoming);

    return $is = ($incoming === $configured);
}

/**
 * Point the request at the bio page.
 *
 * The vanity host has exactly one page. Anything else typed against it is
 * almost certainly a path someone assumed was mirrored, so hand it back to
 * the canonical domain rather than 404ing on a hostname that only ever
 * meant one thing.
 */
add_action('parse_request', 'haunted_tech_links_host_route');
function haunted_tech_links_host_route($wp) {
    if (is_admin() || !haunted_tech_is_links_host()) {
        return;
    }

    $path = (string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path = '/' . trim($path, '/');

    // Leave WordPress's own plumbing alone — feeds, REST, cron, the login
    // form. They are reached by full path and must keep working.
    if (strpos($path, '/wp-') === 0 || strpos($path, '/xmlrpc') === 0) {
        return;
    }

    if ($path !== '/' && $path !== '/' . haunted_tech_links_slug()) {
        // Keep a trailing slash off anything that looks like a file:
        // /robots.txt/ is not /robots.txt.
        $suffix = preg_match('#\.[a-z0-9]{2,5}$#i', $path) ? '' : '/';
        $target = home_url($path . $suffix);
        $query  = wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
        if ($query) {
            $target .= '?' . $query;
        }
        haunted_tech_links_host_own_redirect(true);
        wp_safe_redirect($target, 301);
        exit;
    }

    $page = get_page_by_path(haunted_tech_links_slug());
    if (!$page) {
        return; // page renamed or missing — fall through to a normal 404
    }

    $wp->query_vars   = ['page_id' => $page->ID];
    $wp->matched_rule = '';
    $wp->matched_query = '';
    $wp->did_permalink = false;
}

/**
 * Stop the canonical redirect from undoing all of the above.
 *
 * redirect_canonical() compares the request against get_permalink(), sees a
 * different host, and 301s to codalanguez.com/links/ — which is correct
 * behaviour everywhere except here.
 */
add_filter('redirect_canonical', 'haunted_tech_links_host_no_canonical_redirect', 10, 2);
function haunted_tech_links_host_no_canonical_redirect($redirect_url, $requested_url) {
    return haunted_tech_is_links_host() ? false : $redirect_url;
}

/**
 * Marks the one redirect this file legitimately issues (a stray path handed
 * back to the canonical domain) so the guard below does not cancel it.
 * Call with true to arm; call with no argument to read.
 */
function haunted_tech_links_host_own_redirect($set = null) {
    static $own = false;
    if ($set !== null) {
        $own = (bool) $set;
    }
    return $own;
}

/**
 * Cancel anything else that tries to bounce this host to the canonical domain.
 *
 * redirect_canonical() is not the only thing with an opinion about which
 * hostname the site answers on. The host's own optimizer plugin renders the
 * page in full and *then* attaches a 302 to the same path on the primary
 * domain — the observed symptom was a complete, correct bio page arriving
 * with a Location header stapled to it, so the browser threw the page away
 * and followed the header to the homepage. It only did this to responses it
 * had optimized; a HEAD request, which it skips, came back a clean 200.
 *
 * Two interception points because the mechanism is somebody else's code and
 * may change:
 *
 *   1. The wp_redirect filter, which catches it if the redirect goes through
 *      WordPress. Returning false makes wp_redirect() a no-op. That is only
 *      safe because this particular caller does not exit afterwards — the
 *      page is already rendered, which is how we know.
 *   2. A shutdown pass that strips a Location header still standing at the
 *      end of the request. Belt and braces; if the header is set from inside
 *      an output-buffer callback this will be too early to catch it.
 *
 * Both are scoped to the vanity host and to redirects aimed at the canonical
 * domain, so nothing else on the site is affected. If the host ever fixes
 * alias handling properly, this becomes dead weight rather than a hazard.
 */
add_filter('wp_redirect', 'haunted_tech_links_host_block_bounce', PHP_INT_MAX, 2);
function haunted_tech_links_host_block_bounce($location, $status) {
    if (!haunted_tech_is_links_host() || haunted_tech_links_host_own_redirect()) {
        return $location;
    }
    return haunted_tech_links_host_targets_canonical($location) ? false : $location;
}

add_action('shutdown', 'haunted_tech_links_host_strip_bounce', PHP_INT_MAX);
function haunted_tech_links_host_strip_bounce() {
    if (!haunted_tech_is_links_host() || haunted_tech_links_host_own_redirect() || headers_sent()) {
        return;
    }
    foreach (headers_list() as $header) {
        if (stripos($header, 'location:') !== 0) {
            continue;
        }
        if (haunted_tech_links_host_targets_canonical(trim(substr($header, 9)))) {
            header_remove('Location');
            status_header(200);
        }
        return;
    }
}

/** Does this redirect target point at the canonical domain? */
function haunted_tech_links_host_targets_canonical($location) {
    if (!is_string($location) || $location === '') {
        return false;
    }
    $to   = strtolower((string) wp_parse_url($location, PHP_URL_HOST));
    $home = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));

    return $to !== '' && $home !== '' && $to === $home;
}

/**
 * Serve assets from whichever host the document is on.
 *
 * Fonts are the reason (CORS); everything else comes along because a page
 * that mixes two hosts for its own static files pays an extra connection
 * setup for no benefit.
 */
foreach (['content_url', 'includes_url', 'plugins_url', 'script_loader_src', 'style_loader_src'] as $ht_links_url_filter) {
    add_filter($ht_links_url_filter, 'haunted_tech_links_host_asset_url');
}
unset($ht_links_url_filter);

function haunted_tech_links_host_asset_url($url) {
    if (!is_string($url) || $url === '' || !haunted_tech_is_links_host()) {
        return $url;
    }

    $canonical = wp_parse_url(home_url('/'), PHP_URL_HOST);
    if (!$canonical) {
        return $url;
    }

    $host = wp_parse_url($url, PHP_URL_HOST);
    if (strtolower((string) $host) !== strtolower($canonical)) {
        return $url; // third-party or already local
    }

    return str_replace('://' . $host, '://' . haunted_tech_links_host(), $url);
}
