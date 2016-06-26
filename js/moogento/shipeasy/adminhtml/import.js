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
* File        import.js
* @category   Moogento
* @package    shipEasy
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://www.moogento.com/License.html
*/ 

function changeValue(selectElm)
{
    selectElm = $(selectElm);
    attrId = selectElm.id.replace('attr_preset_', '');
    if (selectElm.getValue() == 'custom') {
        $($('custom_value_' + attrId).up('tr')).show();
    } else {
        $($('custom_value_' + attrId).up('tr')).hide();
    }
}

function changeAttribute(selectElm)
{
    selectElm = $(selectElm);
    attrId = selectElm.id.replace('additional_action_', '');
    if (selectElm.getValue() == 1) {
        $($('attr_preset_' + attrId).up('tr')).show();
        changeValue($('attr_preset_' + attrId));
    } else {
        $($('attr_preset_' + attrId).up('tr')).hide();
        $($('custom_value_' + attrId).up('tr')).hide();
    }
}

document.observe("dom:loaded", function() {
    $$('.szy_attribute_preset').each(function(elm){
        $($(elm).up('tr')).hide();
    });
    $$('.szy_attribute_preset_new').each(function(elm){
        $($(elm).up('tr')).hide();
    });

    $$('.szy_attribute_preset').each(function(elm){
        $(elm).observe('change', function(event){
            eventElm = $(Event.element(event));
            changeValue(eventElm);
        });
    });

    $$('.szy_attribute').each(function(elm){
        $(elm).observe('change', function(event){
            eventElm = $(Event.element(event));
            changeAttribute(eventElm);
        });
        changeAttribute($(elm));
    });

});