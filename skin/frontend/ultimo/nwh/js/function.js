/**
 * Created with JetBrains PhpStorm.
 * User: NAM
 * Date: 3/23/15
 * Time: 9:32 AM
 * To change this template use File | Settings | File Templates.
 */
function equalHeight(group,minheight) {
    tallest = 0;
    minheight = (minheight == 0 ? 0 : minheight);
    group.each(function() {
        thisHeight = jQuery(this).height();
        if(thisHeight > tallest) {
            tallest = thisHeight;

        }
    });
    if(minheight > 0){
        if(tallest < minheight) tallest = minheight;
    }

    group.height(tallest);
}
function addEventForViewMore(){
    if($('btn-view-more-page-1'))
        $('btn-view-more-page-1').removeClassName('item-hide');
    $$('.filter-view-more-btn').each(function(btn){
        $(btn).observe('click', function(event) {
            var btnclass=btn.id;
            var showclass=btnclass.replace("btn", "filter");
            var newtviewmore=parseInt(btnclass.replace("btn-view-more-page-", ""))+1;
            $$('.'+showclass).each(function(element){
                $(element).removeClassName('item-hide');
            });
            if($('btn-view-more-page-'+newtviewmore))
                $('btn-view-more-page-'+newtviewmore).removeClassName('item-hide');
            $(btn).addClassName('item-hide');
        });
    });
}
function addMobileEventForShopBy(){
    if($$('.col-main #layered-nav-title')[0]){
        $$('.col-main #layered-nav-title')[0].next().addClassName('item-hide');
        $$('.col-main #layered-nav-title')[0].observe('click', function(event) {
            if(this.next().hasClassName('item-hide'))
                this.next().removeClassName('item-hide');
            else this.next().addClassName('item-hide');
        });
    }
}

function view_gift(){
    jQblvg(".popup-overlay").css('display', 'block');
    jQblvg(".offer_popup_gray").css('display', 'block');
}
function view1_gift(){
    jQuery(".popup-overlay_header").css('display', 'block').css('z-index', '9999');
}
