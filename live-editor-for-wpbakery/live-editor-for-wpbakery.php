<?php
/**
 * Plugin Name: Live Editor for WPBakery
 * Plugin URI: https://designstack.co.uk
 * Description: Enhances WPBakery Page Builder with a live frontend editor interface for real-time visual editing.
 * Version: 1.0
 * Author: DesignStack
 * Author URI: https://designstack.co.uk
 * Text Domain: live-editor-wpbakery
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package LiveEditorWPBakery
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

// Define plugin constants
if ( ! defined( 'LEW_VERSION' ) ) {
	define( 'LEW_VERSION', '1.0' );
}

if ( ! defined( 'LEW_PLUGIN_DIR' ) ) {
	define( 'LEW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'LEW_PLUGIN_URL' ) ) {
	define( 'LEW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'LEW_BUILDER_DIR' ) ) {
	define( 'LEW_BUILDER_DIR', LEW_PLUGIN_DIR );
}

if ( ! defined( 'LEW_BUILDER_URL' ) ) {
	define( 'LEW_BUILDER_URL', LEW_PLUGIN_URL );
}

/**
 * Check if WPBakery Page Builder is active
 */
function lew_check_wpbakery() {
	if ( ! defined( 'WPB_VC_VERSION' ) && ! class_exists( 'Vc_Manager' ) ) {
		add_action( 'admin_notices', 'lew_wpbakery_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Display admin notice if WPBakery is not active
 */
function lew_wpbakery_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<strong><?php esc_html_e( 'Live Editor for WPBakery', 'live-editor-wpbakery' ); ?></strong>
			<?php esc_html_e( 'requires WPBakery Page Builder to be installed and activated.', 'live-editor-wpbakery' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Autoloader for LEW classes
 *
 * @param string $class_name The class name
 */
function lew_autoload( $class_name ) {
	// Map of available classes
	$class_map = array(
		'LEW_Builder'   => 'class-lew-builder',
		'LEW_Ajax'      => 'class-lew-ajax',
		'LEW_Assets'    => 'class-lew-assets',
		'LEW_Panel'     => 'class-lew-panel',
		'LEW_Preview'   => 'class-lew-preview',
		'LEW_Shortcode' => 'class-lew-shortcode',
	);

	if ( isset( $class_map[ $class_name ] ) ) {
		$file = LEW_PLUGIN_DIR . 'includes/' . $class_map[ $class_name ] . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
spl_autoload_register( 'lew_autoload' );

/**
 * Initialize the plugin
 */
function lew_init() {
	// Check if WPBakery is active
	if ( ! lew_check_wpbakery() ) {
		return;
	}

	// Load helper functions
	require_once LEW_PLUGIN_DIR . 'includes/helpers.php';

	// Initialize the builder
	// Note: Initialize after core and plugins have been initialized
	new LEW_Builder();
}
add_action( 'plugins_loaded', 'lew_init', 20 );

/**
 * Plugin activation hook
 */
function lew_activate() {
	// Check WPBakery on activation
	if ( ! defined( 'WPB_VC_VERSION' ) && ! class_exists( 'Vc_Manager' ) ) {
		wp_die(
			esc_html__( 'Live Editor for WPBakery requires WPBakery Page Builder to be installed and activated.', 'live-editor-wpbakery' ),
			esc_html__( 'Plugin Activation Error', 'live-editor-wpbakery' ),
			array( 'back_link' => true )
		);
	}
}
register_activation_hook( __FILE__, 'lew_activate' );
