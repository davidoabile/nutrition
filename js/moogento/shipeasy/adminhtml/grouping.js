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
* File        grouping.js
* @category   Moogento
* @package    shipEasy
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://www.moogento.com/License.html
*/ 

var moogentoStatusesGrouping = Class.create();

moogentoStatusesGrouping.prototype = {
    initialize: function (containerId, statusesHtml, baseName) {

        this.template = false;
        this.templateSyntax = /(^|.|\r|\n)({{(\w+)}})/;

        this.rowsCount = 0;

        this.templateText = '<tr id="grouping_{{id}}">' +
            '<td><input name="' + baseName + '[row_{{id}}][group_name]" class="required-entry input-text" type="text" value="{{value}}" /></td>' +
            '<td><select name="' + baseName + '[row_{{id}}][group_status][]" class="select multiselect" multiple="multiple" size="5">'+statusesHtml+'</select></td>' +
            '<td><button class="scalable delete delete-select-row icon-btn" type="button"><span><span><span>Delete Group</span></span></span></button></td>' +
        '</tr>';

        this.container = $(containerId);
        this.tableBody = $(this.container.select('tbody')[0]);

        this.addBtn = $(this.container.select('#add-new-group')[0]);

        this.addBtn.observe('click', this.addGroup.bind(this));
    },

    initValues: function(values) {
        for(key in values) {
            this.rowsCount++;
            this.template = new Template(this.templateText, this.templateSyntax);
            Element.insert(this.tableBody, {'bottom':this.template.evaluate({id: this.rowsCount, value: key})});
            statusesSelect = $($('grouping_' + this.rowsCount).select('select')[0]);
            statusesSelect.setValue(values[key]);
        }
        this.bindRemoveBtns();
    },

    addGroup: function() {
        this.rowsCount++;
        this.template = new Template(this.templateText, this.templateSyntax);
        Element.insert(this.tableBody, {'bottom':this.template.evaluate({id: this.rowsCount, value: ''})});
        this.bindRemoveBtns();
    },

    bindRemoveBtns: function() {
        this.tableBody.select('.delete-select-row').each(function(elm){
            elm = $(elm);
            if (!elm.binded) {
                elm.binded = true;
                Event.observe(elm, 'click', this.removeGroup.bind(this));
            }
        }.bind(this));
    },

    removeGroup: function(event) {
        var element = $(Event.findElement(event, 'tr'));
        if (element) {
            element = $(element);
            element.remove();
        }
    }
};

////////////////JS for Packing Sheet/////////////////
var moogenthoSalesOrderColumns = Class.create();
moogenthoSalesOrderColumns.prototype = {
    initialize: function (containerId, statusesHtml,typesHtml,yesnoOptions,countryGroup, baseName) {
        this.template = false;
        this.templateSyntax = /(^|.|\r|\n)({{(\w+)}})/;
        this.rowsCount = 0;
            
        this.templateText = '<tr id="pack_shipping_grouping_{{id}}" class="shipping_background">' +
        // '<td><input  name="' + baseName + '[grid_columns_row_{{id}}][name]" class="grid_columns_row_{{id}}_name  input-text" type="text" value="{{name}}" /></td>' +
        '<td><select style="width: 60px !important;"  id="grid_columns_row_{{id}}_status" style="width: 125px !important;" name="' + baseName + '[grid_columns_row_{{id}}][status]" class="select_type grid_columns_row_{{id}}_status">'+statusesHtml+'</select></td>' +
        '<td><input style="width: 40px !important;" name="' + baseName + '[grid_columns_row_{{id}}][position]" class="grid_columns_row_{{id}}_position  input-text" type="text" value="{{position}}" /></td>' +
        '<td><select  id="grid_columns_row_{{id}}" style="width: 90px !important;" name="' + baseName + '[grid_columns_row_{{id}}][type][]" class="select_type grid_columns_row_{{id}}_type">'+typesHtml+'</select></td>' +

        '<td><select id="change_attribute_grid_columns_row_{{id}}" style="width: 80px !important;" name="' + baseName + '[grid_columns_row_{{id}}][attribute][]" class="select_attribute grid_columns_row_{{id}}_attribute">'+countryGroup+'</select>' +
        '<input style="width: 60px !important;" name="' + baseName + '[grid_columns_row_{{id}}][order_attribute]" class=" input-text grid_columns_row_{{id}}_order_attribute" type="text-area" value="{{order_attribute}}" >{{order_attribute}}</input>' +
        '<input style="width: 60px !important;" name="' + baseName + '[grid_columns_row_{{id}}][product_attribute]" class=" input-text grid_columns_row_{{id}}_product_attribute" type="text-area" value="{{product_attribute}}" >{{product_attribute}}</input></td>' +
        '<td><input name="' + baseName + '[grid_columns_row_{{id}}][renderer]" class="input-text grid_columns_row_{{id}}_renderer" type="text" value="{{renderer}}" /></td>' +

        '<td><select  id="grid_columns_row_{{id}}" style="width: 90px !important;" name="' + baseName + '[grid_columns_row_{{id}}][sortable][]" class="select_sortable grid_columns_row_{{id}}_sortable">'+yesnoOptions+'</select></td>' +

        '<td><input style="width: 30px !important;" name="' + baseName + '[grid_columns_row_{{id}}][priority]" class="input-text grid_columns_row_{{id}}_priority" type="text" value="{{priority}}"/></td>' +
        '<td>{{image}}<input name="' + baseName + '[grid_columns_row_{{id}}][file]" type="file" class="input-text" value="{{image}}" /></td>' +
        '<td><button class="scalable delete delete-select-pack-row icon-btn" type="button"><span><span><span>Delete</span></span></span></button></td>' +
        '</tr>';
            
        this.container = $(containerId);
        this.tableBody = $(this.container.select('tbody')[0]);
        this.addBtn = $(this.container.select('#pack-add-new-shipping-method-group')[0]);
        this.addBtn.observe('click', this.addBackgroundRow.bind(this));
    },

    addBackgroundRow: function() {
        this.rowsCount++;
        this.template = new Template(this.templateText, this.templateSyntax);
        Element.insert(this.tableBody, {'bottom':this.template.evaluate({id: this.rowsCount, value: ''})});
        this.bindRemoveBtns();
        
        var tr = $('pack_shipping_grouping_'+this.rowsCount);
        var type = $(tr.select('select')[0]);
        if(type.getValue() == 'shipping_method')
        {
            var hidden_country_select = $(tr.select('select')[1]);
            if(hidden_country_select != undefined)
                hidden_country_select.hide();
            else
            {
                label = $(tr.select('label')[0]);
                if (label != undefined) {
                    label.hide();
                }
            }
        }
        else if(type.getValue() == 'country_group')
        {
            var hidden_partern_area = $(tr.select('textarea')[0]);
            if(hidden_partern_area != undefined)
                hidden_partern_area.hide();

        }

        var select = $('grid_columns_row_'+this.rowsCount);
        select.observe("change",function(event){
            if(select.getValue() == 'shipping_method' )
            {
               $$('.'+select.getAttribute('id')+'_attribute')[0].hide();
               $$('.'+select.getAttribute('id')+'_order_attribute')[0].show();
            }else
            {
                $$('.'+select.getAttribute('id')+'_order_attribute')[0].hide();
                $$('.'+select.getAttribute('id')+'_attribute')[0].show();
            }
        });
        
    },

    bindRemoveBtns: function() {
        this.tableBody.select('.delete-select-pack-row').each(function(elm){
            elm = $(elm);
            if (!elm.binded) {
                elm.binded = true;
                Event.observe(elm, 'click', this.removeBackgroundRow.bind(this));
            }
        }.bind(this));
    },

    removeBackgroundRow: function(event) {
        var element = $(Event.findElement(event, 'tr'));
        if (element) {
            element = $(element);
            element.remove();
        }
    },

    //Insert value to table.
    initValues: function(values) {
//        alert(JSON.stringify(values, null, 4));
        for(key in values) {
            this.rowsCount++;
            this.template = new Template(this.templateText, this.templateSyntax);
            Element.insert(
                this.tableBody,
                {
                    'bottom':this.template.evaluate({
                        id: this.rowsCount,
                        name: values[key]['name'],
                        type: values[key]['type'],
                        order_attribute: values[key]['order_attribute'],
                        country_group: values[key]['country_group'],
                        renderer: values[key]['renderer'],
                        sortable: values[key]['sortable'],
                        priority: values[key]['priority'],
                        image: values[key]['image']
                    })
                }
            );

            typeSelect = $($('pack_shipping_grouping_' + this.rowsCount).select('select')[0]);
            typeSelect.setValue(values[key]['type']);
            countryGroupSelect = $($('pack_shipping_grouping_' + this.rowsCount).select('select')[1]);
            if (countryGroupSelect != undefined) {
                countryGroupSelect.setValue(values[key]['country_group']);
            }
        }
        this.bindRemoveBtns();
    }


};