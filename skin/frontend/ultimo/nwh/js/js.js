var show_popup_firstload = 1;
function ampromo_show_bonus() {
    show_popup_firstload = 0;
    hide_freegift_sku();
    $('bonus-wellcome').show();
    $('gift-wellcome').hide();
    ampromo_popup();
    ampromo_init();
}
function ampromo_show_gift() {
    show_popup_firstload = 0;
    hide_bonus_sku();
    $('bonus-wellcome').hide();
    $('gift-wellcome').show();
    ampromo_check_initialization();
}
function hide_freegift_sku() {
    jQuery('.ampromo-item').each(function () {
        jQuery(this).show();
    });
    jQuery('.ampromo-item').each(function () {
        if (jQuery.inArray(jQuery(this).attr('data-product-sku'), freesku) != -1)
            jQuery(this).hide();
    });
    jQuery('.popup_right').hide();
    var bonus_qty = 1;
    bonus_qty = bonussku.length;
    jQuery('#ampromo-items').addClass('bonus-items-' + bonus_qty);

}
function showing_amasty_popup() {
    hide_bonus_sku();
    $('bonus-wellcome').hide();
    $('gift-wellcome').show();
    ampromo_init();
    ampromo_popup();
}
function hide_bonus_sku() {
    jQuery('.ampromo-item').each(function () {
        jQuery(this).show();
    });
    jQuery('.ampromo-item').each(function () {
        if (jQuery.inArray(jQuery(this).attr('data-product-sku'), bonussku) != -1)
            jQuery(this).hide();
    });
    jQuery('.popup_right').show();
    var bonus_qty = 1;
    bonus_qty = bonussku.length;
    jQuery('#ampromo-items').removeClass('bonus-items-' + bonus_qty);
}

function BalanceLineEqualHeight(group, li_grop, minheight) {
    minheight = (typeof minheight == 'undefined' ? 0 : minheight);
    var grid_col_num = 0;
    var random_num = Math.floor((Math.random() * 100) + 1);/*call a random number from 1 to 100*/
    li_grop.each(function (index) {
        if (grid_col_num > 0)
            return grid_col_num;
        thisWidth = jQuery(this).width();
        parentWidth = jQuery(this).parent().width();
        grid_col_num = parseInt(parentWidth / thisWidth);
    });

    var grid_line_num = 1;
    var grid_item_count = 1;
    /*put element in separate line*/
    group.each(function (index) {
        var modulo = grid_item_count % grid_col_num;
        if (modulo > 0)
            grid_line_num = (grid_item_count - modulo) / grid_col_num + 1;
        if (modulo == 0)
            grid_line_num = grid_item_count / grid_col_num;
        jQuery(this).addClass('line-' + random_num + '-' + grid_line_num);
        grid_item_count++;
    });
    /*Make equalHeight in each line and remove temporary class*/
    for (i = 1; i <= grid_line_num; i++) {
        equalHeight(jQuery(".line-" + random_num + '-' + i), minheight);
    }
}

var search_value = "";
function searchform_validate() {
    var value = jQuery("#search_ispbxi_").val();
    if (typeof value == 'string')
        search_value = value;
    if (search_value == "") {
        return false;
    }
}
function searchform_load(element) {
    if (search_value == "")
        element.previous().setAttribute('placeholder', 'This is required field *');
}
function searchform_onchange(element) {
    search_value = element.value;
    if (search_value == "")
        element.placeholder = "This is required field *";
}
jQuery(document).ready(function ($) {
    jQuery("input[type='text'],input[type='radio'],input[type='checkbox'],select").uniform({selectAutoWidth: false});
    jQuery("#billing\\:country_id").prev().css("background", "none");
    jQuery("#shipping\\:country_id").prev().css("background", "none");
    if (jQuery('.ezcoupon_message').length > 0) {
        jQuery('.messages').hide();

    }

});
function update_uniform() {
    jQuery("input[type='text'],input[type='radio'],input[type='checkbox'],select").uniform({selectAutoWidth: false});
    jQuery("#billing\\:country_id").prev().css("background", "none");
    jQuery("#shipping\\:country_id").prev().css("background", "none");
}
jQuery(document).ready(function ($) {
//    equalHeight(jQuery(".products-grid .product-image"));
    /* equalHeight(jQuery(".products-grid .price-box"));*/

    /*equalHeight Home page*/
    equalHeight(jQuery("#weekly_special .owl-item .price-box"));
    equalHeight(jQuery("#topsellerjqurosal .owl-item .price-box"));
    equalHeight(jQuery("#mycarousel .owl-item .price-box"));

    BalanceLineEqualHeight(jQuery(".products-grid .item .price-box"), jQuery(".products-grid .item"));
    //BalanceLineEqualHeight(jQuery(".shop-by-category .item .product-image img"),jQuery(".shop-by-category .item"));
    equalHeight(jQuery(".slide-category .products-grid .price-box"));

    jQuery("#products-list .item").each(function (index) { // Category view as: List
        jQuery(this).addClass('setting-h');
        equalHeight(jQuery(".setting-h .mobile-grid-half"));
        jQuery(this).removeClass('setting-h');
    });
    jQuery('.view_gift').on("click", function () {
        jQuery(".popup-overlay").css('display', 'block').css('z-index', '9999');
        jQuery(".offer_popup_gray").css('display', 'block');
        jQuery("#chk_popup").css('opacity', 0);
    });
    jQuery('.view_gift1').on("click", function () {
        jQuery(".popup-overlay_header").css('display', 'block').css('z-index', '9999');
        jQuery(".offer_popup_gray").css('display', 'block');
        jQuery("#chk_popup").css('opacity', 0);
    });
    jQuery(".chk_offer_close").click(function () { // close the popup
        jQuery(".popup-overlay").css('display', 'none');
        jQuery(".offer_popup_gray").css('display', 'none');
    });
    jQuery(".close_gift_img").click(function () { // close the popup
        jQuery(".popup-overlay").css('display', 'none');
        jQuery(".popup-overlay_header").css('display', 'none');
        jQuery(".offer_popup_gray").css('display', 'none');
    });
    jQuery(".popup-overlay,.popup-overlay_header").click(function (e) {
        if (e.target != this)
            return;
        jQuery(this).hide();
    })

    jQuery('.slide-category .jcarousel-skin-tango').owlCarousel({
        pagination: false,
        navigation: true,
        navigationText: false,
        itemsCustom: [[0, 2], [480, 2], [768, 3], [1024, 4]],
        responsiveRefreshRate: 50
    });

    jQuery(window).load(function () {
        if (jQuery(window).width() < 768) {
            jQuery('#brands .hadd_pink').click(function () {
                jQuery(this).next('.brand_link').slideToggle();
                jQuery(this).toggleClass('active');
            });
            jQuery('.shop-by-goal .block-red').click(function () {
                jQuery(this).next('.banner6_area').slideToggle();
                jQuery(this).toggleClass('active');
            });
            jQuery('.block-category-nav .block-title').click(function () {
                jQuery(this).next('.block-content').slideToggle();
                jQuery(this).toggleClass('active');
            });

            jQuery('.header-mobile .skip-contact').click(function () {
                window.location = jQuery(this).attr('href');
            });
            jQuery('.header-mobile .skip-location').click(function () {
                window.location = jQuery(this).attr('href');
            });
            jQuery('.header-mobile .skip-specials').click(function () {
                window.location = jQuery(this).attr('href');
            });

        }
    });

    /*setTimeout(function(){
     BalanceLineEqualHeight(jQuery(".shop-by-category .item .product-image img"),jQuery(".shop-by-category .item"),170);
     }, 2000);*/

    var cat_interval_eqh = setInterval(function () {
        var cat_image_complete = 1;
        jQuery(".shop-by-category .item .product-image img").each(function () {
            if (!this.complete) {
                cat_image_complete = 0;
            }
        });
        if (cat_image_complete == 1) {
            BalanceLineEqualHeight(jQuery(".shop-by-category .item .product-image img"), jQuery(".shop-by-category .item"));
            window.clearTimeout(cat_interval_eqh);
        }
    }, 300);

    jQuery('ul.messages > li').append('<p>Close</p>');
    jQuery('ul.messages > li > p').click(function () {
        jQuery(this).parents('ul.messages > li').hide();
    });
    init_menu_category();
    jQuery(".see-more").click(function () {
        var $this = jQuery(this);
        var parent = $this.parent();
        var flag = parent.attr("data-show");
        if (flag == 0) {
            var list = parent.find("li");
            list.each(function () {
                if (jQuery(this).hasClass("hide")) {
                    jQuery(this).removeClass("hide");
                    jQuery(this).addClass("show");
                }
            });
            $this.html("See less");
            parent.attr("data-show", '1');
        } else {
            var list = parent.find("li");
            list.each(function () {
                if (jQuery(this).hasClass("show")) {
                    jQuery(this).removeClass("show");
                    jQuery(this).addClass("hide");
                }
            });
            $this.html("More..");
            parent.attr("data-show", '0');
        }
    })

    /*store locator event*/
    if (jQuery(".locator-search-index")[0]) {
        jQuery(".loc-srch-res-list .locations").css({height: jQuery(".loc-srch-res-map").height() + "px"});
    }

    /**manufacture page*/
    jQuery('.splash-group-grid .product-name').show();
    jQuery('.splash-group-grid .product-name > a').css({"color": "black"});
    jQuery('.manufacture-logo').mouseover(function () {
        jQuery('.manufacture-logo-hover').hide();
        jQuery('.manufacture-logo').show();
        jQuery('.splash-group-grid .product-name').css({"color": "black"});

        var id = jQuery(this).attr('id').replace("imagelogo-", "");
        jQuery('#hoverlogo-' + id).show();
        jQuery('#image-link-' + id).show();
        jQuery('#image-link-' + id).css({"color": "red"});
        jQuery(this).hide();
    });

    jQuery('.manufacture-logo-hover').mouseout(function () {
        var id = jQuery(this).attr('id').replace("hoverlogo-", "");
        jQuery('.manufacture-logo-hover').hide();
        jQuery('.manufacture-logo').show();
        jQuery('.splash-group-grid .product-name > a').css({"color": "black"});

    });
});

function init_menu_category() {
    var header_mobile = jQuery('.header-mobile')[0];
    var mobile = false;
    if (typeof header_mobile != 'undefined') {
        mobile = true;
    }
    var ul_box = jQuery("#menu_parent_brand ul.level1.nav-submenu");
    ul_box.each(function () {
        var flag = false;
        var list = jQuery(this).find("li");
        if (list.length > 2) {
            flag = true;
            //alert(jQuery(this).find("li:first-child").html());
            list.each(function (index) {
                if (index > 1) {
                    if (!mobile)
                        jQuery(this).addClass("hide");
                }
            });
        }
        if (flag) {
            jQuery(this).append("<a href='javascript:void(0)' class='see-more'>More..</a>");
            jQuery(this).attr("data-show", "0");
        } else {
            jQuery(this).attr("data-show", "1");
        }

    });


}

// Update layout for mega menu on pc and tablet
jQuery(document).ready(function ($) {
    if (jQuery(window).width() >= 768) {
        jQuery('.mainmenucategory .nav-submenu.level0').masonry({
            itemSelector: '.nav-item.level1'
        });
    }
    remove_maincategory_link();

    if ($('#city').length > 0) {
        var registrationLoadingStarted = false;
        // $('#region_id').prop('readonly', true);
        $('#zip').prop('readonly', true);
        $('#city').autocomplete({
            serviceUrl: '/common/index/autopopulate',
            onSearchStart: function () {
                if (registrationLoadingStarted === false) {
                    registrationLoadingStarted = true;
                    $('#city').parent().append('<img src="/skin/frontend/ultimo/nwh/images/opc-ajax-loader.gif" class="geo-loading">');
                    if (location.pathname.indexOf('customer') > -1) {
                        $('.geo-loading').css({position: 'relative', right: '22px', 'margin-top': '3px'});
                    }
                }
            },
            onSearchComplete: function () {
                registrationLoadingStarted = false;
                $('.geo-loading').remove();
                searchComplete();
            },
            onSelect: function (suggestion) {
                if (suggestion.data.default_name == 'N/A') {
                    // $('#region_id').prop('readonly', false);
                    $('#zip').prop('readonly', false);
                    $('#city').val('');
                } else {
                    $('#zip').prop('readonly', false);
                    $('#region_id').val(suggestion.data.region_id);
                    $('#zip').val(suggestion.data.postcode);
                    $('#uniform-region_id span').text(suggestion.data.default_name);
                    //$('#region_id').prop('false', true);
                }


            }
        });
    }
    if ($('#billing\\:city').length > 0) {
        var started = false;
        //  $('#billing\\:region_id').prop('readonly', true);
        $('#billing\\:postcode').prop('readonly', true);
        $('#billing\\:city').autocomplete({
            serviceUrl: '/common/index/autopopulate',
            onSearchStart: function () {
                if (started === false) {
                    started = true;
                    $('#billing\\:city').parent().append('<img src="/skin/frontend/ultimo/nwh/images/opc-ajax-loader.gif" class="geo-loading">');
                    if (location.pathname.indexOf('customer') > -1) {
                        $('.geo-loading').css({position: 'relative', right: '22px', 'margin-top': '3px'});
                    }
                }
            },
            onSearchComplete: function () {
                started = false;
                $('.geo-loading').remove();
                searchComplete();
            },
            onSelect: function (suggestion) {
                if (suggestion.data.default_name == 'N/A') {
                    //  $('#billing\\:region_id').prop('readonly', false);
                    $('#billing\\:postcode').prop('readonly', false);
                    $('#billing\\:city').val('');
                } else {
                    $('#billing\\:postcode').prop('readonly', false);
                    $('#billing\\:region_id').val(suggestion.data.region_id);
                    $('#billing\\:postcode').val(suggestion.data.postcode);
                    $('#uniform-billing\\:region_id span').text(suggestion.data.default_name);
                }
            }
        });
    }
    if ($('#shipping\\:city').length > 0) {
        var shippingLoadingStarted = false;
        // $('#shipping\\:region_id').prop('readonly', true);
        $('#shipping\\:postcode').prop('readonly', true);
        $('#shipping\\:city').autocomplete({
            serviceUrl: '/common/index/autopopulate',
            onSearchStart: function () {
                if (shippingLoadingStarted === false) {
                    shippingLoadingStarted = true;
                    $('#shipping\\:city').parent().append('<img src="/skin/frontend/ultimo/nwh/images/opc-ajax-loader.gif" class="geo-loading">');
                    if (location.pathname.indexOf('customer') > -1) {
                        $('.geo-loading').css({position: 'relative', right: '22px', 'margin-top': '3px'});
                    }
                }
            },
            onSearchComplete: function () {
                shippingLoadingStarted = false;
                $('.geo-loading').remove();
                searchComplete();
            },
            onSelect: function (suggestion) {
                if (suggestion.data.default_name == 'N/A') {
                    // $('#shipping\\:region_id').prop('readonly', false);
                    $('#shipping\\:postcode').prop('readonly', false);
                    $('#shipping\\:city').val('');
                } else {
                    $('#shipping\\:postcode').prop('readonly', false);
                    $('#shipping\\:region_id').val(suggestion.data.region_id);
                    $('#shipping\\:postcode').val(suggestion.data.postcode);
                    $('#uniform-shipping\\:region_id span').text(suggestion.data.default_name);
                }
            }
        });
    }
});
function searchComplete() {
    jQuery('#tbl-autopopulate tr:last').css({background: '#E11E2F', color: '#FFF'});
    jQuery('#tbl-autopopulate tr:last td:first').css({'padding-left': '5px'});
}
function remove_maincategory_link() {
    jQuery('#nav .level-top.mainmenucategory a').first().attr('href', "#");
}
function move_similar_product() {
    var sml_margin = jQuery('.box-additional.grid12-3').offset().top - (jQuery('.product-secondary-column.grid12-3.custom-sidebar-right').offset().top + jQuery('.product-secondary-column.grid12-3.custom-sidebar-right').outerHeight());
    sml_margin = sml_margin - 30;
    if (sml_margin > 0) {
        jQuery('.box-additional.grid12-3').css("position", "relative");
        jQuery('.box-additional.grid12-3').css("margin-top", "-" + sml_margin + "px");
    } else {
        jQuery('.box-additional.grid12-3').css("position", "relative");
        jQuery('.box-additional.grid12-3').css("margin-top", sml_margin + "px");
    }
}
function check_similar_product() {
    var sml_margin = jQuery('.box-additional.grid12-3').offset().top - (jQuery('.product-secondary-column.grid12-3.custom-sidebar-right').offset().top + jQuery('.product-secondary-column.grid12-3.custom-sidebar-right').outerHeight());
    if (sml_margin > 20) {
        jQuery('.box-additional.grid12-3').css("position", "relative");
        jQuery('.box-additional.grid12-3').css("margin-top", "-" + sml_margin + "px");
    }
    if (sml_margin < 0) {
        var _margintop = jQuery('.box-additional.grid12-3').css("margin-top").replace("px", "");
        sml_margin = sml_margin * (-1) + parseInt(_margintop);
        jQuery('.box-additional.grid12-3').css("position", "relative");
        jQuery('.box-additional.grid12-3').css("margin-top", sml_margin + "px");
    }
}

