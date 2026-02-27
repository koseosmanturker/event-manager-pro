<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

function emp_event_columns( $columns ) {
	$columns['emp_event_date']     = __( 'Event Date', 'event-manager-pro' );
	$columns['emp_event_location'] = __( 'Location', 'event-manager-pro' );
	return $columns;
}
add_filter( 'manage_emp_event_posts_columns', 'emp_event_columns' );

/**
 * Render custom column values.
 */
function emp_render_event_columns( $column, $post_id ) {
	if ( 'emp_event_date' === $column ) {
		$date = get_post_meta( $post_id, '_emp_event_date', true );
		echo $date ? esc_html( $date ) : '—';
	}

	if ( 'emp_event_location' === $column ) {
		$loc = get_post_meta( $post_id, '_emp_event_location', true );
		echo $loc ? esc_html( $loc ) : '—';
	}
}
add_action( 'manage_emp_event_posts_custom_column', 'emp_render_event_columns', 10, 2 );