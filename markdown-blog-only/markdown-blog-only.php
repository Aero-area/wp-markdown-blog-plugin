<?php
/**
 * Plugin Name: Markdown Blog Only
 * Version: 1.0
 * Author: Anders
 */

if (!defined('ABSPATH')) {
    exit;
}

$parser_file = plugin_dir_path(__FILE__) . 'parser/Parsedown.php';
$toggle_file = plugin_dir_path(__FILE__) . 'admin/editor-toggle.php';

if (file_exists($parser_file)) {
    require_once $parser_file;
}

if (file_exists($toggle_file)) {
    require_once $toggle_file;
}

/**
 * Detect Elementor/editor-related contexts where parsing must be skipped.
 *
 * @return bool
 */
function mbo_is_elementor_request() {
    if (defined('ELEMENTOR_VERSION')) {
        return true;
    }

    if (defined('REST_REQUEST') && REST_REQUEST) {
        return true;
    }

    if (defined('DOING_AJAX') && DOING_AJAX) {
        return true;
    }

    if (isset($_GET['action']) && $_GET['action'] === 'elementor') {
        return true;
    }

    if (isset($_GET['elementor-preview'])) {
        return true;
    }

    return false;
}

/**
 * Parse Markdown only for regular blog posts on frontend.
 *
 * @param string $content
 * @return string
 */
function mbo_parse_post_markdown($content) {
    if (is_admin()) {
        return $content;
    }

    if (get_post_type() !== 'post') {
        return $content;
    }

    if (mbo_is_elementor_request()) {
        return $content;
    }

    if (!class_exists('Parsedown')) {
        return $content;
    }

    static $parser = null;
    if ($parser === null) {
        $parser = new Parsedown();
    }

    $parsed = $parser->text((string) $content);
    return is_string($parsed) ? $parsed : $content;
}

add_filter('the_content', 'mbo_parse_post_markdown', 9);
