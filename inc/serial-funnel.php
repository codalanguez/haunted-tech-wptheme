<?php
/**
 * Reader-first web-serial discovery helpers and dynamic blocks.
 *
 * Keeps campaign choices in WordPress rather than hard-coding a particular
 * launch into the theme. When the new fields have not been filled in yet, the
 * featured block falls back to the latest chapter-type Hero Update so an
 * existing site never renders an empty first screen after upgrading.
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

/** Read an ACF-backed value without making the theme fatal when ACF is absent. */
function ht_serial_field($name, $post_id, $default = '') {
    $value = function_exists('get_field') ? get_field($name, $post_id) : get_post_meta($post_id, $name, true);
    return ($value === null || $value === false || $value === '') ? $default : $value;
}

/** Register the campaign fields used by the homepage and web-novel pages. */
add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) { return; }

    acf_add_local_field_group([
        'key'      => 'group_ht_serial_campaign',
        'title'    => 'Web Novel — Reader Path',
        'fields'   => [
            ['key'=>'field_ht_serial_featured', 'label'=>'Featured Serial', 'name'=>'featured_serial', 'type'=>'true_false',
             'instructions'=>'Make this the main serial shown on the homepage. Keep only one serial featured at a time.', 'ui'=>1, 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_gateway', 'label'=>'Show in Choose Your Door', 'name'=>'gateway_serial', 'type'=>'true_false',
             'instructions'=>'Include this serial in the three-card reader gateway.', 'ui'=>1, 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_priority', 'label'=>'Campaign Priority', 'name'=>'campaign_priority', 'type'=>'number',
             'instructions'=>'Lower numbers appear first. Use 1, 2 and 3 for the three reader doors.', 'min'=>1, 'step'=>1, 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_lane', 'label'=>'Reader Lane', 'name'=>'reader_lane', 'type'=>'text',
             'instructions'=>'A short, accurate shelf such as Science-Fiction Monster Romance.', 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_hook', 'label'=>'Campaign Hook', 'name'=>'campaign_hook', 'type'=>'textarea', 'rows'=>3,
             'instructions'=>'The sourced premise shown in discovery placements. Use only details that occur in the story.', 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_gateway_label', 'label'=>'Door Label', 'name'=>'gateway_label', 'type'=>'text',
             'instructions'=>'A short doorway label. Leave empty to use the Reader Lane.', 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_gateway_prompt', 'label'=>'Door Prompt', 'name'=>'gateway_prompt', 'type'=>'textarea', 'rows'=>2,
             'instructions'=>'One reader-facing reason to choose this door. Leave empty to use the existing tagline.', 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_read_url', 'label'=>'Primary Reading URL', 'name'=>'primary_read_url', 'type'=>'url',
             'instructions'=>'Link directly to Chapter One or the invitation route, not a platform homepage. Pretty Links /go/ URLs are supported.', 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_platform', 'label'=>'Reading Platform', 'name'=>'reading_platform', 'type'=>'select',
             'choices'=>['onsite'=>'CodaLanguez.com','substack'=>'Substack','lantern_alpha'=>'Lantern — Private Alpha','lantern'=>'Lantern','other'=>'Other'],
             'allow_null'=>1, 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_cta', 'label'=>'Primary Button Label', 'name'=>'primary_cta_label', 'type'=>'text',
             'instructions'=>'For example: Read Chapter One or Get My Lantern Invite.', 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_secondary_url', 'label'=>'Secondary Follow URL', 'name'=>'secondary_follow_url', 'type'=>'url',
             'instructions'=>'Optional continuity route, normally the serial’s Substack publication or newsletter. Do not duplicate the primary reading URL.', 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_secondary_cta', 'label'=>'Secondary Button Label', 'name'=>'secondary_cta_label', 'type'=>'text',
             'instructions'=>'For example: Follow on Substack. Leave empty to use that label.', 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_access', 'label'=>'Current Access Message', 'name'=>'access_message', 'type'=>'textarea', 'rows'=>2,
             'instructions'=>'State exactly what is free, paid or invitation-only right now. Leave empty to use the selected platform default.', 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_manuscript', 'label'=>'Manuscript State', 'name'=>'manuscript_state', 'type'=>'select',
             'choices'=>['complete'=>'Complete manuscript','buffered'=>'Buffered','live'=>'Written live','season_complete'=>'Season complete','hiatus'=>'On hiatus'],
             'allow_null'=>1, 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_current_episode', 'label'=>'Current Episode', 'name'=>'current_episode', 'type'=>'number',
             'min'=>0, 'step'=>1, 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_episode_count', 'label'=>'Planned Episode Count', 'name'=>'planned_episode_count', 'type'=>'number',
             'instructions'=>'Leave empty when the count is not confirmed.', 'min'=>1, 'step'=>1, 'show_in_rest'=>1],
            ['key'=>'field_ht_serial_next_release', 'label'=>'Next Release Date', 'name'=>'next_release_date', 'type'=>'date_picker',
             'display_format'=>'F j, Y', 'return_format'=>'Y-m-d', 'show_in_rest'=>1],
        ],
        'location' => [[['param'=>'post_type','operator'=>'==','value'=>'webnovel']]],
        'menu_order'=>1, 'position'=>'normal', 'style'=>'default', 'active'=>true, 'show_in_rest'=>1,
    ]);

    acf_add_local_field_group([
        'key'      => 'group_ht_hero_serial_campaign',
        'title'    => 'Hero Update — Serial Gateway',
        'fields'   => [
            ['key'=>'field_ht_hero_featured_serial', 'label'=>'Use as Featured Serial', 'name'=>'featured_serial', 'type'=>'true_false',
             'instructions'=>'Use this update as the homepage serial gateway. Useful when the serial lives entirely on Lantern or Substack.', 'ui'=>1, 'show_in_rest'=>1],
            ['key'=>'field_ht_hero_serial_title', 'label'=>'Serial Title', 'name'=>'serial_title', 'type'=>'text',
             'instructions'=>'The actual story title used in compact campaign placements such as the Links page.', 'show_in_rest'=>1],
            ['key'=>'field_ht_hero_reader_lane', 'label'=>'Reader Lane', 'name'=>'reader_lane', 'type'=>'text',
             'instructions'=>'A short, accurate shelf such as Science-Fiction Monster Romance.', 'show_in_rest'=>1],
            ['key'=>'field_ht_hero_platform', 'label'=>'Reading Platform', 'name'=>'reading_platform', 'type'=>'select',
             'choices'=>['onsite'=>'CodaLanguez.com','substack'=>'Substack','lantern_alpha'=>'Lantern — Private Alpha','lantern'=>'Lantern','other'=>'Other'],
             'allow_null'=>1, 'show_in_rest'=>1],
            ['key'=>'field_ht_hero_access', 'label'=>'Current Access Message', 'name'=>'access_message', 'type'=>'textarea', 'rows'=>2,
             'instructions'=>'State exactly what is free, paid or invitation-only right now.', 'show_in_rest'=>1],
            ['key'=>'field_ht_hero_schedule', 'label'=>'Release Schedule', 'name'=>'update_schedule', 'type'=>'text',
             'instructions'=>'For example: New episode every Tuesday.', 'show_in_rest'=>1],
            ['key'=>'field_ht_hero_secondary_url', 'label'=>'Secondary Follow URL', 'name'=>'secondary_follow_url', 'type'=>'url',
             'instructions'=>'Optional Substack or newsletter route shown beside the main reading button.', 'show_in_rest'=>1],
            ['key'=>'field_ht_hero_secondary_cta', 'label'=>'Secondary Button Label', 'name'=>'secondary_cta_label', 'type'=>'text',
             'instructions'=>'For example: Follow on Substack. Leave empty to use that label.', 'show_in_rest'=>1],
        ],
        'location' => [[['param'=>'post_type','operator'=>'==','value'=>'hero_update']]],
        'menu_order'=>1, 'position'=>'normal', 'style'=>'default', 'active'=>true, 'show_in_rest'=>1,
    ]);
});

/** Return the first local chapter and its external reading URL, when present. */
function ht_serial_first_chapter($webnovel_id) {
    $chapters = get_posts([
        'post_type'=>'chapter', 'post_status'=>'publish', 'posts_per_page'=>1,
        'meta_key'=>'chapter_number', 'orderby'=>'meta_value_num', 'order'=>'ASC',
        'meta_query'=>[['key'=>'webnovel','value'=>(int)$webnovel_id]],
        'no_found_rows'=>true,
    ]);
    return $chapters ? $chapters[0] : null;
}

/** Resolve one direct reading destination and its display data. */
function ht_get_serial_destination($post_id, $post_type = '') {
    $post_type = $post_type ?: get_post_type($post_id);
    $platform  = (string) ht_serial_field('reading_platform', $post_id);
    $url       = '';
    $label     = '';

    if ($post_type === 'hero_update') {
        $url   = (string) ht_serial_field('cta_link', $post_id);
        $label = (string) ht_serial_field('cta_label', $post_id);
    } else {
        $url   = (string) ht_serial_field('primary_read_url', $post_id);
        $label = (string) ht_serial_field('primary_cta_label', $post_id);

        if (!$url) {
            $substack = (string) ht_serial_field('substack_url', $post_id);
            if ($substack) {
                $url = $substack;
                $platform = $platform ?: 'substack';
            } else {
                $first = ht_serial_first_chapter($post_id);
                if ($first) {
                    $external = (string) ht_serial_field('external_read_url', $first->ID);
                    $url = $external ?: get_permalink($first);
                }
            }
        }
    }

    if (!$platform && $url) {
        $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
        if (strpos($host, 'substack.com') !== false) $platform = 'substack';
        elseif (strpos($host, 'lanternserials.com') !== false) $platform = 'lantern';
        elseif ($host === strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST)) || !$host) $platform = 'onsite';
        else $platform = 'other';
    }

    if (!$label) {
        if ($platform === 'lantern_alpha') $label = __('Get My Lantern Invite', 'haunted-tech');
        elseif ($platform === 'lantern') $label = __('Read on Lantern', 'haunted-tech');
        elseif ($platform === 'substack') $label = __('Read on Substack', 'haunted-tech');
        else $label = __('Read Chapter One', 'haunted-tech');
    }

    $access = (string) ht_serial_field('access_message', $post_id);
    if (!$access && $platform === 'lantern_alpha') {
        $access = __('Invitation required. During Lantern’s private alpha, every posted episode is free to read.', 'haunted-tech');
    }

    return ['url'=>$url, 'label'=>$label, 'platform'=>$platform, 'access'=>$access];
}

/**
 * Return the reader's two useful routes without presenting duplicate buttons.
 * Lantern (or another configured primary reader) leads; Substack follows as
 * the continuity channel for inbox updates and readers who prefer it there.
 */
function ht_get_serial_links($post_id, $post_type = '') {
    $primary = ht_get_serial_destination($post_id, $post_type);
    $links = [];

    if ($primary['url']) {
        $links[] = [
            'url'      => $primary['url'],
            'label'    => $primary['label'],
            'action'   => 'chapter-one',
            'external' => ht_serial_url_is_external($primary['url']),
        ];
    }

    $source_type = $post_type ?: get_post_type($post_id);
    $secondary = (string) ht_serial_field('secondary_follow_url', $post_id);
    if (!$secondary && $source_type === 'webnovel') {
        $secondary = (string) ht_serial_field('substack_url', $post_id);
    }
    if (!$secondary && function_exists('haunted_tech_get_substack_url')) {
        $secondary = haunted_tech_get_substack_url();
    }
    if ($secondary && untrailingslashit($secondary) !== untrailingslashit($primary['url'])) {
        $links[] = [
            'url'      => $secondary,
            'label'    => (string) ht_serial_field('secondary_cta_label', $post_id, __('Follow on Substack', 'haunted-tech')),
            'action'   => 'substack',
            'external' => ht_serial_url_is_external($secondary),
        ];
    }

    return $links;
}

/** Tell link renderers whether a destination leaves this WordPress site. */
function ht_serial_url_is_external($url) {
    $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
    $site = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
    return $host && $host !== $site;
}

/** Resolve a chapter to its actual reading surface and name that surface. */
function ht_get_chapter_destination($chapter_id) {
    $external_url = (string) ht_serial_field('external_read_url', $chapter_id);
    $url = $external_url ?: get_permalink($chapter_id);
    $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
    $platform = __('this site', 'haunted-tech');

    if (strpos($host, 'lanternserials.com') !== false) $platform = 'Lantern';
    elseif (strpos($host, 'substack.com') !== false || strpos($host, 'newsletter.codalanguez.com') !== false) $platform = 'Substack';
    elseif (ht_serial_url_is_external($url)) $platform = __('the reading site', 'haunted-tech');

    return [
        'url'      => $url,
        'platform' => $platform,
        'external' => ht_serial_url_is_external($url),
        'rel'      => $external_url && function_exists('ht_outbound_rel_for') ? ht_outbound_rel_for($url) : 'noopener',
    ];
}

/** Select the deliberately featured source, with a safe legacy fallback. */
function ht_get_featured_serial_source() {
    foreach (['hero_update', 'webnovel'] as $post_type) {
        $posts = get_posts([
            'post_type'=>$post_type, 'post_status'=>'publish', 'posts_per_page'=>1,
            'meta_key'=>'featured_serial', 'meta_value'=>'1', 'orderby'=>'date', 'order'=>'DESC',
            'no_found_rows'=>true,
        ]);
        if ($posts) return $posts[0];
    }

    $hero = get_posts([
        'post_type'=>'hero_update', 'post_status'=>'publish', 'posts_per_page'=>1,
        'meta_key'=>'update_type', 'meta_value'=>'chapter', 'orderby'=>'date', 'order'=>'DESC',
        'no_found_rows'=>true,
    ]);
    if ($hero) return $hero[0];

    $webnovels = get_posts([
        'post_type'=>'webnovel', 'post_status'=>'publish', 'posts_per_page'=>1,
        'orderby'=>'date', 'order'=>'DESC', 'no_found_rows'=>true,
    ]);
    return $webnovels ? $webnovels[0] : null;
}

/** Normalize cover fields that may return an array, attachment ID or URL. */
function ht_serial_cover_url($post_id, $size = 'large') {
    $cover = ht_serial_field('cover', $post_id);
    if (is_array($cover) && !empty($cover['url'])) return $cover['url'];
    if (is_numeric($cover)) {
        $src = wp_get_attachment_image_url((int)$cover, $size);
        if ($src) return $src;
    }
    if (is_string($cover) && filter_var($cover, FILTER_VALIDATE_URL)) return $cover;
    return get_the_post_thumbnail_url($post_id, $size) ?: '';
}

/** Status facts shown only when an editor supplied them. */
function ht_get_serial_status_items($post_id) {
    $items = [];
    $schedule = (string) ht_serial_field('update_schedule', $post_id);
    $state    = (string) ht_serial_field('manuscript_state', $post_id);
    $current  = ht_serial_field('current_episode', $post_id, null);
    $planned  = ht_serial_field('planned_episode_count', $post_id, null);
    $next     = (string) ht_serial_field('next_release_date', $post_id);
    $states   = ['complete'=>__('Complete manuscript','haunted-tech'),'buffered'=>__('Buffered','haunted-tech'),'live'=>__('Written live','haunted-tech'),'season_complete'=>__('Season complete','haunted-tech'),'hiatus'=>__('On hiatus','haunted-tech')];

    if ($schedule) $items[] = [__('Updates','haunted-tech'), $schedule];
    if ($state && isset($states[$state])) $items[] = [__('Manuscript','haunted-tech'), $states[$state]];
    if ($current !== null) {
        $episode = $planned !== null ? sprintf(__('%1$d of %2$d','haunted-tech'), (int)$current, (int)$planned) : (string)(int)$current;
        $items[] = [__('Episode','haunted-tech'), $episode];
    }
    if ($next && ($timestamp = strtotime($next))) $items[] = [__('Next','haunted-tech'), wp_date(get_option('date_format'), $timestamp)];
    return $items;
}

/** Homepage featured transmission. */
function ht_render_featured_serial($attributes = []) {
    $source = ht_get_featured_serial_source();
    if (!$source) return '';

    $type = get_post_type($source);
    if ($type === 'hero_update') {
        $first = (string) ht_serial_field('title_first', $source->ID, get_the_title($source));
        $accent = (string) ht_serial_field('title_accent', $source->ID);
        $title = trim($first . ' ' . $accent);
        $hook = (string) ht_serial_field('blurb', $source->ID);
        $lane = (string) ht_serial_field('reader_lane', $source->ID, ht_serial_field('eyebrow', $source->ID, __('Featured transmission','haunted-tech')));
    } else {
        $title = get_the_title($source);
        $hook = (string) ht_serial_field('campaign_hook', $source->ID);
        if (!$hook) $hook = (string) ht_serial_field('tagline', $source->ID);
        if (!$hook) $hook = wp_trim_words(wp_strip_all_tags((string)ht_serial_field('blurb', $source->ID)), 42, '…');
        $lane = (string) ht_serial_field('reader_lane', $source->ID, ht_serial_field('genre', $source->ID, __('Featured transmission','haunted-tech')));
    }

    $cover = ht_serial_cover_url($source->ID, 'hero_bg');
    $dest  = ht_get_serial_destination($source->ID, $type);
    $status = $type === 'webnovel' ? ht_get_serial_status_items($source->ID) : [];
    $links = ht_get_serial_links($source->ID, $type);

    ob_start(); ?>
    <section class="serial-feature" id="featured-serial" aria-labelledby="featured-serial-title">
      <?php if ($cover): ?><div class="serial-feature-art" style="background-image:url('<?php echo esc_url($cover); ?>')" aria-hidden="true"></div><?php endif; ?>
      <div class="serial-feature-scrim" aria-hidden="true"></div>
      <div class="serial-feature-frame">
        <p class="serial-kicker"><?php echo esc_html($lane); ?></p>
        <h1 id="featured-serial-title" data-text="<?php echo esc_attr($title); ?>"><?php echo esc_html($title); ?></h1>
        <?php if ($hook): ?><div class="serial-feature-hook"><?php echo wp_kses_post(wpautop($hook)); ?></div><?php endif; ?>
        <?php if ($status): ?><dl class="serial-status"><?php foreach ($status as $item): ?><div><dt><?php echo esc_html($item[0]); ?></dt><dd><?php echo esc_html($item[1]); ?></dd></div><?php endforeach; ?></dl><?php endif; ?>
        <?php if ($dest['access']): ?><p class="serial-access-note"><?php echo esc_html($dest['access']); ?></p><?php endif; ?>
        <?php if ($links): ?><div class="serial-cta-row">
          <?php foreach ($links as $index => $link): ?>
            <a class="serial-primary-cta<?php echo $index ? ' serial-secondary-cta' : ''; ?>" href="<?php echo esc_url($link['url']); ?>" data-serial-action="<?php echo esc_attr($link['action']); ?>" data-serial="<?php echo esc_attr($source->post_name); ?>"<?php echo $link['external'] ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html($link['label']); ?> <span aria-hidden="true">&rarr;</span></a>
          <?php endforeach; ?>
        </div><?php endif; ?>
      </div>
    </section>
    <?php return ob_get_clean();
}

/** Three compact reader-choice cards populated by Web Novel campaign fields. */
function ht_render_serial_doors($attributes = []) {
    $limit = max(1, min(3, isset($attributes['limit']) ? (int)$attributes['limit'] : 3));
    $serials = get_posts([
        'post_type'=>'webnovel', 'post_status'=>'publish', 'posts_per_page'=>$limit,
        'meta_key'=>'campaign_priority', 'orderby'=>'meta_value_num', 'order'=>'ASC',
        'meta_query'=>[['key'=>'gateway_serial','value'=>'1']], 'no_found_rows'=>true,
    ]);
    if (!$serials) {
        $serials = get_posts(['post_type'=>'webnovel','post_status'=>'publish','posts_per_page'=>$limit,'orderby'=>'date','order'=>'DESC','no_found_rows'=>true]);
    }
    if (!$serials) return '';

    ob_start(); ?>
    <section class="serial-doors" id="serial-doors" aria-labelledby="serial-doors-title">
      <div class="serial-doors-heading">
        <p class="serial-kicker"><?php esc_html_e('Choose your door', 'haunted-tech'); ?></p>
        <h2 id="serial-doors-title"><?php esc_html_e('What are you willing to follow home?', 'haunted-tech'); ?></h2>
      </div>
      <div class="serial-door-grid">
        <?php foreach ($serials as $serial):
            $dest = ht_get_serial_destination($serial->ID, 'webnovel');
            $cover = ht_serial_cover_url($serial->ID, 'large');
            $label = (string) ht_serial_field('gateway_label', $serial->ID, ht_serial_field('reader_lane', $serial->ID, ht_serial_field('genre', $serial->ID, __('Web novel','haunted-tech'))));
            $prompt = (string) ht_serial_field('gateway_prompt', $serial->ID, ht_serial_field('tagline', $serial->ID));
            $url = $dest['url'] ?: get_permalink($serial);
        ?>
          <article class="serial-door">
            <a class="serial-door-link" href="<?php echo esc_url($url); ?>" data-serial-action="gateway" data-serial="<?php echo esc_attr($serial->post_name); ?>">
              <?php if ($cover): ?><span class="serial-door-art" style="background-image:url('<?php echo esc_url($cover); ?>')" aria-hidden="true"></span><?php endif; ?>
              <span class="serial-door-shade" aria-hidden="true"></span>
              <span class="serial-door-copy">
                <span class="serial-door-label"><?php echo esc_html($label); ?></span>
                <span class="serial-door-title"><?php echo esc_html(get_the_title($serial)); ?></span>
                <?php if ($prompt): ?><span class="serial-door-prompt"><?php echo esc_html($prompt); ?></span><?php endif; ?>
                <span class="serial-door-cta"><?php echo esc_html($dest['label']); ?> <span aria-hidden="true">&rarr;</span></span>
              </span>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php return ob_get_clean();
}

/** Recent chapter releases with direct, platform-aware reading links. */
function ht_render_latest_episodes($attributes = []) {
    $limit = max(1, min(12, isset($attributes['limit']) ? (int)$attributes['limit'] : 6));
    $chapters = get_posts([
        'post_type'=>'chapter', 'post_status'=>'publish', 'posts_per_page'=>$limit,
        'orderby'=>'date', 'order'=>'DESC', 'no_found_rows'=>true,
    ]);
    if (!$chapters) return '';

    $access_labels = [
        'free'=>__('Free','haunted-tech'), 'early_access'=>__('Early Access','haunted-tech'),
        'patron_only'=>__('Patron Only','haunted-tech'), 'ream_premium'=>__('Ream Premium','haunted-tech'),
        'substack_paid'=>__('Substack Premium','haunted-tech'), 'lantern_free'=>__('Free on Lantern','haunted-tech'),
        'locked'=>__('Off-platform','haunted-tech'),
    ];

    ob_start(); ?>
    <section class="latest-episodes" id="latest-episodes" aria-labelledby="latest-episodes-title">
      <span class="section-anchor-alias" id="web-novels" aria-hidden="true"></span>
      <div class="section-header">
        <h2 class="section-title" id="latest-episodes-title"><?php esc_html_e('Latest Episodes', 'haunted-tech'); ?></h2>
        <div class="section-meta"><?php esc_html_e('Newest doors first', 'haunted-tech'); ?></div>
      </div>
      <div class="latest-episode-grid">
        <?php foreach ($chapters as $chapter):
            $destination = ht_get_chapter_destination($chapter->ID);
            $webnovel = ht_serial_field('webnovel', $chapter->ID);
            $webnovel_id = is_object($webnovel) ? $webnovel->ID : (int)$webnovel;
            $arc = (string) ht_serial_field('arc', $chapter->ID);
            $series = $arc ?: ($webnovel_id ? get_the_title($webnovel_id) : __('Serial', 'haunted-tech'));
            $serial_slug = $arc ? sanitize_title($arc) : ($webnovel_id ? get_post_field('post_name', $webnovel_id) : $chapter->post_name);
            $number = ht_serial_field('chapter_number', $chapter->ID, null);
            $release = (string) ht_serial_field('release_date', $chapter->ID);
            $access = (string) ht_serial_field('access_level', $chapter->ID, 'free');
            $rel = $destination['external'] ? ' rel="' . esc_attr($destination['rel']) . '"' : '';
        ?>
          <article class="latest-episode-card">
            <a href="<?php echo esc_url($destination['url']); ?>" data-serial-action="latest-episode" data-serial="<?php echo esc_attr($serial_slug); ?>" data-episode="<?php echo esc_attr($chapter->post_name); ?>"<?php echo $destination['external'] ? ' target="_blank"' : ''; ?><?php echo $rel; ?>>
              <span class="latest-episode-series"><?php echo esc_html($series); ?></span>
              <span class="latest-episode-number"><?php echo $number !== null ? ((int)$number === 0 ? esc_html__('Prologue','haunted-tech') : sprintf(esc_html__('Episode %d','haunted-tech'), (int)$number)) : esc_html__('New episode','haunted-tech'); ?></span>
              <span class="latest-episode-title"><?php echo esc_html(get_the_title($chapter)); ?></span>
              <span class="latest-episode-meta">
                <span><?php echo esc_html($access_labels[$access] ?? $access_labels['free']); ?></span>
                <?php if ($release && ($timestamp = strtotime($release))): ?><time datetime="<?php echo esc_attr($release); ?>"><?php echo esc_html(wp_date(get_option('date_format'), $timestamp)); ?></time><?php endif; ?>
              </span>
              <span class="latest-episode-cta"><?php echo sprintf(esc_html__('Read on %s', 'haunted-tech'), esc_html($destination['platform'])); ?> <span aria-hidden="true">&rarr;</span></span>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php return ob_get_clean();
}

/** Build one honest Start Here entry from fields already supplied in WordPress. */
function ht_reader_index_entry($source, $source_type = '') {
    $source_type = $source_type ?: get_post_type($source);
    $source_id   = is_object($source) ? (int) $source->ID : (int) $source;
    $source_post = get_post($source_id);
    if (!$source_post) return null;

    if ($source_type === 'hero_update') {
        $title = trim((string) ht_serial_field('serial_title', $source_id));
        if (!$title) {
            $title = trim((string) ht_serial_field('title_first', $source_id) . ' ' . (string) ht_serial_field('title_accent', $source_id));
        }
        $hook     = (string) ht_serial_field('blurb', $source_id);
        $lane     = (string) ht_serial_field('reader_lane', $source_id, ht_serial_field('eyebrow', $source_id));
        $schedule = (string) ht_serial_field('update_schedule', $source_id);
        $chapter_query = [
            'post_type'=>'chapter', 'post_status'=>'publish', 'posts_per_page'=>1,
            'orderby'=>'date', 'order'=>'DESC', 'no_found_rows'=>true,
            'meta_query'=>[['key'=>'arc','value'=>$title]],
        ];
        $index_url = '';
    } else {
        $title     = get_the_title($source_id);
        $hook      = (string) ht_serial_field('campaign_hook', $source_id);
        if (!$hook) $hook = (string) ht_serial_field('gateway_prompt', $source_id, ht_serial_field('tagline', $source_id));
        $lane      = (string) ht_serial_field('reader_lane', $source_id, ht_serial_field('genre', $source_id));
        $schedule  = (string) ht_serial_field('update_schedule', $source_id);
        $chapter_query = [
            'post_type'=>'chapter', 'post_status'=>'publish', 'posts_per_page'=>1,
            'orderby'=>'date', 'order'=>'DESC', 'no_found_rows'=>true,
            'meta_query'=>[['key'=>'webnovel','value'=>$source_id]],
        ];
        $index_url = get_permalink($source_id);
    }

    $start  = ht_get_serial_destination($source_id, $source_type);
    $latest = get_posts($chapter_query);
    $latest_post = $latest ? $latest[0] : null;
    $latest_dest = $latest_post ? ht_get_chapter_destination($latest_post->ID) : null;

    return [
        'id'           => $source_id,
        'slug'         => sanitize_title($title ?: $source_post->post_name),
        'title'        => $title ?: get_the_title($source_id),
        'lane'         => $lane,
        'hook'         => $hook,
        'schedule'     => $schedule,
        'cover'        => ht_serial_cover_url($source_id, 'large'),
        'start'        => $start,
        'latest_post'  => $latest_post,
        'latest'       => $latest_dest,
        'index_url'    => $index_url,
    ];
}

/** A permanent, shareable index that gives every reader a beginning and a return path. */
function ht_render_reader_index($attributes = []) {
    $entries = [];
    $featured = ht_get_featured_serial_source();

    if ($featured && get_post_type($featured) === 'hero_update') {
        $entry = ht_reader_index_entry($featured, 'hero_update');
        if ($entry) $entries[] = $entry;
    }

    $webnovels = get_posts([
        'post_type'=>'webnovel', 'post_status'=>'publish', 'posts_per_page'=>-1,
        'orderby'=>['menu_order'=>'ASC','title'=>'ASC'], 'order'=>'ASC', 'no_found_rows'=>true,
    ]);
    foreach ($webnovels as $webnovel) {
        if ($featured && (int) $featured->ID === (int) $webnovel->ID) continue;
        $entry = ht_reader_index_entry($webnovel, 'webnovel');
        if ($entry) $entries[] = $entry;
    }
    if (!$entries) return '';

    ob_start(); ?>
    <section class="reader-index" aria-labelledby="reader-index-heading">
      <header class="reader-index-intro">
        <p class="serial-kicker"><?php esc_html_e('The serial shelf', 'haunted-tech'); ?></p>
        <h2 id="reader-index-heading"><?php esc_html_e('Pick a story. Make one bad decision.', 'haunted-tech'); ?></h2>
        <p><?php esc_html_e('Start at the beginning, walk into the newest trouble, or let Substack tell you when something else goes wrong.', 'haunted-tech'); ?></p>
      </header>

      <div class="reader-index-grid">
        <?php foreach ($entries as $entry): ?>
          <article class="reader-index-card">
            <?php if ($entry['cover']): ?><div class="reader-index-art" style="background-image:url('<?php echo esc_url($entry['cover']); ?>')" aria-hidden="true"></div><?php endif; ?>
            <div class="reader-index-scrim" aria-hidden="true"></div>
            <div class="reader-index-copy">
              <?php if ($entry['lane']): ?><p class="reader-index-lane"><?php echo esc_html($entry['lane']); ?></p><?php endif; ?>
              <h3><?php echo esc_html($entry['title']); ?></h3>
              <?php if ($entry['hook']): ?><div class="reader-index-hook"><?php echo wp_kses_post(wpautop($entry['hook'])); ?></div><?php endif; ?>
              <?php if ($entry['schedule']): ?><p class="reader-index-schedule"><?php echo esc_html($entry['schedule']); ?></p><?php endif; ?>
              <div class="reader-index-actions">
                <?php if (!empty($entry['start']['url'])):
                    $start_external = ht_serial_url_is_external($entry['start']['url']); ?>
                  <a class="reader-index-primary" href="<?php echo esc_url($entry['start']['url']); ?>" data-serial-action="start-reading" data-serial="<?php echo esc_attr($entry['slug']); ?>"<?php echo $start_external ? ' target="_blank" rel="noopener"' : ''; ?>><?php esc_html_e('Start at Episode One', 'haunted-tech'); ?> <span aria-hidden="true">&rarr;</span></a>
                <?php endif; ?>
                <?php if ($entry['latest'] && !empty($entry['latest']['url']) && untrailingslashit($entry['latest']['url']) !== untrailingslashit($entry['start']['url'])):
                    $latest_rel = $entry['latest']['external'] ? ' rel="' . esc_attr($entry['latest']['rel']) . '"' : ''; ?>
                  <a href="<?php echo esc_url($entry['latest']['url']); ?>" data-serial-action="episode-read" data-serial="<?php echo esc_attr($entry['slug']); ?>" data-episode="<?php echo esc_attr($entry['latest_post']->post_name); ?>"<?php echo $entry['latest']['external'] ? ' target="_blank"' : ''; ?><?php echo $latest_rel; ?>><?php esc_html_e('Newest Episode', 'haunted-tech'); ?></a>
                <?php endif; ?>
                <?php if ($entry['index_url']): ?>
                  <a href="<?php echo esc_url($entry['index_url']); ?>" data-serial-action="serial-index" data-serial="<?php echo esc_attr($entry['slug']); ?>"><?php esc_html_e('All Episodes', 'haunted-tech'); ?></a>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <aside class="reader-index-follow" aria-label="<?php esc_attr_e('Follow new episodes', 'haunted-tech'); ?>">
        <div>
          <p class="serial-kicker"><?php esc_html_e('Prefer the trouble delivered?', 'haunted-tech'); ?></p>
          <h2><?php esc_html_e('Let the next chapter find you.', 'haunted-tech'); ?></h2>
        </div>
        <a href="<?php echo esc_url(home_url('/go/substack')); ?>" data-serial-action="follow-substack" data-serial="publication" target="_blank" rel="noopener"><?php esc_html_e('Follow on Substack', 'haunted-tech'); ?> <span aria-hidden="true">&rarr;</span></a>
      </aside>
    </section>
    <?php return ob_get_clean();
}
