<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

function emp_add_event_meta_boxes() {
	add_meta_box(
		'emp_event_details',
		__( 'Event Details', 'event-manager-pro' ),
		'emp_render_event_details_meta_box',
		'emp_event',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'emp_add_event_meta_boxes' );

function emp_render_event_details_meta_box( $post ) {
	// Security nonce field
	wp_nonce_field( 'emp_save_event_details', 'emp_event_details_nonce' );

	$event_date = get_post_meta( $post->ID, '_emp_event_date', true );
	$location   = get_post_meta( $post->ID, '_emp_event_location', true );

	?>
	<p>
		<label for="emp_event_date"><strong><?php echo esc_html__( 'Event Date', 'event-manager-pro' ); ?></strong></label><br>
		<input
			type="date"
			id="emp_event_date"
			name="emp_event_date"
			value="<?php echo esc_attr( $event_date ); ?>"
		/>
	</p>

	<p>
		<label for="emp_event_location"><strong><?php echo esc_html__( 'Location', 'event-manager-pro' ); ?></strong></label><br>
		<input
			type="text"
			id="emp_event_location"
			name="emp_event_location"
			value="<?php echo esc_attr( $location ); ?>"
			style="width: 100%; max-width: 420px;"
			placeholder="<?php echo esc_attr__( 'e.g., Istanbul', 'event-manager-pro' ); ?>"
		/>
	</p>
	<?php
}

function emp_is_valid_date_ymd( $date ) {
	if ( empty( $date ) ) {
		return true; // empty is allowed
	}
	$d = DateTime::createFromFormat( 'Y-m-d', $date );
	return $d && $d->format( 'Y-m-d' ) === $date;
}

function emp_save_event_details_meta( $post_id ) {
	// 1) Check nonce
	if ( ! isset( $_POST['emp_event_details_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST['emp_event_details_nonce'], 'emp_save_event_details' ) ) {
		return;
	}

	// 2) Prevent autosave overwrite
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// 3) Check post type
	if ( isset( $_POST['post_type'] ) && 'emp_event' !== $_POST['post_type'] ) {
		return;
	}

	// 4) Capability check
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// 5) Sanitize + validate + save
	$event_date = isset( $_POST['emp_event_date'] ) ? sanitize_text_field( wp_unslash( $_POST['emp_event_date'] ) ) : '';
	$location   = isset( $_POST['emp_event_location'] ) ? sanitize_text_field( wp_unslash( $_POST['emp_event_location'] ) ) : '';

	if ( ! emp_is_valid_date_ymd( $event_date ) ) {
		// If invalid, don't save bad data
		$event_date = '';
	}

	update_post_meta( $post_id, '_emp_event_date', $event_date );
	update_post_meta( $post_id, '_emp_event_location', $location );
}
add_action( 'save_post_emp_event', 'emp_save_event_details_meta' );