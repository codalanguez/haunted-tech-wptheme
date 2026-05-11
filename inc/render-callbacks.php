<?php
/**
 * Render callbacks for the Haunted Tech custom blocks.
 *
 * Each function returns the HTML for one section of the site (the same HTML
 * that lived inline in front-page.php / single-*.php in the classic v0.1.0
 * theme). Functions are wired to dynamic blocks in inc/blocks.php so they
 * can be inserted from the Site Editor and the block editor.
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

/* ============================================================
 * SOCIAL BAR — top of page, icon-only
 * ============================================================ */
function ht_render_social_bar($attributes = []) {
    ob_start(); ?>
    <div class="social-bar block-social-bar">
      <ul class="social-list">
        <?php
        if (has_nav_menu('social')) {
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
    <?php
    return ob_get_clean();
}

/* ============================================================
 * SITE HEADER — logo + nav + subscribe CTA
 * ============================================================ */
function ht_render_site_header($attributes = []) {
    ob_start(); ?>
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
    <?php
    return ob_get_clean();
}

/* ============================================================
 * HERO SLIDER — pulls 3 most-recent hero_update posts
 * ============================================================ */
function ht_render_hero_slider($attributes = []) {
    $hero_slides = haunted_tech_get_hero_slides(3);
    ob_start(); ?>
    <div class="hero block-hero" id="hero-slider">
      <div class="hero-watermark" aria-hidden="true">
        <img src="<?php echo esc_url(haunted_tech_logo_url()); ?>" alt="">
      </div>
      <div class="hero-frame">
        <div class="deco-corner-tr"></div>
        <div class="deco-corner-bl"></div>
        <div class="deco-top"></div>
        <div class="deco-bottom"></div>

        <?php if (!empty($hero_slides)): ?>
          <?php foreach ($hero_slides as $i => $slide):
              $eyebrow   = get_field('eyebrow',      $slide->ID) ?: '';
              $first     = get_field('title_first',  $slide->ID) ?: get_the_title($slide);
              $accent    = get_field('title_accent', $slide->ID) ?: '';
              $blurb     = get_field('blurb',        $slide->ID) ?: '';
              $cta_label = get_field('cta_label',    $slide->ID) ?: 'Read More';
              $cta_link  = get_field('cta_link',     $slide->ID) ?: '#';
              $combined  = trim($first . ' ' . $accent);
          ?>
          <div class="hero-content<?php echo $i === 0 ? ' active' : ''; ?>" data-slide="<?php echo (int)$i; ?>">
            <?php if ($eyebrow): ?><div class="hero-eyebrow"><?php echo esc_html($eyebrow); ?></div><?php endif; ?>
            <h1 data-text="<?php echo esc_attr($combined); ?>"><?php echo esc_html($first); ?> <span class="gold"><?php echo esc_html($accent); ?></span></h1>
            <?php if ($blurb): ?><p><?php echo esc_html($blurb); ?></p><?php endif; ?>
            <a href="<?php echo esc_url($cta_link); ?>" class="cta"><?php echo esc_html($cta_label); ?></a>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="hero-content active" data-slide="0">
            <div class="hero-eyebrow">Welcome</div>
            <h1 data-text="HAUNTED TECH">HAUNTED <span class="gold">TECH</span></h1>
            <p>Add your first slide by going to WP Admin &rarr; Hero Updates &rarr; Add New. The three most recent updates appear here automatically.</p>
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=hero_update')); ?>" class="cta">Create First Update</a>
          </div>
        <?php endif; ?>
      </div>

      <?php if (count($hero_slides) > 1): ?>
      <div class="hero-controls" role="group" aria-label="Hero slider">
        <button class="hero-arrow prev" aria-label="Previous update">&larr;</button>
        <div class="hero-dots" role="tablist">
          <?php foreach ($hero_slides as $i => $_): ?>
            <button class="hero-dot<?php echo $i === 0 ? ' active' : ''; ?>" data-slide="<?php echo (int)$i; ?>" role="tab" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"></button>
          <?php endforeach; ?>
        </div>
        <button class="hero-arrow next" aria-label="Next update">&rarr;</button>
      </div>
      <div class="hero-progress"><div class="hero-progress-fill" id="hero-progress-fill"></div></div>
      <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * BOOKSHELF — pull all published books
 * ============================================================ */
function ht_render_bookshelf($attributes = []) {
    $limit = isset($attributes['limit']) ? (int)$attributes['limit'] : 12;
    $books = get_posts([
        'post_type'      => 'book',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    ]);
    $variants = ['oxblood', 'teal', 'obsidian', 'charcoal', 'gold'];
    ob_start(); ?>
    <section class="block-bookshelf" id="books">
      <div class="section-header">
        <h2 class="section-title">Published Works</h2>
        <div class="section-meta">The Spine Collection &mdash; Hover to Pull</div>
      </div>
      <div class="bookshelf-wrap">
        <div class="bookshelf">
          <?php if (!empty($books)): ?>
            <?php foreach ($books as $i => $book):
                $series  = get_field('series', $book->ID) ?: 'Coda Languez';
                $variant = $variants[$i % count($variants)];
                $width   = 60 + (($i * 7) % 25);
                $height  = 380 + (($i * 11) % 60);
            ?>
            <a href="<?php echo esc_url(get_permalink($book)); ?>" class="spine <?php echo esc_attr($variant); ?>" style="width:<?php echo (int)$width; ?>px; height:<?php echo (int)$height; ?>px;">
              <div class="spine-ornament">&#9670; &#9670;</div>
              <div class="spine-title"><?php echo esc_html(get_the_title($book)); ?></div>
              <div class="spine-author"><?php echo esc_html($series); ?></div>
              <div class="spine-ornament">&#9670; &#9670;</div>
            </a>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="color: var(--bone-dim); font-family: 'Cormorant Garamond', serif; font-style: italic; padding: 4rem 2rem; text-align: center;">
              No books yet. <a href="<?php echo esc_url(admin_url('post-new.php?post_type=book')); ?>" style="color: var(--gold);">Add your first book</a>.
            </div>
          <?php endif; ?>
        </div>
        <div class="shelf-base"></div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * CRT MONITOR — list webnovels with status indicators
 * ============================================================ */
function ht_render_crt_monitor($attributes = []) {
    $limit = isset($attributes['limit']) ? (int)$attributes['limit'] : 8;
    $webnovels = get_posts([
        'post_type'      => 'webnovel',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    ]);
    ob_start(); ?>
    <section class="crt-section block-crt-monitor" id="web-novels">
      <div class="section-header">
        <h2 class="section-title">Active Transmissions</h2>
        <div class="section-meta">All Serialized Web Novels &mdash; Live Channel Log</div>
      </div>
      <div class="crt-monitor">
        <div class="crt-screw-bl"></div>
        <div class="crt-screw-br"></div>
        <div class="crt-screen">
          <div class="crt-prompt"><span class="user">coda@haunted-tech</span>:<span class="path">~/webnovels</span>$ ls -la --status</div>
          <div class="crt-list">
            <?php if (!empty($webnovels)): ?>
              <?php foreach ($webnovels as $wn):
                  $status = get_field('status', $wn->ID) ?: 'ongoing';
                  $genre  = get_field('genre',  $wn->ID) ?: '';
                  $total  = get_field('total_chapters', $wn->ID);
                  $chapter_count = count(get_posts([
                      'post_type'      => 'chapter',
                      'posts_per_page' => -1,
                      'meta_query'     => [['key' => 'webnovel', 'value' => $wn->ID]],
                      'fields'         => 'ids',
                  ]));
                  $status_dot   = ['ongoing'=>'&#9679;', 'complete'=>'&#10003;', 'hiatus'=>'&#9711;', 'planned'=>'&#9633;', 'discontinued'=>'&#10007;'][$status] ?? '&#9679;';
                  $status_class = in_array($status, ['ongoing','complete','hiatus'], true) ? $status : 'ongoing';
                  $slug         = sanitize_title(get_the_title($wn)) . '/';
              ?>
              <div class="crt-row">
                <div class="crt-status <?php echo esc_attr($status_class); ?>"><?php echo $status_dot; ?></div>
                <a class="crt-title" href="<?php echo esc_url(get_permalink($wn)); ?>"><?php echo esc_html($slug); ?></a>
                <div class="crt-tag">[<?php echo esc_html(strtoupper($genre)); ?>]</div>
                <div class="crt-meta">ch <?php echo (int)$chapter_count; ?> / <?php echo $total ? (int)$total : '??'; ?></div>
                <div class="crt-state <?php echo esc_attr($status_class); ?>"><?php echo esc_html(strtoupper($status)); ?></div>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="crt-row">
                <div class="crt-status">&#9679;</div>
                <div class="crt-title">no_webnovels_yet/</div>
                <div class="crt-tag">[EMPTY]</div>
                <div class="crt-meta">ch 0 / 0</div>
                <div class="crt-state">WAITING</div>
              </div>
            <?php endif; ?>
          </div>
          <div style="margin-top:1.5rem; position:relative; z-index:2;">
            <span class="user" style="color:var(--gold)">coda@haunted-tech</span>:<span class="path" style="color:var(--bone)">~/webnovels</span>$ <span class="crt-cursor">&#9608;</span>
          </div>
        </div>
        <div class="crt-led"></div>
        <div class="crt-brand">CODA-OS v.0xDEAD</div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * SERVICES — three cards (currently static; future: services CPT)
 * ============================================================ */
function ht_render_services($attributes = []) {
    ob_start(); ?>
    <section class="block-services" id="services">
      <div class="section-header">
        <h2 class="section-title">Services</h2>
        <div class="section-meta">Commissions Open &mdash; Limited Slots</div>
      </div>
      <div class="services-grid">
        <div class="service-card">
          <div class="service-icon-area"><div class="service-icon">&#10048;</div></div>
          <div class="service-meta">
            <div class="service-eyebrow">Bespoke</div>
            <div class="service-title">Art Commissions</div>
            <div class="service-desc">Original character art, cyber-gothic portraits, and scene illustrations. Hand-drawn with neon-glitch finish. Personal or commercial licenses available.</div>
            <a href="#gallery-art" class="service-btn">View Portfolio</a>
          </div>
        </div>
        <div class="service-card">
          <div class="service-icon-area"><div class="service-icon">&#10065;</div></div>
          <div class="service-meta">
            <div class="service-eyebrow">Premade &amp; Custom</div>
            <div class="service-title">Book Cover Design</div>
            <div class="service-desc">Full-wrap cover design for dark romance, horror, and cyberpunk fiction. Includes ebook, paperback, hardcover layouts plus branded series styling.</div>
            <a href="#gallery-covers" class="service-btn">View Portfolio</a>
          </div>
        </div>
        <div class="service-card">
          <div class="service-icon-area"><div class="service-icon">&#9635;</div></div>
          <div class="service-meta">
            <div class="service-eyebrow">AI-Assisted</div>
            <div class="service-title">AI Image Generation</div>
            <div class="service-desc">Custom AI-generated character art, mood boards, and chapter banners. Flux + SDXL workflows. Final pieces are post-processed and finished by hand.</div>
            <a href="#gallery-ai" class="service-btn">View Portfolio</a>
          </div>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * GALLERY — currently static placeholder
 * ============================================================ */
function ht_render_gallery($attributes = []) {
    ob_start();
    include HAUNTED_TECH_DIR . '/inc/gallery-static.php';
    return ob_get_clean();
}

/* ============================================================
 * NEWSLETTER — placeholder form
 * ============================================================ */
function ht_render_newsletter($attributes = []) {
    /* If the user has saved an embed in Customizer → Haunted Tech → Newsletter,
     * inject it inside the callout in place of the placeholder form. */
    $embed = function_exists('haunted_tech_get_newsletter_embed') ? haunted_tech_get_newsletter_embed() : '';
    ob_start(); ?>
    <section class="newsletter block-newsletter" id="newsletter">
      <div class="newsletter-corner tl"></div>
      <div class="newsletter-corner tr"></div>
      <div class="newsletter-corner bl"></div>
      <div class="newsletter-corner br"></div>
      <div class="newsletter-content">
        <div class="newsletter-eyebrow">Encrypted Channel</div>
        <h2 data-text="JOIN THE SIGNAL">JOIN THE <span class="accent">SIGNAL</span></h2>
        <p>Early chapter drops, free shorts, exclusive art, and the occasional voice memo from the static. No spam, ever &mdash; just signal.</p>
        <?php if (!empty($embed)): ?>
          <div class="newsletter-embed"><?php echo $embed; /* admin-saved raw HTML/script */ ?></div>
        <?php else: ?>
          <form class="newsletter-form" onsubmit="return false;">
            <input type="email" class="newsletter-input" placeholder="your.handle@encrypted.net" required>
            <button type="submit" class="newsletter-submit">Subscribe</button>
          </form>
          <div class="newsletter-fine">
            Unsubscribe anytime &middot; PGP key on request
            <?php if (current_user_can('edit_theme_options')): ?>
              &middot; <a href="<?php echo esc_url(admin_url('customize.php?autofocus[section]=haunted_tech_newsletter')); ?>" style="color:var(--gold);">Connect your provider</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * GLOBAL OVERLAYS — CRT band + static burst
 * Inserted once near the top of every page via the header part.
 * ============================================================ */
function ht_render_overlays($attributes = []) {
    return '<div class="crt-band"></div><div class="static-burst"></div>';
}

/* ============================================================
 * LIGHTBOX (gallery enlarger) — singleton, included in footer part
 * ============================================================ */
function ht_render_lightbox($attributes = []) {
    ob_start(); ?>
    <div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-labelledby="lightbox-title" aria-hidden="true">
      <div class="lightbox-frame">
        <button class="lightbox-nav prev" aria-label="Previous">&larr;</button>
        <button class="lightbox-nav next" aria-label="Next">&rarr;</button>
        <button class="lightbox-close" aria-label="Close">&times;</button>
        <div class="lightbox-brand" aria-hidden="true">
          <img src="<?php echo esc_url(haunted_tech_logo_url()); ?>" alt="">
          <span class="lightbox-brand-text"><?php bloginfo('name'); ?></span>
        </div>
        <div class="lightbox-image" id="lightbox-image"><span class="gallery-image-label" id="lightbox-image-label"></span></div>
        <div class="lightbox-meta">
          <div class="lightbox-tag" id="lightbox-tag"></div>
          <div class="lightbox-title" id="lightbox-title"></div>
          <div class="lightbox-divider"></div>
          <div class="lightbox-desc" id="lightbox-desc"></div>
          <a href="#" class="lightbox-cta">Inquire</a>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * ABOUT MODAL — singleton, included in footer part
 * ============================================================ */
function ht_render_about_modal($attributes = []) {
    $about_page = get_page_by_path('about');
    $about_bio  = $about_page ? apply_filters('the_content', $about_page->post_content) : null;
    $portrait   = HAUNTED_TECH_URI . '/assets/coda-portrait.png';
    if ($about_page) {
        $thumb_id = get_post_thumbnail_id($about_page->ID);
        if ($thumb_id) {
            $src = wp_get_attachment_image_src($thumb_id, 'large');
            if ($src) $portrait = $src[0];
        }
    }
    ob_start(); ?>
    <div class="about-modal" id="about-modal" role="dialog" aria-modal="true" aria-labelledby="about-name" aria-hidden="true">
      <div class="about-frame">
        <button class="about-close" aria-label="Close about">&times;</button>
        <div class="about-portrait" style="background-image: url('<?php echo esc_url($portrait); ?>');">
          <?php if (!$about_page): ?>
            <div class="about-portrait-fallback">Save your portrait to <code style="color:var(--gold)">assets/coda-portrait.png</code></div>
          <?php endif; ?>
        </div>
        <div class="about-meta">
          <div class="about-meta-head">
            <div class="about-eyebrow">About the Author</div>
            <h2 class="about-name" id="about-name" data-text="<?php echo esc_attr(get_bloginfo('name')); ?>"><?php bloginfo('name'); ?></h2>
            <div class="about-title">Software Engineer &middot; Author &middot; Geek Overlord</div>
            <div class="about-divider"></div>
          </div>
          <div class="about-bio-wrap">
            <div class="about-bio">
              <?php if ($about_bio): ?>
                <?php echo $about_bio; ?>
              <?php else: ?>
                <p>Edit this content by creating a Page with the slug <code>about</code> in WP Admin.</p>
              <?php endif; ?>
            </div>
          </div>
          <div class="about-meta-foot">
            <div class="about-handle">@codalanguez</div>
          </div>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * SINGLE BOOK — bespoke layout for one book post
 * ============================================================ */
function ht_render_single_book($attributes = []) {
    $post_id = get_the_ID();
    if (!$post_id || get_post_type($post_id) !== 'book') return '';
    $subtitle = get_field('subtitle', $post_id);
    $series   = get_field('series', $post_id);
    $series_n = get_field('series_number', $post_id);
    $blurb    = get_field('blurb', $post_id);
    $genre    = get_field('genre', $post_id);
    $isbn     = get_field('isbn', $post_id);
    $asin     = get_field('asin', $post_id);
    $pages    = get_field('page_count', $post_id);
    $pub_date = get_field('publish_date', $post_id);
    $cover    = get_field('cover', $post_id);
    $amazon   = get_field('buy_amazon', $post_id);
    $bn       = get_field('buy_bn', $post_id);
    $kobo     = get_field('buy_kobo', $post_id);
    $apple    = get_field('buy_apple', $post_id);
    $ku       = get_field('kindle_unlimited', $post_id);
    $cover_url = '';
    if (is_array($cover) && !empty($cover['url'])) $cover_url = $cover['url'];
    elseif (has_post_thumbnail($post_id)) $cover_url = get_the_post_thumbnail_url($post_id, 'large');
    if (!$amazon && $asin) $amazon = 'https://www.amazon.com/dp/' . urlencode($asin);
    ob_start(); ?>
    <article id="post-<?php echo (int)$post_id; ?>" class="ht-book single-book" style="display:grid;grid-template-columns:1fr 1.4fr;gap:4rem;">
      <div class="book-cover-hero" style="background:var(--obsidian);border:1px solid var(--border);position:relative;aspect-ratio:2/3;">
        <?php if ($cover_url): ?>
          <img src="<?php echo esc_url($cover_url); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" style="display:block;width:100%;height:100%;object-fit:cover;">
        <?php else: ?>
          <div class="book-cover" style="height:100%;"><?php echo esc_html(get_the_title($post_id)); ?></div>
        <?php endif; ?>
        <?php if ($ku): ?>
          <div style="position:absolute;top:1rem;right:1rem;background:var(--gold);color:var(--void);font-family:'Forum',serif;font-size:0.7rem;letter-spacing:0.3em;text-transform:uppercase;padding:0.5rem 0.8rem;">&#9670; Kindle Unlimited</div>
        <?php endif; ?>
      </div>
      <div class="book-meta-hero">
        <?php if ($series): ?>
          <div style="font-family:'Forum',serif;font-size:0.75rem;letter-spacing:0.4em;color:var(--red);text-transform:uppercase;margin-bottom:0.75rem;text-shadow:0 0 6px rgba(229,9,20,0.4);">
            &#9670; <?php echo esc_html($series); ?><?php if ($series_n): ?> &middot; Book <?php echo (int)$series_n; ?><?php endif; ?>
          </div>
        <?php endif; ?>
        <h1 data-text="<?php echo esc_attr(get_the_title($post_id)); ?>" style="font-family:'Forum',serif;font-size:clamp(2rem,5vw,3.5rem);color:var(--gold);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.5rem;text-shadow:0 0 24px rgba(255,212,0,0.4);"><?php echo esc_html(get_the_title($post_id)); ?></h1>
        <?php if ($subtitle): ?><div style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1.3rem;color:var(--bone-dim);margin-bottom:2rem;"><?php echo esc_html($subtitle); ?></div><?php endif; ?>
        <div class="book-detail-row" style="display:flex;gap:1.5rem;font-family:'Inter',sans-serif;font-size:0.8rem;letter-spacing:0.2em;color:var(--bone-dim);text-transform:uppercase;margin-bottom:2rem;flex-wrap:wrap;">
          <?php if ($genre): ?><span><?php echo esc_html($genre); ?></span><?php endif; ?>
          <?php if ($pages): ?><span><?php echo (int)$pages; ?> pages</span><?php endif; ?>
          <?php if ($pub_date): ?><span><?php echo esc_html($pub_date); ?></span><?php endif; ?>
          <?php if ($isbn): ?><span>ISBN <?php echo esc_html($isbn); ?></span><?php endif; ?>
        </div>
        <?php if ($blurb): ?>
          <div style="font-family:'Cormorant Garamond',serif;font-size:1.15rem;line-height:1.8;color:var(--bone);margin-bottom:2.5rem;"><?php echo wp_kses_post(wpautop($blurb)); ?></div>
        <?php endif; ?>
        <div style="display:flex;gap:0.6rem;flex-wrap:wrap;">
          <?php if ($amazon): ?><a href="<?php echo esc_url($amazon); ?>" class="cta" style="padding:0.7rem 1.4rem;font-size:0.8rem;">Amazon</a><?php endif; ?>
          <?php if ($bn): ?><a href="<?php echo esc_url($bn); ?>" class="cta" style="padding:0.7rem 1.4rem;font-size:0.8rem;">B&amp;N</a><?php endif; ?>
          <?php if ($kobo): ?><a href="<?php echo esc_url($kobo); ?>" class="cta" style="padding:0.7rem 1.4rem;font-size:0.8rem;">Kobo</a><?php endif; ?>
          <?php if ($apple): ?><a href="<?php echo esc_url($apple); ?>" class="cta" style="padding:0.7rem 1.4rem;font-size:0.8rem;">Apple</a><?php endif; ?>
        </div>
      </div>
    </article>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * SINGLE WEB NOVEL — series page with chapter ToC
 * ============================================================ */
function ht_render_single_webnovel($attributes = []) {
    $wn_id = get_the_ID();
    if (!$wn_id || get_post_type($wn_id) !== 'webnovel') return '';
    $tagline  = get_field('tagline', $wn_id);
    $blurb    = get_field('blurb', $wn_id);
    $cover    = get_field('cover', $wn_id);
    $status   = get_field('status', $wn_id) ?: 'ongoing';
    $sched    = get_field('update_schedule', $wn_id);
    $genre    = get_field('genre', $wn_id);
    $tropes   = get_field('tropes', $wn_id);
    $warnings = get_field('content_warnings', $wn_id);
    $total    = get_field('total_chapters', $wn_id);
    $first_ch = get_field('first_chapter', $wn_id);
    $patreon  = get_field('patreon_url', $wn_id);
    $ream     = get_field('ream_url', $wn_id);
    $substack = get_field('substack_url', $wn_id);
    $cover_url = '';
    if (is_array($cover) && !empty($cover['url'])) $cover_url = $cover['url'];
    elseif (has_post_thumbnail($wn_id)) $cover_url = get_the_post_thumbnail_url($wn_id, 'large');
    $chapters = get_posts([
        'post_type'      => 'chapter',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => [['key' => 'webnovel', 'value' => $wn_id]],
        'meta_key'       => 'chapter_number',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    ]);
    ob_start(); ?>
    <article id="post-<?php echo (int)$wn_id; ?>" class="ht-webnovel">
      <header class="section-header" style="text-align:left;margin-bottom:3rem;">
        <div style="font-family:'Forum',serif;font-size:0.75rem;letter-spacing:0.4em;color:var(--red);text-transform:uppercase;margin-bottom:0.75rem;">
          &#9670; Web Novel &middot; <?php echo esc_html(strtoupper($status)); ?><?php if ($sched): ?> &middot; <?php echo esc_html($sched); ?><?php endif; ?>
        </div>
        <h1 data-text="<?php echo esc_attr(get_the_title($wn_id)); ?>" style="font-family:'Forum',serif;font-size:clamp(2rem,5vw,4rem);color:var(--gold);text-transform:uppercase;letter-spacing:0.06em;text-shadow:0 0 24px rgba(255,212,0,0.4);"><?php echo esc_html(get_the_title($wn_id)); ?></h1>
        <?php if ($tagline): ?><div style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1.3rem;color:var(--bone);margin-top:0.5rem;"><?php echo esc_html($tagline); ?></div><?php endif; ?>
      </header>
      <?php if ($cover_url || $blurb): ?>
      <div style="display:grid;grid-template-columns:<?php echo $cover_url ? '1fr 2fr' : '1fr'; ?>;gap:3rem;margin-bottom:4rem;">
        <?php if ($cover_url): ?>
          <div style="border:1px solid var(--border-dim);aspect-ratio:2/3;background:var(--obsidian);">
            <img src="<?php echo esc_url($cover_url); ?>" alt="<?php echo esc_attr(get_the_title($wn_id)); ?>" style="display:block;width:100%;height:100%;object-fit:cover;">
          </div>
        <?php endif; ?>
        <div>
          <?php if ($blurb): ?><div style="font-family:'Cormorant Garamond',serif;font-size:1.15rem;line-height:1.8;color:var(--bone);margin-bottom:2rem;"><?php echo wp_kses_post(wpautop($blurb)); ?></div><?php endif; ?>
          <?php if ($genre || $tropes): ?>
            <div style="font-family:'Inter',sans-serif;font-size:0.85rem;color:var(--bone-dim);margin-bottom:1.5rem;">
              <?php if ($genre): ?><div><strong style="color:var(--gold);letter-spacing:0.2em;text-transform:uppercase;">Genre:</strong> <?php echo esc_html($genre); ?></div><?php endif; ?>
              <?php if ($tropes): ?><div style="margin-top:0.5rem;"><strong style="color:var(--gold);letter-spacing:0.2em;text-transform:uppercase;">Tropes:</strong> <?php echo esc_html($tropes); ?></div><?php endif; ?>
            </div>
          <?php endif; ?>
          <?php if ($warnings): ?>
            <div style="border:1px solid var(--red);padding:1rem 1.2rem;background:rgba(90,10,18,0.2);font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone);margin-top:1.5rem;">
              <strong style="color:var(--red);letter-spacing:0.2em;text-transform:uppercase;font-style:normal;font-size:0.75rem;display:block;margin-bottom:0.4rem;">&#9670; Content Warnings</strong>
              <?php echo esc_html($warnings); ?>
            </div>
          <?php endif; ?>
          <div style="display:flex;gap:0.6rem;flex-wrap:wrap;margin-top:2rem;">
            <?php if ($first_ch): $first = is_object($first_ch) ? $first_ch : get_post((int)$first_ch); if ($first): ?>
              <a href="<?php echo esc_url(get_permalink($first)); ?>" class="cta">Start Reading</a>
            <?php endif; endif; ?>
            <?php if ($patreon):  ?><a href="<?php echo esc_url($patreon);  ?>" class="cta" style="padding:0.9rem 1.6rem;font-size:0.8rem;">Patreon</a><?php endif; ?>
            <?php if ($ream):     ?><a href="<?php echo esc_url($ream);     ?>" class="cta" style="padding:0.9rem 1.6rem;font-size:0.8rem;">Ream</a><?php endif; ?>
            <?php if ($substack): ?><a href="<?php echo esc_url($substack); ?>" class="cta" style="padding:0.9rem 1.6rem;font-size:0.8rem;">Substack</a><?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
      <?php if (!empty($chapters)): ?>
      <div class="chapters">
        <div class="chapters-inner-frame"></div>
        <h3>Table of Contents</h3>
        <div class="chapters-sub"><?php echo count($chapters); ?> Chapter<?php echo count($chapters) === 1 ? '' : 's'; ?><?php echo $total ? ' of ' . (int)$total : ''; ?></div>
        <?php foreach ($chapters as $ch):
            $ch_num = get_field('chapter_number', $ch->ID) ?: '?';
            $ch_wc  = get_field('word_count',     $ch->ID);
            $ch_acc = get_field('access_level',   $ch->ID) ?: 'free';
            $is_patron = !in_array($ch_acc, ['free'], true);
        ?>
          <div class="chapter-row">
            <div class="chapter-num"><?php echo esc_html($ch_num); ?></div>
            <a class="chapter-title" href="<?php echo esc_url(get_permalink($ch)); ?>" style="color:var(--bone);text-decoration:none;"><?php echo esc_html(get_the_title($ch)); ?></a>
            <div class="chapter-meta"><?php echo esc_html(get_the_date('M j', $ch)); ?><?php if ($ch_wc): ?> &middot; <?php echo number_format((int)$ch_wc); ?> words<?php endif; ?></div>
            <div class="chapter-access <?php echo $is_patron ? 'access-patron' : 'access-free'; ?>"><?php echo $is_patron ? 'Patron' : 'Free'; ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </article>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * SINGLE CHAPTER — reading view + prev/next
 * ============================================================ */
function ht_render_single_chapter($attributes = []) {
    $ch_id = get_the_ID();
    if (!$ch_id || get_post_type($ch_id) !== 'chapter') return '';
    $wn_field  = get_field('webnovel', $ch_id);
    $wn        = is_object($wn_field) ? $wn_field : (is_numeric($wn_field) ? get_post((int)$wn_field) : null);
    $ch_num    = get_field('chapter_number', $ch_id);
    $arc       = get_field('arc', $ch_id);
    $word_ct   = get_field('word_count', $ch_id);
    $release   = get_field('release_date', $ch_id);
    $access    = get_field('access_level', $ch_id) ?: 'free';
    $external  = get_field('external_read_url', $ch_id);
    $notes     = get_field('authors_note', $ch_id);
    $warnings  = get_field('chapter_warnings', $ch_id);
    $manual_prev = get_field('prev_chapter', $ch_id);
    $manual_next = get_field('next_chapter', $ch_id);
    $is_patron = !in_array($access, ['free'], true);
    $prev = $manual_prev ? (is_object($manual_prev) ? $manual_prev : get_post((int)$manual_prev)) : null;
    $next = $manual_next ? (is_object($manual_next) ? $manual_next : get_post((int)$manual_next)) : null;
    if ((!$prev || !$next) && $wn) {
        $siblings = get_posts([
            'post_type'      => 'chapter',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => [['key'=>'webnovel', 'value'=>$wn->ID]],
            'meta_key'       => 'chapter_number',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        ]);
        $ids = array_map(function($p){ return $p->ID; }, $siblings);
        $idx = array_search($ch_id, $ids, true);
        if ($idx !== false) {
            if (!$prev && $idx > 0)                    $prev = $siblings[$idx - 1];
            if (!$next && $idx < count($siblings) - 1) $next = $siblings[$idx + 1];
        }
    }
    ob_start(); ?>
    <article id="post-<?php echo (int)$ch_id; ?>" class="ht-chapter">
      <header style="text-align:center;margin-bottom:3rem;">
        <?php if ($wn): ?>
          <a href="<?php echo esc_url(get_permalink($wn)); ?>" style="font-family:'Forum',serif;font-size:0.75rem;letter-spacing:0.4em;color:var(--red);text-transform:uppercase;text-decoration:none;display:inline-block;margin-bottom:1rem;">&#9670; <?php echo esc_html(get_the_title($wn)); ?></a>
        <?php endif; ?>
        <?php if ($arc): ?><div style="font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone-dim);margin-bottom:0.6rem;letter-spacing:0.2em;text-transform:uppercase;font-size:0.85rem;"><?php echo esc_html($arc); ?></div><?php endif; ?>
        <?php if ($ch_num !== null && $ch_num !== ''): ?><div style="font-family:'Forum',serif;font-size:1rem;color:var(--gold);letter-spacing:0.5em;text-transform:uppercase;margin-bottom:0.4rem;">Chapter <?php echo esc_html($ch_num); ?></div><?php endif; ?>
        <h1 data-text="<?php echo esc_attr(get_the_title($ch_id)); ?>" style="font-family:'Forum',serif;font-size:clamp(1.8rem,4vw,2.8rem);color:var(--gold);text-transform:uppercase;letter-spacing:0.06em;text-shadow:0 0 16px rgba(255,212,0,0.3);"><?php echo esc_html(get_the_title($ch_id)); ?></h1>
        <div style="font-family:'Inter',sans-serif;font-size:0.75rem;letter-spacing:0.2em;color:var(--bone-dim);text-transform:uppercase;margin-top:1.2rem;">
          <?php echo esc_html($release ?: get_the_date('M j, Y')); ?>
          <?php if ($word_ct): ?> &middot; <?php echo number_format((int)$word_ct); ?> words<?php endif; ?>
          &middot; <span class="<?php echo $is_patron ? 'access-patron' : 'access-free'; ?>" style="padding:0.2rem 0.6rem;border:1px solid;display:inline-block;"><?php echo esc_html(ucwords(str_replace('_', ' ', $access))); ?></span>
        </div>
      </header>
      <?php if ($warnings): ?>
        <div style="border:1px solid var(--red);padding:1rem 1.2rem;background:rgba(90,10,18,0.2);font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone);margin-bottom:2rem;text-align:center;">
          <strong style="color:var(--red);letter-spacing:0.2em;text-transform:uppercase;font-style:normal;font-size:0.75rem;display:block;margin-bottom:0.4rem;">&#9670; Chapter Content Warnings</strong>
          <?php echo esc_html($warnings); ?>
        </div>
      <?php endif; ?>
      <?php if ($external): ?>
        <div style="border:1px solid var(--gold);padding:2rem;text-align:center;background:var(--obsidian);margin-bottom:3rem;">
          <div style="font-family:'Forum',serif;color:var(--gold);text-transform:uppercase;letter-spacing:0.3em;margin-bottom:1rem;font-size:0.9rem;">This chapter lives off-site</div>
          <p style="font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone);margin-bottom:1.5rem;">Read the full chapter on the linked platform.</p>
          <a href="<?php echo esc_url($external); ?>" class="cta" target="_blank" rel="noopener">Continue Reading &rarr;</a>
        </div>
      <?php endif; ?>
      <div class="ht-chapter-body" style="font-family:'Cormorant Garamond',serif;font-size:1.18rem;line-height:1.85;color:var(--bone);">
        <?php
        $content_post = get_post($ch_id);
        echo apply_filters('the_content', $content_post->post_content);
        ?>
      </div>
      <?php if ($notes): ?>
        <aside style="margin-top:4rem;border-top:1px solid var(--border-dim);padding-top:2rem;font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone-dim);">
          <div style="font-family:'Forum',serif;color:var(--gold);text-transform:uppercase;letter-spacing:0.3em;font-size:0.75rem;font-style:normal;margin-bottom:1rem;">&#9670; Author's Note</div>
          <?php echo wp_kses_post(wpautop($notes)); ?>
        </aside>
      <?php endif; ?>
      <nav style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:5rem;padding-top:2rem;border-top:1px solid var(--border-dim);">
        <?php if ($prev): ?>
          <a href="<?php echo esc_url(get_permalink($prev)); ?>" style="display:block;padding:1.5rem;border:1px solid var(--border-dim);text-decoration:none;text-align:left;">
            <div style="font-family:'Forum',serif;color:var(--red);font-size:0.7rem;letter-spacing:0.3em;text-transform:uppercase;margin-bottom:0.4rem;">&larr; Previous</div>
            <div style="font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone);"><?php echo esc_html(get_the_title($prev)); ?></div>
          </a>
        <?php else: ?><span></span><?php endif; ?>
        <?php if ($next): ?>
          <a href="<?php echo esc_url(get_permalink($next)); ?>" style="display:block;padding:1.5rem;border:1px solid var(--border-dim);text-decoration:none;text-align:right;">
            <div style="font-family:'Forum',serif;color:var(--red);font-size:0.7rem;letter-spacing:0.3em;text-transform:uppercase;margin-bottom:0.4rem;">Next &rarr;</div>
            <div style="font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone);"><?php echo esc_html(get_the_title($next)); ?></div>
          </a>
        <?php else: ?><span></span><?php endif; ?>
      </nav>
    </article>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * SITE FOOTER — bottom of every page
 * ============================================================ */
function ht_render_site_footer($attributes = []) {
    ob_start(); ?>
    <footer class="block-footer">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo" aria-label="<?php bloginfo('name'); ?>">
        <img src="<?php echo esc_url(haunted_tech_logo_url()); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?> logo">
      </a>
      <div class="ornament"><span>&#9670;</span> <span>&#9670;</span> <span>&#9670;</span></div>
      <div class="links">
        <?php
        if (has_nav_menu('footer')) {
            wp_nav_menu([
                'theme_location' => 'footer',
                'container'      => false,
                'items_wrap'     => '%3$s',
                'fallback_cb'    => false,
                'depth'          => 1,
            ]);
        } else {
            echo '<a href="#">Patreon</a><a href="#">Ream</a><a href="#">Substack</a><a href="#">Amazon</a><a href="#newsletter">Newsletter</a>';
        }
        ?>
      </div>
      <div class="copy">&copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?></div>
      <div class="theme-name">Haunted Tech</div>
    </footer>
    <?php
    return ob_get_clean();
}
