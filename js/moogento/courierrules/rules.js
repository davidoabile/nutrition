function ShippingRule(data) {
    var self = this;
    data = data || {};

    self.id = data.id || '-' + new Date().getTime();

    self.active = ko.observable(data.active ? data.active * 1 : 1).extend({dirty: false});
    self.name = ko.observable(data.name ? data.name : '').extend({dirty: false});
    self.sort = ko.observable(data.sort ? data.sort : '').extend({dirty: false});
    self.scope = ko.observable(data.scope && data.scope != 'default' ? data.scope : '').extend({dirty: false});
    self.shipping_method = ko.observable(data.shipping_method && data.shipping_method != '__any__' ? data.shipping_method : '').extend({dirty: false});
    self.custom_shipping_method = ko.observable(data.custom_shipping_method ? data.custom_shipping_method : '').extend({dirty: false});
    self.shipping_zone = ko.observable(data.shipping_zone ? data.shipping_zone : '').extend({dirty: false});
    self.min_weight = ko.observable(data.min_weight ? data.min_weight : '').extend({dirty: false});
    self.max_weight = ko.observable(data.max_weight ? data.max_weight : '').extend({dirty: false});
    self.tracking_id = ko.observable(data.tracking_id ? data.tracking_id : '').extend({dirty: false});

    self.min_amount = ko.observable(data.min_amount ? data.min_amount : '').extend({dirty: false});
    self.max_amount = ko.observable(data.max_amount ? data.max_amount : '').extend({dirty: false});

    self.quantity_all_items = ko.observable(data.quantity_all_items ? data.quantity_all_items : '').extend({dirty: false});
    self.quantity_free_discount_items = ko.observable(data.quantity_free_discount_items ? data.quantity_free_discount_items : '').extend({dirty: false});
    self.wipe_current_rules_when_importing = ko.observable(data.wipe_current_rules_when_importing ? data.wipe_current_rules_when_importing : '').extend({dirty: false});

    self.shipping_cost_filter_min = ko.observable(data.shipping_cost_filter_min ? data.shipping_cost_filter_min : '').extend({dirty: false});
    self.shipping_cost_filter_max = ko.observable(data.shipping_cost_filter_max ? data.shipping_cost_filter_max : '').extend({dirty: false});

    self.cost_filter_min = ko.observable(data.cost_filter_min ? data.cost_filter_min : '').extend({dirty: false});
    self.cost_filter_max = ko.observable(data.cost_filter_max ? data.cost_filter_max : '').extend({dirty: false});

    if (multipleProductAttribyute) {
        self.product_attribute = ko.observableArray(data.product_attribute ? data.product_attribute : '').extend({dirty: false});
    } else {
        self.product_attribute = ko.observable(data.product_attribute ? data.product_attribute : '').extend({dirty: false});
        self.min_product_attribute = ko.observable(data.min_product_attribute ? data.min_product_attribute : '').extend({dirty: false});
        self.max_product_attribute = ko.observable(data.max_product_attribute ? data.max_product_attribute : '').extend({dirty: false});
    }

    if (multipleProductAttribyute2) {
        self.product_attribute2 = ko.observableArray(data.product_attribute2 ? data.product_attribute2 : '').extend({dirty: false});
    } else {
        self.product_attribute2 = ko.observable(data.product_attribute2 ? data.product_attribute2 : '').extend({dirty: false});
        self.min_product_attribute2 = ko.observable(data.min_product_attribute2 ? data.min_product_attribute2 : '').extend({dirty: false});
        self.max_product_attribute2 = ko.observable(data.max_product_attribute2 ? data.max_product_attribute2 : '').extend({dirty: false});
    }

    if (multipleProductAttribyute3) {
        self.product_attribute3 = ko.observableArray(data.product_attribute3 ? data.product_attribute3 : '').extend({dirty: false});
    } else {
        self.product_attribute3 = ko.observable(data.product_attribute3 ? data.product_attribute3 : '').extend({dirty: false});
        self.min_product_attribute3 = ko.observable(data.min_product_attribute3 ? data.min_product_attribute3 : '').extend({dirty: false});
        self.max_product_attribute3 = ko.observable(data.max_product_attribute3 ? data.max_product_attribute3 : '').extend({dirty: false});
    }

    self.courierrules_method = ko.observable(data.courierrules_method ? data.courierrules_method : 'custom_custom').extend({dirty: false});
    self.target_custom = ko.observable(data.target_custom ? data.target_custom : '').extend({dirty: false});

    self.buildName = function(field, multiple) {
        return 'shipping_rule[' + self.id + '][' + field + ']' + (multiple ? '[]' : '');
    };

    self.buildId = function(main) {
        return 'shipping_rules_' + main + '-' + self.id;
    }

    self.isDirty = ko.computed(function(){
        return self.active.isDirty()
            || self.name.isDirty()
            || self.sort.isDirty()
            || self.scope.isDirty()
            || self.shipping_method.isDirty()
            || self.custom_shipping_method.isDirty()
            || self.shipping_zone.isDirty()
            || self.min_weight.isDirty()
            || self.max_weight.isDirty()
            || self.min_amount.isDirty()
            || self.max_amount.isDirty()
            || self.quantity_all_items.isDirty()
            || self.quantity_free_discount_items.isDirty()
            || self.wipe_current_rules_when_importing.isDirty()
            || self.product_attribute.isDirty()
            || self.product_attribute2.isDirty()
            || self.product_attribute3.isDirty()
            || self.courierrules_method.isDirty()
            || self.target_custom.isDirty()
            || self.shipping_cost_filter_min.isDirty()
            || self.shipping_cost_filter_max.isDirty()
            || self.cost_filter_min.isDirty()
            || self.cost_filter_max.isDirty();
    });

    self.isNotValid = ko.computed(function() {
        return !self.shipping_zone();
    });
}

function Settings(data) {
    var self = this;

    self.display = ko.observableArray();

    if (data.order_grid && data.order_grid == 1) {
        self.display.push('order_grid');
    }

    if (data.shipping_grid && data.shipping_grid == 1) {
        self.display.push('shipping_grid');
    }

    self.displayOriginal = ko.observableArray();

    if (data.order_grid_original && data.order_grid_original == 1) {
        self.displayOriginal.push('order_grid_original');
    }
    if (data.shipping_grid_original && data.shipping_grid_original == 1) {
        self.displayOriginal.push('shipping_grid_original');
    }

    self.enableCron = ko.observable(data.enable_cron ? data.enable_cron * 1 : false);
    self.cronPeriod = ko.observable(data.cron_period || 5);
    self.cronLimit = ko.observable(data.cron_limit || 100);
    self.cronLog = ko.observable(data.cron_log || 0);
    self.cronEmail = ko.observable(data.cron_email || 0);
    self.cronEmailTo = ko.observable(data.cron_email_to || '');

    self.useProductAttribute = ko.observable(data.use_product_attribute ? data.use_product_attribute * 1 : false);
    self.useProductAttributeRange = ko.observable(data.use_product_attribute_range ? data.use_product_attribute_range * 1 : false);
    self.productAttribute = ko.observable(data.product_attribute ? data.product_attribute : '').extend({dirty: false});
    self.useProductAttributeSum = ko.observable(data.use_product_attribute_sum ? data.use_product_attribute_sum * 1 : false).extend({dirty: false});
    self.useProductAttribute2 = ko.observable(data.use_product_attribute2 ? data.use_product_attribute2 * 1 : false);
    self.useProductAttributeRange2 = ko.observable(data.use_product_attribute_range2 ? data.use_product_attribute_range2 * 1 : false);
    self.productAttribute2 = ko.observable(data.product_attribute2 ? data.product_attribute2 : '').extend({dirty: false});
    self.useProductAttributeSum2 = ko.observable(data.use_product_attribute_sum2 ? data.use_product_attribute_sum2 * 1 : false).extend({dirty: false});
    self.useProductAttribute3 = ko.observable(data.use_product_attribute3 ? data.use_product_attribute3 * 1 : false);
    self.useProductAttributeRange3 = ko.observable(data.use_product_attribute_range3 ? data.use_product_attribute_range3 * 1 : false);
    self.productAttribute3 = ko.observable(data.product_attribute3 ? data.product_attribute3 : '').extend({dirty: false});
    self.useProductAttributeSum3 = ko.observable(data.use_product_attribute_sum3 ? data.use_product_attribute_sum3 * 1 : false).extend({dirty: false});
    self.createMissingOptions = ko.observable(data.create_missing_options ? data.create_missing_options * 1 : false);
    self.quantityAllItems = ko.observable(data.quantity_all_items ? data.quantity_all_items * 1 : false);
    self.costFilter = ko.observable(data.cost_filter ? data.cost_filter * 1 : false);
    self.quantityFreeDiscountItems = ko.observable(data.quantity_free_discount_items ? data.quantity_free_discount_items * 1 : false);
    self.wipeCurrentRulesWhenImporting = ko.observable(data.wipe_current_rules_when_importing ? data.wipe_current_rules_when_importing * 1 : false);
    self.shippingCostFilter = ko.observable(data.shipping_cost_filter ? data.shipping_cost_filter * 1 : false);
    self.exactMatch = ko.observable(data.exact_match ? data.exact_match * 1 : false);
    self.replaceShipment = ko.observable(data.replace_shipment ? data.replace_shipment * 1 : false);
    
    self.predefinedOptions = ko.observableArray(data.predefined_options ? data.predefined_options : []);
    self.new_option = ko.observable();
    self.addOption = function() {
        if (self.new_option()) {
            self.predefinedOptions.push(self.new_option());
            self.new_option('');
        }
    };
    self.removeOption = function(item) {
        self.predefinedOptions.remove(item);
    };

    self.collapsed = ko.observable(true);

    self.toggleCollapsed = function() {
        self.collapsed(!self.collapsed());
    };
}

function ShippingRules(rules, settings) {
    var self = this;


    self.settings = new Settings(settings);

    self.sortableOptions = {
        axis: "y",
        cursor: "move",
        forceHelperSize: true,
        handle: '.icon-drag',
        opacity: 0.7
    };

    var rulesObjs = [];
    ko.utils.arrayForEach(rules, function(ruleData) {
        rulesObjs.push(new ShippingRule(ruleData));
    });
    self.rules = ko.observableArray(rulesObjs);

    self.isDirty = ko.observable(false);

    self.dirtyItems = ko.computed(function(){
        return ko.utils.arrayFilter(self.rules(), function(item) {
            return item.isDirty();
        });
    });

    self.dirty = ko.computed(function(){
        return self.isDirty() || self.dirtyItems().length > 0;
    });

    self.addRule = function() {
        var rule = new ShippingRule();
        var max_sort = Math.max.apply(0, ko.utils.arrayMap(self.rules(),function(e){
            return e.sort() * 1;
        }));
        if (max_sort < 0) {
            max_sort = 0;
        }
        rule.sort(max_sort + 1);
        rule.sort.subscribe(self.sortRules);
        self.rules.push(rule);
        self.isDirty(true);
    };

    self.removeRule = function(rule) {
        self.rules.remove(rule);
        self.isDirty(true);
    };

    self.updateSort = function(arg) {
        var index = 1;
        ko.utils.arrayForEach(self.rules(), function(rule) {
            rule.sort(index);
            index++;
        });
    };

    self.sorting = false;
    self.sortRules = function() {
        if (!self.sorting) {
            self.sorting = true;
            self.rules.sort(function(left, right) {
                var leftSort = parseInt(left.sort()),
                    rightSort = parseInt(right.sort());
                return leftSort == rightSort ? 0 : (leftSort < rightSort ? -1 : 1)
            });
            self.sorting = false;
        }
    }
}
