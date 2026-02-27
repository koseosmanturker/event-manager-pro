<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

function emp_load_textdomain() {
	load_plugin_textdomain(
		'event-manager-pro',
		false,
		dirname( plugin_basename( EMP_PLUGIN_PATH . 'event-manager-pro.php' ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'emp_load_textdomain' );