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
 * File        orderView.js
 * @category   Moogento
 * @package    shipEasy
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://www.moogento.com/License.html
 */
jQuery(document).ready(function ($) {
    
    $('#anchor-content').on('click', '#customer_info .customer_name .value.non-edit .edit-icon', function (e) {
        var parent_td = $(this).closest('.non-edit');
        parent_td.removeClass('non-edit');
        parent_td.addClass('for-edit');
        $("#select_customer").focus();
    });

    AutoComplete({
        _Blur: function(e) {
            var el = $(e.target);
            $('#customer_info .customer_name .moogento-spiner').hide();
            setTimeout(function() {
                el.siblings('.autocomplete').removeClass('open');
                el.closest('.for-edit')
                    .removeClass('for-edit')
                    .addClass('non-edit');
            }, 500);
        },
        _Select: function(item) {
            var el = $(item);
            $('.customer_name_container').text(el.text());
            $.ajax({
                type: 'POST',
                url: $("#select_customer").data('save-url'),
                dataType: 'json',
                data: {
                    customer_id: el.data('autocomplete-value'),
                    form_key: FORM_KEY
                },
                success: function (data) {
                    window.location.reload();
                }
            });
        },
        _Pre: function() {
            $('#customer_info .customer_name .moogento-spiner').show();
            return this.Input.value;
        },
        _Post: function(response) {
            $('#customer_info .customer_name .moogento-spiner').hide();
            try {
                var returnResponse = [];

                //JSON return
                var json = JSON.parse(response);


                if (Object.keys(json).length == 0) {
                    return "";
                }

                if (Array.isArray(json)) {
                    for (var i = 0 ; i < Object.keys(json).length; i++) {
                        returnResponse[returnResponse.length] = { "Value": json[i], "Label": json[i] };
                    }
                } else {
                    for (var value in json) {
                        returnResponse.push({
                            "Value": value,
                            "Label": json[value]
                        });
                    }
                }

                return returnResponse;
            } catch (event) {
                //HTML return
                return response;
            }
        }
    }, "#select_customer");
});