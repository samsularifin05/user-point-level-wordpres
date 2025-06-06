<?php
if (!defined('ABSPATH')) {
    exit;
}

function upl_enqueue_custom_js()
{
    wp_enqueue_script(
        'upl-custom-js', // <--- ini yang nanti jadi id="upl-custom-js"
        plugin_dir_url(__FILE__) . '../assets/user-point-level.js', // <-- BENER assets
        array('jquery'), // dependencies
        '1.1.8',         // version (nanti di URL: ?ver=1.0.0)
        true             // load in footer
    );
}

function upl_enqueue_styles()
{
    wp_enqueue_style('upl-rank-style', plugin_dir_url(__FILE__) . '../assets/upl-style.css', [], '1.1.5');
}