var PickScanModule = {};
(function($){
    function PickScan(data) {
        var self = this;

        self.step = ko.observable('init');
        self.back_step = ko.observable();
        self.submitForm = function(data, e) {
            e.preventDefault();
            $(e.currentTarget).parents('form').submit();
        };

        self.showHome = function() {
            if (self.step() != 'home_data') {
                self.back_step(self.step());
                self.step('home_data');
                $('.counter').countdown('destroy');
                $('.counter').countdown({since: new Date(self.started())});
            }
        };
        self.goBack = function() {
            $('.counter').countdown('destroy');
            self.step(self.back_step());
        };

        self.abortPick = function() {
            bootbox.confirm(PickScanModule.messages.confirm_abort, function(result) {
                if (result) {
                    if (self.ordersSaved() > 0) {
                        bootbox.confirm(PickScanModule.messages.confirm_abort_clear, function(result) {
                            if (result) {
                                self.doAbortPick(true);
                            } else {
                                self.doAbortPick(false);
                            }
                        });
                    } else {
                        self.doAbortPick(true);
                    }
                }
            });
        };
        self.doAbortPick = function(includeSaved) {
            var orderIds = [];
            self.clearSnapshot();
            ko.utils.arrayForEach(self.orders(), function(order){
                if (order.saved() && !includeSaved) return;
                orderIds.push(order.id());
            });
            $.ajax({
                method: 'POST',
                url: PickScanModule.abortUrl,
                data: {
                    ids: orderIds,
                    form_key: PickScanModule.form_key
                },
                dataType: 'json',
                complete: function() {
                    window.location = PickScanModule.backUrl;
                }
            })
        };

        self.settings = ko.observable(new Settings());

        self.orders = ko.observableArray();
        self.singleOrderMode = ko.observable(true);

        self.loading = ko.observable(false);
        self.hasError = ko.observable(false);
        self.hasWarning = ko.observable(false);
        self.error = ko.observable('');
        self.hasSuccess = ko.observable(false);
        self.hasSuccess.subscribe(function(newVal) {
            if (newVal) {
                $('.qty-box').addClass('scan-success').addClass('blink');
            } else {
                $('.qty-box').removeClass('scan-success').removeClass('blink');
            }
        });
        self.success = ko.observable('');

        self.started = ko.observable();
        self.finished = ko.observable();

        self.currentOrder = ko.observable();
        self.currentProduct = ko.observable();
        self.currentProduct.subscribe(function(){
            setTimeout(function() {
                var box = $('.details-box');
                box.parent().css({minHeight: box.outerHeight() + 'px'});
            }, 200);

        });
        self.currentBox = ko.observable();
        self.wrongSku = ko.observable();

        self.substituteTooltip = ko.computed(function(){
            return self.wrongSku() ? 'Change the item in this order with the just-scanned ' + self.wrongSku() : '';
        });
        self.updateTooltip = ko.computed(function(){
            return self.currentProduct() ? 'Overwrite the ' + self.currentProduct().barcode() + ' for this product with the just-scanned ' + self.wrongSku() : '';
        });

        self.trolley_id = ko.observable();
        self.trolley_id_scan = ko.computed({
            read: function() {
                return self.trolley_id();
            },
            write: function(newValue) {
                if (newValue) {
                    if (newValue.indexOf('PKNSCN') >= 0) {
                        self.hasError(false);
                        var parts = newValue.replace('PKNSCN', '').split('-');
                        self.trolley_id(parts[0]);
                        if (self.settings().trolleyBoxes() < parts[1]) {
                            self.settings().trolleyBoxes(parts[1]);
                        }
                    } else {
                        self.trolley_id('');
                        self.hasError(true);
                        self.error('Please scan correct barcode');
                    }
                }
            }
        });

        self.images = ko.observable();

        self.scanOrderId = ko.observable();
        self.scanOrderFocus = ko.observable(false);

        self.scanProductBarcode = ko.observable();
        self.scanProductFocus = ko.observable(false);

        self.placeholder = ko.computed(function() {
            return !self.scanProductFocus() ? PickScanModule.messages.placeholder : '';
        });

        self.placeholderOrder = ko.computed(function() {
            return !self.scanOrderFocus() ? PickScanModule.messages.placeholder : '';
        });

        self.showNav = ko.computed(function() {
            return self.step() != 'init';
        });

        self.step.subscribe(function(newVal) {
            if (newVal == 'picking_complete') {
                self.finished(moment());
                if (self.singleOrderMode() && self.currentOrder()) {
                    self.currentOrder().trackingFocus(true);
                } else {
                    var order = ko.utils.arrayFirst(self.orders(), function(order){
                        return !order.saved() && !order.trackingScanned();
                    });
                    if (order) {
                        order.trackingFocus(true);
                    }
                }
            }
        });

        self.start = function(container, e, noTime) {
            if (!noTime) {
                self.started(moment());
            }
            if (self.settings().trolley()) {
                self.nextBatch();
            } else {
                if (self.orders().length > 0) {
                    var order = ko.utils.arrayFirst(self.orders(), function(o){
                        return !o.processed();
                    });
                    if (order) {
                        self.currentOrder(order);
                        self.processOrder();
                    } else {
                        self.step('picking_complete');
                    }
                } else {
                    self.step('scan_order');
                    self.scanOrderFocus(true);
                }
            }
        };

        self.loadOrder = function(data, e) {
            var _self = self;
            if(e.keyCode === 13 || e.type == 'click'){
                self.hasError(false);
                self.hasWarning(false);
                if (!self.scanOrderId()) {
                    self.hasError(true);
                    self.error(PickScanModule.messages.enter_order_id);
                    soundManager.play('wrong');
                    return false;
                }
                self.scanOrderFocus(false);
                self.loading(true);
                $.ajax({
                    method: 'POST',
                    url: PickScanModule.loadOrderUrl,
                    data: {
                        order_id: self.scanOrderId().trim(),
                        form_key: PickScanModule.form_key
                    },
                    dataType: 'json',
                    success: function(data) {
                        self.loading(false);
                        if (data.success) {
                            _self.started(moment());
                            var order = new Order(data.order);
                            order.sortProducts();
                            _self.orders.push(order);
                            _self.start();
                            if (_self.settings().enable_correct_sound()) {
                                soundManager.play('correct');
                            }
                        } else {
                            self.hasError(true);
                            self.scanOrderId('');
                            self.scanOrderFocus(true);
                            self.error(data.message);
                            soundManager.play('wrong');
                        }

                    }
                })
            }
            return true;
        };

        self.batch = ko.observableArray();
        self.batchProducts = ko.observableArray();
        self.batchProcessedProducts = ko.computed(function(){
            var products = [];
            ko.utils.arrayForEach(self.batchProducts(), function(product){
                if (product.processed()) {
                    products.push(product);
                }
            });

            return products;
        });

        self.processedBoxes = ko.computed(function(){
            var orders = [];
            ko.utils.arrayForEach(self.batch(), function(order){
                if (order.processed()) {
                    orders.push(order);
                }
            });

            return orders;
        });

        self.batchProcessed = ko.computed(function(){
            return self.batch().length == self.processedBoxes().length;
        });

        self.prepareBatch = function() {
           var orders = ko.utils.arrayFilter(self.orders(), function(order){
               return !order.processed();
           });

           var batch = orders.slice(0, self.settings().trolleyBoxes()),
               i = 1,
               products = [];
           self.batch.removeAll();
           ko.utils.arrayForEach(batch, function(order){
                order.box(i);
                order.trolley_id(self.trolley_id());
                i++;
                ko.utils.arrayForEach(order.products(), function(product){
                    product.order(order);
                    products.push(product);
                });
               self.batch.push(order);
           });
           self.batchProducts(products);
           self.batchProducts.sort(function(left, right) {
               var left_value = left.getSortValue(),
                   right_value = right.getSortValue();

               return left_value == right_value ? 0 : (left_value < right_value ? -1 : 1)
           });
        };

        self.process = function() {
            if (self.timer) {
                clearTimeout(self.timer);
                self.timer = false;
            }
            if (self.settings().trolley()) {
                self.processBatch();
            } else {
                self.processOrder();
            }
        };

        self.substitute = function () {
            self.currentProduct().substituted(self.wrongSku());
            if (self.settings().substitution_status()) {
                self.currentOrder().newStatus(self.settings().substitution_status());
            }
            if (self.settings().substitution_flag()) {
                self.currentOrder().newFlag(self.settings().substitution_flag());
            }
            self.wrongSku('');
            self.saveSnapshot();
            self.process();
        };

        self.ignore = function() {
            self.currentProduct().ignored(true);
            if (self.settings().ignore_status()) {
                self.currentOrder().newStatus(self.settings().ignore_status());
            }
            if (self.settings().ignore_flag()) {
                self.currentOrder().newFlag(self.settings().ignore_flag());
            }
            self.wrongSku('');
            self.saveSnapshot();
            self.process();
        };

        self.preUpdateBarcode = function() {
            if (self.settings().barcodeUpdateAuth()) {
                bootbox.prompt("This operation requires admin authorization", function(result) {
                    if (result) {
                        if (result in PickScanModule.auth) {
                            self.currentProduct().newBarcodeAuth(PickScanModule.auth[result]);
                            self.updateBarcode();
                        } else {
                            bootbox.alert('Wrong auth code');
                        }
                    }
                });
            } else {
                self.updateBarcode();
            }
        };

        self.updateBarcode = function() {
            self.currentProduct().oldBarcode(self.currentProduct().barcode());
            self.currentProduct().barcode(self.wrongSku());
            self.currentProduct().newBarcode(self.wrongSku());
            self.wrongSku('');
            var barcodeCode = self.settings().barcodeAttribute();

            if (barcodeCode == self.settings().custom1Code()) {
                self.currentProduct().custom_1(self.currentProduct().barcode());
            }
            if (barcodeCode == self.settings().custom2Code()) {
                self.currentProduct().custom_2(self.currentProduct().barcode());
            }
            if (barcodeCode == self.settings().custom3Code()) {
                self.currentProduct().custom_3(self.currentProduct().barcode());
            }
            if (barcodeCode == self.settings().custom4Code()) {
                self.currentProduct().custom_4(self.currentProduct().barcode());
            }
            if (barcodeCode == self.settings().titleCode()) {
                self.currentProduct().titleText(self.currentProduct().barcode());
            }
            if (barcodeCode == self.settings().sortCode()) {
                self.currentProduct().custom_sort(self.currentProduct().barcode());
            }

            if (self.settings().barcodeAttribute() == 'sku') {
                self.currentProduct().sku(self.wrongSku());
            }
            self.doScanProduct();
        };

        self.nextBatch = function() {
            self.prepareBatch();
            if (self.batch().length > 0) {
                self.process();
            } else {
                self.step('picking_complete');
            }
        };

        self.processBatch = function() {
            self.hasError(false);
            self.hasWarning(false);
            self.hasSuccess(false);
            self.scanProductBarcode('');

            if (self.currentOrder() && self.currentOrder().processed() && !self.currentOrder().saved()) {
                self.saveOrder(self.currentOrder());
            }

            if (self.batchProcessed()) {
                var orders = ko.utils.arrayFilter(self.orders(), function(order){
                    return !order.processed();
                });
                if (!orders.length) {
                    self.step('picking_complete');
                } else {
                    self.step('batch_processed');
                }
            } else {
                var product = ko.utils.arrayFirst(self.batchProducts(), function(product){
                    return !product.processed();
                });
                self.currentProduct(product);
                self.currentOrder(product.order());
                self.step('process_product');
                self.scanProductFocus(true);
            }
        };

        self.processOrder = function() {
            self.hasError(false);
            self.hasWarning(false);
            self.hasSuccess(false);
            self.scanProductBarcode('');

            if (self.currentOrder().processed()) {
                var order = ko.utils.arrayFirst(self.orders(), function(o){
                    return !o.processed();
                });
                self.saveOrder(self.currentOrder());
                if (!order) {
                    self.step('picking_complete');
                } else {
                    if (self.settings().assign_tracking()) {
                        self.currentOrder().trackingFocus(true);
                    }
                    self.step('order_processed');
                }
            } else {
                var product = ko.utils.arrayFirst(self.currentOrder().products(), function(product){
                    return !product.processed();
                });
                self.currentProduct(product);
                self.step('process_product');
                self.scanProductFocus(true);
            }
        };

        self.skanProduct = function(data, e) {
            if(e.keyCode === 13) {
                self.hasSuccess(false);
                self.hasWarning(false);
                if (!self.scanProductBarcode()) {
                    self.hasError(true);
                    self.error(PickScanModule.messages.no_product);
                    soundManager.play('wrong');
                    return;
                }
                if (self.scanProductBarcode().trim().toLowerCase() != self.currentProduct().barcode().trim().toLowerCase()) {
                    self.hasError(true);
                    self.error(PickScanModule.messages.wrong_product);
                    self.wrongSku(self.scanProductBarcode());
                    self.scanProductBarcode('');
                    self.scanProductFocus(true);
                    soundManager.play('wrong');
                    return;
                }

                self.doScanProduct();
            }
            return true;
        };

        self.doScanProduct = function() {
            self.hasError(false);
            self.currentProduct().sku_scanned(true);
            if (self.currentProduct().qty_to_scann() > self.currentProduct().qty_remaining()) {
                self.hasError(true);
                PickScanModule.root.error(PickScanModule.messages.wrong_qty + ' ' + (self.currentProduct().qty_to_scann() - self.currentProduct().qty_remaining()));
                soundManager.play('wrong');
                return;
            }
            if (self.currentProduct().qty() != self.currentProduct().qty_scanned()) {
                self.currentProduct().qty_scanned(parseInt(self.currentProduct().qty_scanned()) + parseInt(self.currentProduct().qty_to_scann()));
            }
            self.hasSuccess(true);
            self.success(PickScanModule.messages.product_picked);
            if (self.settings().enable_correct_sound()) {
                soundManager.play('correct');
            }
            var _self = self;
            if (self.currentProduct().qty() == self.currentProduct().qty_scanned()) {
                self.currentProduct().processed(true);

                self.timer = setTimeout(function(){
                    _self.process();
                }, 2000);
            } else {
                self.scanProductBarcode('');
                self.scanProductFocus(true);
                self.timer = setTimeout(function(){
                    _self.hasSuccess(false);
                    _self.success('');
                }, 2000);
            }
        };

        self.restart = function() {
            self.loading(false);
            self.hasError(false);
            self.hasWarning(false);
            self.error('');
            self.hasSuccess(false);
            self.success('');

            self.currentOrder(null);
            self.currentProduct(null);
            self.currentBox(null);

            self.scanOrderId('');
            self.scanProductBarcode('');

            self.start();
        };

        self.totalProgress = ko.computed(function(){
            if (self.orders().length > 0) {
                return '' + Math.round(self.totalProcessProducts() / self.totalProducts() * 100) + '%';
            } else if (self.currentOrder()) {
                return self.currentOrder().totalProgress();
            }
            return '0%';
        });

        self.totalProducts = ko.computed(function(){
            var total = 0;
            ko.utils.arrayForEach(self.orders(), function(order){
                ko.utils.arrayForEach(order.products(), function(product) {
                    total += parseInt(product.qty());
                });
            });

            return total;
        });

        self.totalProcessProducts = ko.computed(function(){
            var total = 0;
            ko.utils.arrayForEach(self.orders(), function(order){
                ko.utils.arrayForEach(order.processedProducts(), function(product) {
                    total += parseInt(product.qty());
                });
            });

            return total;
        });

        self.totalProcessProductsText = ko.computed(function(){
            if (self.totalProducts() == self.totalProcessProducts()) {
                return 'All <b>' + (self.totalProcessProducts()) + '</b> ' + (self.totalProcessProducts() == 1 ? PickScanModule.messages.item : PickScanModule.messages.items) + ' ' + PickScanModule.messages.picked;
            } else {
                return '<b>' + (self.totalProcessProducts()) + '</b> ' + (self.totalProcessProducts() == 1 ? PickScanModule.messages.item : PickScanModule.messages.items) + ' ' + PickScanModule.messages.picked;
            }
        });

        self.totalLeft = ko.computed(function(){
            if (self.totalProcessProducts() == 0) {
                return '0 of <b>' + (self.totalProducts()) + '</b> '  + (self.totalProcessProducts() == 1 ? PickScanModule.messages.item : PickScanModule.messages.items) + ' ' + PickScanModule.messages.picked;
            } else {
                return '<b>' + (self.totalProducts() - self.totalProcessProducts()) + '</b>' + PickScanModule.messages.left_to_pick;
            }
        });

        self.totalProductsBatch = ko.computed(function(){
            var total = 0;
            ko.utils.arrayForEach(self.batchProducts(), function(product) {
                total += parseInt(product.qty());
            });

            return total;
        });

        self.totalProcessProductsBatch = ko.computed(function(){
            var total = 0;
            ko.utils.arrayForEach(self.batchProcessedProducts(), function(product) {
                total += parseInt(product.qty());
            });

            return total;
        });

        self.totalProcessProductsBatchText = ko.computed(function(){
            if (self.totalProductsBatch() == self.totalProcessProductsBatch()) {
                return 'All <b>' + (self.totalProcessProductsBatch()) + '</b> ' + (self.totalProcessProductsBatch() == 1 ? PickScanModule.messages.item : PickScanModule.messages.items) + ' ' + PickScanModule.messages.picked;
            } else {
                return '<b>' + (self.totalProcessProductsBatch()) + '</b> ' + (self.totalProcessProductsBatch() == 1 ? PickScanModule.messages.item : PickScanModule.messages.items) + ' ' + PickScanModule.messages.picked;
            }
        });


        self.totalProgressBatch = ko.computed(function(){
            return '' + Math.round(self.totalProcessProductsBatch() / self.totalProductsBatch() * 100) + '%';
        });

        self.totalLeftBatch = ko.computed(function(){
            if (self.totalProcessProductsBatch() == 0) {
                return '0 of <b>' + (self.totalProductsBatch()) + '</b> '  + (self.totalProductsBatch() == 1 ? PickScanModule.messages.item : PickScanModule.messages.items) + ' ' + PickScanModule.messages.picked;
            } else {
                return '<b>' + (self.totalProductsBatch() - self.totalProcessProductsBatch()) + '</b>' + PickScanModule.messages.left_to_pick;
            }
        });

        self.ordersProccessed = ko.computed(function(){
            var number = 0;
            ko.utils.arrayForEach(self.orders(), function(order) {
                if (order.processed()) {
                    number++;
                }
            });

            return number;
        });
        self.ordersSaved = ko.computed(function(){
            var number = 0;
            ko.utils.arrayForEach(self.orders(), function(order) {
                if (order.saved()) {
                    number++;
                }
            });

            return number;
        });

        self.orderTrackingSaved = ko.computed(function(){
            if (!self.settings().assign_tracking()) {
                return self.orders().length;
            }
            var number = 0;
            ko.utils.arrayForEach(self.orders(), function(order) {
                if (order.trackingSaved()) {
                    number++;
                }
            });

            return number;
        });

        self.saved = ko.computed({
            read: function() {
                return self.orders().length == self.ordersSaved();
            },
            write: function(value) {
                ko.utils.arrayForEach(self.orders(), function(order){
                    order.saved(value);
                });
                if (value) {
                    self.clearSnapshot();
                }
            }
        });

        self.allSaved = ko.computed(function() {
            return self.saved() && self.orderTrackingSaved() == self.orders().length;
        });

        self.allSaved.subscribe(function(newVal){
            if (newVal && self.settings().autoreturn() && self.singleOrderMode()) {
                location.reload();
            }
        });

        self.saved.subscribe(function(newValue){
            if (newValue) {
                self.clearSnapshot()
            }
        });

        self.productsSaved = ko.computed(function(){
            var number = 0;
            ko.utils.arrayForEach(self.orders(), function(order) {
                if (order.saved()) {
                    number += order.products().length;
                }
            });

            return number;
        });

        self.sortOrders = function() {
            if (self.orders().length > 1) {
                self.orders.sort(function (left, right) {
                    left = left.products()[0].getSortValue();
                    right = right.products()[0].getSortValue();

                    return left == right ? 0 : (left < right ? -1 : 1);
                });
            }
        };

        self.totalResults = ko.computed(function(){
            var results = [];

            if (self.started()) {
                results.push('Picking started at ' + self.started().format("hh:mm:ss"));
            }
            if (self.finished()) {
                results.push('Picking finished at ' + self.finished().format("hh:mm:ss"));
            }
            if (self.started()) {
                results.push('Picking time:' + moment.duration(self.started().diff(self.finished())).humanize());
            }

            var picked = 0;
            ko.utils.arrayForEach(self.orders(), function (order) {
                ko.utils.arrayForEach(order.products(), function (product) {
                    picked += product.qty_scanned();
                });
            });

            results.push(picked + ' Item' + (picked != 1 ? 's' : '') + ' picked');

            ko.utils.arrayForEach(self.orders(), function (order) {
                ko.utils.arrayForEach(order.products(), function (product) {
                    if (product.substituted()) {
                        results.push("Sub'd:" + product.sku() + ' > ' + product.substituted());
                    } else if (product.ignored()) {
                        results.push('Skipped: ' + (product.qty()-product.qty_scanned()) + ' x ' + product.sku());
                    } else if (product.newBarcode()) {
                        results.push('Updated: ' + product.oldBarcode() + ' > ' + product.newBarcode() + (product.newBarcodeAuth() ? '(Auth by ' + product.newBarcodeAuth() + ')' : ''));
                    }
                });

                if (order.newStatus()) {
                    results.push('New order status: ' + order.newStatus());
                }
                if (order.newFlag()) {
                    results.push('New order flag: ' + order.newFlag().flag_label() + ' - ' + order.newFlag().label());
                }
            });

            return results.join('<br/>');
        });

        self.batchResults = ko.computed(function(){
            var results = [];

            var picked = 0;
            ko.utils.arrayForEach(self.batchProducts(), function(product){
                picked += product.qty_scanned();
            });

            results.push(picked + ' Item' + (picked != 1 ? 's' : '') + ' picked');

            ko.utils.arrayForEach(self.batchProducts(), function(product){
                if (product.substituted()) {
                    results.push("Sub'd:" + product.sku() + ' > ' + product.substituted());
                } else if (product.ignored()) {
                    results.push('Skipped: ' + (product.qty()-product.qty_scanned()) + ' x ' + product.sku());
                } else if (product.newBarcode()) {
                    results.push('Updated: ' + (product.oldBarcode()) + ' > ' + product.newBarcode() + (product.newBarcodeAuth() ? '(Auth by ' + product.newBarcodeAuth() + ')' : ''));
                }
            });

            return results.join('<br/>');
        });

        self.currentComment = ko.observable();
        self.addComment = function() {
            self.currentComment(new Comment());
        };

        self.doAddComment = function() {
            if (self.currentComment().message()) {
                if (self.currentProduct()) {
                    self.currentComment().sku(self.currentProduct().sku());
                }
                self.currentOrder().comments.push(self.currentComment());
                self.currentComment(false);
                $('.top-right').notify({
                    message: { text: PickScanModule.messages.comment_added },
                    closable: false
                }).show();
            }

            $('[data-target="#addComment"]').click();
        };

        self.saveError = ko.observable(false);
        self.saveResults = function() {
            var data = self.prepareSave(),
                _self = self;
            _self.saveError(false);

            $.ajax({
                type: 'POST',
                url: PickScanModule.saveUrl,
                data: {
                    orders: ko.toJSON(data),
                    form_key: PickScanModule.form_key
                },
                dataType: 'json',
                success: function(result) {
                    if (result.errors) {
                        bootbox.alert(result.errors);
                    }
                    _self.saved(true);
                    _self.clearSnapshot();
                },
                complete: function(xhr) {
                    if (xhr.status != 200) {
                        _self.saveError('Cannot save picking results. Please check internet connection');
                    }
                }
            });
        };

        self.saveOrder = function(order) {
            var data = [self._prepareOrderSave(order)],
                _self = self;
            $.ajax({
                dataType: 'json',
                type: 'POST',
                url: PickScanModule.saveUrl,
                data: {
                    orders: ko.toJSON(data),
                    form_key: PickScanModule.form_key
                },
                success: function(result) {
                    order.saved(true);
                    if (order.tracking()) {
                        order.trackingSaved(true);
                    }
                    if (result.errors) {
                        bootbox.alert(result.errors);
                    }
                    var nextOrder = ko.utils.arrayFirst(_self.orders(), function(order){
                        return !order.saved() && !order.trackingScanned();
                    });
                    if (nextOrder) {
                        nextOrder.trackingFocus(true);
                    }
                }
            });
        };

        self.prepareSave = function() {
            var data = [];

            ko.utils.arrayForEach(self.orders(), function(order){
                if (!order.saved()) {
                    data.push(self._prepareOrderSave(order));
                }
            });
            return data;
        };

        self._prepareOrderSave = function(order) {
            var barcodeUpdates = [];
            ko.utils.arrayForEach(order.products(), function(product){
                if (product.newBarcode()) {
                    barcodeUpdates.push({
                        id: product.id(),
                        barcode: product.newBarcode(),
                        auth: product.newBarcodeAuth()
                    });
                }
            });
            return {
                id: order.id(),
                results: order.results(),
                newFlag: order.newFlag(),
                newStatus: order.newStatus(),
                comments: order.comments(),
                items_count: order.items_count(),
                substituted_count: order.substituted_count(),
                ignored_count: order.ignored_count(),
                barcode_updates: barcodeUpdates,
                tracking: order.tracking(),
                carrier: order.carrier(),
                box: order.box(),
                trolley_id: order.trolley_id()
            };
        };

        self.saveSnapshot = function() {
            $.jStorage.set('picknscan', self._getSnapshotData());
            $.jStorage.setTTL('picknscan', 2 * 60 * 60 * 1000); // 2 hours
        };

        self.restoreSnapshot = function() {
            var data = $.jStorage.get('picknscan');
            if (data) {
                self._doRestoreSnapshotData(data);
                return true;
            }
            return false;
        };

        self._getSnapshotData = function() {
            var data = {
                trolley: self.settings().trolley(),
                trolleyBoxes: self.settings().trolleyBoxes(),
                started: self.started(),
                orders: {}
            };
            $.map(self.orders(), function(order){
                data.orders[order.id()] = order.getSnapshotData();
            });

            return data;
        };

        self._doRestoreSnapshotData = function(data) {
            self.settings().trolley(data.trolley);
            self.settings().trolleyBoxes(data.trolleyBoxes);
            self.started(moment(data.started));
            $.each(data.orders, function(orderId, orderData) {
                var order = ko.utils.arrayFirst(self.orders(), function(o) {
                    return o.id() == orderId;
                });
                if (order) {
                    order.restoreSnapshotData(orderData);
                }
            });
        };

        self.clearSnapshot = function() {
            $.jStorage.flush();
        };

        var mapping = {
            settings: {
                create: function(options) {
                    return new Settings(options.data);
                }
            },
            orders: {
                create: function(options) {
                    return new Order(options.data);
                }
            }
        };
        ko.mapping.fromJS(data, mapping, self);

        self.sortOrders();
        if (self.restoreSnapshot()) {
            self.start(self, false, true);
        } else {
            if (!self.settings().allowTrolley()) {
                self.start();
            }
        }
        if (self.orders().length > 0) {
            self.singleOrderMode(false);
        }
    }

    function Settings(data) {
        var self = this;

        self.allowTrolley = ko.observable(false);
        self.trolley = ko.observable(false);
        self.trolleyBoxes = ko.observable($.cookie('trolley-count')?$.cookie('trolley-count'):2);
        self.trolleyBoxes.subscribe(function(newValue){
            $.cookie('trolley-count', newValue, { expires: 7 });
        });

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

        self.custom1Code = ko.observable();
        self.custom2Code = ko.observable();
        self.custom3Code = ko.observable();
        self.custom4Code = ko.observable();
        self.titleCode = ko.observable();
        self.sortCode = ko.observable();
        self.barcodeAttribute = ko.observable();
        self.autoreturn = ko.observable();

        self.allowSubstitution = ko.observable();
        self.substitution_status = ko.observable();
        self.substitution_flag = ko.observable();

        self.allowIgnore = ko.observable();
        self.ignore_status = ko.observable();
        self.ignore_flag = ko.observable();

        self.order_progress = ko.observable();
        self.trolley_progress = ko.observable();
        self.total_progress = ko.observable();

        self.enable_commenting = ko.observable(false);
        self.enable_correct_sound = ko.observable(false);

        self.assign_tracking = ko.observable(false);

        self.allowance = ko.observable(false);

        self.barcodeUpdateAuth = ko.observable(false);

        ko.mapping.fromJS(data, {}, self);
    }

    function Order(data) {
        var self = this;

        self.id = ko.observable();
        self.inner_id = ko.observable();
        self.box = ko.observable();
        self.trolley_id = ko.observable();
        self.saved = ko.observable(false);
        self.currency = ko.observable();

        self.products = ko.observableArray();

        self.customer_comments = ko.observableArray();
        self.admin_comments = ko.observableArray();

        self.comments = ko.observableArray();

        self.newStatus = ko.observable(false);
        self.newFlag = ko.observable(false);
        self.tracking = ko.observable('');

        self.trackingScanned = ko.observable(false);
        self.trackingSaved = ko.observable(false);

        self.tracking.subscribe(function(newVal){
            self.trackingSaved(false);
            if (newVal) {
                self.trackingScanned(true);
            }
        });

        self.saveTracking = function() {
            var _self = self;
            if (!self.trackingSaved()) {
                $.ajax({
                    type: 'POST',
                    url: PickScanModule.saveTrackingUrl,
                    data: {
                        order_id: self.inner_id(),
                        tracking: self.tracking(),
                        carrier: self.carrier(),
                        form_key: PickScanModule.form_key
                    },
                    dataType: 'json',
                    success: function (result) {
                        if (result.errors) {
                            soundManager.play('wrong');
                            bootbox.alert(result.errors);
                        } else {
                            _self.trackingSaved(true);
                        }
                    }
                });
            }
        };

        self.carrier = ko.observable('');
        self.trackingFocus = ko.observable(false);

        self.processedProducts = ko.computed(function(){
            var products = [];
            ko.utils.arrayForEach(self.products(), function(product){
                if (product.processed()) {
                    products.push(product);
                }
            });

            return products;
        });

        self.print = function(data, e) {
            e.preventDefault();
            var url = $(e.currentTarget).attr('href');
            url += '?order_id=' + self.inner_id();
            window.open(url);
        };

        self.processed = ko.computed(function(){
            return self.products().length == self.processedProducts().length;
        });

        self.icon = ko.computed(function() {
            return self.processed() ? 'fa-check-circle-o' : '';
        });

        self.currentIcon = ko.computed({
            read: function() {
                return PickScanModule.root.currentOrder() && PickScanModule.root.currentOrder().id() == self.id() ? 'fa-hand-o-right' : '';
            },
            deferEvaluation: true
        });

        self.totalProducts = ko.computed(function(){
            var total = 0;
            ko.utils.arrayForEach(self.products(), function(product) {
                total += product.qty();
            });

            return total;
        });

        self.totalProcessProducts = ko.computed(function(){
            var total = 0;
            ko.utils.arrayForEach(self.processedProducts(), function(product) {
                total += product.qty();
            });

            return total;
        });

        self.totalProcessProductsText = ko.computed(function(){
            if (self.totalProducts() == self.totalProcessProducts()) {
                return 'All <b>' + (self.totalProcessProducts()) + '</b> ' + (self.totalProcessProducts() == 1 ? PickScanModule.messages.item : PickScanModule.messages.items) + ' ' + PickScanModule.messages.picked;
            } else {
                return '<b>' + (self.totalProcessProducts()) + '</b> ' + (self.totalProcessProducts() == 1 ? PickScanModule.messages.item : PickScanModule.messages.items) + ' ' + PickScanModule.messages.picked;
            }
        });

        self.totalProgress = ko.computed(function(){
            return '' + Math.round(self.totalProcessProducts() / self.totalProducts() * 100) + '%';
        });

        self.totalLeft = ko.computed(function(){
            if (self.totalProcessProducts() == 0) {
                return '0 of <b>' + (self.totalProducts()) + '</b> '  + (self.totalProcessProducts() == 1 ? PickScanModule.messages.item : PickScanModule.messages.items) + ' ' + PickScanModule.messages.picked;
            } else {
                return '<b>' + (self.totalProducts() - self.totalProcessProducts()) + '</b>' + PickScanModule.messages.left_to_pick;
            }
        });

        self.results = ko.computed(function(){
            var results = [], picked = 0;

            ko.utils.arrayForEach(self.products(), function(product){
                picked += product.qty_scanned();
            });

            results.push(picked + ' Item' + (picked != 1 ? 's' : '') + ' picked');

            ko.utils.arrayForEach(self.products(), function(product){
                if (product.substituted()) {
                    results.push('Sub’d : ' + product.sku() + ' > ' + product.substituted());
                } else if (product.ignored()) {
                    results.push('Skipped : ' + (product.qty() - product.qty_scanned()) + ' x ' + product.sku());
                } else if (product.newBarcode()) {
                    results.push('Updated: ' + (product.oldBarcode()) + ' > ' + product.newBarcode());
                }
            });

            if (self.newStatus()) {
                results.push('New order status: ' + self.newStatus());
            }
            if (self.newFlag()) {
                results.push('New order flag: ' + self.newFlag().flag_label() + ' - ' + self.newFlag().label());
            }

            return results.join('<br/>');
        });
        self.problems = ko.computed(function(){
            var results = [];

            ko.utils.arrayForEach(self.products(), function(product){
                if (product.substituted()) {
                    results.push('Sub’d : ' + product.sku() + ' > ' + product.substituted());
                } else if (product.ignored()) {
                    results.push('Skipped : ' + (product.qty() - product.qty_scanned()) + ' x ' + product.sku());
                }
            });

            return results.join('<br/>');
        });

        self.getSnapshotData = function() {
            var data = {
                newStatus: self.newStatus(),
                newFlag: ko.mapping.toJS(self.newFlag()),
                comments: ko.mapping.toJS(self.comments()),
                saved: self.saved(),
                tracking: self.tracking(),
                carrier: self.carrier(),
                box: self.box(),
                trolley_id: self.trolley_id(),
                products: {}
            };

            $.map(self.products(), function(product){
                data.products[product.sku()] = product.getSnapshotData();
            });
            return data;
        };

        self.restoreSnapshotData = function(data) {
            self.newStatus(data.newStatus);
            if (data.newFlag) {
                self.newFlag(ko.mapping.fromJS(data.newFlag, {}));
            }
            self.comments(data.comments);
            self.saved(data.saved);
            self.tracking(data.tracking);
            self.carrier(data.carrier);

            $.each(data.products, function(productSku, productData) {
                var product = ko.utils.arrayFirst(self.products(), function(p) {
                    return p.sku() == productSku;
                });
                if (product) {
                    product.restoreSnapshotData(productData);
                }
            });
        };

        self.items_count = ko.computed(function(){
            var total = 0;
            ko.utils.arrayForEach(self.products(), function(product){
                total += parseInt(product.qty());
            });

            return total;
        });

        self.substituted_count = ko.computed(function(){
            var total = 0;
            ko.utils.arrayForEach(self.products(), function(product){
                if (product.substituted()) {
                    total += parseInt(product.qty());
                }
            });

            return total;
        });

        self.ignored_count = ko.computed(function(){
            var total = 0;
            ko.utils.arrayForEach(self.products(), function(product){
                if (product.ignored()) {
                    total += parseInt(product.qty() - product.qty_scanned());
                }
            });

            return total;
        });

        self.sortProducts = function() {
            self.products.sort(function(left, right) {
                var left_value = left.getSortValue(),
                    right_value = right.getSortValue();
                return left_value == right_value ? 0 : (left_value < right_value ? -1 : 1)
            });
        };

        self.getComment = function(product) {
            var pComment, index = 0, stop = false;
            $.map(self.comments(), function(comment){
                if (!stop) {
                    index++;
                    if (comment.sku() == product.sku()) {
                        pComment = comment;
                        stop = true;
                    }
                }
            });
            if (pComment) {
                return '<span class="fa fa-comment-o"></span> ' + index;
            } else {
                return '';
            }
        };

        self.scanTracking = function(data, e) {
            if(e.keyCode === 13) {
                self.trackingScanned(true);
                self.saveTracking();
                return false;
            }
            return true;
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
        self.sku_scanned = ko.observable(false);
        self.barcode = ko.observable();
        self.qty = ko.observable();
        self.qty_scanned = ko.observable(0);
        self.qty_to_scann = ko.observable(1);
        self.titleText = ko.observable();
        self.newBarcode = ko.observable();
        self.newBarcodeAuth = ko.observable();
        self.oldBarcode = ko.observable();
        self.price = ko.observable();
        self.allowance_price = ko.observable();

        self.qty_remaining = ko.computed(function(){
            return self.qty()*1 - self.qty_scanned()*1;
        });

        self.qty_scanned.subscribe(function(newVal){
            if (PickScanModule.root) {
                if (self.qty() == newVal) {
                    if (self.sku_scanned()) {
                        self.picked(true);
                        PickScanModule.root.hasSuccess(true);
                        PickScanModule.root.success(PickScanModule.messages.product_picked);
                        $('.qty-box').addClass('blink');
                        PickScanModule.root.timer = setTimeout(function () {
                            PickScanModule.root.process();
                        }, 2000);
                    } else {
                        $('.qty-box').addClass('scan-success');
                    }
                } else if (self.qty() < newVal) {
                    $('.qty-box').removeClass('scan-success');
                    if (self.sku_scanned()) {
                        self.picked(false);
                        PickScanModule.root.hasSuccess(false);
                        PickScanModule.root.hasError(true);
                        PickScanModule.root.error(PickScanModule.messages.wrong_qty + ' ' + (newVal - self.qty()));
                        PickScanModule.root.scanProductFocus(true);
                        PickScanModule.root.scanProductBarcode('');
                        soundManager.play('wrong');
                    }
                } else if (newVal && self.qty() > newVal) {
                    self.picked(false);
                    PickScanModule.root.hasSuccess(false);
                    PickScanModule.root.hasError(false);
                    PickScanModule.root.hasWarning(true);
                    PickScanModule.root.error('Pick ' + (self.qty() - newVal) + ' more');
                    PickScanModule.root.scanProductFocus(true);
                    PickScanModule.root.scanProductBarcode('');
                }
            }
        });

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

        self.imageSrc = ko.computed(function(){
            if (self.image() && PickScanModule.images[self.image()]) {
                return PickScanModule.images[self.image()];
            }
            return self.image();
        });

        self.zoomImage = function(data, e) {
            e.stopPropagation();
            e.preventDefault();
            if (self.image().indexOf('default_image.png') > 0) {
                return;
            }
            $('#zoomImageModal img').attr('src', self.imageSrc());
            $('#zoomImageModal').modal('show');
            $('body').on('click.zoom', function(e) {
                $(this).off('click.zoom');
                e.preventDefault();
                e.stopPropagation();
                $('#zoomImageModal').modal('hide');
                PickScanModule.root.scanProductFocus(true);
            })
        };
        self.canZoom = ko.computed(function(){
            return self.image().indexOf('default_image.png') < 0;
        })

        self.syncInProcess = false;

        self.order = ko.observable();

        self.attributes_info = ko.observableArray();
        self.customer_comments = ko.observableArray();

        self.picked = ko.observable(false);
        self.substituted = ko.observable(false);
        self.ignored = ko.observable(false);

        self.processed = ko.computed({
            read: function() {
                return self.picked() || self.substituted() || self.ignored();
            },
            write: function(newVal) {
                if (newVal) {
                    self.picked(true);
                } else {
                    self.picked(false);
                    self.substituted(false);
                    self.ignored(false);
                }
                PickScanModule.root.saveSnapshot();
            }
        });

        self.qty_scanned_text = ko.computed(function() {
            if (self.processed()) {
                if (self.picked()) {
                    return '' + self.qty_scanned() + ' x OK' + (self.newBarcode() ?  ' Barcode updated': '');
                } else if (self.substituted()) {
                    return '' + self.qty() + ' x Sub';
                } else {
                    var skipped = self.qty() - self.qty_scanned();
                    if (skipped == self.qty()) {
                        return '' + self.qty() + ' x Skip';
                    } else {
                        return  '' + self.qty_scanned() + ' x OK<br/>' + skipped + ' x Skip';
                    }
                }
            } else {
                return '' + self.qty_scanned() + ' x OK';
            }
        });

        self.status = ko.computed(function(){
            if (self.picked()) {
                return PickScanModule.messages.picked;
            } else if (self.substituted()) {
                return PickScanModule.messages.substituted_with + self.substituted();
            } else if (self.ignored()) {
                return PickScanModule.messages.ignored;
            }
        });

        self.getSnapshotData = function() {
            return {
                sku_scanned: self.sku_scanned(),
                qty_scanned: self.qty_scanned(),
                picked: self.picked(),
                substituted: self.substituted(),
                ignored: self.ignored(),
                new_barcode: self.newBarcode()
            };
        };

        self.restoreSnapshotData = function(data) {
            self.sku_scanned(data.sku_scanned);
            self.qty_scanned(data.qty_scanned);
            self.picked(data.picked);
            self.substituted(data.substituted);
            self.ignored(data.ignored);
        };

        self.unscan = function() {
            self.processed(false);
        };

        ko.mapping.fromJS(data, {}, self);
    }

    function Comment() {
        var self = this;

        self.message = ko.observable();
        self.sku = ko.observable();
    }

    PickScanModule.PickScan = PickScan;
})(jQuery);

ko.bindingHandlers.switch = {
    init: function(element, valueAccessor, allBindingsAccessor) {
        $(element).bootstrapSwitch();

        ko.utils.registerEventHandler(element, "switchChange.bootstrapSwitch", function(event, state) {
            var value = valueAccessor();
            if (ko.isObservable(value)) {
                value(state);
            }
        });
    },
    update: function(element, valueAccessor)   {
        var value = ko.utils.unwrapObservable(valueAccessor());
        $(element).bootstrapSwitch('state', value, false);
    }
};
ko.bindingHandlers.spinTrolley = {
    init: function(element, valueAccessor, allBindingsAccessor) {
        $(element).TouchSpin({
            min: 2
        });

        ko.utils.registerEventHandler(element, "change", function(event) {
            var value = valueAccessor();
            if (ko.isObservable(value)) {
                value($(element).val());
            }
        });
    },
    update: function(element, valueAccessor)   {
        var value = ko.utils.unwrapObservable(valueAccessor());
        $(element).val(value);
    }
};
ko.bindingHandlers.spin = {
    init: function(element, valueAccessor, allBindingsAccessor) {
        $(element).TouchSpin({
            min: 1,
            max: 10000
        });

        ko.utils.registerEventHandler(element, "change", function(event) {
            var value = valueAccessor();
            if (ko.isObservable(value)) {
                value($(element).val());
            }
        });
    },
    update: function(element, valueAccessor)   {
        var value = ko.utils.unwrapObservable(valueAccessor());
        $(element).val(value);
    }
};

ko.bindingHandlers.tooltip = {
    init: function(element, valueAccessor, allBindingsAccessor) {
        $(element).tooltipster();
    },
    update: function(element, valueAccessor)   {
        var value = valueAccessor();
        value = ko.utils.unwrapObservable(value);
        if (value) {
            $(element).tooltipster('content', value);
        }
    }
};