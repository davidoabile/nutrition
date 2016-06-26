
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
var directPost = Class.create();
directPost.prototype = {
    initialize : function(methodCode, iframeId, controller, orderSaveUrl,
            cgiUrl, nativeAction) {
        this.iframeId = iframeId;
        this.controller = controller;
        this.orderSaveUrl = orderSaveUrl;
        this.nativeAction = nativeAction;
        this.cgiUrl = cgiUrl;
        this.code = methodCode;
        this.inputs = ['cc_type', 'cc_number', 'expiration', 'expiration_yr', 'cc_cid'];
        this.headers = [];
        this.isValid = true;
        this.paymentRequestSent = false;
        this.isResponse = false;
        this.orderIncrementId = false;
        this.successUrl = false;
        this.hasError = false;
        this.tmpForm = false;

        this.onSaveOnepageOrderSuccess = this.saveOnepageOrderSuccess.bindAsEventListener(this);
        this.onLoadIframe = this.loadIframe.bindAsEventListener(this);
        this.onLoadOrderIframe = this.loadOrderIframe.bindAsEventListener(this);
        this.onSubmitAdminOrder = this.submitAdminOrder.bindAsEventListener(this);

        this.preparePayment();
    },

    validate : function() {
	    //start of fix for #0028366 bug
	    var validator = new Validation(aitCheckout.getForm());
        if (!validator || !validator.validate())
		{
		    this.isValid = false;return this.isValid;
		}
		//end of fix for #0028366 bug
		
        this.isValid = true;
        this.inputs.each(function(elemIndex) {
            if ($(this.code + '_' + elemIndex)) {
                if (!Validation.validate($(this.code + '_' + elemIndex))) {
                    this.isValid = false;
                }
            }
        }, this);

        return this.isValid;
    },

    changeInputOptions : function(param, value) {
        this.inputs.each(function(elemIndex) {
            if ($(this.code + '_' + elemIndex)) {
                $(this.code + '_' + elemIndex).writeAttribute(param, value);
            }
        }, this);
    },

    preparePayment : function() {
        this.changeInputOptions('autocomplete', 'off');
        if ($(this.iframeId)) {
            switch (this.controller) {
                case 'onepage':
                case 'checkout':
                    //this.headers = $$('#' + checkout.accordion.container.readAttribute('id') + ' .section');
                    this.controller = 'onepage';
                    var button = $('checkout-buttons-container').down('button');
                    button.writeAttribute('onclick', '');
                    button.stopObserving('click');
                    button.observe('click', function() {
                        if ($(this.iframeId)) {
                            if (this.validate()) {
                                this.saveOnepageOrder();
                            }
                        } else {
                            review.save();
                        }
                    }.bind(this));
                    break;
                case 'sales_order_create':
                case 'sales_order_edit':
                    var buttons = document.getElementsByClassName('scalable save');
                    for ( var i = 0; i < buttons.length; i++) {
                        buttons[i].writeAttribute('onclick', '');
                        buttons[i].observe('click', this.onSubmitAdminOrder);
                    }
                    $('order-' + this.iframeId).observe('load', this.onLoadOrderIframe);
                    break;
            }

            $(this.iframeId).observe('load', this.onLoadIframe);
        }
    },

    loadIframe : function() {
        if (this.paymentRequestSent) {
            switch (this.controller) {
                case 'onepage':
                    this.paymentRequestSent = false;
                    if (!this.hasError) {
                        this.returnQuote();
                    }
                    break;
                case 'sales_order_edit':
                case 'sales_order_create':
                    if (!this.orderRequestSent) {
                        this.paymentRequestSent = false;
                        if (!this.hasError) {
                            this.returnQuote();
                        } else {
                            this.changeInputOptions('disabled', false);
                            toggleSelectsUnderBlock($('loading-mask'), true);
                            $('loading-mask').hide();
                            enableElements('save');
                        }
                    }
                    break;
            }
            if (this.tmpForm) {
                document.body.removeChild(this.tmpForm);
            }
        }
    },

    loadOrderIframe : function() {
        if (this.orderRequestSent) {
            $(this.iframeId).hide();
            var data = $('order-' + this.iframeId).contentWindow.document.body.innerHTML;
            this.saveAdminOrderSuccess(data);
            this.orderRequestSent = false;
        }
    },

    showError : function(msg) {
        this.hasError = true;
        if (this.controller == 'onepage') {
            $(this.iframeId).hide();
            this.resetLoadWaiting();
        }
        alert(msg);
    },

    returnQuote : function() {
        var url = this.orderSaveUrl.replace('place', 'returnQuote');
        new Ajax.Request(url, {
            onSuccess : function(transport) {
                try {
                    response = eval('(' + transport.responseText + ')');
                } catch (e) {
                    response = {};
                }
                if (response.error_message) {
                    alert(response.error_message);
                }
                $(this.iframeId).show();
                switch (this.controller) {
                    case 'onepage':
                        this.resetLoadWaiting();
                        break;
                    case 'sales_order_edit':
                    case 'sales_order_create':
                        this.changeInputOptions('disabled', false);
                        toggleSelectsUnderBlock($('loading-mask'), true);
                        $('loading-mask').hide();
                        enableElements('save');
                        break;
                }
            }.bind(this)
        });
    },

    setLoadWaiting : function() {
        this.headers.each(function(header) {
            header.removeClassName('allow');
        });
       
        var container = $('checkout-buttons-container');
        container.addClassName('disabled');
        container.setStyle({opacity:.5});
        Element.show('checkout-please-wait'); 
        
    },

    resetLoadWaiting : function() {
        this.headers.each(function(header) {
            header.addClassName('allow');
        });
        var container = $('checkout-buttons-container');
        container.removeClassName('disabled');
        container.setStyle({opacity:1});
        Element.hide('checkout-please-wait'); 
    },

    saveOnepageOrder : function() {
        this.hasError = false;
        this.setLoadWaiting();
        var params = Form.serialize(aitCheckout.getForm());
        if (this.agreementsForm) {
            params += '&'+Form.serialize(AitCheckout.agreementsForm);
        }
        params += '&controller=' + this.controller;
        new Ajax.Request(this.orderSaveUrl, {
            method : 'post',
            parameters : params,
            onComplete : this.onSaveOnepageOrderSuccess,
            onFailure : function(transport) {
                this.resetLoadWaiting();
                if (transport.status == 403) {
                    checkout.ajaxFailure();
                }
            }
        });
    },

    saveOnepageOrderSuccess : function(transport) {
        if (transport.status == 403) {
            checkout.ajaxFailure();
        }
        try {
            response = eval('(' + transport.responseText + ')');
        } catch (e) {
            response = {};
        }

        if (response.success && response.directpost) {
            this.orderIncrementId = response.directpost.fields.x_invoice_num;
            var paymentData = {};
            for ( var key in response.directpost.fields) {
                paymentData[key] = response.directpost.fields[key];
            }
            var preparedData = this.preparePaymentRequest(paymentData);
            this.sendPaymentRequest(preparedData);
        } else {
            var msg = response.error_messages;
            if (typeof (msg) == 'object') {
                msg = msg.join("\n");
            }
            if (msg) {
                alert(msg);
            }

            if (response.update_section) {
                $('checkout-' + response.update_section.name + '-load').update(response.update_section.html);
                response.update_section.html.evalScripts();
            }

            if (response.goto_section) {
                checkout.gotoSection(response.goto_section);
                checkout.reloadProgressBlock();
            }
        }
        this.resetLoadWaiting();
    },

    submitAdminOrder : function() {
        if (editForm.validate()) {
            var paymentMethodEl = $(editForm.formId).getInputs('radio','payment[method]').find(function(radio) {
                return radio.checked;
            });
            this.hasError = false;
            if (paymentMethodEl.value == this.code) {
                toggleSelectsUnderBlock($('loading-mask'), false);
                $('loading-mask').show();
                setLoaderPosition();
                this.changeInputOptions('disabled', 'disabled');
                this.paymentRequestSent = true;
                this.orderRequestSent = true;
                $(editForm.formId).writeAttribute('action', this.orderSaveUrl);
                $(editForm.formId).writeAttribute('target',
                        $('order-' + this.iframeId).readAttribute('name'));
                $(editForm.formId).appendChild(this.createHiddenElement('controller', this.controller));
                disableElements('save');
                $(editForm.formId).submit();
            } else {
                $(editForm.formId).writeAttribute('action', this.nativeAction);
                $(editForm.formId).writeAttribute('target', '_top');
                disableElements('save');
                $(editForm.formId).submit();
            }
        }
    },

    recollectQuote : function() {
        var area = [ 'sidebar', 'items', 'shipping_method', 'billing_method', 'totals', 'giftmessage' ];
        area = order.prepareArea(area);
        var url = order.loadBaseUrl + 'block/' + area;
        var info = $('order-items_grid').select('input', 'select', 'textarea');
        var data = {};
        for ( var i = 0; i < info.length; i++) {
            if (!info[i].disabled && (info[i].type != 'checkbox' || info[i].checked)) {
                data[info[i].name] = info[i].getValue();
            }
        }
        data.reset_shipping = true;
        data.update_items = true;
        if ($('coupons:code') && $F('coupons:code')) {
            data['order[coupon][code]'] = $F('coupons:code');
        }
        data.json = true;
        new Ajax.Request(url, {
            parameters : data,
            loaderArea : 'html-body',
            onSuccess : function(transport) {
                $(editForm.formId).submit();
            }.bind(this)
        });

    },

    saveAdminOrderSuccess : function(data) {
        try {
            response = eval('(' + data + ')');
        } catch (e) {
            response = {};
        }

        if (response.directpost) {
            this.orderIncrementId = response.directpost.fields.x_invoice_num;
            var paymentData = {};
            for ( var key in response.directpost.fields) {
                paymentData[key] = response.directpost.fields[key];
            }
            var preparedData = this.preparePaymentRequest(paymentData);
            this.sendPaymentRequest(preparedData);
        } else {
            if (response.redirect) {
                window.location = response.redirect;
            }
            if (response.error_messages) {
                var msg = response.error_messages;
                if (typeof (msg) == 'object') {
                    msg = msg.join("\n");
                }
                if (msg) {
                    alert(msg);
                }
            }
        }
    },

    preparePaymentRequest : function(data) {
        if ($(this.code + '_cc_cid')) {
            data.x_card_code = $(this.code + '_cc_cid').value;
        }
        var year = $(this.code + '_expiration_yr').value;
        if (year.length > 2) {
            year = year.substring(2);
        }
        var month = parseInt($(this.code + '_expiration').value, 10);
        if (month < 10) {
            month = '0' + month;
        }

        data.x_exp_date = month + '/' + year;
        data.x_card_num = $(this.code + '_cc_number').value;

        return data;
    },

    sendPaymentRequest : function(preparedData) {
        this.recreateIframe();
        this.tmpForm = document.createElement('form');
        this.tmpForm.style.display = 'none';
        this.tmpForm.enctype = 'application/x-www-form-urlencoded';
        this.tmpForm.method = 'POST';
        document.body.appendChild(this.tmpForm);
        this.tmpForm.action = this.cgiUrl;
        this.tmpForm.target = $(this.iframeId).readAttribute('name');
        this.tmpForm.setAttribute('target', $(this.iframeId).readAttribute('name'));

        for ( var param in preparedData) {
            this.tmpForm.appendChild(this.createHiddenElement(param, preparedData[param]));
        }

        this.paymentRequestSent = true;
        this.tmpForm.submit();
    },

    createHiddenElement : function(name, value) {
        var field;
        if (isIE) {
            field = document.createElement('input');
            field.setAttribute('type', 'hidden');
            field.setAttribute('name', name);
            field.setAttribute('value', value);
        } else {
            field = document.createElement('input');
            field.type = 'hidden';
            field.name = name;
            field.value = value;
        }

        return field;
    },

    recreateIframe : function() {
        if ($(this.iframeId)) {
            var nextElement = $(this.iframeId).next();
            var src = $(this.iframeId).readAttribute('src');
            var name = $(this.iframeId).readAttribute('name');
            $(this.iframeId).stopObserving();
            $(this.iframeId).remove();
            var iframe = '<iframe id="' + this.iframeId + 
                '" allowtransparency="true" frameborder="0"  name="' + name + 
                '" style="display:none;width:100%;background-color:transparent" src="' + src + '" />';
            Element.insert(nextElement, {'before':iframe});
            $(this.iframeId).observe('load', this.onLoadIframe);
        }
    }
};