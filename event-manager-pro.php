<?php
/**
 * Plugin Name:     Event Manager Pro
 * Plugin URI:      https://github.com/koseosmanturker/event-manager-pro
 * Description:     Custom event management plugin with CPT, taxonomy, RSVP system, REST API and email notifications.
 * Author:          Osman Türker Köse
 * Author URI:      https://github.com/koseosmanturker
 * Text Domain:     event-manager-pro
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package Event_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'EMP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Internationalization
 */
require_once EMP_PLUGIN_PATH . 'includes/i18n.php';

/**
 * Core structure
 */
require_once EMP_PLUGIN_PATH . 'includes/post-types.php';
require_once EMP_PLUGIN_PATH . 'includes/taxonomies.php';

/**
 * Admin features
 */
require_once EMP_PLUGIN_PATH . 'includes/meta-boxes.php';
require_once EMP_PLUGIN_PATH . 'includes/admin-columns.php';

/**
 * Frontend features
 */
require_once EMP_PLUGIN_PATH . 'includes/shortcode.php';
require_once EMP_PLUGIN_PATH . 'includes/templates.php';

/**
 * RSVP + Emails + REST
 */
require_once EMP_PLUGIN_PATH . 'includes/rsvp.php';
require_once EMP_PLUGIN_PATH . 'includes/emails.php';
require_once EMP_PLUGIN_PATH . 'includes/rest-api.php';

/**
 * Assets (CSS/JS)
 */
require_once EMP_PLUGIN_PATH . 'includes/assets.php';