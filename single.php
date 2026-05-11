<?php
/**
 * Default single-post template.
 *
 * @package HauntedTech
 */
get_header(); ?>

<main class="ht-main" style="max-width:900px;margin:0 auto;padding:5rem 2rem;">
  <?php while (have_posts()): the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class('ht-post'); ?>>
      <header class="section-header" style="margin-bottom:3rem;">
        <h1 class="section-title"><?php the_title(); ?></h1>
        <div class="section-meta"><?php echo esc_html(get_the_date('F j, Y')); ?> &middot; <?php echo esc_html(get_the_author()); ?></div>
      </header>
      <?php if (has_post_thumbnail()): ?>
        <div style="margin-bottom:2.5rem;border:1px solid var(--border-dim);">
          <?php the_post_thumbnail('large', ['style' => 'display:block;width:100%;height:auto;']); ?>
        </div>
      <?php endif; ?>
      <div class="ht-content" style="font-family:'Cormorant Garamond',serif;font-size:1.15rem;line-height:1.8;color:var(--bone);">
        <?php the_content(); ?>
      </div>
    </article>
  <?php endwhile; ?>
</main>

<?php get_footer(); ?>
