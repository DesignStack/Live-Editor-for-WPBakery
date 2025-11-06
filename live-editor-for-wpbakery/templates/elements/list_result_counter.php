<?php defined( 'ABSPATH' ) or die( 'This script cannot be accessed directly.' );

/**
 * Shortcode: us_list_result_counter
 *
 * @var string $text Main text string
 * @var string $text_single Single result text string
 * @var string $text_no_results No results text string
 * @var string $classes Extend class names
 *
 * @param string $el_class Extra class name
 * @param string $el_id Element ID
 */

if ( ! is_archive() AND ! usb_is_preview() ) {
	return;
}

$_atts = array(
	'class' => 'w-list-result-counter',
);

$_atts['class'] .= $classes ?? '';

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

global $wp_query;

if ( usb_is_preview() ) {
	$total = $total_unfiltered = 24; // placeholder value for Live Builder
} else {
	$total = $total_unfiltered = $wp_query->found_posts;
}

$posts_per_page = intval( $wp_query->get( 'posts_per_page' ) );
$paged = max( 1, get_query_var( 'paged', 1 ) );

$lower = ( ( $paged - 1 ) * $posts_per_page ) + 1;
$upper = min( $total, $paged * $posts_per_page );

$json_data = array(
	'text' => $text,
	'textSingle' => $text_single,
	'textNoResults' => $text_no_results,
	'totalUnfiltered' => $total_unfiltered,
	'perPage' => $posts_per_page,
);

if ( $total === 0 ) {
	$text = $text_no_results;
}
if ( $total === 1 ) {
	$text = $text_single;
}

$formatted_text = strtr( $text, array(
	'[lower]' => $lower,
	'[upper]' => $upper,
	'[total]' => $total,
	'[total_unfiltered]' => $total_unfiltered,
) );

if ( empty( $formatted_text ) ) {
	return;
}

echo '<div' . us_implode_atts( $_atts ) . us_pass_data_to_js( $json_data ) . '>';
echo $formatted_text;
echo '</div>';
