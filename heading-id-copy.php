<?php

/**
 * Plugin Name: Heading ID Copy
 * Description: Add ID to heading tags and allow copying URL with the heading ID.
 * Version: 1.4
 * Author: 김철준
 * Author URI: https://devcj.kr
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Direct access not allowed
}

// Add ID to heading tags (h2 to h6)
function headingcopy_headings($content) {
    $content = preg_replace_callback(
        '/<h([2-6])([^>]*)>(.*?)<\/h\1>/iu',
        function ($matches) {
            $tag = $matches[1];
            $attributes = $matches[2]; // Preserve existing attributes (class, id, etc.)
            $text = $matches[3];

            // Generate a unique ID based on the heading text (excluding <a> tags)
            $id_base = preg_replace('/\s+/u', '-', trim(strip_tags($text)));
            $id_base = preg_replace('/[^\p{L}\p{N}-]+/u', '', $id_base);

            static $id_counter = [];
            if (isset($id_counter[$id_base])) {
                $id_counter[$id_base]++;
                $id = $id_base . '-' . $id_counter[$id_base];
            } else {
                $id_counter[$id_base] = 1;
                $id = $id_base;
            }

            // Check if the <h> tag contains an <a> tag
            $has_anchor = stripos($text, '<a ') !== false;

            // Add onclick only if there is no <a> tag inside the <h> tag
            $onclick = $has_anchor ? '' : " onclick=\"copyToClipboard('{$id}')\"";

            return "<h{$tag}{$attributes} id=\"{$id}\"{$onclick}>{$text}
                <img src=\"" . plugin_dir_url(__FILE__) . "images/copy.png\" class=\"copy-icon\" alt=\"Copy\">
            </h{$tag}>";
        },
        $content
    );

    return $content;
}

add_filter('the_content', 'headingcopy_headings');

// Load custom styles for cursor: pointer on wp-block-heading
function headingcopy_styles() {
    wp_enqueue_style('heading-id-copy-styles', plugin_dir_url(__FILE__) . 'css/style.css');
}
add_action('wp_enqueue_scripts', 'headingcopy_styles');

// Enqueue clipboard.js script
function headingcopy_script() {
    wp_enqueue_script('heading-id-copy-script', plugin_dir_url(__FILE__) . 'js/headingIdCopyFunction.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'headingcopy_script');