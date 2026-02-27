<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

function emp_register_event_type_taxonomy() {
	$labels = array(
		'name'              => __( 'Event Types', 'event-manager-pro' ),
		'singular_name'     => __( 'Event Type', 'event-manager-pro' ),
		'search_items'      => __( 'Search Event Types', 'event-manager-pro' ),
		'all_items'         => __( 'All Event Types', 'event-manager-pro' ),
		'edit_item'         => __( 'Edit Event Type', 'event-manager-pro' ),
		'update_item'       => __( 'Update Event Type', 'event-manager-pro' ),
		'add_new_item'      => __( 'Add New Event Type', 'event-manager-pro' ),
		'new_item_name'     => __( 'New Event Type Name', 'event-manager-pro' ),
		'menu_name'         => __( 'Event Types', 'event-manager-pro' ),
	);

	$args = array(
		'hierarchical'      => true, // category-like
		'labels'            => $labels,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'event-type' ),
	);

	register_taxonomy( 'emp_event_type', array( 'emp_event' ), $args );
}
add_action( 'init', 'emp_register_event_type_taxonomy' );