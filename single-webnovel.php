<?php
/**
 * Single Web Novel template — series page with chapter list.
 *
 * @package HauntedTech
 */
get_header();

while (have_posts()): the_post();
    $wn_id    = get_the_ID();
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
    elseif (has_post_thumbnail()) $cover_url = get_the_post_thumbnail_url($wn_id, 'large');

    /* Fetch all chapters belonging to this web novel, ordered by chapter_number */
    $chapters = get_posts([
        'post_type'      => 'chapter',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => [[
            'key'   => 'webnovel',
            'value' => $wn_id,
        ]],
        'meta_key'       => 'chapter_number',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    ]);
?>

<main class="ht-main" style="max-width:1200px;margin:0 auto;padding:5rem 2rem;">
  <article id="post-<?php echo (int)$wn_id; ?>" <?php post_class('ht-webnovel'); ?>>

    <header class="section-header" style="text-align:left;margin-bottom:3rem;">
      <div style="font-family:'Forum',serif;font-size:0.75rem;letter-spacing:0.4em;color:var(--red);text-transform:uppercase;margin-bottom:0.75rem;">
        &#9670; Web Novel &middot; <?php echo esc_html(strtoupper($status)); ?>
        <?php if ($sched): ?> &middot; <?php echo esc_html($sched); ?><?php endif; ?>
      </div>
      <h1 data-text="<?php the_title_attribute(); ?>" style="font-family:'Forum',serif;font-size:clamp(2rem,5vw,4rem);color:var(--gold);text-transform:uppercase;letter-spacing:0.06em;text-shadow:0 0 24px rgba(255,212,0,0.4);"><?php the_title(); ?></h1>
      <?php if ($tagline): ?>
        <div style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1.3rem;color:var(--bone);margin-top:0.5rem;"><?php echo esc_html($tagline); ?></div>
      <?php endif; ?>
    </header>

    <?php if ($cover_url || $blurb): ?>
    <div style="display:grid;grid-template-columns:<?php echo $cover_url ? '1fr 2fr' : '1fr'; ?>;gap:3rem;margin-bottom:4rem;">
      <?php if ($cover_url): ?>
        <div style="border:1px solid var(--border-dim);aspect-ratio:2/3;background:var(--obsidian);">
          <img src="<?php echo esc_url($cover_url); ?>" alt="<?php the_title_attribute(); ?>" style="display:block;width:100%;height:100%;object-fit:cover;">
        </div>
      <?php endif; ?>
      <div>
        <?php if ($blurb): ?>
          <div style="font-family:'Cormorant Garamond',serif;font-size:1.15rem;line-height:1.8;color:var(--bone);margin-bottom:2rem;">
            <?php echo wp_kses_post(wpautop($blurb)); ?>
          </div>
        <?php endif; ?>
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
          <div class="chapter-meta">
            <?php echo esc_html(get_the_date('M j', $ch)); ?>
            <?php if ($ch_wc): ?> &middot; <?php echo number_format((int)$ch_wc); ?> words<?php endif; ?>
          </div>
          <div class="chapter-access <?php echo $is_patron ? 'access-patron' : 'access-free'; ?>">
            <?php echo $is_patron ? 'Patron' : 'Free'; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (get_the_content()): ?>
      <section style="margin-top:4rem;font-family:'Cormorant Garamond',serif;font-size:1.1rem;line-height:1.8;color:var(--bone);">
        <?php the_content(); ?>
      </section>
    <?php endif; ?>
  </article>
</main>

<?php endwhile;
get_footer(); ?>
