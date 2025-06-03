<?php

/**
 * Plugin Name: User Point Levels
 * Description: Plugin untuk mengelola Level berdasarkan Point.
 * Version: 1.1.2
 * Author: Samsul Arifin
 * Author: Samsul Arifin
 * Link : https://github.com/samsularifin05/user-point-level-wordpres
 */

// Cegah akses langsung
if (! defined('ABSPATH')) {
    exit;
}

// Load file fungsi
require_once plugin_dir_path(__FILE__) . 'includes/setup.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/assets.php';
require_once plugin_dir_path(__FILE__) . 'includes/handlers.php';
require_once plugin_dir_path(__FILE__) . 'includes/inject-rank-all-bbPres.php';
require_once plugin_dir_path(__FILE__) . 'includes/update-profile-bbpres.php';
require_once plugin_dir_path(__FILE__) . 'includes/edit-data-user.php';
require_once plugin_dir_path(__FILE__) . 'includes/learnpress-filter-api.php';

// Buat Menu di Admin
add_action('admin_menu', function () {
    add_menu_page(
        'User Point Levels',
        'User Point Levels',
        'manage_options',
        'user-point-level',
        'upl_settings_page',
        plugin_dir_url(__FILE__) . 'assets/icon.png'
    );
});

//Asstes
add_action('wp_enqueue_scripts', 'upl_enqueue_styles');
add_action('wp_enqueue_scripts', 'upl_enqueue_custom_js');

//Crud
add_action('admin_post_upl_delete_level', 'upl_delete_level_handler');
add_action('admin_post_upl_save_levels', 'upl_save_levels_handler');

//Add image to body
add_filter('body_class', 'upl_add_user_data_to_body');

// Buat tabel saat plugin diaktifkan
register_activation_hook(__FILE__, 'upl_create_table');
register_uninstall_hook(__FILE__, 'upl_uninstall');
