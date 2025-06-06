<?php

add_action('init', function () {
    upl_get_bbpress_users_in_forums_topics();
});

function upl_get_bbpress_users_in_forums_topics()
{
    $users = [];

    // Dapatkan semua topik (bbPress Topics)
    $topics = get_posts([
        'post_type' => 'topic',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ]);

    if (!empty($topics)) {
        foreach ($topics as $topic) {
            $author_id = $topic->post_author;
            if (!in_array($author_id, $users)) {
                $users[] = $author_id;
            }
        }
    }

    // Dapatkan semua reply (bbPress Replies)
    $replies = get_posts([
        'post_type' => 'reply',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ]);

    if (!empty($replies)) {
        foreach ($replies as $reply) {
            $author_id = $reply->post_author;
            if (!in_array($author_id, $users)) {
                $users[] = $author_id;
            }
        }
    }

    // Ambil data user lengkap
    $user_data = [];
    global $wpdb;

    foreach ($users as $user_id) {
        $user = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}users WHERE ID = %d", $user_id)
        );

        $levels = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}user_point_levels ORDER BY point ASC"
        );

        $current_image = '';
        $levelname = '';

        foreach ($levels as $level) {
            if ((int)$user->total_points >= (int)$level->point) {
                $current_image = $level->image_url;
                $levelname = $level->level_name;
            }
        }

        if ($user) {
            $user_data[] = [
                'ID' => $user->ID,
                'user_login' => $user->user_login,
                'display_name' => $user->display_name,
                'rank_image' => $current_image,
                'rank_level' => $levelname,
            ];
        }
    }

    return $user_data;
}

$bbpress_users = upl_get_bbpress_users_in_forums_topics();

add_action('wp_enqueue_scripts', function () {
    // Ambil data pengguna bbPress
    $bbpress_users = upl_get_bbpress_users_in_forums_topics();

    if (!empty($bbpress_users)) {
        // Enqueue script dan kirim data ke JS
        wp_enqueue_script('custom-bbpress-script', plugin_dir_url(__FILE__) . '../assets/user-point-level.js', [], null, true);

        // Menggunakan wp_localize_script untuk mengirim data ke JS
        wp_localize_script('custom-bbpress-script', 'bbpress_users_data', [
            'users' => $bbpress_users // Data yang akan diteruskan ke JS
        ]);
    }
});