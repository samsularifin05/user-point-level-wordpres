<?php
// Menambahkan field Total Points di halaman edit profil user
add_action('show_user_profile', 'upl_edit_user_points_field');
add_action('edit_user_profile', 'upl_edit_user_points_field');

function upl_edit_user_points_field($user)
{
    global $wpdb;

    // Ambil total point user
    $user_id = $user->ID;
    $user_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}users WHERE ID = %d", $user_id));

    if (!$user_data) return;

?>
<h2>User Point Levels</h2>
<table class="form-table">
    <tr>
        <th><label for="total_points">Total Points</label></th>
        <td>
            <input type="number" name="total_points" id="total_points"
                value="<?php echo esc_attr($user_data->total_points); ?>" class="regular-text" />
            <p class="description">Update total points for this user.</p>
        </td>
    </tr>
</table>
<?php
}

// Menyimpan perubahan Total Points
add_action('personal_options_update', 'upl_save_user_points');
add_action('edit_user_profile_update', 'upl_save_user_points');

function upl_save_user_points($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    if (isset($_POST['total_points'])) {
        global $wpdb;
        $total_points = intval($_POST['total_points']);

        $wpdb->update(
            $wpdb->prefix . 'users',
            ['total_points' => $total_points],
            ['ID' => $user_id],
            ['%d'],
            ['%d']
        );
    }
}


// Menambahkan kolom Total Points ke daftar Users
add_filter('manage_users_columns', 'upl_add_points_column');
function upl_add_points_column($columns)
{
    $columns['total_points'] = 'Total Points';
    return $columns;
}
// Mengisi data Total Points di daftar Users
add_action('manage_users_custom_column', 'upl_show_points_column_content', 10, 3);
function upl_show_points_column_content($value, $column_name, $user_id)
{
    if ('total_points' == $column_name) {
        global $wpdb;
        $user_data = $wpdb->get_row($wpdb->prepare("SELECT total_points FROM {$wpdb->prefix}users WHERE ID = %d", $user_id));
        if ($user_data) {
            return intval($user_data->total_points);
        } else {
            return '0';
        }
    }
    return $value;
}

// Membuat kolom Total Points bisa di-sort
add_filter('manage_users_sortable_columns', 'upl_make_points_column_sortable');
function upl_make_points_column_sortable($sortable_columns)
{
    $sortable_columns['total_points'] = 'total_points';
    return $sortable_columns;
}