<?php
/**
 * Book archive — bookshelf-style listing of all published books.
 *
 * @package HauntedTech
 */
get_header();
$spine_variants = ['oxblood', 'teal', 'obsidian', 'charcoal', 'gold'];
?>

<main class="ht-main" style="max-width:1300px;margin:0 auto;padding:5rem 2rem;">
  <section class="block-bookshelf">
    <div class="section-header">
      <h2 class="section-title">All Books</h2>
      <div class="section-meta"><?php echo (int)$wp_query->found_posts; ?> Title<?php echo $wp_query->found_posts === 1 ? '' : 's'; ?></div>
    </div>

    <?php if (have_posts()): ?>
      <div class="bookshelf-wrap">
        <div class="bookshelf">
          <?php
          $i = 0;
          while (have_posts()): the_post();
              $variant = $spine_variants[$i % count($spine_variants)];
              $series  = get_field('series') ?: 'Coda Languez';
              $width   = 60 + (($i * 7) % 25);
              $height  = 380 + (($i * 11) % 60);
              $i++;
          ?>
            <a href="<?php the_permalink(); ?>" class="spine <?php echo esc_attr($variant); ?>" style="width:<?php echo (int)$width; ?>px; height:<?php echo (int)$height; ?>px;">
              <div class="spine-ornament">&#9670; &#9670;</div>
              <div class="spine-title"><?php the_title(); ?></div>
              <div class="spine-author"><?php echo esc_html($series); ?></div>
              <div class="spine-ornament">&#9670; &#9670;</div>
            </a>
          <?php endwhile; ?>
        </div>
        <div class="shelf-base"></div>
      </div>
    <?php else: ?>
      <p style="text-align:center;font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--bone-dim);padding:4rem;">No books yet.</p>
    <?php endif; ?>
  </section>
</main>

<?php get_footer(); ?>
