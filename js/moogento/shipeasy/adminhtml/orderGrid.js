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
 * Version     3.0.10
 * File        orderGrid.js
 * @category   Moogento
 * @package    shipEasy
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://www.moogento.com/License.html
 */

var ie6 = false;

// Help prevent flashes of unstyled content
if (Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE") + 5)) == 6) {
    ie6 = true;
} else {
    document.documentElement.className = document.documentElement.className + ' dk_fouc';
}
var start_ajax_custom_field = false;

function ajaxDomLoaded() {
    // var custom_shipping_method = $('szy_filter_shippingmethod');
//         if((typeof(custom_shipping_method) !='undefined') && (custom_shipping_method != null))
//             $(custom_shipping_method).hide();

    $$('.input_custom_value').each(function (element) {
        $(element).hide();
    });

    reMoveTrTitle();

    var custom_value_1 = $('custom_value_1');
    if ((typeof(custom_value_1) != 'undefined') && (custom_value_1 != null))
        $(custom_value_1).hide();

    var custom_value_2 = $('custom_value_2');
    if ((typeof(custom_value_2) != 'undefined') && (custom_value_2 != null))
        $(custom_value_2).hide();

    var custom_value_3 = $('custom_value_3');
    if ((typeof(custom_value_3) != 'undefined') && (custom_value_3 != null))
        $(custom_value_3).hide();

    var date_custom_value_1 = $('date_custom_value_1');
    if ((typeof(date_custom_value_1) != 'undefined') && (date_custom_value_1 != null))
        $(date_custom_value_1).hide();

    var date_custom_value_2 = $('date_custom_value_2');
    if ((typeof(date_custom_value_2) != 'undefined') && (date_custom_value_2 != null))
        $(date_custom_value_2).hide();

    var date_custom_value_3 = $('date_custom_value_3');
    if ((typeof(date_custom_value_3) != 'undefined') && (date_custom_value_3 != null))
        $(date_custom_value_3).hide();

    var szy_shipping_method_preset = $('sales_order_grid_filter_szy_shipping_method') || $('filter_szy_shipping_method');
    if ((typeof(szy_shipping_method_preset) != 'undefined') && (szy_shipping_method_preset != null)) {
        if (szy_shipping_method_preset.getValue() == 'szy_shipping_custom_value')
            $('szy_filter_shippingmethod').show();
        else
            $('szy_filter_shippingmethod').hide();
        szy_shipping_method_preset.writeAttribute('onchange', 'szychangeShippingmethod(this);')
    }

    (function ($) {
        var config = {
            '.chosen-select': {},
            '.chosen-select-deselect': {allow_single_deselect: true},
            '.chosen-select-no-single': {disable_search_threshold: 10},
            '.chosen-select-no-results': {no_results_text: 'Oops, nothing found!'},
            '.chosen-select-width': {width: "95%"}
        }
        for (var selector in config) {
            $(selector).chosen(config[selector]);
        }
        $('div.chosen-container.chosen-container-single').hide();

    })(jQuery);

    /* New codes for show and hide images  */
    $$(".hide_images").each(Element.hide);
    $$('.show_hide_images').each(function (element) {
        element.observe('click', function (event) {
            if (element.text.indexOf("Show more") != -1) {
                element.update("Hide &uarr;");
                var str_tr = 'div.' + element.id + '';
                var str_img = 'div.' + element.id + ' img';
                $$(str_tr).each(Element.show);
                $$(str_img).each(Element.show);
            } else {
                element.update("Show more &darr;");
                var str_tr = 'div.' + element.id + '';
                var str_img = 'div.' + element.id + ' img';
                $$(str_tr).each(Element.hide);
                $$(str_img).each(Element.hide);
            }

        });
    });
    /* End New codes for show and hide images */

    //Update order attribute
    var szy_attr_no = $('szy_attr_no');
    if ((typeof(szy_attr_no) != 'undefined') && (szy_attr_no != null))
        szy_attr_no.writeAttribute('onchange', 'szyChangeAttribute(this);');

    var szy_attr_custom_text = $('szy_attr_custom_text');
    if ((typeof(szy_attr_custom_text) != 'undefined') && (szy_attr_custom_text != null))
        szy_attr_custom_text.hide();
    for (i = 1; i <= 3; i++) {
        var szy_attr_preset = $('szy_attr_preset_' + i);
        if ((typeof(szy_attr_preset) != 'undefined') && (szy_attr_preset != null)) {
            szy_attr_preset.writeAttribute('onchange', 'szychangePreset(this);')
            szy_attr_preset.hide();
            $(szy_attr_preset.up()).hide();
        }
    }

    szyChangeAttribute('szy_attr_no');
    //Filter custom attributes
    for (var i = 1; i <= 3; i++) {
        var date_custom_value_ = $('date_custom_value_' + i);
        if ((typeof(date_custom_value_) != 'undefined') && (date_custom_value_ != null)) {
            date_custom_value_.hide();
        }
        var custom_value_ele = $('custom_value_' + i);
        if ((typeof(custom_value_ele) != 'undefined') && (custom_value_ele != null))
            custom_value_ele.hide();
        if (i > 1) {
            var field = $('sales_order_grid_filter_szy_custom_attribute' + i) || $('filter_szy_custom_attribute' + i);
            field.writeAttribute('onchange', 'szyfilterchangePreset(this);');
            szyfilterchangePreset(field);
        } else {
            var field = $('sales_order_grid_filter_szy_custom_attribute') || $('filter_szy_custom_attribute');
            field.writeAttribute('onchange', 'szyfilterchangePreset(this);');
            szyfilterchangePreset(field);
        }

    }


    var szy_shipping_method_preset = $('sales_order_grid_filter_szy_shipping_method') || $('filter_szy_shipping_method');
    if ((typeof(szy_shipping_method_preset) != 'undefined') && (szy_shipping_method_preset != null)) {
        if (szy_shipping_method_preset.getValue() == 'szy_shipping_custom_value')
            $('szy_filter_shippingmethod').show();
        else
            $('szy_filter_shippingmethod').hide();
        szy_shipping_method_preset.writeAttribute('onchange', 'szychangeShippingmethod(this);')
    }


    var date_custom_value_1 = $('date_custom_value_1');
    if ((typeof(date_custom_value_1) != 'undefined') && (date_custom_value_1 != null)) {
        Calendar.setup({
            inputField: 'date_custom_value_1',
            ifFormat: '%d-%m-%Y',
            //         button : '_dob_trig',
            align: 'Bl',
            singleClick: true
        });
    }

    var date_custom_value_2 = $('date_custom_value_2');
    if ((typeof(date_custom_value_2) != 'undefined') && (date_custom_value_2 != null)) {
        Calendar.setup({
            inputField: 'date_custom_value_2',
            ifFormat: '%d-%m-%Y',
            //         button : '_dob_trig',
            align: 'Bl',
            singleClick: true
        });
    }

    var date_custom_value_3 = $('date_custom_value_3');
    if ((typeof(date_custom_value_3) != 'undefined') && (date_custom_value_3 != null)) {
        Calendar.setup({
            inputField: 'date_custom_value_3',
            ifFormat: '%d-%m-%Y',
            //         button : '_dob_trig',
            align: 'Bl',
            singleClick: true
        });
    }

}

Ajax.Responders.register({
    onCreate: function () {
    },
    onComplete: function () {
        ajaxDomLoaded();
    },
    onFailure: function () {
        //hideProgress('fail');
    },
    onLoaded: function () {
        //hideProgress('loaded');
    }
});

function szyChangeAttribute(elm) {
    try {
        for (var i = 1; i <= 3; i++) {
            if (i == $(elm).getValue()) {
                $('szy_attr_preset_' + i).show();
                $($('szy_attr_preset_' + i).up()).show();
                szychangePreset('szy_attr_preset_' + i);

            } else {
                $('szy_attr_preset_' + i).hide();
                $($('szy_attr_preset_' + i).up()).hide();
            }
        }
    }
    catch (err) {
    }
}

function szychangePreset(elm) {
    if ($(elm).getValue() == 'custom') {
        $('szy_attr_custom_text').show();
    } else {
        $('szy_attr_custom_text').hide();
    }
}

function szychangeShippingmethod(elm) {
    if ($(elm).getValue() == 'szy_shipping_custom_value') {
        $('szy_filter_shippingmethod').show();
    } else {
        $('szy_filter_shippingmethod').hide();
    }
}


function szyfilterchangePreset(elm) {
    if ($(elm).readAttribute('id') == 'sales_order_grid_filter_szy_shipping_method' || $(elm).readAttribute('id') == 'filter_szy_shipping_method') {
        if ($(elm).getValue() == 'szy_shipping_custom_value')
            $('szy_filter_shippingmethod').show();
        else
            $('szy_filter_shippingmethod').hide();
    }
    else if ($(elm).readAttribute('id') == 'sales_order_grid_filter_szy_custom_attribute' || $(elm).readAttribute('id') == 'filter_szy_custom_attribute') {
        if ($(elm).getValue() == 'custom')
            $('custom_value_1').show();
        else
            $('custom_value_1').hide();

        if ($(elm).getValue() == '{{date}}')
            $('date_custom_value_1').show();
        else
            $('date_custom_value_1').hide();
    }
    else if ($(elm).readAttribute('id') == 'sales_order_grid_filter_szy_custom_attribute2' || $(elm).readAttribute('id') == 'filter_szy_custom_attribute2') {
        if ($(elm).getValue() == 'custom')
            $('custom_value_2').show();
        else
            $('custom_value_2').hide();

        if ($(elm).getValue() == '{{date}}')
            $('date_custom_value_2').show();
        else
            $('date_custom_value_2').hide();
    }
    else if ($(elm).readAttribute('id') == 'sales_order_grid_filter_szy_custom_attribute3' || $(elm).readAttribute('id') == 'filter_szy_custom_attribute3') {
        if ($(elm).getValue() == 'custom')
            $('custom_value_3').show();
        else
            $('custom_value_3').hide();

        if ($(elm).getValue() == '{{date}}')
            $('date_custom_value_3').show();
        else
            $('date_custom_value_3').hide();
    }

}


Event.observe(window, 'load', function () {
});

function reMoveTrTitle() {
    $$('#sales_order_grid_table tr').each(function (element) {
        //element.removeAttribute('title');
    });


    var MB_close = $('MB_close');
    if ((typeof(MB_close) != 'undefined') && (MB_close != null))
        MB_close.observe('click', function (event) {
            sales_order_gridJsObject.doFilter()
        });

}
function domLoaded() {

    /* New codes for show and hide images  */
    $$(".hide_images").each(Element.hide);
    $$('.show_hide_images').each(function (element) {
        element.observe('click', function (event) {
            if (element.text.indexOf("Show more") != -1) {
                element.update("Hide &uarr;");
                var str_tr = 'div.' + element.id + '';
                var str_img = 'div.' + element.id + ' img';
                $$(str_tr).each(Element.show);
                $$(str_img).each(Element.show);
            } else {
                element.update("Show more &darr;");
                var str_tr = 'div.' + element.id + '';
                var str_img = 'div.' + element.id + ' img';
                $$(str_tr).each(Element.hide);
                $$(str_img).each(Element.hide);
            }

        });
    });
    // End New codes for show and hide images 

    //Update order attribute
    var szy_attr_no = $('szy_attr_no');
    if ((typeof(szy_attr_no) != 'undefined') && (szy_attr_no != null))
        szy_attr_no.writeAttribute('onchange', 'szyChangeAttribute(this);');

    var szy_attr_custom_text = $('szy_attr_custom_text');
    if ((typeof(szy_attr_custom_text) != 'undefined') && (szy_attr_custom_text != null))
        szy_attr_custom_text.hide();
    for (i = 1; i <= 3; i++) {
        var szy_attr_preset = $('szy_attr_preset_' + i);
        if ((typeof(szy_attr_preset) != 'undefined') && (szy_attr_preset != null)) {
            szy_attr_preset.writeAttribute('onchange', 'szychangePreset(this);')
            szy_attr_preset.hide();
            $(szy_attr_preset.up()).hide();
        }
    }

    szyChangeAttribute('szy_attr_no');


    //shipping method.
    var szy_shipping_method_preset = $('sales_order_grid_filter_szy_shipping_method') || $('filter_szy_shipping_method');
    if ((typeof(szy_shipping_method_preset) != 'undefined') && (szy_shipping_method_preset != null)) {
        if (szy_shipping_method_preset.getValue() == 'szy_shipping_custom_value')
            $('szy_filter_shippingmethod').show();
        else
            $('szy_filter_shippingmethod').hide();

        szy_shipping_method_preset.writeAttribute('onchange', 'szychangeShippingmethod(this);')
    }


    //Filter custom attributes
    for (var i = 1; i <= 3; i++) {
        var date_custom_value_ = $('date_custom_value_' + i);
        if ((typeof(date_custom_value_) != 'undefined') && (date_custom_value_ != null)) {
            date_custom_value_.hide();
        }
        var custom_value_ele = $('custom_value_' + i);
        if ((typeof(custom_value_ele) != 'undefined') && (custom_value_ele != null))
            custom_value_ele.hide();
        if (i > 1) {
            var custom_filter_ele = $('sales_order_grid_filter_szy_custom_attribute' + i) || $('filter_szy_custom_attribute' + i);
            if ((typeof(custom_filter_ele) != 'undefined') && (custom_filter_ele != null)) {
                custom_filter_ele.writeAttribute('onchange', 'szyfilterchangePreset(this);');
                szyfilterchangePreset(custom_filter_ele);
            }
        }
        else {
            var custom_filter_ele = $('sales_order_grid_filter_szy_custom_attribute') || $('filter_szy_custom_attribute');
            if ((typeof(custom_filter_ele) != 'undefined') && (custom_filter_ele != null)) {
                custom_filter_ele.writeAttribute('onchange', 'szyfilterchangePreset(this);');
                szyfilterchangePreset(custom_filter_ele);
            }
        }


    }

    var date_custom_value_1 = $('date_custom_value_1');
    if ((typeof(date_custom_value_1) != 'undefined') && (date_custom_value_1 != null)) {
        Calendar.setup({
            inputField: 'date_custom_value_1',
            ifFormat: '%d-%m-%Y',
            //         button : '_dob_trig',
            align: 'Bl',
            singleClick: true
        });
    }

    var date_custom_value_2 = $('date_custom_value_2');
    if ((typeof(date_custom_value_2) != 'undefined') && (date_custom_value_2 != null)) {
        Calendar.setup({
            inputField: 'date_custom_value_2',
            ifFormat: '%d-%m-%Y',
            //         button : '_dob_trig',
            align: 'Bl',
            singleClick: true
        });
    }

    var date_custom_value_3 = $('date_custom_value_3');
    if ((typeof(date_custom_value_3) != 'undefined') && (date_custom_value_3 != null)) {
        Calendar.setup({
            inputField: 'date_custom_value_3',
            ifFormat: '%d-%m-%Y',
            //         button : '_dob_trig',
            align: 'Bl',
            singleClick: true
        });
    }

    $$('.input_custom_value').each(function (element) {
        $(element).hide();
    });
    reMoveTrTitle();
}
document.observe("dom:loaded", function () {
    domLoaded();
});

varienGridMassaction.prototype.appendVisible = function () {
    checkedValues = this.getCheckedValues();
    checkedValues = checkedValues.split(',');
    valuesToAppend = this.getCheckboxesValues();

    newValues = valuesToAppend.concat(checkedValues);
    newValues = newValues.uniq();
    this.setCheckedValues(newValues.join(','));
    this.checkCheckboxes();
    this.updateCount();
    return false;
}

varienGridMassaction.addMethods({
    apply: function () {
        if (varienStringArray.count(this.checkedString) == 0) {
            alert(this.errorText);
            return;
        }

        var item = this.getSelectedItem();
        if (!item) {
            this.validator.validate();
            return;
        }
        this.currentItem = item;
        var fieldName = (item.field ? item.field : this.formFieldName);
        var fieldsHtml = '';

        if (this.currentItem.confirm && !window.confirm(this.currentItem.confirm)) {
            return;
        }

        this.formHiddens.update('');

        var trackingNo = new Hash();
        var trackingCarries = new Hash();
        var trackingNoFields = $$('#' + this.grid.containerId + ' input.tracking_number');
        if (trackingNoFields.length) {
            var tableId = this.grid.containerId + this.grid.tableSufix;
            var rowCounter = 0;
            $$('#' + tableId + ' tr').each(function (tableRow) {
                rowCounter++;
                //
                // Heading and Filters
                //
                if (rowCounter <= 2) {
                    return;
                }

                var selected = false;
                var objectId = 0;

                Element.select($(tableRow), 'input, select').each(function (inputElm) {
                    if ($(inputElm).isMassactionCheckbox) {
                        selected = $(inputElm).checked;
                        objectId = $(inputElm).value;
                    } else {
                        if (selected) {
                            if ($(inputElm).readAttribute('name') == 'szy_tracking_number') {
                                trackingNo.set(objectId, ($(inputElm).value ? $(inputElm).value : ''));
                            } else if ($(inputElm).readAttribute('name') == 'tracking_carrier') {
                                trackingCarries.set(objectId, ($(inputElm).value ? $(inputElm).value : ''));
                            }
                        }
                    }
                });
            });

            trackingNo.each(function (value) {
                var fieldName = 'szy_tracking_number[' + value.key + ']';
                new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({
                    name: fieldName,
                    value: value.value
                }));
            }.bind(this));

            trackingCarries.each(function (value) {
                var fieldName = 'tracking_carrier[' + value.key + ']';
                new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({
                    name: fieldName,
                    value: value.value
                }));
            }.bind(this));

        }

        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({
            name: fieldName,
            value: this.checkedString
        }));
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({
            name: 'massaction_prepare_key',
            value: fieldName
        }));

        if (!this.validator.validate()) {
            return;
        }

        var $this = this;
        if(($this.form.order_ids.value.split(",")).length > 200){
            (function ($) {
                $('#sales_order_grid').append('<div id="dialog-selected-more-then-200-orders" title="Selected more then 200 orders" style="displat:none;"><p>Are you sure you want to process this many orders?</p></div>');
                var dialog_box = $('#dialog-selected-more-then-200-orders');
                dialog_box.dialog({
                    resizable: false,
                    height: 200,
                    modal: true,
                    buttons: {
                        "OK": function () {
                            dialog_box.dialog("close");
                            if (item.hasOwnProperty('func')) {
                                window[item.func].apply($this, [$this, item]);
                            } else {            
                                if ($this.useAjax && item.url) {
                                    new Ajax.Request(item.url, {
                                        'method': 'post',
                                        'parameters': $this.form.serialize(true),
                                        'onComplete': $this.onMassactionComplete.bind(this)
                                    });
                                } else if (item.url) {
                                    $this.form.action = item.url;
                                    $this.form.submit();
                                }
                            }

                        },
                        Cancel: function () {
                            dialog_box.dialog("close");
                        }
                    }
                });
            })(jQuery);  
        } else {
            if (item.hasOwnProperty('func')) {
                window[item.func].apply($this, [$this, item]);
            } else {            
                if ($this.useAjax && item.url) {
                    new Ajax.Request(item.url, {
                        'method': 'post',
                        'parameters': $this.form.serialize(true),
                        'onComplete': $this.onMassactionComplete.bind(this)
                    });
                } else if (item.url) {
                    $this.form.action = item.url;
                    $this.form.submit();
                }
            }
        }
    }
});

function updateTitle(button, fieldId) {
    new Ajax.Request(szy_baseUrl + 'updateTitle', {
        method: 'post',
        parameters: {id: fieldId, title: $(button).previous('input').getValue()}
    });
}

function assignTracking($this, item) {
    if (jQuery($this.form).find(custom_text).val().trim() != '') {
        $this.form.action = item.url;
        var this_form = $this.form;
        $this.form.request({
            onSuccess: function (response) {
                var response_txt = response.responseText;
                var data = response_txt.evalJSON();
                window.qaz = response_txt;
                var message_text = "Please note that there are no shipments for the orders";
                data.orders_numbers.each(function (i) {
                    if (typeof i != 'function')
                        message_text += ' ' + i + ',';
                });
                message_text = message_text.slice(0, -1);
                message_text += ". These shipments will be automatically created.";
                (function ($) {
                    if (data.orders_numbers.length > 0) {
                        $('#sales_order_grid').append('<div id="dialog-confirm" title="Finding orders without shipment"><p>' + message_text + '</p></div>');
                        var dialog_box = $('#dialog-confirm');
                        dialog_box.dialog({
                            resizable: false,
                            height: 200,
                            modal: true,
                            buttons: {
                                "OK": function () {
                                    dialog_box.dialog("close");
                                    $(this_form).find('#step').val('2');
                                    $(this_form).find('#orders_for_new_shipment').val(JSON.stringify(data.orders_id));
                                    $(this_form).submit();

                                },
                                Cancel: function () {
                                    dialog_box.dialog("close");
                                    $(this_form).find('#step').val('3');
                                    $(this_form).submit();
                                }
                            }
                        });
                    } else {
                        $(this_form).find('#step').val('3');
                        $(this_form).submit();
                    }
                })(jQuery);
            }
        });
    } else {
        alert("Field for tracking number can't be empty!");
    }
}

jQuery(document).ready(function ($) {

    window.bill_ship_form_changing = false;
    window.bill_ship_form_present = false;

    var config = {
        '.szy_custom_col .chosen-select': {allow_single_deselect: true},
        '.szy_custom_col .chosen-select-deselect': {allow_single_deselect: true},
        '.szy_custom_col .chosen-select-no-single': {disable_search_threshold: 10},
        '.szy_custom_col .chosen-select-no-results': {no_results_text: 'Oops, nothing found!'},
        '.szy_custom_col .chosen-select-width': {width: "95%"}
    }
    for (var selector in config) {
        $(selector).chosen(config[selector]);
    }

    $('.szy_custom_col div.chosen-container.chosen-container-single').hide();

    $(document).on('click', function (e) {
        if (bill_ship_form_present) {
            if (bill_ship_form_changing) {
                var bill_ship_last_click = $(e.target);
                if ($(e.target).closest("#ship_bill_form").length <= 0) {
                    if ($(e.target).closest(".ui-dialog.ui-widget.ui-widget-content.ui-corner-all.ui-front.ui-dialog-buttons.ui-draggable").length <= 0) {
                        e.preventDefault();
                        e.stopPropagation();

                        var form = $(this);
                        if (bill_ship_form_changing) {
                            dialog_ship_bill_box.dialog({
                                hide: {
                                    effect: "explode",
                                    duration: 1000
                                },
                                resizable: false,
                                height: 200,
                                modal: true,
                                closeOnEscape: false,
                                buttons: {
                                    "Back to form": {
                                        text: "Back to form",
                                        class: 'back_dialog_btn',
                                        click: function () {
                                            bill_ship_form_changing = true;
                                            dialog_ship_bill_box.dialog("close");
                                            form.find('td.value>input:first').focus();
                                        }
                                    },
                                    "Continue": {
                                        text: "Continue",
                                        class: 'continue_dialog_btn',
                                        click: function () {
                                            bill_ship_form_changing = false;
                                            dialog_ship_bill_box.dialog("close");
                                            bill_ship_last_click.click();
                                        }
                                    }
                                },
                            });
                        }
                        return false;
                    }
                }
            }
        }
    });

    $('#anchor-content').on('avtChangeClass', '.szy_custom_col div.chosen-container.chosen-container-single', function (e) {
        $(this).hide();
        $(this).parents('td').find('.custom_color').show();
            
        changeRestriction($(this).closest('td'));
    });

    $('#anchor-content').on('click', '.custom_color,.custom_flag', function (e) {
        $(this).parent().click()
    });

    $('#anchor-content').on('click', 'td.szy_custom_col', function (e) {
        e.stopPropagation();   

        var $elem = $(this);
        if (e.target != this){ 
            var $etarget = $(e.target);
            if(!($etarget.hasClass('custom_column_img') || $etarget.hasClass('szy_custom_col_data'))){
                $elem = $etarget.closest('custom_column_img');
                if (!$etarget.hasClass('restriction')) return;
            }
        }
        
        if (!bill_ship_form_changing) {
            changeRestriction($elem);
            $elem.find('div.chosen-container.chosen-container-single').show();
            $elem.find('.custom_color').hide();
            $elem.find('select.chosen-select').trigger('chosen:open');
            }
    });

    $('#anchor-content').on('change', '.szy_custom_col select.chosen-select', function (e) {
        var $selectElem = $(this);
        var $order_id = $selectElem.attr('name').replace('custom', '');
        var $order_id_part1 = $order_id.substring(-100, 1);
        var $order_id_part2 = $order_id.substr(2);
        var $parent_of_elem = $selectElem.closest('td.szy_custom_col');

        if($selectElem.val() == "New Value") {
            $parent_of_elem.find('.custom_color').hide();   
            $selectElem.next().hide();
            $parent_of_elem.find(".input_custom_value").show().focus();            
        } else {
            //Hide all to change data 
            showSpinner($parent_of_elem);
            $parent_of_elem.find('.custom_color').hide();
            $selectElem.next().hide();
            //Ajax for getting new data
            $.ajax({
                type: "POST",
                url: szy_baseUrl,
                data: {orderId: $order_id_part2, attribute: $order_id_part1, value: $selectElem.val(), form_key: FORM_KEY}
            })
            .done(function (data) {
                $selectElem.siblings('.custom_color').first().replaceWith(JSON.parse(data).html);
                hideSpinner($parent_of_elem);
                $parent_of_elem.find('.custom_color').show();
                $parent_of_elem.find('.input_custom_value').hide();
            });
        }
    });
    
    $('.szy_custom_col .input_custom_value').keyup(function (e) {
        if (e.keyCode == 13) {
            start_ajax_custom_field = true;
            var $selectElem = $(this);
            var $order_id = $selectElem.attr('name').replace('custom', '');
            var $order_id_part1 = $order_id.substring(-100, 1);
            var $order_id_part2 = $order_id.substr(2);

            //Hide all to change data 
            var $parent_of_elem = $selectElem.closest('td.szy_custom_col');
            $parent_of_elem.find('.custom_color').hide();
            $parent_of_elem.find('.input_custom_value').hide();

            showSpinner($parent_of_elem);
            $selectElem.next().hide();
            //Ajax for getting new data
            $.ajax({
                type: "POST",
                url: szy_baseUrl,
                data: {orderId: $order_id_part2, attribute: $order_id_part1, value: $selectElem.val(), form_key: FORM_KEY}
            })
            .done(function (data) {
                $parent_of_elem.find('.custom_color').first().replaceWith(JSON.parse(data).html);
                hideSpinner($parent_of_elem);
                $parent_of_elem.find('.custom_color').show();
//                $selectElem.hide();
        });
        }
    });
    
    $('#anchor-content').on('focusout', '.szy_custom_col .input_custom_value', function (e) {
        var $parent_of_elem = $(this).closest('td.szy_custom_col');
        if (start_ajax_custom_field){
            $parent_of_elem.find('.custom_color').hide();
        } else {
            $parent_of_elem.find('.custom_color').show();
        }
        start_ajax_custom_field = false;
        $parent_of_elem.find('.input_custom_value').hide();
    });
    
    $('#anchor-content').on('submit', 'form.send_address_by_ajax', function (e) {
        e.preventDefault();
        var selected_cell = $(e.target).closest('td.bill_ship');
        var formShipBill = new varienForm('ship_bill_form', true);
        
        changeRestriction($(this).closest('td.bill_ship'));
        
        showSpinner(selected_cell);
        if (formShipBill.validator.validate()) {
            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function (result_data) {
                    hideSpinner(selected_cell);
                    if (result_data == "Address didn't saving") {
                        alert(result_data);
                    } else {
                        selected_cell.find('div.bill_ship_for_form').html(result_data);
                        selected_cell.closest('tr').find('.szy_company').html(selected_cell.find('#company').val());
                    }
                    $('td.bill_ship div.bill_ship_for_form').show();
                    $('div.bill_ship_form').remove();
                    bill_ship_form_changing = false;
                    $("td.bill_ship .edit-icon").removeClass('hidden_el');
                    addGreenAlertForAddresForm(selected_cell, true);
                },
            });
        }
        return false;
    });

    $('#anchor-content').on('click', 'td.bill_ship .edit-icon', function (e) {
        e.stopPropagation();
        if (!bill_ship_form_changing) {
            $("td.bill_ship .edit-icon").removeClass('hidden_el');
            $(this).addClass('hidden_el');
            var cell = $(this).parents('td');
            var div_block = cell.find('div.bill_ship_for_form:first');
            div_block.hide();
            showSpinner(cell);

            changeRestriction(cell);

            $.ajax({
                type: "GET",
                url: div_block.data('url'),
                success: function (result_data) {
                    $('td.bill_ship div.bill_ship_for_form').show();
                    $('div.bill_ship_form').remove();
                    $('#dialog-bill-ship-unsaved').remove();
                    hideSpinner(cell);
                    $(div_block).hide();
                    $(div_block).after(result_data + '<div id="dialog-bill-ship-unsaved" title="WARNING" style="display:none"><p> You have unsaved changes!</p></div>');
                    window.dialog_ship_bill_box = $('#dialog-bill-ship-unsaved');

                    var arr = $('#ship_bill_form').find('tr');
                    for (var i = 0; i < arr.length; i++) {
                        var field_label = $(arr[i]).find('td.label').find('label').text();
                        $(arr[i]).find('td.value').find('input:first').attr('placeholder', field_label);
                    }
                    $('#ship_bill_form').find('td.label').remove();
                    bill_ship_form_changing = false;
                    bill_ship_form_present = true;
                    $('#ship_bill_form input').keyup(function () {
                        fixedChangingAddressForm()
                    });
                },
            });
        }
    });

    $('#anchor-content').on('click', '.btn-bs-form-cancel', function (e) {
        var selected_cell = $(this).closest('td.bill_ship');
        $('td.bill_ship div.bill_ship_for_form').show();
        $('div.bill_ship_form').remove();
        $('#dialog-bill-ship-unsaved').remove();
        bill_ship_form_present = false;
        $("td.bill_ship .edit-icon").removeClass('hidden_el');
        addGreenAlertForAddresForm(selected_cell);

        changeRestriction(selected_cell);
        return false;
    });

    function addGreenAlertForAddresForm(selected_cell, withTick) {
        if (withTick) {
            selected_cell.css('height', '40px');
        }
        selected_cell.append('<div class="form_alert' + (withTick ? ' with-tick' : '') + '" style="width:' + selected_cell.outerWidth() + 'px; height:' + selected_cell.outerHeight() + 'px;" ></div>');
        setTimeout(function () {
            selected_cell.find('.form_alert').fadeOut('slow', function () {
                selected_cell.css('height', 'inherit');
            });
        }, 200);
    }

    $('#anchor-content').on('mouseenter', 'div.szy_grid_sku', function () {
        $(this).children('.linkout_sku').fadeIn();
    }).on('mouseleave', 'div.szy_grid_sku', function () {
        $(this).children('.linkout_sku').fadeOut();
    });
    
    $('#anchor-content').on('change', "#ship_bill_form input, #ship_bill_form select", function (e) {
        bill_ship_form_changing = true;
        fixedChangingAddressForm();
    }).on('keyup', '.tracking_number', function (e) {
        var el = $(this);
        if (e.which == 13 && el.val()) {
            if (el.siblings('button').length > 0) {
                el.siblings('button').click();
            } else {
                var td = el.parents('td');
                showSpinner(td);
                el.blur();
                $.ajax({
                    type: 'POST',
                    url: el.data('url'),
                    dataType: 'json',
                    data: {
                        id: el.data('id'),
                        tracking_number: el.val(),
                        carrier: td.find('.tracking_carrier').val(),
                        form_key: FORM_KEY
                    },
                    success: function (data) {
                        if (data.error) {
                            alert(data.error);
                        } else if (data.html) {
                            td.html(data.html);
                        }
                    },
                    complete: function () {
                        hideSpinner(td);
                    }
                });
            }
        }
    }).on('mouseenter', 'span.tracking_link', function () {
        $(this).children('.fa').fadeIn();
    }).on('mouseleave', 'span.tracking_link', function () {
        $(this).children('.fa').fadeOut();
    }).on('click', 'span.tracking_link .fa-pencil', function (e) {
        e.stopPropagation();
        var wrapper = $(this).parents('span.tracking_link'),
            form = $('<span class="tracking_edit" />').html($('#tracking_form').html()).insertAfter(wrapper);
        wrapper.hide();
        form.find('.tracking_carrier').val(wrapper.data('carrier'))
        form.find('.tracking_number').val(wrapper.data('number'));
        changeRestriction($(this).closest('td'));
        
    }).on('click', 'span.tracking_link .fa-minus', function (e) {
        e.stopPropagation();
        var url_for_del = $(this).data( "url" );
        var tracking_td = $(this).parents('td');
        var tracking_link = $(this).parents('.tracking_link');
        var wrapper = $(this).parents('.tracking_wrapper');

        $.ajax({
            type: 'POST',
            url: url_for_del,
            dataType: 'json',
            data: {
                id: tracking_link.data('id'),
                type: 'tracking_number',
                form_key: FORM_KEY
            },
            success: function (data) {
                tracking_link.remove();
                if(wrapper.html() == ""){
                    wrapper.remove();
                    tracking_td.find('.tracking_number').show();
                }
            },
        });
    }).on('click', 'span.tracking_link .fa-plus', function (e) {
        e.stopPropagation();
        var wrapper = $(this).parents('.tracking_wrapper'),
            td = wrapper.parents('td');
        if (td.find('.tracking_add').length == 0) {
            var form = $('<span class="tracking_add" />').html($('#tracking_form').html()).insertAfter(wrapper);
            form.find('button span').text('Add');
        } else {
            td.find('.tracking_add .tracking_number').focus();
        }

        changeRestriction($(this).closest('td'));

    }).on('click', 'span.tracking_edit button', function (e) {
        e.stopPropagation();
        var form = $(this).parents('.tracking_edit'),
            wrapper = form.prev('.tracking_link'),
            mainWrapper = form.parents('.tracking_wrapper');
        if (!form.find('.tracking_number').val()) return;

        if (wrapper.data('number') == form.find('.tracking_number').val() && wrapper.data('carrier') == form.find('.tracking_carrier').val()) {
            form.remove();
            wrapper.show();
            return;
        }

        var td = form.parents('td');
        showSpinner(td);
        $.ajax({
            type: 'POST',
            url: mainWrapper.data('url-edit'),
            dataType: 'json',
            data: {
                id: mainWrapper.data('id'),
                old_tracking_number: wrapper.data('number'),
                tracking_number: form.find('.tracking_number').val(),
                carrier: form.find('.tracking_carrier').val(),
                notify: form.find('.notify_customer').is(':checked') ? 1 : 0,
                form_key: FORM_KEY
            },
            success: function (data) {
                if (data.error) {
                    alert(data.error);
                } else if (data.html) {
                    td.html(data.html);
                }
            },
            complete: function () {
                hideSpinner(td);
            }
        });
    }).on('click', 'span.tracking_add button', function (e) {
        e.stopPropagation();
        var form = $(this).parents('.tracking_add'),
            wrapper = form.parents('td').find('.tracking_wrapper');
        if (!form.find('.tracking_number').val()) return;

        var td = form.parents('td');
        showSpinner(td);
        $.ajax({
            type: 'POST',
            url: wrapper.data('url-add'),
            dataType: 'json',
            data: {
                id: wrapper.data('id'),
                tracking_number: form.find('.tracking_number').val(),
                carrier: form.find('.tracking_carrier').val(),
                notify: form.find('.notify_customer').is(':checked') ? 1 : 0,
                form_key: FORM_KEY
            },
            success: function (data) {
                if (data.error) {
                    alert(data.error);
                } else if (data.html) {
                    td.html(data.html);
                }
            },
            complete: function () {
                hideSpinner(td);
            }
        });
    }).on('mouseenter', 'span.track_link_not_fixed', function () {
        $(this).children('.fa-pencil').fadeIn();
        $(this).children('.fa-minus').fadeIn();
    }).on('mouseleave', 'span.track_link_not_fixed', function () {
        $(this).children('.fa-pencil').fadeOut();
        $(this).children('.fa-minus').fadeOut();
    }).on('click', 'span.track_link_not_fixed .fa-pencil', function (e) {
        e.stopPropagation();
        $(this).parents('span.track_link_not_fixed').hide();
        $(this).parents('td').find("input.tracking_number").show();
        $(this).parents('td').find('select').parent().show();
    }).on('click', 'span.track_link_not_fixed .fa-minus', function (e) {
        e.stopPropagation();
        var url_for_del = $(this).data( "url" );
        var track_td = $(this).parents('td');
        var track_td_input = track_td.find('input');
        var track_link_not_fixed = $(this).parents('.track_link_not_fixed');

        var newstr = '<i class="fa fa-clock-o"></i>';
        newstr += track_link_not_fixed.html().substring(track_link_not_fixed.html().indexOf('<a class="fa fa-pencil"'));
        track_link_not_fixed.html(newstr);
        track_link_not_fixed.hide();
        track_td_input.val('');
        track_td_input.show();
        $.ajax({
            type: 'POST',
            url: url_for_del,
            dataType: 'json',
            data: {
                id: track_td_input.data('id'),
                type: 'track_link_not_fixed',
                form_key: FORM_KEY
            },
            success: function (data) {

            },
        });       
    });

    function fixedChangingAddressForm() {
        var submit_btn = $("#ship_bill_form").find('input#submit');
        submit_btn.removeClass("unchanging");
        submit_btn.addClass("changing");
    }

    $('#anchor-content').on('click', '.szy_status_col .edit-icon', function (e) {
        var parent_td = $(this).closest('.szy_status_col');
        parent_td.find('.status_showing_data').hide();
        parent_td.find('.status_form').show();
        parent_td.find('.edit-icon').addClass('hidden_el');
    });
    
    $('#anchor-content').on('click', 'button.szy_status_button_close', function (e) {
        var parent_td = $(this).closest('.szy_status_col');
        parent_td.find('.status_showing_data').show();
        parent_td.find('.status_form').hide();
        parent_td.find('.edit-icon').removeClass('hidden_el');
    });
    
    $('#anchor-content').on('click', 'button.szy_status_button_edit', function (e) {
        var $this = $(this);
        var selected_cell = $this.closest('td');
        var selected_div = selected_cell.find('.status_div');
        var notify_cust = selected_cell.find('input[type="checkbox"]').attr("checked") != 'checked' ? 0 : 1;
        var sel_status = selected_cell.find('.szy_status_change');
        var order_id = sel_status.data('orderid');
        var status = sel_status.val();
        var prev_status = sel_status.data('status');

        if(prev_status != status){
            selected_div.hide();
            showSpinner(selected_cell);
            var err_msg = "";
            $.ajax({
                type: 'POST',
                url: sel_status.data('url'),
                dataType: 'json',
                data: {
                    order_id: order_id,
                    status: status,
                    notify: notify_cust,
                    form_key: FORM_KEY
                },
                success: function (data) {
                    var flag = 0;
                    data.each(function( index ) {
                        $.each(index, function( i, val ) {
                            if(i == "error") flag++;
                            err_msg = i + ": " + val;
                        });
                    });
                    if(flag == 0){
                        var parent_tr = $this.parents( 'tr.'+prev_status );
                        parent_tr.removeClass(prev_status);
                        parent_tr.addClass(status);
                        sel_status.data('status', status);
                        selected_div.find('.status_showing_data').html(sel_status.find('option:selected').text());
                    } else {
                        $this.val(prev_status);                        
                        if($("#dialog_szy_status_error").length<=0){
                            $('#anchor-content').after('<div id="dialog_szy_status_error" title="WARNING" style="display:none"><p> ' + err_msg + '</p></div>');
                        }                        
                        window.dialog_szy_status_error = $('#dialog_szy_status_error');
                        var dialog_box = $('#dialog_szy_status_error');
                        dialog_box.dialog({
                            resizable: false,
                            height: 200,
                            modal: true,
                            buttons: {
                                "OK": function () {
                                    dialog_box.dialog("close");
                                }
                            }
                        });
                    }
                    hideSpinner(selected_cell);

                    selected_div.show();
                    selected_div.find('.status_showing_data').show();
                    selected_div.find('.status_form').hide();
                    selected_cell.find('.edit-icon').removeClass('hidden_el');
                },
            });  
        } else {
            selected_div.find('.status_showing_data').show();
            selected_div.find('.status_form').hide();  
            selected_cell.find('.edit-icon').removeClass('hidden_el');
        }
    });
    
    $('#anchor-content').on('mouseover', '.szy_image .one_of_image', function (e) {
        var $this = $(this);
        $this.addClass('hover_test');
        setTimeout( function() { if($this.hasClass('hover_test')) $this.addClass('nearly_hover'); }, 600);
    });

    $('#anchor-content').on('mouseout', '.szy_image .one_of_image', function (e) {
        $(this).removeClass('hover_test');
        $(this).removeClass('nearly_hover');
    });

    $('#anchor-content').on('click', '.customer_email_list .edit-icon', function (e) {
        var parent_td = $(this).closest('.customer_email_list');
        parent_td.removeClass('non-edit');
        parent_td.addClass('for-edit');
        
        changeRestriction(parent_td);
    });

    $('#anchor-content').on('click', '.customer_email_list .inputs_for_admin_email .cancel_for_admin_email', function (e) {
        var parent_td = $(this).closest('.customer_email_list');
        var select_cust = parent_td.find('#select_customer');
        select_cust.val(select_cust.data('value'));
        parent_td.removeClass('for-edit');
        parent_td.addClass('non-edit');
        
        changeRestriction(parent_td);

    });
    
    $('#anchor-content').on('click', '.customer_email_list .inputs_for_admin_email .submit_for_admin_email', function (e) {
        var parent_td = $(this).closest('.customer_email_list');
        var input_for_email = parent_td.find('.text_for_admin_email');
        showSpinner(parent_td);

        changeRestriction(parent_td);
        
        var url_data = input_for_email.data('changeurl');
        var order_id = input_for_email.data('orderid');
        var email = input_for_email.val();
        $.ajax({
            type: 'POST',
            url: url_data,
            dataType: 'html',
            data: {
                orderid: order_id,
                email: email,
                form_key: FORM_KEY
            },
            success: function (data) {
                hideSpinner(parent_td)
                parent_td.html(data);
           }
        });

        parent_td.removeClass('for-edit');
        parent_td.addClass('non-edit');
    });
});

function changeRestriction(el) {
    (function ($) {
        var restriction = el.find('.max-width-none, .restriction');
        if(restriction.length >0){
            restriction.toggleClass('max-width-none');
            restriction.toggleClass('restriction');
        }
    })(jQuery);
}
