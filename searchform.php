<?php
/**
 * Search form.
 *
 * @package HauntedTech
 */
?>
<form role="search" method="get" class="ht-searchform" action="<?php echo esc_url(home_url('/')); ?>" style="display:flex;gap:0.5rem;max-width:540px;margin:0 auto;">
  <input type="search"
         class="newsletter-input"
         placeholder="<?php echo esc_attr_x('search the signal…', 'placeholder', 'haunted-tech'); ?>"
         value="<?php echo get_search_query(); ?>"
         name="s"
         required>
  <button type="submit" class="newsletter-submit"><?php echo esc_html_x('Search', 'submit button', 'haunted-tech'); ?></button>
</form>
