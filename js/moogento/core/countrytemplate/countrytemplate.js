var CountryTemplateConfigModule = (function(){
    function guidGenerator() {
        var S4 = function() {
            return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
        };
        return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
    }

    function CountryTemplate(data) {
        var self = this;

        self.gen_id = guidGenerator();
        self.id = data.id ? data.id : "";
        self.enable = ko.observable(!!parseInt(data.enable));
        self.country_code = ko.observable(data.country_code ? data.country_code : '');
        self.country_template = ko.observable(data.country_template ? data.country_template : '');
        self.sort_number = ko.observable(data.sort_number ? data.sort_number : '');

        self.buildName = function(key) {
            return 'country_template_base_format[' + self.gen_id + '][' + key + ']';
        }
    }

    function CountryTemplates(data) {
        var self = this;

        self.columns = ko.observableArray();
        
        self.sortableOptions = {
            axis: "y",
            cursor: "move",
            forceHelperSize: true,
            handle: '.icon-drag',
            opacity: 0.7
        };

        self.list = ko.observableArray();
        ko.utils.arrayForEach(data, function(data){
            self.list.push(new CountryTemplate(data));
        });

        self.addCountryTemplate = function() {
            self.list.push(new CountryTemplate({}));
        };

        self.removeCountryTemplate = function(item) {
            self.list.remove(item);
            new Ajax.Request(URL_for_destoy_country_templates, {
                method: 'get',
                parameters: {id: item.id}
            });            
        };
        
        self.updateSort = function(arg) {
            var index = 1;
            ko.utils.arrayForEach(self.list(), function(column) {
                column.sort_number(index);
                index++;
            });
        };

    }

    return {
        CountryTemplates: CountryTemplates
    }
})();