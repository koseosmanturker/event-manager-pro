<?php
/**
 * Single Event template (plugin).
 */
get_header();

while ( have_posts() ) :
    the_post();

    $date = get_post_meta( get_the_ID(), '_emp_event_date', true );
    $loc  = get_post_meta( get_the_ID(), '_emp_event_location', true );
    ?>

    <main class="emp-wrap" style="max-width:900px;margin:30px auto;padding:0 15px;">
        <article>
            <h1><?php the_title(); ?></h1>

            <div class="emp-meta" style="margin:10px 0 20px;padding:12px;border:1px solid #ddd;">
                <?php if ( $date ) : ?>
                    <div><strong><?php echo esc_html__( 'Event Date:', 'event-manager-pro' ); ?></strong> <?php echo esc_html( $date ); ?></div>
                <?php endif; ?>

                <?php if ( $loc ) : ?>
                    <div><strong><?php echo esc_html__( 'Location:', 'event-manager-pro' ); ?></strong> <?php echo esc_html( $loc ); ?></div>
                <?php endif; ?>

                <div style="margin-top:8px;">
                    <strong><?php echo esc_html__( 'Type:', 'event-manager-pro' ); ?></strong>
                    <?php
                    $terms = get_the_terms( get_the_ID(), 'emp_event_type' );
                    if ( $terms && ! is_wp_error( $terms ) ) {
                        $names = wp_list_pluck( $terms, 'name' );
                        echo esc_html( implode( ', ', $names ) );
                    } else {
                        echo '—';
                    }
                    ?>
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
