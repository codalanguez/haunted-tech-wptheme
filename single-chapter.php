<?php
/**
 * Single Chapter template — reading view with prev/next nav.
 *
 * @package HauntedTech
 */
get_header();

while (have_posts()): the_post();
    $ch_id     = get_the_ID();
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

    /* Compute prev/next chapter by chapter_number within the same web novel,
       unless the author has manually overridden via the prev_chapter / next_chapter fields. */
    $prev = $manual_prev ? (is_object($manual_prev) ? $manual_prev : get_post((int)$manual_prev)) : null;
    $next = $manual_next ? (is_object($manual_next) ? $manual_next : get_post((int)$manual_next)) : null;
    if ((!$prev || !$next) && $wn) {
        $siblings = get_posts([
            'post_type'      => 'chapter',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => [[ 'key'=>'webnovel', 'value'=>$wn->ID ]],
            'meta_key'       => 'chapter_number',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        ]);
        $ids = array_map(function($p){ return $p->ID; }, $siblings);
        $idx = array_search($ch_id, $ids, true);
        if ($idx !== false) {
            if (!$prev && $idx > 0)                       $prev = $siblings[$idx - 1];
            if (!$next && $idx < count($siblings) - 1)    $next = $siblings[$idx + 1];
        }
    }
?>

<main class="ht-main" style="max-width:780px;margin:0 auto;padding:5rem 2rem;">
  <article id="post-<?php echo (int)$ch_id; ?>" <?php post_class('ht-chapter'); ?>>

    <header style="text-align:center;margin-bottom:3rem;">
      <?php if ($wn): ?>
        <a href="<?php echo esc_url(get_permalink($wn)); ?>" style="font-family:'Forum',serif;font-size:0.75rem;letter-spacing:0.4em;color:var(--red);text-transform:uppercase;text-decoration:none;display:inline-block;margin-bottom:1rem;">&#9670; <?php echo esc_html(get_the_title($wn)); ?></a>
      <?php endif; ?>
      <?php if ($arc): ?>
        <div style="font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone-dim);margin-bottom:0.6rem;letter-spacing:0.2em;text-transform:uppercase;font-size:0.85rem;"><?php echo esc_html($arc); ?></div>
      <?php endif; ?>
      <?php if ($ch_num !== null && $ch_num !== ''): ?>
        <div style="font-family:'Forum',serif;font-size:1rem;color:var(--gold);letter-spacing:0.5em;text-transform:uppercase;margin-bottom:0.4rem;">Chapter <?php echo esc_html($ch_num); ?></div>
      <?php endif; ?>
      <h1 data-text="<?php the_title_attribute(); ?>" style="font-family:'Forum',serif;font-size:clamp(1.8rem,4vw,2.8rem);color:var(--gold);text-transform:uppercase;letter-spacing:0.06em;text-shadow:0 0 16px rgba(255,212,0,0.3);"><?php the_title(); ?></h1>
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
      <?php the_content(); ?>
    </div>

    <?php if ($notes): ?>
      <aside style="margin-top:4rem;border-top:1px solid var(--border-dim);padding-top:2rem;font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone-dim);">
        <div style="font-family:'Forum',serif;color:var(--gold);text-transform:uppercase;letter-spacing:0.3em;font-size:0.75rem;font-style:normal;margin-bottom:1rem;">&#9670; Author's Note</div>
        <?php echo wp_kses_post(wpautop($notes)); ?>
      </aside>
    <?php endif; ?>

    <nav style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:5rem;padding-top:2rem;border-top:1px solid var(--border-dim);">
      <?php if ($prev): ?>
        <a href="<?php echo esc_url(get_permalink($prev)); ?>" style="display:block;padding:1.5rem;border:1px solid var(--border-dim);text-decoration:none;text-align:left;transition:all 0.2s;" onmouseover="this.style.borderColor='var(--gold)';" onmouseout="this.style.borderColor='var(--border-dim)';">
          <div style="font-family:'Forum',serif;color:var(--red);font-size:0.7rem;letter-spacing:0.3em;text-transform:uppercase;margin-bottom:0.4rem;">&larr; Previous</div>
          <div style="font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone);"><?php echo esc_html(get_the_title($prev)); ?></div>
        </a>
      <?php else: ?><span></span><?php endif; ?>
      <?php if ($next): ?>
        <a href="<?php echo esc_url(get_permalink($next)); ?>" style="display:block;padding:1.5rem;border:1px solid var(--border-dim);text-decoration:none;text-align:right;transition:all 0.2s;" onmouseover="this.style.borderColor='var(--gold)';" onmouseout="this.style.borderColor='var(--border-dim)';">
          <div style="font-family:'Forum',serif;color:var(--red);font-size:0.7rem;letter-spacing:0.3em;text-transform:uppercase;margin-bottom:0.4rem;">Next &rarr;</div>
          <div style="font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone);"><?php echo esc_html(get_the_title($next)); ?></div>
        </a>
      <?php else: ?><span></span><?php endif; ?>
    </nav>
  </article>
</main>

<?php endwhile;
get_footer(); ?>
