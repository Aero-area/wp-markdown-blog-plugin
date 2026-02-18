<?php

if (!defined('ABSPATH')) {
    exit;
}

add_filter('user_can_richedit', function ($default) {
    global $post;

    if ($post && isset($post->post_type) && $post->post_type === 'post') {
        return false;
    }

    return $default;
});
