<?php
// Tambahkan meta box ke post type Event
function se_add_event_meta_boxes() {
    add_meta_box('se_event_details', 'Detail Event', 'se_render_event_meta_box', 'event', 'normal', 'default');
}
add_action('add_meta_boxes', 'se_add_event_meta_boxes');

// Render isi meta box
function se_render_event_meta_box($post) {
    // Get existing values
    $start_date = get_post_meta($post->ID, '_se_event_start_date', true);
    $start_time = get_post_meta($post->ID, '_se_event_start_time', true);
    $end_date = get_post_meta($post->ID, '_se_event_end_date', true);
    $end_time = get_post_meta($post->ID, '_se_event_end_time', true);
    $location = get_post_meta($post->ID, '_se_event_location', true);
    $quota = get_post_meta($post->ID, '_se_event_quota', true);
    
    // Set default time values if empty
    if (empty($start_time)) $start_time = '09:00';
    if (empty($end_time)) $end_time = '17:00';
    
    // Add nonce for security
    wp_nonce_field('se_event_meta_nonce', 'se_event_meta_nonce');
    ?>
    <div class="se-meta-section">
        <p><label for="se_event_start_date"><strong>Tanggal Mulai:</strong></label><br>
        <input type="date" id="se_event_start_date" name="se_event_start_date" value="<?php echo esc_attr($start_date); ?>" required>
        <label for="se_event_start_time" style="margin-left: 10px;"><strong>Jam Mulai:</strong></label>
        <input type="time" id="se_event_start_time" name="se_event_start_time" value="<?php echo esc_attr($start_time); ?>" required></p>

        <p><label for="se_event_end_date"><strong>Tanggal Selesai:</strong></label><br>
        <input type="date" id="se_event_end_date" name="se_event_end_date" value="<?php echo esc_attr($end_date); ?>" required>
        <label for="se_event_end_time" style="margin-left: 10px;"><strong>Jam Selesai:</strong></label>
        <input type="time" id="se_event_end_time" name="se_event_end_time" value="<?php echo esc_attr($end_time); ?>" required></p>

        <p><label for="se_event_location"><strong>Lokasi:</strong></label><br>
        <input type="text" id="se_event_location" name="se_event_location" value="<?php echo esc_attr($location); ?>" style="width:100%;"></p>

        <p><label for="se_event_quota"><strong>Kuota Maksimal:</strong></label><br>
        <input type="number" id="se_event_quota" name="se_event_quota" value="<?php echo esc_attr($quota); ?>" min="1"></p>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get form elements
        const startDateInput = document.getElementById('se_event_start_date');
        const startTimeInput = document.getElementById('se_event_start_time');
        const endDateInput = document.getElementById('se_event_end_date');
        const endTimeInput = document.getElementById('se_event_end_time');
        
        // Function to validate dates
        function validateDates() {
            const startDate = new Date(`${startDateInput.value}T${startTimeInput.value}`);
            const endDate = new Date(`${endDateInput.value}T${endTimeInput.value}`);
            
            if (endDate < startDate) {
                alert('Error: Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
                return false;
            }
            return true;
        }
        
        // Set up validation when the form is submitted
        const form = document.querySelector('#post');
        form.addEventListener('submit', function(e) {
            if (!validateDates()) {
                e.preventDefault();
            }
        });
        
        // Set up validation when end date or time changes
        endDateInput.addEventListener('change', validateDates);
        endTimeInput.addEventListener('change', validateDates);
        
        // If start date changes, update end date minimum
        startDateInput.addEventListener('change', function() {
            endDateInput.min = startDateInput.value;
            validateDates();
        });
    });
    </script>
    <style>
    .se-meta-section {
        background: #f9f9f9;
        padding: 15px;
        border-radius: 5px;
    }
    </style>
    <?php
}

// Simpan metadata saat event disimpan
function se_save_event_meta($post_id) {
    // Cek apakah ini autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    // Cek nonce untuk keamanan
    if (!isset($_POST['se_event_meta_nonce']) || !wp_verify_nonce($_POST['se_event_meta_nonce'], 'se_event_meta_nonce')) {
        return;
    }

    // Cek jenis post
    if (get_post_type($post_id) !== 'event') return;
    
    // Cek jika user memiliki izin
    if (!current_user_can('edit_post', $post_id)) return;

    // Validasi tanggal (server-side)
    if (isset($_POST['se_event_start_date']) && isset($_POST['se_event_end_date']) && 
        isset($_POST['se_event_start_time']) && isset($_POST['se_event_end_time'])) {
        
        $start_datetime = strtotime($_POST['se_event_start_date'] . ' ' . $_POST['se_event_start_time']);
        $end_datetime = strtotime($_POST['se_event_end_date'] . ' ' . $_POST['se_event_end_time']);
        
        if ($end_datetime < $start_datetime) {
            // Add error message
            add_settings_error(
                'se_event_dates',
                'se_invalid_dates',
                'Error: Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
                'error'
            );
            
            // Show error message
            settings_errors('se_event_dates');
            return;
        }
    }

    // Simpan masing-masing meta jika tersedia
    if (isset($_POST['se_event_start_date'])) {
        update_post_meta($post_id, '_se_event_start_date', sanitize_text_field($_POST['se_event_start_date']));
    }
    
    if (isset($_POST['se_event_start_time'])) {
        update_post_meta($post_id, '_se_event_start_time', sanitize_text_field($_POST['se_event_start_time']));
    }

    if (isset($_POST['se_event_end_date'])) {
        update_post_meta($post_id, '_se_event_end_date', sanitize_text_field($_POST['se_event_end_date']));
    }
    
    if (isset($_POST['se_event_end_time'])) {
        update_post_meta($post_id, '_se_event_end_time', sanitize_text_field($_POST['se_event_end_time']));
    }

    if (isset($_POST['se_event_location'])) {
        update_post_meta($post_id, '_se_event_location', sanitize_text_field($_POST['se_event_location']));
    }

    if (isset($_POST['se_event_quota'])) {
        update_post_meta($post_id, '_se_event_quota', intval($_POST['se_event_quota']));
    }
}
add_action('save_post', 'se_save_event_meta');

// Helper function to combine date and time for display
function se_get_event_datetime($post_id, $type = 'start') {
    $date = get_post_meta($post_id, "_se_event_{$type}_date", true);
    $time = get_post_meta($post_id, "_se_event_{$type}_time", true);
    
    if (empty($date)) return '';
    
    if (!empty($time)) {
        return $date . ' ' . $time;
    }
    
    return $date;
}