
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
var AitCheckout = Class.create();
AitCheckout.prototype = {
    _isDisabled: false,
    _statusChanged: false,
    initialize: function(form, urls) 
    {
        this.form = form;
        this.ajaxUpdateUrl = urls.ajaxUpdateUrl;
        this.failureUrl = urls.failureUrl;
		this.refreshUrl = urls.refreshUrl;
        this.steps = [];
        
        this.sagepayTokenSuccess = false;
        this.runSaveAfterUpdate = false;
        this.valid = true;
        
        this.cache = [];
        
        $$('.checkout-types').each(function(container) {
            $(container).select('button').each(function(btn) {
                btn.onclick = '';
                btn.observe('click', function(event) {
                    Effect.ScrollTo(this.form); 
                }.bind(this))
            }.bind(this));
        }.bind(this));  
        
        Ajax.Responders.register({
            onComplete: function(transport) 
            {        
                if (0 < Ajax.activeRequestCount)
                {
                    return false;
                }
                if (this.runSaveAfterUpdate)
                {
                    //submit order after update if button "Place Order" clicked.
                    if (typeof SuiteConfig != 'undefined')
                    {
                        //compatibility with SagePaySuite
                        document.fire('sagepay:submit');
                    } else {
                        review.save.bind(review).defer();
                    }
                    
                }
            }.bind(this)
        });
        
    },
    
    ajaxFailure: function(){
        window.location = this.failureUrl;
    },
    
    getForm: function()
    {
        return this.form;
    },
    
    getValidator: function()
    {
        return new Validation(this.form);
    },
    
    setStep: function(name, step)
    {
        this.steps[name] = step;
        return this;
    },
    
    getStep: function(name)
    {
        if (this.steps[name]) 
        {
            return this.steps[name]; 
        }
    },
    
    disableCheckout: function() {
        this.setCheckoutStatus(true);
    },
    
    enableCheckout: function() {
        this.setCheckoutStatus(false);
    },
    
    setCheckoutStatus: function(status) {
        if(this._isDisabled != status) {
            this._statusChanged = true;
        }
        this._isDisabled = status;
    },
    
    isDisabled: function() {
        return this._isDisabled;
    },
    
    isStatusChanged: function() {
        return this._statusChanged;
    },

    getClassNameAitoc: function(name) {
        var arName = name.split('_');
        name = '';
        for (var i = 0; i < arName.length; i++)
        {
            name += arName[i].charAt(0).toUpperCase() + arName[i].slice(1);
        }
        return 'Ait'+name;
    },

    createStep: function(name, urls, options) {
        classname = this.getClassNameAitoc(name);
        var step = new window[classname](name, aitCheckout, urls, options );
        aitCheckout.setStep(name, step);
        aitCheckout.getStep(name).afterSet();
    },
	
	refresh: function(steps)
    {
        steps.each(
            function(stepName) {
                this.getStep(stepName).loadWaiting();
            }.bind(this)
        );
        var params = Form.serialize(this.getForm()) + '&' +
            Object.toQueryString({step : steps[0], reload_steps : steps.join(',')});

        var request = new Ajax.Request(
            this.refreshUrl,
            {
                method: 'post',
                onComplete: this.getStep(steps[0]).onUpdateChild,
                onSuccess: this.getStep(steps[0]).onUpdate,
                parameters: params,
                onFailure: this.ajaxFailure.bind(this)
            }
        );
    }
}

if (Prototype.Browser.IE && Prototype.Version.substr(0,3)=='1.6') {     
    //rewriting prototype 1.6 IE selector function, to be able to select some elements several times
    Object.extend(Selector.handlers, {
        unmark: function(nodes) {
            for (var i = 0, node; node = nodes[i]; i++) {
                node.removeAttribute('_countedByPrototype');
                //in unique() function it checks if(!node._countedByPrototype) and this code must set this value to false or undefined, but for some reason it don't
                //because of that we setting it by force
                if(node._countedByPrototype) node._countedByPrototype = false;
            }
            return nodes;
        }
    });
}