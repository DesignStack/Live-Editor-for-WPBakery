<?php
/**
 * Main class of Live Editor for WPBakery
 *
 * @package LiveEditorWPBakery
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

/**
 * LEW_Builder Class
 */
final class LEW_Builder {

	/**
	 * CSS classes for the body element
	 *
	 * @var array
	 */
	private $body_classes = array( 'lew-builder' );

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( is_admin() ) {
			// On the page builder we initialize all the necessary handlers and functionality
			if ( lew_is_builder_page() ) {
				// Disable errors output for deprecated functions
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					add_filter( 'deprecated_function_trigger_error', '__return_false' );
				}

				// Get the post ID
				$post_id = lew_get_post_id();

				// Set the post ID for methods from the main functionality
				add_filter( 'lew_get_current_id', function( $current_id ) {
					return $current_id > 0 ? $current_id : lew_get_post_id();
				} );

				// Get minimal information about a record
				global $post; // force WP_Post object
				$post = get_post( $post_id );

				// Go to the home page if no post ID
				if ( ! wp_doing_ajax() && ! $post_id ) {
					$post_id = (int) get_option( 'page_on_front' );
					if ( ! $post_id ) {
						$post_id = $this->get_first_editable_post();
					}
					if ( $post_id ) {
						wp_redirect( lew_get_edit_link( $post_id ) );
						exit;
					}
				}

				// Post unlock link handler in the builder
				if ( ! empty( $post_id ) && ! empty( $_GET['get-post-lock'] ) ) {
					add_action( 'admin_init', function() use ( $post_id ) {
						check_admin_referer( 'lock-post_' . $post_id );
						wp_set_post_lock( $post_id );
						wp_redirect( lew_get_edit_link( $post_id ) );
						exit;
					}, 1 );
					return;
				}

				// If the post_id is set then check the post
				if ( ! empty( $post_id ) ) {
					// Check if a post exists
					if ( is_null( $post ) ) {
						wp_die( __( 'You attempted to edit an item that doesn\'t exist. Perhaps it was deleted?', 'live-editor-wpbakery' ) );
					}

					// Checking edit permission by post type
					if ( ! in_array( $post->post_type, lew_get_allowed_edit_post_types() ) ) {
						wp_die( __( 'Editing of this page is not supported.', 'live-editor-wpbakery' ) );
					}

					// Checking edit permission by post ID
					if ( ! current_user_can( 'edit_post', $post_id ) ) {
						wp_die( __( 'Sorry, you are not allowed to access this page.', 'live-editor-wpbakery' ) );
					}

					// Publish the post if it doesn't exist
					if ( $post->post_status === 'auto-draft' ) {
						wp_update_post(
							array(
								'ID'          => $post_id,
								'post_title'  => '#' . $post_id,
								'post_status' => 'publish',
							)
						);
					}
				}

				LEW_Panel::instance(); // this class describes the functionality of the panel

				// Initializing the builder actions
				add_action( 'admin_action_lew-builder', array( $this, 'init_builder_page' ), 1 );

				// Add styles and scripts on the builder page
				add_action( 'wp_print_styles', array( $this, 'enqueue_assets_for_builder' ), 1 );

				// Outputs the editor scripts, stylesheets, and default settings
				add_action( 'lew_admin_footer_scripts', array( $this, 'admin_footer_scripts' ), 1 );

			} else {
				// At regular admin pages, add a link to Live Editor for posts and pages
				add_filter( 'post_row_actions', array( $this, 'row_actions' ), 501, 2 );
				add_filter( 'page_row_actions', array( $this, 'row_actions' ), 501, 2 );

				add_action( 'edit_form_after_title', array( $this, 'output_builder_switch' ) );
			}

		} else {
			// Frontend

			// Disable WPBakery custom CSS output to avoid duplicates
			add_filter( 'vc_post_custom_css', '__return_false' );

			// The output custom css for a page
			if ( lew_is_post_preview() ) {
				add_action( 'wp_head', array( $this, 'output_post_custom_css' ), 999 );
			}

			// Setting up a site as a preview
			if ( lew_is_preview() ) {
				new LEW_Preview(); // this is a class for working with shortcodes

			} elseif ( has_action( 'admin_bar_menu' ) ) {
				add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu_action' ), 81 );
			}
		}

		// Init the class for handling AJAX requests
		if ( wp_doing_ajax() ) {
			LEW_Ajax::init();
		}
	}

	/**
	 * Get first editable post
	 *
	 * @return int|null
	 */
	private function get_first_editable_post() {
		$args = array(
			'post_type'      => lew_get_allowed_edit_post_types(),
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$posts = get_posts( $args );
		return ! empty( $posts ) ? $posts[0]->ID : null;
	}

	/**
	 * Add styles and scripts on the builder page
	 */
	public function enqueue_assets_for_builder() {
		global $wp_scripts, $wp_styles;

		// Reset scripts
		$wp_scripts = new WP_Scripts();

		// Reset styles
		$wp_styles = new WP_Styles();
		wp_default_styles( $wp_styles );

		// Remove assets that are not in use
		wp_dequeue_script( 'admin-bar' );

		// Include all files needed to use the WordPress media API
		wp_enqueue_media();
		wp_enqueue_style( 'media' );
		wp_enqueue_editor();

		// WordPress styles for correct appearance of fields
		wp_enqueue_style( 'forms' );

		/**
		 * Hook for changing the output of assets on the constructor page
		 */
		do_action( 'lew_enqueue_assets_for_builder' );

		// If the post is locked, then we display only the styles
		if ( lew_post_editing_is_locked() ) {
			wp_enqueue_style( 'lew-builder', LEW_BUILDER_URL . 'assets/css/builder.min.css', array(), LEW_VERSION );
			return;
		}

		// Enqueue builder assets
		wp_enqueue_script( 'lew-builder-js', LEW_BUILDER_URL . 'assets/js/builder.min.js', array( 'jquery' ), LEW_VERSION, true );
		wp_enqueue_style( 'lew-builder-css', LEW_BUILDER_URL . 'assets/css/builder.min.css', array(), LEW_VERSION );
	}

	/**
	 * Add a link that will be displayed under the title of the record in the records table in the admin panel
	 *
	 * @param array   $actions Actions array
	 * @param WP_Post $post    The current post object
	 * @return array
	 */
	public function row_actions( $actions, $post ) {
		if (
			in_array( $post->post_type, lew_get_allowed_edit_post_types() )
			&& $post->post_status !== 'trash' // don't add link for deleted posts
		) {
			// Add link to edit post live
			$edit_post_link = lew_get_edit_link( $post->ID );
			$actions['edit_lew_builder'] = '<a href="' . esc_url( $edit_post_link ) . '">' . esc_html__( 'Edit Live', 'live-editor-wpbakery' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Add a button that switches editing to Live Builder
	 *
	 * @param WP_Post $post The current post object
	 */
	public function output_builder_switch( $post ) {
		if ( ! in_array( $post->post_type, lew_get_allowed_edit_post_types() ) ) {
			return;
		}
		?>
		<div id="lew-switch" style="margin: 10px 0;">
			<a href="<?php echo esc_url( lew_get_edit_link( $post->ID ) ); ?>" class="button button-primary button-large">
				<span><?php esc_html_e( 'Edit Live', 'live-editor-wpbakery' ); ?></span>
			</a>
		</div>
		<?php
	}

	/**
	 * Output Custom CSS in Preview
	 */
	public function output_post_custom_css() {
		$post_id = lew_get_post_id();

		// Get custom css for latest revision
		if ( isset( $_GET['preview'] ) && $_GET['preview'] === 'true' && wp_revisions_enabled( get_post( $post_id ) ) ) {
			$latest_revision = wp_get_post_revisions( $post_id );
			if ( ! empty( $latest_revision ) ) {
				$array_values = array_values( $latest_revision );
				$post_id = $array_values[0]->ID;
			}
		}

		$post_custom_css = get_post_meta( $post_id, 'lew_post_custom_css', true );
		$post_custom_css = apply_filters( 'lew_output_post_custom_css', $post_custom_css, $post_id );

		if ( empty( $post_custom_css ) ) {
			return;
		}

		echo '<style type="text/css" data-type="lew_post_custom_css">';
		echo wp_strip_all_tags( $post_custom_css );
		echo '</style>';
	}

	/**
	 * This is the hook used to add, remove, or manipulate admin bar items
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The admin bar
	 */
	public function admin_bar_menu_action( $wp_admin_bar ) {
		$post_id = null;

		if ( is_front_page() ) {
			$post_id = get_option( 'page_on_front' );
		} elseif ( is_home() ) {
			$post_id = get_option( 'page_for_posts' );
		} elseif ( is_singular( lew_get_allowed_edit_post_types() ) ) {
			$post_id = get_queried_object_id();
		}

		if ( ! empty( $post_id ) && current_user_can( 'edit_post', $post_id ) ) {
			$edit_link = lew_get_edit_link( $post_id );
		}

		if ( empty( $edit_link ) ) {
			return;
		}

		$node = array(
			'id'    => 'lew-builder',
			'title' => __( 'Edit Live', 'live-editor-wpbakery' ),
			'href'  => $edit_link,
			'meta'  => array(
				'class' => 'lew-builder',
				'html'  => '<style>.lew-builder > a{font-weight:600!important;color:#23ccaa!important}</style>',
			),
		);

		$wp_admin_bar->add_node( $node );
	}

	/**
	 * Including additional scripts or settings in the output
	 */
	public function admin_footer_scripts() {
		// Get output footer scripts
		do_action( 'admin_print_footer_scripts' );

		// Heartbeat for post locking
		wp_enqueue_script( 'heartbeat' );

		// Post locked notice
		$users_args = array(
			'fields' => 'ID',
			'number' => 2,
		);

		if (
			function_exists( '_admin_notice_post_locked' )
			&& (
				is_multisite()
				|| count( get_users( $users_args ) ) > 1
			)
		) {
			add_filter( 'get_edit_post_link', array( $this, 'filter_unlock_post_link' ), 501, 2 );
			_admin_notice_post_locked();
			remove_filter( 'get_edit_post_link', array( $this, 'filter_unlock_post_link' ) );
		}

		// Print scripts
		if ( function_exists( 'wp_print_scripts' ) ) {
			wp_print_scripts();
		}

		// If the post is locked, then exit
		if ( lew_post_editing_is_locked() ) {
			return;
		}
	}

	/**
	 * Filter for getting a link to unblock a post in the builder
	 *
	 * @param string $link    The edit link
	 * @param int    $post_id Post ID
	 * @return string Returns a link to unblock a post
	 */
	public function filter_unlock_post_link( $link, $post_id ) {
		return lew_get_edit_link( $post_id );
	}

	/**
	 * Initializing the builder page
	 */
	public function init_builder_page() {
		$post_id = lew_get_post_id();
		$post_type = get_post_type( $post_id );

		if ( is_rtl() ) {
			$this->body_classes[] = 'rtl';
		}

		// Post edit locked
		if ( lew_post_editing_is_locked() ) {
			$this->body_classes[] = 'edit_locked';
		}

		// Get a link to the page
		$post_link = ! empty( $post_id )
			? get_permalink( $post_id )
			: get_home_url( null, '/' );
		$post_link = apply_filters( 'the_permalink', $post_link );

		// Get responsive breakpoints
		$breakpoints = lew_get_responsive_breakpoints();

		// Mask for the title of the edited page
		$admin_page_title = esc_html__( 'Edit Live', 'live-editor-wpbakery' ) . ' - %s';

		// The general settings for Live Editor
		$lew_config = array(
			'_nonce' => LEW_Ajax::create_nonce(),

			// Mask for the title of the edited page
			'adminPageTitleMask' => $admin_page_title,

			// Meta key for post custom css
			'keyCustomCss' => 'lew_post_custom_css',

			// List of actions
			'actions' => array(
				'post_editing' => 'lew-builder',
			),

			// Settings for preview
			'preview' => array(
				// Minimum preview screen width (in pixels)
				'minWidth'  => 320,
				// Minimum preview screen height (in pixels)
				'minHeight' => 320,
			),

			// Default parameters for AJAX requests
			'ajaxArgs' => array(
				'post'   => $post_id,
				'_nonce' => (
					! lew_post_editing_is_locked()
						? wp_create_nonce( 'lew-builder' )
						: ''
				),
			),

			// The set responsive states
			'responsiveStates' => array_keys( $breakpoints ),

			// Set breakpoints
			'breakpoints' => $breakpoints,
		);

		// Get edit post link
		$edit_post_link = get_edit_post_link( $post_id );

		// Text translations
		$text_translations = array(
			'empty_clipboard'        => __( 'There is nothing to paste.', 'live-editor-wpbakery' ),
			'failed_set_data'        => __( 'An error has occurred. Please reload the page and try again.', 'live-editor-wpbakery' ),
			'invalid_data'           => __( 'Invalid value.', 'live-editor-wpbakery' ),
			'cannot_paste'           => __( 'Cannot paste into this container.', 'live-editor-wpbakery' ),
			'page_leave_warning'     => __( 'The changes you made will be lost if you navigate away from this page.', 'live-editor-wpbakery' ),
			'page_updated'           => sprintf(
				'%s <a href="%s" target="_blank">%s</a>',
				__( 'Page updated.', 'live-editor-wpbakery' ),
				$post_link,
				__( 'View Page', 'live-editor-wpbakery' )
			),
			'editing_not_supported' => sprintf(
				'%s<br><a href="%s" target="_blank">%s</a>',
				__( 'Editing of this element is not supported.', 'live-editor-wpbakery' ),
				$edit_post_link,
				__( 'Edit page in Backend', 'live-editor-wpbakery' )
			),
		);

		// Get current preview URL
		$current_preview_url = add_query_arg(
			array(
				'lew-builder' => wp_create_nonce( 'lew-builder' ),
			),
			$post_link
		);

		// If the post is blocked, then load as a simple page, not a preview
		if ( lew_post_editing_is_locked() ) {
			$current_preview_url = $post_link;
		}

		// Add an action class to apply all styles on load
		$this->body_classes[] = 'action_lew-builder';

		// Get the title of the page being edited
		if ( ! empty( $post_id ) ) {
			$admin_page_title = sprintf( $admin_page_title, get_the_title( $post_id ) );
		} else {
			$admin_page_title = sprintf( $admin_page_title, get_bloginfo( 'name' ) );
		}

		// Load the main page template
		lew_load_template(
			'main',
			array(
				'ajaxurl'             => admin_url( 'admin-ajax.php' ),
				'body_class'          => implode( ' ', $this->body_classes ),
				'current_preview_url' => $current_preview_url,
				'edit_post_link'      => $edit_post_link,
				'post_link'           => $post_link,
				'post_type'           => $post_type,
				'text_translations'   => $text_translations,
				'title'               => $admin_page_title,
				'lew_config'          => $lew_config,
			)
		);
		exit;
	}
}
