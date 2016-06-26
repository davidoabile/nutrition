<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Grid widget massaction default block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Moogento_Core_Block_Adminhtml_Widget_Grid_Massaction extends Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract
{
    public function getJavaScript()
    {
        return " var {$this->getJsObjectName()} = new varienGridMassaction('{$this->getHtmlId()}', "
               . "{$this->getGridJsObjectName()}, '{$this->getSelectedJson()}'"
               . ", '{$this->getFormFieldNameInternal()}', '{$this->getFormFieldName()}');"
               . "{$this->getJsObjectName()}.setItems({$this->getItemsJson()}); "
               . ($this->getUseAjax() ? "{$this->getJsObjectName()}.setUseAjax(true);" : '')
               . ($this->getUseSelectAll() ? "{$this->getJsObjectName()}.setUseSelectAll(true);" : '')
               . "{$this->getJsObjectName()}.errorText = '{$this->getErrorText()}';"
               . "jQuery(function($){
                    var btn = $('.massaction .selectall, #szy_sellect_all');
                        btn.addClass('disabled');
                        btn.attr('onClick', 'void 0;return false;')
                   $.get('{$this->getUrl('*/sales_grid/ids')}', function(response){
                        if (typeof gridTotalCount !== 'undefined' && response.split(',').length != gridTotalCount) {
                            $.get('{$this->getUrl('*/sales_grid/ids', array('reset_cache' => 1))}', function(response){
                                {$this->getJsObjectName()}.setGridIds(response);
                                btn.removeClass('disabled');
                                btn.attr('onclick', 'return {$this->getJsObjectName()}.selectAll()');
                            });
                        } else {
                            {$this->getJsObjectName()}.setGridIds(response);
                            btn.removeClass('disabled');
                            btn.attr('onclick', 'return {$this->getJsObjectName()}.selectAll()');
                        }
                   });
               })";
    }
}
