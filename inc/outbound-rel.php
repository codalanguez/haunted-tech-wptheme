<?php
/**
 * Haunted Tech — rel attributes for commercial outbound links in post content.
 *
 * The theme already sets rel on the links it renders itself: the nav walker
 * (Haunted_Tech_Social_Walker::rel_for), the book page buy buttons, the
 * chapter CTAs. Links typed into the block editor bypass all of that, so an
 * affiliate or referral link written into a post body has gone out bare.
 *
 * Why this is a filter and not a content edit (2026-08-17, and again 08-23):
 * post 1535's Lantern referral link was corrected by hand twice — once as a
 * styled .cta button carrying rel="sponsored nofollow noopener" — and both
 * times a later save in the block editor restored an older revision and
 * dropped it. A hand-edit cannot survive an editor that holds a stale copy.
 * Applying rel at render time can, and it covers every future post without
 * anyone having to remember.
 *
 * Scope, deliberately narrow:
 *   - Only `the_content`. Comments and widgets are untouched.
 *   - Only links whose href matches the commercial list below — Pretty Link
 *     /go/ slugs and known retailer hosts. Everything else, including plain
 *     outbound links to other writers, stays followable.
 *   - An <a> that already carries a rel is left exactly as authored. This
 *     filter fills gaps; it never overrides an explicit choice.
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

/**
 * The rel value a given outbound URL should carry.
 *
 * Single source of truth for the commercial needle list — the nav walker
 * delegates here too, so the menu and post content cannot drift apart.
 *
 * @param string $url
 * @param string $label  Optional link text, also matched against.
 * @return string        Space-separated rel value.
 */
function ht_outbound_rel_for($url, $label = '') {
    $s = strtolower(($url ?: '') . ' ' . ($label ?: ''));

    /**
     * Substrings that mark a link as commercial or compensated: retailers,
     * tip jars, and the Pretty Link slugs that front them.
     *
     * Both Lantern slugs belong here, and it is worth saying why, because
     * only one of them looks like a referral from its name. Resolved
     * 2026-08-23:
     *
     *   /go/joinlantern -> lanternserials.com/?ref=9726680A2E
     *   /go/lantern     -> lanternserials.com/author/coda-languez?ref=9726680A2E
     *
     * Both carry the referral code, and Lantern counts sign-ups through it
     * and features the week's top referrer — compensated placement, the
     * same class as an affiliate link. **Resolve a /go/ slug before
     * deciding it is harmless; the name does not tell you.**
     *
     * Matched on the /go/ SLUGS, not the lanternserials.com host: a plain
     * link to one of Coda's own stories there (/story/custodian-...) has no
     * referral code and should stay followable. Marking the whole host
     * sponsored would mislabel it and shed link equity for nothing.
     *
     * ⛔ CORRECTION 2026-09-06 — the premise above was half wrong.
     * "A plain lanternserials.com link has no ref code" stopped being true
     * on 2026-09-03, when The First Sky chapters began carrying
     * `lanternserials.com/story/...?ref=9726680A2E` directly, with no /go/
     * slug in front of it. The *distinction* still holds — post 1568 links
     * the same story with no ref code and correctly stays followable — so
     * the answer is neither "match the host" nor "match the slug".
     *
     * **Match the referral code.** A /go/ slug, a bare host and a query
     * parameter are three shapes of one commercial relationship; the ref
     * check below catches all three, and the needle list stays for
     * retailers that use no code of their own.
     */
    $commercial = apply_filters('ht_commercial_link_needles', [
        'amazon', 'audible', 'barnes', 'noble', '/go/bn', 'kobo', 'apple',
        'bookshop', 'gumroad', 'redbubble', 'etsy', 'ko-fi', 'kofi',
        '/go/joinlantern', '/go/lantern',
    ]);

    foreach ($commercial as $needle) {
        if (strpos($s, $needle) !== false) {
            return 'sponsored nofollow noopener';
        }
    }

    /**
     * Referral / affiliate codes carried as a query parameter, whatever the
     * host. Matched on the parameter NAME, so a new partner needs no code
     * added here. `ref=` is Lantern's; the rest are the usual suspects.
     */
    $ref_params = apply_filters('ht_referral_query_params', [
        'ref', 'referral', 'aff', 'affiliate', 'tag', 'utm_affiliate',
    ]);
    $query = (string) wp_parse_url((string) $url, PHP_URL_QUERY);
    if ($query !== '') {
        parse_str($query, $args);
        foreach ($ref_params as $p) {
            if (!empty($args[$p])) {
                return 'sponsored nofollow noopener';
            }
        }
    }

    return 'noopener';
}

/**
 * Add rel to bare commercial links in an arbitrary block of HTML.
 *
 * Extracted from the the_content filter so it can also be applied to the
 * chapter fields that never pass through it — `authors_note` is rendered
 * straight out of ACF, and the `external_read_url` CTAs are built in
 * inc/render-callbacks.php. Those were emitting referral links with a
 * hardcoded rel="noopener" until 2026-09-06.
 *
 * Leaves alone: anything already carrying a rel, and anything not
 * commercial by ht_outbound_rel_for().
 *
 * @param string $html
 * @return string
 */
function ht_add_outbound_rel($html) {
    if (!is_string($html) || $html === '' || stripos($html, '<a ') === false) {
        return $html;
    }

    return preg_replace_callback(
        '#<a\s+([^>]*?)href\s*=\s*(["\'])(.*?)\2([^>]*?)>#i',
        function ($m) {
            $before = $m[1];
            $quote  = $m[2];
            $href   = $m[3];
            $after  = $m[4];

            // Respect an explicitly authored rel — this fills gaps only.
            if (preg_match('#\srel\s*=#i', $before . ' ' . $after)) {
                return $m[0];
            }

            $rel = ht_outbound_rel_for(html_entity_decode($href, ENT_QUOTES));
            if ($rel !== 'sponsored nofollow noopener') {
                return $m[0]; // Not commercial: leave it followable and untouched.
            }

            return '<a ' . trim($before) . ' href=' . $quote . $href . $quote
                 . rtrim($after) . ' rel="' . $rel . '">';
        },
        $html
    );
}

/** Post content — the original surface this module covered. */
add_filter('the_content', function ($content) {
    if (is_admin()) { return $content; }
    return ht_add_outbound_rel($content);
}, 20);

/**
 * The chapter author's note, rendered straight out of ACF in
 * inc/render-callbacks.php and therefore never seen by `the_content`.
 */
add_filter('ht_chapter_authors_note_html', 'ht_add_outbound_rel', 20);
