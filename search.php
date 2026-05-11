<?php
/**
 * Search results.
 *
 * @package HauntedTech
 */
get_header(); ?>

<main class="ht-main" style="max-width:1100px;margin:0 auto;padding:5rem 2rem;">
  <header class="section-header" style="margin-bottom:3rem;">
    <div style="font-family:'Forum',serif;font-size:0.75rem;letter-spacing:0.4em;color:var(--red);text-transform:uppercase;margin-bottom:0.6rem;">&#9670; Search Results &#9670;</div>
    <h1 class="section-title" style="font-size:1.8rem;">&ldquo;<?php echo esc_html(get_search_query()); ?>&rdquo;</h1>
    <div class="section-meta"><?php echo (int)$wp_query->found_posts; ?> result<?php echo $wp_query->found_posts === 1 ? '' : 's'; ?></div>
  </header>

  <?php get_search_form(); ?>

  <?php if (have_posts()): ?>
    <div style="margin-top:3rem;">
    <?php while (have_posts()): the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="margin-bottom:2.5rem;padding-bottom:2rem;border-bottom:1px solid var(--border-dim);">
        <div style="font-family:'Forum',serif;font-size:0.7rem;letter-spacing:0.3em;color:var(--red);text-transform:uppercase;margin-bottom:0.4rem;">&#9670; <?php echo esc_html(get_post_type_object(get_post_type())->labels->singular_name); ?></div>
        <h2 style="font-family:'Forum',serif;color:var(--gold);text-transform:uppercase;letter-spacing:0.06em;font-size:1.4rem;margin-bottom:0.6rem;">
          <a href="<?php the_permalink(); ?>" style="color:inherit;text-decoration:none;"><?php the_title(); ?></a>
        </h2>
        <div style="font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone-dim);"><?php the_excerpt(); ?></div>
      </article>
    <?php endwhile; ?>
    </div>
    <nav style="text-align:center;margin-top:3rem;"><?php the_posts_pagination(['prev_text'=>'&larr;', 'next_text'=>'&rarr;']); ?></nav>
  <?php else: ?>
    <p style="text-align:center;font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone-dim);margin-top:3rem;">No transmissions found. Try a different signal.</p>
  <?php endif; ?>
</main>

<?php get_footer(); ?>
