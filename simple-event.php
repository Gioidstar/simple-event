<?php
/**
 * Plugin Name: Simple Event
 * Description: Plugin untuk membuat event dan form pendaftaran dengan sistem submission.
 * Version: 1.0.1
 * Author: Gio fandi
 */


// Load all includes
require_once plugin_dir_path(__FILE__) . 'includes/post-types.php';
require_once plugin_dir_path(__FILE__) . 'includes/meta-boxes.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration-form.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcode-event-list.php';// menampilkan data submission di admin
require_once plugin_dir_path(__FILE__) . 'includes/replay-form.php';
require_once plugin_dir_path(__FILE__) . 'includes/ticket.php';

// Elementor Integration
function se_register_elementor_widgets($widgets_manager) {
    require_once plugin_dir_path(__FILE__) . 'includes/widgets/elementor-event-grid.php';
    $widgets_manager->register(new SE_Elementor_Event_Grid_Widget());
}
add_action('elementor/widgets/register', 'se_register_elementor_widgets');

// Auto-migrasi kolom form_type jika belum ada
function se_maybe_migrate_form_type_column() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'event_submissions';

    // Cek apakah tabel ada
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        return;
    }

    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name", ARRAY_A);
    $existing_columns = array_column($columns, 'Field');

    if (!in_array('form_type', $existing_columns)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN form_type VARCHAR(20) NOT NULL DEFAULT 'registration'");
    }
}
add_action('init', 'se_maybe_migrate_form_type_column');

// Auto-migrasi kolom custom_fields jika belum ada
function se_maybe_migrate_custom_fields_column() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'event_submissions';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        return;
    }

    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name", ARRAY_A);
    $existing_columns = array_column($columns, 'Field');

    if (!in_array('custom_fields', $existing_columns)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN custom_fields TEXT DEFAULT NULL");
    }
}
add_action('init', 'se_maybe_migrate_custom_fields_column');

// Load single event template
function simple_event_template($template) {
    if (is_singular('event')) {
        $custom_template = plugin_dir_path(__FILE__) . 'templates/single-event.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter('single_template', 'simple_event_template');

// Buat tabel event_submissions saat plugin diaktifkan
register_activation_hook(__FILE__, 'se_create_event_submission_table');

function se_create_event_submission_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'event_submissions';
    $charset_collate = $wpdb->get_charset_collate();

    // Buat tabel jika belum ada
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        event_id BIGINT(20) UNSIGNED NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        company VARCHAR(100) DEFAULT NULL,
        job_title VARCHAR(100) DEFAULT NULL,
        custom_fields TEXT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX (event_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql); // Aman digunakan berkali-kali

    // Tambahkan kolom jika belum tersedia (jaga-jaga)
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name", ARRAY_A);
    $existing_columns = array_column($columns, 'Field');

    $alter_queries = [];

    if (!in_array('phone', $existing_columns)) {
        $alter_queries[] = "ADD COLUMN phone VARCHAR(20) DEFAULT NULL";
    }
    if (!in_array('company', $existing_columns)) {
        $alter_queries[] = "ADD COLUMN company VARCHAR(100) DEFAULT NULL";
    }
    if (!in_array('job_title', $existing_columns)) {
        $alter_queries[] = "ADD COLUMN job_title VARCHAR(100) DEFAULT NULL";
    }
    if (!in_array('form_type', $existing_columns)) {
        $alter_queries[] = "ADD COLUMN form_type VARCHAR(20) NOT NULL DEFAULT 'registration'";
    }
    if (!in_array('custom_fields', $existing_columns)) {
        $alter_queries[] = "ADD COLUMN custom_fields TEXT DEFAULT NULL";
    }

    if (!empty($alter_queries)) {
        $wpdb->query("ALTER TABLE $table_name " . implode(", ", $alter_queries));
    }
}


