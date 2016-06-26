/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery.noConflict();

jQuery(document).ready(function ($) {
    // You can use the locally-scoped $ in here as an alias to jQuery.
    $("#product_info_tabs_stocklevels_tab").on('click', function () {
        $('#loading-mask').show();
        var sku = $('#product-info').data('sku');
        $.get('/stocklevels/index/grid', {sku: sku}, function (response) {
            loadData(response);

        });
    });
    
    $('.save-levels').on('click', function(){
        alert("Lazy David... didn't complete");
    });
    function sync() {
        $('.sync').on('click', function () {
            $('#loading-mask').show();
            var productId = $(this).data('productid');
            var channelId = $(this).data('channelid');
            var sku = $(this).data('sku');
            var seqno = $(this).data('seqno');
            if (parseInt(productId) > 0) {
                $.get('/stocklevels/index/sync', {seqno: seqno, productId: productId, channelId: channelId, sku: sku, reload: true}, function (response) {
                    loadData(response);
                });
            } else {
                $('#loading-mask').hide();
                alert('This is not a REX product');
            }
        });
    }


    function loadData(response) {
        if (response.success === true) {
            if (response.data.length > 0) {
                $('#stocklevels-main').html(new template('stocklevels-tpl', response.data).render());
                $('#stocklevels-main tr').each(function (i) {
                    var data = response.data[i];
                    var form = new template(null, data);
                    $(this).find('input, select, textarea, checkbox').each(function () {
                        form.populateFormTpl(this, data);
                    });
                });
            } else {
                $('#stocklevels-main').html('<tr class="a-center"><td colspan=8> No stock levels found</td></tr>');
            }

            $('#loading-mask').hide('slow');
            sync();
        }
    }
    function template(id, jsonData) {
        this.operators = ['-eq', '-ne', '-lt', '-le', '-gt', '-ge'];
        var me = this;
        this.tpl = '';
        this.foreachData = [];

        this.setTpl = function (tpl) {
            this.tpl = tpl;
        };
        /**
         * You can pass the new tpl to render
         * @param string tpl html
         * @param array format data attributes to pass to the format
         * @returns {unresolved}
         */
        this.render = function (tpl, eval) {
            if (typeof tpl !== 'undefined' && tpl !== null) {
                this.tpl = tpl;
            } else {
                this.tpl = $('#' + id).html();
            }
            if (this.tpl) {
                //$.isArray
                if ($.isPlainObject(jsonData)) {
                    return this.parseObject(jsonData, false, eval);
                } else {
                    return this.parse(jsonData, false, eval);
                }
            }
            console.log('invalid element id: ' + id);
        };

        this.parse = function (data, child, eval) {
            var i = 0,
                    len = data.length,
                    fragment = '';
            for (; i < len; i++) {
                if (child === false) {
                    fragment += this.replace(this.tpl, data[i], eval);

                } else {
                    fragment += this.replace3(this.tpl, data[i]);
                }
            }
            return fragment;
        };

        this.parseObject = function (data, child) {
            var fragment = '';

            for (var key in data) {
                if (child === false) {
                    fragment += this.replace(this.tpl, data[key]);
                    if (this.foreachData[key]) {
                        fragment += this.foreachData[key];
                    }
                } else {
                    fragment += this.replace3(this.tpl, data[key]);
                }
            }
            return fragment;
        };

        this.replace = function (tpl, obj, eval) {
            var t, key, reg, val;
            for (key in obj) {
                reg = new RegExp('{{' + key + '}}', 'ig');
                val = obj[key];
                if (typeof eval !== 'undefined' && eval !== null) {
                    if (eval.indexOf(key) > -1) {
                        val = this.format(tpl, key, val);
                    }
                }
                t = (t || tpl).replace(reg, val);
            }

            return t;

        };

        this.replace3 = function (tpl, obj, eval) {
            var t, key, reg;
            for (key in obj) {
                reg = new RegExp('{{' + key + '}}', 'ig');

                t = (t || tpl).replace(reg, obj[key]);
            }
            return t;

        };

        this.format = function (tpl, key, val) {
            var result = val;
            $(tpl).find('td').each(function () {
                if ($(this).data('field') === key) {
                    result = window[$(this).data('format')](val);
                    return false;
                }
            });
            return result;
        };

        this.checkForeach = function () {
            if (null === id) {
                return false;
            }
            $('#' + id + ' > div').each(function (index) {
                if ($(this).attr('data-type') === 'foreach') {
                    me.processForeach($(this).attr('id'), $(this).attr('data-key'));
                }
            });
        };

        this.populateForm = function (id, data) {
            var me = this;
            $('#' + id + ' input, select, textarea, checkbox').each(function () {
                me.populateFormTpl(this, data);
            });
        };
        this.populateFormTpl = function (obj, data) {
            var key = $(obj).attr('name');
            var type = $(obj).attr('type');
            if ($(obj).is('select')) {
                $(obj).val(data[key]).attr('selected', true).siblings('option').removeAttr('selected');
            } else if (type === 'checkbox') {
                if (data[key] === 'Y') {
                    $(obj).val(data[key]).prop('checked', true);
                } else {
                    $(obj).val(data[key]).prop('checked', false);
                }
            } else {
                var val = data[key];
                $(obj).val(val);
            }
        };

    }


});
