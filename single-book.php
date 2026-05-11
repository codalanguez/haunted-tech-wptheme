<?php
/**
 * Single Book template — renders one book post with its ACF fields.
 *
 * @package HauntedTech
 */
get_header();

while (have_posts()): the_post();
    $post_id  = get_the_ID();
    $subtitle = get_field('subtitle', $post_id);
    $series   = get_field('series', $post_id);
    $series_n = get_field('series_number', $post_id);
    $blurb    = get_field('blurb', $post_id);
    $genre    = get_field('genre', $post_id);
    $isbn     = get_field('isbn', $post_id);
    $asin     = get_field('asin', $post_id);
    $pages    = get_field('page_count', $post_id);
    $pub_date = get_field('publish_date', $post_id);
    $cover    = get_field('cover', $post_id);
    $amazon   = get_field('buy_amazon', $post_id);
    $bn       = get_field('buy_bn', $post_id);
    $kobo     = get_field('buy_kobo', $post_id);
    $apple    = get_field('buy_apple', $post_id);
    $ku       = get_field('kindle_unlimited', $post_id);

    /* If ACF cover is missing, fall back to featured image, then to a CSS gradient placeholder. */
    $cover_url = '';
    if (is_array($cover) && !empty($cover['url'])) {
        $cover_url = $cover['url'];
    } elseif (has_post_thumbnail()) {
        $cover_url = get_the_post_thumbnail_url($post_id, 'large');
    }

    /* Build Amazon URL from ASIN if buy_amazon is empty */
    if (!$amazon && $asin) {
        $amazon = 'https://www.amazon.com/dp/' . urlencode($asin);
    }
?>

<main class="ht-main" style="max-width:1200px;margin:0 auto;padding:5rem 2rem;">
  <article id="post-<?php echo (int)$post_id; ?>" <?php post_class('ht-book single-book'); ?> style="display:grid;grid-template-columns:1fr 1.4fr;gap:4rem;">

    <div class="book-cover-hero" style="background:var(--obsidian);border:1px solid var(--border);padding:0;position:relative;aspect-ratio:2/3;">
      <?php if ($cover_url): ?>
        <img src="<?php echo esc_url($cover_url); ?>" alt="<?php the_title_attribute(); ?>" style="display:block;width:100%;height:100%;object-fit:cover;">
      <?php else: ?>
        <div class="book-cover" style="height:100%;">
          <?php echo esc_html(get_the_title()); ?>
        </div>
      <?php endif; ?>
      <?php if ($ku): ?>
        <div style="position:absolute;top:1rem;right:1rem;background:var(--gold);color:var(--void);font-family:'Forum',serif;font-size:0.7rem;letter-spacing:0.3em;text-transform:uppercase;padding:0.5rem 0.8rem;text-shadow:none;">
          &#9670; Kindle Unlimited
        </div>
      <?php endif; ?>
    </div>

    <div class="book-meta-hero">
      <?php if ($series): ?>
        <div style="font-family:'Forum',serif;font-size:0.75rem;letter-spacing:0.4em;color:var(--red);text-transform:uppercase;margin-bottom:0.75rem;text-shadow:0 0 6px rgba(229,9,20,0.4);">
          &#9670; <?php echo esc_html($series); ?><?php if ($series_n): ?> &middot; Book <?php echo (int)$series_n; ?><?php endif; ?>
        </div>
      <?php endif; ?>
      <h1 data-text="<?php the_title_attribute(); ?>" style="font-family:'Forum',serif;font-size:clamp(2rem,5vw,3.5rem);color:var(--gold);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.5rem;text-shadow:0 0 24px rgba(255,212,0,0.4);"><?php the_title(); ?></h1>
      <?php if ($subtitle): ?>
        <div style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1.3rem;color:var(--bone-dim);margin-bottom:2rem;"><?php echo esc_html($subtitle); ?></div>
      <?php endif; ?>

      <div class="book-detail-row" style="display:flex;gap:1.5rem;font-family:'Inter',sans-serif;font-size:0.8rem;letter-spacing:0.2em;color:var(--bone-dim);text-transform:uppercase;margin-bottom:2rem;flex-wrap:wrap;">
        <?php if ($genre): ?><span><?php echo esc_html($genre); ?></span><?php endif; ?>
        <?php if ($pages): ?><span><?php echo (int)$pages; ?> pages</span><?php endif; ?>
        <?php if ($pub_date): ?><span><?php echo esc_html($pub_date); ?></span><?php endif; ?>
        <?php if ($isbn): ?><span>ISBN <?php echo esc_html($isbn); ?></span><?php endif; ?>
      </div>

      <?php if ($blurb): ?>
        <div style="font-family:'Cormorant Garamond',serif;font-size:1.15rem;line-height:1.8;color:var(--bone);margin-bottom:2.5rem;">
          <?php echo wp_kses_post(wpautop($blurb)); ?>
        </div>
      <?php endif; ?>

      <div style="display:flex;gap:0.6rem;flex-wrap:wrap;">
        <?php if ($amazon): ?><a href="<?php echo esc_url($amazon); ?>" class="cta" style="padding:0.7rem 1.4rem;font-size:0.8rem;">Amazon</a><?php endif; ?>
        <?php if ($bn): ?><a href="<?php echo esc_url($bn); ?>" class="cta" style="padding:0.7rem 1.4rem;font-size:0.8rem;">B&amp;N</a><?php endif; ?>
        <?php if ($kobo): ?><a href="<?php echo esc_url($kobo); ?>" class="cta" style="padding:0.7rem 1.4rem;font-size:0.8rem;">Kobo</a><?php endif; ?>
        <?php if ($apple): ?><a href="<?php echo esc_url($apple); ?>" class="cta" style="padding:0.7rem 1.4rem;font-size:0.8rem;">Apple</a><?php endif; ?>
      </div>
    </div>
  </article>

  <?php if (get_the_content()): ?>
    <section style="margin-top:5rem;font-family:'Cormorant Garamond',serif;font-size:1.1rem;line-height:1.8;color:var(--bone);">
      <?php the_content(); ?>
    </section>
  <?php endif; ?>
</main>

<?php endwhile;
get_footer(); ?>
