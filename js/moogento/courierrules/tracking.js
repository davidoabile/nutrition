function TrackingPool(data)
{
    data = data || {};
    var self = this;

    self.id = data.id || '-' + new Date().getTime();
    self.name = ko.observable(data.name ? data.name : '').extend({dirty: false});
    self.codes = ko.observable(data.codes ? data.codes : '').extend({dirty: false});
    self.warn_low = ko.observable(data.warn_low ? data.warn_low : '').extend({dirty: false});

    self.collapsed = ko.observable(true);

    self.buildName = function(field, multiple) {
        return 'tracking_pool[' + self.id + '][' + field + ']' + (multiple ? '[]' : '');
    }

    self.toggleCollapse = function() {
        self.collapsed(!self.collapsed());
    };

    self.dirty = ko.observable(false);

    self.isDirty = ko.computed(function(){
        return self.dirty()
            || self.name.isDirty()
            || self.codes.isDirty();
    });
}

function TrackingPools(pools, config)
{
    var self = this;

    self.pools = ko.observableArray();

    ko.utils.arrayForEach(pools, function(poolData) {
        self.pools.push(new TrackingPool(poolData));
    });

    self.addPool = function() {
        var pool = new TrackingPool()
        pool.collapsed(false);
        pool.dirty(true);
        self.pools.push(pool);
    };

    self.removePool = function(pool) {
        self.pools.remove(pool);
        self.isDirty(true);
    };

    self.isDirty = ko.observable(false);

    self.dirtyItems = ko.computed(function(){
        return ko.utils.arrayFilter(self.pools(), function(item) {
            return item.isDirty();
        });
    });

    self.email_notification = ko.observable(config.email_notification).extend({dirty: false});

    self.dirty = ko.computed(function(){
        return self.isDirty()
            || self.email_notification.isDirty()
            || self.dirtyItems().length > 0;
    });



    self.collapsed = ko.observable(true);

    self.toggleCollapsed = function() {
        self.collapsed(!self.collapsed());
    };
}
