<?php
    # get settings
    $tbtestimonials_settings = tbtestimonials_settings();
?>
<div class="wrap">
    <h2>TBTestimonials Output Settings</h2>

    <?php if( isset( $_GET['updated'] ) && $_GET['updated'] == 'true' ) : ?>
        <div class="updated fade"><p>Settings saved.</p></div>
    <?php endif; ?>

    <div style="background-color:#ffffdf; border:1px solid #e7e2a5; padding:10px; margin-top:20px;">
        <h3 style="padding:0; margin:0; line-height:1em;">Notice</h3>
        <p style="padding:0; margin:0;">In this version we introduced a new Output Template API. We've taken your current templates and converted the tags to the new syntax used.<br><em style="font-weight:bold;">You will NOT notice any changes until you go to the <a href="<?php echo admin_url('edit.php?post_type=testimonial&page=tbtestimonials-settings'); ?>">General Settings</a> page and enable the new API.</em></p>
    </div>

    <form action="options.php" method="post">
        <?php settings_fields( 'tbtestimonials_output_settings' ); ?>

        <h3>Output Syntax</h3>
        <p class="notice">You can use conditionals in your template tags.<br />Example: <code>%if company_url%&lt;p&gt;%company_url%&lt;/p&gt;%else%&lt;p&gt;No Company URL&lt;/p&gt;%endif%</code><br />This will check to see if <code>%company_url%</code> is empty and if it is not it will output the company url, if it is empty, it will say No Company URL</p>
        <table class="form-table">
            <tr>
                <th>
                    <strong>Widget Testimonial</strong><br /><br />
                    <small>
                        <strong>Template Tags</strong>:<br />
                        %permalink%<br/>
                        %gravatar%<br/>
                        %testimonial%<br/>
                        %testimonial_excerpt%<br/>
                        %author_prefix%<br/>
                        %author%<br/>
                        %company_url%<br/>
                        %company_name%<br/>
                    </small>
                </th>
                <td>
                    <textarea id="testimonial-syntax" name="tbtestimonials_output_settings[testimonial_syntax]" rows="10" cols="50"><?php echo $tbtestimonials_settings['testimonial_syntax']; ?></textarea>
                </td>
            </tr>
            <tr>
                <th>
                    <strong>Shortcode</strong><br /><small>(single testimonials)</small><br /><br />
                    <small>
                        <strong>Template Tags</strong>:<br />
                        %permalink%<br/>
                        %gravatar%<br/>
                        %testimonial%<br/>
                        %testimonial_excerpt%<br/>
                        %author_prefix%<br/>
                        %author%<br/>
                        %company_url%<br/>
                        %company_name%<br/>
                    </small>
                </th>
                <td>
                    <textarea id="testimonial-syntax-shortcode" name="tbtestimonials_output_settings[testimonial_syntax_shortcode]" rows="10" cols="50"><?php echo $tbtestimonials_settings['testimonial_syntax_shortcode']; ?></textarea>
                </td>
            </tr>
            <tr>
                <th>
                    <strong>Shortcode</strong><br /><small>(all testimonials / listing page)</small><br /><br />
                    <small>
                        <strong>Template Tags</strong>:<br />
                        %permalink%<br/>
                        %gravatar%<br/>
                        %testimonial%<br/>
                        %testimonial_excerpt%<br/>
                        %author_prefix%<br/>
                        %author%<br/>
                        %company_url%<br/>
                        %company_name%<br/>
                    </small>
                </th>
                <td>
                    <textarea id="testimonial-syntax-listing" name="tbtestimonials_output_settings[testimonial_syntax_listing]" rows="10" cols="50"><?php echo $tbtestimonials_settings['testimonial_syntax_listing']; ?></textarea>
                </td>
            </tr>
        </table>

        <!--<h2 class="top-marg-40">Add Testimonial Form</h2>

        <table class="form-table">
            <tr>
                <td>
                    <textarea id="add-testimonial-form" name="tbtestimonials_output_settings[add_testimonial_form]" rows="10" cols="50"><?php echo esc_attr( $tbtestimonials_settings['add_testimonial_form'] ); ?></textarea>
                </td>
            </tr>
        </table>-->

        <p class="submit">
            <input type="submit" class="button-primary" value="Save Changes" />
        </p>
    </form>
</div>
