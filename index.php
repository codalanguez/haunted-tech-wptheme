<?php
/**
 * Fallback template — used when no more specific template matches.
 * Lists posts in the standard blog format.
 *
 * @package HauntedTech
 */
get_header(); ?>

<main class="ht-main" style="max-width:1100px;margin:0 auto;padding:4rem 2rem;">
  <?php if (have_posts()): ?>
    <?php while (have_posts()): the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class('ht-post'); ?> style="margin-bottom:4rem;border-bottom:1px solid var(--border-dim);padding-bottom:3rem;">
        <h2 style="font-family:'Forum',serif;color:var(--gold);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.5rem;">
          <a href="<?php the_permalink(); ?>" style="color:inherit;text-decoration:none;"><?php the_title(); ?></a>
        </h2>
        <div style="font-family:'Inter',sans-serif;font-size:0.8rem;letter-spacing:0.2em;color:var(--bone-dim);text-transform:uppercase;margin-bottom:1.5rem;">
          <?php echo esc_html(get_the_date()); ?>
        </div>
        <div class="ht-excerpt" style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;line-height:1.7;">
          <?php the_excerpt(); ?>
        </div>
        <a href="<?php the_permalink(); ?>" class="cta" style="margin-top:1.5rem;display:inline-flex;">Read More</a>
      </article>
    <?php endwhile; ?>

    <nav class="ht-pagination" style="text-align:center;margin-top:3rem;">
      <?php the_posts_pagination([
          'prev_text' => '&larr;',
          'next_text' => '&rarr;',
          'class'     => 'gallery-page-indicator',
      ]); ?>
    </nav>
  <?php else: ?>
    <p style="text-align:center;font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone-dim);">Nothing here yet.</p>
  <?php endif; ?>
</main>

<?php get_footer(); ?>
