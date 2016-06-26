
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
var AitGiftmessage = Class.create(Step,
{
    initGiftMessage: function(allowCheckboxId, allowForOrderCheckboxId, allowForItemsCheckboxId)
    {
        $(allowCheckboxId, allowForOrderCheckboxId, allowForItemsCheckboxId).each(function(input) {
            if(input!=null)
            {
                var source = input.id;
                var objects = [input.id + '-container'];
                this.toogleVisibilityOnObjects(source, objects);
                input.observe('click', function(event) {
                    this.toogleVisibilityOnObjects(source, objects);        
                }.bind(this));
            }
        }.bind(this));
        
        $(this.container).select('input', 'textarea').each(
            function(input)
            {
                Event.observe(input, 'change', this.onChangeStepData.bind(this));
            }.bind(this)
        );
    },
    
    toogleVisibilityOnObjects: function(source, objects) {
        if($(source) && $(source).checked) {
            objects.each(function(item){
                $(item).show();
                $$('#' + item + ' .input-text').each(function(item) {
                    item.removeClassName('validation-passed');
                });
            });


        } else {
            objects.each(function(item){  
                $(item).hide();
                $$('#' + item + ' .input-text').each(function(sitem) {
                    sitem.addClassName('validation-passed');
                });

                $$('#' + item + ' .giftmessage-area').each(function(sitem) {
                    sitem.value = '';
                });
                $$('#' + item + ' .checkbox').each(function(sitem) {
                    sitem.checked = false; 
                    this.toogleVisibilityOnObjects(sitem.id, [sitem.id + '-container']);
                }.bind(this));
                $$('#' + item + ' .select').each(function(sitem) {
                    sitem.value = '';
                });
                $$('#' + item + ' .price-box').each(function(sitem) {
                    sitem.addClassName('no-display');
                });
            }.bind(this));
        }
    },

    afterInit:function()
    {
        this.initGiftMessage(this.ids.allowGift, this.ids.allowGiftForOrder, this.ids.allowGiftForItems);

        if (aitCheckout.getStep('review') && this.isShowCartInCheckout)
        {
            aitCheckout.getStep('review').addReloadSteps(['giftmessage']);
        }
    }
});