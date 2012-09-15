<?php $tbt = new TBTestimonials(); $tbtestimonials_settings = get_option( 'tbtestimonials_settings' ); ?>
<li class="widget tbtestimonials-widget">
    <?php echo TBTestimonialsWidget::$before_title . TBTestimonialsWidget::$title . TBTestimonialsWidget::$after_title; ?>
    <?php if( isset( $tbtestimonials_settings['show_loading_graphic'] ) ) : ?>
        <p class="ajax-loader">
            <?php if( $tbtestimonials_settings['loading_text_position'] == 'before' ) { echo $tbt_settings['loading_text']; } ?>
            <img src="<?php echo $tbtestimonials_settings['loading_graphic_url']; ?>" alt="loading" style="position:relative;top:2px;" />
            <?php if( $tbtestimonials_settings['loading_text_position'] == 'after' ) { echo $tbtestimonials_settings['loading_text']; } ?>
        </p>
    <?php endif; ?>

    <div class="nojs">
        <?php
            $testimonials = new WP_Query(
                array(
                    'showposts' => TBTestimonialsWidget::$display_count,
                    'post_type' => $tbt->post_type,
                    'post_status' => 'publish'
                )
            );
            
            //printf( '<pre class="debug">%s</pre>', print_r( $testimonials, 1 ) );

            if( $testimonials->have_posts() )
            {
                echo '<ul class="testimonials">';
                while( $testimonials->have_posts() )
                {
                    $testimonials->the_post();
                    echo $tbt->prepare_testimonial( 'widget' );
                }
                echo '</ul>';
            }
            else
                printf( '<li>No Testimonials Found.</li>' );
        ?>
    </div>
    <ul class="testimonials"></ul>
</li>