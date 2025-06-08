<?php

    add_action('wp_footer', function () {
        if (is_user_logged_in()) {
            $logout_url  = wp_logout_url(home_url());
            $profile_url = home_url('/lp-profile/');

        ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const loginBox = document.querySelector('.thim-link-login.thim-login-popup');
    if (loginBox) {
        loginBox.innerHTML = `
                    <a href="<?php echo esc_url($profile_url); ?>">Profile 123</a> |
                    <a href="<?php echo esc_url($logout_url); ?>">Logout</a>
                `;
        loginBox.classList.remove('thim-login-popup'); // Optional: remove popup behavior
    }
});
</script>
<?php
}
});