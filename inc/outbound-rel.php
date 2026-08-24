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
     * tip jars, and the Pretty Link slugs that front them. `joinlantern` is
     * a referral link — Lantern counts sign-ups through it and features the
     * week's top referrer — which is compensated placement, the same class
     * as an affiliate link.
     *
     * Matched on the referral SLUG, not the lanternserials.com host: a plain
     * link to one of Coda's own stories there (/story/custodian-...) is
     * ordinary editorial linking, carries no referral code, and should stay
     * followable. Marking a whole host sponsored would mislabel it and shed
     * link equity for nothing.
     */
    $commercial = apply_filters('ht_commercial_link_needles', [
        'amazon', 'audible', 'barnes', 'noble', '/go/bn', 'kobo', 'apple',
        'bookshop', 'gumroad', 'redbubble', 'etsy', 'ko-fi', 'kofi',
        'joinlantern',
    ]);

    foreach ($commercial as $needle) {
        if (strpos($s, $needle) !== false) {
            return 'sponsored nofollow noopener';
        }
    }

    return 'noopener';
}

/**
 * Add rel to bare commercial links in post content.
 *
 * Leaves alone: anything already carrying a rel, and anything that isn't
 * commercial by the list above.
 */
add_filter('the_content', function ($content) {
    if (is_admin() || !is_string($content) || $content === '' || stripos($content, '<a ') === false) {
        return $content;
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

            $rel = ht_outbound_rel_for($href);
            if ($rel !== 'sponsored nofollow noopener') {
                return $m[0]; // Not commercial: leave it followable and untouched.
            }

            return '<a ' . trim($before) . ' href=' . $quote . $href . $quote
                 . rtrim($after) . ' rel="' . $rel . '">';
        },
        $content
    );
}, 20);
