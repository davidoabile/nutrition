jQuery( document ).ready(function($) {
    $("#messages .error-msg").first().hide();
    $("#messages .success-msg").first().hide();
    $("#messages .warning-msg").first().hide();
    $("#messages .notice-msg").first().hide();
    $('#messages .error-msg').html($('.error-msg').html()+'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>');
    $('#messages .success-msg').html($('.success-msg').html()+'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>');
    $('#messages .warning-msg').html($('.warning-msg').html()+'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>');
    $('#messages .notice-msg').html($('.notice-msg').html()+'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>');
    
   
    $("#messages .error-msg").first().slideDown('slow');
    $("#messages .warning-msg").first().slideDown('slow');
    $("#messages .success-msg").first().slideDown('slow');
    $("#messages .notice-msg").first().slideDown('slow');
    
    
    $("#messages button.close").on('click',function(e){
        e.stopPropagation();
        $(this).parent('.messages>li').slideUp('slow');
    });
    
    if((typeShowingErrorMessage == 'slide_all_except_error_messages')||(typeShowingErrorMessage == 'slide_all')){
        setTimeout(function(){
            $('#messages .warning-msg').slideUp('slow');
            $('#messages .success-msg').slideUp('slow');
            $('#messages .notice-msg').slideUp('slow');
        },2000+600);
        
        if(typeShowingErrorMessage == 'slide_all'){
            setTimeout(function(){
                $('#messages .error-msg').slideUp('slow');
            },30000+600);
        }
    }
});

function confirmSetLocation(message, url){
    jQuery('#html-body').append('<div id="dialog-confirmSetLocation" title="Warning!" style="displat:none;"><p>'+ message +'</p></div>');
    var dialog_box = jQuery('#dialog-confirmSetLocation');
    dialog_box.dialog({
        resizable: false,
        height: 200,
        modal: true,
        closeOnEscape: false,
        buttons: [
            {
                text: "OK",
                "class": 'okConfirmSetLocation',
                click: function () {
                    dialog_box.dialog("close");
                    setLocation(url);
                }
            },
            {
                text: "Cancel",
                "class": 'cancelConfirmSetLocation',
                click: function () {
                    dialog_box.dialog("close");
                }
            }
        ]
    }).dialog("widget").find(".ui-dialog-titlebar-close").hide();
    return false;
}