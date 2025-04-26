<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register handler untuk form post
function upl_save_levels_handler()
{
    // Cek nonce untuk validasi keamanan
    if (!isset($_POST['upl_save_levels_nonce_field']) || !wp_verify_nonce($_POST['upl_save_levels_nonce_field'], 'upl_save_levels_nonce')) {
        wp_die('Security check failed.');
    }

    if (!current_user_can('manage_options')) {
        wp_die('You are not allowed to do this.');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'user_point_levels';

    // Ambil data dari form
    $level_name = sanitize_text_field($_POST['level_name']);
    $level_point = intval($_POST['level_point']);

    // Menangani file upload
    if (!empty($_FILES['image_url']['name'])) {
        $upload = media_handle_upload('image_url', 0);

        if (is_wp_error($upload)) {
            wp_die('Upload gambar gagal: ' . $upload->get_error_message());
        }

        // Dapatkan URL gambar setelah upload
        $image_url = wp_get_attachment_url($upload);
    } else {
        // Jika tidak ada file gambar, biarkan URL kosong
        $image_url = '';
    }

    // Masukkan data ke database
    $result = $wpdb->insert($table_name, [
        'level_name' => $level_name,
        'point' => $level_point,
        'image_url' => $image_url,
    ], [
        '%s',
        '%d',
        '%s'
    ]);

    if ($result === false) {
        wp_die('Insert gagal: ' . $wpdb->last_error);
    }

    // Redirect kembali ke halaman settings
    wp_redirect(admin_url('admin.php?page=user-point-level'));
    exit;
}

//Delete
function upl_delete_level_handler()
{
    if (!isset($_POST['upl_delete_level_nonce_field']) || !wp_verify_nonce($_POST['upl_delete_level_nonce_field'], 'upl_delete_level_nonce')) {
        wp_die('Security check failed.');
    }

    if (!current_user_can('manage_options')) {
        wp_die('You are not allowed to do this.');
    }

    if (isset($_POST['level_id'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_point_levels';
        $level_id = intval($_POST['level_id']);

        $wpdb->delete($table_name, ['id' => $level_id]);
    }

    wp_redirect(admin_url('admin.php?page=user-point-level'));
    exit;
}

// Ambil level berdasarkan poin user
function get_user_level($user_id)
{
    global $wpdb;

    $dataUser = $wpdb->get_row(
        $wpdb->prepare("SELECT total_points FROM {$wpdb->prefix}users WHERE ID = %d", $user_id)
    );

    $total_points = (int) $dataUser->total_points;

    $levels = $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}user_point_levels ORDER BY point ASC"
    );

    $current_image = '';
    $levelname = '';
    foreach ($levels as $level) {
        if ($total_points >= (int)$level->point) {
            $current_image = $level->image_url;
            $levelname = $level->level_name;
        }
    }


    return [
        'level' => $levelname,
        'image' => $current_image,
    ];
}





// Hook untuk menambahkan tab baru ke LearnPress Profile
add_filter('learn-press/profile-tabs', function ($tabs) {
    $tabs['my-rank'] = array(
        'title'    => __('My Rank', 'your-textdomain'),
        'slug'     => 'my-rank',
        'priority' => 30,
        'callback' => 'upl_render_my_rank_tab',
        'icon'     => '<i class="upl-rank-icon"></i>', // Menambahkan icon custom
    );
    return $tabs;
});

// Fungsi untuk render konten tab "My Rank"
function upl_render_my_rank_tab()
{
    global $wpdb;

    // Ambil ID pengguna saat ini
    $user_id = get_current_user_id();

    // Ambil total points pengguna dari tabel users
    $dataUser = $wpdb->get_row(
        $wpdb->prepare("SELECT total_points FROM {$wpdb->prefix}users WHERE ID = %d", $user_id)
    );

    // Pastikan ada total_points untuk pengguna
    if (!$dataUser) {
        echo '<p>User not found.</p>';
        return;
    }

    $total_points = (int) $dataUser->total_points;

    // Ambil level dari tabel user_point_levels, urutkan berdasarkan point
    $levels = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}user_point_levels ORDER BY point ASC");

    // Tentukan level berdasarkan total_points
    $current_level = 'Unranked'; // Default level
    $current_image = ''; // Default image (jika ada)

    foreach ($levels as $level) {
        if ($total_points >= $level->point) {
            $current_level = $level->level_name;
            $current_image = isset($level->image_url) ? esc_url($level->image_url) : ''; // Ambil image URL jika ada
        } else {
            break; // Jika total_points kurang dari level berikutnya, berhenti
        }
    }

    // Tampilkan informasi level dan points
    echo '<div class="upl-my-rank">';
    echo '<h3>My Rank</h3>';
    echo '<p>Total Points: <strong>' . esc_html($total_points) . '</strong></p>';
    echo '<p>Your Current Level: <strong>' . esc_html($current_level) . '</strong></p>';

    if (!empty($current_image)) {
        echo '<div class="upl-level-image">';
        echo '<img src="' . $current_image . '" alt="' . esc_attr($current_level) . '" style="max-width:300px;">';
        echo '</div>';
    }

    echo '</div>';
}

function upl_add_user_data_to_body($classes)
{
    if (is_user_logged_in()) {
        global $wpdb;

        $user_id = get_current_user_id();
        $username = wp_get_current_user();

        $user = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}users WHERE ID = %d", $user_id)
        );

        $total_points = 0;
        if ($user && isset($user->total_points)) {
            $total_points = (int) $user->total_points;
        }

        $levels = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}user_point_levels ORDER BY point ASC"
        );

        $current_image = ''; // Default kosong
        foreach ($levels as $level) {
            if ($total_points >= (int)$level->point) {
                $current_image = $level->image_url; // Update terus kalau cocok
            }
        }

        echo 'data-user-name="' . esc_attr($username->user_login) . '" ';
        echo 'data-image-user-rank="' . esc_url($current_image) . '" ';
    }

    return $classes;
}