<?php
if (!defined('ABSPATH')) {
    exit;
}
function upl_create_table()
{
    global $wpdb;

    // Menambahkan tabel 'user_point_levels' jika belum ada
    $table_name = $wpdb->prefix . 'user_point_levels';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        level_name varchar(255) NOT NULL,
        point int NOT NULL,
        image_url varchar(255) DEFAULT '',
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Menambahkan kolom 'total_points' ke tabel wp_users jika belum ada
    $column_exists = $wpdb->get_results(
        "SHOW COLUMNS FROM {$wpdb->prefix}users LIKE 'total_points'"
    );

    if (empty($column_exists)) {
        // Kolom 'total_points' belum ada, jadi kita tambahkan
        $wpdb->query(
            "ALTER TABLE {$wpdb->prefix}users ADD COLUMN total_points INT DEFAULT 0"
        );
    }
}

// Hook untuk menghapus tabel dan kolom saat plugin di-uninstall
function upl_uninstall()
{
    global $wpdb;

    // Menghapus tabel user_point_levels
    $table_name = $wpdb->prefix . 'user_point_levels';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");

    // Menghapus kolom 'total_points' dari tabel wp_users
    $wpdb->query("ALTER TABLE {$wpdb->prefix}users DROP COLUMN IF EXISTS total_points");
}