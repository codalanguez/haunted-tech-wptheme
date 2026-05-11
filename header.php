<?php
/**
 * Site header — includes <head>, opens <body>, renders the
 * social bar (top), header (logo + nav + subscribe CTA),
 * and the global CRT-band + static-burst overlays.
 *
 * @package HauntedTech
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="<?php echo esc_url(haunted_tech_logo_url()); ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="crt-band"></div>
<div class="static-burst"></div>

<!-- ===== BLOCK: SOCIAL BAR (top) ===== -->
<div class="social-bar block-social-bar">
  <ul class="social-list">
    <?php
    /*
     * Renders a WP menu assigned to the 'social' location if one exists.
     * Otherwise falls back to a hard-coded set so the theme works out-of-the-box.
     * Each menu item should have its URL pointing to the platform; the icon is
     * matched against the URL host below.
     */
    $social_menu = has_nav_menu('social');
    if ($social_menu) {
        wp_nav_menu([
            'theme_location' => 'social',
            'container'      => false,
            'items_wrap'     => '%3$s',
            'walker'         => new Haunted_Tech_Social_Walker(),
            'fallback_cb'    => false,
        ]);
    } else {
        $defaults = [
            'Patreon'   => ['url' => '#', 'icon' => 'fa-brands fa-patreon'],
            'Ream'      => ['url' => '#', 'icon' => 'fa-solid fa-book-open-reader'],
            'Substack'  => ['url' => '#', 'icon' => 'fa-solid fa-envelope-open-text'],
            'Discord'   => ['url' => '#', 'icon' => 'fa-brands fa-discord'],
            'Bluesky'   => ['url' => '#', 'icon' => 'fa-brands fa-bluesky'],
            'Instagram' => ['url' => '#', 'icon' => 'fa-brands fa-instagram'],
            'TikTok'    => ['url' => '#', 'icon' => 'fa-brands fa-tiktok'],
            'Goodreads' => ['url' => '#', 'icon' => 'fa-brands fa-goodreads-g'],
            'Amazon'    => ['url' => '#', 'icon' => 'fa-brands fa-amazon'],
            'Threads'   => ['url' => '#', 'icon' => 'fa-brands fa-threads'],
            'X'         => ['url' => '#', 'icon' => 'fa-brands fa-x-twitter'],
        ];
        foreach ($defaults as $label => $data) {
            printf(
                '<li><a href="%s" data-label="%s" aria-label="%s"><i class="%s"></i></a></li>',
                esc_url($data['url']),
                esc_attr($label),
                esc_attr($label),
                esc_attr($data['icon'])
            );
        }
    }
    ?>
  </ul>
</div>
<!-- ===== /BLOCK: SOCIAL BAR ===== -->

<!-- ===== BLOCK: HEADER ===== -->
<header class="block-header">
  <div class="header-inner">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="logo" aria-label="<?php bloginfo('name'); ?>">
      <img src="<?php echo esc_url(haunted_tech_logo_url()); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?> logo">
      <span class="logo-text-wrap">
        <span class="logo-text"><?php bloginfo('name'); ?></span>
        <?php $tagline = get_bloginfo('description'); if ($tagline): ?>
          <span class="logo-tagline"><?php echo esc_html($tagline); ?></span>
        <?php endif; ?>
      </span>
    </a>
    <div class="header-right">
      <nav role="navigation" aria-label="Primary">
        <?php
        wp_nav_menu([
            'theme_location' => 'primary',
            'container'      => false,
            'fallback_cb'    => 'haunted_tech_default_primary_menu',
        ]);
        ?>
      </nav>
      <a href="#newsletter" class="header-cta">Subscribe</a>
    </div>
  </div>
</header>
<!-- ===== /BLOCK: HEADER ===== -->

<?php
/**
 * Fallback menu shown if the user hasn't configured the Primary menu location.
 */
function haunted_tech_default_primary_menu() {
    echo '<ul>';
    echo '<li><a href="' . esc_url(home_url('/#books'))      . '">Books</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#web-novels')) . '">Web Novels</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#services'))   . '">Services</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#gallery'))    . '">Gallery</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#about'))      . '" data-open-about>About</a></li>';
    echo '</ul>';
}

/**
 * Walker that renders each menu item as a Font Awesome icon based on URL.
 * Used by the social-bar block when the user assigns a menu to 'social'.
 */
if (!class_exists('Haunted_Tech_Social_Walker')) {
    class Haunted_Tech_Social_Walker extends Walker_Nav_Menu {
        public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
            $url    = $item->url ?? '#';
            $label  = $item->title ?? '';
            $icon   = self::icon_for($url);
            $output .= sprintf(
                '<li><a href="%s" data-label="%s" aria-label="%s"><i class="%s"></i></a></li>',
                esc_url($url),
                esc_attr($label),
                esc_attr($label),
                esc_attr($icon)
            );
        }
        public function end_el(&$output, $item, $depth = 0, $args = null) { /* no-op */ }
        public static function icon_for($url) {
            $host = parse_url($url, PHP_URL_HOST) ?: '';
            $map = [
                'patreon.com'   => 'fa-brands fa-patreon',
                'ream.com'      => 'fa-solid fa-book-open-reader',
                'reamstories.com'=> 'fa-solid fa-book-open-reader',
                'substack.com'  => 'fa-solid fa-envelope-open-text',
                'discord.com'   => 'fa-brands fa-discord',
                'discord.gg'    => 'fa-brands fa-discord',
                'bsky.app'      => 'fa-brands fa-bluesky',
                'instagram.com' => 'fa-brands fa-instagram',
                'tiktok.com'    => 'fa-brands fa-tiktok',
                'goodreads.com' => 'fa-brands fa-goodreads-g',
                'amazon.com'    => 'fa-brands fa-amazon',
                'threads.net'   => 'fa-brands fa-threads',
                'twitter.com'   => 'fa-brands fa-x-twitter',
                'x.com'         => 'fa-brands fa-x-twitter',
            ];
            foreach ($map as $needle => $cls) {
                if (strpos($host, $needle) !== false) return $cls;
            }
            return 'fa-solid fa-link';
        }
    }
}
?>
