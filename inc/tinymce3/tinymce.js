function init() {
    tinyMCEPopup.resizeToInnerSize();
}

function insert_testimonial( id ) {

    var tagtext = "[testimonial id='" + id + "']";

    if(window.tinyMCE) {
        //TODO: For QTranslate we should use here 'qtrans_textarea_content' instead 'content'
        window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
        //Peforms a clean up of the current editor HTML.
        //tinyMCEPopup.editor.execCommand('mceCleanup');
        //Repaints the editor. Sometimes the browser has graphic glitches.
        tinyMCEPopup.editor.execCommand('mceRepaint');
        tinyMCEPopup.close();
    }
    return false;
}

jQuery( function($){
   $('p.testimonial').hover(
        function(){ $(this).data('oc', $(this).css('backgroundColor') ); $(this).css({backgroundColor:'#e2e9ff'}); },
        function(){ $(this).css('backgroundColor', $(this).data('oc')); });
});