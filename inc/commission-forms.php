<?php
/**
 * Commission inquiry forms — three shortcodes, each self-submits
 * and sends an email to coda@codalanguez.com via wp_mail().
 *
 * Shortcodes:
 *   [ht_commission_art]   — Art Commissions
 *   [ht_commission_cover] — Book Cover Design
 *   [ht_commission_ai]    — AI Image Generation
 *
 * @package HauntedTech
 */

if (!defined('ABSPATH')) { exit; }

/** Recipient for all commission inquiry emails. */
define('HT_COMMISSION_EMAIL', 'coda@codalanguez.com');

/* ============================================================
 * Process form submission + send email.
 * Returns: null  — wrong form / not submitted
 *          array — ['success' => bool] on send attempt
 *          array — ['errors'  => string[]] on validation fail
 * ============================================================ */
function ht_commission_process($form_id, $subject_prefix, $fields) {
    if (
        empty($_POST['ht_form_id']) ||
        $_POST['ht_form_id'] !== $form_id ||
        empty($_POST['ht_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ht_nonce'])), $form_id)
    ) {
        return null;
    }

    $errors = [];
    $lines  = [];

    foreach ($fields as $f) {
        $raw = isset($_POST[$f['id']]) ? $_POST[$f['id']] : '';
        $val = ($f['type'] === 'textarea')
            ? sanitize_textarea_field(wp_unslash($raw))
            : sanitize_text_field(wp_unslash($raw));
        if (!empty($f['required']) && $val === '') {
            $errors[] = $f['label'] . ' is required.';
        }
        if ($val !== '') {
            $lines[] = $f['label'] . ': ' . $val;
        }
    }

    if (!empty($errors)) {
        return ['errors' => $errors];
    }

    $first = sanitize_text_field(wp_unslash($_POST['first_name'] ?? ''));
    $last  = sanitize_text_field(wp_unslash($_POST['last_name']  ?? ''));
    $name  = trim($first . ' ' . $last) ?: $first;
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));

    $body  = "New {$subject_prefix} inquiry from {$name} <{$email}>\n\n";
    $body .= implode("\n", $lines);
    $body .= "\n\n---\nSent via " . get_bloginfo('name') . ' — ' . get_bloginfo('url');

    $subject = "[{$subject_prefix}] Inquiry from {$first}";
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        "Reply-To: {$name} <{$email}>",
    ];

    $sent = wp_mail(HT_COMMISSION_EMAIL, $subject, $body, $headers);

    /* Best-effort receipt back to the submitter — doesn't affect $sent /
     * the on-page success state, since the inquiry itself already landed. */
    if ($sent && is_email($email)) {
        $confirm_subject = "Got it — your {$subject_prefix} inquiry has landed";
        $confirm_body  = "Hey {$first},\n\n";
        $confirm_body .= "Your {$subject_prefix} inquiry just landed in my inbox. I'll review it and reply within 2–3 business days.\n\n";
        $confirm_body .= "For your records, here's what you sent:\n\n";
        $confirm_body .= implode("\n", $lines);
        $confirm_body .= "\n\n---\n" . get_bloginfo('name') . ' — ' . get_bloginfo('url');
        $confirm_headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . HT_COMMISSION_EMAIL,
        ];
        wp_mail($email, $confirm_subject, $confirm_body, $confirm_headers);
    }

    return ['success' => $sent];
}

/* ============================================================
 * Render a single field.
 * ============================================================ */
function ht_commission_field($f) {
    $id       = esc_attr($f['id']);
    $type     = $f['type'] ?? 'text';

    if ($type === 'hint') {
        echo '<div class="htf-field htf-field--hint">';
        echo '<p class="htf-hint">' . wp_kses_post($f['text']) . '</p>';
        echo '</div>';
        return;
    }

    $req      = !empty($f['required']);
    $req_attr = $req ? ' required' : '';
    $posted   = isset($_POST[$f['id']]) ? wp_unslash($_POST[$f['id']]) : '';

    echo '<div class="htf-field">';
    echo '<label class="htf-label" for="' . $id . '">' . esc_html($f['label']);
    if ($req) echo ' <span class="htf-req" aria-hidden="true">*</span>';
    echo '</label>';

    switch ($type) {
        case 'textarea':
            $val = esc_textarea(sanitize_textarea_field($posted));
            echo '<textarea class="htf-input" id="' . $id . '" name="' . $id . '" rows="5"' . $req_attr . '>' . $val . '</textarea>';
            break;
        case 'select':
            $val = sanitize_text_field($posted);
            echo '<select class="htf-input htf-select" id="' . $id . '" name="' . $id . '"' . $req_attr . '>';
            echo '<option value="">— Select —</option>';
            foreach ($f['options'] as $opt) {
                $sel = ($val === $opt) ? ' selected' : '';
                echo '<option value="' . esc_attr($opt) . '"' . $sel . '>' . esc_html($opt) . '</option>';
            }
            echo '</select>';
            break;
        default:
            $val = esc_attr(sanitize_text_field($posted));
            echo '<input class="htf-input" type="' . esc_attr($type) . '" id="' . $id . '" name="' . $id . '" value="' . $val . '"' . $req_attr . '>';
    }

    echo '</div>';
}

/* ============================================================
 * Render the full form (process + output).
 * ============================================================ */
function ht_commission_render($form_id, $subject_prefix, $fields) {
    $result = ht_commission_process($form_id, $subject_prefix, $fields);
    ob_start();

    echo '<div class="htf-wrap">';

    if (isset($result['success'])) {
        if ($result['success']) {
            echo '<div class="htf-success">';
            echo '<div class="htf-success-icon" aria-hidden="true">&#9670;</div>';
            echo '<h3 class="htf-success-title">Inquiry Received</h3>';
            echo '<p class="htf-success-msg">Thank you — I\'ll review your inquiry and reply within 2&#8211;3 business days.</p>';
            echo '</div>';
        } else {
            echo '<div class="htf-error-box">There was a problem sending your message. Please try again or <a href="mailto:' . esc_attr(HT_COMMISSION_EMAIL) . '">email directly</a>.</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }

    if (!empty($result['errors'])) {
        echo '<div class="htf-error-box" role="alert"><ul class="htf-error-list">';
        foreach ($result['errors'] as $e) {
            echo '<li>' . esc_html($e) . '</li>';
        }
        echo '</ul></div>';
    }

    echo '<form class="htf-form" method="post" action="">';
    wp_nonce_field($form_id, 'ht_nonce');
    echo '<input type="hidden" name="ht_form_id" value="' . esc_attr($form_id) . '">';

    foreach ($fields as $f) {
        ht_commission_field($f);
    }

    echo '<div class="htf-footer">';
    echo '<p class="htf-req-note"><span class="htf-req">*</span> Required fields</p>';
    echo '<button type="submit" class="htf-submit">Send Inquiry &nbsp;&rarr;</button>';
    echo '</div>';
    echo '</form>';
    echo '</div>';

    return ob_get_clean();
}

/* ============================================================
 * [ht_commission_art] — Art Commissions
 * ============================================================ */
add_shortcode('ht_commission_art', function () {
    return ht_commission_render('ht_art', 'Art Commission', [
        ['id' => 'first_name',  'label' => 'First Name',                             'type' => 'text',     'required' => true],
        ['id' => 'last_name',   'label' => 'Last Name',                              'type' => 'text',     'required' => false],
        ['id' => 'email',       'label' => 'Email Address',                          'type' => 'email',    'required' => true],
        ['id' => 'comm_type',   'label' => 'Commission Type',                        'type' => 'select',   'required' => true,
         'options' => ['Character Portrait', 'Scene / Illustration', 'Cyber-Gothic / Dark Art', 'Couple / Multi-character', 'Other']],
        ['id' => 'char_count',  'label' => 'Number of Characters',                   'type' => 'select',   'required' => false,
         'options' => ['1', '2', '3+']],
        ['id' => 'description', 'label' => 'Description — Pose, Mood, Details',      'type' => 'textarea', 'required' => true],
        ['id' => 'references',  'label' => 'Reference Links (Pinterest, Imgur, Drive…)', 'type' => 'text', 'required' => false],
        ['id' => 'license',     'label' => 'License Needed',                         'type' => 'select',   'required' => true,
         'options' => ['Personal use only', 'Commercial — book cover', 'Commercial — merchandise / print', 'Other (describe below)']],
        ['id' => 'budget',      'label' => 'Budget Range',                           'type' => 'select',   'required' => true,
         'options' => ['Under $50', '$50 – $100', '$100 – $200', '$200 – $500', '$500+', "Not sure — let's talk"]],
        ['id' => 'deadline',    'label' => 'Deadline',                               'type' => 'select',   'required' => false,
         'options' => ['Flexible', '2 months+', '1 month', '2 – 4 weeks', 'Under 2 weeks', 'Rush — under 1 week']],
        ['id' => 'extra',       'label' => 'Anything else I should know?',           'type' => 'textarea', 'required' => false],
        ['id' => 'referral',    'label' => 'How did you find me?',                   'type' => 'text',     'required' => false],
    ]);
});

/* ============================================================
 * [ht_commission_cover] — Book Cover Design
 * ============================================================ */
add_shortcode('ht_commission_cover', function () {
    return ht_commission_render('ht_cover', 'Book Cover', [
        ['id' => 'first_name',  'label' => 'First Name',                             'type' => 'text',     'required' => true],
        ['id' => 'last_name',   'label' => 'Last Name',                              'type' => 'text',     'required' => false],
        ['id' => 'pen_name',    'label' => 'Pen Name (if different)',                'type' => 'text',     'required' => false],
        ['id' => 'email',       'label' => 'Email Address',                          'type' => 'email',    'required' => true],
        ['id' => 'cover_type',  'label' => 'Cover Type',                             'type' => 'select',   'required' => true,
         'options' => ['Ebook only', 'Paperback wrap', 'Hardcover', 'Full series package', 'Premade — browse existing']],
        ['id' => 'genre',       'label' => 'Genre / Subgenre',                       'type' => 'text',     'required' => true],
        ['id' => 'book_title',  'label' => 'Book Title / Subtitle / Series Name',    'type' => 'text',     'required' => true],
        ['id' => 'logline',     'label' => 'Tagline or Logline (1–2 sentences)',      'type' => 'textarea', 'required' => false],
        ['id' => 'comps',       'label' => 'Comparable Covers (URLs or describe)',    'type' => 'textarea', 'required' => false],
        ['id' => 'characters',  'label' => 'Character Description (if figures needed)', 'type' => 'textarea', 'required' => false],
        ['id' => 'palette',     'label' => 'Color Palette Preferences',              'type' => 'text',     'required' => false],
        ['id' => 'trim',        'label' => 'Trim Size (print covers, e.g. 6×9)',     'type' => 'text',     'required' => false],
        ['id' => 'budget',      'label' => 'Budget Range',                           'type' => 'select',   'required' => true,
         'options' => ['Under $100', '$100 – $200', '$200 – $400', '$400 – $700', '$700+', "Not sure — let's talk"]],
        ['id' => 'deadline',    'label' => 'Deadline',                               'type' => 'select',   'required' => false,
         'options' => ['Flexible', '2 months+', '1 month', '2 – 4 weeks', 'Under 2 weeks']],
        ['id' => 'extra',       'label' => 'Anything else?',                         'type' => 'textarea', 'required' => false],
    ]);
});

/* ============================================================
 * [ht_commission_ai] — AI Image Generation
 * ============================================================ */
add_shortcode('ht_commission_ai', function () {
    return ht_commission_render('ht_ai', 'AI Generation', [
        ['id' => 'first_name',  'label' => 'First Name',                             'type' => 'text',     'required' => true],
        ['id' => 'last_name',   'label' => 'Last Name',                              'type' => 'text',     'required' => false],
        ['id' => 'email',       'label' => 'Email Address',                          'type' => 'email',    'required' => true],
        ['id' => 'use_case',    'label' => 'Use Case',                               'type' => 'select',   'required' => true,
         'options' => ['Character art', 'Mood board', 'Chapter / scene banner', 'Social media assets', 'Book cover (AI-assisted)', 'Other']],
        ['id' => 'lora_interest', 'label' => 'LoRA Model Training',                  'type' => 'select',   'required' => false,
         'options' => ['Not interested — images only', 'Yes — I want a LoRA trained']],
        ['id' => 'lora_hint',   'type' => 'hint', 'text' => 'A LoRA (Low-Rank Adaptation) is a small fine-tuned model layer trained on your specific character, concept, or object. Once trained, it lets any compatible image-generation model produce consistent depictions of that subject across scenes and prompts — without re-describing it every time. LoRAs are trained to a specific base model. Note: style LoRAs are not offered.'],
        ['id' => 'lora_type',   'label' => 'LoRA Type (if applicable)',              'type' => 'select',   'required' => false,
         'options' => ['Character (person, OC, face)', 'Concept (item, theme, motif)', 'Object (prop, vehicle, location)']],
        ['id' => 'lora_model',  'label' => 'Target Base Model (if applicable)',      'type' => 'select',   'required' => false,
         'options' => ['SDXL', 'Illustrious', 'Anima', 'Z']],
        ['id' => 'style_ref',   'label' => 'Style Reference (URLs or describe)',     'type' => 'textarea', 'required' => false],
        ['id' => 'dimensions',  'label' => 'Aspect Ratio / Dimensions',              'type' => 'select',   'required' => false,
         'options' => ['Square (1:1)', 'Portrait (2:3)', 'Landscape (16:9)', 'Wide banner (3:1)', 'Multiple / mix']],
        ['id' => 'quantity',    'label' => 'Number of Final Images',                 'type' => 'select',   'required' => true,
         'options' => ['1', '2 – 3', '4 – 6', '7 – 10', '10+']],
        ['id' => 'finishing',   'label' => 'Post-processing / Hand Finishing',       'type' => 'select',   'required' => false,
         'options' => ['Yes — full hand finish', 'Yes — light touch-up only', 'No — AI output as-is']],
        ['id' => 'platform',    'label' => 'Where will it be used?',                 'type' => 'text',     'required' => false],
        ['id' => 'budget',      'label' => 'Budget Range',                           'type' => 'select',   'required' => true,
         'options' => ['Under $30', '$30 – $75', '$75 – $150', '$150 – $300', '$300+', "Not sure — let's talk"]],
        ['id' => 'deadline',    'label' => 'Deadline',                               'type' => 'select',   'required' => false,
         'options' => ['Flexible', '1 month', '2 – 4 weeks', 'Under 2 weeks', 'Rush — under 1 week']],
        ['id' => 'extra',       'label' => 'Anything else?',                         'type' => 'textarea', 'required' => false],
    ]);
});
