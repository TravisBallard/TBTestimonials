<?php global $tbtestimonials; ?>
<div class="wrap">
    <h2>TBTestimonials Documentation</h2>
    <p style="width:800px;">This is a small documentation I've put together for you guys and gals on a temporary basis until I get a chance to redo my website. I'll explain some hooks and filters that are available to developers as well as how to use some of the default functionality offered by the plugin.</p>
    <p style="width:800px;">This documentation is in no way shape or form complete. You will see changes to the documentation as different versions of the plugin are completed and released. It may even also be removed in the future.</p>

    <div class="shameless-plugs">
        <div class="twitter">
            <a href="http://twitter.com/ansimation/">Follow me on twitter.</a>
        </div>
        <div class="donate-button">
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                <input type="hidden" name="cmd" value="_s-xclick">
                <input type="hidden" name="hosted_button_id" value="5X4EL67QY4AVS">
                <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
        </div>
        <div class="website">
            <a href="http://travisballard.com/">Travis Ballard Design</a>
        </div>
        <div class="clear"></div>
    </div>

    <div id="tbt-documentation-tabs">
        <ul id="tabs">
            <li><a href="#functions">Functions</a></li>
            <li><a href="#shortcode">ShortCode</a></li>
            <li><a href="#filters">Filters</a></li>
            <li><a href="#examples">Examples</a></li>
        </ul>
        <div class="clear"></div>

        <div id="tab-content-container">
            <div class="tab-content" id="functions">
                <dl>
                    <dt>tbtestimonial( [integer|string $id] [, bool $cat = false ] [, string $template = 'shortcode' ] [, bool $echo = false] [, $order = 'desc' ] [, $orderby = 'menu_order' ] )</dt>
                    <dd>
                        <strong style="margin:10px 0 10px;display:block;">Parameters:</strong>
                        <dl>
                            <dt>ID</dt>
                            <dd>Integer or String - The ID of a testimonial to be displayed. Can be substituted for 'all', 'rand', or 'random'. If false we assume random.</dd>
                            <dt>Cat</dt>
                            <dd>Bool - Display testimonials from a category. ID must be a string if using this. eg: <code>tbtestimonial( 'my category', true );</code></dd>
                            <dt>Template</dt>
                            <dd>String - Name of the output template to use. Defined on Output Settings page.</dd>
                            <dt>Echo</dt>
                            <dd>Boolean - Echo if true, return if false</dd>
                            <dt>Order</dt>
                            <dd>String - The order parameter is not required and defaults to desc if absent. Valid values are: ASC, DESC</dd>
                            <dt>OrderBy</dt>
                            <dd>String - The order by parameter is not required and will default to menu_order if absent. Valid values are: none, ID, author, title, date, modified, parent, rand, menu_order, meta_value, and meta_value_num. <br />See <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters">the codex page</a> for more information.</dd>
                        </dl>
                    </dd>
                    <dt>tbtestimonials_register_preloaders( string|array $url )</dt>
                    <dd>
                        <strong style="margin:10px 0 10px;display:block;">Parameters:</strong>
                        <dl>
                            <dt>URL</dt>
                            <dd>Local URL(s) or file path(s) to preloader image(s). Must be in the wp-content folder, sub-directories are fine. <strong><em>See Examples Tab</em></strong>.</dd>
                        </dl>
                    </dd>
                    <dt>tbtestimonials_prepare_preloader( string $url )</dt>
                    <dd>
                        <strong style="margin:10px 0 10px;display:block;">Parameters:</strong>
                        <dl>
                            <dt>URL</dt>
                            <dd>Local URL or file path to preloader image. Must be in the wp-content folder, sub-directories are fine. <strong><em>See Examples Tab</em></strong>.</dd>
                        </dl>
                        <p class="updated fade" style="display:inline; padding:5px; position:relative; top:10px; color:#333;">Note: This function returns a preloader object to be used in <code>tbtestimonials_register_preloaders()</code></p>
                    </dd>
                </dl>
            </div>

            <div class="tab-content" id="shortcode">
                <dl>
                    <dt>[testimonial id='NUMERIC_ID|all|rand|random' template='shortcode' order="desc" orderby="menu_order"]</dt>
                    <dd>
                        <strong style="margin:10px 0 10px;display:block;">Parameters:</strong>
                        <dl>
                            <dt>ID</dt>
                            <dd>The ID parameter is not required <small>(<code>[testmonial]</code>)</small>.<br />If absent we will assume random. Aside from a numeric ID for a testimonial you can pass one of these strings, "all", "rand", or "random"</dd>
                            <dt>Template</dt>
                            <dd>The template parameter is not required and if missing will default to the "shortcode" template as defined in the <a href="<?php echo admin_url( 'edit.php?post_type=testimonial&page=tbtestimonials-syntax-settings' ); ?>">Output Settings</a></dd>
                            <dt>Order</dt>
                            <dd>The order parameter is not required and defaults to desc if absent. Valid values are: ASC, DESC</dd>
                            <dt>OrderBy</dt>
                            <dd>The order by parameter is not required and will default to menu_order if absent. Valid values are: none, ID, author, title, date, modified, parent, rand, menu_order, meta_value, and meta_value_num. <br />See <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters">the codex page</a> for more information.</dd>
                        </dl>
                    </dd>

                    <dt>[testimonial cat='my category' template='listing' order="desc" orderby="menu_order"]</dt>
                    <dd>
                        <strong style="margin:10px 0 10px;display:block;">Parameters:</strong>
                        <dl>
                            <dt>Cat</dt>
                            <dd>
                                The category name to show posts from ( case insensitive ).<br />
                                <em>The ID argument can not be used while using the cat argument. If it is, cat will be ignored.</em>
                            </dd>
                            <dt>Template</dt>
                            <dd>The template parameter is not required and if missing will default to the "listing" template as defined in the <a href="<?php echo admin_url( 'edit.php?post_type=testimonial&page=tbtestimonials-syntax-settings' ); ?>">Output Settings</a></dd>
                            <dt>Order</dt>
                            <dd>The order parameter is not required and defaults to desc if absent. Valid values are: ASC, DESC</dd>
                            <dt>OrderBy</dt>
                            <dd>The order by parameter is not required and will default to menu_order if absent. Valid values are: none, ID, author, title, date, modified, parent, rand, menu_order, meta_value, and meta_value_num. <br />See <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters">the codex page</a> for more information.</dd>
                        </dl>
                    </dd>
                </dl>
            </div>

            <div class="tab-content" id="filters">
                <dl>
                    <dt>tbtestimonials_thumbnail_size</dt>
                    <dd>Filter thumbnail size to display. New sizes are registered with <a href="http://codex.wordpress.org/Function_Reference/add_image_size">add_image_size()</a>. Default: tbtestimonial_thumbnail </dd>

                    <dt>tbtestimonials_testimonial_thumbnail</dt>
                    <dd>Filter thumbnails, gravatar and/or featured image.</dd>

                    <dt>tbtestimonials_template_tags</dt>
                    <dd>Filter default template tags.</dd>

                    <dt>tbtestimonials_widget_syntax</dt>
                    <dd>Filter widget syntax before it's used.</dd>

                    <dt>tbtestimonials_single_syntax</dt>
                    <dd>Filter single testimonial syntax for shortcode output</dd>

                    <dt>tbtestimonials_listing_syntax</dt>
                    <dd>Filter listing syntax for testimonials. When 'all' is passed as the ID in a shortcode.</dd>

                    <dt>tbtestimonials_preloaders</dt>
                    <dd>Filter default list of preloaders.</dd>

                    <dt>tbt_template_functions</dt>
                    <dd><strong>Hook</strong> for adding new tags. See Examples Tab for an example.</dd>
                </dl>
            </div>

            <div class="tab-content" id="examples">
                <div class="example" id="register_preloaders_example">
                    <h4>Add a new preloader image.</h4>
                    <?php ob_start(); ?>add_filter( 'tbtestimonials_preloaders', 'add_custom_preloaders' );

function add_custom_preloaders( $preloaders ){
    return array_merge( tbtestimonials_prepare_preloader( 'http://website.com/wp-content/themes/foo/images/preloader.gif' ), $preloaders );
}<?php highlight_string( "<?php \n\n" . ob_get_clean() ); ?>
                </div>

                <?php if( ! isset( $tbtestimonials->settings['use_template_api'] ) ) : ?>
                    <div class="example" id="add_deprecated_template_tags">
                        <h4>Add new template tags. <span style="color:#666; font-weight:normal;">( Enable the new Template API on the <a href="<?php echo admin_url( 'edit.php?post_type=testimonial&page=tbtestimonials-settings' ); ?>">General Settings</a> page then come back to view examples using the new tbt_template_functions hook. )</span></h4>
                        <?php ob_start(); ?>add_filter( 'tbtestimonials_template_tags', 'add_featured_image_to_testimonials_template_tags' );
add_filter( 'tbtestimonials_template_tag_replacements', 'add_featured_image_to_testimonials_template_replacements' );

function add_featured_image_to_testimonials_template_tags( $t ){
    return array_merge( array( '%featured_image%' ), $t );
}

function add_featured_image_to_testimonials_template_replacements( $r ){
    add_image_size( 'my_testimonial_thumbnail_size', 75, 75, 1 );
    return array_merge( array( get_the_post_thumbnail( null, 'my_testimonial_thumbnail_size' ) ), $r );
}

<?php highlight_string( "<?php \n\n" . ob_get_clean() ); ?>
                    </div>
                <?php else : ?>
                    <div class="example" id="add_template_tags">
                        <h4>Add new template tags : Procedural Example</h4>
                        <?php ob_start(); ?>add_action( 'tbt_template_functions', 'add_tags_to_tbtestimonials' );

/**
* add a 'foobar' variable to tbt
*
* @param mixed $twig
*/
function add_tags_to_tbtestimonials( $twig ){
    $twig->addGlobal( 'foobar', call_user_func( 'foobar_func' ) );
    $twig->addGlobal( 'foo', call_user_func( 'foo_func' ) );
}

/**
* callback for foo tag
*
*/
function foo_func(){
    return 'Testimonial ID: ' . get_the_ID();
}

/**
* callback for foobar tag
*
*/
function foobar_func(){
    return 'ID: ' . get_the_ID();
}

<?php highlight_string( "<?php \n\n" . ob_get_clean() ); ?>
                    </div>
                    <div class="example" id="add_template_tags">
                        <h4>Add new template tags : OOP Example</h4>
                        <?php ob_start(); ?>class MyClass
{
    /**
    * magic
    *
    */
    public function __construct(){
        add_action( 'tbt_template_functions', array( $this, 'add_variables' ) );
    }

    /**
    * add variables
    *
    * @param mixed $twig
    */
    public function add_variables( $twig ){
        $twig->addGlobal( 'foobar', call_user_func( 'MyClass::my_tag_func' ) );
    }

    /**
    * static callback function
    *
    */
    public static function my_tag_func(){
        return spritnf( 'Testimonial[%d]', get_the_ID() );
    }
}

$instance = new MyClass();

<?php highlight_string( "<?php \n\n" . ob_get_clean() ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>