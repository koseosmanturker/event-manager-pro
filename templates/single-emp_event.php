<?php
/**
 * Single Event template (plugin).
 */

get_header();

while ( have_posts() ) :
    the_post();

    $date = get_post_meta( get_the_ID(), '_emp_event_date', true );
    $loc  = get_post_meta( get_the_ID(), '_emp_event_location', true );

    $formatted_date = '';
    if ( $date ) {
	    $ts = strtotime( $date );
	    if ( $ts ) {
		// "XX Month XXXX" format, localized
		    $formatted_date = date_i18n( 'j F Y', $ts );
	    }
    }
    
    ?>

    <main class="emp-wrap" style="max-width:900px;margin:30px auto;padding:0 15px;">
        <article>
            <h1><?php the_title(); ?></h1>

            <div class="emp-card emp-meta-grid">
                <?php if ( $formatted_date ) : ?>
                    <div class="emp-meta-item">
                        <div class="emp-meta-k">📅 <?php echo esc_html__( 'Event Date', 'event-manager-pro' ); ?></div>
                        <div class="emp-meta-v"><?php echo esc_html( $formatted_date ); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ( $loc ) : ?>
                    <div class="emp-meta-item">
                        <div class="emp-meta-k">📍 <?php echo esc_html__( 'Location', 'event-manager-pro' ); ?></div>
                        <div class="emp-meta-v"><?php echo esc_html( $loc ); ?></div>
                    </div>
                <?php endif; ?>

                <?php
                $terms      = get_the_terms( get_the_ID(), 'emp_event_type' );
                $type_label = '—';
                if ( $terms && ! is_wp_error( $terms ) ) {
                    $names      = wp_list_pluck( $terms, 'name' );
                    $type_label = implode( ', ', $names );
                }
                ?>
                <div class="emp-meta-item">
                    <div class="emp-meta-k">🏷️ <?php echo esc_html__( 'Event Type', 'event-manager-pro' ); ?></div>
                    <div class="emp-meta-v"><?php echo esc_html( $type_label ); ?></div>
                </div>
            </div>

            <div class="emp-content">
                <?php the_content(); ?>
            </div>

            <?php
            if ( function_exists( 'emp_render_rsvp_form' ) ) {
                echo emp_render_rsvp_form( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
        </article>
    </main>

<?php
endwhile;

get_footer();
