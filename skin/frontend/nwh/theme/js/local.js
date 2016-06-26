;(function($) {
    $.easing['BounceEaseOut'] = function(p, t, b, c, d) {
        if ((t/=d) < (1/2.75)) {
            return c*(7.5625*t*t) + b;
        } else if (t < (2/2.75)) {
            return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
        } else if (t < (2.5/2.75)) {
            return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
        } else {
            return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
        }
    };

    $(document).ready(function() {
        /*$('#mycarousel').jcarousel({
            animation: 1000,
            itemFallbackDimension: 100
        });
        $('#weekly_special').jcarousel({
            animation: 1000,
            itemFallbackDimension: 100
        });
        $('#ACCESSORIES').jcarousel({
            animation: 1000,
            itemFallbackDimension: 100
        });
        $('#topsellerjqurosal').jcarousel({
            animation: 1000,
            itemFallbackDimension: 100
        });
        $('.category_jcarousel').jcarousel({
            animation: 1000,
            itemFallbackDimension: 100
        });*/
        $('.jcarousel-skin-tango').jcarousel({
            animation: 1000,
            itemFallbackDimension: 100
        });
        $('#AdsBanner').jcarousel({
            animation: 3000,
            auto: 2,
            wrap: 'circular',
            scroll: 1,
            itemFallbackDimension: 100
        });

        /* Layered navigation */

        var allPanels = $('#narrow-by-list > dd ol').hide();

        $('#narrow-by-list > dt').click(function() {
            var dt = $(this);
            var dd = $(this).next();

            if (dt.hasClass('active')) {
                dt.removeClass('active');
                dd.removeClass('active');
                dd.find('ol').slideUp(300, 'linear');
                //dd.animate( { height: "hide" }, 500, 'linear' );
            } else {
                dt.addClass('active');
                dt.siblings('dt').removeClass('active');

                dd.addClass('active');
                dd.siblings('dd.active').find('ol').slideUp(300, 'linear');
                //dd.siblings('dd.active').animate( { height: "hide" }, 500, 'linear' );
                dd.find('ol').slideDown(300, 'linear');
                //dd.animate( { height: "show" }, 500, 'linear' );
            }

            return false;
        });
    });
})(jQuery);

