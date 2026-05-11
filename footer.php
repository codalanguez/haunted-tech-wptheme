<?php
/**
 * Site footer + global modal/lightbox markup.
 *
 * @package HauntedTech
 */
?>

<!-- ===== BLOCK: LIGHTBOX (singleton, used by the gallery JS) ===== -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-labelledby="lightbox-title" aria-hidden="true">
  <div class="lightbox-frame">
    <button class="lightbox-nav prev" aria-label="Previous">&larr;</button>
    <button class="lightbox-nav next" aria-label="Next">&rarr;</button>
    <button class="lightbox-close" aria-label="Close">&times;</button>
    <div class="lightbox-brand" aria-hidden="true">
      <img src="<?php echo esc_url(haunted_tech_logo_url()); ?>" alt="">
      <span class="lightbox-brand-text"><?php bloginfo('name'); ?></span>
    </div>
    <div class="lightbox-image" id="lightbox-image"><span class="gallery-image-label" id="lightbox-image-label"></span></div>
    <div class="lightbox-meta">
      <div class="lightbox-tag" id="lightbox-tag"></div>
      <div class="lightbox-title" id="lightbox-title"></div>
      <div class="lightbox-divider"></div>
      <div class="lightbox-desc" id="lightbox-desc"></div>
      <a href="#" class="lightbox-cta">Inquire</a>
    </div>
  </div>
</div>
<!-- ===== /BLOCK: LIGHTBOX ===== -->

<?php
/**
 * About modal — pulls bio from the page named "About" if it exists,
 * otherwise from a fallback hard-coded blurb.
 */
$about_page = get_page_by_path('about');
$about_bio  = $about_page ? apply_filters('the_content', $about_page->post_content) : null;
$portrait   = HAUNTED_TECH_URI . '/assets/coda-portrait.png';
if ($about_page) {
    $thumb_id = get_post_thumbnail_id($about_page->ID);
    if ($thumb_id) {
        $src = wp_get_attachment_image_src($thumb_id, 'large');
        if ($src) $portrait = $src[0];
    }
}
?>

<!-- ===== BLOCK: ABOUT MODAL ===== -->
<div class="about-modal" id="about-modal" role="dialog" aria-modal="true" aria-labelledby="about-name" aria-hidden="true">
  <div class="about-frame">
    <button class="about-close" aria-label="Close about">&times;</button>
    <div class="about-portrait" style="background-image: url('<?php echo esc_url($portrait); ?>');">
      <?php if (!$about_page): ?>
        <div class="about-portrait-fallback">Save your portrait to <code style="color:var(--gold)">assets/coda-portrait.png</code></div>
      <?php endif; ?>
    </div>
    <div class="about-meta">
      <div class="about-meta-head">
        <div class="about-eyebrow">About the Author</div>
        <h2 class="about-name" id="about-name" data-text="<?php echo esc_attr(get_bloginfo('name')); ?>"><?php bloginfo('name'); ?></h2>
        <div class="about-title">Software Engineer &middot; Author &middot; Geek Overlord</div>
        <div class="about-divider"></div>
      </div>
      <div class="about-bio-wrap">
        <div class="about-bio">
          <?php if ($about_bio): ?>
            <?php echo $about_bio; ?>
          <?php else: ?>
            <p>Edit this content by creating a Page with the slug <code>about</code> in WP Admin.</p>
          <?php endif; ?>
        </div>
      </div>
      <div class="about-meta-foot">
        <div class="about-handle">@codalanguez</div>
      </div>
    </div>
  </div>
</div>
<!-- ===== /BLOCK: ABOUT MODAL ===== -->

<!-- ===== BLOCK: FOOTER ===== -->
<footer class="block-footer">
  <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo" aria-label="<?php bloginfo('name'); ?>">
    <img src="<?php echo esc_url(haunted_tech_logo_url()); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?> logo">
  </a>
  <div class="ornament"><span>&#9670;</span> <span>&#9670;</span> <span>&#9670;</span></div>
  <div class="links">
    <?php
    if (has_nav_menu('footer')) {
        wp_nav_menu([
            'theme_location' => 'footer',
            'container'      => false,
            'items_wrap'     => '%3$s',
            'fallback_cb'    => false,
            'depth'          => 1,
        ]);
    } else {
        echo '<a href="#">Patreon</a><a href="#">Ream</a><a href="#">Substack</a><a href="#">Amazon</a><a href="#newsletter">Newsletter</a>';
    }
    ?>
  </div>
  <div class="copy">&copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?></div>
  <div class="theme-name">Haunted Tech</div>
</footer>
<!-- ===== /BLOCK: FOOTER ===== -->

<?php wp_footer(); ?>
</body>
</html>
