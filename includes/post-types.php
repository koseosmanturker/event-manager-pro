<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

function emp_register_event_cpt() {
	$labels = array(
		'name'                  => __( 'Events', 'event-manager-pro' ),
		'singular_name'         => __( 'Event', 'event-manager-pro' ),
		'menu_name'             => __( 'Events', 'event-manager-pro' ),
		'name_admin_bar'        => __( 'Event', 'event-manager-pro' ),
		'add_new'               => __( 'Add New', 'event-manager-pro' ),
		'add_new_item'          => __( 'Add New Event', 'event-manager-pro' ),
		'new_item'              => __( 'New Event', 'event-manager-pro' ),
		'edit_item'             => __( 'Edit Event', 'event-manager-pro' ),
		'view_item'             => __( 'View Event', 'event-manager-pro' ),
		'all_items'             => __( 'All Events', 'event-manager-pro' ),
		'search_items'          => __( 'Search Events', 'event-manager-pro' ),
		'not_found'             => __( 'No events found.', 'event-manager-pro' ),
		'not_found_in_trash'    => __( 'No events found in Trash.', 'event-manager-pro' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'has_archive'        => true,
		'show_in_rest'       => true, // REST API enabled
		'menu_icon'          => 'dashicons-calendar-alt',
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'rewrite'            => array( 'slug' => 'events' ),
	);

	register_post_type( 'emp_event', $args );
}
add_action( 'init', 'emp_register_event_cpt' );