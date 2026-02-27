<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

function emp_register_event_meta_rest() {
	register_post_meta(
		'emp_event',
		'_emp_event_date',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () {
				return true; // readable publicly
			},
		)
	);

	register_post_meta(
		'emp_event',
		'_emp_event_location',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () {
				return true;
			},
		)
	);
}
add_action( 'init', 'emp_register_event_meta_rest' );

/**
 * REST: RSVP endpoint
 * POST /wp-json/emp/v1/events/<id>/rsvp
 * body: { "name": "...", "email": "..." }
 */
function emp_register_rest_routes() {
	register_rest_route(
		'emp/v1',
		'/events/(?P<id>\d+)/rsvp',
		array(
			'methods'             => 'POST',
			'callback'            => 'emp_rest_rsvp_callback',
			'permission_callback' => '__return_true', // public RSVP allowed (can tighten later)
			'args'                => array(
				'name'  => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'email' => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_email',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'emp_register_rest_routes' );

/**
 * REST RSVP handler.
 */
function emp_rest_rsvp_callback( WP_REST_Request $request ) {
	$event_id = absint( $request['id'] );

	if ( ! $event_id || 'emp_event' !== get_post_type( $event_id ) ) {
		return new WP_REST_Response(
			array( 'error' => 'Invalid event ID' ),
			404
		);
	}

	// Basic rate limit per IP+event: 1 request per 10 seconds
	$ip        = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$rate_key  = 'emp_rsvp_rate_' . md5( $ip . '_' . $event_id );
	$rate_hit  = get_transient( $rate_key );
	if ( $rate_hit ) {
		return new WP_REST_Response(
			array( 'error' => 'Too many requests. Please wait.' ),
			429
		);
	}
	set_transient( $rate_key, 1, 10 );

	$name  = sanitize_text_field( (string) $request->get_param( 'name' ) );
	$email = sanitize_email( (string) $request->get_param( 'email' ) );

	if ( empty( $name ) || empty( $email ) || ! is_email( $email ) ) {
		return new WP_REST_Response(
			array( 'error' => 'Invalid name or email' ),
			400
		);
	}

	$rsvps = get_post_meta( $event_id, '_emp_rsvps', true );
	if ( ! is_array( $rsvps ) ) {
		$rsvps = array();
	}

	foreach ( $rsvps as $r ) {
		if ( isset( $r['email'] ) && strtolower( $r['email'] ) === strtolower( $email ) ) {
			return new WP_REST_Response(
				array( 'status' => 'already_rsvped' ),
				200
			);
		}
	}

	$rsvps[] = array(
		'name'  => $name,
		'email' => $email,
		'time'  => time(),
	);

	update_post_meta( $event_id, '_emp_rsvps', $rsvps );

	// optional: send emails
	emp_send_rsvp_emails( $event_id, $name, $email );

	return new WP_REST_Response(
		array(
			'status'   => 'success',
			'event_id' => $event_id,
		),
		200
	);
}