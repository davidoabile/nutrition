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
 * @package    Belvg_ProductZoomLight
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

var mediaZoomer;
var zoomCloudFancy = Class.create();

(function($) {
    zoomCloudFancy.prototype = {
        initialize: function(config)
        {
            this.config = Object.extend({
                cloudZoom : {},
                fancyBox  : {},
                tagBigImgLink     : '.mediaZoomerMain',
                tagBigImg         : '.mediaZoomerMain-image',
                tagMoreViewsBlock : '.mediaZoomerGallery-wrapper',
                tagMoreViews      : '.mediaZoomerGallery',
                tagMoreViewLinks  : '.mediaZoomerGallery a'
            }, config);

            $(this.config.tagBigImgLink).absoluteClick();

            this.init();
        },

        init: function()
        {
            var $this  = this;
            var big    = $($this.config.tagBigImgLink);
            var thumbs = $($this.config.tagMoreViewLinks);

            if ($this.config.fancyBox.enabled) {
                big.addClass('fancybox').attr('data-fancybox-group', 'thumb');
                thumbs.each(function() {
                    $(this).addClass('fancybox');
                    if ($this.config.cloudZoom.enabled && $(this).attr('href') != big.attr('href')) {
                        $(this).attr('data-fancybox-group', 'thumb');
                    }
                });

                $(".fancybox").fancybox($this.config.fancyBox);
                
                if ($this.config.cloudZoom.enabled) {
                    thumbs.click(function() {
                        $(this).attr('data-fancybox-group', '').parent('li').siblings().find('a').attr('data-fancybox-group', 'thumb');
                    });
                }
            }

            if ($this.config.cloudZoom.enabled) {
                big.addClass('cloud-zoom').attr('rel', '');
                thumbs.addClass('cloud-zoom-gallery');
                $('.cloud-zoom, .cloud-zoom-gallery').CloudZoom($this.config.cloudZoom);
            }
        },

        refresh: function(productId)
        {
            var $this  = this;
            var big    = $($this.config.tagBigImgLink);
            var thumbs = $($this.config.tagMoreViewLinks);

            big.attr('id', 'zoom' + productId);

            try {
                thumbs.each(function() {
                    var colorSwatchRel = eval('(' + $(this).attr('rel') + ')');
                    if (typeof colorSwatchRel != 'undefined') {
                        $(this).attr('href', colorSwatchRel.largeimage);

                        if ($this.config.cloudZoom.enabled) {
                            $(this).attr('rel', "useZoom: 'zoom" + productId + "', smallImage: '" + colorSwatchRel.smallimage + "' ");
                        }

                        if ($this.config.fancyBox.enabled) {

                        }
                    }
                });
            } catch(e) {
            
            }

            this.init();
        }
    };

    $.fn.absoluteClick = function() {
        var elnt = $(this);
        $('.product-img-box').click(function(pos) {
            var a = parseInt(elnt.offset().left);
            var x = parseInt(elnt.offset().left + elnt.width());
            var b = parseInt(elnt.offset().top);
            var y = parseInt(elnt.offset().top + elnt.height());
            var m = parseInt(pos.pageX);
            var n = parseInt(pos.pageY);

            if (n>b && n<y && m>a && m<x) {
                elnt.trigger('click');
            }
        });
    }
})(jQblvg);