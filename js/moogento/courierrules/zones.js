function ZipCode(code)
{
    var self = this;

    self.code = ko.observable(code ? code : '').extend({dirty: false});

    self.isDirty = ko.computed(function(){
        return self.code.isDirty();
    });
}

function ShippingZone(data)
{
    data = data || {};
    var self = this;

    self.id = data.id || '-' + new Date().getTime();
    self.name = ko.observable(data.name ? data.name : '').extend({dirty: false});
    self.countries = ko.observableArray(data.countries ? data.countries : []).extend({dirty: false});
    self.zip_codes = ko.observableArray();

    if (data.zip_codes && data.zip_codes.length > 0) {
        ko.utils.arrayForEach(data.zip_codes, function(item){
            self.zip_codes.push(new ZipCode(item));
        });
    }


    self.collapsed = ko.observable(true);

    self.buildName = function(field, multiple) {
        return 'shipping_zone[' + self.id + '][' + field + ']' + (multiple ? '[]' : '');
    }

    self.toggleCollapse = function() {
        self.collapsed(!self.collapsed());
    };

    self.addZip = function() {
        var code = new ZipCode('');
        code.code.markDirty();
        self.zip_codes.push(code);
    }

    self.removeZip = function(zip) {
        self.zip_codes.remove(zip);
        self.dirty(true);
    }

    self.dirtyItems = ko.computed(function(){
        return ko.utils.arrayFilter(self.zip_codes(), function(item) {
            return item.isDirty();
        });
    });

    self.dirty = ko.observable(false);

    self.isDirty = ko.computed(function(){
        return self.dirty()
            || self.name.isDirty()
            || self.countries.isDirty()
            || self.dirtyItems().length > 0;
    });
}

function ShippingZones(zones)
{
    var self = this;

    self.zones = ko.observableArray();

    ko.utils.arrayForEach(zones, function(zoneData) {
        self.zones.push(new ShippingZone(zoneData));
    });

    self.addZone = function() {
        var zone = new ShippingZone()
        zone.collapsed(false);
        zone.dirty(true);
        self.zones.push(zone);
    };

    self.removeZone = function(zone) {
        self.zones.remove(zone);
        self.isDirty(true);
    };

    self.isDirty = ko.observable(false);

    self.dirtyItems = ko.computed(function(){
        return ko.utils.arrayFilter(self.zones(), function(item) {
            return item.isDirty();
        });
    });

    self.dirty = ko.computed(function(){
        return self.isDirty() || self.dirtyItems().length > 0;
    });
}

