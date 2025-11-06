<?php
/**
 * Shortcode management for Live Editor for WPBakery
 *
 * @package LiveEditorWPBakery
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

/**
 * LEW_Shortcode Class
 */
final class LEW_Shortcode {

	/**
	 * Singleton instance
	 *
	 * @var LEW_Shortcode
	 */
	private static $instance = null;

	/**
	 * Current post content
	 *
	 * @var string
	 */
	private $post_content = '';

	/**
	 * Get singleton instance
	 *
	 * @return LEW_Shortcode
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
		// Constructor is private for singleton
	}

	/**
	 * Set post content
	 */
	public function set_post_content() {
		$post_id = lew_get_post_id();
		if ( ! $post_id ) {
			return;
		}

		global $post;
		$post = get_post( $post_id );
		setup_postdata( $post );

		// Get post content from request if available (for preview without saving)
		if ( isset( $_REQUEST['content'] ) ) {
			$this->post_content = wp_unslash( $_REQUEST['content'] );
		} else {
			$this->post_content = $post->post_content;
		}

		// Filter the content to use our version
		add_filter( 'the_content', array( $this, 'filter_the_content' ), 1 );
	}

	/**
	 * Filter the content to use our stored version
	 *
	 * @param string $content Original content
	 * @return string Modified content
	 */
	public function filter_the_content( $content ) {
		if ( ! empty( $this->post_content ) ) {
			return $this->post_content;
		}
		return $content;
	}

	/**
	 * Add data-usbid attribute to shortcode HTML output
	 *
	 * @param string $output   Shortcode output
	 * @param string $tag      Shortcode tag
	 * @param array  $attr     Shortcode attributes
	 * @return string Modified output with data-usbid
	 */
	public function add_usbid_to_html( $output, $tag, $attr ) {
		// Only add usbid in preview mode
		if ( ! lew_is_post_preview() ) {
			return $output;
		}

		// Get usbid from attributes if exists
		$usbid = isset( $attr['usbid'] ) ? $attr['usbid'] : '';

		if ( empty( $usbid ) ) {
			return $output;
		}

		// Try to add data-usbid to the first HTML tag
		$output = preg_replace(
			'/^(<[a-z][^>]*?)(\s*\/?>)/i',
			'$1 data-usbid="' . esc_attr( $usbid ) . '"$2',
			$output,
			1
		);

		return $output;
	}

	/**
	 * Export page data for the builder
	 */
	public function export_page_data() {
		$post_id = lew_get_post_id();
		if ( ! $post_id ) {
			return;
		}

		$post = get_post( $post_id );

		// Export page data
		$page_data = array(
			'content'    => $post->post_content,
			'custom_css' => get_post_meta( $post_id, 'lew_post_custom_css', true ),
			'post_id'    => $post_id,
			'post_title' => $post->post_title,
		);

		// Output as JSON in script tag
		echo '<script type="text/javascript">';
		echo 'window.lewPageData = ' . wp_json_encode( $page_data ) . ';';
		echo 'if(window.parent && window.parent.$lew){window.parent.$lew.trigger("iframe.pageDataLoaded");}';
		echo '</script>';
	}
}
