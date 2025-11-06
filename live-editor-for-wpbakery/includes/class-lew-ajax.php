<?php
/**
 * AJAX handler for Live Editor for WPBakery
 *
 * @package LiveEditorWPBakery
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

/**
 * LEW_Ajax Class
 */
final class LEW_Ajax {

	/**
	 * Init hooks for AJAX actions
	 */
	public static function init() {
		// Checking for edit permission
		if (
			! is_user_logged_in()
			|| (
				! current_user_can( 'edit_posts' )
				&& ! current_user_can( 'edit_pages' )
			)
		) {
			return;
		}

		// Check for an action in the list of all actions
		$action = isset( $_POST['action'] ) ? $_POST['action'] : '';
		if ( ! $action || ! in_array( $action, self::get_actions(), true ) ) {
			return;
		}

		// The check _nonce
		if ( ! check_ajax_referer( __CLASS__, '_nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'An error has occurred. Please reload the page and try again.', 'live-editor-wpbakery' ),
				)
			);
		}

		// Adds actions to process requests
		foreach ( self::get_actions() as $action_name ) {
			if ( ! empty( $action_name ) && is_string( $action_name ) ) {
				// The add corresponding method from the class to the AJAX action
				$method_name = str_replace( 'lew_', '', $action_name );
				add_action( 'wp_ajax_' . $action_name, __CLASS__ . '::setup_postdata' );
				add_action( 'wp_ajax_' . $action_name, __CLASS__ . "::{$method_name}" );
			}
		}

		// For AJAX requests, we activate the definition of the builder page
		add_filter( 'lew_is_builder_page', '__return_true' );
	}

	/**
	 * Get the AJAX actions
	 *
	 * @return array The actions
	 */
	public static function get_actions() {
		return array(
			'lew_render_shortcode',
			'lew_save_post',
		);
	}

	/**
	 * Creates a nonce
	 *
	 * @return string
	 */
	public static function create_nonce() {
		return wp_create_nonce( __CLASS__ );
	}

	/**
	 * Setup postdata for correct render of post related data
	 */
	public static function setup_postdata() {
		if ( ! $post_id = (int) ( isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : 0 ) ) {
			return;
		}

		global $post, $wp_query;
		$query_args = array(
			'p'         => $post_id,
			'post_type' => lew_get_allowed_edit_post_types(),
		);

		$wp_query->query( $query_args );
		$wp_query->the_post();
		$post = get_post( $post_id );
	}

	/**
	 * Render shortcode AJAX handler
	 */
	public static function render_shortcode() {
		$post_id = isset( $_POST['post'] ) ? (int) $_POST['post'] : 0;
		$shortcode = isset( $_POST['shortcode'] ) ? wp_unslash( $_POST['shortcode'] ) : '';

		if ( ! $post_id || ! $shortcode ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid request data.', 'live-editor-wpbakery' ),
				)
			);
		}

		// Check permission
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to edit this post.', 'live-editor-wpbakery' ),
				)
			);
		}

		// Setup the post
		global $post;
		$post = get_post( $post_id );
		setup_postdata( $post );

		// Render the shortcode
		$output = do_shortcode( $shortcode );

		wp_reset_postdata();

		wp_send_json_success(
			array(
				'html' => $output,
			)
		);
	}

	/**
	 * Save post AJAX handler
	 */
	public static function save_post() {
		$post_id = isset( $_POST['post'] ) ? (int) $_POST['post'] : 0;
		$content = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : '';
		$custom_css = isset( $_POST['custom_css'] ) ? wp_unslash( $_POST['custom_css'] ) : '';

		if ( ! $post_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid post ID.', 'live-editor-wpbakery' ),
				)
			);
		}

		// Check permission
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to edit this post.', 'live-editor-wpbakery' ),
				)
			);
		}

		// Update post content
		if ( $content !== '' ) {
			$update_post = array(
				'ID'           => $post_id,
				'post_content' => $content,
			);
			wp_update_post( $update_post );
		}

		// Update custom CSS
		if ( $custom_css !== '' ) {
			update_post_meta( $post_id, 'lew_post_custom_css', $custom_css );
			// Also update WPBakery's custom CSS meta for compatibility
			update_post_meta( $post_id, '_wpb_post_custom_css', $custom_css );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Post saved successfully.', 'live-editor-wpbakery' ),
			)
		);
	}
}
