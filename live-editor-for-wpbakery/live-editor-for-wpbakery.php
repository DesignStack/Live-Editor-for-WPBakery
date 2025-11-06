<?php
/**
 * Plugin Name: Live Editor for WPBakery
 * Plugin URI: https://designstack.co.uk
 * Description: Enhances WPBakery Page Builder with a live frontend editor interface for real-time visual editing. This plugin brings the powerful US Builder (Live Editor) from Impreza theme to any WordPress site using WPBakery.
 * Version: 1.03
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

/**
 * ============================================================================
 * DEFINE PLUGIN CONSTANTS
 * ============================================================================
 */

// Plugin version
if ( ! defined( 'LEW_VERSION' ) ) {
	define( 'LEW_VERSION', '1.03' );
}

// Plugin directory path
if ( ! defined( 'LEW_PLUGIN_DIR' ) ) {
	define( 'LEW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin directory URL
if ( ! defined( 'LEW_PLUGIN_URL' ) ) {
	define( 'LEW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * ============================================================================
 * DEFINE US CORE COMPATIBLE CONSTANTS
 * ============================================================================
 * These constants mimic us-core plugin structure for compatibility
 */

// US Core directory (point to our plugin directory)
if ( ! defined( 'US_CORE_DIR' ) ) {
	define( 'US_CORE_DIR', LEW_PLUGIN_DIR );
}

// US Core URI (point to our plugin URL)
if ( ! defined( 'US_CORE_URI' ) ) {
	define( 'US_CORE_URI', LEW_PLUGIN_URL );
}

// US Core version
if ( ! defined( 'US_CORE_VERSION' ) ) {
	define( 'US_CORE_VERSION', LEW_VERSION );
}

// Builder directory
if ( ! defined( 'US_BUILDER_DIR' ) ) {
	define( 'US_BUILDER_DIR', LEW_PLUGIN_DIR . 'builder' );
}

// Builder URL
if ( ! defined( 'US_BUILDER_URL' ) ) {
	define( 'US_BUILDER_URL', LEW_PLUGIN_URL . 'builder' );
}

// Theme name constant (required by builder UI)
if ( ! defined( 'US_THEMENAME' ) ) {
	define( 'US_THEMENAME', 'Impreza' );
}

// Typography tags
if ( ! defined( 'US_TYPOGRAPHY_TAGS' ) ) {
	define( 'US_TYPOGRAPHY_TAGS', array( 'body', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) );
}

// Typography style tag id in builder
if ( ! defined( 'US_BUILDER_TYPOGRAPHY_TAG_ID' ) ) {
	define( 'US_BUILDER_TYPOGRAPHY_TAG_ID', 'usb-customize-fonts' );
}

/**
 * ============================================================================
 * INITIALIZE GLOBAL VARIABLES FOR FILE SEARCH
 * ============================================================================
 */
global $us_template_directory, $us_stylesheet_directory, $us_template_directory_uri, $us_stylesheet_directory_uri;
global $us_files_search_paths, $us_file_paths;

// Set template directory variables (plugin acts as theme)
$us_template_directory = LEW_PLUGIN_DIR;
$us_stylesheet_directory = LEW_PLUGIN_DIR;
$us_template_directory_uri = LEW_PLUGIN_URL;
$us_stylesheet_directory_uri = LEW_PLUGIN_URL;

// Reinitialize file search paths
unset( $us_files_search_paths );
unset( $us_file_paths );

/**
 * ============================================================================
 * CHECK WPBAKERY DEPENDENCY
 * ============================================================================
 */

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
 * ============================================================================
 * LOAD HELPER FUNCTIONS
 * ============================================================================
 */

// Load theme-compatible helper functions (us_locate_file, us_translate, etc.)
require_once LEW_PLUGIN_DIR . 'includes/theme-helpers.php';

// Load US Core helper functions
require_once LEW_PLUGIN_DIR . 'includes/us-helpers.php';

// Load Builder-specific helper functions
require_once LEW_PLUGIN_DIR . 'includes/builder-helpers.php';

/**
 * ============================================================================
 * INITIALIZE USOF (OPTIONS FRAMEWORK)
 * ============================================================================
 */

/**
 * Initialize USOF Framework
 */
function lew_init_usof() {
	// Load USOF only if it exists
	if ( file_exists( LEW_PLUGIN_DIR . 'usof/usof.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'usof/usof.php';
	}
}

/**
 * ============================================================================
 * INITIALIZE BUILDER
 * ============================================================================
 */

/**
 * Initialize the Live Builder
 * This mimics the us-core initialization process
 */
function lew_init_builder() {
	// Load builder entry point
	if ( file_exists( LEW_PLUGIN_DIR . 'builder/builder.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'builder/builder.php';
	}
}

/**
 * ============================================================================
 * PLUGIN INITIALIZATION
 * ============================================================================
 */

/**
 * Main plugin initialization
 * Runs after theme setup to ensure compatibility
 */
function lew_init() {
	// Check if WPBakery is active
	if ( ! lew_check_wpbakery() ) {
		return;
	}

	// Initialize USOF framework
	lew_init_usof();

	// Load WP Background Process (required by builder)
	if ( file_exists( LEW_PLUGIN_DIR . 'vendor/wp-background-processing/wp-background-processing.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'vendor/wp-background-processing/wp-background-processing.php';
	}

	// Load fallback functions
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/fallback.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/fallback.php';
	}

	// Load shortcodes (required by builder)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/shortcodes.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/shortcodes.php';
	}

	// Load grid functions (required for grid elements)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/grid.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/grid.php';
	}

	// Load list functions (required for list elements)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/list.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/list.php';
	}

	// Load media functions (required for media handling)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/media.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/media.php';
	}

	// Load post functions (required for post elements)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/post.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/post.php';
	}

	// Load layout functions (required for layout)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/layout.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/layout.php';
	}

	// Load header functions (required for navigation menus)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/header.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/header.php';
	}

	// Load widget area functions (required for widget areas)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/widget_areas.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/widget_areas.php';
	}

	// Load widget functions (required for widgets)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/widgets.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/widgets.php';
	}

	// Load theme options functions (required for theme options)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/theme-options.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/theme-options.php';
	}

	// Load breadcrumb functions (required for breadcrumbs)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/breadcrumbs.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/breadcrumbs.php';
	}

	// Load cookie notice functions (required for cookie notices)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/cookie-notice.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/cookie-notice.php';
	}

	// Load enqueue functions (required for asset loading)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/enqueue.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/enqueue.php';
	}

	// Load meta tags functions (required for meta tags)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/meta-tags.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/meta-tags.php';
	}

	// Load post types functions (required for custom post types)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/post-types.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/post-types.php';
	}

	// Load migration functions (required for version migrations)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/migration.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/migration.php';
	}

	// Load init functions (required for initialization)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/init.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/init.php';
	}

	// Load helper functions (required for utilities)
	if ( file_exists( LEW_PLUGIN_DIR . 'functions/helpers.php' ) ) {
		require_once LEW_PLUGIN_DIR . 'functions/helpers.php';
	}

	// Load admin functions if in admin
	if ( is_admin() OR ( defined( 'WP_CLI' ) AND WP_CLI ) ) {
		// Admin Enqueue
		if ( file_exists( LEW_PLUGIN_DIR . 'admin/functions/enqueue.php' ) ) {
			require_once LEW_PLUGIN_DIR . 'admin/functions/enqueue.php';
		}
		// Filter Indexer
		if ( file_exists( LEW_PLUGIN_DIR . 'admin/functions/filter-indexer.php' ) ) {
			require_once LEW_PLUGIN_DIR . 'admin/functions/filter-indexer.php';
		}
		// Optimize assets
		if ( file_exists( LEW_PLUGIN_DIR . 'admin/functions/optimize-assets.php' ) ) {
			require_once LEW_PLUGIN_DIR . 'admin/functions/optimize-assets.php';
		}
		// Used icons
		if ( file_exists( LEW_PLUGIN_DIR . 'admin/functions/used-icons.php' ) ) {
			require_once LEW_PLUGIN_DIR . 'admin/functions/used-icons.php';
		}
	}

	// AJAX related functions
	if ( wp_doing_ajax() ) {
		$ajax_files = array(
			'ajax/header_builder.php',
			'ajax/grid_builder.php',
			'ajax/grid.php',
			'ajax/cform.php',
			'ajax/cart.php',
			'ajax/cookie_notice.php',
			'ajax/gallery.php',
			'ajax/post_list.php',
			'ajax/add_to_favs.php',
			'ajax/us_login.php',
		);
		foreach ( $ajax_files as $ajax_file ) {
			if ( file_exists( LEW_PLUGIN_DIR . 'functions/' . $ajax_file ) ) {
				require_once LEW_PLUGIN_DIR . 'functions/' . $ajax_file;
			}
		}
	}

	// Initialize the builder (priority 8 to run before other theme setups)
	lew_init_builder();
}

// Hook into after_setup_theme with priority 8 (same as us-core)
add_action( 'after_setup_theme', 'lew_init', 8 );

/**
 * ============================================================================
 * PLUGIN ACTIVATION HOOK
 * ============================================================================
 */

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

	// Flush rewrite rules
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'lew_activate' );

/**
 * Plugin deactivation hook
 */
function lew_deactivate() {
	// Flush rewrite rules
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'lew_deactivate' );
