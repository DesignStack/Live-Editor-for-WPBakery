<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: [us_list_result_counter]
 */

$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

/**
 * @return array
 */
return array(
	'title' => __( 'List Result Counter', 'us' ),
	'category' => __( 'Lists', 'us' ),
	'show_for_post_types' => array( 'us_content_template' ),
	'icon' => 'fas fa-abacus',
	'class' => 'show_new_badge',
	'params' => us_set_params_weight(
		array(
			'text' => array(
				'title' => us_translate( 'Text' ),
				'type' => 'text',
				'std' => sprintf( __( '%s - %s of %s results', 'us' ), '[lower]', '[upper]', '[total]' ),
				'description' =>
					'[lower] – ' . __( 'Lower result number', 'us' )
					. '<br>[upper] – ' . __( 'Upper result number', 'us' )
					. '<br>[total] – ' . __( 'Total number of filtered results', 'us' )
					. '<br>[total_unfiltered] – ' . __( 'Total number of unfiltered results', 'us' ),
				'admin_label' => TRUE,
				'usb_preview' => TRUE,
			),
			'text_single' => array(
				'title' => __( 'Text for single result', 'us' ),
				'type' => 'text',
				'std' => __( '1 result', 'us' ),
			),
			'text_no_results' => array(
				'title' => __( 'Text when no results', 'us' ),
				'type' => 'text',
				'std' => '',
				'description' => __( 'Leave blank to hide the element', 'us' ),
			),
		),

		$conditional_params,
		$design_options_params
	),
);
