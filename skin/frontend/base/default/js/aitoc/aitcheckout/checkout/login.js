
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
var AitLogin = Class.create();
AitLogin.prototype = {
    wait:null,
    popup:null,
    count: 0,
    type: 0,
    lastRequestId: null,
    alists: ['forgotpassword', 'backtologin', 'backtologin'],
    
    initialize: function(name, container, popup, urls, options, buttons)
    {
        this.name = name;
        this.container = container;
        this.popup = popup;
        this.urls = urls;
        this.options = options;
        this.wait = $(this.name+'-waiting');
        this.notice = $(this.name+'-notice');
        
        Event.observe($(this.name+'-forgotpassword'),   'click', this.showForgot.bind(this));
        Event.observe($(this.name+'-backtologin'),      'click', this.showLogin.bind(this));
        Event.observe($(this.name+'-backtocheckout'),   'click', this.hide.bind(this));
        for(var i=0;i<buttons.length;i++) {
            Event.observe($(this.name+'-'+buttons[i]),  'click', this.submit.bind(this));
        }
        inputs = $$('#aitcheckout-login input');
        for(var i=0;i<inputs.length;i++) {
            Event.observe(inputs[i],  'keypress', function(evt){if(evt.keyCode == Event.KEY_RETURN){this.submit();}}.bind(this));
        }
        this.showBlock(0);
		var copyThis = this;
		document.observe("dom:loaded", function() {
            if($('forgotB')) {
			    $('forgotB').observe('click', function(event) {
                    aitLoginPopup.show();
                    copyThis.type = 1;
	                copyThis.showBlock(1);
			    });
            }
		});
    },
    
    showForgot: function(e) {
        Event.stop(e);
        this.type = 1;
        this.showBlock(1);
    },
    
    showLogin: function(e) {
        Event.stop(e);
        this.type = 0;
        this.showBlock(0);
    },
    
    showBlock: function(id) {
        this.hideNotice();
        var i, el, shown = '';
        for(i=0;i<this.alists.length;i++) {
            el = $(this.name+'-'+this.alists[i]);
            if(!el) continue;
            if(i==id) {
                shown = this.alists[i];
                el.show();
            } else {
                if(shown=='' || shown!=this.alists[i])
                    el.hide();
            }
        }
        for(j in this.options) {
            for(i=0;i<this.options[j].length;i++) {
                el = $(this.name+'-'+this.options[j][i]);
                if(j == id) {
                    el.show();
                } else {
                    el.hide();
                }
            }
        }
    },
    
    hide: function(e) {
        this.popup.hide();
        if(e!=false) {
            this.showLogin(e);
            Event.stop(e);
        }
        return false;
    },
    
    getForm: function() {
        return this.name+'-form';
    },
    
    submit: function() 
    {
        var validator = new Validation(this.container);
        if ( validator && validator.validate())
        {
            this.disable();
            var params, url;
            switch(this.type) {
                case 1:
                    this.lastRequestId = 'user_forgotpassword';
                    params = {            
                        'email':$('forgot_email').value
                    };
                    var el = $('captcha_user_forgotpassword');
                    if(el) {
                        params[ el.name ] = el.value;
                    }
                    url = this.urls.ajaxForgotUrl;
                break;
                default:
                    this.lastRequestId = 'user_login';
                    params = {            
                        'login[username]':$('email').value,
                        'login[password]':$('pass').value
                    };
                    var el = $('captcha_user_login');
                    if(el) {
                        params[ el.name ] = el.value;
                    }
                    url = this.urls.ajaxLoginUrl;
                break;
            }
            params.formId = this.lastRequestId;
            
            var request = new Ajax.Request(
                url,
                {
                    method: 'post', 
                    parameters: params,
                    evalJSON: 'force', 
                    onSuccess: this.onUpdate.bind(this)
                }
            );
        }
        return false;
    },
    
    onUpdate : function(transport) {
        this.enable();
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if(this.isError(response)) {
            this.clearCaptcha(response);
            return false;
        }
        if(typeof(response.response) != 'undefined' && response.response == 'success') {
            if (typeof(response.redirect)!='undefined') {
                this.hide(false);
                location.href = response.redirect;
            }
            if (typeof(response.message)!='undefined') {
                this.showBlock(2);
                this.addNotice(response.message,'success-msg');
                this.clearCaptcha(response);
            }
            return;
        }
    },
    
    clearCaptcha: function(response) {
        if(typeof(response.captcha_image) != 'undefined') {
            if(!$(response.form_id)) {
                var html = $('captcha_code').innerHTML;
                html = html.replace(/captcha_id/g, response.form_id);
                var target = false;
                if(response.form_id == 'user_login') {
                    target = $$('#login-list ul')[0];
                }
                if(target) {
                    target.insert(html);
                    $(response.form_id).captcha = new Captcha(this.urls.captchaReloadUrl, response.form_id);
                }
            }
            $(response.form_id).src = response.captcha_image;
            $('captcha_' + response.form_id).value = '';
        }
    },
    
    isError: function(response) {
        if(typeof(response.error) != 'undefined') {
            this.addNotice(response.error,'error-msg');
            return true;
        }
        return false;
    },
    
    addNotice: function(message, className) {
        this.notice.className = className;
        this.notice.update(message).show();
    },
    
    hideNotice: function() {
        this.notice.hide();
    },
            
    disable: function() {
        $(this.name+'-notice').hide();
        this.wait.show();
    },
    
    enable: function() {
        this.wait.hide();
    }
}