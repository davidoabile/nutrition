jQuery( document ).ready(function($) {
    $('#system_config_tabs dt').on('click', function(){
        var choosen_dl = $(this).closest('dl');
        choosen_dl.toggleClass("hide_dd");
        var choosen_dl_id = choosen_dl.attr('id');
        if(choosen_dl.find('.active').length>0){
            choosen_dl.toggleClass("active_dl");
        }
        if($.cookie(choosen_dl_id)){
            $.removeCookie(choosen_dl_id, { path: '/' });
        } else {
            $.cookie(choosen_dl_id, "1", { expires: 365, path: '/' });
        }        
    });
    
    $( "#system_config_tabs dl" ).each(function( index ) {
        $(this).toggleClass("hide_dd");
        var dl_id = $(this).attr("id");
        if($(this).find('.active').length>0){
            $(this).toggleClass("active_dl");
        }
        if($.cookie(dl_id)){
            $(this).toggleClass("hide_dd");
        }
    });
    
});