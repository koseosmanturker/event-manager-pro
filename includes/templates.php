<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function emp_template_loader( $template ) {

	if ( is_singular( 'emp_event' ) ) {
		$plugin_template = EMP_PLUGIN_PATH . 'templates/single-emp_event.php';
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}
	}

	if ( is_post_type_archive( 'emp_event' ) ) {
		$plugin_template = EMP_PLUGIN_PATH . 'templates/archive-emp_event.php';
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}
	}

	return $template;
}
add_filter( 'template_include', 'emp_template_loader' );