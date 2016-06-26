var $jQ      = jQuery.noConflict();
var ulHeight = 0;
var selectUl = null;
$jQ(".mgkviewlightbox").click(function(e){
    thisMgkviewlightbox = $jQ(this);
    /* Close Current Quickview ? */
    if(selectUl != null && selectUl.offset().top != $jQ(this).parent().parent().offset().top){                    
        $jQ('#mc_quickview').hide();
        selectUl.next().animate({"margin-bottom":"0px"},{
            duration: 500,
            complete: function() {                       
                $jQ('html,body').animate({
                    scrollTop: selectUl.next().offset().top - ($jQ(window).height()-530)/2
                }, 500);   
            }
        });
    }
    if(selectUl == null || selectUl.offset().top != $jQ(this).parent().parent().offset().top){
        selectUl = $jQ(this).parent().parent();
        ulHeight = selectUl.height();
        $jQ('#mc_quickview #magikloading').show();
        $jQ('html,body').animate({
            scrollTop: selectUl.next().offset().top - ($jQ(window).height()-530)/2
        }, 1000);
        selectUl.next().animate({"margin-bottom":"461px"},{
            duration: 1000,
            complete: function() {
                $jQ('#mc_quickview').show();
                $jQ('#mcquickview').show();
                $jQ('#mc_quickview #magikloading').show();
                $jQ('#mc_quickview').offset({top:selectUl.offset().top + ulHeight , left:0});
          
            }
        });
    } else {
        selectUl = $jQ(this).parent().parent();
        ulHeight = selectUl.height();
        $jQ('html,body').animate({
            scrollTop: selectUl.next().offset().top - ($jQ(window).height()-530)/2
        }, 1000); 
        $jQ('#mc_quickview').show();
        $jQ('#mcquickview').show();
        $jQ('#mc_quickview #magikloading').show();
        showQuickView(thisMgkviewlightbox,e);                                                                 
    }    
    $jQ(this).click(function() { return false; });
    e.preventDefault();                                     
    $jQ('#mc_quickview').css("left", $jQ(this).parent().offset().left + $jQ(this).parent().width()/2);
});
jQuery(document).ready(function() {

    jQuery(document).on("click", ".OVERVIEWTAB", function() {
        jQuery('#tabs-1').show();
        jQuery('#tabs-2').hide();
    
        jQuery('.DESCRIPTIONTAB').removeClass('tab_link_current');
        jQuery('.DESCRIPTIONTAB').addClass('tab_link');
    
        jQuery('.OVERVIEWTAB').addClass('tab_link_current');
    });
    jQuery(document).on("click", ".DESCRIPTIONTAB", function() {
        jQuery('.OVERVIEWTAB').removeClass('tab_link_current');
        jQuery('.DESCRIPTIONTAB').addClass('tab_link_current');
        jQuery('.OVERVIEWTAB').addClass('tab_link');
        jQuery('#tabs-1').hide();
        jQuery('#tabs-2').show();
    });
    jQuery(document).on("click", ".readmoredesc", function() {
        var collapse_content_selector = jQuery(this).attr('href');					
        var toggle_switch = jQuery(this);
        jQuery(collapse_content_selector).slideToggle('slow',function(){
            if(jQuery(this).css('display')=='none'){
                //change the button label to be 'Show'
                toggle_switch.text('Read More');
            }else{
                //change the button label to be 'Hide'
                toggle_switch.text('Hide');
            }
        });
    });


    var pbBaseUrl = $jQ('input#pb_url').val()+'productbook/';
    var pbClass = $jQ('input#pbClass').val();
    //var pb_caption = '<?php echo $this->getPBConfigByName('pb_caption') ?>';
    var pb_caption = '<img src="'+$jQ('input#pb_caption').val()+'">';
    if (jQuery.trim(pb_caption) == '') {
        pb_caption = 'Quickshop';
    }
    var prod_image = jQuery(pbClass);
    /*Create the wrapper*/
    prod_image.each(function() {
        jQuery(this).find('img').wrap('<div class="pb_wrapper" />').after('<div class="pb_caption"><a href="#" class="pb_btn"></a></div>');
    });
    /*Set the wrapper height/width*/
    var wrap_h = prod_image.height();
    var wrap_w = prod_image.width();
    //jQuery(".pb_wrapper").css({'height': wrap_h, 'width': wrap_w});

    /*Hover effect*/
    jQuery('.pb_wrapper').hover(
        function(){
            jQuery(this).find('img').animate({opacity: "1.6"}, 300);		
            jQuery(this).find('.pb_caption').animate({top:"10px"}, 300);			
            
        }, function(){
            jQuery(this).find('img').animate({opacity: "1.0"}, 300);					
            jQuery(this).find('.pb_caption').animate({top:"85px"}, 100);
            
        }		
    );

    jQuery(document).on("click", ".pb_caption", function(e) {
        e.preventDefault();
        var ID = jQuery(this).closest(".EnableQuickView").attr('id').split("pb_item_");
        var ulid = jQuery(this).closest(".EnableQuickView").attr('data-link');
        var iddd = '#'+ulid;
    
        if (!ID[1] || ID[1] == "" || ID[1] == "undefined") {
            return false;
        }
        var _this = jQuery(this);
        var org_caption = _this.find(".pb_btn").text();


        var url = pbBaseUrl+"index/view" +"?id=" + ID[1];
        jQuery.fancybox.open({
            padding :0,
            href    : url,
            autoDimensions: false,
            'autoSize':false,
            'width'         :   900,
            'height'        :   600,
            type    : 'iframe'
        });

        /*jQuery.ajax({
            type: "POST",
            url: pbBaseUrl+"index",
            data: "id="+ID[1],
            beforeSend: function() {
                        
                // _this.find(".pb_btn").text("Loading...");
                // jQuery(iddd).empty().append('<img height="50" width="50" src="'+$jQ('input#pb_anima').val()+'" >');
                // jQuery(iddd).addClass('loadingQuickview');
                // jQuery(iddd).show();
                var data = '<img height="50" width="50" src="'+$jQ('input#pb_anima').val()+'" >';
                jQuery.fancybox(data, {
                    // API options here
                });

            }
        }).done(function(msg){
             jQuery.fancybox(msg, {
                    // API options here
                });
            // _this.find(".pb_btn").text(org_caption);
            // jQuery(iddd).removeClass('loadingQuickview');
            // jQuery(iddd).hide();
		
            // jQuery(iddd).empty().append(msg);
                    
            // if (!jQuery(iddd).is(":visible"))
            // {
            //     jQuery(iddd).fancybox();
                        
            // }
            // $jQ('html,body').animate({
            //     scrollTop: jQuery(iddd).offset().top - (jQuery(window).height()-530)/2
            // }, 1000);
        });*/
 
    });

    jQuery(document).on("click", ".btn-cart22", function() {
        var _this = jQuery(this);
        var ID = _this.attr("ID").split("pb_cart_");
        var pid = ID[1];
        var qty = jQuery('#qty_'+pid).val();

        _this.find("span span").html('Adding...');
        jQuery.ajax({
            type: "POST",
            url: pbBaseUrl+"addtocart",
            dataType: "json",
            data: 'product='+pid+'&qty='+qty,
        }).done(function(data) {
            try {
                if (data.status == "success") {
                    _this.find("span span").html('In cart');
                    jQuery(".pb_crt_cnt").html(data.cart_qty);
                    jQuery(".pb_crt_total").html(data.cart_total);
                    var url_redirect = $jQ('input#pb_url').val()+'recommends';
                    window.location.href = url_redirect;
                } else if (data.status == "redirect") {
                    window.location.href = data.url;
                } else {
                    //alert(data.msg);
                    alert('The item is no longer available');
                }
            } catch(e) {
                //
            }
        });
    });

    //if close button is clicked
    jQuery(document).on("click", '.pb_close', function(e) {
        //Cancel the link behavior
        e.preventDefault();
        closePB();
    });    

    //if mask is clicked
    jQuery(document).on("click", '#mask', function(e) {
        e.preventDefault();
        closePB();
    });

    jQuery(document).keyup(function(e) {
        if (e.keyCode == 27) {
            closePB();
        }
    });

    function closePB() {
        jQuery(".slidmain").hide(1100,function(){
            setTimeout( "jQuery('.slidmain').empty();",1000);
        });
    }
});