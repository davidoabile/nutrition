
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
var AitValidation = Class.create(Validation, {

   validate : function() {
        var result = false;
        var useTitles = this.options.useTitles;
        var callback = this.options.onElementValidate;
        try {
            if(this.options.stopOnFirst) {
                result = Form.getElements(this.form).all(function(elm) {
                    if (elm.hasClassName('local-validation') && !this.isElementInForm(elm, this.form)) {
                        return true;
                    }
                    return AitValidation.validate(elm,{useTitle : useTitles, onElementValidate : callback});
                }, this);
            } else {
                result = Form.getElements(this.form).collect(function(elm) {
                    if (elm.hasClassName('local-validation') && !this.isElementInForm(elm, this.form)) {
                        return true;
                    }
                    return AitValidation.validate(elm,{useTitle : useTitles, onElementValidate : callback});
                }, this).all();
            }
        } catch (e) {
        }
//aitoc start 
        var selector = $$('.customer-dob')[0];
        if (selector) {       
            var dobAdvice = Element.select(selector, '.validation-advice')[0];
            if(dobAdvice) {
                dobAdvice.hide();
            }
        }
//aitoc finish        
        if(!result && this.options.focusOnError) {
            try{
                Form.getElements(this.form).findAll(function(elm){return $(elm).hasClassName('validation-failed')}).first().focus()
            }
            catch(e){
            }
        }
        this.options.onFormValidate(result, this.form);
        return result;
    }    
});

Object.extend(AitValidation, {
    validate : function(elm, options){
        options = Object.extend({
            useTitle : false,
            onElementValidate : function(result, elm) {}
        }, options || {});
        elm = $(elm);
        var cn = $w(elm.className);
        return result = cn.all(function(value) {
            var test = AitValidation.test(value,elm,options.useTitle);
            options.onElementValidate(test, elm);
            return test;
        });
    },
   
    test : function(name, elm, useTitle) {     
        var v = Validation.get(name);
        var prop = '__advice'+name.camelize();
        try {
        if(Validation.isVisible(elm) && !v.test($F(elm), elm)) 
        {
            Validation.updateCallback(elm, 'failed');
            elm[prop] = 1;
            return false;
        } 
        else {
            Validation.updateCallback(elm, 'passed');
            elm[prop] = '';
            return true;
        }
        } catch(e) {
            throw(e)
        }
    }
}); 

Validation.isOnChange = true;

Validation.add('validate-one-required-by-name', 'Please select one of the options.', function (v,elm) {
        var inputs = $$('input[name="' + elm.name.replace(/([\\"])/g, '\\$1') + '"]');

        var error = 1;
        for(var i=0;i<inputs.length;i++) {
        
            if(!Validation.isVisible(inputs[i]))
            {
                continue;
            }
        
            if((inputs[i].type == 'checkbox' || inputs[i].type == 'radio') && inputs[i].checked == true) {
                error = 0;
            }
            
/*aitoc_comment_start            
            if(Validation.isOnChange && (inputs[i].type == 'checkbox' || inputs[i].type == 'radio')) {
                Validation.reset(inputs[i]);
            }
aitoc_comment_end*/            
            
        }

        if( error == 0 ) {
//aitoc start 
            for(var i=0;i<inputs.length;i++) 
            {  
                if(Validation.isOnChange && (inputs[i].type == 'checkbox' || inputs[i].type == 'radio')) 
                {
                    Validation.reset(inputs[i]);
                }
            }
//aitoc finish
            return true;
        } else {
            review.resetLoadWaiting();
            aitCheckout.runSaveAfterUpdate = false;
            return false;
        }
    });

    
if(typeof Varien.DateElement!='undefined') {
    
    Varien.DateElement.prototype.validate = function() {
            var error = false, day = parseInt(this.day.value, 10) || 0, month = parseInt(this.month.value, 10) || 0, year = parseInt(this.year.value, 10) || 0;
            if (!day && !month && !year) {
                if (this.required) {
                    error = 'This date is a required value.';
                    if (this.day.hasClassName('validation-passed') 
                        || this.month.hasClassName('validation-passed')
                        || this.year.hasClassName('validation-passed'))
                    {
                        this.day.removeClassName('validation-passed'); 
                        this.month.removeClassName('validation-passed'); 
                        this.year.removeClassName('validation-passed'); 
                        this.day.addClassName('validation-failed');
                        this.month.addClassName('validation-failed');
                        this.year.addClassName('validation-failed');
                    }
                } else {
                    this.full.value = '';
                }
            } else if (!day || !month || !year) {
                error = 'Please enter a valid full date.';
                if (!day) {
                    this.day.removeClassName('validation-passed');
                    this.day.addClassName('validation-failed');    
                }
                if (!month) {
                    this.month.removeClassName('validation-passed');
                    this.month.addClassName('validation-failed');    
                }
                if (!year) {
                    this.year.removeClassName('validation-passed');
                    this.year.addClassName('validation-failed');    
                }
            } else {
                var date = new Date, curyear = date.getFullYear(), countDaysInMonth = 0, errorType = null;
                date.setYear(year); date.setMonth(month-1); date.setDate(32);
                countDaysInMonth = 32 - date.getDate();
                if(!countDaysInMonth || countDaysInMonth>31) countDaysInMonth = 31;

                if (day<1 || day>countDaysInMonth) {
                    errorType = 'day';
                    error = 'Please enter a valid day (1-%d).';
                    this.day.removeClassName('validation-passed');
                    this.day.addClassName('validation-failed');
                } else {
                    this.day.removeClassName('validation-failed');
                    this.day.addClassName('validation-passed');
                }
                if (month<1 || month>12) {
                    errorType = 'month';
                    error = 'Please enter a valid month (1-12).';
                    this.month.removeClassName('validation-passed');
                    this.month.addClassName('validation-failed');
                } else {
                    this.month.removeClassName('validation-failed');
                    this.month.addClassName('validation-passed');
                }
                if (year<1900 || year>curyear) {
                    errorType = 'year';
                    error = 'Please enter a valid year (1900-%d).';
                    this.year.removeClassName('validation-passed');
                    this.year.addClassName('validation-failed');
                } else {
                    this.year.removeClassName('validation-failed');
                    this.year.addClassName('validation-passed');
                } 
                if (error === false)
                {
                    if(day % 10 == day) this.day.value = '0'+day;
                    if(month % 10 == month) this.month.value = '0'+month;
                    this.full.value = this.format.replace(/%[mb]/i, this.month.value).replace(/%[de]/i, this.day.value).replace(/%y/i, this.year.value);
                    var testFull = this.month.value + '/' + this.day.value + '/'+ this.year.value;
                    var test = new Date(testFull);
                    if (isNaN(test)) {
                        error = 'Please enter a valid date.';
                        this.day.removeClassName('validation-passed'); 
                        this.month.removeClassName('validation-passed'); 
                        this.year.removeClassName('validation-passed'); 
                        this.day.addClassName('validation-failed');
                        this.month.addClassName('validation-failed');
                        this.year.addClassName('validation-failed');
                    }
                }
            }
            if (error !== false) {
                try {
                    error = Translator.translate(error);
                }
                catch (e) {}
                this.advice.innerHTML = error.replace('%d', errorType == 'day' ? countDaysInMonth : curyear);
                this.advice.show();
                return false;
            }
            this.advice.hide();
            return true;
        }
        
    Object.extend(Validation, {   
        test : function(name, elm, useTitle) {
            var v = Validation.get(name);
            var prop = '__advice'+name.camelize();
            try {
            if(Validation.isVisible(elm) && !v.test($F(elm), elm)) {

    //aitoc start  
                var selector = $$('.customer-dob')[0];
                var dayContainer = false;
                var monthContainer = false;
                var yearContainer = false
                if (selector) {
                    dayContainer = Element.select(selector, '.dob-day input')[0];
                    monthContainer = Element.select(selector, '.dob-month input')[0];
                    yearContainer = Element.select(selector, '.dob-year input')[0];    
                }
                if( [dayContainer.id, monthContainer.id, yearContainer.id].indexOf(elm.id) < 0 ) 
                {    
    //aitoc finish 

                //if(!elm[prop]) {
                    var advice = Validation.getAdvice(name, elm);
                    if (advice == null) {
                        advice = this.createAdvice(name, elm, useTitle);
                    }
                    this.showAdvice(elm, advice, name);
                    this.updateCallback(elm, 'failed');
                //}
                    elm[prop] = 1;
                    if (!elm.advaiceContainer) {
                        elm.removeClassName('validation-passed');
                        elm.addClassName('validation-failed');
                    }

                    if (Validation.defaultOptions.addClassNameToContainer && Validation.defaultOptions.containerClassName != '') {
                        var container = elm.up(Validation.defaultOptions.containerClassName);
                        if (container && this.allowContainerClassName(elm)) {
                            container.removeClassName('validation-passed');
                            container.addClassName('validation-error');
                        }
                    }
                    
    //aitoc start        
                }      
    //aitoc finish 

                return false;
            } else {
                var advice = Validation.getAdvice(name, elm);
                this.hideAdvice(elm, advice);
                this.updateCallback(elm, 'passed');
                elm[prop] = '';
                elm.removeClassName('validation-failed');
                elm.addClassName('validation-passed');
                if (Validation.defaultOptions.addClassNameToContainer && Validation.defaultOptions.containerClassName != '') {
                    var container = elm.up(Validation.defaultOptions.containerClassName);
                    if (container && !container.down('.validation-failed') && this.allowContainerClassName(elm)) {
                        if (!Validation.get('IsEmpty').test(elm.value) || !this.isVisible(elm)) {
                            container.addClassName('validation-passed');
                        } else {
                            container.removeClassName('validation-passed');
                        }
                        container.removeClassName('validation-error');
                    }
                }
                return true;
            }
            } catch(e) {
                throw(e)
            }
        }
    });
}