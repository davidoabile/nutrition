var AutomationModule = AutomationModule || {};

(function ($) {
    function guidGenerator() {
        var S4 = function() {
            return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
        };
        return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
    }

    function StatusUpdateRow(data) {
        var self = this;

        self.id = guidGenerator();
        self.enable = ko.observable(!!parseInt(data.enable));
        self.status = ko.observable(data.status);
        self.check_time = ko.observable(data.check_time);
        self.target_status = ko.observable(data.target_status);

        self.buildName = function(key, multi) {
            return 'automation_status_update[' + self.id + '][' + key + ']' + (multi ? '[]' : '');
        };
    }

    function StatusUpdate(data) {
        var self = this;

        self.rows = ko.observableArray();

        ko.utils.arrayForEach(data, function(data){
            self.rows.push(new StatusUpdateRow(data));
        });


        self.addRow = function() {
            self.rows.push(new StatusUpdateRow([]));
        };

        self.deleteRow = function(row) {
            self.rows.remove(row);
        };
    }

    AutomationModule.StatusUpdate = StatusUpdate;
})(jQuery);