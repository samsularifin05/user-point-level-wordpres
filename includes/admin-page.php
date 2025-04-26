<?php
if (!defined('ABSPATH')) {
    exit;
}

function upl_settings_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_point_levels';

    $levels = $wpdb->get_results("SELECT * FROM $table_name");

?>
<div class="wrap">
    <h1>User Point Levels</h1>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upl_save_levels">
        <?php wp_nonce_field('upl_save_levels_nonce', 'upl_save_levels_nonce_field'); ?>

        <table class="form-table">
            <tr>
                <th><label for="level_name">Level Name</label></th>
                <td><input type="text" name="level_name" id="level_name" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="level_point">Point</label></th>
                <td><input type="number" name="level_point" id="level_point" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="image_url">Image Upload</label></th>
                <td><input type="file" name="image_url" id="image_url" class="regular-text"></td>
            </tr>
        </table>

        <?php submit_button('Save'); ?>
    </form>


    <hr>

    <!-- TABEL DATA YANG SUDAH DISIMPAN -->
    <h2>Daftar Levels</h2>
    <table class="widefat">
        <thead>
            <tr>
                <th>ID</th>
                <th>Level Name</th>
                <th>Point</th>
                <th>Image URL</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($levels) : ?>
            <?php foreach ($levels as $level) : ?>
            <tr>
                <td><?php echo esc_html($level->id); ?></td>
                <td><?php echo esc_html($level->level_name); ?></td>
                <td><?php echo esc_html($level->point); ?></td>
                <td><img style="width: 100px; height:100px" src="<?php echo esc_html($level->image_url); ?>" /></td>
                <td>
                    <!-- Tombol Delete -->
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                        style="display:inline;">
                        <input type="hidden" name="action" value="upl_delete_level">
                        <input type="hidden" name="level_id" value="<?php echo esc_attr($level->id); ?>">
                        <?php wp_nonce_field('upl_delete_level_nonce', 'upl_delete_level_nonce_field'); ?>
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this level?');"
                            style="background-color: #e3342f; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
                            Delete
                        </button>

                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php else : ?>
            <tr>
                <td colspan="5">Belum ada level.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
}