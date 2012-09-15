<?php
/**
*   Plugin Name: TB Testimonials
*   Plugin URI: http://travisballard.com/wordpress/tb-testimonials/
*   Description: Testimonials managed by Custom Post Types. Supports a testimonial.php template file for single testimonial pages. Testimonial Shortcode to insert testimonials in any post. Scrolling Sidebar Widget
*   Version: 1.6.1
*   Author: Travis Ballard
*   Author URI: http://www.travisballard.com
*
*   Copyright 2010 Travis Ballard
*/
/*******************************************************************************
*
*   This program is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 2 of the License, or
*   (at your option) any later version.

*   This program is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*   GNU General Public License for more details.

*   You should have received a copy of the GNU General Public License
*   along with this program; if not, write to the Free Software
*   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

require_once( 'inc/twig/lib/Twig/Autoloader.php' );
require_once( 'inc/TBTagFunction.class.php' );
require_once( 'inc/TestimonialOutputTemplate.class.php' );

class TBTestimonials
{
    public  $settings,
            $templates,
            $twig,
            $load_codemirror_js = false,
            $text_domain = 'tb_testimonials',
            $css_path = 'inc/css/',
            $js_path = 'inc/js/',
            $post_type = 'testimonial',
            $load_js_in_footer = false;

    /**
    * constructor
    *
    */
    function __construct()
    {
        # settings
        add_action( 'init', array( &$this, 'init' ) );
        add_action( 'admin_init', array( &$this, 'register_settings' ) );
        $this->settings = tbtestimonials_settings();

        # twig
        Twig_Autoloader::register();
        $this->twig = new Twig_Environment( new Twig_Loader_String(), array( 'autoescape' => false ) );

        # templates
        $this->get_templates();

        # load javascript in head or footer.
        if( array_key_exists( 'js_in_footer', $this->settings ) && $this->settings['js_in_footer'] == 1 )
            $this->load_js_in_footer = true;

        # enable post thumbnails
        add_theme_support( 'post-thumbnails' );

        # change post title box text
        add_action( 'gettext', array( &$this, 'change_title_text' ) );

        # check for quote bug where we had <div class="testimonial-data""> with the extra " in the deffault output.
        # exists in versions installed prior to version 1.4
        if( $this->quote_bug_exists() ) $this->fix_quote_bug();

        # update messages
        if( is_admin() ) add_filter( 'post_updated_messages', array( &$this, 'post_update_messages' ) );

        # add in a testimonial template for people that want to link to them on their own pages
        if( ! is_admin() ) add_action( 'template_redirect', array( &$this, 'maybe_load_testimonial_template' ) );

        # meta boxes
        if( is_admin() ) add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );

        # save meta box data
        add_action( 'save_post', array( &$this, 'save_postdata' ) );

        # menu items
        add_action( 'admin_menu', array( &$this, 'add_menus' ) );

        # shortcodes
        add_action('wp_ajax_tbtestimonials-shortcode', array( &$this, 'shortcode_window' ) );
        add_shortcode( 'testimonial', array( &$this, 'shortcode' ) );
        add_shortcode( 'testimonial_form', array( &$this, 'testimonial_form' ) );

        # image sizes
        add_image_size( 'tbtestimonial_admin_thumbnail', 40, 40, 1 );
        add_image_size( 'tbtestimonial_thumbnail', $this->settings['gravatar_size'], $this->settings['gravatar_size'], 1 );

        # listing formatting. - Special thanks to Mark Haller @ LogicSpot.com for this
        add_filter( 'manage_edit-testimonial_columns', array( &$this, 'testimonial_listing_edit_columns' ) );
        add_action( 'manage_pages_custom_column', array( &$this, 'testimonial_listing_columns' ) );

        /**
        *   nag about new template api
        */
        if( ! isset( $this->settings['use_template_api'] ) && ! get_option( 'tbt_template_nag_shown' ) )
        {
            add_action( 'admin_notices', array( &$this, 'template_api_nag' ) );
            add_option( 'tbt_template_nag_shown', time() );
        }
        else if( ! isset( $this->settings['use_template_api'] ) && $time = get_option( 'tbt_template_nag_shown' ) )
        {
            if( $time <= strtotime( '-1 week' ) ){
                add_action( 'admin_notices', array( &$this, 'template_api_nag' ) );
                update_option( 'tbt_template_nag_shown', time() );
            }
        }
    }

    public function template_api_nag(){
        printf( '<div class="updated fade"><p>There is a new Template API available in TBTestimonials. You should switch to it before the next update cause it\'s totally worth it and you\'ll love it. <a href="%s">Let\'s do it!</a></p></div>', admin_url( 'edit.php?post_type=testimonial&page=tbtestimonials-settings' ) );
    }

    /**
    * init funcitons. register scripts, styles, taxonomies and post types
    *
    */
    function init()
    {
        $this->register_scripts();
        $this->register_styles();

        # add testimonial post type
        register_post_type(
            $this->post_type,
            array(
                'labels' => array(
                    'name' => 'Testimonials',
                    'singular_name' => 'Testimonial',
                    'add_new' => 'Add A Testimonial',
                    'add_new_item' => 'Add A Testimonial',
                    'edit_item' => 'Edit Testimonial',
                    'new_item' => 'New Testimonial',
                    'view_item' => 'View Testimonial',
                    'search_items' => 'Search Testimonials',
                    'not_found' => 'No testimonials were found.',
                    'not_found_in_trash' => 'No testimonials found in Trash.',
                    'parent_item_colon' => ''
                ),
                'public' => 1,
                'show_ui' => 1,
                'publicly_queryable' => 1,
                'query_var' => 1,
                'rewrite' => 1,
                'show_in_menu' => true,
                'capability_type' => 'post',
                'hierarchical' => 1,
                'supports' => array(
                    'title',
                    'editor',
                    'thumbnail',
                    'excerpt',
                    'page-attributes'
                )
            )
        );

        # testimonial category taxonomy
        register_taxonomy(
            'tbtestimonial_category',
            array( 'testimonial' ),
            array(
                'hierarchical' => true,
                'show_ui' => true,
                'query_var' => 'testimonial_category',
                'rewrite' => array( 'slug' => __( 'testimonial-category', $this->text_domain ) ),
                'labels' => array(
                    'name' => __( 'Categories', $this->text_domain ),
                    'singular_name' => __( 'Category', $this->text_domain ),
                    'search_items' => __( 'Search Categories', $this->text_domain ),
                    'all_items' => __( 'All Categories', $this->text_domain ),
                    'parent_item' => __( 'Parent Category', $this->text_domain ),
                    'parent_item_colon' => null,
                    'edit_item' => __( 'Edit Category', $this->text_domain ),
                    'update_item' => __( 'Update Category', $this->text_domain ),
                    'add_new_item' => __( 'Add New Category', $this->text_domain ),
                    'new_item_name' => __( 'New Category Name', $this->text_domain )
                )
            )
        );
    }

    /**
    * add settings page to menu
    *
    */
    function add_menus()
    {
        add_submenu_page( 'edit.php?post_type=testimonial', 'TBTestimonials Settings', 'General Settings', 'manage_options', 'tbtestimonials-settings', array( &$this, 'settings_page' ) );
        $output_syntax_page = add_submenu_page( 'edit.php?post_type=testimonial', 'TBTestimonials Output Syntax Settings', 'Output Settings', 'manage_options', 'tbtestimonials-syntax-settings', array( &$this, 'syntax_page' ) );
        $documentation_page = add_submenu_page( 'edit.php?post_type=testimonial', 'TBTestimonials Documentation', 'Documentation', 'manage_options', 'tbtestimonials-documentation', array( &$this, 'documentation_page' ) );
        add_action( 'admin_print_scripts-' . $output_syntax_page, create_function( '', 'wp_enqueue_script("CodeMirror");') );
        add_action( 'admin_print_scripts-' . $documentation_page, array( &$this, 'load_documentation_scripts' ) );
        add_action( 'admin_print_scripts-' . $output_syntax_page, array( &$this, 'load_documentation_scripts' ) );
        add_action( 'admin_print_styles-' . $output_syntax_page, array( &$this, 'load_documentation_styles' ) );
        add_action( 'admin_print_styles-' . $documentation_page, array( &$this, 'load_documentation_styles' ) );
    }

    /**
    * update messages for custom post type
    *
    * @param mixed $m
    */
    function post_update_messages( $m )
    {
        global $post;

        $m['testimonial'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf( 'Testimonial updated. <a href="%s">View testimonial</a>', esc_url( get_permalink( $post->ID ) ) ),
            2 => 'Custom field updated.',
            3 => 'Custom field deleted.',
            4 => 'Testimonial updated.',
            /* translators: %s: date and time of the revision */
            5 => isset( $_GET['revision'] ) ? sprintf( 'Testimonial restored to revision from %s', wp_post_revision_title( (int)$_GET['revision'], false ) ) : false,
            6 => sprintf( 'Testimonial published. <a href="%s">View testimonial</a>', esc_url( get_permalink( $post->ID ) ) ),
            7 => 'Testimonial saved.',
            8 => sprintf( 'Testimonial submitted. <a target="_blank" href="%s">Preview testimonial</a>', esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) ),
            9 => sprintf( 'Testimonial scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview testimonial</a>', date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
            10 => sprintf( 'Testimonial draft updated. <a target="_blank" href="%s">Preview testimonial</a>', esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) )
        );

        return $m;
    }

    /**
    * enqueue scripts for documentation page
    *
    */
    function load_documentation_scripts(){
        wp_enqueue_script( 'tbt_documentation' );
    }

    /**
    * enqueue styles for documentation page
    *
    */
    function load_documentation_styles(){
        wp_enqueue_style( 'tbt_documentation' );
    }

    /**
    * add meta boxes to testimonial post types
    *
    */
    function add_meta_boxes()
    {
        if( function_exists( 'add_meta_box' ) ){
            add_meta_box( 'tbtestimonial-company', 'Company Information', array( &$this, 'company_info' ), 'testimonial', 'normal', 'low' );
        }
    }

    /**
    * company info meta box
    *
    * @param mixed $post
    */
    function company_info( $post )
    {
        ?><p><?php wp_nonce_field( plugin_basename( __FILE__ ), 'tbtestimonial_nonce' ); ?><label for="testimonial_company">Company Name</label><input style="width:100%" type="text" name="testimonial_company" id="testimonial_company" value="<?php echo get_post_meta( $post->ID, 'tbtestimonial_company', 1 ); ?>" /></p>
        <p><label for="testimonial_company_url">Company URL</label><input style="width:100%" type="text" name="testimonial_company_url" id="testimonial_company_url" value="<?php echo get_post_meta( $post->ID, 'tbtestimonial_company_url', 1 ); ?>" /></p>
        <p><label for="testimonial_company_email">Email Address (<small>For Gravatar</small>)</label><input style="width:100%" type="text" name="testimonial_company_email" id="testimonial_company_email" value="<?php echo get_post_meta( $post->ID, 'tbtestimonial_company_email', 1 ); ?>" /></p><?php
    }

    /**
    * save post data from author meta box
    *
    * @param mixed $id
    */
    function save_postdata( $id )
    {
        if( ! isset( $_POST['tbtestimonial_nonce'] ) || ! wp_verify_nonce( $_POST['tbtestimonial_nonce'], plugin_basename( __FILE__ ) ) )
            return $id;

        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $id;

        if( 'testimonial' == $_POST['post_type'] )
        {
            if( ! current_user_can( 'edit_page', $id ) )
                return $id;

            if( isset( $_POST['testimonial_company_email'] ) && ! empty( $_POST['testimonial_company_email'] ) )
                update_post_meta( $id, 'tbtestimonial_company_email', strip_tags( $_POST['testimonial_company_email'] ) );
            else
                if( get_post_meta( $id, 'tbtestimonial_company_email', 1 ) )
                    delete_post_meta( $id, 'tbtestimonial_company_email' );

            if( isset( $_POST['testimonial_company_url'] ) && ! empty( $_POST['testimonial_company_url'] ) )
                update_post_meta( $id, 'tbtestimonial_company_url', strip_tags( $_POST['testimonial_company_url'] ) );
            else
                if( get_post_meta( $id, 'tbtestimonial_company_url', 1 ) )
                    delete_post_meta( $id, 'tbtestimonial_company_url' );

            if( isset( $_POST['testimonial_company'] ) && ! empty( $_POST['testimonial_company'] ) )
                update_post_meta( $id, 'tbtestimonial_company', strip_tags( $_POST['testimonial_company'] ) );
            else
                if( get_post_meta( $id, 'tbtestimonial_company', 1 ) )
                    delete_post_meta( $id, 'tbtestimonial_company' );
        }
    }

    /**
    * register settings
    *
    */
    function register_settings()
    {
        register_setting( 'tbtestimonials_settings', 'tbtestimonials_settings' );
        register_setting( 'tbtestimonials_output_settings', 'tbtestimonials_output_settings' );
    }

    /**
    * add testimonial.php template file for single pages
    *
    */
    function maybe_load_testimonial_template()
    {
        global $wp_query;
        $post_type = $wp_query->query_vars['post_type'];
        if( $post_type == 'testimonial' )
        {
            if( ! have_posts() )
            {
                # no posts found, show 404 if found or just return false if no 404 template
                if ( $template = get_404_template() )
                {
                    $wp_query->set_404();
                    status_header( 404 );
                    include( $template );
                    exit;
                }
                return;
            }

            $posts = $wp_query->posts;
            $ID = $posts[0]->ID;
            $testimonial = get_posts( array( 'id' => $ID, 'post_type' => 'testimonial' ) );
            if ( ! $testimonial )
            {
                if ( $template = get_404_template() )
                {
                    $wp_query->set_404();
                    status_header( 404 );
                    include( $template );
                    exit;
                }
                else if ( file_exists( TEMPLATEPATH . "/index.php" ) )
                {
                    include( TEMPLATEPATH . "/index.php" );
                    exit;
                }
            }

            if ( $template = $this->get_testimonial_template() )
            {
                include( $template );
                exit;
            }
            else if ( file_exists( TEMPLATEPATH . "/index.php" ) )
            {
                include( TEMPLATEPATH . "/index.php" );
                exit;
            }
        }
    }

    /**
    * get testimonial template
    */
    function get_testimonial_template()
    {
        return get_query_template( 'testimonial' );
    }

    /**
    * load ajax popup window for shortcode.
    */
    function shortcode_window()
    {
        require_once( dirname( __FILE__ ) . '/inc/tinymce3/window.php' );
        die();
    }

    /**
    * handle shortcode
    *
    * @param mixed $atts
    * @return string
    */
    function shortcode( $atts )
    {
        extract(
            shortcode_atts(
                array(
                    'id' => null,
                    'cat' => null,
                    'template' => null,
                    'orderby' => 'menu_order',
                    'order' => 'asc'
                ),
                $atts
            )
        );
        if( ! is_null( $id ) && is_null( $cat ) )
        {
            if( strtolower( $id ) == 'all' ) # all testimonials
            {
                $q = new WP_Query( array( 'post_type' => 'testimonial', 'post_status' => 'publish', 'posts_per_page' => '-1', 'orderby' => $orderby, 'order' => $order ) );

                if( $q->have_posts() )
                {
                    $return = '<div id="tbtestimonial-listing">';
                    while( $q->have_posts() ){
                        $q->the_post();
                        isset( $this->settings['use_template_api'] ) ?
                            $return .= $this->prepare_testimonial( is_null( $template ) ? 'listing' : $template ) :
                            $return .= $this->deprecated__prepare_testimonial( 'shortcode-all' );
                    }
                    $return .= '</div>';
                }
                else{
                    wp_reset_query();
                    return;
                }

                wp_reset_query();
                return $return;
            }
            elseif( strtolower( $id ) == 'random' || strtolower( $id ) == 'rand' ) # random testimonial
            {
                $q = new WP_Query( array( 'post_type' => 'testimonial', 'post_status' => 'publish', 'orderby' => 'rand', 'posts_per_page' => 1 ) );

                if( $q->have_posts() )
                {
                    $return = '<div id="tbtestimonial-listing">';
                    while( $q->have_posts() )
                    {
                        $q->the_post();
                        isset( $this->settings['use_template_api'] ) ?
                            $return .= $this->prepare_testimonial( is_null( $template ) ? 'listing' : $template ) :
                            $return .= $this->deprecated__prepare_testimonial( 'shortcode-all' );
                    }
                    wp_reset_query();
                    return $return . '</div>';
                }
                else
                    return;
            }
            else # single testimonial
            {
                if( ! is_numeric( $id ) ) return;

                $q = new WP_Query( array( 'p' => $id, 'post_type' => 'testimonial', 'post_status' => 'publish' ) );
                if( $q->have_posts() )
                {
                    $return = '';
                    while( $q->have_posts() )
                    {
                        $q->the_post();
                        return isset( $this->settings['use_template_api'] ) ?
                            $this->prepare_testimonial( is_null( $template ) ? 'shortcode' : $template ) :
                            $this->deprecated__prepare_testimonial( 'shortcode-single' );
                    }
                }
                else
                    return;
            }
        }
        else if( ! is_null( $id ) && ! is_null( $cat ) && strtolower( $id ) == 'random' || strtolower( $id ) == 'rand' ) # random from category
        {
            $q = new WP_Query( array(
                'post_type' => 'testimonial',
                'post_status' => 'publish',
                'orderby' => 'rand',
                'posts_per_page' => 1,
                'testimonial_category' => $cat
            ) );

            if( $q->have_posts() )
            {
                $return = '<div id="tbtestimonial-listing">';
                while( $q->have_posts() )
                {
                    $q->the_post();
                    isset( $this->settings['use_template_api'] ) ?
                        $return .= $this->prepare_testimonial( is_null( $template ) ? 'listing' : $template ) :
                        $return .= $this->deprecated__prepare_testimonial( 'shortcode-all' );
                }
                wp_reset_query();
                return $return . '</div>';
            }
            else
                return;
        }
        else if( is_null( $id ) && ! is_null( $cat ) ) # category listing
        {
            $q = new WP_Query( array(
                'post_type' => 'testimonial',
                'post_status' => 'publish',
                'posts_per_page' => '-1',
                'testimonial_category' => $cat,
                'orderby' => $orderby,
                'order' => $order
            ) );

            if( $q->have_posts() )
            {
                $return = '<div id="tbtestimonial-listing">';
                while( $q->have_posts() )
                {
                    $q->the_post();
                    isset( $this->settings['use_template_api'] ) ?
                        $return .= $this->prepare_testimonial( is_null( $template ) ? 'listing' : $template ) :
                        $return .= $this->deprecated__prepare_testimonial( 'shortcode-all' );
                }
                wp_reset_query();
                return $return . '</div>';
            }
            else
                return;
        }
        else
            return $this->shortcode( array( 'id' => 'random' ) );
    }

    /**
    * load settings page
    *
    */
    function settings_page()
    {
        tbtestimonials_load_template( 'settings' );
    }

    /**
    * load documentation page
    *
    */
    function documentation_page(){
        tbtestimonials_load_template( 'documentation' );
    }

    /**
    * load syntax settings page
    *
    */
    function syntax_page()
    {
        if( ! isset( $this->settings['use_template_api'] ) )
            tbtestimonials_load_template( 'syntax-settings' );
        else
            tbtestimonials_load_template( 'syntax-settings-api' );
    }

    /**
    * return prepared testimonial syntax. must be within loop.
    *
    * @param mixed $template
    * @return mixed
    */
    function prepare_testimonial( $template = 'widget' )
    {
        if( array_key_exists( sanitize_title( $template ), $this->templates ) )
        {
            $template = $this->templates[ sanitize_title( $template ) ];

            # set thumbnail to post thumbnail or gravatar
            if( ! function_exists( 'has_post_thumbnail' ) )
                require( ABSPATH . WPINC . '/post-thumbnail-template.php' );

            if( ! has_post_thumbnail() )
            {
                $thumbnail = get_post_meta( get_the_ID(), 'tbtestimonial_company_email', 1 ) ?
                    get_avatar( get_post_meta( get_the_ID(), 'tbtestimonial_company_email', 1 ), $this->settings['gravatar_size'] ) :
                    get_avatar( 'unknown', $this->settings['gravatar_size'] );
            }
            else
                $thumbnail = get_the_post_thumbnail( null, apply_filters( 'tbtestimonials_thumbnail_size', 'tbtestimonial_thumbnail' ) );

            $thumbnail = apply_filters( 'tbtestimonials_testimonial_thumbnail', $thumbnail );

            # tags
            $tags = array(
                'permalink',
                'gravatar',
                'testimonial',
                'author_prefix',
                'author',
                'company_url',
                'company_name',
                'testimonial_excerpt'
            );

            $replacements = array(
                get_permalink(),
                $thumbnail,
                apply_filters( 'the_content', get_the_content() ),
                $this->settings['author_prefix'],
                get_the_title(),
                get_post_meta( get_the_ID(), 'tbtestimonial_company_url', 1 ),
                get_post_meta( get_the_ID(), 'tbtestimonial_company', 1 ),
                get_the_excerpt()
            );

            $tags = apply_filters( 'tbtestimonials_template_tags', $tags );
            $replacements = apply_filters( 'tbtestimonials_template_tag_replacements', $replacements );

            do_action( 'tbt_template_functions', $this->twig );

            $twig_options = array();
            foreach( $tags as $key => $tag )
                $twig_options[$tag] = $replacements[ $key ];

            return $this->twig->render( $template->get(), $twig_options );
        }
    }

    /**
    * old prepare_testimonial method
    * @deprecated as of 1.6.0
    *
    * @param mixed $type
    * @return mixed
    */
    public function deprecated__prepare_testimonial( $type = 'widget' )
    {
        # post thumbnail or gravatar
        if( ! function_exists( 'has_post_thumbnail' ) )
            require( ABSPATH . WPINC . '/post-thumbnail-template.php' );

        if( ! has_post_thumbnail() )
        {
            $thumbnail = get_post_meta( get_the_ID(), 'tbtestimonial_company_email', 1 ) ?
                get_avatar( get_post_meta( get_the_ID(), 'tbtestimonial_company_email', 1 ), $this->settings['gravatar_size'] ) :
                get_avatar( 'unknown', $this->settings['gravatar_size'] );
        }
        else
            $thumbnail = get_the_post_thumbnail( null, apply_filters( 'tbtestimonials_thumbnail_size', 'tbtestimonial_thumbnail' ) );

        $thumbnail = apply_filters( 'tbtestimonials_testimonial_thumbnail', $thumbnail );

        # tags and replacements
        $tags = array( '%permalink%', '%gravatar%', '%testimonial%', '%author_prefix%', '%author%', '%company_url%', '%company_name%', '%testimonial_excerpt%' );
        $replacements = array(
            get_permalink(),
            $thumbnail,
            apply_filters( 'the_content', get_the_content() ),
            $this->settings['author_prefix'],
            get_the_title(),
            get_post_meta( get_the_ID(), 'tbtestimonial_company_url', 1 ),
            get_post_meta( get_the_ID(), 'tbtestimonial_company', 1 ),
            get_the_excerpt()
        );

        $tags = apply_filters( 'tbtestimonials_template_tags', $tags );
        $replacements = apply_filters( 'tbtestimonials_template_tag_replacements', $replacements );

        switch( $type )
        {
            case 'widget' : $syntax = apply_filters( 'tbtestimonials_widget_syntax', $this->settings['testimonial_syntax'] ); break;
            case 'shortcode-single' : $syntax = apply_filters( 'tbtestimonials_single_syntax', $this->settings['testimonial_syntax_shortcode'] ); break;
            case 'shortcode-all' : $syntax = apply_filters( 'tbtestimonials_listing_syntax', $this->settings['testimonial_syntax_listing'] ); break;
            default : $syntax = apply_filters( 'tbtestimonials_single_syntax', $this->settings['testimonial_syntax_shortcode'] ); break;
        }

        $return = str_replace( $tags, $replacements, $syntax );


        # check for user defined conditionals
        if( preg_match_all( '/%if (.+?)%(.+?)??(?:%else%(.+?)??)?%endif%/sim', $return, $matches ) )
        {
            foreach( $matches[0] as $key => $pattern )
            {
                switch( trim( $matches[1][$key] ) )
                {
                    case 'permalink' : $variable = get_permalink(); break;
                    case 'gravatar' : $variable = get_post_meta( get_the_ID(), 'tbtestimonial_company_email', 1 ) ? get_avatar( get_post_meta( get_the_ID(), 'tbtestimonial_company_email', 1 ), $this->settings['gravatar_size'] ) : false; break;
                    case 'testimonial' : $variable = apply_filters( 'the_content', get_the_content() ); break;
                    case 'author_prefix' : $variable = $this->settings['author_prefix']; break;
                    case 'author' : $variable = get_the_title(); break;
                    case 'company_url' : $variable = get_post_meta( get_the_ID(), 'tbtestimonial_company_url', 1 ); break;
                    case 'company_name' : $variable = get_post_meta( get_the_ID(), 'tbtestimonial_company', 1 ); break;
                    case 'testimonial_excerpt' : $variable = get_the_excerpt(); break;
                    default: $variable = false;
                }

                # if var is set, output
                if( isset( $variable ) && ! empty( $variable ) && false !== $variable )
                    $return = str_replace( $pattern, trim( $matches[2][$key] ), $return );
                else # output else section if it exists
                {
                    if( isset( $matches[3][$key] ) && ! empty( $matches[3][$key] ) ) $return = str_replace( $pattern, $matches[3][$key], $return );
                    else $return = str_replace( $pattern, '', $return );
                }
            }
        }

        return $return;
    }

    /**
    * get add testimonial form
    *
    * @param mixed $atts
    */
    function testimonial_form( $atts )
    {
        extract( shortcode_atts( array(), $atts ) );
        return $this->settings['add_testimonial_form'];
    }

    /**
    * add codemirror to settings page
    *
    */
    function add_codemirror()
    {
        $js_url = plugins_url( 'inc/js/', __FILE__ );
        $css_url = plugins_url( 'inc/css/', __FILE__ );
        $parser = '["parsexml.js"]';
        $styles = '["'.$css_url.'xmlcolors.css"]';

        if( ! isset( $this->settings['use_template_api'] ) )
        {
            $templates = array(
                'testimonial-syntax',
                'testimonial-syntax-shortcode',
                'testimonial-syntax-listing'
            );
        }
        else
        {
            $templates = array();
            foreach( $this->templates as $obj )
                $templates[] = sanitize_title( $obj->name() );

            $templates[] = 'new-template-syntax';
        }

        ?>
            <script type="text/javascript">
                <?php $x = 0; foreach( $templates as $template ) : ?>
                    var editor<?php echo ++$x; ?> = CodeMirror.fromTextArea( '<?php echo $template; ?>', {
                      height: "210px",
                      parserfile: <?php echo $parser; ?>,
                      stylesheet: <?php echo $styles; ?>,
                      path:"<?php echo $js_url; ?>",
                      continuousScanning: 500
                   });
               <?php endforeach; ?>
            </script>
        <?php
    }

    /**
    * check for a bug in earlier versions that had a double quote in the output
    *
    */
    function quote_bug_exists()
    {
        $bug = false;
        if( preg_match( '/testimonial-data"">/si', $this->settings['testimonial_syntax'] ) ) $bug = true;
        if( preg_match( '/testimonial-data"">/si', $this->settings['testimonial_syntax_shortcode'] ) ) $bug = true;
        if( preg_match( '/testimonial-data"">/si', $this->settings['testimonial_syntax_listing'] ) ) $bug = true;
        return $bug;
    }

    /**
    * fix double quote bug. we do it this way as to not overwrite any changes made by existing users
    *
    */
    function fix_quote_bug()
    {
        $new = array();
        $new['testimonial_syntax'] = preg_replace( 'testimonial-data""', 'testimonial-data"', $this->settings['testimonial_syntax'] );
        $new['testimonial_syntax_shortcode'] = preg_replace( 'testimonial-data""', 'testimonial-data"', $this->settings['testimonial_syntax_shortcode'] );
        $new['testimonial_syntax_listing'] = preg_replace( 'testimonial-data""', 'testimonial-data"', $this->settings['testimonial_syntax_listing'] );
        update_option( 'tbtestimonials_output_settings', $new );
        $this->settings = tbtestimonials_settings();
    }

    /**
    * add columns to listing table
    *
    * @param mixed $columns
    */
    function testimonial_listing_edit_columns( $columns )
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'tbtgravatar' => 'Gravatar',
            'title' => 'Author Name',
            'tbtdescription' => 'Excerpt',
            'tbtcompany' => 'Company',
            'tbtcompany_url' => 'Company URL',
        );

        return $columns;
    }

    /**
    * content for new columns
    *
    * @param mixed $column
    */
    function testimonial_listing_columns( $column )
    {
        global $post;
        $custom = get_post_custom( $post->ID );

        if( ! function_exists( 'has_post_thumbnail' ) )
            require( ABSPATH . WPINC . '/post-thumbnail-template.php' );

        switch ( $column )
        {
            case "tbtdescription" : the_excerpt(); break;

            case "tbtcompany" :
                    echo isset( $custom["tbtestimonial_company"][0] ) ? $custom["tbtestimonial_company"][0] : '';
                    break;

            case "tbtcompany_url" :
                    echo isset( $custom["tbtestimonial_company_url"][0] ) ? $custom["tbtestimonial_company_url"][0] : '';
                    break;

            case "tbtgravatar" :
                    if( ! get_the_post_thumbnail( $post->ID ) )
                        echo isset( $custom["tbtestimonial_company_email"][0] ) ? get_avatar( $custom["tbtestimonial_company_email"][0], 40 ) : '';
                    else
                        the_post_thumbnail( 'tbtestimonial_admin_thumbnail' );

                    break;

            case 'tbttitle' : the_title(); break;
        }
    }


    /**
    * edit 'Enter title here' text on post screen
    *
    * @param mixed $translation
    * @param mixed $text
    * @param mixed $domain
    */
    function change_title_text( $translation )
    {
        global $post;
        if( isset( $post ) )
        {
            switch( $post->post_type ){
                case 'testimonial' :
                    if( $translation == 'Enter title here' )
                        return 'Testimonial Author';
                    break;
            }
        }

        return $translation;
    }

    /**
    * register scripts used in plugin
    *
    */
    function register_scripts()
    {
        global $pagenow;

        # admin js
        $pages = array( 'edit.php', 'post-new.php', 'post.php' );
        if( in_array( $pagenow, $pages ) ){
            wp_enqueue_script( 'tbtestimonials_admin', plugins_url( 'inc/js/tbtestimonials.admin.js', __FILE__ ), array( 'jquery' ), '1.0' );
        }

        # jquery: we use the version on google's cdn because other plugins load the one bundled with WordPress in the header and this sometime
        # causes conflicts. Having 2 versions of jQuery will cause a conflict as well though so that's why we have the option to load in the header
        # or the footer depending on whichever one works for you. By default we try to load it in the footer for performance reasons.
        wp_register_script( 'jquery-footer', "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js", false, '1.4.2', $this->load_js_in_footer ? 1 : 0 );

        # cycle
        $which_jq = (bool)$this->load_js_in_footer ? array( 'jquery-footer' ) : array( 'jquery' );
        wp_register_script( 'jquery-cycle', plugins_url( 'inc/js/jquery.cycle.all.min.js', __FILE__ ), $which_jq, '1.0', (bool)$this->load_js_in_footer );

        # documentation
        wp_register_script( 'tbt_documentation', plugins_url( 'inc/js/documentation.js', __FILE__ ), array( 'jquery-ui-tabs' ), '1.0' );

        # code mirror
        if( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'testimonial' && isset( $_GET['page'] ) && $_GET['page'] == 'tbtestimonials-syntax-settings' ){
            wp_enqueue_script( 'CodeMirror', plugins_url( 'inc/js/codemirror.js', __FILE__ ), array(), '1.0' );
            add_action ('admin_footer', array( &$this, 'add_codemirror' ) );
        }

        # only call when widget is active
        if( is_active_widget( false, false, 'tbtestimonialswidget' ) )
        {
            # load js
            if( ! is_admin() ){
                $requires = isset( $this->settings['disable_cycle'] ) && $this->settings['disable_cycle'] == 1 ? array() : array( 'jquery-cycle' );
                wp_enqueue_script( 'tbtestimonials', plugins_url( 'inc/js/tbtestimonials.js', __FILE__ ), $requires, '1.0', $this->load_js_in_footer ? 1 : 0 );
            }
        }
    }

    /**
    * register styles used in plugin
    *
    */
    function register_styles()
    {
        # admin css
        if( is_admin() ) wp_enqueue_style( 'tbtestimonials-admin-stylesheet', plugins_url( 'inc/css/tbtestimonials.admin.css', __FILE__ ), array(), '1.0', 'screen' );

        # documentation
        wp_register_style( 'tbt_documentation', plugins_url( 'inc/css/documentation.css', __FILE__ ), array(), '1.0', 'screen' );

        # only call when widget is active
        /*if( is_active_widget( false, false, 'tbtestimonialswidget' ) )
        {*/
            # load css
            if( ! is_admin() && isset( $this->settings['use_stylesheet'] ) )
                wp_enqueue_style( 'tbtestimonials-stylesheet', plugins_url( 'inc/css/tbtestimonials.css', __FILE__ ), array(), '1.0', 'screen' );
        /*}*/
    }

    /**
    * check if template api exists
    *
    */
    public function template_api_exists(){
        return get_option( 'tbt_templates' );
    }

    /**
    * add default templates to new template api
    *
    */
    public function add_defaults_to_template_api()
    {
        $templates = array();
        $templates['widget'] = new Testimonial_Output_Template( 'widget', 'Default widget syntax', $this->switch_to_twig_tags( $this->settings['testimonial_syntax'] ) );
        $templates['shortcode'] = new Testimonial_Output_Template( 'shortcode', 'Default shortcode syntax', $this->switch_to_twig_tags( $this->settings['testimonial_syntax_shortcode'] ) );
        $templates['listing'] = new Testimonial_Output_Template( 'listing', 'Default listing syntax', $this->switch_to_twig_tags( $this->settings['testimonial_syntax_listing'] ) );
        add_option( 'tbt_templates', $templates );
        return $templates;
    }

    public function switch_to_twig_tags( $template )
    {
        $template = preg_replace( array( '/(%if(.+?)%)/', '/(?:[^{>"]?)(%([^ if](.*?)[^"])%)/' ), array( "{% if $2 %}", "{{ $2 }}" ), $template );
        $template = str_replace( array( '{{ else }}', '{{ endif }}' ), array( '{% else %}', '{% endif %}' ), $template );
        return $template;
    }

    public function get_templates()
    {
        # check for template system api, update if not present
        if( ! $templates = $this->template_api_exists() )
            $templates = $this->add_defaults_to_template_api();

        $this->templates = $templates;
    }

    /**
    * create and add template object to templates list
    *
    * @param mixed $name
    * @param mixed $description
    * @param mixed $syntax
    */
    public function add_template( $name, $description, $syntax )
    {
        $exists = array_key_exists( sanitize_title( $name ), $this->templates );

        if( ! $exists && isset( $name ) && ! empty( $name ) )
            $this->templates[ sanitize_title( $name ) ] = new Testimonial_Output_Template( $name, $description, $syntax );
        else
            return false;
    }

    /**
    * delete a template
    *
    * @param mixed $name
    */
    public function delete_template( $name )
    {
        if( isset( $name ) && ! empty( $name ) )
        {
            if( array_key_exists( sanitize_title( $name ), $this->templates ) )
            {
                # not allowed to delete defaults
                if( ! in_array( sanitize_title( $name ), array( 'widget', 'shortcode', 'listing' ) ) ){
                    unset( $this->templates[ sanitize_title( $name ) ] );
                    return true;
                }
                return false;
            }
            return false;
        }
        return false;
    }

    /**
    * update/save template list
    *
    */
    public function update_templates(){
        update_option( 'tbt_templates', $this->templates );
    }
}

# add widget
require_once( sprintf( '%s/tbtestimonials-widget.php', dirname( __FILE__ ) ) );

# init plugin
$tbtestimonials = new TBTestimonials();

# tinymce button
if( is_admin() )
{
    require_once( dirname( __FILE__ ) . '/inc/tinymce3/tinymce.php' );
    $tbtestimonials_tinymce_button = new TBTestimonialsTinyMCEButton();
}

/**
* function to output testimonial programatically into theme
*
* @param mixed $id
*/
function tbtestimonial( $id = false, $cat = false, $template = 'shortcode', $echo = true, $order = 'desc', $orderby = 'menu_order' )
{
    $tbtestimonials = new TBTestimonials();

    if( is_string( $id ) && $id == 'random' || is_string( $id ) &&  $id == 'rand' || $id === false  )
        $q = new WP_Query( array( 'post_type' => 'testimonial', 'post_status' => 'publish', 'orderby' => 'rand', 'posts_per_page' => 1 ) );
    elseif( is_string( $id ) && strtolower( $id ) == 'all' )
        $q = new WP_Query( array( 'post_type' => 'testimonial', 'post_status' => 'publish', 'orderby' => $orderby, 'order' => $order, 'posts_per_page' => -1 ) );
    elseif( is_numeric( $id ) && ! $cat )
        $q = new WP_Query( array( 'p' => (int)$id, 'post_type' => 'testimonial', 'post_status' => 'publish' ) );
    elseif( is_numeric( $id ) && $cat )
        $q = new WP_Query( array( 'testimonial_category' => $id, 'post_type' => 'testimonial', 'post_status' => 'publish' ) );
    else
        return false;

    if( $q->have_posts() )
    {
        while( $q->have_posts() )
        {
            $q->the_post();
            if( isset( $tbtestimonials->settings['use_template_api'] ) )
                $output = sprintf( '<div class="tbtestimonial">%s</div>', $tbtestimonials->prepare_testimonial( sanitize_title( $template ) ) );
            else
                $output = sprintf( '<div class="tbtestimonial">%s</div>', $tbtestimonials->deprecated__prepare_testimonial( 'shortcode-single' ) );
        }

        $output = apply_filters( 'tbtestimonials_single_syntax', $output );
        if( ! $echo ) return $output; else print $output;
    }
    else
        return false;
}


/**
* load a template file from cwd. seperate php and markup this way.
*
* @param string $file
*/
function tbtestimonials_load_template( $file )
{
    $file = sprintf( '%s/inc/tpl/%s.php', dirname( __FILE__ ), $file );
    if( file_exists( $file ) && is_readable( $file ) )
        include( $file );
    else
    {
        if( ! is_readable( $file ) && file_exists( $file ) )
            wp_die( sprintf( 'Unable to load template file %s. File is not readable. Check permissions and try again.', $file ) );
        elseif( ! file_exists( $file ) )
            wp_die( sprintf( 'Unable to load template file %s. File does no exist.', $file ) );
    }
}

/**
* get settings or add them if needed.
*
*/
function tbtestimonials_settings()
{
    if( ! $general_settings = get_option( 'tbtestimonials_settings' ) )
    {
        add_option( 'tbtestimonials_settings', array(
            'author_prefix' => '&mdash;',
            'use_stylesheet' => 1,
            'use_cycle' => 1,
            'js_in_footer' => 0,
            'show_loading_graphic' => 1,
            'loading_graphic_url' => plugins_url( 'inc/i/loaders/loader_default.gif', __FILE__ ),
            'loading_text' => 'Loading',
            'loading_text_position' => 'after',
            'gravatar_size' => 50
        ) );

        $general_settings = get_option( 'tbtestimonials_settings' );
    }

    if( ! $output_settings = get_option( 'tbtestimonials_output_settings' ) )
    {
        add_option( 'tbtestimonials_output_settings', array( 'testimonial_syntax' => '<li class="testimonial">
    <div class="testimonial-gravatar">%gravatar%</div>
    <div class="testimonial-data">
        <p class="testimonial-content">%testimonial_excerpt%</p>
        <p class="testimonial-author">%author_prefix%%author%</p>
        %if company_url%
            <p class="testimonial-company"><a href="%company_url%">%company_name%</a></p>
        %endif%
    </div>
    <div class="tbtclear"></div>
</li>',
            'testimonial_syntax_shortcode' => '<div class="in-content-testimonial">
    <div class="testimonial-gravatar">%gravatar%</div>
    <div class="testimonial-data">
        <p class="testimonial-content">%testimonial%</p>
        <p class="testimonial-author">%author_prefix%%author%</p>
        %if company_url%
            <p class="testimonial-company"><a href="%company_url%">%company_name%</a></p>
        %endif%
    </div>
    <div class="tbtclear"></div>
</div>
',
            'testimonial_syntax_listing' => '<div class="in-listing-testimonial">
    <div class="testimonial-gravatar">%gravatar%</div>
    <div class="testimonial-data">
        <p class="testimonial-content">%testimonial%</p>
        <p class="testimonial-author">%author_prefix%%author%</p>
        %if company_url%
            <p class="testimonial-company"><a href="%company_url%">%company_name%</a></p>
        %endif%
    </div>
    <div class="tbtclear"></div>
</div>
',
            'add_testimonial_form' => '<form action="" method="post" class="add-testimonial" id="tbtestimonials-insert-testimonial-form">
    <p><label for="name">Name:</label><input id="name" type="text" size"40" name="tbtestimonial_name" /></p>
    <p><label for="company_name">Company Name:</label><input id="company_name" type="text" size"40" name="tbtestimonial_company_name" /></p>
    <p><label for="company_url">Website URL:</label><input id="company_url" type="text" size"40" name="tbtestimonial_company_url" /></p>
    <p><label for="email_address">Email Address</label><input id="email_address" type="text" size"40" name="tbtestimonial_email_address" /></p>
    <p><label for="testimonial">Testimonial</label><textarea name="testimonial" id="testimonial" cols="100" rows="10"></textarea></p>
    <p><input type="submit" value="Submit" /></p>
</form>'
 ) );

        $output_settings = get_option( 'tbtestimonials_output_settings' );
    }

    # add default preloaders
    if( ! $preloaders = get_option( 'tbtestimonials_preloaders' ) ){
        $preloaders = tbtestimonials_register_preloaders( array( 'loader_default.gif', 'loader_1.gif', 'loader_2.gif', 'loader_3.gif', 'loader_4.gif', 'loader_5.gif', 'loader_6.gif', 'loader_7.gif' ) );
    }

    return array_merge( $general_settings, $output_settings, array( 'preloaders' => $preloaders ) );
}

/**
* add preloader
*
* @param filepath|filename|url $url - Can be a filename of an image in the loaders folder, a file path to a file in the wp-content folder, or a url to an image in the wp-content folder.
*/
function tbtestimonials_register_preloaders( $url )
{
    # make sure option exists, if not create it.
    if( ! $preloaders = get_option( 'tbtestimonials_preloaders' ) )
    {
        add_option( 'tbtestimonials_preloaders', array() );
        $preloaders = get_option( 'tbtestimonials_preloaders' );
    }

    if( is_array( $url ) )
    {
        foreach( $url as $loader )
        {
            if( in_array( basename( $loader ), $preloaders ) )
                continue;

            if( $preloader_obj = tbtestimonials_prepare_preloader( $loader ) )
                $preloaders += $preloader_obj;
        }
    }
    else
    {
        if( in_array( basename( $url ), $preloaders ) )
            return false;

        if( $preloader_obj = tbtestimonials_prepare_preloader( $loader ) ){
            if( is_object( $preloader_obj ) )
                $preloaders += $preloader_obj;
        }
    }

    return apply_filters( 'tbtestimonials_preloaders', $preloaders );
}

/**
* prepare preloader object
*
* @param mixed $url
* @return mixed
*/
function tbtestimonials_prepare_preloader( $url )
{
    # check if valid url
    if( preg_match( '/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', $url ) )
    {
        $preloader = array();
        $preloader['url'] = esc_attr( $url );
        $preloader['path'] = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, esc_attr( $url ) );
    }
    else
    {   # invalid url, check file exists
        if( file_exists( $url ) )
        {
            $preloader = array();
            $preloader['url'] = str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, esc_attr( $url ) );
            $preloader['path'] = esc_attr( $url );
        }
        else # file doesn't exist
        {   # check in loaders folder
            if( file_exists( sprintf( '%s%s', str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, plugins_url( 'inc/i/loaders/', __FILE__) ), basename( $url ) ) ) )
            {
                $preloader = array();
                $preloader['url'] = sprintf( '%s%s', plugins_url( 'inc/i/loaders/', __FILE__), basename( $url ) );
                $preloader['path'] = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $preloader['url'] );
            }
            else # not in loaders folder, return
                return false;
        }
    }

    if( isset( $preloader ) )
        return array( basename( $url ) => (object)$preloader );
    else
        return false;
}