<?php
/**
 * Panel functionality for Live Editor for WPBakery
 *
 * @package LiveEditorWPBakery
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

/**
 * LEW_Panel Class
 */
final class LEW_Panel {

	/**
	 * Singleton instance
	 *
	 * @var LEW_Panel
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return LEW_Panel
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		// Panel functionality can be extended here
	}

	/**
	 * Get panel titles
	 *
	 * @return array Panel titles for translations
	 */
	public static function get_titles() {
		return array(
			'add_element'     => __( 'Add Element', 'live-editor-wpbakery' ),
			'edit_element'    => __( 'Edit Element', 'live-editor-wpbakery' ),
			'elements'        => __( 'Elements', 'live-editor-wpbakery' ),
			'templates'       => __( 'Templates', 'live-editor-wpbakery' ),
			'settings'        => __( 'Settings', 'live-editor-wpbakery' ),
			'save'            => __( 'Save', 'live-editor-wpbakery' ),
			'cancel'          => __( 'Cancel', 'live-editor-wpbakery' ),
			'delete'          => __( 'Delete', 'live-editor-wpbakery' ),
			'duplicate'       => __( 'Duplicate', 'live-editor-wpbakery' ),
			'copy'            => __( 'Copy', 'live-editor-wpbakery' ),
			'paste'           => __( 'Paste', 'live-editor-wpbakery' ),
			'edit'            => __( 'Edit', 'live-editor-wpbakery' ),
			'close'           => __( 'Close', 'live-editor-wpbakery' ),
			'navigator'       => __( 'Navigator', 'live-editor-wpbakery' ),
			'page_settings'   => __( 'Page Settings', 'live-editor-wpbakery' ),
			'responsive_mode' => __( 'Responsive Mode', 'live-editor-wpbakery' ),
		);
	}
}
