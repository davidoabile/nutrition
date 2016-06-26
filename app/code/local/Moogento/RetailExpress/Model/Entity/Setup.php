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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * EAV Entity Setup Model
 *
 * @category   Mage
 * @package    Mage_Eav
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Moogento_RetailExpress_Model_Entity_Setup extends Mage_Eav_Model_Entity_Setup
{
    /**
     * Add Attribure Option
     *
     * @param array $option
     */
    public function addAttributeOption($option)
    {
        $optionTable        = $this->getTable('eav/attribute_option');
        $optionValueTable   = $this->getTable('eav/attribute_option_value');

        if (isset($option['value'])) {
            foreach ($option['value'] as $optionId => $values) {
                $intOptionId = (int) $optionId;
                if (!empty($option['delete'][$optionId])) {
                    if ($intOptionId) {
                        $condition = array('option_id =?' => $intOptionId);
                        $this->_conn->delete($optionTable, $condition);
                    }
                    continue;
                }

                if (!$intOptionId) {
                    $data = array(
                        'attribute_id'  => $option['attribute_id'],
                        'sort_order'    => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                        'retail_express_id'   => $option['retail_express_id'],
                    );
                    $this->_conn->insert($optionTable, $data);
                    $intOptionId = $this->_conn->lastInsertId($optionTable);
                } else {
                    $data = array(
                        'sort_order'    => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                    );
                    $this->_conn->update($optionTable, $data, array('option_id=?' => $intOptionId));
                }

                // Default value
                if (!isset($values[0])) {
                    Mage::throwException(Mage::helper('eav')->__('Default option value is not defined'));
                }
                $condition = array('option_id =?' => $intOptionId);
                $this->_conn->delete($optionValueTable, $condition);
                foreach ($values as $storeId => $value) {
                    $data = array(
                        'option_id' => $intOptionId,
                        'store_id'  => $storeId,
                        'value'     => $value,
                    );
                    $this->_conn->insert($optionValueTable, $data);
                }
            }
        }
        else if (isset($option['values'])) {
            foreach ($option['values'] as $sortOrder => $label) {
                // add option
                $data = array(
                    'attribute_id' => $option['attribute_id'],
                    'sort_order'   => $sortOrder,
                );
                $this->_conn->insert($optionTable, $data);
                $intOptionId = $this->_conn->lastInsertId($optionTable);

                $data = array(
                    'option_id' => $intOptionId,
                    'store_id'  => 0,
                    'value'     => $label,
                );
                $this->_conn->insert($optionValueTable, $data);
            }
        }
    }
}
