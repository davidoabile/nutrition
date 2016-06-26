
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
    }
});