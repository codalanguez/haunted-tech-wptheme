<?php
/**
 * Homepage template — assembles all the section blocks.
 *
 *   Hero slider (dynamic, pulls 3 most-recent hero_update posts)
 *   Bookshelf   (dynamic, pulls book CPT)
 *   CRT monitor (dynamic, pulls webnovel CPT)
 *   Services    (static — 3 cards)
 *   Gallery     (static masonry — items in a future iteration become a CPT)
 *   Newsletter  (static form — connect to your provider later)
 *
 * @package HauntedTech
 */

get_header();

/* ============================================================
 * HERO SLIDER — pull 3 most recent hero_update entries
 * ============================================================ */
$hero_slides = haunted_tech_get_hero_slides(3);
?>

<!-- ===== BLOCK: HERO SLIDER ===== -->
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
      <!-- Fallback slides shown when no hero_update posts exist yet. Create posts in WP Admin → Hero Updates to replace. -->
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
<!-- ===== /BLOCK: HERO SLIDER ===== -->


<?php
/* ============================================================
 * BOOKSHELF — pull all published books
 * ============================================================ */
$books = get_posts([
    'post_type'      => 'book',
    'posts_per_page' => 12,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post_status'    => 'publish',
]);
/* Cycle through spine-binding variants for visual variety */
$spine_variants = ['oxblood', 'teal', 'obsidian', 'charcoal', 'gold'];
?>

<!-- ===== BLOCK: BOOKSHELF ===== -->
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
            $variant = $spine_variants[$i % count($spine_variants)];
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
<!-- ===== /BLOCK: BOOKSHELF ===== -->


<?php
/* ============================================================
 * CRT MONITOR — pull all webnovels with computed status
 * ============================================================ */
$webnovels = get_posts([
    'post_type'      => 'webnovel',
    'posts_per_page' => 8,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post_status'    => 'publish',
]);
?>

<!-- ===== BLOCK: CRT MONITOR ===== -->
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
              /* Count chapters for this webnovel */
              $chapter_count = count(get_posts([
                  'post_type'      => 'chapter',
                  'posts_per_page' => -1,
                  'meta_query'     => [[
                      'key'   => 'webnovel',
                      'value' => $wn->ID,
                  ]],
                  'fields'         => 'ids',
              ]));
              $status_dot   = ['ongoing'=>'&#9679;', 'complete'=>'&#10003;', 'hiatus'=>'&#9711;', 'planned'=>'&#9633;', 'discontinued'=>'&#10007;'][$status] ?? '&#9679;';
              $status_class = in_array($status, ['ongoing','complete','hiatus'], true) ? $status : 'ongoing';
              $state_label  = strtoupper($status);
              $slug         = sanitize_title(get_the_title($wn)) . '/';
          ?>
          <div class="crt-row">
            <div class="crt-status <?php echo esc_attr($status_class); ?>"><?php echo $status_dot; ?></div>
            <a class="crt-title" href="<?php echo esc_url(get_permalink($wn)); ?>"><?php echo esc_html($slug); ?></a>
            <div class="crt-tag">[<?php echo esc_html(strtoupper($genre)); ?>]</div>
            <div class="crt-meta">ch <?php echo (int)$chapter_count; ?> / <?php echo $total ? (int)$total : '??'; ?></div>
            <div class="crt-state <?php echo esc_attr($status_class); ?>"><?php echo esc_html($state_label); ?></div>
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
<!-- ===== /BLOCK: CRT MONITOR ===== -->


<!-- ===== BLOCK: SERVICES ===== -->
<section class="block-services" id="services">
  <div class="section-header">
    <h2 class="section-title">Services</h2>
    <div class="section-meta">Commissions Open &mdash; Limited Slots</div>
  </div>
  <div class="services-grid">
    <div class="service-card">
      <div class="service-icon-area">
        <div class="service-icon">&#10048;</div>
      </div>
      <div class="service-meta">
        <div class="service-eyebrow">Bespoke</div>
        <div class="service-title">Art Commissions</div>
        <div class="service-desc">Original character art, cyber-gothic portraits, and scene illustrations. Hand-drawn with neon-glitch finish. Personal or commercial licenses available.</div>
        <a href="#gallery-art" class="service-btn">View Portfolio</a>
      </div>
    </div>
    <div class="service-card">
      <div class="service-icon-area">
        <div class="service-icon">&#10065;</div>
      </div>
      <div class="service-meta">
        <div class="service-eyebrow">Premade &amp; Custom</div>
        <div class="service-title">Book Cover Design</div>
        <div class="service-desc">Full-wrap cover design for dark romance, horror, and cyberpunk fiction. Includes ebook, paperback, hardcover layouts plus branded series styling.</div>
        <a href="#gallery-covers" class="service-btn">View Portfolio</a>
      </div>
    </div>
    <div class="service-card">
      <div class="service-icon-area">
        <div class="service-icon">&#9635;</div>
      </div>
      <div class="service-meta">
        <div class="service-eyebrow">AI-Assisted</div>
        <div class="service-title">AI Image Generation</div>
        <div class="service-desc">Custom AI-generated character art, mood boards, and chapter banners. Flux + SDXL workflows. Final pieces are post-processed and finished by hand.</div>
        <a href="#gallery-ai" class="service-btn">View Portfolio</a>
      </div>
    </div>
  </div>
</section>
<!-- ===== /BLOCK: SERVICES ===== -->


<!-- ===== BLOCK: GALLERY (placeholder content — replace with CPT-driven items later) ===== -->
<?php
/*
 * The full gallery section from the mockup is preserved here as static HTML.
 * In a future iteration, register a `gallery_item` CPT with fields:
 *   service_tab (art|covers|ai), tag, title, description, image, ratio
 * and replace this block with a WP_Query loop.
 */
get_template_part('parts/gallery-static');
?>


<!-- ===== BLOCK: NEWSLETTER ===== -->
<section class="newsletter block-newsletter" id="newsletter">
  <div class="newsletter-corner tl"></div>
  <div class="newsletter-corner tr"></div>
  <div class="newsletter-corner bl"></div>
  <div class="newsletter-corner br"></div>
  <div class="newsletter-content">
    <div class="newsletter-eyebrow">Encrypted Channel</div>
    <h2 data-text="JOIN THE SIGNAL">JOIN THE <span class="accent">SIGNAL</span></h2>
    <p>Early chapter drops, free shorts, exclusive art, and the occasional voice memo from the static. No spam, ever &mdash; just signal.</p>
    <?php
    /*
     * Drop your newsletter provider's embed/form here (Mailchimp, ConvertKit, Substack, etc.).
     * Until then, this is a non-functional placeholder.
     */
    ?>
    <form class="newsletter-form" onsubmit="return false;">
      <input type="email" class="newsletter-input" placeholder="your.handle@encrypted.net" required>
      <button type="submit" class="newsletter-submit">Subscribe</button>
    </form>
    <div class="newsletter-fine">Unsubscribe anytime &middot; PGP key on request</div>
  </div>
</section>
<!-- ===== /BLOCK: NEWSLETTER ===== -->

<?php get_footer(); ?>
