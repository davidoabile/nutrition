
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
var AitGiftcard = Class.create(Step,
{
    initGiftcard: function(applyId, cancelId)
    {
        if ($(applyId))
        {
            $(applyId).observe('click', this.onChangeStepData.bind(this));
        }
        if ($(cancelId))
        {
            $(cancelId).observe('click', this.onChangeStepData.bind(this));
        }
       
    },   
    
    update: function(event)
    {
        var params = Form.serialize(this.getCheckout().getForm()) + '&' + 
            Object.toQueryString({step : this.name, reload_steps : this.reloadSteps.join(',')});
        var validator = new Validation(this.container);
        
        if (validator && validator.validate())
        { 
            this.reloadSteps.each(
                function(stepName) {
                    this.getCheckout().getStep(stepName).loadWaiting();    
                }.bind(this)
            );    
            
            var request = new Ajax.Request(
                this.urls.giftcardUpdateUrl,
                {
                    method: 'post',
                    onComplete: this.onUpdateChild,
                    onSuccess: this.onUpdate,
                    parameters: params
                }
            );
        }
            
    },
    
    onUpdateResponseAfter: function(response)
    {
        var notice = $('giftcard-notice');
        if (response.giftcard.length != 0)
        {
            if (response.giftcard.error == 0)
            {
                notice.addClassName('success-msg');  
            } else if (response.giftcard.error == -1)
            {
                notice.addClassName('error-msg');
            } else if (response.giftcard.error == 1)
            {
                notice.addClassName('notice-msg');    
            }
            notice.update(response.giftcard.message);
            $('giftcard-notice').show();
        };
        update_uniform();
    },

    afterInit: function()
    {
        this.setReloadSteps(['giftcard']);
        if (this.reloadMessage) {
            this.addReloadSteps(['messages']);
        }

        if (aitCheckout.getStep('shipping_method') && $$('input:checked[type="radio"][name="shipping_method"]').pluck('value').length)
        {
            this.addReloadSteps(['shipping_method']);
        } else {
            this.addReloadSteps(['payment', 'review']);
        }

        if (aitCheckout.getStep('aitgiftwrap'))
        {
            this.addReloadSteps(['aitgiftwrap']);
        }
    }
          
});