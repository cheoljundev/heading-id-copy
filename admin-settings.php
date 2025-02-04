<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check if the current admin locale is Korean.
 */
function headingcopy_is_korean() {
    $locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
    return ( strpos( $locale, 'ko_' ) === 0 );
}

/**
 * Add admin menu for settings.
 */
function headingcopy_admin_menu() {
    add_options_page(
        headingcopy_is_korean() ? 'Heading ID Copy 설정' : 'Heading ID Copy Settings',
        'Heading ID Copy',
        'manage_options',
        'heading-id-copy-settings',
        'headingcopy_settings_page'
    );
}
add_action( 'admin_menu', 'headingcopy_admin_menu' );

/**
 * Register settings.
 */
function headingcopy_register_settings() {
    register_setting( 'headingcopy_options_group', 'heading_id_copy_options', 'headingcopy_options_sanitize' );

    add_settings_section(
        'headingcopy_main_section',
        headingcopy_is_korean() ? '기능 표시 설정' : 'Display Options',
        'headingcopy_main_section_cb',
        'heading-id-copy-settings'
    );

    add_settings_field(
        'headingcopy_visibility',
        headingcopy_is_korean() ? '표시 대상' : 'Visibility',
        'headingcopy_visibility_field_cb',
        'heading-id-copy-settings',
        'headingcopy_main_section'
    );
}
add_action( 'admin_init', 'headingcopy_register_settings' );

/**
 * Section callback.
 */
function headingcopy_main_section_cb() {
    if ( headingcopy_is_korean() ) {
        echo '<p>복사 아이콘, 링크 복사 기능 및 추가 CSS를 누구에게 표시할지 선택합니다. "모든 사용자"로 하면 일반 방문자에게도 copy 기능이 보이고, "관리자만"으로 하면 관리자에게만 copy 기능이 활성화됩니다. 단, id 속성은 항상 추가됩니다.</p>';
    } else {
        echo '<p>Select who can see the copy icon, link copy function, and additional CSS. If set to "all", the copy function and the "heading-id-copy" class will be applied for all visitors; if set to "admin", only administrators will have the copy function and the class applied. However, the id attribute is always added.</p>';
    }
}

/**
 * Visibility field callback.
 */
function headingcopy_visibility_field_cb() {
    $options    = get_option( 'heading_id_copy_options' );
    $visibility = isset( $options['visibility'] ) ? $options['visibility'] : 'all';
    if ( headingcopy_is_korean() ) {
        ?>
        <label>
            <input type="radio" name="heading_id_copy_options[visibility]" value="all" <?php checked( $visibility, 'all' ); ?>>
            모든 사용자에게 표시
        </label><br>
        <label>
            <input type="radio" name="heading_id_copy_options[visibility]" value="admin" <?php checked( $visibility, 'admin' ); ?>>
            관리자에게만 표시 (비관리자에게는 copy 기능 미노출)
        </label>
        <?php
    } else {
        ?>
        <label>
            <input type="radio" name="heading_id_copy_options[visibility]" value="all" <?php checked( $visibility, 'all' ); ?>>
            Display to all users
        </label><br>
        <label>
            <input type="radio" name="heading_id_copy_options[visibility]" value="admin" <?php checked( $visibility, 'admin' ); ?>>
            Display to administrators only (copy function hidden for non-admins)
        </label>
        <?php
    }
}

/**
 * Settings page callback.
 */
function headingcopy_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( headingcopy_is_korean() ? '권한이 없습니다.' : 'You do not have permission to access these settings.' );
    }
    ?>
    <div class="wrap">
        <h1><?php echo headingcopy_is_korean() ? 'Heading ID Copy 설정' : 'Heading ID Copy Settings'; ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'headingcopy_options_group' ); ?>
            <?php do_settings_sections( 'heading-id-copy-settings' ); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Sanitize options.
 */
function headingcopy_options_sanitize( $input ) {
    $new_input = array();
    if ( isset( $input['visibility'] ) && in_array( $input['visibility'], array( 'all', 'admin' ), true ) ) {
        $new_input['visibility'] = $input['visibility'];
    } else {
        $new_input['visibility'] = 'all';
    }
    return $new_input;
}

/**
 * Flush the object cache and clear page caches on option update.
 */
function headingcopy_flush_cache( $old_value, $new_value ) {
    // Flush WP object cache
    wp_cache_flush();

    // WP Rocket cache clearing
    if ( function_exists( 'rocket_clean_domain' ) ) {
        rocket_clean_domain();
        error_log( 'WP Rocket cache cleared.' );
    }

    // W3 Total Cache cache clearing
    if ( function_exists( 'w3tc_flush_all' ) ) {
        w3tc_flush_all();
        error_log( 'W3 Total Cache flushed.' );
    }
}
add_action( 'update_option_heading_id_copy_options', 'headingcopy_flush_cache', 10, 2 );