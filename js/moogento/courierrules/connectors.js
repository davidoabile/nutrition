var ConnectorsModule = (function () {

    function Field(data, parent) {
        var self = this;

        self.parent = parent;

        self.key = ko.observable(data.key);
        self.label = ko.observable(data.label);
        self.showLabel = ko.observable(data.showLabel);

        self.type = ko.observable(data.type);
        if (data.type == 'multiselect') {
            self.value = ko.observableArray(data.value);
        } else {
            self.value = ko.observable(data.value);
        }
        self.options = ko.observableArray(data.options ? data.options : []);
        self.checked = ko.observable(data.checked ? 1*data.checked : 0);
        self.comment = ko.observable(data.comment ? data.comment : '');


        self.tpl = ko.computed(function(){
            return 'connector-field-template-' + self.type();
        });
    }

    function Service(data, carrier) {
        var self = this;

        self.carrier = carrier;

        self.enabled = ko.observable(!!parseInt(data.enabled));
        self.code = data.code;
        self.label = data.label;
        self.package = ko.observable(data.package);
        self.dispatch_date = ko.observable(data.dispatch_date);
        self.additional_fields = ko.observableArray();

        if (data.code in carrier.used_in_rules) {
            self.can_disable = ko.observable(false);
            self.used_in = ko.observable(carrier.used_in_rules[data.code]);
        } else {
            self.can_disable = ko.observable(true);
            self.used_in = ko.observable('');
        }

        ko.utils.arrayForEach(carrier.additional_fields, function(field){
            if (field.type == 'checkbox') {
                field.checked = !!parseInt(data[field.key]);
            } else {
                field.value = data[field.key];
            }
            field.showLabel = false;
            self.additional_fields.push(new Field(field, self));
        });

        self.buildName = function(key) {
            return self.carrier.buildName('services') + '[' + self.code + ']' + '[' + key + ']';
        };
    }

    function Carrier(data, connector) {
        var self = this;

        self.connector = connector;

        self.enabled = ko.observable(!!parseInt(data.enabled));
        self.code = data.code;
        self.label = data.label;
        self.package_required = data.package_required;
        self.packages = data.packages;
        self.additional_fields = data.additional_fields;
        self.services = ko.observableArray(data.services);
        self.services_used = ko.observableArray();
        self.used_in_rules = data.used_in_rules;

        self.can_disable = ko.observable(data.used_in_rules.length == 0);

        self.getServiceLabel = function(code) {
            var service = ko.utils.arrayFirst(self.services(), function(service) {
                return service.value == code;
            });

            return service ? service.label : '';
        };

        ko.utils.arrayForEach(data.services_used, function(data){
            data.label = self.getServiceLabel(data.code);
            self.services_used.push(new Service(data, self));
        });
        self.selected_service = ko.observable();

        self.service_avaliable = ko.computed(function(){
            return ko.utils.arrayFilter(self.services(), function(service) {
                return !ko.utils.arrayFirst(self.services_used(), function(used){
                    return service.value == used.code;
                });
            });
        });


        self.addService = function() {
            self.services_used.push(new Service({
                enabled: 1,
                code: self.selected_service(),
                label: self.getServiceLabel(self.selected_service())
            }, self));
        };
        self.removeService = function(item) {
            self.services_used.remove(item);
        };

        self.buildName = function(key) {
            return self.connector.buildName() + '[carriers][' + self.code + '][' + key + ']';
        };
    }

    function Connector(data) {
        var self = this;

        self.enabled = ko.observable(!!parseInt(data.enabled));
        self.name = data.name;
        self.html_name_prefix = data.html_name_prefix;

        self.config = [];
        ko.utils.arrayForEach(data.config, function(data) {
            data.showLabel = true;
            self.config.push(new Field(data, self));
        });

        self.carriers = [];
        ko.utils.arrayForEach(data.carriers, function(data) {
            self.carriers.push(new Carrier(data, self));
        });

        self.buildName = function(key) {
            return self.html_name_prefix + (key ? '[' + key + ']' : '');
        }
    }

    function Connectors(data) {
        var self = this;

        self.connectors = ko.observableArray();

        var connectors = [];
        ko.utils.arrayForEach(data, function (data) {
            connectors.push(new Connector(data));
        });

        self.connectors(connectors);
    }


    return {
        Connectors: Connectors
    };
})();