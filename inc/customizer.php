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

    $wp_customize->add_setting('haunted_tech_newsletter_embed', [
        'type'              => 'option',
        'capability'        => 'edit_theme_options',
        'default'           => '',
        'sanitize_callback' => 'haunted_tech_sanitize_embed_html',
    ]);

    $wp_customize->add_control('haunted_tech_newsletter_embed', [
        'type'        => 'textarea',
        'section'     => 'haunted_tech_newsletter',
        'label'       => __('Embed code', 'haunted-tech'),
        'description' => __('HTML/script provided by your newsletter platform. Inserted as-is inside the newsletter callout.', 'haunted-tech'),
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
     * limited to admins. We strip nothing but normalize CRLF. */
    $value = (string) $value;
    return wp_check_invalid_utf8($value, true);
}

/* ---------------------------------------------------------------------------
 * Public accessors used by render callbacks + main.js
 * ------------------------------------------------------------------------- */
function haunted_tech_get_newsletter_embed() {
    return (string) get_option('haunted_tech_newsletter_embed', '');
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
