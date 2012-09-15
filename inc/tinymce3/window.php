<?php $q = new WP_Query( array( 'post_type' => 'testimonial', 'post_status' => 'publish', 'posts_per_page' => -1 ) );  wp_enqueue_script( 'jquery' ); ?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>TBTestimonial Shortcode Generator</title>
    <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
    <?php wp_head(); ?>
    <script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
    <script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
    <script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
    <script language="javascript" type="text/javascript" src="<?php echo plugins_url( '', __FILE__ ); ?>/tinymce.js"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo plugins_url( 'window.css', __FILE__ ); ?>" />
    <base target="_self" />
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="display: none; font-size:62.5%;">
    <h3>Listing</h3>
    <p class="testimonial listing-button" onclick="insert_testimonial('all');"><strong>Insert Testimonial Listing</strong></p>

    <h3>Select a testimonial to insert</h3>
    <?php
        if( $q->have_posts() )
        { ?>
            <div class="testimonials"><?php
            while( $q->have_posts() )
            {
                $q->the_post(); ?>
                <p class="testimonial" onclick="insert_testimonial(<?php the_ID(); ?>);"><?php printf( '<strong>%s</strong>: <em>%s</em>', ucwords( strtolower( get_the_title() ) ), get_the_excerpt() ); ?></p><?php

            } ?>
            </div><?php
        }
    ?>
</body>
</html>
