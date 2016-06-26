
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
var AitMagentoReview = Class.create( Review,
    {
        save: function()
        {
            var validator = new Validation(aitCheckout.getForm());
            if (validator && validator.validate())
            {
                this.setLoadWaiting();
                if (0 < Ajax.activeRequestCount)
                {
                    aitCheckout.runSaveAfterUpdate = true;
                    return;
                } else {
                    aitCheckout.runSaveAfterUpdate = false;
                }

                var params = Form.serialize(aitCheckout.getForm());
                if (this.agreementsForm) {
                    params += '&'+Form.serialize(this.agreementsForm);
                }
                params.save = true;

                var request = new Ajax.Request(
                    this.saveUrl,
                    {
                        method:'post',
                        parameters:params,
                        onComplete: this.onComplete,
                        onSuccess: this.onSave,
                        onFailure: aitCheckout.ajaxFailure.bind(aitCheckout)
                    }
                );
            } else {
                if (0 < Ajax.activeRequestCount)
                {
                    aitCheckout.valid = false;
                }
            }

        },

        setLoadWaiting: function()
        {
            var container = $('checkout-buttons-container');
            container.addClassName('disabled');
            container.setStyle({opacity:.8});
            this._disableEnableAll(container, true);
            Element.show('checkout-please-wait');
        },

        resetLoadWaiting: function()
        {
            var container = $('checkout-buttons-container');
            container.removeClassName('disabled');
            container.setStyle({opacity:1});
            this._disableEnableAll(container, false);
            Element.hide('checkout-please-wait');
        },

        _disableEnableAll: function(element, isDisabled) {
            var descendants = element.descendants();
            for (var k in descendants) {
                descendants[k].disabled = isDisabled;
            }
            element.disabled = isDisabled;
        },

        nextStep: function(transport){
            if (transport && transport.responseText) {
                try{
                    response = eval('(' + transport.responseText + ')');
                }
                catch (e) {
                    response = {};
                }

                // check for errors
                var msg = response.error_messages;
                if (typeof(msg)=='object') {
                    msg = msg.join("\n");
                }
                if (msg) {
                    alert(msg);
                    return;
                }

                //SagePay messages
                msg = response.response_status_detail;
                if (msg) {
                    alert(msg);
                    return;
                }

                if (response.redirect) {
                    location.href = response.redirect;
                    return;
                }
                if (response.success) {
                    this.isSuccess = true;
                    window.location=this.successUrl;
                }

                // necessary for paypal post-save review block
                if(response.update_presection) {
                    var step = aitCheckout.getStep(response.update_presection.name);
                    var container = step.getContainer();
                    container.update(response.update_presection.html);
                }

                if (response.update_section) {
                    $('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
                }

                if (response.goto_section) {
                    checkout.gotoSection(response.goto_section);
                    checkout.reloadProgressBlock();
                }
            }
        }
    });