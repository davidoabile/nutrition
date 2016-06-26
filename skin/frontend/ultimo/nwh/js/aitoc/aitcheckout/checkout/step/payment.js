
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
var AitPayment = Class.create(Step,  
{
    initPayment: function(paymentContainerId)
    {
        this.initEvents(paymentContainerId);
        $(paymentContainerId).select('input[type="radio"]').each(function(input){
            input.addClassName('validate-one-required-by-name');
        });                
    },

    afterInit: function()
    {
        this.initPayment(this.ids.paymentMethodLoad);
        this.setReloadSteps(['review']);
    },
     _updateRequest: function(name)
        {
            this.reloadSteps.each(
                function(stepName) {
                    this.getCheckout().getStep(stepName).loadWaiting();    
                }.bind(this)
            );
            var params = Form.serialize(this.getCheckout().getForm()) + '&' + 
            Object.toQueryString({step : name, reload_steps : this.reloadSteps.join(',')});
            if (typeof window['preparePayment'] === 'function') {
                preparePayment(this);
            }
            var request = new Ajax.Request(
                this.checkout.ajaxUpdateUrl,
                {
                    method: 'post',
                    onComplete: function(transport) {
                        update_uniform();
                        this.onUpdateChild
                    },
                    onSuccess: this.onUpdate,
                    parameters: params,
                    onFailure: this.getCheckout().ajaxFailure.bind(this.getCheckout())
                }
            );
    },
    
});