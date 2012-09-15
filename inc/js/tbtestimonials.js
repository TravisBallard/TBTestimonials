jQuery( document ).ready( function ( ) {
    if( typeof( tbtestimonial_settings ) != 'undefined' )
    {
        if( tbtestimonial_settings.animate == 1 )
        {
            if( tbtestimonial_settings.preloader == 1 )
            {
                jQuery('#tbtestimonials-widget').css({display:'block'}).hide();

                jQuery( '.ajax-loader' ).delay(1800).fadeOut( 'slow', function(){
                    jQuery('#tbtestimonials-widget').fadeIn('slow');
                    jQuery('#tbtestimonials-widget').show().delay(800).cycle({
                        fx: tbtestimonial_settings.transition,
                        timeout: tbtestimonial_settings.timer_interval * 1000,
                        speed: tbtestimonial_settings.transition_interval * 1000
                    });
                });
            }
            else
            {
                jQuery('#tbtestimonials-widget').cycle({
                    fx: tbtestimonial_settings.transition,
                    timeout: tbtestimonial_settings.timer_interval * 1000,
                    speed: tbtestimonial_settings.transition_interval * 1000
                });
            }
        }
        else
        {
            if( tbtestimonial_settings.preloader == 1 )
            {
                jQuery('#tbtestimonials-widget').css({display:'block'}).hide();
                jQuery( '.ajax-loader' ).delay(1800).fadeOut( 'slow', function(){
                    jQuery('#tbtestimonials-widget').fadeIn('slow');
                    jQuery('#tbtestimonials-widget').show();
                });
            }
        }
    }
} );