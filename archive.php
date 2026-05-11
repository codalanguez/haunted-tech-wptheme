<?php
/**
 * Generic archive — used for date / author / category / tag archives.
 *
 * @package HauntedTech
 */
get_header(); ?>

<main class="ht-main" style="max-width:1100px;margin:0 auto;padding:5rem 2rem;">
  <header class="section-header" style="margin-bottom:3rem;">
    <h1 class="section-title"><?php the_archive_title(); ?></h1>
    <?php $desc = get_the_archive_description(); if ($desc): ?>
      <div class="section-meta"><?php echo wp_kses_post($desc); ?></div>
    <?php endif; ?>
  </header>

  <?php if (have_posts()): ?>
    <?php while (have_posts()): the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="margin-bottom:4rem;border-bottom:1px solid var(--border-dim);padding-bottom:3rem;">
        <h2 style="font-family:'Forum',serif;color:var(--gold);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.5rem;">
          <a href="<?php the_permalink(); ?>" style="color:inherit;text-decoration:none;"><?php the_title(); ?></a>
        </h2>
        <div style="font-family:'Inter',sans-serif;font-size:0.8rem;letter-spacing:0.2em;color:var(--bone-dim);text-transform:uppercase;margin-bottom:1.5rem;">
          <?php echo esc_html(get_the_date()); ?>
        </div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;line-height:1.7;">
          <?php the_excerpt(); ?>
        </div>
      </article>
    <?php endwhile; ?>
    <nav style="text-align:center;margin-top:3rem;">
      <?php the_posts_pagination(['prev_text'=>'&larr;', 'next_text'=>'&rarr;']); ?>
    </nav>
  <?php else: ?>
    <p style="text-align:center;font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone-dim);">Nothing in this archive yet.</p>
  <?php endif; ?>
</main>

<?php get_footer(); ?>
