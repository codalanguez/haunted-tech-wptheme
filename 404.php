<?php
/**
 * 404 — page-not-found template.
 *
 * @package HauntedTech
 */
get_header(); ?>

<main class="ht-main" style="max-width:780px;margin:0 auto;padding:8rem 2rem;text-align:center;min-height:60vh;">
  <div style="font-family:'Forum',serif;font-size:0.85rem;letter-spacing:0.7em;color:var(--red);text-transform:uppercase;margin-bottom:2rem;">
    &#9670; Signal Lost &#9670;
  </div>
  <h1 data-text="404" style="font-family:'Forum',serif;font-size:clamp(5rem,15vw,10rem);line-height:1;color:var(--gold);text-shadow:0 0 32px rgba(255,212,0,0.5);position:relative;display:inline-block;animation:glitch-tear 2.4s infinite;">
    <span style="color:var(--gold);">404</span>
  </h1>
  <p style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:1.3rem;color:var(--bone);margin:2rem 0 3rem;">The page you're looking for has been redacted, encrypted, or never existed in this timeline.</p>
  <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="cta">Return to Signal</a>
    <a href="<?php echo esc_url(home_url('/#newsletter')); ?>" class="cta">Subscribe Anyway</a>
  </div>
</main>

<?php get_footer(); ?>
