<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function emp_render_rsvp_form( $event_id ) {
	if ( ! $event_id ) {
		return '';
	}

	// Show a success message after redirect
	$success = isset( $_GET['emp_rsvp'] ) ? sanitize_text_field( wp_unslash( $_GET['emp_rsvp'] ) ) : '';

	ob_start();

	if ( 'success' === $success ) {
		echo '<div class="emp-rsvp-success">';
		echo esc_html__( 'Thanks! Your RSVP has been recorded.', 'event-manager-pro' );
		echo '</div>';
	}

	echo '<div class="emp-card emp-rsvp-box">';
	echo '<h3 class="emp-rsvp-title">' . esc_html__( 'RSVP', 'event-manager-pro' ) . '</h3>';

	echo '<form method="post" class="emp-rsvp-form">';
	wp_nonce_field( 'emp_rsvp_action', 'emp_rsvp_nonce' );

	echo '<div class="emp-field">';
	echo '<label for="emp_rsvp_name"><strong>' . esc_html__( 'Your Name', 'event-manager-pro' ) . '</strong></label>';
	echo '<input id="emp_rsvp_name" type="text" name="emp_rsvp_name" required>';
	echo '</div>';

	echo '<div class="emp-field">';
	echo '<label for="emp_rsvp_email"><strong>' . esc_html__( 'Your Email', 'event-manager-pro' ) . '</strong></label>';
	echo '<input id="emp_rsvp_email" type="email" name="emp_rsvp_email" required>';
	echo '</div>';

	echo '<input type="hidden" name="emp_event_id" value="' . esc_attr( $event_id ) . '">';

	echo '<button class="emp-btn" type="submit" name="emp_rsvp_submit" value="1">';
	echo esc_html__( 'Confirm Attendance', 'event-manager-pro' );
	echo '</button>';

	echo '</form>';
	echo '</div>';

	return ob_get_clean();
}

/**
 * Handle RSVP form submission.
 */
function emp_handle_rsvp_submission() {
	if ( ! isset( $_POST['emp_rsvp_submit'] ) ) {
		return;
	}

	// Nonce check
	if ( ! isset( $_POST['emp_rsvp_nonce'] ) || ! wp_verify_nonce( $_POST['emp_rsvp_nonce'], 'emp_rsvp_action' ) ) {
		return;
	}

	$event_id = isset( $_POST['emp_event_id'] ) ? absint( $_POST['emp_event_id'] ) : 0;
	if ( ! $event_id || 'emp_event' !== get_post_type( $event_id ) ) {
		return;
	}

	$name  = isset( $_POST['emp_rsvp_name'] ) ? sanitize_text_field( wp_unslash( $_POST['emp_rsvp_name'] ) ) : '';
	$email = isset( $_POST['emp_rsvp_email'] ) ? sanitize_email( wp_unslash( $_POST['emp_rsvp_email'] ) ) : '';

	if ( empty( $name ) || empty( $email ) || ! is_email( $email ) ) {
		return;
	}

	// Load existing RSVPs
	$rsvps = get_post_meta( $event_id, '_emp_rsvps', true );
	if ( ! is_array( $rsvps ) ) {
		$rsvps = array();
	}

	// Prevent duplicates by email
	foreach ( $rsvps as $r ) {
		if ( isset( $r['email'] ) && strtolower( $r['email'] ) === strtolower( $email ) ) {
			wp_safe_redirect( add_query_arg( 'emp_rsvp', 'success', get_permalink( $event_id ) ) );
			exit;
		}
	}

	$rsvps[] = array(
		'name'  => $name,
		'email' => $email,
		'time'  => time(),
	);

	update_post_meta( $event_id, '_emp_rsvps', $rsvps );

	// Send emails
	if ( function_exists( 'emp_send_rsvp_emails' ) ) {
		emp_send_rsvp_emails( $event_id, $name, $email );
	}

	// Redirect (prevents resubmission on refresh)
	wp_safe_redirect( add_query_arg( 'emp_rsvp', 'success', get_permalink( $event_id ) ) );
	exit;
}
add_action( 'template_redirect', 'emp_handle_rsvp_submission' );