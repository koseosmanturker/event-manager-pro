<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

function emp_enqueue_assets() {
	// Only load on event pages OR pages that use the shortcode (simple approach: load on front)
	wp_enqueue_style(
		'emp-styles',
		EMP_PLUGIN_URL . 'assets/css/emp-styles.css',
		array(),
		'1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'emp_enqueue_assets' );