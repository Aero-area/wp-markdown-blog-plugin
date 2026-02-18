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

function markdown_blog_only_parse($content) {

    // aldrig i admin
    if (is_admin()) {
        return $content;
    }

    // kun blog posts
    if (get_post_type() !== 'post') {
        return $content;
    }

    // stop hvis Elementor editor eller preview
    if (
        defined('ELEMENTOR_VERSION') &&
        (
            isset($_GET['elementor-preview']) ||
            isset($_GET['action']) && $_GET['action'] === 'elementor' ||
            isset($_GET['elementor_library'])
        )
    ) {
        return $content;
    }

    // stop ved REST eller AJAX
    if (
        defined('REST_REQUEST') ||
        defined('DOING_AJAX')
    ) {
        return $content;
    }

    // load parser
    if (!class_exists('Parsedown')) {
        return $content;
    }

    $parser = new Parsedown();
    $parsed = $parser->text($content);

    if (!$parsed) {
        return $content;
    }

    return $parsed;
}

add_filter('the_content', 'markdown_blog_only_parse', 9);
