<?php
/**
 * Plugin Name: Heading ID Copy
 * Description: Add ID to heading tags and allow copying URL with the heading ID.
 * Version: 1.7
 * Author: 김철준
 * Author URI: https://devcj.kr
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access
}

/*---------------------------------------------
  Frontend Functionality: Copy Icon, Link Copy, CSS/JS Loading
---------------------------------------------*/

/**
 * Retrieve the 'visibility' option.
 * Returns 'all' as the default value if not set.
 */
function headingcopy_get_visibility() {
    $options = get_option( 'heading_id_copy_options' );
    return isset( $options['visibility'] ) ? $options['visibility'] : 'all';
}

/**
 * Always load the CSS file.
 */
function headingcopy_styles() {
    wp_enqueue_style('heading-id-copy-styles', plugin_dir_url(__FILE__) . 'css/style.css');
}
add_action( 'wp_enqueue_scripts', 'headingcopy_styles' );

/**
 * Always load the JS file.
 */
function headingcopy_script() {
    wp_enqueue_script( 'heading-id-copy-script', plugin_dir_url(__FILE__) . 'js/headingIdCopyFunction.js', array(), null, true );
}
add_action( 'wp_enqueue_scripts', 'headingcopy_script' );

/**
 * Process all heading tags (h1-h6):
 * - Always add a unique id attribute.
 * - Append the "heading-id-copy" class based on the visibility option:
 *      * If visibility is "all", attach the class for every user.
 *      * If visibility is "admin", attach the class only if the current user is an administrator.
 * - Conditionally add the copy functionality (onclick event and copy icon) based on the visibility option.
 */
function headingcopy_headings( $content ) {
    $content = preg_replace_callback(
        '/<h([1-6])([^>]*)>(.*?)<\/h\1>/iu',
        function ( $matches ) {
            $tag        = $matches[1];
            $attributes = $matches[2];
            $text       = $matches[3];

            // Determine whether to append "heading-id-copy" class
            $visibility = headingcopy_get_visibility();
            if ( $visibility === 'all' || ( $visibility === 'admin' && current_user_can( 'manage_options' ) ) ) {
                if ( preg_match( '/class\s*=\s*"([^"]*)"/i', $attributes, $match_classes ) ) {
                    $existing_classes = $match_classes[1];
                    $new_classes      = trim( $existing_classes . ' heading-id-copy' );
                    $attributes       = preg_replace( '/class\s*=\s*"([^"]*)"/i', 'class="' . $new_classes . '"', $attributes );
                } else {
                    $attributes .= ' class="heading-id-copy"';
                }
            }

            // Generate a unique id based on the heading text
            $id_base = preg_replace( '/\s+/u', '-', trim( strip_tags( $text ) ) );
            $id_base = preg_replace( '/[^\p{L}\p{N}-]+/u', '', $id_base );

            static $id_counter = array();
            if ( isset( $id_counter[ $id_base ] ) ) {
                $id_counter[ $id_base ]++;
                $id = $id_base . '-' . $id_counter[ $id_base ];
            } else {
                $id_counter[ $id_base ] = 1;
                $id                     = $id_base;
            }

            // Determine whether to enable the copy feature based on the option
            $copy_feature = false;
            if ( $visibility === 'all' || ( $visibility === 'admin' && current_user_can( 'manage_options' ) ) ) {
                $copy_feature = true;
            }

            // Check if the heading text already contains an <a> tag
            $has_anchor = stripos( $text, '<a ' ) !== false;
            if ( $copy_feature && ! $has_anchor ) {
                $onclick   = " onclick=\"copyToClipboard('{$id}')\"";
                $copy_icon = '<img src="' . plugin_dir_url( __FILE__ ) . 'images/copy.png" class="copy-icon" alt="Copy">';
            } else {
                $onclick   = "";
                $copy_icon = "";
            }

            // Always add the id attribute
            return "<h{$tag}{$attributes} id=\"{$id}\"{$onclick}>{$text}{$copy_icon}</h{$tag}>";
        },
        $content
    );

    return $content;
}
add_filter( 'the_content', 'headingcopy_headings' );

/*---------------------------------------------
  Load Admin Settings if in Admin Area
---------------------------------------------*/
if ( is_admin() ) {
    require_once plugin_dir_path( __FILE__ ) . 'admin-settings.php';
}