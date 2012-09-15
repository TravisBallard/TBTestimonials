<?php

/**
*   widget class
*/
class TBTestimonialsWidget extends WP_Widget
{

    var $animate = false,
        $post_type = null;

    /**
    * widget construct
    *
    */
    function TBTestimonialsWidget(){
        global $tbtestimonials;
        parent::WP_Widget( false, $name = 'TB Testimonials Widget' );
        $this->post_type = $tbtestimonials->post_type;
    }

    /**
    * update settings
    *
    * @param mixed $new_instance
    * @param mixed $old_instance
    * @return array
    */
    function update( $new_instance, $old_instance )
    {
        global $tbtestimonials;

        $instance = $old_instance;
        $instance['title'] = strip_tags( esc_html( $new_instance['title'] ) );
        $instance['display_count'] = filter_var( $new_instance['display_count'], FILTER_VALIDATE_INT, array( 'default' => 3, 'min_range' => 1 ) );
        $instance['timer_interval'] = filter_var( $new_instance['timer_interval'], FILTER_VALIDATE_INT, array( 'default' => 5, 'min_range' => 1 ) );
        $instance['transition_interval'] = filter_var( $new_instance['transition_interval'], FILTER_VALIDATE_INT, array( 'default' => 1, 'min_range' => 1 ) );
        $instance['loop_all'] = filter_var( $new_instance['loop_all'], FILTER_VALIDATE_BOOLEAN, array( 'default' => 0 ) );
        $instance['order'] = esc_html( $new_instance['order'] );
        $instance['orderby'] = esc_html( $new_instance['orderby'] );
        $instance['transition'] = esc_html( $new_instance['transition'] );

        if( isset( $tbtestimonials->settings['use_template_api'] ) )
            $instance['template'] = esc_html( $new_instance['template'] );

        $x = 0;
        $categories = array();

        foreach( get_terms( 'tbtestimonial_category', array( 'hide_empty' => false) ) as $term )
        {
            if( isset( $new_instance[ 'category_' . ++$x ] ) && ! empty( $new_instance[ 'category_' . $x ] ) )
            {
                if( isset( $instance[ 'category_' . $x ] ) ) unset( $instance[ 'category_' . $x ] );
                $instance[ 'categories' ][ 'category_' . $x ] = esc_html( $new_instance[ 'category_' . $x ] );
            }
            else
                if( isset( $instance['categories'][ 'category_' . $x ] ) ) unset( $instance['categories'][ 'category_' . $x ] );
        }

        return $instance;
    }

    /**
    * widget form
    *
    * @param mixed $instance
    */
    function form( $instance )
    {
        global $tbtestimonials;

        if( ! isset( $instance['orderby'] ) || empty( $instance['orderby'] ) )
            $instance['orderby'] = 'ID';

        if( ! isset( $instance['order'] ) || empty( $instance['order'] ) )
            $instance['order'] = 'desc';

        if( ! isset( $instance['transition'] ) || empty( $instance['transition'] ) )
            $instance['transition'] = 'fade';

        if( ! isset( $instance['template'] ) || empty( $instance['template'] ) )
            $instance['template'] = 'widget';

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo isset( $instance['title'] ) ? $instance['title'] : ''; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'display_count' ); ?>"><?php _e( 'Display Count:' ); ?> <small><?php _e( '(-1 shows all testimonials)' ); ?></small>
                <input class="widefat" id="<?php echo $this->get_field_id( 'display_count' ); ?>" name="<?php echo $this->get_field_name( 'display_count' ); ?>" type="text" value="<?php echo isset( $instance['display_count'] ) ? $instance['display_count'] : 3; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'timer_interval' ); ?>"><?php _e( 'Timer Interval:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'timer_interval' ); ?>" name="<?php echo $this->get_field_name( 'timer_interval' ); ?>" type="text" value="<?php echo isset( $instance['timer_interval'] ) ? $instance['timer_interval'] : 5; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'transition_interval' ); ?>"><?php _e( 'Transition Interval:' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'transition_interval' ); ?>" name="<?php echo $this->get_field_name( 'transition_interval' ); ?>" type="text" value="<?php echo isset( $instance['transition_interval'] ) ? $instance['transition_interval'] : 1; ?>" />
            </label>
        </p>
        <?php if( isset( $tbtestimonials->settings['use_template_api'] ) ) : ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e( 'Output Template:' ); ?></label>
                <select name="<?php echo $this->get_field_name( 'template' ); ?>" id="<?php echo $this->get_field_id; ?>" class="widefat">
                    <?php foreach( $tbtestimonials->templates as $template_id => $template ) : ?>
                        <option value="<?php echo $template_id; ?>" <?php selected( $template_id, $instance['template'] ); ?> ><?php echo $template->name(); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
        <?php endif; ?>
        <p>
            <?php if( ! isset( $instance['loop_all'] ) ) $instance['loop_all'] = 'true'; ?>
            <label for="<?php echo $this->get_field_id( 'loop_all' ); ?>">
                <input id="<?php echo $this->get_field_id( 'loop_all' ); ?>" value="true" name="<?php echo $this->get_field_name( 'loop_all' ); ?>" type="checkbox"<?php checked( true, $instance['loop_all'] ); ?> />
                <?php _e( 'Loop through all testimonials' ); ?>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order By:' ); ?></label>

            <select name="<?php echo $this->get_field_name( 'orderby' ); ?>" id="<?php echo $this->get_field_id; ?>">
                <option value="author"<?php selected( 'author', $instance['orderby'] ); ?>><?php _e( 'Author' ); ?></option>
                <option value="date"<?php selected( 'date', $instance['orderby'] ); ?>><?php _e( 'Date' ); ?></option>
                <option value="title"<?php selected( 'title', $instance['orderby'] ); ?>><?php _e( 'Title' ); ?></option>
                <option value="modified"<?php selected( 'modified', $instance['orderby'] ); ?>><?php _e( 'Modified' ); ?></option>
                <option value="menu_order"<?php selected( 'menu_order', $instance['orderby'] ); ?>><?php _e( 'Menu Order' ); ?></option>
                <option value="parent"<?php selected( 'parent', $instance['orderby'] ); ?>><?php _e( 'Parent' ); ?></option>
                <option value="ID"<?php selected( 'ID', $instance['orderby'] ); ?>><?php _e( 'ID' ); ?></option>
                <option value="rand"<?php selected( 'rand', $instance['orderby'] ); ?>><?php _e( 'Random' ); ?></option>
                <option value="none"<?php selected( 'none', $instance['orderby'] ); ?>><?php _e( 'None' ); ?></option>
            </select>

            <select name="<?php echo $this->get_field_name( 'order' ); ?>" id="<?php echo $this->get_field_id; ?>">
                <option value="asc"<?php selected( 'asc', $instance['order'] ); ?>><?php _e( 'ASC' ); ?></option>
                <option value="desc"<?php selected( 'desc', $instance['order'] ); ?>><?php _e( 'DESC' ); ?></option>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'transition' ); ?>"><?php _e( 'Transition:' ); ?></label>

            <select name="<?php echo $this->get_field_name( 'transition' ); ?>" id="<?php echo $this->get_field_id; ?>">
                <option value="none"<?php selected( 'none', $instance['transition'] ); ?>><?php _e( 'None' ); ?></option>
                <option value="fade"<?php selected( 'fade', $instance['transition'] ); ?>><?php _e( 'Fade' ); ?></option>
                <option value="fadeZoom"<?php selected( 'fadeZoom', $instance['transition'] ); ?>><?php _e( 'FadeZoom' ); ?></option>
                <option value="blindX"<?php selected( 'blindX', $instance['transition'] ); ?>><?php _e( 'Blind-X' ); ?></option>
                <option value="blindY"<?php selected( 'blindY', $instance['transition'] ); ?>><?php _e( 'Blind-Y' ); ?></option>
                <option value="blindZ"<?php selected( 'blindZ', $instance['transition'] ); ?>><?php _e( 'Blind-Z' ); ?></option>
                <option value="cover"<?php selected( 'cover', $instance['transition'] ); ?>><?php _e( 'Cover' ); ?></option>
                <option value="curtainX"<?php selected( 'curtainX', $instance['transition'] ); ?>><?php _e( 'Curtain-X' ); ?></option>
                <option value="curtainY"<?php selected( 'curtainY', $instance['transition'] ); ?>><?php _e( 'Curtain-Y' ); ?></option>
                <option value="growX"<?php selected( 'growX', $instance['transition'] ); ?>><?php _e( 'Grow-X' ); ?></option>
                <option value="growY"<?php selected( 'growY', $instance['transition'] ); ?>><?php _e( 'Grow-Y' ); ?></option>
                <option value="scrollUp"<?php selected( 'scrollUp', $instance['transition'] ); ?>><?php _e( 'Scroll Up' ); ?></option>
                <option value="scrollDown"<?php selected( 'scrollDown', $instance['transition'] ); ?>><?php _e( 'Scroll Down' ); ?></option>
                <option value="scrollLeft"<?php selected( 'scrollLeft', $instance['transition'] ); ?>><?php _e( 'Scroll Left' ); ?></option>
                <option value="scrollRight"<?php selected( 'scrollRight', $instance['transition'] ); ?>><?php _e( 'Scroll Right' ); ?></option>
                <option value="scrollHorz"<?php selected( 'scrollHorz', $instance['transition'] ); ?>><?php _e( 'Scroll Horizontally' ); ?></option>
                <option value="scrollVert"<?php selected( 'scrollVert', $instance['transition'] ); ?>><?php _e( 'Scroll Vertically' ); ?></option>
                <option value="shuffle"<?php selected( 'shuffle', $instance['transition'] ); ?>><?php _e( 'Shuffle' ); ?></option>
                <option value="slideX"<?php selected( 'slideX', $instance['transition'] ); ?>><?php _e( 'Slide-X' ); ?></option>
                <option value="slideY"<?php selected( 'slideY', $instance['transition'] ); ?>><?php _e( 'Slide-Y' ); ?></option>
                <option value="toss"<?php selected( 'toss', $instance['transition'] ); ?>><?php _e( 'Toss' ); ?></option>
                <option value="turnUp"<?php selected( 'turnUp', $instance['transition'] ); ?>><?php _e( 'Turn Up' ); ?></option>
                <option value="turnDown"<?php selected( 'turnDown', $instance['transition'] ); ?>><?php _e( 'Turn Down' ); ?></option>
                <option value="turnLeft"<?php selected( 'turnLeft', $instance['transition'] ); ?>><?php _e( 'Turn Left' ); ?></option>
                <option value="turnRight"<?php selected( 'turnRight', $instance['transition'] ); ?>><?php _e( 'Turn Right' ); ?></option>
                <option value="uncover"<?php selected( 'uncover', $instance['transition'] ); ?>><?php _e( 'Uncover' ); ?></option>
                <option value="wipe"<?php selected( 'wipe', $instance['transition'] ); ?>><?php _e( 'Wipe' ); ?></option>
                <option value="zoom"<?php selected( 'zoom', $instance['transition'] ); ?>><?php _e( 'Zoom' ); ?></option>
            </select>
        </p>

        <h4>Categories to display</h4>
        <p><small>Due to current limitations only 1 category at a time is supported.</small></p>
        <?php

            $x = 0;
            $categories = isset( $instance['categories'] ) ? $instance['categories'] : array();

            foreach( get_terms( 'tbtestimonial_category', array( 'hide_empty' => false ) ) as $term ) : ++$x; ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'category_' . $x ); ?>">
                    <input id="<?php echo $this->get_field_id( 'category_' . $x ); ?>" type="checkbox" value="<?php echo $term->term_id; ?>" name="<?php echo $this->get_field_name( 'category_' . $x ); ?>"<?php echo checked( true, in_array( $term->term_id, $categories ) ); ?> />
                    <?php _e( $term->name ); ?> <small>(<?php echo $term->count; ?>)</small>
                </label>
            </p>
        <?php endforeach;
    }

    /**
    * widget
    *
    * @param mixed $args
    * @param mixed $instance
    */
    function widget( $args, $instance )
    {
        extract( $args );
        $tbtestimonials_settings = get_option( 'tbtestimonials_settings' );

        echo $before_widget;

        if( isset( $instance['title'] ) )
            printf( '%s%s%s', $before_title, esc_attr( $instance['title'] ), $after_title );

        if( intval( $instance['display_count'] ) == 0 )
            $instance['display_count'] = -1;

        if( ! isset( $instance['template'] ) || empty( $instance['template'] ) )
            $instance['template'] = 'widget';

        $testimonial_args = array(
            'post_type' => $this->post_type,
            'posts_per_page' => isset( $instance['display_count'] ) ? intval( $instance['display_count'] ) : 3,
            'post_status' => 'publish',
            'orderby' => isset( $instance['orderby'] ) ? esc_attr( $instance['orderby'] ) : 'ID',
            'order' => isset( $instance['order'] ) ? esc_attr( $instance['order'] ) : 'DESC',
        );

        # displaying category only?
        if( isset( $instance['categories'] ) && count( $instance['categories'] ) > 0 )
        {
            sort( $instance['categories'] ); # remove associative keys. category_1, category_2, etc.

            /**
            * As of right now, only one taxonomy can be queried at a time.
            * see http://core.trac.wordpress.org/ticket/12891 for more information
            *
            * so here we get the term name of the first category in the array and
            * pass that as the value for testimonial_category
            */
            $term = get_term( $instance['categories'][0], 'tbtestimonial_category' );
            $testimonial_args['testimonial_category'] = $term->name;

        }

        # loop all
        if( 1 == (int)$instance['loop_all'] ){
            $testimonial_args['posts_per_page'] = -1;
            $this->animate = true;
        }

        $testimonials = new WP_Query( $testimonial_args );

        # widget settings ?>
            <script type="text/javascript">/* <![CDATA[ */
                var tbtestimonial_settings = {
                    transition_interval:  <?php echo (int)$instance['transition_interval']; ?>,
                    timer_interval: <?php echo (int)$instance['timer_interval']; ?>,
                    display_count: <?php echo (int)$instance['display_count']; ?>,
                    loop_all:  <?php echo (int)$instance['loop_all']; ?>,
                    animate: <?php echo (int)$this->animate; ?>,
                    preloader: <?php echo (int)isset( $tbtestimonials_settings['show_loading_graphic'] ); ?>,
                    transition: '<?php echo $instance['transition']; ?>'
                };
            /*]]>*/</script>
        <?php

        if( $testimonials->have_posts() )
        {
            $tbtestimonials = new TBTestimonials();

            if( isset( $tbtestimonials_settings['show_loading_graphic'] ) ) : ?>
                <p class="ajax-loader">
                    <?php if( $tbtestimonials_settings['loading_text_position'] == 'before' ) { echo $tbtestimonials_settings['loading_text']; } ?>
                    <img src="<?php echo $tbtestimonials_settings['loading_graphic_url']; ?>" alt="loading" style="position:relative;top:2px;" />
                    <?php if( $tbtestimonials_settings['loading_text_position'] == 'after' ) { echo $tbtestimonials_settings['loading_text']; } ?>
                </p>
            <?php endif; ?>

            <ul id="tbtestimonials-widget">
            <?php

            $x = 0;
            $t = array();

            while( $testimonials->have_posts() )
            {
                $testimonials->the_post();
                if( isset( $tbtestimonials->settings['use_template_api'] ) )
                    $t[] = $tbtestimonials->prepare_testimonial( $instance['template'] );
                else
                    $t[] = $tbtestimonials->deprecated__prepare_testimonial( 'widget' );
            }

            if( intval( $instance['display_count'] ) > 0 )
                $pages = array_chunk( $t, isset( $instance['display_count'] ) ? intval( $instance['display_count'] ) : 3, true );
            else
            {
                $pages = array();
                $pages[] = $t;
            }

            $p = 0;
            foreach( $pages as $page )
            {
                $class = ++$p > 1 ? 'testimonial-slide hidden-testimonial' : 'testimonial-slide';
                printf( '<li class="%s"><ul>', $class );

                foreach( $page as $item )
                    if( ! empty( $item ) ) printf('%s', $item );

                printf( '</ul></li>' );
            }

            ?></ul><?php
        }

        wp_reset_query();
        echo $after_widget;
    }
}

# init widget
add_action( 'widgets_init', create_function( '', 'return register_widget( "TBTestimonialsWidget" );' ) );