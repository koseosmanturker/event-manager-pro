<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function emp_events_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'type'   => '',          // taxonomy slug (emp_event_type)
			'from'   => '',          // YYYY-MM-DD
			'to'     => '',          // YYYY-MM-DD
			'search' => '',          // text search
			'limit'  => 10,
			'sort'   => 'date_asc',  // date_asc | date_desc
		),
		$atts,
		'events'
	);

	// Allow filters from URL query (?emp_type=...&emp_from=...&emp_to=...&emp_s=...&emp_sort=...)
	$get_type   = isset( $_GET['emp_type'] ) ? sanitize_text_field( wp_unslash( $_GET['emp_type'] ) ) : '';
	$get_from   = isset( $_GET['emp_from'] ) ? sanitize_text_field( wp_unslash( $_GET['emp_from'] ) ) : '';
	$get_to     = isset( $_GET['emp_to'] ) ? sanitize_text_field( wp_unslash( $_GET['emp_to'] ) ) : '';
	$get_search = isset( $_GET['emp_s'] ) ? sanitize_text_field( wp_unslash( $_GET['emp_s'] ) ) : '';
	$get_sort   = isset( $_GET['emp_sort'] ) ? sanitize_text_field( wp_unslash( $_GET['emp_sort'] ) ) : '';

	// Priority: URL filters override shortcode atts
	if ( '' !== $get_type ) {
		$atts['type'] = $get_type;
	}
	if ( '' !== $get_from ) {
		$atts['from'] = $get_from;
	}
	if ( '' !== $get_to ) {
		$atts['to'] = $get_to;
	}
	if ( '' !== $get_search ) {
		$atts['search'] = $get_search;
	}
	if ( '' !== $get_sort ) {
		$atts['sort'] = $get_sort;
	}

	$type   = sanitize_text_field( $atts['type'] );
	$from   = sanitize_text_field( $atts['from'] );
	$to     = sanitize_text_field( $atts['to'] );
	$search = sanitize_text_field( $atts['search'] );
	$sort   = sanitize_text_field( $atts['sort'] );

	$limit = absint( $atts['limit'] );
	if ( $limit <= 0 ) {
		$limit = 10;
	}

	$args = array(
		'post_type'      => 'emp_event',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		's'              => $search ? $search : '',
		'no_found_rows'  => true, // performance
	);

	// Taxonomy filter
	if ( $type ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'emp_event_type',
				'field'    => 'slug',
				'terms'    => $type,
			),
		);
	}

	// Date range filter (meta_query)
	$meta_query = array();

	if ( $from && emp_is_valid_date_ymd( $from ) ) {
		$meta_query[] = array(
			'key'     => '_emp_event_date',
			'value'   => $from,
			'compare' => '>=',
			'type'    => 'DATE',
		);
	}

	if ( $to && emp_is_valid_date_ymd( $to ) ) {
		$meta_query[] = array(
			'key'     => '_emp_event_date',
			'value'   => $to,
			'compare' => '<=',
			'type'    => 'DATE',
		);
	}

	if ( ! empty( $meta_query ) ) {
		$args['meta_query'] = $meta_query;
	}

	/**
	 * Sorting
	 * We always sort by event date when sort param is provided.
	 * (Works whether or not date filters are used.)
	 */
	if ( in_array( $sort, array( 'date_asc', 'date_desc' ), true ) ) {
		$args['meta_key'] = '_emp_event_date';
		$args['orderby']  = 'meta_value';
		$args['order']    = ( 'date_desc' === $sort ) ? 'DESC' : 'ASC';
	} else {
		// Fallback (in case of invalid value)
		$sort = 'date_asc';
		$args['meta_key'] = '_emp_event_date';
		$args['orderby']  = 'meta_value';
		$args['order']    = 'ASC';
	}

	// Build filter form (taxonomy dropdown)
	$terms = get_terms(
		array(
			'taxonomy'   => 'emp_event_type',
			'hide_empty' => false,
		)
	);

	$q = new WP_Query( $args );

	ob_start();

	// Filter Form (GET)
	$current_url = remove_query_arg( array( 'emp_type', 'emp_from', 'emp_to', 'emp_s', 'emp_sort' ) );

	echo '<form method="get" action="' . esc_url( $current_url ) . '" class="emp-events-filter">';

	// Type
	echo '<label class="emp-field">' . esc_html__( 'Type', 'event-manager-pro' ) . ': ';
	echo '<select name="emp_type">';
	echo '<option value="">' . esc_html__( 'All', 'event-manager-pro' ) . '</option>';

	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		foreach ( $terms as $t ) {
			$selected = selected( $type, $t->slug, false );
			echo '<option value="' . esc_attr( $t->slug ) . '"' . $selected . '>' . esc_html( $t->name ) . '</option>';
		}
	}

	echo '</select></label>';

	// From
	echo '<label class="emp-field">' . esc_html__( 'From', 'event-manager-pro' ) . ': ';
	echo '<input type="date" name="emp_from" value="' . esc_attr( $from ) . '"></label>';

	// To
	echo '<label class="emp-field">' . esc_html__( 'To', 'event-manager-pro' ) . ': ';
	echo '<input type="date" name="emp_to" value="' . esc_attr( $to ) . '"></label>';

	// Search
	echo '<label class="emp-field">' . esc_html__( 'Search', 'event-manager-pro' ) . ': ';
	echo '<input type="text" name="emp_s" value="' . esc_attr( $search ) . '" placeholder="' . esc_attr__( 'keyword...', 'event-manager-pro' ) . '"></label>';

	// Sort
	echo '<label class="emp-field">' . esc_html__( 'Sort', 'event-manager-pro' ) . ': ';
	echo '<select name="emp_sort">';
	echo '<option value="date_asc"' . selected( $sort, 'date_asc', false ) . '>' . esc_html__( 'Upcoming first', 'event-manager-pro' ) . '</option>';
	echo '<option value="date_desc"' . selected( $sort, 'date_desc', false ) . '>' . esc_html__( 'Latest first', 'event-manager-pro' ) . '</option>';
	echo '</select></label>';

	// Button
	echo '<button type="submit" class="emp-btn">' . esc_html__( 'Filter', 'event-manager-pro' ) . '</button>';

	echo '</form>';

	// Results
	echo '<div class="emp-events-list">';

	if ( $q->have_posts() ) {
		echo '<ul class="emp-list">';

		while ( $q->have_posts() ) {
			$q->the_post();

			$date = get_post_meta( get_the_ID(), '_emp_event_date', true );
			$loc  = get_post_meta( get_the_ID(), '_emp_event_location', true );

			echo '<li class="emp-item">';

			echo '<div class="emp-item-title">';
			echo '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
			echo '</div>';

			echo '<div class="emp-item-meta">';

			if ( $date ) {
				$ts = strtotime( $date );
				$pretty = $ts ? date_i18n( 'j F Y', $ts ) : $date;
				echo '<span>📅 ' . esc_html( $pretty ) . '</span>';
			}

			if ( $loc ) {
				echo '<span>📍 ' . esc_html( $loc ) . '</span>';
			}

			echo '</div>';

			echo '</li>';
		}

		echo '</ul>';
	} else {
		echo '<p>' . esc_html__( 'No events found.', 'event-manager-pro' ) . '</p>';
	}

	echo '</div>';

	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'events', 'emp_events_shortcode' );