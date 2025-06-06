<?php
add_action('bbp_template_after_user_details', function () {
    $user_id = bbp_get_displayed_user_id(); // ID user yang profilenya sedang dibuka

    if (!$user_id) {
        return;
    }

    global $wpdb;

    // Ambil rank user
    $user = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}users WHERE ID = %d", $user_id)
    );

    // Ambil data level badge
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



    // Mengirimkan current_image ke JavaScript menggunakan wp_localize_script
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const vcardLink = document.querySelector('.url.fn.n');

    // Mendapatkan URL gambar dari PHP
    const currentImageSrc = "<?php echo esc_url($current_image); ?>";

    if (vcardLink && currentImageSrc) {
        // Buat elemen gambar
        const rankImage = document.createElement('img');
        rankImage.src = currentImageSrc; // Ambil URL gambar dari PHP
        rankImage.alt = 'User Rank'; // Deskripsi gambar
        rankImage.className = 'rank-title-img'; // Tambahkan kelas jika diperlukan
        rankImage.style.maxWidth = '66px'; // Ukuran gambar
        rankImage.style.position = 'absolute';
        rankImage.style.top = '-30px'; // Atur posisi gambar sesuai kebutuhan
        rankImage.style.left = '160px';

        // Tambahkan gambar ke dalam <a class="url fn n">
        vcardLink.appendChild(rankImage);
    }
});
</script>
<?php
}, 10);