<?php
/**
 * Archive Events template (plugin).
 */
get_header();
?>

<main class="emp-wrap" style="max-width:900px;margin:30px auto;padding:0 15px;">
	<h1><?php echo esc_html__( 'Events', 'event-manager-pro' ); ?></h1>

	<div style="margin:15px 0;">
		<?php
		if ( shortcode_exists( 'events' ) ) {
			echo do_shortcode( '[events limit="20"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
	</div>
</main>

<?php
get_footer();