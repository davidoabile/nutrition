
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
var AitBilling = Class.create(AitAddress,  
{   
    guestCaptcha: null,
    registerCaptcha: null,
    passwordContainerId: null,
    
    initRegister: function(checkboxId, passwordContainerId)
    {
        this.onPasswordUpdate = this.reupdate.bindAsEventListener(this);
		
        if ($(passwordContainerId)){
            this.passwordContainerId = passwordContainerId;
        }
        if ($(checkboxId)) 
        {
            $(checkboxId).observe('click', this.onRegisterChange.bind(this, checkboxId));                  
        }
		
		
        this.initEvents(this.passwordContainerId);
    },
    
    initCaptcha: function(guestCaptchaId, registerCaptchaId, captchaMethod)
    {
        if($(guestCaptchaId)) {
            this.guestCaptcha = $(guestCaptchaId);
            this.initEvents(guestCaptchaId);
        } else {
            this.guestCaptchaId = false;
        }
        if($(registerCaptchaId)) {
            this.registerCaptcha = $(registerCaptchaId);
            this.initEvents(registerCaptchaId);
        } else {
            this.registerCaptchaId = false;
        }
        this.setCaptchaMethod(captchaMethod);
    },
    
    onRegisterChange: function(checkboxId, event)
    {
        this.setEnable(this.passwordContainerId,$(checkboxId).checked);
        if ($(checkboxId).checked) {
            Element.show(this.passwordContainerId);
            var method = 'register';
            this.setCaptchaMethod(true);
/** Preventing cross request data lost **/
            var request = new Ajax.Request(
                this.urls.saveMethodUrl, {method: 'post', parameters: {method : method}}
            );
/****************** End *****************/
        } else {
            Element.hide(this.passwordContainerId);
            var method = 'guest'; 
            this.setCaptchaMethod(false);
/** Preventing cross request data lost **/
            var request = new Ajax.Request(
                this.urls.saveMethodUrl, 
                {method: 'post',
				onComplete: this.onPasswordUpdate,
                parameters: {method : method}}
            );
/****************** End *****************/
        }
    },
    
    setEnable: function(containerId,bEnable)
    {
        if ($(containerId))
        {
            if (bEnable)
            {
                $(containerId).select('input').invoke('enable');
            }else{
                $(containerId).select('input').invoke('disable');
            }
        }    
    },
    
    
    setCaptchaMethod: function(show_req) 
    {
        if(this.guestCaptcha) {
            if(show_req) {
                this.guestCaptcha.hide();
                this.guestCaptcha.next().hide();
            } else {
                this.guestCaptcha.show();
                this.guestCaptcha.next().show();                
            }
        }
        if(this.registerCaptcha) {
            if(show_req) {
                this.registerCaptcha.show();
                this.registerCaptcha.next().show();
            } else {
                this.registerCaptcha.hide();
                this.registerCaptcha.next().hide();                
            }
        }
    },
    
    stepErrorHandler: function(stepResponse)
    {
        this.clearCaptcha(stepResponse);
    },
    
    stepErrorResolveHandler: function(stepResponse)
    {
        if(typeof(stepResponse.hide_captcha)!= 'undefined') {
            var el = $(stepResponse.hide_captcha);
            if(el) {
                el.hide();
                el.next().hide();
                //disable further functional with captcha
                if(el.id == this.guestCaptcha.id) {
                    this.guestCaptcha = false;
                } else {
                    this.registerCaptcha = false;
                }
            }
        }
        this.clearCaptcha(stepResponse);
    },
    
    initVirtualUpdate: function()
    {
        Event.observe(window, 'load', this.update.bind(this));    
    },
    
    reupdate : function()
    {
        this.getCheckout().getStep('billing').update();
    },

    afterInit: function()
    {
        this.initAddress(this.ids.addressSelect, this.ids.addressForm, 'billing');
        this.initAdditional(this.ids.requiredFields);
        this.initRegister(this.ids.billingRegister, this.ids.customerPassword);
        this.initCaptcha(this.ids.guestCaptcha, this.ids.registerCaptcha, this.captchaOption);

        aitCheckout.createStep(this.name + 'location',this.urls, {
            doCheckErrors : this.doCheckErrors,
            isLoadWaiting : this.isLoadWaiting,
            isUpdateOnReload : this.isUpdateOnReload,
            container : this.container + 'location',
            parent : this
        });
    },

    afterSet: function()
    {
        if(this.isVirtual) {
            aitCheckout.getStep('billing').initVirtualUpdate();
        }
    }


});