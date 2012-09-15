<?php

    /**
     * add tbtestimonials button to tinymce
     * thanks to Alex Rabe (NextGen) for making this as easy as possible.
     */
    class TBTestimonialsTinyMCEButton
    {

        var $pluginname = 'tbtestimonials';
        var $path = '';
        var $version = 1;

        /**
         * @desc php4 constructor
         * @return void
         */
        function TBTestimonialsTinyMCEButton()
        {
            $argv = func_get_args();
            call_user_func_array( array( &$this, '__construct' ), $argv );
        }

        /**
        * @desc php5 constructor
        * @return void
        */
        function __construct()
        {
            // Set path to editor_plugin.js
            $this->path = plugins_url( '', __FILE__ );
            add_filter( 'tiny_mce_version', array ( &$this, 'change_tinymce_version' ) );
            add_action( 'init', array ( &$this, 'addbuttons' ) );
        }

        /**
         * @desc add buttons to editor
         * @return void
         */
        function addbuttons() {
            // Don't bother doing this stuff if the current user lacks permissions
            if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') )
                return;

            // Add only in Rich Editor mode
            if ( get_user_option( 'rich_editing' ) == 'true' )
            {
                // add the button for wp2.5 in a new way
                add_filter( "mce_external_plugins", array( &$this, 'add_tinymce_plugin' ), 5 );
                add_filter( 'mce_buttons', array( &$this, 'register_button' ), 5 );
            }
        }

        /**
         * @desc used to insert button in wordpress 2.5x editor
         * @return $buttons
         */
        function register_button( $buttons )
        {
            array_push( $buttons, 'separator', $this->pluginname );
            return $buttons;
        }

        /**
         * @desc Load the TinyMCE plugin : editor_plugin.js
         * @return $plugin_array
         */
        function add_tinymce_plugin( $plugin_array )
        {
            $plugin_array[ $this->pluginname ] =  $this->path . '/editor_plugin.js';
            return $plugin_array;
        }

        /**
         * @desc rebuild the cache
         * @return $version
         */
        function change_tinymce_version( $version )
        {
            $version = $version . '-tbtestimonials-' . $this->version;
            return $version;
        }
    }