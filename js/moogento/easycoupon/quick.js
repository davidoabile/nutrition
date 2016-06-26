ko.bindingHandlers.typeahead = {
    init: function (element, valueAccessor) {
        var options = ko.unwrap(valueAccessor()) || {},
            $el = jQuery(element),
            triggerChange = function () {
                $el.change();
            };

        options.dupDetector = function(remoteMatch, localMatch) {
            return false;
        };
        options.source = options.taOptions.ttAdapter();

        var thisTypeAhead = $el.typeahead(null, options)
                .on("typeahead:selected", triggerChange)
                .on("typeahead:autocompleted", triggerChange)
            ;

        ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
            $el.typeahead("destroy");
            $el = null;
        });
    }
};
var EasyCouponQuick = function(settings) {

    function ProductOption(sku, qty){
        var self = this;
        self.sku = ko.observable(sku || '');
        self.qty = ko.observable(qty || 1);

        self.asJson = function() {
            return {
                sku: self.sku(),
                qty: self.qty()
            };
        };
    }

    function localStorageSupported() {
        try {
            return 'localStorage' in window && window['localStorage'] !== null;
        } catch (e) {
            return false;
        }
    }

    function generateShort() {
        var alphabet = "abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPRSTUVWXYZ23456789".split(""),
            length = alphabet.length,
            rnd1 = Math.round(Math.random()*(length-1)),
            rnd2 = Math.round(Math.random()*(length-1));
        return alphabet[rnd1] + alphabet[rnd2];
    }

    var self = this;

    self.website = ko.observable();
    self.website.subscribe(function(newValue){
        if (!localStorageSupported()) return;
        localStorage['easycoupon.website'] = newValue;
    });

    self.coupon = ko.observable();
    self.coupon.subscribe(function(newValue){
        if (!localStorageSupported()) return;
        localStorage['easycoupon.coupon'] = newValue;
    });

    self.saveProductOptions = function(newValue) {
        if (!localStorageSupported()) return;
        var data = [];
        ko.utils.arrayForEach(newValue, function(opt){
            data.push(opt.asJson());
        });
        localStorage['easycoupon.skus'] = JSON.stringify(data);
    };

    self.skus = ko.observableArray();
    self.skus.subscribe(self.saveProductOptions);

    self.target = ko.observable();
    self.target.subscribe(function(newValue){
        if (!localStorageSupported()) return;
        localStorage['easycoupon.target'] = newValue;
    });

    self.createShortLink = ko.observable(false);
    self.shortLink = ko.observable(generateShort());

    self.addProduct = function() {
        self.doAddProduct();
    };
    self.doAddProduct = function(sku, qty) {
        var opt = new ProductOption(sku, qty);
        opt.sku.subscribe(function(){
            self.saveProductOptions(self.skus());
        });
        opt.qty.subscribe(function(){
            self.saveProductOptions(self.skus());
        });
        self.skus.push(opt);
    };
    self.removeProduct = function(sku) {
        self.skus.remove(sku);
    };

    self.restoreSaved = function() {
        if (!localStorageSupported()) {
            if (!self.skus().length) {
                self.addProduct();
            }
            return;
        }
        self.website(localStorage['easycoupon.website']);
        self.coupon(localStorage['easycoupon.coupon']);
        self.target(localStorage['easycoupon.target']);

        if (localStorage['easycoupon.skus']) {
            var skus = JSON.parse(localStorage['easycoupon.skus']);
            if (skus && skus.length > 0) {
                ko.utils.arrayForEach(skus, function (data) {
                    self.doAddProduct(data.sku, data.qty);
                });
            }
        }
        if (!self.skus().length) {
            self.addProduct();
        }
    };
    self.restoreSaved();

    self.storeUrls = settings.storeUrls;

    self.couponOptions = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.whitespace,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote : {
            url: settings.couponUrl,
            wildcard: '%QUERY'
        }
    });
    self.couponOptions.initialize();

    self.skuOptions = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.whitespace,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote : {
            url: settings.skuUrl,
            wildcard: '%QUERY'
        }
    });
    self.skuOptions.initialize();

    self.generated_link = ko.computed(function() {
        var url = self.storeUrls.base;
        if (self.website() && self.website() in self.storeUrls) {
            url = self.storeUrls[self.website()];
        }
        var parts = [];
        if (self.coupon() && self.coupon().trim()) {
            var coupon = encodeURIComponent(self.coupon().trim());
            if (coupon) {
                parts.push('coupon=' + coupon);
            }
        }

        if (self.target()) {
            parts.push('target=' + self.target());
        }

        if (self.skus().length == 1) {
            var sku = self.skus()[0];
            if (sku.sku()) {
                parts.push('ezsku=' + encodeURIComponent(sku.sku()));
                if (sku.qty() && sku.qty() != 1) {
                    parts.push('ezqty=' + sku.qty());
                }
            }
        } else {
            ko.utils.arrayForEach(self.skus(), function(el, index) {
                if (el.sku()) {
                    parts.push('ezsku[' + index + ']=' + encodeURIComponent(el.sku()));
                    if (el.qty() && el.qty() != 1) {
                        parts.push('ezqty[' + index + ']=' + el.qty());
                    }
                }
            });
        }

        return url + (parts.length > 0 ? '?' + parts.join('&') : '');
    });

    self.generated_short_link = ko.computed(function() {
        var url = self.storeUrls.base;
        if (self.website() && self.website() in self.storeUrls) {
            url = self.storeUrls[self.website()];
        }

        return url + '?' + self.shortLink();
    });

    self.error = ko.observable(false);
    self.saveFinished = ko.observable(false);
    self.saveShortLink = function() {
        self.saveFinished(false);
        var skus = [];
        ko.utils.arrayForEach(self.skus(), function(opt){
            skus.push(opt.asJson());
        });
        new Ajax.Request(settings.saveShortLinkUrl, {
            method: 'POST',
            parameters: {
                shortlink: self.shortLink(),
                website: self.website(),
                coupon: self.coupon(),
                target: self.target(),
                skus: JSON.stringify(skus)
            },
            onSuccess: function(response) {
                var data = JSON.parse(response.responseText);
                if ('error' in data) {
                    self.error(data.error);
                }
                self.saveFinished(true);
            }
        });
    }
};