<?php
/**
 * Main template for Live Editor for WPBakery
 *
 * @var array  $lew_config Array of configs for live edit
 * @var string $ajaxurl Link for AJAX requests
 * @var string $body_class Classes for the page body
 * @var string $current_preview_url Current preview URL
 * @var string $edit_post_link Link to the post editing in the admin area
 * @var string $post_link Link to the post on frontend
 * @var string $title Page title
 * @var array  $text_translations Text translations
 * @var string $post_type Post type
 *
 * @package LiveEditorWPBakery
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

$post_id = lew_get_post_id();

$post_has_frontend_view = ! in_array( $post_type, array( 'us_page_block', 'us_content_template' ), true );
?>
<!DOCTYPE HTML>
<html dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" <?php language_attributes( 'html' ); ?>>
<head>
	<title><?php echo esc_html( $title ); ?></title>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<?php wp_print_styles(); ?>
	<script type="text/javascript">
		// Link to get data via AJAX
		var ajaxurl = '<?php echo esc_js( $ajaxurl ); ?>';
		// Text translations for Live Builder
		window.lewGlobalData = window.lewGlobalData || {};
		window.lewGlobalData.textTranslations = <?php echo wp_json_encode( $text_translations ); ?>;
		window.lewCurrentPostId = <?php echo (int) $post_id; ?>;
	</script>
</head>
<body class="<?php echo esc_attr( $body_class ); ?>">
<div id="lew-wrapper" class="lew-wrapper"<?php echo lew_pass_data_to_js( $lew_config ); ?>>

	<!-- Begin left sidebar / Panel -->
	<aside id="lew-panel" class="lew-panel wp-core-ui">
		<div class="lew-panel-switcher" title="<?php esc_attr_e( 'Hide/Show panel', 'live-editor-wpbakery' ); ?>">
			<span class="dashicons dashicons-arrow-left-alt2"></span>
		</div>

		<!-- Panel Header -->
		<header class="lew-panel-header">
			<div class="lew-panel-header-title">
				<h1><?php esc_html_e( 'Live Editor', 'live-editor-wpbakery' ); ?></h1>
			</div>

			<!-- Menu -->
			<div class="lew-panel-header-menu">
				<button class="lew-menu-button" title="<?php esc_attr_e( 'Menu', 'live-editor-wpbakery' ); ?>">
					<span class="dashicons dashicons-menu"></span>
				</button>
				<div class="lew-panel-header-menu-list">
					<ul>
						<?php if ( $post_has_frontend_view ) : ?>
						<li>
							<a href="<?php echo esc_url( $post_link ); ?>" target="_blank">
								<?php esc_html_e( 'View Page', 'live-editor-wpbakery' ); ?>
							</a>
						</li>
						<?php endif; ?>
						<li>
							<a href="<?php echo esc_url( admin_url( '/' ) ); ?>" target="_blank">
								<?php esc_html_e( 'Visit Dashboard', 'live-editor-wpbakery' ); ?>
							</a>
						</li>
						<li>
							<a href="<?php echo esc_url( $edit_post_link ); ?>" target="_blank">
								<?php esc_html_e( 'Edit in Backend', 'live-editor-wpbakery' ); ?>
							</a>
						</li>
					</ul>
				</div>
			</div>

			<!-- Save Button -->
			<div class="lew-panel-header-save">
				<button class="button button-primary lew-save-button" disabled>
					<?php esc_html_e( 'Save Changes', 'live-editor-wpbakery' ); ?>
				</button>
			</div>
		</header>

		<!-- Panel Body -->
		<div id="lew-panel-body" class="lew-panel-body">

			<!-- Elements Tab -->
			<div class="lew-panel-section" data-section="elements">
				<h3><?php esc_html_e( 'Add Elements', 'live-editor-wpbakery' ); ?></h3>
				<div class="lew-elements-list">
					<p><?php esc_html_e( 'WPBakery elements will appear here', 'live-editor-wpbakery' ); ?></p>
				</div>
			</div>

			<!-- Navigator Tab -->
			<div class="lew-panel-section" data-section="navigator">
				<h3><?php esc_html_e( 'Navigator', 'live-editor-wpbakery' ); ?></h3>
				<div class="lew-navigator">
					<p><?php esc_html_e( 'Page structure will appear here', 'live-editor-wpbakery' ); ?></p>
				</div>
			</div>

			<!-- Settings Tab -->
			<div class="lew-panel-section" data-section="settings">
				<h3><?php esc_html_e( 'Page Settings', 'live-editor-wpbakery' ); ?></h3>
				<div class="lew-settings">
					<p><?php esc_html_e( 'Page settings will appear here', 'live-editor-wpbakery' ); ?></p>
				</div>
			</div>

		</div>
	</aside>

	<!-- Preview Frame -->
	<main id="lew-preview" class="lew-preview">

		<!-- Preview Toolbar -->
		<div class="lew-preview-toolbar">
			<div class="lew-preview-toolbar-left">
				<span class="lew-preview-label"><?php esc_html_e( 'Preview:', 'live-editor-wpbakery' ); ?></span>
			</div>

			<div class="lew-preview-toolbar-center">
				<!-- Responsive Mode Switcher -->
				<div class="lew-preview-responsive">
					<button class="lew-preview-mode active" data-mode="default" title="<?php esc_attr_e( 'Desktop', 'live-editor-wpbakery' ); ?>">
						<span class="dashicons dashicons-desktop"></span>
					</button>
					<button class="lew-preview-mode" data-mode="tablet" title="<?php esc_attr_e( 'Tablet', 'live-editor-wpbakery' ); ?>">
						<span class="dashicons dashicons-tablet"></span>
					</button>
					<button class="lew-preview-mode" data-mode="mobile" title="<?php esc_attr_e( 'Mobile', 'live-editor-wpbakery' ); ?>">
						<span class="dashicons dashicons-smartphone"></span>
					</button>
				</div>
			</div>

			<div class="lew-preview-toolbar-right">
				<!-- Preview URL -->
				<div class="lew-preview-url">
					<a href="<?php echo esc_url( $current_preview_url ); ?>" target="_blank" title="<?php esc_attr_e( 'Open in new tab', 'live-editor-wpbakery' ); ?>">
						<span class="dashicons dashicons-external"></span>
					</a>
				</div>
			</div>
		</div>

		<!-- Preview iFrame Container -->
		<div class="lew-preview-container">
			<?php if ( ! lew_post_editing_is_locked() && ! empty( $current_preview_url ) ) : ?>
			<iframe
				id="lew-preview-iframe"
				class="lew-preview-iframe"
				src="<?php echo esc_url( $current_preview_url ); ?>"
				frameborder="0"
			></iframe>
			<?php else : ?>
			<div class="lew-preview-locked">
				<p><?php esc_html_e( 'This post is currently being edited by another user.', 'live-editor-wpbakery' ); ?></p>
			</div>
			<?php endif; ?>
		</div>

	</main>

</div>

<?php
// Output footer scripts
do_action( 'lew_admin_footer_scripts' );
?>

</body>
</html>
