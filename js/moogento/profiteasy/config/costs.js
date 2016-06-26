var CostsModule = (function ($) {
    function guidGenerator() {
        var S4 = function() {
            return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
        };
        return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
    }

    function StoreCost(data, parent) {
        var self = this;

        self.id = guidGenerator();
        self.parent = parent;
        self.store = ko.observable(data.store);
        self.calculation_type = ko.observable(data.calculation_type);
        self.cost = ko.observable(data.cost);

        self.buildName = function(key, multi) {
            return 'profiteasy_costs[' + self.parent.id + '][store_costs][' + self.id + '][' + key + ']' + (multi ? '[]' : '');
        };
    }

    function CostsRow(data) {
        var self = this;

        self.id = data.id || - new Date().getTime();
        self.enable = ko.observable(!!parseInt(data.enable));
        self.label = ko.observable(data.label);
        self.charge_type = ko.observable(data.charge_type);
        self.calculation_type = ko.observable(data.calculation_type);
        self.cost = ko.observable(data.cost);

        self.payment = ko.observable(data.payment);
        self.month = ko.observableArray(data.month);
        self.year = ko.observable(data.year);

        self.store_costs = ko.observableArray();
        if ('store_costs' in data) {
            ko.utils.arrayForEach(data.store_costs, function(subdata){
                self.store_costs.push(new StoreCost(subdata, self));
            });
        }

        self.buildName = function(key, multi) {
            return 'profiteasy_costs[' + self.id + '][' + key + ']' + (multi ? '[]' : '');
        };

        self.addRow = function() {
            self.store_costs.push(new StoreCost({}, self));
        };

        self.deleteRow = function(row) {
            self.store_costs.remove(row);
        };
    }

    function Costs(data) {
        var self = this;

        self.rows = ko.observableArray();

        ko.utils.arrayForEach(data, function(data){
            self.rows.push(new CostsRow(data));
        });


        self.addRow = function() {
            self.rows.push(new CostsRow([]));
        };

        self.deleteRow = function(row) {
            self.rows.remove(row);
        };
    }

    return {
        Costs: Costs
    }
})(jQuery);