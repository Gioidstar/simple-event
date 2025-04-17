<?php
function se_event_registration_form($atts) {
    ob_start();
    global $post, $wpdb;

    $event_id = $post->ID;
    $quota = (int) get_post_meta($event_id, '_se_event_quota', true);
    $current = se_get_registered_count($event_id);
    $table_name = $wpdb->prefix . 'event_submissions';

    if ($current >= $quota) {
        echo '<p><strong>Pendaftaran ditutup (kuota penuh).</strong></p>';
        return ob_get_clean();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['se_register'])) {
        $name = sanitize_text_field($_POST['se_name']);
        $email = sanitize_email($_POST['se_email']);
        $phone = sanitize_text_field($_POST['se_phone']);
        $company = sanitize_text_field($_POST['se_company']);
        $job_title = sanitize_text_field($_POST['se_job_title']);

        // Cek apakah sudah pernah daftar
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE event_id = %d AND email = %s",
            $event_id,
            $email
        ));

        if ($exists > 0) {
            echo '<p style="color:red;">Email ini sudah terdaftar untuk event ini.</p>';
        } else {
            $wpdb->insert($table_name, [
                'event_id' => $event_id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'company' => $company,
                'job_title' => $job_title,
                'created_at' => current_time('mysql')
            ]);
            echo '<p style="color:green;">Pendaftaran berhasil!</p>';
        }
    }

    ?>
    <div style="border: 1px solid #ddd; border-radius: 10px; padding: 25px; background: #f9f9f9; max-width: 500px;">
        <form method="post">
            <p><label for="se_name">Nama:</label><br>
            <input type="text" name="se_name" id="se_name" required style="width:100%; padding: 8px;"></p>

            <p><label for="se_email">Email:</label><br>
            <input type="email" name="se_email" id="se_email" required style="width:100%; padding: 8px;"></p>

            <p><label for="se_phone">No HP:</label><br>
            <input type="text" name="se_phone" id="se_phone" required style="width:100%; padding: 8px;"></p>

            <p><label for="se_company">Company:</label><br>
            <input type="text" name="se_company" id="se_company" style="width:100%; padding: 8px;"></p>

            <p><label for="se_job_title">Job Title:</label><br>
            <input type="text" name="se_job_title" id="se_job_title" style="width:100%; padding: 8px;"></p>

            <p><button type="submit" name="se_register" style="padding: 10px 20px; background-color: #EA242A;">Daftar</button></p>
        </form>
    </div>
    <?php

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
