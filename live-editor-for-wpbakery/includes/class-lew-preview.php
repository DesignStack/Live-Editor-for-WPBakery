<?php
/**
 * Preview functionality for Live Editor for WPBakery
 *
 * @package LiveEditorWPBakery
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

/**
 * LEW_Preview Class
 */
final class LEW_Preview {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Hide admin bar
		add_filter( 'show_admin_bar', '__return_false' );

		// Add styles and scripts on the preview
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_preview_assets' ) );

		// Export data for the builder
		add_action( 'wp_footer', array( $this, 'export_builder_data' ), 9999 );

		// Setting up a preview to page edit
		if ( lew_is_post_preview() ) {
			// Disable output of WPBakery custom styles
			add_filter( 'vc_post_custom_css', '__return_false', 1 );

			// This is an instance of the class for working with shortcodes
			$shortcode = LEW_Shortcode::instance();

			// Set post content in Live Builder
			add_action( 'template_redirect', array( $shortcode, 'set_post_content' ), 501 );

			// Add data-usbid attribute to html when output shortcode result
			add_filter( 'do_shortcode_tag', array( $shortcode, 'add_usbid_to_html' ), 9999, 3 );

			// Export page content, page metadata, custom css for Builder
			add_action( 'wp_footer', array( $shortcode, 'export_page_data' ), 9999 );
		}
	}

	/**
	 * Add styles and scripts on the preview
	 */
	public function enqueue_preview_assets() {
		wp_enqueue_script( 'lew-preview-js', LEW_BUILDER_URL . 'assets/js/builder-preview.min.js', array( 'jquery' ), LEW_VERSION, true );
		wp_enqueue_style( 'lew-preview-css', LEW_BUILDER_URL . 'assets/css/builder-preview.min.css', array(), LEW_VERSION );
	}

	/**
	 * Export data for the builder
	 */
	public function export_builder_data() {
		if ( ! lew_is_post_preview() ) {
			return;
		}

		// Template for highlight an element on hover
		$output = '<div class="lew-hover">';
		$output .= '<div class="lew-hover-panel">';
		$output .= '<div class="lew-hover-panel-name">Element</div>';
		$output .= '<div class="lew-hover-panel-btn ui-icon_copy" title="' . esc_attr__( 'Copy', 'live-editor-wpbakery' ) . '"></div>';
		$output .= '<div class="lew-hover-panel-btn ui-icon_paste" title="' . esc_attr__( 'Paste', 'live-editor-wpbakery' ) . '"></div>';
		$output .= '<div class="lew-hover-panel-btn ui-icon_duplicate" title="' . esc_attr__( 'Duplicate', 'live-editor-wpbakery' ) . '"></div>';
		$output .= '<div class="lew-hover-panel-btn ui-icon_delete" title="' . esc_attr__( 'Delete', 'live-editor-wpbakery' ) . '"></div>';
		$output .= '</div>'; // .lew-hover-panel
		$output .= '<div class="lew-hover-h"></div>';
		$output .= '</div>'; // .lew-hover
		echo $output;
	}
}
