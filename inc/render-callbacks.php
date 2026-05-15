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
          <?php
          $_logo_id = get_theme_mod('custom_logo');
          if ($_logo_id) {
              echo wp_get_attachment_image($_logo_id, [200, 200], false, [
                  'alt'      => esc_attr(get_bloginfo('name')) . ' logo',
                  'loading'  => 'eager',
                  'decoding' => 'async',
                  'class'    => 'site-logo-img',
              ]);
          } else {
              echo '<img src="' . esc_url(haunted_tech_logo_url()) . '" alt="' . esc_attr(get_bloginfo('name')) . ' logo" width="512" height="512" loading="eager">';
          }
          ?>
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
    <span id="hero" class="ht-anchor" aria-hidden="true"></span>
    <span id="top"  class="ht-anchor" aria-hidden="true"></span>
    <div class="hero block-hero" id="hero-slider">
      <div class="hero-watermark" aria-hidden="true">
        <?php
        $_wm_id = get_theme_mod('custom_logo');
        if ($_wm_id) {
            echo wp_get_attachment_image($_wm_id, 'medium', false, ['alt' => '', 'loading' => 'eager', 'decoding' => 'async']);
        } else {
            echo '<img src="' . esc_url(haunted_tech_logo_url()) . '" alt="" width="512" height="512" loading="eager">';
        }
        ?>
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
            <a id="book-<?php echo esc_attr($book->post_name); ?>" href="<?php echo esc_url(get_permalink($book)); ?>" data-open-book="<?php echo esc_attr($book->post_name); ?>" class="spine <?php echo esc_attr($variant); ?>" style="width:<?php echo (int)$width; ?>px; height:<?php echo (int)$height; ?>px;">
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
              <div class="crt-row" id="webnovel-<?php echo esc_attr($wn->post_name); ?>">
                <div class="crt-status <?php echo esc_attr($status_class); ?>"><?php echo $status_dot; ?></div>
                <a class="crt-title" href="<?php echo esc_url(get_permalink($wn)); ?>" data-open-webnovel="<?php echo esc_attr($wn->post_name); ?>"><?php echo esc_html($slug); ?></a>
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
        <div class="service-card" id="service-art">
          <div class="service-icon-area"><div class="service-icon">&#10048;</div></div>
          <div class="service-meta">
            <div class="service-eyebrow">Bespoke</div>
            <div class="service-title">Art Commissions</div>
            <div class="service-desc">Original character art, cyber-gothic portraits, and scene illustrations. Hand-drawn with neon-glitch finish. Personal or commercial licenses available.</div>
            <div class="service-actions">
              <a href="#gallery-art" class="service-btn">View Portfolio</a>
              <button type="button" class="service-btn service-btn-inquire" data-open-commission="art">Inquire &rarr;</button>
            </div>
          </div>
        </div>
        <div class="service-card" id="service-covers">
          <div class="service-icon-area"><div class="service-icon">&#10065;</div></div>
          <div class="service-meta">
            <div class="service-eyebrow">Premade &amp; Custom</div>
            <div class="service-title">Book Cover Design</div>
            <div class="service-desc">Full-wrap cover design for dark romance, horror, and cyberpunk fiction. Includes ebook, paperback, hardcover layouts plus branded series styling.</div>
            <div class="service-actions">
              <a href="#gallery-covers" class="service-btn">View Portfolio</a>
              <button type="button" class="service-btn service-btn-inquire" data-open-commission="cover">Inquire &rarr;</button>
            </div>
          </div>
        </div>
        <div class="service-card" id="service-ai">
          <div class="service-icon-area"><div class="service-icon">&#9635;</div></div>
          <div class="service-meta">
            <div class="service-eyebrow">AI-Assisted</div>
            <div class="service-title">AI Image Generation</div>
            <div class="service-desc">Custom AI-generated character art, mood boards, and chapter banners. Flux + SDXL workflows. Final pieces are post-processed and finished by hand.</div>
            <div class="service-actions">
              <a href="#gallery-ai" class="service-btn">View Portfolio</a>
              <button type="button" class="service-btn service-btn-inquire" data-open-commission="ai">Inquire &rarr;</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Commission inquiry modals — opened by data-open-commission buttons.
         Styled to match the About modal: full-screen overlay, gold-framed
         panel with corner accents + scanline backdrop. -->
    <?php foreach ([
        'art'   => ['eyebrow' => 'Bespoke',            'title' => 'Art Commission',    'shortcode' => '[ht_commission_art]'],
        'cover' => ['eyebrow' => 'Premade & Custom',   'title' => 'Book Cover Design', 'shortcode' => '[ht_commission_cover]'],
        'ai'    => ['eyebrow' => 'AI-Assisted',        'title' => 'AI Generation',     'shortcode' => '[ht_commission_ai]'],
    ] as $key => $cfg): ?>
    <div class="commission-modal" id="commission-modal-<?php echo esc_attr($key); ?>" role="dialog" aria-modal="true" aria-labelledby="cm-title-<?php echo esc_attr($key); ?>" aria-hidden="true">
      <div class="commission-frame">
        <button class="commission-close" aria-label="Close inquiry form">&times;</button>
        <div class="commission-meta">
          <div class="commission-meta-head">
            <div class="commission-eyebrow"><?php echo esc_html($cfg['eyebrow']); ?></div>
            <h2 class="commission-title" id="cm-title-<?php echo esc_attr($key); ?>" data-text="<?php echo esc_attr($cfg['title']); ?>"><?php echo esc_html($cfg['title']); ?></h2>
            <div class="commission-divider"></div>
          </div>
          <div class="commission-body-wrap">
            <div class="commission-body">
              <?php echo do_shortcode($cfg['shortcode']); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <script>
    (function(){
      var modals = document.querySelectorAll('.commission-modal');
      function open(key){
        var m = document.getElementById('commission-modal-' + key);
        if (!m) return;
        m.classList.add('active');
        m.setAttribute('aria-hidden', 'false');
        document.body.classList.add('commission-open');
      }
      function close(m){
        m.classList.remove('active');
        m.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('commission-open');
      }
      document.querySelectorAll('[data-open-commission]').forEach(function(btn){
        btn.addEventListener('click', function(e){
          e.preventDefault();
          open(this.dataset.openCommission);
        });
      });
      modals.forEach(function(m){
        m.querySelector('.commission-close').addEventListener('click', function(){ close(m); });
        m.addEventListener('click', function(e){ if (e.target === m) close(m); });
      });
      document.addEventListener('keydown', function(e){
        if (e.key === 'Escape') modals.forEach(function(m){ if (m.classList.contains('active')) close(m); });
      });
    })();
    </script>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * GALLERY — driven by the gallery_item CPT (one post per tile)
 * ============================================================ */
function ht_render_gallery($attributes = []) {
    $all_items = get_posts([
        'post_type'      => 'gallery_item',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => ['menu_order' => 'ASC', 'date' => 'DESC'],
    ]);

    /* No items yet → fall back to the static placeholder shipped with the theme. */
    if (empty($all_items)) {
        ob_start();
        include HAUNTED_TECH_DIR . '/inc/gallery-static.php';
        return ob_get_clean();
    }

    /* Group items by service tab */
    $grouped = ['art' => [], 'covers' => [], 'ai' => []];
    foreach ($all_items as $item) {
        $tab = get_field('service_tab', $item->ID) ?: 'art';
        if (isset($grouped[$tab])) $grouped[$tab][] = $item;
    }

    $tab_labels = [
        'art'    => 'Art Commissions',
        'covers' => 'Book Covers',
        'ai'     => 'AI Generation',
    ];
    $tab_targets = [
        'art'    => 'panel-art',
        'covers' => 'panel-covers',
        'ai'     => 'panel-ai',
    ];
    $tab_cta = [
        'art'    => 'All Commissions',
        'covers' => 'All Covers',
        'ai'     => 'All AI Pieces',
    ];

    /* First non-empty tab is active by default */
    $first_active = 'art';
    foreach (array_keys($tab_labels) as $key) {
        if (!empty($grouped[$key])) { $first_active = $key; break; }
    }

    $page_size = 9;
    ob_start(); ?>
    <section class="gallery block-gallery" id="gallery">
      <div class="section-header">
        <h2 class="section-title">Gallery</h2>
        <div class="section-meta">Recent Work &mdash; Filter by Service</div>
      </div>

      <div class="gallery-tabs" role="tablist">
        <?php foreach ($tab_labels as $key => $label):
            $is_active = ($key === $first_active);
        ?>
          <button class="gallery-tab<?php echo $is_active ? ' active' : ''; ?>" data-target="<?php echo esc_attr($tab_targets[$key]); ?>" role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"><?php echo esc_html($label); ?></button>
        <?php endforeach; ?>
      </div>

      <?php
      /* Map gallery tab → commission modal key (covers→cover; art/ai pass-through). */
      $tab_to_modal = ['art' => 'art', 'covers' => 'cover', 'ai' => 'ai'];
      foreach ($tab_labels as $tab => $label):
          $items       = $grouped[$tab];
          $is_active   = ($tab === $first_active);
          $panel_id    = $tab_targets[$tab];
          $total_pages = max(1, (int) ceil(count($items) / $page_size));
          $modal_key   = $tab_to_modal[$tab];
      ?>
        <div class="gallery-panel<?php echo $is_active ? ' active' : ''; ?>" id="<?php echo esc_attr($panel_id); ?>" role="tabpanel" data-page="1" data-pages="<?php echo (int)$total_pages; ?>">

          <?php
          /* Filter chips only on the Art Commissions tab. Derive unique category labels from this tab's items. */
          if ($tab === 'art' && !empty($items)):
              $cats = [];
              foreach ($items as $i) {
                  $raw = trim((string) get_field('category', $i->ID));
                  if ($raw === '') continue;
                  $slug  = strtolower(sanitize_title($raw));
                  $cats[$slug] = ucfirst($raw);
              }
              if (!empty($cats)): ?>
                <div class="gallery-chips" role="toolbar" aria-label="Filter commissions">
                  <button class="gallery-chip active" data-filter="all">All</button>
                  <?php foreach ($cats as $slug => $label_cat): ?>
                    <button class="gallery-chip" data-filter="<?php echo esc_attr($slug); ?>"><?php echo esc_html($label_cat); ?></button>
                  <?php endforeach; ?>
                </div>
          <?php endif; endif; ?>

          <?php if (!empty($items)): ?>
            <div class="masonry">
              <?php foreach ($items as $idx => $item):
                  $page_num   = (int) floor($idx / $page_size) + 1;
                  $cat_raw    = (string) get_field('category', $item->ID);
                  $cat_slug   = $cat_raw !== '' ? strtolower(sanitize_title($cat_raw)) : '';
                  $card_tag   = (string) get_field('tag', $item->ID);
                  $desc       = (string) get_field('description', $item->ID);
                  $ratio      = (string) (get_field('aspect_ratio', $item->ID) ?: '3/4');
                  $variant    = 'v' . ((($idx % 8) + 1));
                  $image      = get_field('image', $item->ID);
                  $image_url  = '';
                  if (is_array($image) && !empty($image['url'])) {
                      $image_url = $image['url'];
                  } elseif (has_post_thumbnail($item->ID)) {
                      $image_url = get_the_post_thumbnail_url($item->ID, 'large');
                  }
                  $alt = is_array($image) && !empty($image['alt']) ? $image['alt'] : get_the_title($item);
                  $page_class = $page_num > 1 ? ' page-hidden' : '';
              ?>
                <a href="<?php echo $image_url ? esc_url($image_url) : '#'; ?>"
                   class="gallery-item<?php echo esc_attr($page_class); ?>"
                   data-page="<?php echo (int)$page_num; ?>"
                   <?php if ($cat_slug): ?>data-cat="<?php echo esc_attr($cat_slug); ?>"<?php endif; ?>
                   data-tag="<?php echo esc_attr($card_tag); ?>"
                   data-title="<?php echo esc_attr(get_the_title($item)); ?>"
                   data-desc="<?php echo esc_attr($desc); ?>"
                   data-image-class="<?php echo esc_attr($variant); ?>">
                  <div class="gallery-image <?php echo esc_attr($variant); ?>" style="--ratio: <?php echo esc_attr($ratio); ?>; position:relative;">
                    <?php if ($image_url): ?>
                      <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block;z-index:1;">
                    <?php else: ?>
                      <span class="gallery-image-label"><?php echo esc_html(get_the_title($item)); ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="gallery-meta">
                    <?php if ($card_tag): ?><div class="gallery-tag"><?php echo esc_html($card_tag); ?></div><?php endif; ?>
                    <div class="gallery-title"><?php echo esc_html(get_the_title($item)); ?></div>
                    <?php if ($desc): ?><div class="gallery-caption"><?php echo esc_html(wp_trim_words($desc, 18, '…')); ?></div><?php endif; ?>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>

            <div class="gallery-footer">
              <button class="gallery-arrow prev" aria-label="Previous page" disabled>&larr;</button>
              <div class="gallery-page-indicator">Page <span>1</span> / <?php echo (int)$total_pages; ?></div>
              <button class="gallery-arrow next" aria-label="Next page" <?php echo $total_pages <= 1 ? 'disabled' : ''; ?>>&rarr;</button>
              <button type="button" class="gallery-inquire" data-open-commission="<?php echo esc_attr($modal_key); ?>">Inquire &rarr;</button>
            </div>

          <?php else: ?>
            <div style="text-align:center;padding:3rem 2rem;font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone-dim);">
              No <?php echo esc_html(strtolower($label)); ?> items yet.
              <?php if (current_user_can('edit_posts')): ?>
                <br>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=gallery_item')); ?>" style="color:var(--gold);">Add the first one</a> &mdash; set <em>Service Tab</em> to "<?php echo esc_html($label); ?>".
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </section>
    <?php
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
 * SINGLE BOOK — bespoke book hero matching mockup 14/15/16.
 * Every field conditional: empty values collapse out entirely.
 * ============================================================ */
function ht_render_single_book($attributes = []) {
    $post_id = get_the_ID();
    if (!$post_id || get_post_type($post_id) !== 'book') return '';

    $subtitle    = get_field('subtitle', $post_id);
    $series      = get_field('series', $post_id);
    $series_n    = get_field('series_number', $post_id);
    $blurb       = get_field('blurb', $post_id);
    $genre       = get_field('genre', $post_id);
    $isbn        = get_field('isbn', $post_id);
    $asin        = get_field('asin', $post_id);
    $pages       = get_field('page_count', $post_id);
    $pub_date    = get_field('publish_date', $post_id);
    $cover       = get_field('cover', $post_id);
    $amazon      = get_field('buy_amazon', $post_id);
    $bn          = get_field('buy_bn', $post_id);
    $kobo        = get_field('buy_kobo', $post_id);
    $apple       = get_field('buy_apple', $post_id);
    $ku          = get_field('kindle_unlimited', $post_id);
    /* v0.8 discovery + content-warning fields */
    $goodreads   = get_field('goodreads_url', $post_id);
    $bookbub     = get_field('bookbub_url',   $post_id);
    $storygraph  = get_field('storygraph_url', $post_id);
    $cw_graphic  = get_field('content_warnings_graphic', $post_id);
    $cw_standard = get_field('content_warnings',         $post_id);
    /* v0.9 reader-magnet field */
    $download    = get_field('download_url', $post_id);

    if (!$amazon && $asin) $amazon = 'https://www.amazon.com/dp/' . urlencode($asin);

    $cover_url = '';
    if (is_array($cover) && !empty($cover['url'])) $cover_url = $cover['url'];
    elseif (has_post_thumbnail($post_id)) $cover_url = get_the_post_thumbnail_url($post_id, 'large');

    $cw_graphic_items  = array_filter(array_map('trim', explode(',', (string)$cw_graphic)));
    $cw_standard_items = array_filter(array_map('trim', explode(',', (string)$cw_standard)));
    $cw_total          = count($cw_graphic_items) + count($cw_standard_items);

    $find_links = array_filter([
        'Goodreads'  => $goodreads,
        'BookBub'    => $bookbub,
        'StoryGraph' => $storygraph,
    ]);

    ob_start(); ?>
    <section class="book-hero">
      <div class="book-hero-inner">
        <div class="book-cover-wrap">
          <?php if ($ku): ?><div class="ku-badge">Kindle Unlimited</div><?php endif; ?>
          <?php if ($cover_url): ?>
            <img src="<?php echo esc_url($cover_url); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block;z-index:1;">
          <?php else: ?>
            <div class="book-cover-art">
              <?php if ($series): ?>
                <div class="cover-series-mark"><?php echo esc_html($series); ?><?php if ($series_n): ?> &middot; <?php echo (int)$series_n; ?><?php endif; ?></div>
              <?php endif; ?>
              <div class="cover-title"><?php echo esc_html(get_the_title($post_id)); ?></div>
              <div class="cover-author"><?php bloginfo('name'); ?></div>
            </div>
          <?php endif; ?>
        </div>

        <div class="book-meta-col">
          <?php if ($series): ?>
            <div class="book-series-mark">
              <?php echo esc_html($series); ?><?php if ($series_n): ?> &middot; Book <?php echo (int)$series_n; ?><?php endif; ?>
            </div>
          <?php endif; ?>

          <h1 class="book-title" data-text="<?php echo esc_attr(get_the_title($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></h1>

          <?php /* Author byline — small portrait + name, click → opens About modal */ ?>
          <a href="#about" data-open-about class="book-byline" aria-label="<?php esc_attr_e('About the author', 'haunted-tech'); ?>">
            <img src="<?php echo esc_url(haunted_tech_logo_url()); ?>" alt="" class="book-byline-portrait">
            <span class="book-byline-text">by <?php bloginfo('name'); ?></span>
          </a>

          <?php if ($subtitle): ?><div class="book-subtitle"><?php echo esc_html($subtitle); ?></div><?php endif; ?>

          <?php
          $details = array_filter([
              $genre    ? ['Genre',     esc_html($genre)]    : null,
              $pages    ? ['Pages',     (int)$pages]         : null,
              $pub_date ? ['Published', esc_html($pub_date)] : null,
              $isbn     ? ['ISBN',      esc_html($isbn)]     : null,
              $asin     ? ['ASIN',      esc_html($asin)]     : null,
          ]);
          if (!empty($details)): ?>
            <div class="book-detail-row">
              <?php foreach ($details as $d): ?>
                <span><strong><?php echo esc_html($d[0]); ?>:</strong> <?php echo $d[1]; ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php
          /* Buy / acquire row.
           * If a download_url is set (reader magnet), "Download Free" leads
           * the row with a primary-CTA class hook (buy-btn-download). Paid
           * retailer buttons follow in the standard buy-btn treatment. */
          $buys = array_filter([
              $download ? ['Download Free',  $download, 'buy-btn buy-btn-download'] : null,
              $amazon   ? ['Amazon',         $amazon,   'buy-btn'] : null,
              $bn       ? ['Barnes & Noble', $bn,       'buy-btn'] : null,
              $kobo     ? ['Kobo',           $kobo,     'buy-btn'] : null,
              $apple    ? ['Apple Books',    $apple,    'buy-btn'] : null,
          ]);
          if (!empty($buys)): ?>
            <div class="book-buy-row">
              <?php foreach ($buys as $b):
                  $cls = $b[2] ?? 'buy-btn';
              ?>
                <a href="<?php echo esc_url($b[1]); ?>" class="<?php echo esc_attr($cls); ?>" target="_blank" rel="noopener"><?php echo esc_html($b[0]); ?></a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($find_links)): ?>
            <div class="book-find-row">
              <span class="book-find-label">Find online</span>
              <?php foreach ($find_links as $label => $url): ?>
                <a href="<?php echo esc_url($url); ?>" class="find-btn" target="_blank" rel="noopener"><?php echo esc_html($label); ?></a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($blurb): ?>
            <div class="book-blurb"><?php echo wp_kses_post(wpautop($blurb)); ?></div>
          <?php endif; ?>

          <?php if ($cw_total > 0): ?>
            <details class="content-warnings">
              <summary>Content Warnings <span class="cw-count"><?php echo (int)$cw_total; ?> listed</span></summary>
              <div class="cw-body">
                <p class="cw-intro">This book engages with difficult material on purpose. If any of these would harm your day, this isn't the book for it.</p>
                <?php if (!empty($cw_graphic_items)): ?>
                  <ul class="cw-list cw-graphic">
                    <?php foreach ($cw_graphic_items as $cw): ?><li><?php echo esc_html($cw); ?></li><?php endforeach; ?>
                  </ul>
                <?php endif; ?>
                <?php if (!empty($cw_standard_items)): ?>
                  <ul class="cw-list" style="margin-top:0.6rem;">
                    <?php foreach ($cw_standard_items as $cw): ?><li><?php echo esc_html($cw); ?></li><?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </div>
            </details>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * BOOK EXCERPT — chapter preview with drop-cap
 * ============================================================ */
function ht_render_book_excerpt($attributes = []) {
    $post_id = get_the_ID();
    if (!$post_id || get_post_type($post_id) !== 'book') return '';
    $excerpt = get_field('excerpt_html', $post_id);
    if (!$excerpt) return '';
    $eyebrow = get_field('excerpt_eyebrow', $post_id);
    $amazon  = get_field('buy_amazon', $post_id);
    $asin    = get_field('asin', $post_id);
    if (!$amazon && $asin) $amazon = 'https://www.amazon.com/dp/' . urlencode($asin);
    ob_start(); ?>
    <section class="book-excerpt-section">
      <?php if ($eyebrow): ?><div class="book-excerpt-eyebrow"><?php echo esc_html($eyebrow); ?></div><?php endif; ?>
      <h3 class="book-excerpt-title">Begin Reading</h3>
      <div class="book-excerpt-body"><?php echo wp_kses_post($excerpt); ?></div>
      <?php if ($amazon): ?>
        <div class="book-excerpt-fade">
          <a href="<?php echo esc_url($amazon); ?>" class="cta" target="_blank" rel="noopener">Continue Reading on Amazon</a>
        </div>
      <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * MORE IN THIS SERIES — mini-shelf of series siblings
 * Returns '' if not part of a series or no siblings exist.
 * ============================================================ */
function ht_render_book_more_in_series($attributes = []) {
    $post_id = get_the_ID();
    if (!$post_id || get_post_type($post_id) !== 'book') return '';
    $series = get_field('series', $post_id);
    if (!$series) return '';

    $siblings = get_posts([
        'post_type'      => 'book',
        'posts_per_page' => 12,
        'post_status'    => 'publish',
        'meta_query'     => [['key' => 'series', 'value' => $series]],
        'meta_key'       => 'series_number',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    ]);
    if (count($siblings) < 2) return '';

    $variants = ['oxblood', 'obsidian', 'teal', 'charcoal', 'gold'];
    ob_start(); ?>
    <section class="more-series-section">
      <div class="section-header">
        <h2 class="section-title"><?php echo esc_html($series); ?></h2>
        <div class="section-meta"><?php echo (int)count($siblings); ?>-book series</div>
      </div>
      <div class="series-spine-row">
        <?php foreach ($siblings as $i => $sib):
            $variant    = $variants[$i % count($variants)];
            $is_current = ($sib->ID === $post_id);
            $width      = 64 + (($i * 7) % 25);
            $height     = 380 + (($i * 11) % 60);
            $n          = get_field('series_number', $sib->ID) ?: '?';
            $cls        = 'more-spine ' . $variant . ($is_current ? ' current' : '');
        ?>
          <a <?php echo $is_current ? '' : 'href="' . esc_url(get_permalink($sib)) . '" data-open-book="' . esc_attr($sib->post_name) . '"'; ?>
             class="<?php echo esc_attr($cls); ?>"
             style="width:<?php echo (int)$width; ?>px; height:<?php echo (int)$height; ?>px;">
            <div class="more-spine-ornament">&#9670; &#9670;</div>
            <div class="more-spine-title"><?php echo esc_html(get_the_title($sib)); ?> &mdash; <?php echo esc_html($n); ?></div>
            <div class="more-spine-author"><?php echo esc_html($series); ?></div>
            <div class="more-spine-ornament">&#9670; &#9670;</div>
          </a>
        <?php endforeach; ?>
        <div class="series-shelf-base"></div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * ALSO BY CODA — cross-promo grid of other books
 * Excludes current book and same-series siblings.
 * ============================================================ */
function ht_render_also_by($attributes = []) {
    $current_id     = get_the_ID();
    $current_series = $current_id ? get_field('series', $current_id) : '';
    $exclude_ids    = $current_id ? [$current_id] : [];

    if ($current_series) {
        $sibling_ids = get_posts([
            'post_type'      => 'book',
            'posts_per_page' => -1,
            'meta_query'     => [['key' => 'series', 'value' => $current_series]],
            'fields'         => 'ids',
        ]);
        $exclude_ids = array_merge($exclude_ids, $sibling_ids);
    }

    $others = get_posts([
        'post_type'      => 'book',
        'posts_per_page' => 4,
        'post_status'    => 'publish',
        'post__not_in'   => array_unique($exclude_ids),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    if (empty($others)) return '';

    ob_start(); ?>
    <section class="also-by-section">
      <div class="section-header">
        <h2 class="section-title">Also by <?php bloginfo('name'); ?></h2>
        <div class="section-meta">Other haunted infrastructure</div>
      </div>
      <div class="also-by-grid">
        <?php foreach ($others as $b):
            $b_series  = get_field('series', $b->ID);
            $b_blurb   = get_field('blurb',  $b->ID);
            $tag       = $b_series ?: 'Standalone';
            $tagline   = $b_blurb ? wp_trim_words($b_blurb, 18, '…') : '';
            $b_cover   = get_field('cover', $b->ID);
            $cover_url = (is_array($b_cover) && !empty($b_cover['url'])) ? $b_cover['url']
                       : (has_post_thumbnail($b->ID) ? get_the_post_thumbnail_url($b->ID, 'medium') : '');
        ?>
          <a href="<?php echo esc_url(get_permalink($b)); ?>" data-open-book="<?php echo esc_attr($b->post_name); ?>" class="also-by-card">
            <div class="also-by-cover" style="<?php echo $cover_url ? 'padding:0;' : ''; ?>">
              <?php if ($cover_url): ?>
                <img src="<?php echo esc_url($cover_url); ?>" alt="<?php echo esc_attr(get_the_title($b)); ?>" style="display:block;width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                <div class="also-by-cover-title"><?php echo esc_html(get_the_title($b)); ?></div>
              <?php endif; ?>
            </div>
            <div class="ab-meta">
              <div class="ab-tag"><?php echo esc_html($tag); ?></div>
              <div class="ab-title"><?php echo esc_html(get_the_title($b)); ?></div>
              <?php if ($tagline): ?><div class="ab-tagline"><?php echo esc_html($tagline); ?></div><?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * COMPOSED MODAL CONTENT — used by REST endpoint + single-book template.
 * ============================================================ */
function ht_render_book_modal_content() {
    return ht_render_single_book()
         . ht_render_book_excerpt()
         . ht_render_book_more_in_series()
         . ht_render_also_by();
}

/* ============================================================
 * BOOK MODAL — singleton shell, populated via REST on click
 * ============================================================ */
function ht_render_book_modal_shell($attributes = []) {
    ob_start(); ?>
    <div class="book-modal" id="book-modal" role="dialog" aria-modal="true" aria-hidden="true">
      <div class="book-modal-frame">
        <div class="book-modal-topbar">
          <div class="book-modal-breadcrumb">
            <a href="<?php echo esc_url(home_url('/#books')); ?>">Books</a> <span>&rsaquo;</span> <span id="book-modal-title">…</span>
          </div>
          <button class="book-modal-close" aria-label="Close book">&times;</button>
        </div>
        <div class="book-modal-body" id="book-modal-body"><!-- populated via REST --></div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}

/* ============================================================
 * WEB NOVEL MODAL — same shell pattern, REST-fetched
 * ============================================================ */
function ht_render_webnovel_modal_shell($attributes = []) {
    ob_start(); ?>
    <div class="book-modal webnovel-modal" id="webnovel-modal" role="dialog" aria-modal="true" aria-hidden="true">
      <div class="book-modal-frame">
        <div class="book-modal-topbar">
          <div class="book-modal-breadcrumb">
            <a href="<?php echo esc_url(home_url('/#web-novels')); ?>">Web Novels</a> <span>&rsaquo;</span> <span id="webnovel-modal-title">…</span>
          </div>
          <button class="book-modal-close" data-close-webnovel aria-label="Close web novel">&times;</button>
        </div>
        <div class="book-modal-body" id="webnovel-modal-body"></div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * "Also by Coda" for a web novel — pulls 4 other web novels, excludes current.
 */
function ht_render_also_by_webnovels($attributes = []) {
    $current_id = get_the_ID();
    $others = get_posts([
        'post_type'      => 'webnovel',
        'posts_per_page' => 4,
        'post_status'    => 'publish',
        'post__not_in'   => $current_id ? [$current_id] : [],
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    if (empty($others)) return '';
    ob_start(); ?>
    <section class="also-by-section">
      <div class="section-header">
        <h2 class="section-title">Other Serials</h2>
        <div class="section-meta">More live channels</div>
      </div>
      <div class="also-by-grid">
        <?php foreach ($others as $wn):
            $status  = get_field('status', $wn->ID) ?: 'ongoing';
            $tagline = get_field('tagline', $wn->ID);
        ?>
          <a href="<?php echo esc_url(get_permalink($wn)); ?>" data-open-webnovel="<?php echo esc_attr($wn->post_name); ?>" class="also-by-card">
            <div class="also-by-cover">
              <div class="also-by-cover-title"><?php echo esc_html(get_the_title($wn)); ?></div>
            </div>
            <div class="ab-meta">
              <div class="ab-tag"><?php echo esc_html(ucfirst($status)); ?></div>
              <div class="ab-title"><?php echo esc_html(get_the_title($wn)); ?></div>
              <?php if ($tagline): ?><div class="ab-tagline"><?php echo esc_html($tagline); ?></div><?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php
    return ob_get_clean();
}

function ht_render_webnovel_modal_content() {
    return ht_render_single_webnovel()
         . ht_render_also_by_webnovels();
}

/* ============================================================
 * BACK TO TOP — floating arrow
 * ============================================================ */
function ht_render_back_to_top($attributes = []) {
    return '<a href="#top" class="back-to-top" id="back-to-top" aria-label="Back to top" title="Back to top">&uarr;</a>';
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
    <footer class="block-footer" id="footer">
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


/* ============================================================
 * LINKTREE — single-page bio-link layout: avatar + bio + stacked
 * link cards for every published Book, Web Novel, and a social-bar
 * footer. Drop the haunted-tech/linktree block on any WP page.
 * ============================================================ */
function ht_render_linktree($attributes = []) {
    $books = get_posts([
        'post_type'      => 'book',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    $webnovels = get_posts([
        'post_type'      => 'webnovel',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    $bio = get_bloginfo('description');
    ob_start(); ?>
    <section class="block-linktree">
      <div class="linktree-card">

        <div class="linktree-header">
          <img class="linktree-avatar" src="<?php echo esc_url(haunted_tech_logo_url()); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
          <h1 class="linktree-name" data-text="<?php echo esc_attr(get_bloginfo('name')); ?>"><?php bloginfo('name'); ?></h1>
          <?php if ($bio): ?><p class="linktree-bio"><?php echo esc_html($bio); ?></p><?php endif; ?>
        </div>

        <?php if (!empty($books)): ?>
          <div class="linktree-section">
            <h2 class="linktree-section-title">Books</h2>
            <div class="linktree-stack">
              <?php foreach ($books as $b):
                  $cover = get_field('cover', $b->ID);
                  $cover_url = (is_array($cover) && !empty($cover['url'])) ? $cover['url']
                             : (has_post_thumbnail($b->ID) ? get_the_post_thumbnail_url($b->ID, 'medium') : '');
                  $series  = get_field('series', $b->ID);
                  $tag     = get_field('tagline', $b->ID);
                  $sub     = $tag ? wp_trim_words($tag, 14, "\xE2\x80\xA6") : '';
                  $is_free = (bool) get_field('download_url', $b->ID);
              ?>
                <a href="<?php echo esc_url(get_permalink($b)); ?>" class="linktree-tile linktree-tile--book<?php echo $is_free ? ' linktree-tile--free' : ''; ?>">
                  <div class="linktree-tile-cover">
                    <?php if ($cover_url): ?>
                      <img src="<?php echo esc_url($cover_url); ?>" alt="" loading="lazy">
                    <?php else: ?>
                      <span>&#9670;</span>
                    <?php endif; ?>
                  </div>
                  <div class="linktree-tile-body">
                    <?php if ($is_free): ?><div class="linktree-tile-eyebrow linktree-tile-eyebrow--free">Free Download</div>
                    <?php elseif ($series): ?><div class="linktree-tile-eyebrow"><?php echo esc_html($series); ?></div><?php endif; ?>
                    <div class="linktree-tile-title"><?php echo esc_html(get_the_title($b)); ?></div>
                    <?php if ($sub): ?><div class="linktree-tile-sub"><?php echo esc_html($sub); ?></div><?php endif; ?>
                  </div>
                  <div class="linktree-tile-cta">&rarr;</div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if (!empty($webnovels)): ?>
          <div class="linktree-section">
            <h2 class="linktree-section-title">Web Novels</h2>
            <div class="linktree-stack">
              <?php foreach ($webnovels as $wn):
                  $cover = get_field('cover', $wn->ID);
                  $cover_url = (is_array($cover) && !empty($cover['url'])) ? $cover['url']
                             : (has_post_thumbnail($wn->ID) ? get_the_post_thumbnail_url($wn->ID, 'medium') : '');
                  $tag = get_field('tagline', $wn->ID) ?: get_field('blurb', $wn->ID);
                  $sub = $tag ? wp_trim_words($tag, 14, "\xE2\x80\xA6") : '';
                  $status = get_field('status', $wn->ID);
              ?>
                <a href="<?php echo esc_url(get_permalink($wn)); ?>" class="linktree-tile linktree-tile--webnovel">
                  <div class="linktree-tile-cover">
                    <?php if ($cover_url): ?>
                      <img src="<?php echo esc_url($cover_url); ?>" alt="" loading="lazy">
                    <?php else: ?>
                      <span>&#9998;</span>
                    <?php endif; ?>
                  </div>
                  <div class="linktree-tile-body">
                    <?php if ($status): ?><div class="linktree-tile-eyebrow"><?php echo esc_html(ucfirst((string)$status)); ?></div><?php endif; ?>
                    <div class="linktree-tile-title"><?php echo esc_html(get_the_title($wn)); ?></div>
                    <?php if ($sub): ?><div class="linktree-tile-sub"><?php echo esc_html($sub); ?></div><?php endif; ?>
                  </div>
                  <div class="linktree-tile-cta">&rarr;</div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if (has_nav_menu('social')): ?>
          <div class="linktree-section linktree-section--social">
            <h2 class="linktree-section-title">Follow</h2>
            <ul class="linktree-social">
              <?php wp_nav_menu([
                  'theme_location' => 'social',
                  'container'      => false,
                  'items_wrap'     => '%3$s',
                  'walker'         => new Haunted_Tech_Social_Walker(),
                  'fallback_cb'    => false,
              ]); ?>
            </ul>
          </div>
        <?php endif; ?>

      </div>
    </section>
    <?php
    return ob_get_clean();
}
