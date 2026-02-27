<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

function emp_send_rsvp_emails( $event_id, $name, $email ) {
	$event_title = get_the_title( $event_id );
	$event_link  = get_permalink( $event_id );

	// Email to user
	$user_subject = sprintf( __( 'RSVP confirmed: %s', 'event-manager-pro' ), $event_title );
	$user_message = sprintf(
		__( "Hi %s,\n\nYour RSVP has been confirmed for: %s\n\nEvent link: %s\n\nThank you!", 'event-manager-pro' ),
		$name,
		$event_title,
		$event_link
	);
	wp_mail( $email, $user_subject, $user_message );

	// Email to admin
	$admin_email   = get_option( 'admin_email' );
	$admin_subject = sprintf( __( 'New RSVP: %s', 'event-manager-pro' ), $event_title );
	$admin_message = sprintf(
		__( "New RSVP received.\n\nEvent: %s\nName: %s\nEmail: %s\n\nLink: %s", 'event-manager-pro' ),
		$event_title,
		$name,
		$email,
		$event_link
	);
	wp_mail( $admin_email, $admin_subject, $admin_message );
}

/**
 * Notify admin when an event is published for the first time.
 * Notify RSVPs when an event is updated (only when already published).
 */
function emp_event_publish_update_notifications( $new_status, $old_status, $post ) {
	if ( ! $post || 'emp_event' !== $post->post_type ) {
		return;
	}

	// Only act for publish transitions or updates of published posts
	$is_publish_transition = ( 'publish' === $new_status && 'publish' !== $old_status );
	$is_update_published   = ( 'publish' === $new_status && 'publish' === $old_status );

	// 1) New publish -> email admin
	if ( $is_publish_transition ) {
		$admin_email = get_option( 'admin_email' );
		$subject     = sprintf( __( 'New Event Published: %s', 'event-manager-pro' ), $post->post_title );
		$message     = sprintf(
			__( "A new event has been published.\n\nTitle: %s\nLink: %s", 'event-manager-pro' ),
			$post->post_title,
			get_permalink( $post->ID )
		);

		wp_mail( $admin_email, $subject, $message );

		// Mark as notified to avoid re-notifying on quick status flips (bonus safety)
		update_post_meta( $post->ID, '_emp_notified_published', time() );
		return;
	}

	// 2) Update published -> email RSVPs
	if ( $is_update_published ) {
		// Simple anti-spam: don't send update emails more than once every 5 minutes
		$last_sent = (int) get_post_meta( $post->ID, '_emp_last_update_email_sent', true );
		if ( $last_sent && ( time() - $last_sent ) < 300 ) {
			return;
		}

		$rsvps = get_post_meta( $post->ID, '_emp_rsvps', true );
		if ( ! is_array( $rsvps ) || empty( $rsvps ) ) {
			return;
		}

		$subject = sprintf( __( 'Event Updated: %s', 'event-manager-pro' ), $post->post_title );
		$link    = get_permalink( $post->ID );

		foreach ( $rsvps as $r ) {
			if ( empty( $r['email'] ) || ! is_email( $r['email'] ) ) {
				continue;
			}
			$name = ! empty( $r['name'] ) ? $r['name'] : __( 'there', 'event-manager-pro' );

			$message = sprintf(
				__( "Hi %s,\n\nThe event you RSVPed to has been updated:\n\n%s\n%s\n\nThanks!", 'event-manager-pro' ),
				$name,
				$post->post_title,
				$link
			);

			wp_mail( $r['email'], $subject, $message );
		}

		update_post_meta( $post->ID, '_emp_last_update_email_sent', time() );
	}
}
add_action( 'transition_post_status', 'emp_event_publish_update_notifications', 10, 3 );