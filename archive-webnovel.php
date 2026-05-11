<?php
/**
 * Web Novel archive — CRT monitor styled listing.
 *
 * @package HauntedTech
 */
get_header(); ?>

<main class="ht-main" style="max-width:1300px;margin:0 auto;padding:5rem 2rem;">
  <section class="crt-section block-crt-monitor">
    <div class="section-header">
      <h2 class="section-title">All Web Novels</h2>
      <div class="section-meta">Complete Channel Log &mdash; <?php echo (int)$wp_query->found_posts; ?> Title<?php echo $wp_query->found_posts === 1 ? '' : 's'; ?></div>
    </div>
    <div class="crt-monitor">
      <div class="crt-screw-bl"></div>
      <div class="crt-screw-br"></div>
      <div class="crt-screen">
        <div class="crt-prompt"><span class="user">coda@haunted-tech</span>:<span class="path">~/webnovels</span>$ ls -la --all --status</div>
        <div class="crt-list">
          <?php if (have_posts()): ?>
            <?php while (have_posts()): the_post();
                $wn_id  = get_the_ID();
                $status = get_field('status', $wn_id) ?: 'ongoing';
                $genre  = get_field('genre',  $wn_id) ?: '';
                $total  = get_field('total_chapters', $wn_id);
                $chapter_count = count(get_posts([
                    'post_type'      => 'chapter',
                    'posts_per_page' => -1,
                    'meta_query'     => [[ 'key'=>'webnovel', 'value'=>$wn_id ]],
                    'fields'         => 'ids',
                ]));
                $status_dot   = ['ongoing'=>'&#9679;', 'complete'=>'&#10003;', 'hiatus'=>'&#9711;', 'planned'=>'&#9633;', 'discontinued'=>'&#10007;'][$status] ?? '&#9679;';
                $status_class = in_array($status, ['ongoing','complete','hiatus'], true) ? $status : 'ongoing';
                $slug         = sanitize_title(get_the_title()) . '/';
            ?>
              <div class="crt-row">
                <div class="crt-status <?php echo esc_attr($status_class); ?>"><?php echo $status_dot; ?></div>
                <a class="crt-title" href="<?php the_permalink(); ?>"><?php echo esc_html($slug); ?></a>
                <div class="crt-tag">[<?php echo esc_html(strtoupper($genre)); ?>]</div>
                <div class="crt-meta">ch <?php echo (int)$chapter_count; ?> / <?php echo $total ? (int)$total : '??'; ?></div>
                <div class="crt-state <?php echo esc_attr($status_class); ?>"><?php echo esc_html(strtoupper($status)); ?></div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="crt-row">
              <div class="crt-status">&#9679;</div>
              <div class="crt-title">no_webnovels/</div>
              <div class="crt-tag">[EMPTY]</div>
              <div class="crt-meta">--</div>
              <div class="crt-state">WAITING</div>
            </div>
          <?php endif; ?>
        </div>
        <div style="margin-top:1.5rem;position:relative;z-index:2;">
          <span class="user" style="color:var(--gold)">coda@haunted-tech</span>:<span class="path" style="color:var(--bone)">~/webnovels</span>$ <span class="crt-cursor">&#9608;</span>
        </div>
      </div>
      <div class="crt-led"></div>
      <div class="crt-brand">CODA-OS v.0xDEAD</div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
