jQblvg(document).ready(function() {
    var s   = jQblvg("#order_summary_sidebar");
    var c   = jQblvg("#confidance");
    var d=  jQblvg(".footer-container");

    if(isMobile==0 && screen.width > 767){
        if(typeof  s != 'undefined' && typeof  c != 'undefined' && s.length && c.length )  {

         var j_width = jQblvg(window).width(), j_height = jQblvg(window).height();


         var pos = s.position();
         var stickermax  = jQblvg(document).outerHeight() - jQblvg(".footer-container").outerHeight() - s.outerHeight();
         var stickermax1 = jQblvg(document).outerHeight() - jQblvg(".footer-container").outerHeight() - s.outerHeight();
         jQblvg(window).scroll(function() {
             var windowpos = jQblvg(window).scrollTop();

             if (windowpos >= pos.top && windowpos < stickermax) {
                 s.attr("style", "");
                 s.addClass("stick");
                 c.removeClass();

                 var offset = jQblvg('.sc_left').position();
                 var w      = jQblvg('.sc_left').outerWidth();
                 jQblvg('.stick').css('left',offset.left + w + 13);

             } else if (windowpos >= stickermax) {
                 s.removeClass("stick");
                 //c.addClass("stickyC");
                 s.css({position: "absolute", top: stickermax + "px"});
             } else {
                 s.removeClass("stick");
                 c.removeClass();
             }

             if (windowpos >= stickermax1) {
                 //c.addClass("stickyC");
             }
             if(d.position().top < s.offset().top + s.outerHeight() ){

                 var overl=(s.offset().top + s.outerHeight() - d.position().top);
                 var temp_os=s.offset();temp_os.top=temp_os.top - overl-10;
                 s.offset(temp_os);

             }
             if(d.position().top < c.offset().top + c.outerHeight() ){
                 var overl=(c.offset().top + c.outerHeight() - d.position().top);
                 var temp_os=c.offset();temp_os.top=temp_os.top - overl-10;
                 c.offset(temp_os);

             }
         });
     }
    }
    

     jQblvg("input[name=radio_insurance]").change(function(){
        var selectedIns = jQblvg("input[name=radio_insurance]:checked").val();
        var url=jQblvg('input#url_ajax_checkout').val();
        if (selectedIns == "freight") {
        	url = url+'aitcheckout/cart/addInsurance';
            jQblvg.post(url, function(data) {
                document.location.reload(true);
            });
        } else {
        	url = url+'aitcheckout/cart/removeInsurance';
            jQblvg.post(url, function(data) {
                document.location.reload(true);
            });
        }
    });

    jQblvg('.view_bunusoffer').on("click", function() {
        jQblvg(".bonus_popup").css('display', 'block');
        jQblvg(".bonus_popup_gray").css('display', 'block');
    });
    jQblvg(".close_bonus_img").click(function() { // close the popup        
        jQblvg(".bonus_popup").css('display', 'none');
        jQblvg(".bonus_popup_gray").css('display', 'none');
    });
    jQblvg(".addbonusProd").click(function(){
        jQblvg(".bonus_popup").css('display', 'none');
        jQblvg(".bonus_popup_gray").css('display', 'none');
    });
    jQblvg('#co-shipping-method-form input').change(function() {
        jQblvg('#co-shipping-method-form').submit();
    });

    jQblvg('#co-shipping-method-form li label').click(function() {
        jQblvg(this).parents('li').first().find('input').click();//prop('checked', true);
    });

    jQblvg('.cart_qty_nav_up').click(function() {
        var input = jQblvg(this).parents('tr').first().find('.qty');
        var qty   = parseInt(input.val());
        qty++;

        input.val(qty);
    });
    jQblvg('.cart_qty_nav_down').click(function() {
        var input = jQblvg(this).parents('tr').first().find('.qty');
        var qty   = parseInt(input.val());
        qty--;
        if (qty < 0) {
            qty = 0
        }

        input.val(qty);
    });
});
