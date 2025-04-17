<?php
// Post Type: Event
function se_register_event_post_type() {
    register_post_type('event', [
        'labels' => [
            'name' => 'Events',
            'singular_name' => 'Event',
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'event'],
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => ['title', 'editor', 'thumbnail'],
    ]);
}
add_action('init', 'se_register_event_post_type');

// Tambahkan meta box untuk menampilkan pendaftar
function se_add_submission_meta_box() {
    add_meta_box(
        'se_event_submissions',
        'Daftar Pendaftar',
        'se_render_submission_meta_box',
        'event',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'se_add_submission_meta_box');

// Render isi meta box
function se_render_submission_meta_box($post) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'event_submissions';
    $event_id = $post->ID;

    // Notifikasi jika pendaftar dihapus
    if (isset($_GET['message']) && $_GET['message'] === 'deleted') {
        echo '<div class="notice notice-success is-dismissible"><p>Pendaftar berhasil dihapus.</p></div>';
    }

    // Pagination setup
    $per_page = 15;
    $paged = isset($_GET['paged_submission']) ? max(1, intval($_GET['paged_submission'])) : 1;
    $offset = ($paged - 1) * $per_page;

    // Hitung total
    $total = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE event_id = %d",
        $event_id
    ));
    $total_pages = ceil($total / $per_page);

    // Tombol Export
    echo '<p>';
    echo '<a href="' . esc_url(add_query_arg(['se_export' => 'csv', 'event_id' => $event_id])) . '" class="button button-primary" style="margin-right:10px;">Export CSV</a>';

    // Ambil data sesuai pagination
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE event_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $event_id, $per_page, $offset
    ));

    if ($results) {
        echo '<table class="widefat striped">';
        echo '<thead><tr>
            <th>Nama</th>
            <th>Email</th>
            <th>No HP</th>
            <th>Company</th>
            <th>Job Title</th>
            <th>Waktu Daftar</th>
            <th>Barcode</th>
            <th>Aksi</th>
        </tr></thead>';
        echo '<tbody>';
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->name) . '</td>';
            echo '<td>' . esc_html($row->email) . '</td>';
            echo '<td>' . esc_html(isset($row->phone) ? $row->phone : '-') . '</td>';
            echo '<td>' . esc_html(isset($row->company) ? $row->company : '-') . '</td>';
            echo '<td>' . esc_html(isset($row->job_title) ? $row->job_title : '-') . '</td>';
            echo '<td>' . esc_html(date('d M Y H:i', strtotime($row->created_at))) . '</td>';
            echo '<td><img src="' . plugin_dir_url(__FILE__) . 'barcode-generator.php?code=' . urlencode($row->id) . '" alt="barcode" /></td>';

            $delete_url = wp_nonce_url(
                admin_url('admin-post.php?action=se_delete_submission&submission_id=' . $row->id . '&event_id=' . $event_id),
                'se_delete_submission_' . $row->id
            );

            echo '<td><a href="' . esc_url($delete_url) . '" class="button button-small" onclick="return confirm(\'Yakin ingin menghapus pendaftar ini?\')">Hapus</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';

        // Pagination controls
        echo '<div style="margin-top:15px;">';
        for ($i = 1; $i <= $total_pages; $i++) {
            $url = add_query_arg([
                'paged_submission' => $i,
                'post' => $post->ID,
                'action' => 'edit'
            ]);
            if ($i === $paged) {
                echo "<strong style='margin-right:5px;'>$i</strong>";
            } else {
                echo "<a href='" . esc_url($url) . "' style='margin-right:5px;'>$i</a>";
            }
        }
        echo '</div>';
    } else {
        echo '<p>Belum ada pendaftar.</p>';
    }
}

// Handler untuk menghapus submission
add_action('admin_post_se_delete_submission', 'se_handle_delete_submission');

function se_handle_delete_submission() {
    if (!current_user_can('edit_posts') || !isset($_GET['submission_id']) || !isset($_GET['event_id'])) {
        wp_die('Akses tidak sah');
    }

    $submission_id = intval($_GET['submission_id']);
    $event_id = intval($_GET['event_id']);

    if (!wp_verify_nonce($_GET['_wpnonce'], 'se_delete_submission_' . $submission_id)) {
        wp_die('Nonce tidak valid');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'event_submissions';

    $wpdb->delete($table_name, ['id' => $submission_id]);

    wp_redirect(admin_url('post.php?post=' . $event_id . '&action=edit&message=deleted'));
    exit;
}
