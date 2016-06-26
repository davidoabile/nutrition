
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
var AitShippingMethod = Class.create(Step,  
    {
        initShippingMethod: function(shippingMethodContainerId)
        {
            this.initEvents(shippingMethodContainerId);
        },

        afterInit: function()
        {
            this.initShippingMethod(this.ids.loadContainer);

            if (aitCheckout.getStep('shipping'))
            {
                aitCheckout.getStep('shippinglocation').setReloadSteps(['shipping_method']);
            }
            else
            {
                aitCheckout.getStep('billinglocation').setReloadSteps(['shipping_method']);
                aitCheckout.getStep('billinglocation').initVirtualUpdate();
            }

            this.setReloadSteps(['payment', 'review']);
        }
    });