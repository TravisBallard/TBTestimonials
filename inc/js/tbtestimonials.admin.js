jQuery( document ).ready( function(){ init(); } );

function init()
{
    jQuery( function($){
        var link;
        $( '#tbtestimonials-category-add-toggle' ).click( function(){ link = $(this); $('#tbtestimonials-link-category-add').slideToggle( 'fast', function(){ if( link.html() == '+ Add New Category' ) link.html( ' - Add New Category' ); else link.html( '+ Add New Category' ); link = null; } ); return false; });
        $( '#tbtestimonials-newcat' ).focus(function(){ if( $(this).val() == 'New category name' ) $(this).val(''); })
        $( '#tbtestimonials-newcat' ).blur(function(){ if( $(this).val() == '' ) $(this).val( 'New category name' ); })
        $( '#tbtestimonials-category-add-submit' ).click( function(){ if( $( '#tbtestimonials-newcat' ).val() != 'New category name' && $( '#tbtestimonials-newcat' ).val() != '' ) add_testimonial_category(); else return; });
        update_delete_links();
    });
}

function add_testimonial_category()
{
    var catname = jQuery( '#tbtestimonials-newcat' ).val(),
        nonce = jQuery( '#tbtestimonials_category_ajax_nonce' ).val();

    jQuery( '#tbtestimonials-newcat' ).val( '' );

    jQuery.ajax({
        url: tbtestimonials_admin.add_category_ajaxurl,
        timeout: 10000,
        type:'POST',
        data: 'catname=' + catname + '&nonce=' + nonce,
        dataType:'json',
        error: function(e){ handle_error(e) },
        success: function(json){ add_testimonial_to_display(json); }
    });


}

function handle_error( e )
{
    alert( e.msg );
}

function add_testimonial_to_display( r )
{
    if( r.error == 1 ) alert( r.msg );
    jQuery( '#tbtestimonials-category-ajax-response' ).append( r.element );
    update_delete_links();
}

function remove_testimonial_from_display( r, e )
{
    if( r.catid != 0 ) jQuery( '#' + e ).fadeOut('slow').remove();
    else alert( r.msg );
    if( r.error == 1 ) alert( r.msg );
}

function delete_testimonial_category( element_id )
{
    var nonce = jQuery( '#tbtestimonials_category_ajax_nonce' ).val();
    var id = element_id.match(/(\d+)$/ig);

    if( confirm( 'Are you sure want to delete this category?' ) )
    {
        jQuery.ajax({
            url: tbtestimonials_admin.delete_category_ajaxurl,
            timeout: 10000,
            type:'POST',
            data: 'catid=' + id + '&nonce=' + nonce,
            dataType:'json',
            error: function(e){ handle_error(e) },
            success: function(json){ remove_testimonial_from_display( json, element_id ); }
        });
    }

    return false;
}

function update_delete_links()
{
    var i = 0;
    jQuery( '.tbtestimonials-delete-cat' ).each(function(){ jQuery(this).attr( 'id', 'tbtestimonial-cat-' + i ).unbind('click').click( function(){ delete_testimonial_category( jQuery(this).attr( 'rel' ) ); i++; return false; } ); });
}