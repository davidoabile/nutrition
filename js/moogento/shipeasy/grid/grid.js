function TableRow(fields, data, field) {
    var self = this;
    self.additionalFields = ko.observableArray();
    self.field = ko.observable(field);
    self.index = ko.observable();

    ko.utils.arrayForEach(fields, function(fieldData) {
        fieldData.value = data[fieldData.key] ? data[fieldData.key] : '';
        fieldData.hideLabel = true;
//        console.log(fieldData);
        self.additionalFields.push(new AdditionalField(fieldData, self));
    });

    self.buildName = function(key, multi) {
        return self.field().key() + '_table' + '[' + self.index() + ']' + '[' + key + ']' + (multi ? '[]':'');
    }
}

function AdditionalField(data, column) {
    var self = this;
    self.index = ko.observable(100);
    self.column = ko.observable(column);
    self.key = ko.observable(data.key);
    self.label = ko.observable(data.label);
    self.hideLabel = ko.observable(data.hideLabel ? data.hideLabel : false);
    self.type = ko.observable(data.type);
    if (data.type == 'multiselect') {
        self.value = ko.observableArray(data.value);
    } else {
        self.value = ko.observable(data.value);
    }
    self.options = ko.observableArray(data.options ? data.options : []);
    self.visible = ko.observable(data.visible ? data.visible : true);
    self.checked = ko.observable(data.checked ? 1*data.checked : 0);
    self.comment = ko.observable(data.comment ? data.comment : '');
    self.showToolTip = ko.observable(false);

    self.fields = ko.observableArray(data.fields ? data.fields : []);

    self.rows = ko.observableArray();
    self.rows.subscribe(function(){
        var rows = self.rows();
        for (var i = 0, j = rows.length; i < j; i++) {
            rows[i].index(i);
        }
    });

    if (self.type() == 'serializable_table' && self.value()) {
        ko.utils.arrayForEach(self.value(), function(data) {
            self.rows.push(new TableRow(self.fields(), data, self));
        });
    }

    self.addRow = function() {
        self.rows.push(new TableRow(self.fields(), {}, self));
    };
    self.removeRow = function(row) {
        self.rows.remove(row);
    };



    self.tpl = ko.computed(function(){
        return 'field-template-' + self.type();
    });

    self.isVisible = ko.computed(function() {
        var visible = true;
        if (typeof self.visible() === 'object' && self.column) {
            for (var key in self.visible()) {
                var value = self.visible()[key],
                    field = ko.utils.arrayFirst(self.column().additionalFields(), function(field) {
                        return field.key() == key;
                    });
                if (!field || (field.type() == 'checkbox' && field.checked() != value) || (field.type() != 'checkbox' && field.value() != value)) {
                    visible = false;
                }
                if(!field || ((field.type() == 'multiselect') && (field.value().indexOf(value) > -1))){
                    visible = true;
                }
            }
        }
        return visible;
    });
}
function GridColumn(data) {
    var self = this;

    self.key = ko.observable(data.key);
    self.order = ko.observable(data.order ? parseInt(data.order) : 10000);
    self.show = ko.observable(1*data.show);
    self.header = ko.observable(data.header ? data.header : '');
    self.orig_header = ko.observable(data.orig_header ? data.orig_header : '');
    self.width = ko.observable(data.width ? data.width : '');
    self.showAll = ko.observable(false);
    self.doShowAll = function() {
        self.showAll(true);
    };

    self.additionalFields = ko.observableArray();

    if (data.additionalFields.length > 0) {
        ko.utils.arrayForEach(data.additionalFields, function(data){
            self.additionalFields.push(new AdditionalField(data, self));
        });
    }
    self.visibleFields = ko.computed(function(){
        var fields = [], i = 0;
        ko.utils.arrayForEach(self.additionalFields(), function(field){
            if (field.isVisible()) {
                field.index(i);
                fields.push(field);
                i++;
            } else {
                field.index(100);
            }
        });

        return fields;
    });

    self.buildName = function(field, multi) {
        return 'groups[grid][fields][' + self.key() + '_' + field + '][value]' + (multi ? '[]' : '');
    };

    self.isDirty = ko.computed(function(){
        return false;
    });
}


function GridColumns(columns) {
    var self = this;

    self.columns = ko.observableArray();

    self.sortableOptions = {
        axis: "y",
        cursor: "move",
        forceHelperSize: true,
        handle: '.icon-drag',
        opacity: 0.7
    };

    ko.utils.arrayForEach(columns, function(columnData) {
        var column = new GridColumn(columnData);
        self.columns.push(column);
        column.order.subscribe(function(){
            self.sortColumns();
        });
    });

    self.dirtyItems = ko.computed(function(){
        return ko.utils.arrayFilter(self.columns(), function(item) {
            return item.isDirty();
        });
    });

    self.dirty = ko.computed(function(){
        return self.dirtyItems().length > 0;
    });

    self.updateOrder = function() {
        var index = 10;
        self.sorting = true;
        ko.utils.arrayForEach(self.columns(), function(column) {
            column.order(index);
            index += 10;
        });
        self.sorting = false;
        self.sortColumns();
    };

    self.sorting = false;
    self.sortColumns = function() {
        if (!self.sorting) {
            self.sorting = true;
            self.columns.sort(function(left, right) {
                var leftSort = parseInt(left.order()),
                    rightSort = parseInt(right.order());
                return leftSort == rightSort ? 0 : (leftSort < rightSort ? -1 : 1)
            });
            self.sorting = false;
        }
    }
    self.sortColumns();
}

jQuery( document ).ready(function( $ ) {
    $("#moogento_shipeasy_grid").on('change', 'input.input-text.input-width', function(e){
        var data = $(this).val();
        if(data == parseInt(data, 10)){
            $(this).val(data+'px');
        }
    });
    
    $("#moogento_shipeasy_grid").on('change', '#sorting_type_input', function(e){
        $(this).next().val( $(this).prop('checked') ? '1' : '0');
    });
 });
 
function changingPresortingGroup() {
    jQuery("#presort .sorting_group").hide();
    switch(jQuery("#presort_select").val()){
        case 'ship':
            jQuery("#sorting_group_ship").show();
            break;
        case 'courierrules':
            jQuery("#sorting_group_courier_rule").show();
            break;
    }
    return false;
}