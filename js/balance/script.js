function showMenuClick(){
    $j('#sidenav > li > a.show-cat').click(function() {
        $j('#sidenav li ul').slideUp();
        if (!$j(this).hasClass('active')) {
            $j(this).next().slideToggle();
            $j('#sidenav li a.show-cat').removeClass('active');
            $j(this).addClass('active');
        } else if ($j(this).hasClass('active')) {
            $j(this).removeClass('active');
        }
    });
    $j('#sidenav > li > ul > li > a.show-cat').click(function() {
        $j('#sidenav li ul li ul').slideUp();
        if (!$j(this).hasClass('active')) {
            $j(this).next().slideToggle();
            $j('#sidenav li ul li a.show-cat').removeClass('active');
            $j(this).addClass('active');
        } else if ($j(this).hasClass('active')) {
            $j(this).removeClass('active');
        }
    });
}
function counpondAjax(){
    jQuery('.addcoupon').live('click',function(){
                 //var rr = jQuery('#p_method_pay').is(':checked');
                    //if(rr){
        var data1 = new Object();
        data1.giftcoupon = jQuery('#coupon_code1').val();
        jQuery('#msg').hide();
        jQuery('#coupon_code1').removeClass('validation-failed');
        jQuery('#advice-required-entry-coupon_code1').hide();
        if(jQuery('#coupon_code1').val() == ''){
            jQuery('#coupon_code1').addClass('validation-failed');
            jQuery('#advice-required-entry-coupon_code1').show();
            return false;}
        jQuery('#coupon-please-wait').show();
        var urlCopond = jQuery('input#url_copond_code').val();
        jQuery.ajax({
            url: urlCopond+'aitcheckout/cart/customcouponPost',
            dataType: 'json',
            data: data1, 
            type:'POST',
            success: function(data) {
                               document.location.reload(true);
                jQuery('#coupon-please-wait').hide();
                if(data.status == 'SUCCESS'){
                    
                    if(jQuery('#checkout-review-table-wrapper')){
                        jQuery('#checkout-review-table-wrapper').replaceWith(data.review);
                       
                    }
                    if(data.msg){
                        jQuery('#msg').html(data.msg);
                        jQuery('#msg').show();
                      }
                }else{
                    if(jQuery('#checkout-review-table-wrapper')){
                        jQuery('#checkout-review-table-wrapper').replaceWith(data.review);
                        
                    }
                    if(data.msg){
                        jQuery('#msg').html(data.msg);
                        jQuery('#msg').show();
                      }
                }
            }
        });
                   // }
    });

    jQuery('.cancelcoupon').live('click',function(){
        var data1 = new Object();
        data1.remove = 1;
        jQuery('#msg').hide();
        if(jQuery('#coupon_code1').val() == ''){return false;}
        jQuery('#coupon-please-wait').show();
        var urlCopond = jQuery('input#url_copond_code').val();
        jQuery.ajax({
            url: urlCopond+'aitcheckout/cart/customcouponPost',
            dataType: 'json',
            data: data1, 
            type:'POST',
            success: function(data) {
            document.location.reload(true);
                jQuery('#coupon-please-wait').hide();
                if(data.status == 'SUCCESS'){
                    
                    if(jQuery('#checkout-review-table-wrapper')){
                        jQuery('#checkout-review-table-wrapper').replaceWith(data.review);
                        
                    }
                    if(data.msg){
                        jQuery('#msg').html(data.msg);
                        jQuery('#msg').show();
                      }
                }else{
                    if(jQuery('#checkout-review-table-wrapper')){
                        jQuery('#checkout-review-table-wrapper').replaceWith(data.review);
                        
                    }
                    if(data.msg){
                        jQuery('#msg').html(data.msg);
                        jQuery('#msg').show();
                      }
                
                    }
            }
        });
    });
}
function counpondOnepage() {
    jQuery('.addcoupon').live('click',function(){
        var data1 = new Object();
        data1.giftcoupon = jQuery('#coupon_code1').val();
        jQuery('#msg').hide();
        jQuery('#coupon_code1').removeClass('validation-failed');
        if(jQuery('#coupon_code1').val() == ''){
            jQuery('#coupon_code1').addClass('validation-failed');
            return false;}
        jQuery('#coupon-please-wait').show();
        var urlAjax = jQuery('input#url_copond_onepage').val();
        jQuery.ajax({
            url: urlAjax+'aitcheckout/cart/customcouponPost',
            dataType: 'json',
            data: data1, 
            type:'POST',
            success: function(data) {
                               location.reload();
                jQuery('#coupon-please-wait').hide();
                if(data.status == 'SUCCESS'){
                    
                    if(jQuery('#checkout-review-table-wrapper')){
                        jQuery('#checkout-review-table-wrapper').replaceWith(data.review);
                       
                    }
                    if(data.msg){
                        jQuery('#msg').html(data.msg);
                        jQuery('#msg').show();
                      }
                }else{
                    if(jQuery('#checkout-review-table-wrapper')){
                        jQuery('#checkout-review-table-wrapper').replaceWith(data.review);
                        
                    }
                    if(data.msg){
                        jQuery('#msg').html(data.msg);
                        jQuery('#msg').show();
                      }
                }
            }
        });
    });

    jQuery('.cancelcoupon').live('click',function(){
        var data1 = new Object();
        data1.remove = 1;
        jQuery('#msg').hide();
        if(jQuery('#coupon_code1').val() == ''){return false;}
        jQuery('#coupon-please-wait').show();
        var urlAjax = jQuery('input#url_copond_onepage').val();
        jQuery.ajax({
            url: urlAjax+'aitcheckout/cart/customcouponPost',
            dataType: 'json',
            data: data1, 
            type:'POST',
            success: function(data) {
            location.reload();
                jQuery('#coupon-please-wait').hide();
                if(data.status == 'SUCCESS'){
                    
                    if(jQuery('#checkout-review-table-wrapper')){
                        jQuery('#checkout-review-table-wrapper').replaceWith(data.review);
                        
                    }
                    if(data.msg){
                        jQuery('#msg').html(data.msg);
                        jQuery('#msg').show();
                      }
                }else{
                    if(jQuery('#checkout-review-table-wrapper')){
                        jQuery('#checkout-review-table-wrapper').replaceWith(data.review);
                        
                    }
                    if(data.msg){
                        jQuery('#msg').html(data.msg);
                        jQuery('#msg').show();
                      }
                
                    }
            }
        });
    });
}
function toggleMenu(el, over)
{
    if (Element.childElements(el)) {
    var uL = Element.childElements(el)[1];
    var iS = true;
    }
    if (over) {
        Element.addClassName(el, 'over');
        
        if(iS){ uL.addClassName('shown-sub')};
    }
    else {
        Element.removeClassName(el, 'over');
        if(iS){ uL.removeClassName('shown-sub')};
    }
}
(function($) {
    var cartSpeedIn  = 100;
    var cartSpeedOut = 50;
    var cartEffect   = 'linear';
    function cartHoverOver()
    {
        var cartMini = $('.cart_popup');
        if (cartMini.length) {
            cartMini.stop();
            cartMini.attr('style', 'opacity: 0').show();
            cartMini.hide();
           
            cartMini.css({'opacity': 1});
            cartMini.slideDown(cartSpeedIn, cartEffect);
        }
    }
    function cartHoverOut()
    {
        var cartMini = $('.cart_popup');
        if (cartMini.length) {
            cartMini.stop();

            cartMini.fadeTo(cartSpeedOut, 0, cartEffect, function() {
                $(this).hide();
            });
        }
    }
    function toggleRememberMepopup(event){
        if($('remember-me-popup')){
            var viewportHeight = document.viewport.getHeight(),
                docHeight      = $$('body')[0].getHeight(),
                height         = docHeight > viewportHeight ? docHeight : viewportHeight;
            $('remember-me-popup').toggle();
            $('window-overlay').setStyle({ height: height + 'px' }).toggle();
        }
        Event.stop(event);
    }
    $(document).ready(function() {
        $('#header-cart-mini').hover(
            function () {
                cartHoverOver();
            },
            function () {
                cartHoverOut();
            }
        );
        jQuery('.closepopup1').on('click', function() {
            jQuery.fancybox.close();
            return false;
        });

        $$('.remember-me-popup-close').each(function(element){
            Event.observe(element, 'click', toggleRememberMepopup);
        })
        $$('#remember-me-box a').each(function(element) {
            Event.observe(element, 'click', toggleRememberMepopup);
        });
        var t = jQuery(".price-box").children().prop("tagName");
        if(t == 'SPAN'){
            jQuery(".prod_pricing_right table tr td").height(29);
        }else if(t == 'P'){
            jQuery(".prod_pricing_right table tr td").height(129);
        }
        jQuery(document).on("click", ".OVERVIEWTAB1", function() {
            jQuery('#tab1_detl').show();
            jQuery('#tabs-2').hide();
            
            jQuery('.DESCRIPTIONTAB1').removeClass('tab_link_current');
            jQuery('.DESCRIPTIONTAB1').addClass('tab_link');
            
            jQuery('.OVERVIEWTAB1').addClass('tab_link_current');
        });
        jQuery(document).on("click", ".DESCRIPTIONTAB1", function() {
            jQuery('.OVERVIEWTAB1').removeClass('tab_link_current');
            jQuery('.DESCRIPTIONTAB1').addClass('tab_link_current');
            jQuery('.OVERVIEWTAB1').addClass('tab_link');
            jQuery('#tab1_detl').hide();
            jQuery('#tabs-2').show();
        });
        var form = $('#shop-assistant');
	    form.find('select').on('change', function() {
	        var next = $(this).parents('li').first().next().find('select');
	        if (next.length) {
	            loader.show();
	            var data = form.serialize() + '&current=' + $(this).attr('rel');

	            form.find('select').prop("disabled", true);
	            next.html('<option>Loading ...</option>')
	            $.ajax({
	                type    : "POST",
	                cache   : false,
	                url     : jQuery('input#form-ajax-action').val(),
	                data    : data,
	                success : function (result) {
	                    form.find('select').prop("disabled", false);
	                    var data = eval('(' + result + ')');
	                    next.html(data.select);

	                    loader.hide();
	                }
	            });
	        } else {
	            form.submit();
	        }
	    });
	    var LastLink = jQuery('.bread_crumbs_txt a:last').attr('href');
        jQuery('.bread_crumbs a.link_back').attr('href', LastLink);
    });
})(jQblvg);