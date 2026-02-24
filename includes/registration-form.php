<?php
function se_get_email_logo_html() {
    $logo_html = '';
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $logo_image = wp_get_attachment_image_src($custom_logo_id, 'full');
        if ($logo_image) {
            $logo_url = $logo_image[0];
            $site_name = get_bloginfo('name');
            $logo_html = "<div style='text-align:center; margin-bottom:20px;'><img src='" . esc_url($logo_url) . "' alt='" . esc_attr($site_name) . "' style='max-width:200px; height:auto;'></div>";
        }
    }
    return $logo_html;
}

/**
 * Render a single form field based on field config.
 */
function se_render_form_field($field, $id_prefix = 'se') {
    $key      = esc_attr($field['key']);
    $label    = esc_html($field['label']);
    $type     = $field['type'] ?? 'text';
    $required = !empty($field['required']) ? 'required' : '';
    $req_star = !empty($field['required']) ? ' <span style="color:red;">*</span>' : '';
    $field_id = $id_prefix . '_' . $key;
    $field_name = 'se_' . $key;
    $style    = 'width:100%; padding: 8px; box-sizing: border-box;';
    $placeholder = esc_attr('Enter ' . $field['label']);

    echo '<p>';
    echo '<label for="' . $field_id . '">' . $label . ':' . $req_star . '</label><br>';

    switch ($type) {
        case 'textarea':
            echo '<textarea name="' . $field_name . '" id="' . $field_id . '" ' . $required . ' style="' . $style . ' height:80px;" placeholder="' . $placeholder . '"></textarea>';
            break;
        case 'select':
            echo '<select name="' . $field_name . '" id="' . $field_id . '" ' . $required . ' style="' . $style . '">';
            echo '<option value="">-- Select --</option>';
            if (!empty($field['options'])) {
                $options = array_map('trim', explode(',', $field['options']));
                foreach ($options as $opt) {
                    echo '<option value="' . esc_attr($opt) . '">' . esc_html($opt) . '</option>';
                }
            }
            echo '</select>';
            break;
        case 'checkbox':
            if (!empty($field['options'])) {
                $options = array_map('trim', explode(',', $field['options']));
                echo '<div style="padding:4px 0;">';
                foreach ($options as $idx => $opt) {
                    $opt_id = $field_id . '_' . $idx;
                    echo '<label style="display:block; margin-bottom:4px; cursor:pointer;">';
                    echo '<input type="checkbox" name="' . $field_name . '[]" value="' . esc_attr($opt) . '" id="' . $opt_id . '" style="margin-right:6px;">';
                    echo esc_html($opt);
                    echo '</label>';
                }
                echo '</div>';
            }
            break;
        case 'radio':
            if (!empty($field['options'])) {
                $options = array_map('trim', explode(',', $field['options']));
                echo '<div style="padding:4px 0;">';
                foreach ($options as $idx => $opt) {
                    $opt_id = $field_id . '_' . $idx;
                    echo '<label style="display:block; margin-bottom:4px; cursor:pointer;">';
                    echo '<input type="radio" name="' . $field_name . '" value="' . esc_attr($opt) . '" id="' . $opt_id . '" ' . $required . ' style="margin-right:6px;">';
                    echo esc_html($opt);
                    echo '</label>';
                }
                echo '</div>';
            }
            break;
        case 'tel':
            echo '<input type="tel" name="' . $field_name . '" id="' . $field_id . '" ' . $required . ' class="se-intl-phone" style="' . $style . '" placeholder="' . $placeholder . '">';
            break;
        default:
            echo '<input type="' . esc_attr($type) . '" name="' . $field_name . '" id="' . $field_id . '" ' . $required . ' style="' . $style . '" placeholder="' . $placeholder . '">';
            break;
    }

    echo '</p>';
}

/**
 * Enqueue intl-tel-input library for phone fields with country code.
 */
function se_enqueue_intl_tel_input() {
    static $enqueued = false;
    if ($enqueued) return;
    $enqueued = true;
    wp_enqueue_style('intl-tel-input', 'https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.21/build/css/intlTelInput.css', [], '17.0.21');
    wp_enqueue_script('intl-tel-input', 'https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.21/build/js/intlTelInput.min.js', [], '17.0.21', true);
}

/**
 * Output intl-tel-input init script (call after form HTML).
 */
function se_render_intl_tel_init() {
    ?>
    <style>.iti{width:100%}</style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.intlTelInput === 'undefined') return;
        var phoneInputs = document.querySelectorAll('.se-intl-phone');
        phoneInputs.forEach(function(input) {
            var iti = window.intlTelInput(input, {
                initialCountry: 'id',
                preferredCountries: ['id', 'my', 'sg', 'us', 'au', 'jp'],
                separateDialCode: true,
                utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.21/build/js/utils.js'
            });
            var form = input.closest('form');
            if (form) {
                form.addEventListener('submit', function() {
                    if (typeof iti.getNumber === 'function') {
                        input.value = iti.getNumber();
                    }
                });
            }
        });
    });
    </script>
    <?php
}

function se_event_registration_form($atts) {
    ob_start();
    global $post, $wpdb;

    $event_id = $post->ID;
    $quota = (int) get_post_meta($event_id, '_se_event_quota', true);
    $current = se_get_registered_count($event_id);
    $table_name = $wpdb->prefix . 'event_submissions';
    $form_fields = se_get_event_form_fields($event_id);
    $default_keys = ['name', 'email', 'phone', 'company', 'job_title'];

    // Check if tel field exists and enqueue intl-tel-input
    $has_tel = false;
    foreach ($form_fields as $ff) {
        if (($ff['type'] ?? 'text') === 'tel') { $has_tel = true; break; }
    }
    if ($has_tel) { se_enqueue_intl_tel_input(); }

    if ($current >= $quota) {
        echo '<p><strong>Registration closed (quota full).</strong></p>';
        return ob_get_clean();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['se_register'])) {
        $db_data = [
            'event_id'   => $event_id,
            'created_at' => current_time('mysql'),
        ];
        $custom_data = [];

        foreach ($form_fields as $field) {
            $key = $field['key'];
            $post_key = 'se_' . $key;
            $field_type = $field['type'] ?? 'text';

            if ($field_type === 'checkbox' && isset($_POST[$post_key]) && is_array($_POST[$post_key])) {
                $value = implode(', ', array_map('sanitize_text_field', $_POST[$post_key]));
            } else {
                $value = isset($_POST[$post_key]) ? sanitize_text_field($_POST[$post_key]) : '';
            }

            if (in_array($key, $default_keys)) {
                $db_data[$key] = ($key === 'email') ? sanitize_email($value) : sanitize_text_field($value);
            } else {
                $custom_data[$key] = $value;
            }
        }

        if (!empty($custom_data)) {
            $db_data['custom_fields'] = wp_json_encode($custom_data);
        }

        $email = $db_data['email'] ?? '';
        $name = $db_data['name'] ?? '';

        // Check if already registered
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE event_id = %d AND email = %s",
            $event_id,
            $email
        ));

        if ($exists > 0) {
            echo '<p style="color:red;">This email is already registered for this event.</p>';
        } else {
            $wpdb->insert($table_name, $db_data);
            $submission_id = $wpdb->insert_id;

            // Send confirmation email with event details + QR ticket
            $event_title = get_the_title($event_id);
            $start_date = get_post_meta($event_id, '_se_event_start_date', true);
            $start_time = get_post_meta($event_id, '_se_event_start_time', true);
            $end_date = get_post_meta($event_id, '_se_event_end_date', true);
            $end_time = get_post_meta($event_id, '_se_event_end_time', true);
            $location = get_post_meta($event_id, '_se_event_location', true);
            $display_date = !empty($start_date) ? date_i18n('l, d F Y', strtotime($start_date)) : '-';
            $event_url = get_permalink($event_id);
            $ticket_qr_html = se_get_ticket_qr_html($submission_id);
            $feedback_url = get_post_meta($event_id, '_se_event_feedback_form_url', true);
            $meeting_url = get_post_meta($event_id, '_se_event_meeting_url', true);

            $meeting_html = '';
            if (!empty($meeting_url)) {
                $meeting_html = "
    <div style='background: #E8F5E9; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #4CAF50;'>
        <p style='margin: 0 0 10px; font-weight: bold;'>Join the Event Online</p>
        <p style='margin: 0 0 10px;'>Use the link below to join the event via Zoom, Google Meet, or the designated platform:</p>
        <p style='margin: 0;'><a href='" . esc_url($meeting_url) . "' style='display:inline-block; background:#4CAF50; color:#fff; padding:10px 20px; text-decoration:none; border-radius:4px; font-weight:bold;'>Join Meeting</a></p>
    </div>";
            }

            $feedback_html = '';
            if (!empty($feedback_url)) {
                $feedback_html = "
    <div style='background: #FFF8E1; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #FFC107;'>
        <p style='margin: 0 0 10px; font-weight: bold;'>We Value Your Feedback!</p>
        <p style='margin: 0 0 10px;'>Please take a moment to share your feedback about this event:</p>
        <p style='margin: 0;'><a href='" . esc_url($feedback_url) . "' style='display:inline-block; background:#FFC107; color:#333; padding:10px 20px; text-decoration:none; border-radius:4px; font-weight:bold;'>Give Feedback</a></p>
    </div>";
            }

            $subject = 'Registration Confirmation - ' . $event_title;
            $logo_html = se_get_email_logo_html();
            $message = "
<html><body style='font-family: Arial, sans-serif; color: #333;'>
<div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
    {$logo_html}
    <h2 style='color: #EA242A;'>Registration Successful!</h2>
    <p>Hello <strong>{$name}</strong>,</p>
    <p>Thank you for registering for the following event:</p>
    <div style='background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 15px 0;'>
        <h3 style='margin-top: 0;'>{$event_title}</h3>
        <p><strong>Date:</strong> {$display_date}</p>
        <p><strong>Time:</strong> {$start_time} - {$end_time}</p>
        <p><strong>Location:</strong> {$location}</p>
    </div>
    {$meeting_html}
    {$ticket_qr_html}
    {$feedback_html}
    <p><a href='{$event_url}' style='display:inline-block; background:#EA242A; color:white; padding:10px 20px; text-decoration:none; border-radius:4px;'>View Event Details</a></p>
    <hr style='margin: 20px 0; border: none; border-top: 1px solid #ddd;'>
    <p style='font-size: 12px; color: #888;'>This email was sent automatically, please do not reply to this email.</p>
</div>
</body></html>";

            $headers = ['Content-Type: text/html; charset=UTF-8'];
            wp_mail($email, $subject, $message, $headers);

            echo '<p style="color:green;">Registration successful! Check your email for event details.</p>';
        }
    }

    ?>
    <div style="width: 100%;">
        <form method="post">
            <?php foreach ($form_fields as $field): ?>
                <?php se_render_form_field($field, 'se'); ?>
            <?php endforeach; ?>
            <p><button type="submit" name="se_register" style="width:100%; padding: 12px 20px; background-color: #EA242A; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 16px;">Register Now</button></p>
        </form>
    </div>
    <?php
    if ($has_tel) { se_render_intl_tel_init(); }

    return ob_get_clean();
}

add_shortcode('event_registration_form', 'se_event_registration_form');

function se_get_registered_count($event_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'event_submissions';

    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE event_id = %d",
        $event_id
    ));
}
