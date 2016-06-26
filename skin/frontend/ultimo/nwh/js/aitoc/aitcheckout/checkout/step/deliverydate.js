
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
var AitDeliverydate = Class.create(Step,  
{
    initDeliverydate: function()
    {
        this.initEvents(this.container); 
    },

    afterInit: function()
    {
        this.initDeliverydate();
    }
    
});