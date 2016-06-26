var CarriersConfigModule = (function(){
    function guidGenerator() {
        var S4 = function() {
            return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
        };
        return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
    }

    function Carrier(data) {
        var self = this;

        self.id = guidGenerator();
        self.enable = ko.observable(!!parseInt(data.enable));
        self.code = ko.observable(data.code ? data.code : '');
        self.title = ko.observable(data.title ? data.title : '');
        self.link = ko.observable(data.link ? data.link : '');
        self.length = ko.observable(data.length ? data.length : '');
        self.file = ko.observable(data.file ? data.file : '');
        self.sort_order = ko.observable(0);

        self.imageSrc = ko.computed(function(){
            if (self.file()) {
                return imageBase + self.file();
            }
            return false;
        });

        self.buildName = function(key) {
            return 'carriers_list[' + self.id + '][' + key + ']';
        }
    }

    function Carriers(data) {
        var self = this;

        self.sortableOptions = {
            axis: "y",
            cursor: "move",
            forceHelperSize: true,
            handle: '.icon-drag',
            opacity: 0.7
        };

        self.list = ko.observableArray();

        ko.utils.arrayForEach(data, function(data){
            self.list.push(new Carrier(data));
        });

        self.addCarrier = function() {
            self.list.push(new Carrier({}));
        };

        self.removeCarrier = function(item) {
            self.list.remove(item);
        };

        self.updateSort = function(arg) {
            var index = 1;
            ko.utils.arrayForEach(self.list(), function(carrier) {
                carrier.sort_order(index);
                index++;
            });
        };

        self.sorting = false;
        self.sortRules = function() {
            if (!self.sorting) {
                self.sorting = true;
                self.list.sort(function(left, right) {
                    var leftSort = parseInt(left.sort_order()),
                        rightSort = parseInt(right.sort_order());
                    return leftSort == rightSort ? 0 : (leftSort < rightSort ? -1 : 1)
                });
                self.sorting = false;
            }
        }
    }

    return {
        Carriers: Carriers
    }
})();