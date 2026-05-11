<?php
/**
 * Default page template.
 *
 * @package HauntedTech
 */
get_header(); ?>

<main class="ht-main" style="max-width:900px;margin:0 auto;padding:5rem 2rem;">
  <?php while (have_posts()): the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class('ht-page'); ?>>
      <header class="section-header" style="margin-bottom:3rem;">
        <h1 class="section-title"><?php the_title(); ?></h1>
      </header>
      <div class="ht-content" style="font-family:'Cormorant Garamond',serif;font-size:1.15rem;line-height:1.8;color:var(--bone);">
        <?php the_content(); ?>
      </div>
    </article>
  <?php endwhile; ?>
</main>

<?php get_footer(); ?>
