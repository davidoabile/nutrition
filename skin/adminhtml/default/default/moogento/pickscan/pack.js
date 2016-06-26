var PickScanModule = (function(){

    function Settings() {
        var self = this;

        self.customSortName = ko.observable();
        self.customSortLimit = ko.observable();
        self.customFirstShow = ko.observable();
        self.customFirstName = ko.observable();
        self.customSecondShow = ko.observable();
        self.customSecondName = ko.observable();
        self.customThirdShow = ko.observable();
        self.customThirdName = ko.observable();
        self.customForthShow = ko.observable();
        self.customForthName = ko.observable();
        self.showConfigurableOptions = ko.observable(false);

        self.enable_commenting = ko.observable(false);
        self.enable_correct_sound = ko.observable(false);

        self.assign_tracking = ko.observable(false);
    }

    function Order(data) {
        var self = this;

        self.id = ko.observable();
        self.inner_id = ko.observable();
        self.box = ko.observable();
        self.trolley_id = ko.observable();

        self.products = ko.observableArray();

        self.customer_comments = ko.observableArray();
        self.admin_comments = ko.observableArray();

        self.comments = ko.observableArray();

        self.tracking = ko.observable('');
        self.trackingScanned = ko.observable(false);
        self.carrier = ko.observable('');
        self.trackingFocus = ko.observable(false);

        self.pick_results = ko.observable('');

        self.sortProducts = function() {
            self.products.sort(function(left, right) {
                var left_value = left.getSortValue(),
                    right_value = right.getSortValue();
                return left_value == right_value ? 0 : (left_value < right_value ? -1 : 1)
            });
        };

        self.finished = ko.observable(false);

        self.finish = function() {
            if (!self.finished()) {
                self.finished(true);
                $.ajax({
                    url: PickScanModule.urls.finish,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        form_key: PickScanModule.form_key,
                        order_id: self.inner_id()
                    }
                });
            }
        };

        var mapping = {
            products: {
                create: function(options) {
                    return new Product(options.data);
                }
            }
        };
        ko.mapping.fromJS(data, mapping, self);
        self.sortProducts();
    }

    function Product(data) {

        var self = this;

        self.id = ko.observable();
        self.sku = ko.observable();
        self.barcode = ko.observable();
        self.qty = ko.observable();
        self.titleText = ko.observable();

        self.name = ko.observable();
        self.custom_sort = ko.observable();
        self.custom_sort_limit = ko.observable(0);

        self.getSortValue = ko.computed(function() {
            var limit = self.custom_sort_limit();
            if (limit > 0) {
                return self.custom_sort().substring(0, limit);
            } else {
                return self.custom_sort();
            }
        });

        self.custom_1 = ko.observable();
        self.custom_2 = ko.observable();
        self.custom_3 = ko.observable();
        self.custom_4 = ko.observable();
        self.image = ko.observable();

        self.zoomImage = function(data, e) {
            e.stopPropagation();
            e.preventDefault();
            $('#zoomImageModal img').attr('src', self.image());
            $('#zoomImageModal').modal('show');
            $('body').on('click.zoom', function(e) {
                $(this).off('click.zoom');
                e.preventDefault();
                e.stopPropagation();
                $('#zoomImageModal').modal('hide');
                PickScanModule.root.scanProductFocus(true);
            })
        };
        self.order = ko.observable();

        self.attributes_info = ko.observableArray();
        self.customer_comments = ko.observableArray();

        ko.mapping.fromJS(data, {}, self);
    }

    function Pack(settings) {
        var self = this;

        self.step = ko.observable('init');
        self.settings = ko.observable(new Settings());
        self.loading = ko.observable(false);
        self.hasError = ko.observable(false);
        self.error = ko.observable('');

        self.currentOrder = ko.observable(false);

        self.packstation = ko.observable();
        self.packstationFocus = ko.observable(true);
        self.packstation_scan = ko.computed({
            read: function() {
                return self.packstation();
            },
            write: function(newVal) {
                self.packstation(newVal);
                if (newVal) {
                    self.step('box_scan');
                    self.boxFocus(true);
                }
            }
        });

        self.box = ko.observable();
        self.boxFocus = ko.observable();
        self.box_scan = ko.computed({
            read: function(){
                return self.box();
            },
            write: function(newVal) {
                self.box('');
                if (newVal) {
                    if (self.currentOrder()) {
                        self.currentOrder().finish();
                    }
                    self.loadBoxData(newVal);
                }
            }
        });



        self.loadBoxData = function(box) {
            self.loading(true);
            self.hasError(false);
            self.error('');
            $.ajax({
                url: PickScanModule.urls.loadBoxData,
                type: 'POST',
                dataType: 'json',
                data: {
                    form_key: PickScanModule.form_key,
                    box: box,
                    packstation: self.packstation()
                },
                success: function(response) {
                    self.loading(false);
                    if (response.error) {
                        self.hasError(true);
                        self.error(response.error);
                    } else {
                        self.currentOrder(new Order(response.data));
                        self.step('order_view');
                        self.boxFocus(true);
                    }
                },
                failure: function() {
                    self.loading(false);
                }
            })
        };

        ko.mapping.fromJS(settings, {}, self.settings());
    }

    return {
        Pack: Pack
    }
})();