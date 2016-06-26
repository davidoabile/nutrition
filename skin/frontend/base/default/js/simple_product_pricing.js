/**
 * @category    Ayasoftware
 * @package     Ayasoftware_SimpleProductPricing
 * @copyright   2015 Ayasoftware (http://www.ayasoftware.com)
 * @author      EL HASSAN MATAR <support@ayasoftware.com>
 */
/*
 Some of these override earlier varien/product.js methods, therefore
 varien/product.js must have been included prior to this file.
 some of these functions were initially written by Matt Dean ( http://organicinternet.co.uk/ )
 NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.
 */

Product.Config.prototype.getMatchingSimpleProduct = function () {
    var inScopeProductIds = this.getInScopeProductIds();
    if ((typeof inScopeProductIds != 'undefined') && (inScopeProductIds.length == 1)) {
        return inScopeProductIds[0];
    }
    return false;
};

/*
 Find products which are within consideration based on user's selection of
 config options so far
 Returns a normal array containing product ids
 allowedProducts is a normal numeric array containing product ids.
 childProducts is a hash keyed on product id
 optionalAllowedProducts lets you pass a set of products to restrict by,
 in addition to just using the ones already selected by the user
 */
Product.Config.prototype.getInScopeProductIds = function (optionalAllowedProducts) {

    var childProducts = this.config.childProducts;
    var allowedProducts = [];

    if ((typeof optionalAllowedProducts != 'undefined') && (optionalAllowedProducts.length > 0)) {
        allowedProducts = optionalAllowedProducts;
    }

    for (var s = 0, len = this.settings.length - 1; s <= len; s++) {
        if (this.settings[s].selectedIndex <= 0) {
            break;
        }
        var selected = this.settings[s].options[this.settings[s].selectedIndex];
        if (s == 0 && allowedProducts.length == 0) {
            allowedProducts = selected.config.allowedProducts;
        } else {
            allowedProducts = allowedProducts.intersect(selected.config.allowedProducts).uniq();
        }
    }

    //If we can't find any products (because nothing's been selected most likely)
    //then just use all product ids.
    if ((typeof allowedProducts == 'undefined') || (allowedProducts.length == 0)) {
        productIds = Object.keys(childProducts);
    } else {
        productIds = allowedProducts;
    }
    return productIds;
};


Product.Config.prototype.getProductIdOfCheapestProductInScope = function (priceType, optionalAllowedProducts) {

    var childProducts = this.config.childProducts;
    var productIds = this.getInScopeProductIds(optionalAllowedProducts);

    var minPrice = Infinity;
    var lowestPricedProdId = false;

    //Get lowest price from product ids.
    for (var x = 0, len = productIds.length; x < len; ++x) {
        var thisPrice = Number(childProducts[productIds[x]][priceType]);
        if (thisPrice < minPrice) {
            minPrice = thisPrice;
            lowestPricedProdId = productIds[x];
        }
    }
    return lowestPricedProdId;
};


Product.Config.prototype.getProductIdOfMostExpensiveProductInScope = function (priceType, optionalAllowedProducts) {

    var childProducts = this.config.childProducts;
    var productIds = this.getInScopeProductIds(optionalAllowedProducts);

    var maxPrice = 0;
    var highestPricedProdId = false;

    //Get highest price from product ids.
    for (var x = 0, len = productIds.length; x < len; ++x) {
        var thisPrice = Number(childProducts[productIds[x]][priceType]);
        if (thisPrice >= maxPrice) {
            maxPrice = thisPrice;
            highestPricedProdId = productIds[x];
        }
    }
    return highestPricedProdId;
};

Product.OptionsPrice.prototype.updateSpecialPriceDisplay = function (price, finalPrice) {
    var prodForm = $('product_addtocart_form');
    var specialPriceBox = prodForm.select('p.special-price');
    var oldPricePriceBox = prodForm.select('p.old-price, p.was-old-price');
    var magentopriceLabel = prodForm.select('span.price-label');

    if (price == finalPrice) {
        specialPriceBox.each(function (x) {
            x.hide();
        });
        magentopriceLabel.each(function (x) {
            x.hide();
        });
        oldPricePriceBox.each(function (x) {
            x.removeClassName('old-price');
            x.addClassName('was-old-price');
        });
    } else {
        specialPriceBox.each(function (x) {
            x.show();
        });
        magentopriceLabel.each(function (x) {
            x.show();
        });
        oldPricePriceBox.each(function (x) {
            x.removeClassName('was-old-price');
            x.addClassName('old-price');
        });
    }

};

Product.OptionsPrice.prototype.reloadPriceLabels = function (productPriceIsKnown) {
    var priceFromLabel = '';
    var prodForm = $('product_addtocart_form');

    if (!productPriceIsKnown && typeof spConfig != "undefined") {
        priceFromLabel = spConfig.config.priceFromLabel;
    }

    var priceSpanId = 'configurable-price-from-' + this.productId;
    var duplicatePriceSpanId = priceSpanId + this.duplicateIdSuffix;

    if ($(priceSpanId) && $(priceSpanId).select('span.configurable-price-from-label'))
        $(priceSpanId).select('span.configurable-price-from-label').each(function (label) {
            label.innerHTML = priceFromLabel;
        });

    if ($(duplicatePriceSpanId) && $(duplicatePriceSpanId).select('span.configurable-price-from-label')) {
        $(duplicatePriceSpanId).select('span.configurable-price-from-label').each(function (label) {
            label.innerHTML = priceFromLabel;
        });
    }
};

//This triggers reload of price and other elements that can change
//once all options are selected
Product.Config.prototype.reloadPrice = function () {
    var childProductId = this.getMatchingSimpleProduct();
    var childProducts = this.config.childProducts;
    if (childProductId) {
        var price = childProducts[childProductId]["price"];
        var finalPrice = childProducts[childProductId]["finalPrice"];
        optionsPrice.productPrice = finalPrice;
        optionsPrice.productOldPrice = price;
        optionsPrice.reload();
        optionsPrice.reloadPriceLabels(true);
        optionsPrice.updateSpecialPriceDisplay(price, finalPrice);

        if (this.config.updateProductName) {
            this.updateProductName(childProductId);
        }
        if (this.config.updateShortDescription) {
            this.updateProductShortDescription(childProductId);
        }
        if (this.config.updateDescription) {
            this.updateProductDescription(childProductId);
        }
        if (this.config.productAttributes) {
            this.updateProductAttributes(childProductId);
        }

        if (this.config.customStockDisplay) {
            this.updateProductAvailability(childProductId);
        }
        this.showTierPricingBlock(childProductId, this.config.productId);
        if (this.config.updateproductimage) {
            this.updateProductImage(childProductId);
        }

    } else {
        var cheapestPid = this.getProductIdOfCheapestProductInScope("finalPrice");
        var price = childProducts[cheapestPid]["price"];
        var finalPrice = childProducts[cheapestPid]["finalPrice"];
        optionsPrice.productPrice = finalPrice;
        optionsPrice.productOldPrice = price;
        optionsPrice.reload();
        optionsPrice.reloadPriceLabels(false);
        optionsPrice.updateSpecialPriceDisplay(price, finalPrice);
        if (this.config.updateProductName) {
            this.updateProductName(false);
        }
        if (this.config.updateShortDescription) {
            this.updateProductShortDescription(false);
        }
        if (this.config.updateDescription) {
            this.updateProductDescription(false);
        }

        if (this.config.productAttributes) {
            this.updateProductAttributes(this.config.productId);
        }
        if (this.config.customStockDisplay) {
            this.updateProductAvailability(false);
        }
        this.showTierPricingBlock(false);
        if (this.config.updateproductimage) {
            this.updateProductImage(this.config.productId);
        }
    }
};

Product.Config.prototype.updateProductImage = function (productId) {
    var imgUrl;
    var zoomtype = this.config.zoomtype;
    var product_image_markup = this.config.product_image_markup;
    if (parseInt(productId) !== parseInt(this.config.productId)) {
        imgUrl = this.config.ajaxBaseUrl + "image/?id=" + productId + '&pid=' + this.config.productId;
    }
    else {
        imgUrl = this.config.ajaxBaseUrl + "image/?id=" + this.config.productId;
    }
    new Ajax.Request(imgUrl, {
        method: 'POST',
        onFailure: function (transport) {
            vJSONResp = transport.responseText;
        },
        onSuccess: function (transport) {
            if (200 == transport.status) {
                image = transport.responseText;
                switch (zoomtype) {
                    case '1':
                        $$(product_image_markup).each(function (el) {
                            // $('zoom1').innerHTML = image;
                            el.innerHTML = image;
                            if ($('zoom1')) {
                                var imgObj = new Image();
                                imgObj.onload = function () {
                                    product_zoom = new Product.Zoom('zoom1', 'track', 'handle', 'zoom_in', 'zoom_out', 'track_hint');
                                };
                                imgObj.src = $('image').src;
                            }
                        });
                        break;
                    case '2':
                        $$(product_image_markup).each(function (el) {
                            //  $('main-image-container').innerHTML = image;
                            el.innerHTML = image;
                            ProductMediaManager.init();
                        });
                        break;
                    default:
                        $$(product_image_markup).each(function (el) {
                            //  $('main-image-container').innerHTML = image;
                            el.innerHTML = image;
                        });
                        break;

                }
            }

        }
    });
};

Product.Config.prototype.updateProductName = function (productId) {
    var product_name_markup = this.config.product_name_markup;
    var productName = this.config.productName;
    if (productId && this.config.ProductNames[productId].ProductName) {
        productName = this.config.ProductNames[productId].ProductName;
    }
    $$(product_name_markup).each(function (el) {
        el.innerHTML = productName;
    });
};

Product.Config.prototype.updateProductAvailability = function (productId) {
    var stockInfo = this.config.stockInfo;
    var me = this;
    var el = jQuery('#product-options-wrapper select')[1];
    if (typeof el === 'undefined') {
        el = jQuery('#product-options-wrapper select')[0];
    }
    var id = jQuery(this.availabilityCheckEl).attr('id');
    if (productId) {
        if (jQuery('#' + id + " option:selected").text().indexOf('Availability') > -1) {
            jQuery('.availability').removeClass('in-stock');
            jQuery('#product-addtocart-button').hide();
            jQuery('.availability').html('<span class="please-wait"> ' +
                    ' <img src="/skin/frontend/ultimo/nwh/images/opc-ajax-loader.gif" \n\
                        alt="" title="" class="v-middle"> Checking Availability...</span>');
            var text = jQuery('#' + id + " option:selected").text();

            if (stockInfo[productId]) {
                jQuery.get('/stocklevels/index/syncConfigurable', {productId: productId, sku: stockInfo[productId]['sku'], path : location.pathname },
                function (response) {
                    if (response.data) {

                        if (response.data.stockLabel.indexOf('n Stock') > -1) {
                            jQuery('#' + id + " option:selected").text(text.replace(me.availabilityLabel, me.inStock));
                        } else {
                            jQuery('#' + id + " option:selected").text(text.replace(me.availabilityLabel, '( Out of Stock )'));
                        }
                        me.updateAjaxProductAvailability(productId, response.data);
                        jQuery('#product-addtocart-button').show();
                    }
                });
            } else {
                jQuery('#' + id + " option:selected").text(text.replace(me.availabilityLabel, '( Out of stock )'));
                me.updateAjaxProductAvailability(productId, {stockLabel: 'Out of stock', stockQty: -1, is_in_stock: false, stockalert: ''});
            }
        } else {
            me.updateAjaxProductAvailability(productId, stockInfo[productId]);
            jQuery('#product-addtocart-button').show();
        }
    }
};


Product.Config.prototype.updateAjaxProductAvailability = function (productId, stocklevels) {
    var stockalert = '';
    var product_customstockdisplay_markup = this.config.product_customstockdisplay_markup;
    var is_in_stock = false;
    var stockLabel = '';
    if (stocklevels) {
        jQuery('.availability').html('Availability: <span></span>');
        stockLabel = stocklevels["stockLabel"];
        stockQty = stocklevels["stockQty"];
        is_in_stock = stocklevels["is_in_stock"];
        stockalert = stocklevels["stockalert"];
    }
    $$(product_customstockdisplay_markup + ' span').each(function (el) {
        $$('.product-notify')[0].innerHTML = '';
        if (is_in_stock) {
            $$(product_customstockdisplay_markup).each(function (es) {
                es.removeClassName('availability out-of-stock');
                es.addClassName('availability in-stock');
                $$('.product-options-bottom')[0].show();

            });
            /*el.innerHTML = stockQty + '  ' + stockLabel;*/
            el.innerHTML = stockLabel;
        } else {
            $$(product_customstockdisplay_markup).each(function (ef) {
                ef.removeClassName('availability in-stock');
                ef.addClassName('availability out-of-stock');
                $$('.product-options-bottom')[0].hide();
                // $$('.product-notify')[0].innerHTML = stockInfo[productId]["stockalert"]; 
                $$('.product-notify')[0].innerHTML = stockalert;
                jQuery('html').bind('keypress', function (e)
                {
                    if (e.keyCode == 13)
                    {
                        return false;
                    }
                });
            });
            el.innerHTML = stockLabel;
        }
    });

};
Product.Config.prototype.updateProductShortDescription = function (productId) {
    var shortDescription = this.config.shortDescription;
    var product_shortdescription_markup = this.config.product_shortdescription_markup;
    if (productId && this.config.shortDescriptions[productId].shortDescription) {
        shortDescription = this.config.shortDescriptions[productId].shortDescription;
    }
    $$(product_shortdescription_markup).each(function (el) {
        el.innerHTML = shortDescription;
    });
};

Product.Config.prototype.updateProductDescription = function (productId) {
    var description = this.config.description;
    var product_description_markup = this.config.product_description_markup;
    if (productId && this.config.Descriptions[productId].Description) {
        description = this.config.Descriptions[productId].Description;
    }
    $$(product_description_markup).each(function (el) {
        el.innerHTML = description;
    });
};
/*
 * updates product attributes 
 */
Product.Config.prototype.updateProductAttributes = function (productId) {
    var productAttributes = this.config.productAttributes;
    var product_attributes_markup = this.config.product_attributes_markup;
    var coUrl;
    if (productId !== this.config.productId) {
        coUrl = this.config.ajaxBaseUrl + "productattributes/?id=" + productId + '&pid=' + this.config.productId;
    } else {
        coUrl = this.config.ajaxBaseUrl + "productattributes/?id=" + this.config.productId;
    }
    new Ajax.Request(coUrl, {
        method: 'POST',
        onFailure: function (transport) {
            vJSONResp = transport.responseText;
            var JSON = eval("(" + vJSONResp + ")");
            updateStatus(JSON.code + ": " + JSON.message);
        },
        onSuccess: function (transport) {
            if (200 == transport.status) {
                productAttributes = transport.responseText;
                $$(product_attributes_markup).each(function (el) {
                    el.innerHTML = productAttributes;
                });

            }
        }
    });
};


//SCP: Forces the 'next' element to have it's optionLabels reloaded too
Product.Config.prototype.configureElement = function (element) {
    this.reloadOptionLabels(element);
    if (element.value) {
        this.state[element.config.id] = element.value;
        if (element.nextSetting) {
            element.nextSetting.disabled = false;
            this.fillSelect(element.nextSetting);
            this.reloadOptionLabels(element.nextSetting);
            this.resetChildren(element.nextSetting);
        }
    }
    else {
        this.resetChildren(element);
    }
    this.reloadPrice();
};

//SCP: Changed logic to use absolute price ranges rather than price differentials
Product.Config.prototype.reloadOptionLabels = function (element) {
    var childProducts = this.config.childProducts;
    var stockInfo = this.config.stockInfo;
   
    var availabilityCheckEl = jQuery(this.availabilityCheckEl).attr('id');
    var sizeOptions = jQuery(element).attr('id') == availabilityCheckEl ? true : false;
    //Don't update elements that have a selected option
    if (element.options[element.selectedIndex].config) {
        return;
    }

    for (var i = 0; i < element.options.length; i++) {
        if (element.options[i].config) {
             var stock = this.inStock;
            var cheapestPid = this.getProductIdOfCheapestProductInScope("finalPrice", element.options[i].config.allowedProducts);
            var mostExpensivePid = this.getProductIdOfMostExpensiveProductInScope("finalPrice", element.options[i].config.allowedProducts);
            var cheapestFinalPrice = childProducts[cheapestPid]["finalPrice"];
            var mostExpensiveFinalPrice = childProducts[mostExpensivePid]["finalPrice"];
            if (cheapestPid == mostExpensivePid && sizeOptions === false) {
                 var products = element.options[i].config.products;
               
                for (var p = 0; p < products.length; p++) {
                     if (parseFloat(stockInfo[products[p]]['stockQty']) <= 0) {
                         stock = this.outOfStock;
                         break;
                     }
                }
               
            } else if (sizeOptions === true) {

               // var products = element.options[i].config.products;
                var isInStock = true;
                stock = this.inStock;
                //for (var p = 0; p < products.length; p++) {
                    var info = stockInfo[cheapestPid];
                    if (info) {
                        if (parseFloat(info['stockQty']) <= 0) {
                            isInStock = false;
                        } 
                    } else {
                        isInStock = false;
                    }
                //}
                if (isInStock === false) {
                    stock = this.availabilityLabel;
                    isInStock = true;
                }
            }

            if (this.config.showOutOfStock) {
                if (this.config.disable_out_of_stock_option) {
                    if (!stockInfo[cheapestPid]["is_in_stock"]) {
                        if (cheapestPid == mostExpensivePid) {
                            element.options[i].disabled = true;
                            stock = '( ' + stockInfo[cheapestPid]["stockLabel"] + ' )';
                        }
                    }
                }
            }
            var tierpricing = childProducts[mostExpensivePid]["tierpricing"];
            element.options[i].text = this.getOptionLabel(element.options[i].config, cheapestFinalPrice, mostExpensiveFinalPrice, stock, tierpricing);
        }
    }
};

Product.Config.prototype.availabilityLabel = '( Check Availability )';
Product.Config.prototype.inStock = '( In Stock )';
Product.Config.prototype.outOfStock = '( Out Of Stock )';
//SCP: Changed logic to use absolute price ranges rather than price differentials
Product.Config.prototype.reloadOptionLabelsSync = function (element) {
    var childProducts = this.config.childProducts;
    var stockInfo = this.config.stockInfo;
    var stock = this.inStock;
    var availabilityCheckEl = jQuery(this.availabilityCheckEl).attr('id');
    var sizeOptions = jQuery(element).attr('id') == availabilityCheckEl ? true : false;
    //Don't update elements that have a selected option
    if (element.options[element.selectedIndex].config) {
        return;
    }

    for (var i = 0; i < element.options.length; i++) {
        if (element.options[i].config) {
            var cheapestPid = this.getProductIdOfCheapestProductInScope("finalPrice", element.options[i].config.allowedProducts);
            var mostExpensivePid = this.getProductIdOfMostExpensiveProductInScope("finalPrice", element.options[i].config.allowedProducts);
            var cheapestFinalPrice = childProducts[cheapestPid]["finalPrice"];
            var mostExpensiveFinalPrice = childProducts[mostExpensivePid]["finalPrice"];
            if (cheapestPid == mostExpensivePid && sizeOptions === false) {
                if (stockInfo[cheapestPid]["stockLabel"] != '') {
                    stock = '( ' + stockInfo[cheapestPid]["stockLabel"] + ' )';
                }
            } else if (sizeOptions === true) {

                var products = element.options[i].config.products;
                var isInStock = false;
                stock = this.inStock;
                for (var p = 0; p < products.length; p++) {
                    var info = stockInfo[products[p]];

                    if (parseFloat(info['stockQty']) > 0) {
                        isInStock = true;
                    }
                }
                if (isInStock === false) {
                    stock = this.availabilityLabel;
                }
            }

            if (this.config.showOutOfStock) {
                if (this.config.disable_out_of_stock_option) {
                    if (!stockInfo[cheapestPid]["is_in_stock"]) {
                        if (cheapestPid == mostExpensivePid) {
                            element.options[i].disabled = true;
                            stock = '( ' + stockInfo[cheapestPid]["stockLabel"] + ' )';
                        }
                    }
                }
            }
            var tierpricing = childProducts[mostExpensivePid]["tierpricing"];
            element.options[i].text = this.getOptionLabel(element.options[i].config, cheapestFinalPrice, mostExpensiveFinalPrice, stock, tierpricing);
        }
    }
};

Product.Config.prototype.showTierPricingBlock = function (productId, parentId) {
    var coUrl = this.config.ajaxBaseUrl + "co/?id=" + productId + '&pid=' + parentId;
    if (productId) {
        new Ajax.Updater('sppTierPricingDiv', coUrl, {
            method: 'get',
            evalScripts: true,
            onComplete: function () {
                $$('span.scp-please-wait').each(function (el) {
                    el.hide();
                });
            }
        });
    } else {
        if ($('sppTierPricingDiv') !== undefined) {
            // $('sppTierPricingDiv').innerHTML = '';
        }
    }
};

//SCP: Changed label formatting to show absolute price ranges rather than price differentials
Product.Config.prototype.getOptionLabel = function (option, lowPrice, highPrice, stock, tierpricing) {

    var str = option.label;
    if (tierpricing > 0 && tierpricing < lowPrice) {
        var tierpricinglowestprice = ': As low as (' + this.formatPrice(tierpricing, false) + ')';
    } else {
        var tierpricinglowestprice = '';
    }
    if (!this.config.showPriceRangesInOptions) {
        return str;
    }

    if (typeof stock == 'undefined') {
        stock = '';
    }
    ;

    if (!this.config.showOutOfStock) {
        stock = '';
    }

    lowPrices = this.getTaxPrices(lowPrice);
    highPrices = this.getTaxPrices(highPrice);

    if (this.config.hideprices) {
        if (this.config.showOutOfStock) {
            return str + '  ' + stock + '  ';
        } else {
            return str;
        }
    }

    var to = ' ' + this.config.rangeToLabel + ' ';
    var separator = ': ( ';
    if (lowPrice && highPrice) {
        if (this.config.showfromprice) {
            this.config.priceFromLabel = this.config.priceFromLabel; //'From: ';
        }
        if (lowPrice != highPrice) {
            if (this.taxConfig.showBothPrices) {
                str += separator + this.formatPrice(lowPrices[2], false) + ' (' + this.formatPrice(lowPrices[1], false) + ' ' + this.taxConfig.inclTaxTitle + ')';
                str += to + this.formatPrice(highPrices[2], false) + ' (' + this.formatPrice(highPrices[1], false) + ' ' + this.taxConfig.inclTaxTitle + ')';
                str += " ) ";
            } else {
                str += separator + this.formatPrice(lowPrices[0], false);
                str += to + this.formatPrice(highPrices[0], false);
                str += " ) ";
            }
        } else {

            if (this.taxConfig.showBothPrices) {
                str += separator + this.formatPrice(lowPrices[2], false) + ' (' + this.formatPrice(lowPrices[1], false) + ' ' + this.taxConfig.inclTaxTitle + ')';
                str += " ) ";
                str += stock;
                str += tierpricinglowestprice;
            } else {
                if (tierpricing == 0) {
                    str += separator + this.formatPrice(lowPrices[0], false);
                    str += " ) ";
                }
                str += tierpricinglowestprice;
                str += '  ' + stock;
            }
        }
    }
    return str;
};


//SCP: Refactored price calculations into separate function
Product.Config.prototype.getTaxPrices = function (price) {
    var price = parseFloat(price);
    if (this.taxConfig.includeTax) {
        var tax = price / (100 + this.taxConfig.defaultTax) * this.taxConfig.defaultTax;
        var excl = price - tax;
        var incl = excl * (1 + (this.taxConfig.currentTax / 100));
    } else {
        var tax = price * (this.taxConfig.currentTax / 100);
        var excl = price;
        var incl = excl + tax;
    }
    if (this.taxConfig.showIncludeTax || this.taxConfig.showBothPrices) {
        price = incl;
    } else {
        price = excl;
    }

    return [price, incl, excl];
};

Product.Config.prototype.getChildProducts = function () {
    return this.config.childProducts;
};

Product.Config.prototype.getChildProductSkus = function () {
    var skus = [];
    for (var key in this.config.stockInfo) {
        skus.push(this.config.stockInfo[key]['sku']);
    }
    return skus;
};

Product.Config.prototype.getChildProductIds = function () {
    var ids = [];
    for (var key in this.config.stockInfo) {
        ids.push(key);
    }
    return ids;
};

Product.Config.prototype.sync = function () {
    var me = this;
    jQuery.get('/stocklevels/index/syncBulkLevels', {ids: this.getChildProductIds()},
    function (response) {
        if (response.data) {
            if (response.data.hq) {
                me.config.stockInfo = response.data.hq;
                me.stockLevels = response.data;
                var el = jQuery('#product-options-wrapper select')[1];
                if (typeof el === 'undefined') {
                    el = jQuery('#product-options-wrapper select')[0];
                }
                me.availabilityCheckEl = el;
                me.reloadOptionLabels(el);
            }
        }
    });
}
Product.Config.prototype.availabilityCheckEl = '';
Product.Config.prototype.stockLevels = {};
//SCP: Forces price labels to be updated on load
//so that first select shows ranges from the start
document.observe("dom:loaded", function () {
    //Really only needs to be the first element that has configureElement set on it,
    //rather than all.
    if (typeof opConfig !== "undefined") {
        opConfig.reloadPrice();
    }

    spConfig.sync();

    $('product_addtocart_form').getElements().each(function (el) {
        if (el.type == 'select-one') {
            if (el.options && (el.options.length > 1)) {
                el.options[0].selected = true;
                spConfig.reloadOptionLabels(el);
                // var cheapestPid = spConfig.getProductIdOfCheapestProductInScope("finalPrice");
                // var childProducts = spConfig.getChildProducts();
                // var price = childProducts[cheapestPid]["price"];
                // var finalPrice = childProducts[cheapestPid]["finalPrice"];
                // optionsPrice.productPrice = finalPrice;
                // optionsPrice.productOldPrice = price;
                // optionsPrice.reload();
            }
        }
    });
});