<?php
    # get settings
    $tbtestimonials_settings = tbtestimonials_settings();
    global $tbtestimonials;
?>
<div class="wrap">
    <h2>TBTestimonials Settings</h2>
    <?php if( isset( $_GET['updated'] ) && $_GET['updated'] == 'true' ) : ?>
        <div class="updated fade"><p>Settings saved.</p></div>
    <?php endif; ?>
    <form action="options.php" method="post">
        <?php settings_fields( 'tbtestimonials_settings' ); ?>
        <div style="margin:10px 0 40px; border:1px solid #dfdfdf; padding:10px 20px 20px; background-color:#ffffdf;">
            <h2>Template API</h2>
            <p>The Template API introduced in verison 1.6.0 offers a lot more flexibility to customize the output of your testimonials thanks to <a href="http://twig.sensiolabs.org/">Twig</a>. There's a new syntax for template tags and conditionals but it's similar and way more powerful.<br />
            <em>This option will be removed in future versions and users will be forced to use the new syntax. I'm giving you the option to switch now on your own before making it mandatory. This will also help work out any bugs if there are any.</em></p>
            <table class="form-table">
                <tr>
                    <th><label for="use_template_api"><strong>Enable Template API</strong></label></th>
                    <td><input type="checkbox" name="tbtestimonials_settings[use_template_api]" id="use_template_api" <?php if( isset( $tbtestimonials_settings['use_template_api'] ) ) checked( 1, 1 ); ?> /></td>
                </tr>
            </table>
        </div>
        <h3>General Settings</h3>
        <table class="form-table">
            <tr>
                <th>Author Prefix</th>
                <td><input type="text" size="6" name="tbtestimonials_settings[author_prefix]" id="author_prefix" value="<?php echo isset( $tbtestimonials_settings['author_prefix'] ) ? $tbtestimonials_settings['author_prefix'] : ''; ?>" /></td>
            </tr>
            <tr>
                <th>Use Included Stylesheet?<br /><small>(tb-testimonials/inc/css/style.css)</small></th>
                <td>
                    <input name="tbtestimonials_settings[use_stylesheet]" id="use_stylesheet" value="1" type="checkbox"<?php if( isset( $tbtestimonials_settings['use_stylesheet'] ) ) checked( $tbtestimonials_settings['use_stylesheet'], 1 ); ?> />
                    <a href="<?php echo admin_url(); ?>plugin-editor.php?file=tb-testimonials/inc/css/tbtestimonials.css&plugin=tb-testimonials/tb-testimonials.php" style="margin-left:40px;">Edit the included StyleSheet</a>
                </td>
            </tr>
            <tr>
                <th>Disable jQuery Cycle?<br /><small>(Incase your theme already loads it)</small></th>
                <td>
                    <input name="tbtestimonials_settings[disable_cycle]" id="disable_cycle" value="1" type="checkbox"<?php if( isset( $tbtestimonials_settings['disable_cycle'] ) ) checked( $tbtestimonials_settings['disable_cycle'], 1 ); ?> />
                </td>
            </tr>
            <tr>
                <th>Load JavaScript in the footer?<br /><small>If you are encountering a conflict with other JavaScript, unchecking this may solve it.</small></th>
                <td>
                    <input name="tbtestimonials_settings[js_in_footer]" id="js_in_footer" value="1" type="checkbox"<?php if( isset( $tbtestimonials_settings['js_in_footer'] ) ) checked( $tbtestimonials_settings['js_in_footer'], 1 ); ?> />
                </td>
            </tr>
            <tr>
                <th>Display Loading Graphic<br /><small>(in sidebar widget)</small></th>
                <td><input name="tbtestimonials_settings[show_loading_graphic]" id="show_loading_graphic" value="1" type="checkbox"<?php if( isset( $tbtestimonials_settings['show_loading_graphic'] ) ) checked( $tbtestimonials_settings['show_loading_graphic'], 1 ); ?> /> Note: As of version 1.5 these serve no actual purpose and are here purely for aesthetic reasons. You know, to make it look pretty :)</td>
            </tr>
            <tr>
                <th>Loading Graphic URL</th>
                <td><input type="text" size="60" name="tbtestimonials_settings[loading_graphic_url]" id="loading_graphic_url" value="<?php echo isset( $tbtestimonials_settings['loading_graphic_url'] ) ? $tbtestimonials_settings['loading_graphic_url'] : ''; ?>" /><?php if( isset( $tbtestimonials_settings['loading_graphic_url'] ) && ! empty( $tbtestimonials_settings['loading_graphic_url'] ) ) printf( '<img src="%s" alt="loading image" style="margin-left:10px;" id="loading-image-preview" />', $tbtestimonials_settings['loading_graphic_url'] ); ?></td>
            </tr>
            <tr>
                <th>Loading Graphics<br /><small>Click to use.</small></th>
                <td>
                    <?php
                        foreach( $tbtestimonials_settings['preloaders'] as $filename => $loader )
                            printf( '<p><a href="#" onclick="jQuery(\'#loading_graphic_url\').val(\'%s\'); jQuery(\'#loading-image-preview\').attr(\'src\', \'%s\' ); return false;"><img src="%s" alt="%s" /></a></p>', $loader->url, $loader->url, $loader->url, $filename );
                    ?>
                    <p>You can generate more loading graphics at <a href="http://www.ajaxload.info">www.ajaxload.info</a> or <a href="http://www.preloaders.net">www.preloaders.net</a>.<br />
                    To register a new preloader, use the <code>tbtestimonials_preloaders</code> filter.</p>
                </td>
            </tr>
            <tr>
                <th>Loading Text</th>
                <td><input type="text" size="20" name="tbtestimonials_settings[loading_text]" id="loading_text" value="<?php echo isset( $tbtestimonials_settings['loading_text'] ) ? $tbtestimonials_settings['loading_text'] : ''; ?>" /></td>
            </tr>
            <tr>
                <th>Loading Text Position</th>
                <td><input type="radio" name="tbtestimonials_settings[loading_text_position]" id="loading_text_position_before" value="before"<?php if( isset( $tbtestimonials_settings['loading_text_position'] ) ) checked( $tbtestimonials_settings['loading_text_position'], 'before' ); ?> /><label for="loading_text_position_before">Before Graphic</label><br /><input type="radio" name="tbtestimonials_settings[loading_text_position]" id="loading_text_position_after" value="after"<?php checked( $tbtestimonials_settings['loading_text_position'], 'after' ); ?> /><label for="loading_text_position_after">After Graphic</label></td>
            </tr>
            <tr>
                <th>Gravatar Size<br /><small>(in pixels)</small></th>
                <td><input type="text" size="6" name="tbtestimonials_settings[gravatar_size]" id="gravatar_size" value="<?php echo isset( $tbtestimonials_settings['gravatar_size'] ) ? $tbtestimonials_settings['gravatar_size'] : 50; ?>" /></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" class="button-primary" value="Save Changes" />
        </p>
    </form>
</div>