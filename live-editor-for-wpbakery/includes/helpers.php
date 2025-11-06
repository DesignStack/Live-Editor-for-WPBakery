<?php
/**
 * Helper Functions for Live Editor for WPBakery
 *
 * @package LiveEditorWPBakery
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

/**
 * Get the ID of the post being edited
 *
 * @return int Returns post_id if successful, otherwise zero
 */
function lew_get_post_id() {
	if ( lew_is_builder_page() || lew_is_preview() ) {
		return (int) ( isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : get_queried_object_id() );
	}
	return 0;
}

/**
 * Determines if this is a post edit page in Live Editor
 *
 * @return bool Returns TRUE if this is a post edit page, otherwise FALSE
 */
function lew_is_post_editing() {
	// Action definitions based on referral link for AJAX requests
	if ( wp_doing_ajax() ) {
		$referer = wp_get_referer();
		if ( $referer ) {
			$url_params = wp_parse_url( $referer, PHP_URL_QUERY );
			return $url_params && strpos( $url_params, '&action=lew-builder' ) !== false;
		}
		return false;
	}

	global $pagenow;
	return (
		strtolower( basename( $pagenow, '.php' ) ) === 'post'
		&& isset( $_REQUEST['post'] )
		&& isset( $_REQUEST['action'] )
		&& strtolower( $_REQUEST['action'] ) === 'lew-builder'
	);
}

/**
 * Determines if this is a builder page
 *
 * @return bool Returns TRUE if this is a builder page, otherwise FALSE
 */
function lew_is_builder_page() {
	$is_builder_page = lew_is_post_editing();
	return (bool) apply_filters( 'lew_is_builder_page', $is_builder_page );
}

/**
 * Determines if builder preview page is shown
 *
 * @return bool Returns TRUE if the current page is a preview in the builder, otherwise FALSE
 */
function lew_is_post_preview() {
	// Preview page definitions via query params
	if ( isset( $_REQUEST['lew-builder'] ) ) {
		$nonce = $_REQUEST['lew-builder'];
		return (bool) wp_verify_nonce( $nonce, 'lew-builder' );
	}

	// Preview page definitions via action in AJAX requests
	if ( wp_doing_ajax() ) {
		return isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'lew_render_shortcode';
	}

	return false;
}

/**
 * Determines if builder preview is shown
 *
 * @return bool TRUE if builder preview, FALSE otherwise
 */
function lew_is_preview() {
	return lew_is_post_preview();
}

/**
 * Determine if a post editing is locked
 *
 * @return bool Returns true if the post editing is locked, false otherwise
 */
function lew_post_editing_is_locked() {
	if ( ! $post_id = lew_get_post_id() ) {
		return false;
	}
	return (bool) wp_check_post_lock( $post_id );
}

/**
 * Get edit link in Live Builder
 *
 * @param int   $post_id The post ID
 * @param array $params  The additional parameters for the URL
 * @return string Returns a link to edits in builder
 */
function lew_get_edit_link( $post_id, $params = array() ) {
	if ( $post_id === 0 ) {
		return '';
	}

	$default_params = array(
		'post'   => (int) $post_id,
		'action' => 'lew-builder',
	);

	$url = admin_url( 'post.php?' . build_query( array_merge( $default_params, (array) $params ) ) );

	return apply_filters( 'lew_get_edit_link', $url, $params, $default_params );
}

/**
 * Get allowed post types for editing
 *
 * @return array List of post types names
 */
function lew_get_allowed_edit_post_types() {
	$post_types = array( 'post', 'page' );

	// Add custom post types that support editor
	$custom_post_types = get_post_types(
		array(
			'public'   => true,
			'_builtin' => false,
		)
	);

	foreach ( $custom_post_types as $post_type ) {
		if ( post_type_supports( $post_type, 'editor' ) ) {
			$post_types[] = $post_type;
		}
	}

	return apply_filters( 'lew_allowed_edit_post_types', $post_types );
}

/**
 * Pass data to JavaScript
 *
 * @param array $data Data to pass
 * @return string Data attribute with JSON encoded data
 */
function lew_pass_data_to_js( $data ) {
	if ( empty( $data ) || ! is_array( $data ) ) {
		return '';
	}
	return ' data-lew-config=\'' . esc_attr( wp_json_encode( $data ) ) . '\'';
}

/**
 * Load a template file
 *
 * @param string $template_name Template name (without .php extension)
 * @param array  $vars          Variables to pass to template
 */
function lew_load_template( $template_name, $vars = array() ) {
	$template_path = LEW_PLUGIN_DIR . 'templates/' . $template_name . '.php';

	if ( file_exists( $template_path ) ) {
		if ( ! empty( $vars ) && is_array( $vars ) ) {
			extract( $vars );
		}
		include $template_path;
	}
}

/**
 * Get responsive breakpoints
 *
 * @return array Responsive breakpoints
 */
function lew_get_responsive_breakpoints() {
	$breakpoints = array(
		'default' => array(
			'label'     => __( 'Desktop', 'live-editor-wpbakery' ),
			'max_width' => 2560,
		),
		'laptop'  => array(
			'label'     => __( 'Laptop', 'live-editor-wpbakery' ),
			'max_width' => 1280,
			'media'     => '@media (max-width: 1280px)',
		),
		'tablet'  => array(
			'label'     => __( 'Tablet', 'live-editor-wpbakery' ),
			'max_width' => 1024,
			'media'     => '@media (max-width: 1024px)',
		),
		'mobile'  => array(
			'label'     => __( 'Mobile', 'live-editor-wpbakery' ),
			'max_width' => 768,
			'media'     => '@media (max-width: 768px)',
		),
	);

	return apply_filters( 'lew_responsive_breakpoints', $breakpoints );
}

/**
 * Sanitize shortcode content
 *
 * @param string $content Shortcode content
 * @return string Sanitized content
 */
function lew_sanitize_shortcode_content( $content ) {
	// Basic sanitization - can be enhanced based on needs
	return wp_kses_post( $content );
}

/**
 * Get usbid container attribute
 *
 * @return string Data attribute for container
 */
function lew_get_usbid_container() {
	if ( lew_is_post_preview() ) {
		return ' data-usbid="container" ';
	}
	return '';
}
