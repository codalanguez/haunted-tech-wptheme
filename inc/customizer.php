<?php
/**
 * Customizer panel — Haunted Tech theme options.
 *
 * Adds a "Haunted Tech" panel under Appearance → Customize with two sections:
 *   1. Newsletter — embed code from Mailchimp / ConvertKit / Substack / etc.
 *   2. Hero Slider — auto-rotation duration in milliseconds.
 *
 * Helper accessors:
 *   - haunted_tech_get_newsletter_embed() — sanitized HTML to inject into the
 *     newsletter section, or '' if unset (falls back to the placeholder form)
 *   - haunted_tech_get_slider_duration() — int ms (default 5000)
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

add_action('customize_register', function (\WP_Customize_Manager $wp_customize) {

    $wp_customize->add_panel('haunted_tech', [
        'title'       => __('Haunted Tech', 'haunted-tech'),
        'description' => __('Theme-specific options: newsletter provider embed and hero slider behavior.', 'haunted-tech'),
        'priority'    => 30,
    ]);

    /* ---------- Newsletter section ---------- */
    $wp_customize->add_section('haunted_tech_newsletter', [
        'title'       => __('Newsletter', 'haunted-tech'),
        'description' => __('Paste the embed code from your newsletter provider (Mailchimp, ConvertKit, Substack, Beehiiv, etc.). It replaces the placeholder form in the homepage’s "Join the Signal" section. Leave empty to keep the placeholder.', 'haunted-tech'),
        'panel'       => 'haunted_tech',
        'priority'    => 10,
    ]);

    /* Provider mode — pick how to render the form */
    $wp_customize->add_setting('haunted_tech_newsletter_provider', [
        'type'              => 'option',
        'capability'        => 'edit_theme_options',
        'default'           => 'placeholder',
        'sanitize_callback' => 'haunted_tech_sanitize_provider',
    ]);
    $wp_customize->add_control('haunted_tech_newsletter_provider', [
        'type'    => 'select',
        'section' => 'haunted_tech_newsletter',
        'label'   => __('Provider', 'haunted-tech'),
        'choices' => [
            'placeholder' => __('— Placeholder form —', 'haunted-tech'),
            'substack'    => __('Substack (paste your URL below)', 'haunted-tech'),
            'embed'       => __('Custom embed (paste HTML below)', 'haunted-tech'),
        ],
    ]);

    /* Substack URL — used when provider = substack */
    $wp_customize->add_setting('haunted_tech_substack_url', [
        'type'              => 'option',
        'capability'        => 'edit_theme_options',
        'default'           => '',
        'sanitize_callback' => 'haunted_tech_sanitize_substack_url',
    ]);
    $wp_customize->add_control('haunted_tech_substack_url', [
        'type'        => 'url',
        'section'     => 'haunted_tech_newsletter',
        'label'       => __('Substack URL', 'haunted-tech'),
        'description' => __('Your Substack home (e.g. https://codalanguez.substack.com). The theme builds the iframe embed automatically.', 'haunted-tech'),
        'input_attrs' => ['placeholder' => 'https://yourname.substack.com'],
    ]);

    /* Custom embed code — used when provider = embed */
    $wp_customize->add_setting('haunted_tech_newsletter_embed', [
        'type'              => 'option',
        'capability'        => 'edit_theme_options',
        'default'           => '',
        'sanitize_callback' => 'haunted_tech_sanitize_embed_html',
    ]);
    $wp_customize->add_control('haunted_tech_newsletter_embed', [
        'type'        => 'textarea',
        'section'     => 'haunted_tech_newsletter',
        'label'       => __('Custom embed code', 'haunted-tech'),
        'description' => __('Raw HTML/script from Mailchimp, ConvertKit, Beehiiv, etc. Used when Provider = Custom embed.', 'haunted-tech'),
        'input_attrs' => ['rows' => 10, 'placeholder' => '<form action="https://yourprovider.com/subscribe" method="post">…</form>'],
    ]);

    /* ---------- Hero Slider section ---------- */
    $wp_customize->add_section('haunted_tech_slider', [
        'title'       => __('Hero Slider', 'haunted-tech'),
        'description' => __('Tune the auto-rotation behavior of the homepage hero slider.', 'haunted-tech'),
        'panel'       => 'haunted_tech',
        'priority'    => 20,
    ]);

    $wp_customize->add_setting('haunted_tech_slider_duration', [
        'type'              => 'option',
        'capability'        => 'edit_theme_options',
        'default'           => 5000,
        'sanitize_callback' => 'haunted_tech_sanitize_duration',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('haunted_tech_slider_duration', [
        'type'        => 'number',
        'section'     => 'haunted_tech_slider',
        'label'       => __('Slide duration (ms)', 'haunted-tech'),
        'description' => __('Milliseconds each slide stays visible before auto-advancing. Default 5000 (5 seconds). Range 1500–30000.', 'haunted-tech'),
        'input_attrs' => ['min' => 1500, 'max' => 30000, 'step' => 250],
    ]);

    $wp_customize->add_setting('haunted_tech_slider_autoplay', [
        'type'              => 'option',
        'capability'        => 'edit_theme_options',
        'default'           => 1,
        'sanitize_callback' => 'absint',
    ]);

    $wp_customize->add_control('haunted_tech_slider_autoplay', [
        'type'        => 'checkbox',
        'section'     => 'haunted_tech_slider',
        'label'       => __('Auto-rotate slides', 'haunted-tech'),
        'description' => __('Uncheck to disable auto-advance entirely (visitors must use arrows/dots).', 'haunted-tech'),
    ]);
});

/* ---------------------------------------------------------------------------
 * Sanitizers
 * ------------------------------------------------------------------------- */
function haunted_tech_sanitize_duration($value) {
    $value = absint($value);
    if ($value < 1500)  $value = 1500;
    if ($value > 30000) $value = 30000;
    return $value;
}

function haunted_tech_sanitize_embed_html($value) {
    /* Allow form/script-bearing markup since newsletter embeds need it.
     * Only theme-options-capable users can save the field, so XSS surface is
     * limited to admins. We strip nothing but normalize encoding. */
    $value = (string) $value;
    return wp_check_invalid_utf8($value, true);
}

function haunted_tech_sanitize_provider($value) {
    $allowed = ['placeholder', 'substack', 'embed'];
    return in_array($value, $allowed, true) ? $value : 'placeholder';
}

function haunted_tech_sanitize_substack_url($value) {
    $value = esc_url_raw(trim((string)$value));
    /* Tolerate the user pasting either https://name.substack.com or
     * https://name.substack.com/embed — we'll resolve to the embed form below. */
    return $value;
}

/* ---------------------------------------------------------------------------
 * Public accessors used by render callbacks + main.js
 * ------------------------------------------------------------------------- */
function haunted_tech_get_newsletter_provider() {
    $p = get_option('haunted_tech_newsletter_provider', 'placeholder');
    $allowed = ['placeholder', 'substack', 'embed'];
    return in_array($p, $allowed, true) ? $p : 'placeholder';
}

function haunted_tech_get_substack_url() {
    return (string) get_option('haunted_tech_substack_url', '');
}

/**
 * Returns whatever HTML should render INSIDE the newsletter callout.
 * Empty string means "use the built-in placeholder form".
 */
function haunted_tech_get_newsletter_embed() {
    $provider = haunted_tech_get_newsletter_provider();

    if ($provider === 'substack') {
        $url = haunted_tech_get_substack_url();
        if (!$url) return '';
        /* Normalize: strip trailing slashes, drop /embed if user added it. */
        $base = rtrim(preg_replace('#/embed/?$#', '', $url), '/');
        $embed_src = $base . '/embed';
        /* Substack provides a sandboxed iframe widget that handles the form,
         * confirmation, and double-opt-in. ~320px is enough for the input + button. */
        return sprintf(
            '<iframe src="%s" class="newsletter-embed-frame newsletter-embed-frame--substack" loading="lazy" frameborder="0" scrolling="no" referrerpolicy="no-referrer-when-downgrade" title="%s" style="width:100%%;border:1px solid var(--gold);background:var(--void);min-height:320px;"></iframe>',
            esc_url($embed_src),
            esc_attr__('Subscribe via Substack', 'haunted-tech')
        );
    }

    if ($provider === 'embed') {
        return (string) get_option('haunted_tech_newsletter_embed', '');
    }

    return ''; // placeholder mode
}
function haunted_tech_get_slider_duration() {
    return (int) get_option('haunted_tech_slider_duration', 5000);
}
function haunted_tech_get_slider_autoplay() {
    return (bool) get_option('haunted_tech_slider_autoplay', 1);
}

/* ---------------------------------------------------------------------------
 * Localize options to the front-end JS (so main.js can read them)
 * ------------------------------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {
    wp_localize_script('haunted-tech-main', 'HauntedTechOpts', [
        'sliderDuration' => haunted_tech_get_slider_duration(),
        'sliderAutoplay' => haunted_tech_get_slider_autoplay(),
    ]);
}, 20); // priority 20 so it runs after the script is enqueued in functions.php
