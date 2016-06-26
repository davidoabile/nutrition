
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
var AitCart = Class.create(Step,  
{
    initCart: function(productOptionsCssQuery, qtyCssQuery, increaseCssQuery, decreaseCssQuery, removeCssQuery, wishlistBtnQuery)
    {
        $$(productOptionsCssQuery).each(function(element)
        {
            element.select('input', 'select', 'textarea').each(
                function(input)
                {
                    if (input.type.toLowerCase() == 'radio' || input.type.toLowerCase() == 'checkbox') {
                        input.observe('click', this.updatePost.bind(this));
                    } else {
                        input.observe('change', this.updatePost.bind(this));    
                    }
                }.bind(this)
            );                 
        }.bind(this)); 
        
        $$(increaseCssQuery, decreaseCssQuery).each(function(element){
            element.observe('click', this.updateOptions.bind(this));    
        }.bind(this)); 
        
        $$(qtyCssQuery).each(function(element){
            element.observe('change', this.updatePost.bind(this));        
        }.bind(this)); 
         
        $$(removeCssQuery).each(function(element){
            element.observe('click', this.deletePost.bind(this));    
        }.bind(this)); 
        
        $$(wishlistBtnQuery).each(function(element){
            element.observe('click', function(event){
                var itemId = Event.element(event).value;
                $('cart-' + itemId + '-wishlist').value = 1;
                this.updatePost(event);    
            }.bind(this));    
        }.bind(this));
        
        
    }, 
    
    
    updateOptions: function(event)
    {
        var params = Form.serialize(this.getCheckout().getForm()) + '&' + 
            Object.toQueryString({step : this.name, reload_steps : this.reloadSteps.join(',')});  
        var itemRel = Event.element(event).rel;
        
        //in future need to add param @options@ to step initialize params 
        var sign = Event.element(event).hasClassName('btn-increase') ? '1' : (
                        Event.element(event).hasClassName('btn-decrease') ? '-1' : '0' );     
        this.loadWaiting();    
        this.reloadSteps.each(
            function(stepName) {
                this.getCheckout().getStep(stepName).loadWaiting();    
            }.bind(this)
        ); 
        var request = new Ajax.Request(
            this.urls.updateOptionsUrl + 'id/' + itemRel + '/sign/'+ sign,
            {
                method:'post',
                parameters: params,
                onComplete: this.onUpdateChild,
                onSuccess: this.onUpdate,
                onFailure: this.getCheckout().ajaxFailure.bind(this.getCheckout())
            }
        );   
    },

    updatePost: function(event)
    {
        var params = Form.serialize(this.getCheckout().getForm()) + '&' + 
            Object.toQueryString({step : this.name, reload_steps : this.reloadSteps.join(',')});  
        var itemRel = Event.element(event).rel;  
        this.loadWaiting();     
        this.reloadSteps.each(
            function(stepName) {
                this.getCheckout().getStep(stepName).loadWaiting();    
            }.bind(this)
        );  
        var request = new Ajax.Request(
            this.urls.updatePostUrl,
            {
                method:'post',
                parameters: params,
                onComplete: this.onUpdateChild,
                onSuccess: this.onUpdate,
                onFailure: this.getCheckout().ajaxFailure.bind(this.getCheckout())
            }
        );   
    },
    
    deletePost: function(event)
    {
        var params = Object.toQueryString({step : this.name, reload_steps : this.reloadSteps.join(',')});  
        var itemRel = Event.element(event).rel;  
        this.loadWaiting(); 
        this.reloadSteps.each(
            function(stepName) {
                this.getCheckout().getStep(stepName).loadWaiting();    
            }.bind(this)
        );  
        var request = new Ajax.Request(
            this.urls.deleteUrl + 'id/' + itemRel,
            {
                method:'post',
                parameters: params,
                onComplete: this.onUpdateChild,
                onSuccess: this.onUpdate,
                onFailure: this.getCheckout().ajaxFailure.bind(this.getCheckout())
            }
        );   
    },

    afterInit: function()
    {
        if (this.doNotInitAfter == false)
        {
            this.initCart('.product-options', '.aititemqty', '.btn-increase', '.btn-decrease', '.btn-remove2', '.btn-move-to-wishlist');

            this.setReloadSteps(['messages']);
            if (aitCheckout.getStep('shipping_method'))
            {
                this.addReloadSteps(['shipping_method']);
            } else {
                this.addReloadSteps(['payment', 'review']);
            }

            if (aitCheckout.getStep('giftmessage'))
            {
                this.addReloadSteps(['giftmessage']);
            }

            if (aitCheckout.getStep('aitgiftwrap'))
            {
                this.addReloadSteps(['aitgiftwrap']);
            }
        }
    }

});