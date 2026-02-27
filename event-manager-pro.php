<?php
/**
 * Plugin Name:     Event Manager Pro
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     event-manager-pro
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Event_Manager_Pro
 */

function emp_load_textdomain() {
	load_plugin_textdomain(
		'event-manager-pro',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'emp_load_textdomain' );

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

/**
 * Register Taxonomy: Event Type
 */
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

/**
 * Add meta box for Event details.
 */
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

/**
 * Render meta box fields.
 */
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

/**
 * Validate date format (YYYY-MM-DD).
 */
function emp_is_valid_date_ymd( $date ) {
	if ( empty( $date ) ) {
		return true; // empty is allowed
	}
	$d = DateTime::createFromFormat( 'Y-m-d', $date );
	return $d && $d->format( 'Y-m-d' ) === $date;
}

/**
 * Save event meta box fields securely.
 */
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

/**
 * Add custom columns to Events list table.
 */
function emp_event_columns( $columns ) {
	$columns['emp_event_date']     = __( 'Event Date', 'event-manager-pro' );
	$columns['emp_event_location'] = __( 'Location', 'event-manager-pro' );
	return $columns;
}
add_filter( 'manage_emp_event_posts_columns', 'emp_event_columns' );

/**
 * Render custom column values.
 */
function emp_render_event_columns( $column, $post_id ) {
	if ( 'emp_event_date' === $column ) {
		$date = get_post_meta( $post_id, '_emp_event_date', true );
		echo $date ? esc_html( $date ) : '—';
	}

	if ( 'emp_event_location' === $column ) {
		$loc = get_post_meta( $post_id, '_emp_event_location', true );
		echo $loc ? esc_html( $loc ) : '—';
	}
}
add_action( 'manage_emp_event_posts_custom_column', 'emp_render_event_columns', 10, 2 );



/**
 * Shortcode: [events]
 * Example:
 * [events type="conference" from="2026-01-01" to="2026-12-31" search="ai" limit="10"]
 */
function emp_events_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'type'   => '', // taxonomy slug (emp_event_type)
			'from'   => '', // YYYY-MM-DD
			'to'     => '', // YYYY-MM-DD
			'search' => '', // text search
			'limit'  => 10,
		),
		$atts,
		'events'
	);

	// Allow filters from URL query (?emp_type=...&emp_from=...&emp_to=...&emp_s=...)
	$get_type   = isset( $_GET['emp_type'] ) ? sanitize_text_field( wp_unslash( $_GET['emp_type'] ) ) : '';
	$get_from   = isset( $_GET['emp_from'] ) ? sanitize_text_field( wp_unslash( $_GET['emp_from'] ) ) : '';
	$get_to     = isset( $_GET['emp_to'] ) ? sanitize_text_field( wp_unslash( $_GET['emp_to'] ) ) : '';
	$get_search = isset( $_GET['emp_s'] ) ? sanitize_text_field( wp_unslash( $_GET['emp_s'] ) ) : '';

	// Priority: URL filters override shortcode atts
	if ( $get_type !== '' ) {
		$atts['type'] = $get_type;
	}
	if ( $get_from !== '' ) {
		$atts['from'] = $get_from;
	}
	if ( $get_to !== '' ) {
		$atts['to'] = $get_to;
	}
	if ( $get_search !== '' ) {
		$atts['search'] = $get_search;
	}

	$type   = sanitize_text_field( $atts['type'] );
	$from   = sanitize_text_field( $atts['from'] );
	$to     = sanitize_text_field( $atts['to'] );
	$search = sanitize_text_field( $atts['search'] );
	$limit  = absint( $atts['limit'] );
	if ( $limit <= 0 ) {
		$limit = 10;
	}

	$args = array(
		'post_type'      => 'emp_event',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		's'              => $search ? $search : '',
		'no_found_rows'  => true, // performance
	);

	// Taxonomy filter
	if ( $type ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'emp_event_type',
				'field'    => 'slug',
				'terms'    => $type,
			),
		);
	}

	// Date range filter (meta_query)
	$meta_query = array();

	if ( $from && emp_is_valid_date_ymd( $from ) ) {
		$meta_query[] = array(
			'key'     => '_emp_event_date',
			'value'   => $from,
			'compare' => '>=',
			'type'    => 'DATE',
		);
	}

	if ( $to && emp_is_valid_date_ymd( $to ) ) {
		$meta_query[] = array(
			'key'     => '_emp_event_date',
			'value'   => $to,
			'compare' => '<=',
			'type'    => 'DATE',
		);
	}

	// Sort by date if we are filtering by date (or if you want always, we can always sort)
	if ( ! empty( $meta_query ) ) {
		$args['meta_query'] = $meta_query;
		$args['meta_key']   = '_emp_event_date';
		$args['orderby']    = 'meta_value';
		$args['order']      = 'ASC';
	}

	// Build filter form (taxonomy dropdown)
	$terms = get_terms(
		array(
			'taxonomy'   => 'emp_event_type',
			'hide_empty' => false,
		)
	);

	$q = new WP_Query( $args );

	ob_start();

	// Filter Form (GET)
	$current_url = remove_query_arg( array( 'emp_type', 'emp_from', 'emp_to', 'emp_s' ) );

	echo '<form method="get" action="' . esc_url( $current_url ) . '" style="margin-bottom:15px;padding:12px;border:1px solid #ddd;">';

	echo '<label style="margin-right:10px;">' . esc_html__( 'Type', 'event-manager-pro' ) . ': ';
	echo '<select name="emp_type">';
	echo '<option value="">' . esc_html__( 'All', 'event-manager-pro' ) . '</option>';

	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		foreach ( $terms as $t ) {
			$selected = selected( $type, $t->slug, false );
			echo '<option value="' . esc_attr( $t->slug ) . '"' . $selected . '>' . esc_html( $t->name ) . '</option>';
		}
	}

	echo '</select></label>';

	echo '<label style="margin-right:10px;">' . esc_html__( 'From', 'event-manager-pro' ) . ': ';
	echo '<input type="date" name="emp_from" value="' . esc_attr( $from ) . '"></label>';

	echo '<label style="margin-right:10px;">' . esc_html__( 'To', 'event-manager-pro' ) . ': ';
	echo '<input type="date" name="emp_to" value="' . esc_attr( $to ) . '"></label>';

	echo '<label style="margin-right:10px;">' . esc_html__( 'Search', 'event-manager-pro' ) . ': ';
	echo '<input type="text" name="emp_s" value="' . esc_attr( $search ) . '" placeholder="' . esc_attr__( 'keyword...', 'event-manager-pro' ) . '"></label>';

	echo '<button type="submit">' . esc_html__( 'Filter', 'event-manager-pro' ) . '</button>';
	echo '</form>';

	// Results
	echo '<div class="emp-events-list">';

	if ( $q->have_posts() ) {
		echo '<ul style="padding-left:18px;">';

		while ( $q->have_posts() ) {
			$q->the_post();

			$date = get_post_meta( get_the_ID(), '_emp_event_date', true );
			$loc  = get_post_meta( get_the_ID(), '_emp_event_location', true );

			echo '<li style="margin-bottom:10px;">';
			echo '<a href="' . esc_url( get_permalink() ) . '"><strong>' . esc_html( get_the_title() ) . '</strong></a>';

			if ( $date ) {
				echo ' — <span>' . esc_html( $date ) . '</span>';
			}
			if ( $loc ) {
				echo ' — <span>' . esc_html( $loc ) . '</span>';
			}

			echo '</li>';
		}

		echo '</ul>';
	} else {
		echo '<p>' . esc_html__( 'No events found.', 'event-manager-pro' ) . '</p>';
	}

	echo '</div>';

	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'events', 'emp_events_shortcode' );

/**
 * Render RSVP form HTML.
 */
function emp_render_rsvp_form( $event_id ) {
	if ( ! $event_id ) {
		return '';
	}

	// Show a success message after redirect
	$success = isset( $_GET['emp_rsvp'] ) ? sanitize_text_field( wp_unslash( $_GET['emp_rsvp'] ) ) : '';

	ob_start();

	if ( 'success' === $success ) {
		echo '<div class="emp-rsvp-success" style="margin-top:15px;padding:10px;border:1px solid #46b450;">';
		echo esc_html__( 'Thanks! Your RSVP has been recorded.', 'event-manager-pro' );
		echo '</div>';
	}

	echo '<div class="emp-rsvp-box" style="margin-top:20px;padding:15px;border:1px solid #ddd;">';
	echo '<h3 style="margin-top:0;">' . esc_html__( 'RSVP', 'event-manager-pro' ) . '</h3>';

	echo '<form method="post">';
	wp_nonce_field( 'emp_rsvp_action', 'emp_rsvp_nonce' );

	echo '<p><label><strong>' . esc_html__( 'Your Name', 'event-manager-pro' ) . '</strong></label><br>';
	echo '<input type="text" name="emp_rsvp_name" required style="width:100%;max-width:420px;"></p>';

	echo '<p><label><strong>' . esc_html__( 'Your Email', 'event-manager-pro' ) . '</strong></label><br>';
	echo '<input type="email" name="emp_rsvp_email" required style="width:100%;max-width:420px;"></p>';

	echo '<input type="hidden" name="emp_event_id" value="' . esc_attr( $event_id ) . '">';

	echo '<p><button type="submit" name="emp_rsvp_submit" value="1">' . esc_html__( 'Confirm Attendance', 'event-manager-pro' ) . '</button></p>';
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
			// Already RSVP'd - redirect as success to avoid leaking info
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
	emp_send_rsvp_emails( $event_id, $name, $email );

	// Redirect (prevents resubmission on refresh)
	wp_safe_redirect( add_query_arg( 'emp_rsvp', 'success', get_permalink( $event_id ) ) );
	exit;
}
add_action( 'template_redirect', 'emp_handle_rsvp_submission' );

/**
 * Send RSVP emails (to user + admin).
 */
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

/**
 * Expose event meta fields in REST API.
 */
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

/**
 * Load plugin templates for single/archive of emp_event.
 */
function emp_template_loader( $template ) {
	if ( is_singular( 'emp_event' ) ) {
		$plugin_template = plugin_dir_path( __FILE__ ) . 'templates/single-emp_event.php';
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}
	}

	if ( is_post_type_archive( 'emp_event' ) ) {
		$plugin_template = plugin_dir_path( __FILE__ ) . 'templates/archive-emp_event.php';
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}
	}

	return $template;
}
add_filter( 'template_include', 'emp_template_loader' );