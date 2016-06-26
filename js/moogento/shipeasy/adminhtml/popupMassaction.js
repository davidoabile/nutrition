/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://www.moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* Version	  3.0.10
* File        popupMassaction.js
* @category   Moogento
* @package    shipEasy
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://www.moogento.com/License.html
*/ 


var moogentoPopupMassaction = Class.create();
var dialogBoxForAssignTrackingInBarcode;

moogentoPopupMassaction.prototype = {

    initialize: function (containerId, formId) {
        this.useAjax        = false;
        this.containerId    = containerId;
        this.formId         = formId;
        this.mainField = $('scan_order_number');
        this.autoCheckbox = $('scan_order_auto');
        this.form               = $(this.formId);
        this.validator          = new Validation(this.form);
        this.formHiddens        = [];
        this.formAdditionals    = [];
        this.selects            = [];
        this.attributeSelector = null;
        
        var $this = this;
        idGridMassactionFieldsInBarcode.forEach(function(element, index, array) {
            $this.initMassactionElements(element);
        });

        this.form.stopObserving('submit', this.onSubmit);
        this.form.observe('submit', this.onSubmit.bindAsEventListener(this));
    },

    initMassactionElements: function(element) {
        this.formHiddens[element] = $(element + '-form-hiddens');
        this.formAdditionals[element] = $(element + '-form-additional');
        this.selects[element] = $(element + '-select');
        this.selects[element].observe('change', this.onSelectChange.bindAsEventListener(this));
    },

    dataEntered: function(evt) {
        // if (this.autoCheckbox.checked) {
//             this.formValidate();
//         }
    },
    
     sumitEntered: function(evt) {
        // if (this.autoCheckbox.checked) {
//             this.formValidate();
//         }
    },

    onSubmit: function(event) {
        if (($('sales_order_grid_massaction_1-select').getValue() == "") && ($('sales_order_grid_massaction_2-select').getValue() == "")){
            alert('Please apply an action!');
        } else {
            if (!$('scan-button').hasClassName('disabled')) {           
                this.formValidate();
            }
        }
        Event.stop(event);
        return false;
    },

    onSelectChange: function(event) {
        var element = Event.element(event);
        var item_id = element.id.replace("-select","");
        var item = this.getSelectedItem(item_id);
        if(item) {
            this.formAdditionals[item_id].update($(item_id + '-item-' + item.id + '-block').innerHTML);
            var obj = this;
            this.formAdditionals[item_id].select('select, input').each(function(elm) {
                var id = elm.readAttribute('id');
                elm.writeAttribute('name', 'scan[' + item_id + '][' + id + ']');
                elm.writeAttribute('id', item_id + '-' + id);
                if (id.match(/^szy_attr_/)) {
                    if ('szy_attr_no' == id && null == obj.attributeSelector) {
                        obj.attributeSelector = new moogentoPopupMassactionAttribute(elm, obj);
                    } else if (id.match(/^szy_attr_preset_/)) {
                        obj.attributeSelector.setValuesSelector(elm, 'custom');
                    } else if (id.match(/^szy_attr_custom_text/)) {
                        obj.attributeSelector.setTextInput(elm);
                        obj.attributeSelector.updateSettings();
                    }
                }
            });
        } else {
            this.formAdditionals[item_id].update('');
        }
        if(item.id == "assign_tracking"){
            $(item_id+"-custom_text").addClassName("required-entry");
        } else {
            $(item_id+"-custom_text").removeClassName("required-entry");
        }

        this.validator.reset();
    },

    getSelectedItem: function(id) {
        var value = this.getItem(this.selects[id].value);
        if(value) {            
            return value;
        } else {
            return false;
        }
    },

    setItems: function(items) {
        this.items = items;        
        this.items.forEach(function(element, index, array) {
            array[index].id = index;
        });        
    },

    getItem: function(itemId) {
        var value = this.items[itemId];
        if(value) {
            this.items[itemId].id = itemId;
            return value;
        }
        return false;
    },

    resetFormData: function() {
        $(this.mainField).removeClassName('validation-passed');
        $(this.mainField).setValue('');
    },

    formValidate: function(evt) {
        if(this.validator && this.validator.validate()) {
            //this.form.stopObserving('submit');
            $('scan-button').addClassName('disabled');
            var assign_tracking_start = false;
            var shipping_creation = false;
            for (var i = 0; i < idGridMassactionFieldsInBarcode.length; i++) {
                if($(idGridMassactionFieldsInBarcode[i]+"-select").getValue() == "assign_tracking"){
                    assign_tracking_start = true;
                }
                if($(idGridMassactionFieldsInBarcode[i]+"-select").getValue() == "ship_order"
                    || $(idGridMassactionFieldsInBarcode[i]+"-select").getValue() == "ship_invoice_order"){
                    shipping_creation = true;
                }
            }
            if (shipping_creation) {
                assign_tracking_start = false;
            }
            if(assign_tracking_start){
                var proto_this = this;
                var url = getCheckOrderShipmentUrl;
                var order_id =jQuery("#scan_order_number").val()
                jQuery.ajax({
                    type: "POST",
                    url: url,
                    data: {order_id: order_id, form_key: FORM_KEY},
                    success: function (result_data) {
                        var data = JSON.parse(result_data);
                        switch (data.result) {
                            case 0:
                                alert(data.message);
                                jQuery("#scan-button").removeClass("disabled");
                                break
                            case 1:
                                dialogBoxForAssignTrackingInBarcode.elem = proto_this;
                                dialogBoxForAssignTrackingInBarcode.dialog("open");
                                break
                            case 2:
                                formRequest(this);
                                break
                        }
                    },
                });            
            } else {
                formRequest(this);
            }
        }
    }
};

function formRequest($this) {
    $this.form.request({
        method: 'post',
        onComplete: function(transport) {
            $('scan-button').removeClassName('disabled');
            $this.resetFormData();
        }.bind($this),

        onSuccess: function(transport) {
            result = {};
            if (transport && transport.responseText){
                try{
                    result = eval('(' + transport.responseText + ')');
                }
                catch (e) {
                    result = {};
                }
            }

            $('scanform_messages').update();
            if (result.msg) {
                messagesHtml = '';
                if (result.msg.success) {
                    successMessages = result.msg.success;
                    messagesHtml += '<li class="success-msg"><ul>';
                    for(i=0; i< successMessages.length; i++) {
                        messagesHtml += '<li>' + successMessages[i] +'</li>'
                    }
                    messagesHtml += '</ul></li>';
                }

                if (result.msg.error) {
                    errorMessages = result.msg.error;
                    messagesHtml += '<li class="error-msg"><ul>';
                    for(i=0; i< errorMessages.length; i++) {
                        messagesHtml += '<li>' + successMessages[i] +'</li>'
                    }
                    messagesHtml += '</ul></li>';
                }
                $('scanform_messages').update(messagesHtml);
            }

            if (result.history_content) {
                $('history_container').update(result.history_content);
            }
        }.bind($this)
    });
}

var moogentoPopupMassactionAttribute = Class.create();

moogentoPopupMassactionAttribute.prototype = {

    initialize: function (containerId, parent) {
        this.mainSelector = $(containerId);
        this.mainSelector.style.width = '';
        this.parent = parent;
        this.sValueWithText = [];
        this.textInput = [];
        this.valuesSelector = [];
    },

    setValuesSelector: function(oValueSelector, sValueText) {
        oValueSelector.style.width = '';
        this.valuesSelector.push(oValueSelector);
        this.sValueWithText.push(sValueText);
    },

    setTextInput: function(oTextInput) {
        this.textInput = oTextInput;
    },

    updateSettings: function() {
        this.mainSelector.observe('change', this.onMainSelector.bindAsEventListener(this));
        var obj = this;
        this.valuesSelector.each(function(el) {
            el.observe('change', obj.onValuesSelector.bindAsEventListener(obj));
            el.hide();
        });
        this.textInput.hide();
        this.onMainSelector();
    },

    onMainSelector: function(evt) {
        var iMainSelector = this.mainSelector.getValue() - 1;
        this.valuesSelector.each(function(el, idx) {
            if (idx == iMainSelector) {
                el.show();
                el.up().show();
            } else {
                el.hide();
                el.up().hide();
            }
        });
        this.onValuesSelector();
    },

    onValuesSelector: function(evt) {
        var iMainSelector = this.mainSelector.getValue() - 1;
        var currentValue = this.valuesSelector[iMainSelector].getValue();
        if (this.sValueWithText[iMainSelector] == currentValue) {
            this.textInput.show();
            this.textInput.style.marginLeft = this.valuesSelector[iMainSelector].previous().getWidth() + 'px';
        } else {
            this.textInput.hide();
        }
    }
};

jQuery( document ).ready(function( $ ) {
    dialogBoxForAssignTrackingInBarcode = $('<div/>',{
        id: "dialog-for-assign-tracking-in-barcode",
        title: "Alert",
        text: "This order maybe without shipment! Create it?",
    });
    
    dialogBoxForAssignTrackingInBarcode.dialog({
        resizable: false,
        autoOpen: false,
        closeOnEscape: false,
        height: 200,
        modal: true,
        buttons: {
            "OK": function() {
                assignTrackingFromBarcodeOK();
                dialogBoxForAssignTrackingInBarcode.dialog( "close" );
                formRequest(dialogBoxForAssignTrackingInBarcode.elem);
            },
            Cancel: function() {
                assignTrackingFromBarcodeCancel();
                dialogBoxForAssignTrackingInBarcode.dialog( "close" );
                formRequest(dialogBoxForAssignTrackingInBarcode.elem);
            }
        }
    });
    
    var old = $('#scan-form fieldset').html();
    setInterval(function () {
        if ($('#scan-form fieldset').html() != old) {
            if($('#scan-form fieldset .entry-edit>.validation-advice').length>0){
                $('#scan-form fieldset .entry-edit>.validation-advice').first().prev().append($('#scan-form fieldset .entry-edit>.validation-advice').first());
            }
            old = $('#scan-form fieldset').html();
        }
    }, 500);
});

function assignTrackingFromBarcodeOK(){
    for (var i = 0; i < idGridMassactionFieldsInBarcode.length; i++) {
        if($(idGridMassactionFieldsInBarcode[i]+"-select").getValue() == "assign_tracking"){
            $(idGridMassactionFieldsInBarcode[i]+"-step").setValue('1');
        }
    }
}

function assignTrackingFromBarcodeCancel(){
    for (var i = 0; i < idGridMassactionFieldsInBarcode.length; i++) {
        if($(idGridMassactionFieldsInBarcode[i]+"-select").getValue() == "assign_tracking"){
            $(idGridMassactionFieldsInBarcode[i]+"-step").setValue('0');
        }
    }
}

function checkCheckboxComment(){
    var historyComment = jQuery("#history_comment");
    var checkboxComment = jQuery('#checkbox_comment');
    if(checkboxComment.is(':checked')){
        historyComment.removeClass("disabled_history_comment");
        historyComment.addClass("enabled_history_comment");
        historyComment.prop("disabled", false);
    } else {
        historyComment.removeClass("enabled_history_comment");
        historyComment.addClass("disabled_history_comment");
        historyComment.prop("disabled", true);
    }
}
