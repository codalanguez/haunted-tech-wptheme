<?php
/**
 * Onboarding admin notice — a dismissible setup checklist that appears at
 * the top of the WP admin until the theme is fully configured.
 *
 * Each step is a small status row: green check when done, red diamond +
 * "Set up" button when not. Dismiss state is per-user via user_meta.
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

const HAUNTED_TECH_DISMISS_KEY = 'haunted_tech_onboarding_dismissed_v1';

/* Build the checklist — array of [done?, title, fix_url, hint] */
function haunted_tech_onboarding_steps() {
    $steps = [];

    /* 1. Site logo */
    $steps[] = [
        'done'  => (bool) get_theme_mod('custom_logo'),
        'title' => __('Upload your site logo', 'haunted-tech'),
        'url'   => admin_url('customize.php?autofocus[section]=title_tagline'),
        'hint'  => __('Customizer · Site Identity', 'haunted-tech'),
    ];

    /* 2. Primary menu */
    $steps[] = [
        'done'  => has_nav_menu('primary'),
        'title' => __('Set up the Primary menu', 'haunted-tech'),
        'url'   => admin_url('nav-menus.php'),
        'hint'  => __('Appearance · Menus', 'haunted-tech'),
    ];

    /* 3. Social menu */
    $steps[] = [
        'done'  => has_nav_menu('social'),
        'title' => __('Set up the Social menu', 'haunted-tech'),
        'url'   => admin_url('nav-menus.php'),
        'hint'  => __('Appearance · Menus · Social', 'haunted-tech'),
    ];

    /* 4. Newsletter provider */
    $provider = function_exists('haunted_tech_get_newsletter_provider') ? haunted_tech_get_newsletter_provider() : 'placeholder';
    $steps[] = [
        'done'  => $provider !== 'placeholder',
        'title' => __('Connect a newsletter provider', 'haunted-tech'),
        'url'   => admin_url('customize.php?autofocus[section]=haunted_tech_newsletter'),
        'hint'  => __('Customizer · Haunted Tech · Newsletter', 'haunted-tech'),
    ];

    /* 5. At least one hero update */
    $hero_count = (int) wp_count_posts('hero_update')->publish;
    $steps[] = [
        'done'  => $hero_count > 0,
        'title' => __('Publish your first Hero Update', 'haunted-tech'),
        'url'   => admin_url('post-new.php?post_type=hero_update'),
        'hint'  => __('Drives the homepage slider', 'haunted-tech'),
    ];

    /* 6. At least one book */
    $book_count = post_type_exists('book') ? (int) wp_count_posts('book')->publish : 0;
    $steps[] = [
        'done'  => $book_count > 0,
        'title' => __('Publish your first Book', 'haunted-tech'),
        'url'   => admin_url('post-new.php?post_type=book'),
        'hint'  => __('Populates the bookshelf', 'haunted-tech'),
    ];

    /* 7. About page */
    $about = get_page_by_path('about');
    $steps[] = [
        'done'  => $about !== null,
        'title' => __('Create an About page (slug: about)', 'haunted-tech'),
        'url'   => admin_url('post-new.php?post_type=page'),
        'hint'  => __('Populates the About modal — content + featured image', 'haunted-tech'),
    ];

    /* 8. Links page — Linktree-style bio-link page (v0.9.1) */
    $links = get_page_by_path('links');
    $links_ok = $links && $links->post_status === 'publish';
    $steps[] = [
        'done'  => $links_ok,
        'title' => __('Create a Links page (slug: links)', 'haunted-tech'),
        'url'   => admin_url('post-new.php?post_type=page'),
        'hint'  => __('Add the Linktree Page block — your social-bio destination', 'haunted-tech'),
    ];

    return $steps;
}

/* Render the notice */
add_action('admin_notices', function () {
    if (!current_user_can('edit_theme_options')) return;
    if (get_user_meta(get_current_user_id(), HAUNTED_TECH_DISMISS_KEY, true)) return;

    $steps = haunted_tech_onboarding_steps();
    $done  = array_filter($steps, fn($s) => $s['done']);
    $total = count($steps);
    $count = count($done);
    if ($count === $total) return; // hide when fully configured

    $dismiss_url = wp_nonce_url(
        add_query_arg('haunted_tech_dismiss_onboarding', '1'),
        'haunted_tech_dismiss_onboarding'
    );
    ?>
    <div class="notice notice-info haunted-tech-onboarding" style="border-left-color:#FFD400;padding:18px 20px 18px 24px;">
      <div style="display:flex;align-items:center;gap:1rem;margin-bottom:0.8rem;">
        <strong style="font-size:14px;letter-spacing:0.05em;text-transform:uppercase;color:#B89200;">
          ◆ Haunted Tech setup
        </strong>
        <span style="color:#666;font-size:12px;"><?php echo (int)$count; ?> of <?php echo (int)$total; ?> done</span>
        <div style="flex:1;height:6px;background:#eee;border-radius:3px;overflow:hidden;max-width:200px;">
          <div style="width:<?php echo (int)(($count/$total)*100); ?>%;height:100%;background:linear-gradient(90deg,#FFD400,#E50914);"></div>
        </div>
        <a href="<?php echo esc_url($dismiss_url); ?>" style="font-size:12px;color:#999;text-decoration:none;margin-left:auto;">Dismiss</a>
      </div>
      <ul style="margin:0;padding:0;list-style:none;font-size:13px;">
        <?php foreach ($steps as $s): ?>
          <li style="display:flex;align-items:center;gap:0.6rem;padding:0.35rem 0;">
            <?php if ($s['done']): ?>
              <span style="color:#46b450;font-size:14px;line-height:1;">✓</span>
              <span style="color:#666;text-decoration:line-through;"><?php echo esc_html($s['title']); ?></span>
            <?php else: ?>
              <span style="color:#E50914;font-size:14px;line-height:1;">◆</span>
              <span><?php echo esc_html($s['title']); ?></span>
              <a href="<?php echo esc_url($s['url']); ?>" style="font-size:11px;text-transform:uppercase;letter-spacing:0.15em;color:#B89200;text-decoration:none;border:1px solid #B89200;padding:2px 8px;margin-left:0.4rem;">Set up</a>
              <small style="color:#999;font-style:italic;"><?php echo esc_html($s['hint']); ?></small>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php
});

/* Handle dismiss action */
add_action('admin_init', function () {
    if (!isset($_GET['haunted_tech_dismiss_onboarding'])) return;
    if (!current_user_can('edit_theme_options')) return;
    check_admin_referer('haunted_tech_dismiss_onboarding');
    update_user_meta(get_current_user_id(), HAUNTED_TECH_DISMISS_KEY, 1);
    wp_safe_redirect(remove_query_arg(['haunted_tech_dismiss_onboarding', '_wpnonce']));
    exit;
});

/* On theme activation, reset the per-user dismiss flag so the checklist
 * reappears for everyone (in case the theme is reactivated after upgrade). */
add_action('after_switch_theme', function () {
    delete_metadata('user', 0, HAUNTED_TECH_DISMISS_KEY, '', true);
});
