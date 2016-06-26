var PurchaseProductsModule = (function($) {

    function Product(data) {
        var self = this;

        self.id = ko.observable(data.id);
        self.name = ko.observable(data.name);
        self.price = ko.observable(data.price);
        self.qty = ko.observable(data.qty);
        self.cost = ko.observable(data.cost);

        self.checked = ko.observable(0);
    }

    function ProductsList() {
        var self = this;

        self.products = ko.observableArray();

        self.selectedProducts = ko.computed(function(){
            return ko.utils.arrayFilter(self.products(), function(product) {return product.checked();})
        });

        self.product_id = ko.observable();

        self.action = ko.observable();

        self.success = ko.observable();
        self.selecting = ko.observable(false);
        self.unselecting = function() {
            self.selecting(false);
        };
        self.loading = ko.observable(false);

        self.order_number = ko.observable();
        self.supplier = ko.observable();

        self.search = function() {
            self.loading(true);
            $.ajax({
                type: 'POST',
                url: PurchaseProductsModule.urls.search,
                dataType: 'json',
                data: {
                    product_id: self.product_id(),
                    form_key: FORM_KEY
                },
                success: function(data) {
                    self.loading(false);
                    self.product_id('');
                    if (!data.id) {
                        alert(PurchaseProductsModule.messages.noData);
                    } else {
                        if (ko.utils.arrayFirst(self.products(), function(product){return product.id() == data.id})) {
                            alert(PurchaseProductsModule.messages.alreadySelected);
                        } else {
                            self.products.push(new Product(data));
                        }
                    }
                }
            });
        };

        self.doSearch = function(root, e) {
            if (e.which == 13) {
                self.search();
            }
            return true;
        };

        self.clear = function() {
            self.products.removeAll();
        };

        self.doSubmit = function() {
            if (self.selectedProducts().length == 0) {
                alert(PurchaseProductsModule.messages.firstSelect);
                return;
            }

            if (!self.action()) {
                alert(PurchaseProductsModule.messages.firstSelectAction);
                return;
            }

            if (self.action() == 'working_sheet') {
                self.generatePdf();
            } else {
                self.selecting(true);
            }
        };

        self.saveCost = function() {
            if (self.selectedProducts().length == 0) {
                alert(PurchaseProductsModule.messages.firstSelect);
                return;
            }

            var data = {};
            ko.utils.arrayForEach(self.selectedProducts(), function(product){
                data[product.id()] = product.cost();
            });
            self.loading(true);
            $.ajax({
                type: 'POST',
                url: PurchaseProductsModule.urls.saveCost,
                dataType: 'json',
                data: {
                    costs: data,
                    form_key: FORM_KEY
                },
                success: function(data) {
                    self.loading(false);
                    if (data.error) {
                        alert(data.error);
                    } else {
                        self.success(PurchaseProductsModule.messages.costSaved)
                    }
                },
                failure: function() {
                    self.loading(false);
                }
            });
        };

        self.selectAll = function() {
            ko.utils.arrayForEach(self.products(), function(product){
                product.checked(1);
            })
        };

        self.unselectAll = function() {
            ko.utils.arrayForEach(self.products(), function(product){
                product.checked(0);
            })
        };

        self.generatePdf = function () {

            var data = {};
            ko.utils.arrayForEach(self.selectedProducts(), function (product) {
                data[product.id()] = {
                    qty: product.qty(),
                    cost: product.cost()
                }
            });

            self.selecting(false);
            self.loading(true);
            $.ajax({
                type: 'POST',
                url: PurchaseProductsModule.urls.pdf,
                dataType: 'json',
                data: {
                    type: self.action(),
                    products: data,
                    order_number: self.order_number(),
                    supplier: self.supplier(),
                    form_key: FORM_KEY
                },
                success: function (data) {
                    self.loading(false);
                    if (data.success) {
                        self.success(data.success);
                        window.location.href = data.file;
                    } else {
                        alert(data.error);
                    }
                },
                failure: function() {
                    self.loading(true);
                }
            });
        }
    }

    return {
        ProductsList: ProductsList
    }
})(jQuery);