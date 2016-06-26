shownCleanOverview = !jQuery.cookie("clean_overview") ? true : false;

jQuery( document ).ready(function($) {
    $('.graph_img').on('click', function(){
        $(this).closest(".entry-edit-head").parent("div").toggleClass("close_graph");
        if($(this).closest(".entry-edit-head").parent("div").find("#overviewLineChart").length){
            overviewLineChart_edit = !overviewLineChart_edit;
        }
    });

    $('.drop_graph_img').on('click', function(){
        var choosen_div = $(this).closest(".entry-edit-head").parent("div");
        var choosen_div_id = choosen_div.attr('id');
        choosen_div.toggleClass("close_tab");
        if($.cookie(choosen_div_id)){
            $.removeCookie(choosen_div_id);
            if(!shownCleanOverview){
                showOverviewLineChart(); shownCleanOverview = true;
            }
        } else {
            $.cookie(choosen_div_id, "1", { expires: 365 });
        }        
    });
    
    $( "#left_dashboard>div.entry-edit" ).each(function( index ) {
        var div_id = $(this).attr("id");
        if($.cookie(div_id)){
            $(this).toggleClass("close_tab");
        }
    });
    
    $('.showing_quaters, .hidden_quaters').on('click', function(){
        $(this).closest(".clean_dash").toggleClass("hide_quaters");
    });
    
});