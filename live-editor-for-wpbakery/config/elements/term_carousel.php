<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: us_term_carousel
 */

$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

// Get params from Term List and Content Carousel to avoid params duplication
$term_list_params = array();

if ( us_is_elm_editing_page() ) {
	$term_list_params = us_config( 'elements/term_list.params' );
	$responsive_params = us_config( 'elements_responsive_options' );
}

foreach( $term_list_params as $_param_name => &$_param ) {

	if ( in_array( $_param_name, array_keys( $conditional_params ) ) ) {
		unset( $term_list_params[ $_param_name ] );
	}
	if ( in_array( $_param_name, array_keys( $design_options_params ) ) ) {
		unset( $term_list_params[ $_param_name ] );
	}
	if ( in_array( $_param_name, array_keys( $responsive_params ) ) ) {
		unset( $term_list_params[ $_param_name ] );
	}

	if ( ! empty( $_param['exclude_for_us_term_carousel'] ) ) {
		unset( $term_list_params[ $_param_name ] );
	}
	if ( $_param_name === 'items_gap' ) {
		$_param['group'] = __( 'Carousel', 'us' );
		$_param['usb_preview'] = TRUE;
	}
}

$content_carousel_params = us_config( 'elements/content_carousel.params' );

foreach ( $content_carousel_params as $_param_name => &$_param ) {

	if ( in_array( $_param_name, array_keys( $conditional_params ) ) ) {
		unset( $content_carousel_params[ $_param_name ] );
	}
	if ( in_array( $_param_name, array_keys( $design_options_params ) ) ) {
		unset( $content_carousel_params[ $_param_name ] );
	}

	if ( ! empty( $_param['exclude_for_us_carousel'] ) ) {
		unset( $content_carousel_params[ $_param_name ] );
	}
	if ( ! isset( $_param['group'] ) ) {
		$_param['group'] = __( 'Carousel', 'us' );
	}
}
unset( $_param );

return array(
	'title' => __( 'Term Carousel', 'us' ),
	'category' => __( 'Lists', 'us' ),
	'icon' => 'fas fa-laptop-code',
	'class' => 'improve_list_elm_ui show_new_badge',
	'usb_reload_element' => TRUE,
	'params' => us_set_params_weight(
		$term_list_params,
		$content_carousel_params,
		$conditional_params,
		$design_options_params
	),
	'usb_init_js' => '
		$elm.usCarousel();
		$us.$window.trigger( \'scroll.waypoints\' );
		jQuery( \'[data-content-height]\', $elm ).usCollapsibleContent()
	',
);
