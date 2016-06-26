var massSetCRMethod = false;
var $dialogCourierrulesBox;

jQuery( document ).ready(function( $ ) {
    
    checkCRCustomValueFilter();
    
    $('#anchor-content').on('change', '#sales_order_grid_filter_courierrules_description', function(e){
        if($("#sales_order_grid_filter_courierrules_description").val() == "custom_value"){
            $("#szy_filter_courierrules_description").show();
        } else {
            $("#szy_filter_courierrules_description").hide();
            $("#szy_filter_courierrules_description").val("");
        }
    });
    
    var courierrules_selects = $(".courierrules_td .chosen-select");
    $.each( courierrules_selects, function( index, value ){
        $(courierrules_selects[index]).data('before-value', $(value).val());
//        console.log($(value));
    });
    
    
    $(".courierrules_td .chosen-select").chosen({
        disable_search:true,
        allow_single_deselect:true,
        width: "95%",    
    });
    
    $('.courierrules_td div.chosen-container.chosen-container-single').hide();
    
    $('#anchor-content').on('click', '.courierrules_td .edit-icon', function(e){
        e.stopPropagation();
        $(".courierrules_td .edit-icon").removeClass("invisible_span");
        $(this).addClass("invisible_span");
        var $this = $(this).closest('td');
        var cell_select = $this.find('select.chosen-select:first');
        $.when(
            jQuery.each( $('.courierrules_td'), function( i, val ) {
                courierrulesBackToNormalView($(val));
            })
        ).then(function() {
            $this.find('div.chosen-container.chosen-container-single').show();
            $this.find('p').hide();
            cell_select.trigger('chosen:open');
            if(cell_select.val() == 'custom'){
                $this.find('input.courierrules_custom').show();
            }
        });
    });

    $('#anchor-content').on('avtChangeClass', '.courierrules_td div.chosen-container.chosen-container-single', function(e){
        if(!($(e.target).closest('td').find('.courierrules_custom:focus').length>0)){
            $$(".courierrules_td .edit-icon").invoke('removeClassName',"invisible_span");
            courierrulesBackToNormalView($(this).closest('td'));
        }
    });
    
    $('#anchor-content').on('change', 'input.courierrules_custom', function(e){
        changeAjaxCourierrules($(this).closest('td'));
    });

    $('#anchor-content').on('change', '.courierrules_td select.chosen-select', function(e){
        e.stopPropagation();
        var parent_cell = $(this).closest('td');
        var cell_input = parent_cell.find('input.courierrules_custom');
        var cell_select = parent_cell.find('select.chosen-select');
        if(cell_select.val() == 'custom'){
            cell_input.show();
        } else {
            changeAjaxCourierrules(parent_cell);
        }
    });
    
    // ----- Add dialog box for field courierrules_method ----- start
    var $dialog_courierrules = $('<div/>',{
        id: "dialog_courierrules",
        text: "Warning! This order has a tracking number assigned - would you like to:",
    });
    
    $dialogCourierrulesBox = $dialog_courierrules.dialog({
        hide: {
          effect: "explode",
          duration: 1000
        },
        autoOpen: false,
        resizable: false,
        height: 140,
        width: 500,
        modal: true,
        closeOnEscape: false,
        open: function(){
            $('.ui-widget-overlay').bind('click',function(){
                var calling_cell = jQuery.data($dialogCourierrulesBox, 'parent_cell');
                courierrulesBackToNormalView(calling_cell);
                var calling_cell_select = calling_cell.find("select.chosen-select");
                calling_cell_select.val(calling_cell_select.data("before-value"));
                calling_cell_select.trigger("chosen:updated");
                $$(".courierrules_td .edit-icon").invoke('removeClassName',"invisible_span");
                $dialog_courierrules.dialog('close');
            })
        },
        buttons: {
            "wipe it": {
                text: "wipe it",
                click: function() {
                    if($('#remember_dialog_courierrules').prop('checked')){
                        $.cookie('remember_dialog_box_answer', '1', { expires: 365, path: '/'});
                    }
                    if(massSetCRMethod){
                        setCRMethods(jQuery.data($dialogCourierrulesBox, 'this'), jQuery.data($dialogCourierrulesBox, 'item'), true);
                    } else {
                        var post_data = jQuery.data($dialogCourierrulesBox, 'post_data');
                        post_data.change_track = true;
                        setCRMethod(jQuery.data($dialogCourierrulesBox, 'parent_cell'), post_data);
                    }
                    massSetCRMethod = false;
                    $dialog_courierrules.dialog( "close" );
                }
            },
            "keep it": {
                text: "keep it",
                click: function() {
                    if($('#remember_dialog_courierrules').prop('checked')){
                        $.cookie('remember_dialog_box_answer', '0', { expires: 365, path: '/'});
                    }
                    if(massSetCRMethod){
                        setCRMethods(jQuery.data($dialogCourierrulesBox, 'this'), jQuery.data($dialogCourierrulesBox, 'item'), false);
                    } else {
                        var post_data = jQuery.data($dialogCourierrulesBox, 'post_data');
                        post_data.change_track = false;
                        setCRMethod(jQuery.data($dialogCourierrulesBox, 'parent_cell'), post_data);
                    }
                    massSetCRMethod = false;
                    $dialog_courierrules.dialog( "close" );
                }
            }
        },
    });
    // ----- Add dialog box for field courierrules_method ----- end
    
    // ----- Add remembering checkbox to dialog box ----- start
    var $dialog_courierrules_input = $('<div/>',{
        id: "dialog_courierrules_input",
    });
    //$dialog_courierrules_input.html('<input id="remember_dialog_courierrules" type="checkbox" checked="checked">Remember this decision for this combination of shipping methods'+"'");
    $dialog_courierrules_input.html('<input id="remember_dialog_courierrules" type="checkbox">Remember this decision for this combination of shipping methods'+"'");
    $dialog_courierrules.closest('.ui-dialog.ui-widget.ui-widget-content.ui-corner-all.ui-front.ui-dialog-buttons.ui-draggable').find('.ui-dialog-buttonpane.ui-widget-content.ui-helper-clearfix').append($dialog_courierrules_input);
    // ----- Add remembering checkbox to dialog box ----- end
    
    $('#anchor-content').on('change', '#cr_method', function(e){
        if($(this).val() == 'custom'){
            $('#cr_method_custom').show();
        } else {
            $('#cr_method_custom').hide();
        }
    });
    
});

varienGridMassaction.prototype.appendVisible = function() {
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
    apply: function() {
        if(varienStringArray.count(this.checkedString) == 0) {
                alert(this.errorText);
                return;
            }

        var item = this.getSelectedItem();
        if(!item) {
            this.validator.validate();
            return;
        }
        this.currentItem = item;
        var fieldName = (item.field ? item.field : this.formFieldName);
        var fieldsHtml = '';

        if(this.currentItem.confirm && !window.confirm(this.currentItem.confirm)) {
            return;
        }

        this.formHiddens.update('');

        var shippingCost = new Hash();
        var trackingNo   = new Hash();
        var select_all   = new Hash();
        
        var shippingCostCurrency = new Hash();
        var trackingNoFields = $$('#'+this.grid.containerId+' input.tracking_number');
        if (trackingNoFields.length) {
            var tableId = this.grid.containerId + this.grid.tableSufix;
            var rowCounter = 0;
            $$('#'+tableId+' tr').each(function(tableRow){
                rowCounter++;
                /**
                 * Heading and Filters
                 */
                if (rowCounter <= 2) {
                    return;
                }

                var selected = false;
                var objectId = 0;

                Element.select($(tableRow), 'input').each(function(inputElm){
                    if ($(inputElm).isMassactionCheckbox) {
                        selected = $(inputElm).checked;
                        objectId = $(inputElm).value;
                    } else {
                        if (selected) {
                            if ($(inputElm).readAttribute('name') == 'szy_tracking_number') {
                                trackingNo.set(objectId, ($(inputElm).value) ? $(inputElm).value : '');
                            }
                        }
                    }
                });
            });

            trackingNo.each(function(value){
                var fieldName = 'szy_tracking_number[' + value.key + ']';
                new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: fieldName, value: value.value}));
            }.bind(this));
        }        
        var shippingCostFields = $$('#'+this.grid.containerId+' input.shipping_cost');
        if (shippingCostFields.length) {
            var tableId = this.grid.containerId + this.grid.tableSufix;
            var rowCounter = 0;
            $$('#'+tableId+' tr').each(function(tableRow){
                rowCounter++;
                /**
                 * Heading and Filters
                 */
                if (rowCounter <= 2) {
                    return;
                }

                var selected = false;
                var objectId = 0;

                Element.select($(tableRow), 'input').each(function(inputElm){
                    if ($(inputElm).isMassactionCheckbox) {
                        selected = $(inputElm).checked;
                        objectId = $(inputElm).value;
                    } else {
                        if (selected) {
                            if ($(inputElm).readAttribute('name') == 'szy_base_shipping_cost') {
                                shippingCost.set(objectId, ($(inputElm).value) ? $(inputElm).value : 0);
                                //shippingCost[objectId] = ($(inputElm).value) ? $(inputElm).value : 0;
                            }
                        }
                    }
                });
            });

            shippingCost.each(function(value){
                var fieldName = 'szy_base_shipping_cost[' + value.key + ']';
                new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: fieldName, value: value.value}));
            }.bind(this));
        }
        var shippingCostCurrencyFields = $$('#'+this.grid.containerId+' select.shipping_cost_currency');
        if (shippingCostCurrencyFields.length) {
            var tableId = this.grid.containerId + this.grid.tableSufix;
            var rowCounter = 0;
            $$('#'+tableId+' tr').each(function(tableRow){
                rowCounter++;
                /**
                 * Heading and Filters
                 */
                if (rowCounter <= 2) {
                    return;
                }

                var selected = false;
                var objectId = 0;

                Element.select($(tableRow), 'input,select').each(function(inputElm){
                    if ($(inputElm).isMassactionCheckbox) {
                        selected = $(inputElm).checked;
                        objectId = $(inputElm).value;
                    } else {
                        if (selected) {
                            if ($(inputElm).readAttribute('name') == 'szy_base_shipping_cost_currency') {
                                shippingCostCurrency.set(objectId, $(inputElm).getValue());
                            }
                        }
                    }
                });
            });

            shippingCostCurrency.each(function(value){
                var fieldName = 'szy_base_shipping_cost_currency[' + value.key + ']';
                new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: fieldName, value: value.value}));
            }.bind(this));
        }
        
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: fieldName, value: this.checkedString}));
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: 'massaction_prepare_key', value: fieldName}));

        if(!this.validator.validate()) {
            return;
        }

        if(item.hasOwnProperty('func')){
            window[item.func].apply(this, [this, item]);
        } else {
            if(this.useAjax && item.url) {
                new Ajax.Request(item.url, {
                    'method': 'post',
                    'parameters': this.form.serialize(true),
                    'onComplete': this.onMassactionComplete.bind(this)
                });
            } else if(item.url) {
                this.form.action = item.url;
                this.form.submit();
            }
        }
    }
});

function changeAjaxCourierrules(parent_cell) /*all params is jQuery object*/
{
    var cell_input = parent_cell.find('input.courierrules_custom');
    var cell_select = parent_cell.find('select.chosen-select');
    showSpinner(parent_cell);
    parent_cell.find('div.chosen-container.chosen-container-single').hide();
    cell_input.hide();

    var post_data = {};
    post_data.rule_id = cell_select.val();
    post_data.input_custom = cell_input.val();
    post_data.order_id = cell_select.attr("name").replace('courierrules_description_', '');
    post_data.form_key = window.FORM_KEY;

    if(parent_cell.find('p:first').data('track') != ''){
        if(jQuery.cookie('remember_dialog_box_answer') === undefined){
            jQuery.data($dialogCourierrulesBox, 'parent_cell', parent_cell);
            jQuery.data($dialogCourierrulesBox, 'post_data', post_data);
            $dialogCourierrulesBox.dialog('open');
        } else {
            post_data.change_track = jQuery.cookie('remember_dialog_box_answer');
            setCRMethod(parent_cell, post_data);
        }
    } else {
        post_data.change_track = true;
        setCRMethod(parent_cell, post_data);
    }
}

function courierrulesBackToNormalView(parent_cell) /*all params is jQuery object*/
{
    parent_cell.find('div.chosen-container.chosen-container-single').hide();
    parent_cell.find('input.courierrules_custom').hide();
    parent_cell.find('p').show();        
    hideSpinner(parent_cell);
}

function setCRMethod(parent_cell, post_data) /*all params is jQuery object*/
{
    jQuery.ajax({
        type: "POST",
        url: parent_cell.find('input.courierrules_custom').data('url'),
        data: post_data,
        success: function(result_data) {
            parent_cell.find('p:first').replaceWith(result_data);
            $$(".courierrules_td .edit-icon").invoke('removeClassName',"invisible_span");
            courierrulesBackToNormalView(parent_cell);
            var calling_cell_select = parent_cell.find('select.chosen-select:first');
            calling_cell_select.data("before-value", calling_cell_select.val());
        },
    });      
}

function setCRMethods($this, item, change_track)
{
    $("cr_method_change_track").setValue(change_track);
    $this.form.action = item.url;
    $this.form.submit();
}

function setCRMethodsFromGridMassaction($this, item)
{
    if(jQuery.cookie('remember_dialog_box_answer') === undefined){
        massSetCRMethod = true;
        jQuery.data($dialogCourierrulesBox, 'this', $this);
        jQuery.data($dialogCourierrulesBox, 'item', item);
        $dialogCourierrulesBox.dialog('open');
    } else {
        setCRMethods($this, item, jQuery.cookie('remember_dialog_box_answer'));
    }
}

function checkCRCustomValueFilter()
{
    if(jQuery("#sales_order_grid_filter_courierrules_description").val() != "custom_value"){
        jQuery("#szy_filter_courierrules_description").hide();
    }
}
