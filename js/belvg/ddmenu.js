;
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_DropDownMenu
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */
(function($){
    $.fn.ddmenu = function(options)
    {
        var overlay = $('#overlay-nav');
        var nav     = this;

        /**
         * Default settings
         */
        var settings = $.extend({
            fly:           true,
            opacity:       '0.5',
            transitionIn:  'fade',
            transitionOut: 'fade',
            speedIn:        300,
            speedOut:       200,
            effectLinear:  'linear',
            effectIn:      'easeOutBack',
            effectOut:     'easeInBack',
            hoverOverDelay: 300,
            hoverOutDelay:  100,
            mobileWindowHeight: 770
        }, options);
        settings.overlaySpeedIn = Math.round(settings.speedIn / 2);

        var methods = {
            /**
             * Event to open the menu
             */
            hoverOver: function(li)
            {
                var sub = $(li).find(".sub");
                if (sub.length) {
                    methods.overlayShow();
                    sub.stop().parent('li').css('z-index','10703');
                    sub.stop().parent('li').siblings().css('z-index','10702');
                    sub.attr('style', 'opacity:0').show();
                    methods.resize(li);
                    sub.hide();

                    switch (settings.transitionIn) {
                        case 'fade':
                            sub.fadeTo(settings.speedIn, 1, settings.effectIn, function(){
                                $(this).show();
                            });
                            break;
                        case 'slide':
                            sub.css({'opacity':1});
                            sub.slideDown(settings.speedIn, settings.effectIn);
                            break;
                        default:
                            sub.css({'opacity':1});
                            sub.show();
                    }
                }

                $(li).addClass('active');
            },

            /**
             * Event to close the menu
             */
            hoverOut: function(li)
            {
                var sub = $(li).find(".sub");
                if (sub.length) {
                    sub.stop().parent('li').css('z-index','10702');
                    switch (settings.transitionOut) {
                        case 'fade':
                            sub.fadeTo(settings.speedOut, 0, settings.effectOut, function(){
                                $(this).hide();
                            });
                            break;
                        case 'slide':
                            sub.slideUp(settings.speedOut, settings.effectOut);
                            break;
                        default:
                            sub.hide();
                    }
                    methods.overlayHide();
                }

                $(li).removeClass('active');
            },

            hoverOverMobile: function(li)
            {
                var sub = $(li).find(".sub");
                if (sub.length) {
                    sub.css({'opacity':1});
                    sub.show();
                }
            },

            hoverOutMobile: function(li)
            {
                var sub = $(li).find(".sub");
                if (sub.length) {
                    sub.hide();
                }
            },

            /**
             * Event to show overlay
             */
            overlayShow: function()
            {
                if (overlay.length) {
                    overlay.css('z-index', 10700);
                    overlay.css(methods.overlayHeight()).stop().fadeTo(settings.overlaySpeedIn, settings.opacity, settings.effectLinear, function() {
                        $(this).show();
                    });
                }
            },

            /**
             * Event to hide overlay
             */
            overlayHide: function()
            {
                if (overlay.length) {
                    overlay.stop().fadeTo(settings.speedOut, 0, settings.effectLinear, function() {
                        overlay.css('z-index', '');
                        $(this).hide();
                    });
                }
            },

            overlayHeight: function()
            {
                //console.log(this);
                if (settings.overlayHeight) {
                    var topPos = nav.offset().top + nav.outerHeight();
                    return {'height': $(document).height(), 'top': topPos, 'position': 'absolute'}
                } else {
                    return {'height': $(window).height(), 'top': 0, 'position': 'fixed'}
                }
            },

            /**
             * Change submenu properties
             */
            resize: function(li)
            {
                methods.setNavWidth(li);
                methods.setLeftPos(li);
                methods.setNavHeight(li);
            },

            /**
             * Change submenu width
             */
            setNavWidth: function(li)
            {
                if (settings.stretched) {
                    var rowWidth = nav.innerWidth()-20;
                } else {
                    var rowWidth = 'initial';
                }

                $(li).find(".sub").css({'width' : rowWidth});
            },

            /**
             * Change submenu height
             */
            setNavHeight: function(li)
            {
                var columnHeight = 0;
                $(li).find(".sub > dl > dd").each(function() {
                    if (columnHeight < $(this).outerHeight()) {
                        columnHeight = $(this).outerHeight()
                    }
                });
                $(li).find(".sub > dl > dd").each(function() {
                    $(this).css({'height' : columnHeight});
                    $(this).find('> ul').css({'height' : columnHeight - 40});
                });

            },

            /**
             * Change position submenu (depending on the width)
             */
            setLeftPos: function(li)
            {
                var liWidth     = parseInt($(li).find(".sub").width());
                var liPos       = liWidth + parseInt($(li).find(".sub").offset().left);
                var navWidth    = parseInt(nav.width()) + parseInt(nav.css('padding-left')) + parseInt(nav.css('padding-right'));
                var navPos      = navWidth + parseInt(nav.offset().left) - 2;

                if (liPos > navPos) {
                    if (liWidth > navWidth) {
                        // center
                        $(li).find(".sub").css('left', navPos - liPos + 1 + parseInt((liWidth-navWidth)/2));
                    } else {
                        // right
                        $(li).find(".sub").css('left', navPos - liPos);
                    }
                }
            }
        };

        $('#overlay-nav').css({'opacity':0}).hide();

        return this.each(function() {
            //var $this       = $(this);

            /**
             * Initialization Menu
             */
            var li_cache, over = false;
            nav.find("li.level0").hover(
                function (e) {
                    if ($(window).width() > settings.mobileWindowHeight) {
                        var $li     = $(this), speed;

                        if (li_cache === this && over) {
                            $.doTimeout("hoverOut");
                            return;
                        }

                        if (over) {
                            $.doTimeout("hoverOut", true);
                            speed = 0;
                        } else {
                            $.doTimeout("hoverOut");
                            speed = settings.hoverOverDelay;
                        }

                        $.doTimeout("hoverIn", speed, function() {
                            over    = true;
                            methods.hoverOver($li);
                        });
                    }
                }, function(e) {
                    if ($(window).width() > settings.mobileWindowHeight) {
                        var $li = $(this);

                        $.doTimeout("hoverIn" );
                        $.doTimeout("hoverOut", settings.hoverOutDelay, function(){
                            over = false;
                            methods.hoverOut($li);
                        });
                    }
                }
            );
            
            /*nav.find("li.level0").click(
                function (e) {
                    if ($(window).width() <= settings.mobileWindowHeight) {
                        var $li = $(this);
                        if ($li.hasClass('active')) {
                            methods.hoverOutMobile($li);
                        } else {
                            methods.hoverOverMobile($li);
                        }
                    }
                }
            )*/

            nav.find("li.sub-menu").find(".sub > table td:last").addClass('last');

            /**
             * Initialization Floating Menu
             */
            if (settings.fly) {
                var nav_top_pos = nav.offset().top;
                $(window).scroll(function() {
                    if ($(window).scrollTop() > nav_top_pos) {
                        if (!nav.hasClass('ddmenu_fly')) {
                            nav.addClass('ddmenu_fly');

                            //var top       = $('.header-language-background');
                            var topHeight = 0;//top.outerHeight();
                            var flyBG     = $('#top-nav-ddmenu_fly_bg');
                            nav.css({
                                opacity: 0,
                                top: topHeight - 50,
                            });
                            flyBG.css({
                                opacity: 0,
                                top: topHeight - 50,
                            });

                            flyBG.show();

                            nav.animate(
                                {
                                    opacity: 0.7,
                                    top: topHeight
                                },
                                400,
                                'linear',
                                function() {}
                            );
                            flyBG.animate(
                                {
                                    opacity: settings.flyOpacity * 2 / 3,
                                    top: topHeight
                                },
                                400,
                                'linear',
                                function() {}
                            );

                            setTimeout(
                                function() {
                                    nav.animate(
                                        {
                                            opacity: 1,
                                        },
                                        150,
                                        'linear',
                                        function() {}
                                    );
                                    flyBG.animate(
                                        {
                                            opacity: settings.flyOpacity,
                                        },
                                        150,
                                        'linear',
                                        function() {}
                                    );
                                },
                                400
                            );
                        };
                    } else {
                        if (nav.hasClass('ddmenu_fly')) {
                            $('#top-nav-ddmenu_fly_bg').hide();
                            nav.removeClass('ddmenu_fly');
							nav.css('top', '');
                        }
                    }
                });
            }
        });

    };

})(jQblvg);


/*var $j = jQblvg;
var bp = {
    xsmall : 479,
    small  : 599,
    medium : 770,
    large  : 979,
    xlarge : 1199
}
$j(document).ready(function () {

    // ==============================================
    // Header Menus
    // ==============================================

    var nav = $j('#nav');

    // ----------------------------------------------
    // Top Menus

    var MenuManagerState = {
        TOUCH_SCROLL_THRESHOLD: 20,

        touchStartPosition: null,

        shouldCancelTouch: function() {
            if(!this.touchStartPosition) {
                return false;
            }

            var scroll = $j(window).scrollTop() - this.touchStartPosition;
            return Math.abs(scroll) > this.TOUCH_SCROLL_THRESHOLD;
        }
    };

    var pointerEvent = 'touchend';
    // If device has implemented touch/click agnostic event, use it instead of "click"
    if (window.navigator.pointerEnabled) {
        pointerEvent = 'pointerdown';
    } else if (window.navigator.msPointerEnabled) {
        pointerEvent = 'MSPointerDown';
    }

    nav.find('a.has-children.level0').on(pointerEvent,function (event) {
        //scroll occurred, cancel event
        if(MenuManagerState.shouldCancelTouch()) {
            return;
        }

        // If mouse is being used on large viewport, use native hover state
        if (window.navigator.msPointerEnabled
            && event.originalEvent.pointerType == 'mouse'
            && Modernizr.mq("screen and (min-width:" + (bp.medium + 1) + "px)")
        ) {
            $j(this).data('has-touch', false);
            return;
        }
        $j(this).data('has-touch', true);
        var elem = $j(this).parent();

        var alreadyExpanded = elem.hasClass('menu-active');

        nav.find('li.level0').removeClass('menu-active');

        // Collapse all active sub-menus
        nav.find('.sub-menu-active').removeClass('sub-menu-active');

        if (!alreadyExpanded) {
            elem.addClass('menu-active');
        }

        event.preventDefault();
    }).on('click', function (event) {
        var elem = $j(this);
        if (elem.data('has-touch')) {
            elem.data('has-touch', false);
            event.preventDefault();
            return;
        }

        if(Modernizr.mq("screen and (max-width:" + bp.medium + "px)")) {
            var elem = $j(this).parent();

            var alreadyExpanded = elem.hasClass('menu-active');

            nav.find('li.level0').removeClass('menu-active');

            // Collapse all active sub-menus
            nav.find('.sub-menu-active').removeClass('sub-menu-active');

            if (!alreadyExpanded) {
                elem.addClass('menu-active');
            }

            event.preventDefault();
        }
    }).on('touchstart', function(event) {
        $j(this).data('has-touch');
        MenuManagerState.touchStartPosition = $j(window).scrollTop();
    });

    // ----------------------------------------------
    // Sub Menus

    nav.find('li.level1 a.has-children').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var elem = $j(this).parent();

        // Check if sub-menu is open
        var isSubMenuActive = elem.hasClass('sub-menu-active') ? 1 : 0;

        // On smaller screens, allow multiple sibling sub-menus to show at once,
        // but this is a large touch device, avoid multiple sub-menus showing at once.
        if (Modernizr.mq("screen and (min-width:" + (bp.medium + 1) + "px)")) {
            elem
                .siblings('.sub-menu-active')
                .removeClass('sub-menu-active')
                .find('.sub-menu-active')
                .removeClass('sub-menu-active');
        }
        if (isSubMenuActive) {
            elem.removeClass('sub-menu-active');
        } else {
            elem.addClass('sub-menu-active');
        }
    });
});*/