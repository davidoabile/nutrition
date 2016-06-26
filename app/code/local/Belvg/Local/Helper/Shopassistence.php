<?php
class Belvg_Local_Helper_Shopassistence extends Mage_Core_Helper_Data
{
    protected $_nextAttributeName    = NULL;
    protected $_currentAttributeName = NULL;

    protected function _loadPost($key)
    {
        $assistant  = Mage::app()->getRequest()->getParam('assistant');

        if (isset($assistant[$key])) {
            return $assistant[$key];
        } else {
            return '';
        }
    }

    public function setCurrentAttributeName($name)
    {
        $this->_currentAttributeName = $name;

        return $this;
    }

    public function setNextAttributeName($name)
    {
        $this->_nextAttributeName = $name;

        return $this;
    }

    public function currentAttributeName()
    {
        if ( !$this->_currentAttributeName ) {
            $this->_currentAttributeName = Mage::app()->getRequest()->getParam('current');
        }

        return $this->_currentAttributeName;
    }

    public function nextAttributeName()
    {
        if ( !$this->_nextAttributeName ) {
            $brand      = $this->_loadPost('brand');
            $goal       = $this->_loadPost('goal');
            $ingredient = $this->_loadPost('ingredient');
            if ($ingredient) {
                $this->_nextAttributeName = '';
            } elseif ($goal) {
                $this->_nextAttributeName = 'ingredients';
            } elseif ($brand) {
                $this->_nextAttributeName = 'goal';
            }
        }

        return $this->_nextAttributeName;
    }

    protected function _addShopAssistenceFilters($collection)
    {
        $brand      = $this->_loadPost('brand');
        $goal       = $this->_loadPost('goal');
        $ingredient = $this->_loadPost('ingredient');
        $current    = $this->currentAttributeName();

        $collection->addAttributeToSelect('brand',       'left');
        $collection->addAttributeToSelect('goal',        'left');
        $collection->addAttributeToSelect('ingredients', 'left');

        /* WHERE */
        switch ($current) {
            case 'ingredient':

            case 'goal':
                $collection->addAttributeToFilter('goal', $goal);
            case 'brand':
                $collection->addAttributeToFilter('brand', $brand);
        }

        /* GROUP BY */
        switch ($current) {
            case 'goal':
                $collection->groupByAttribute('ingredients');
                break;
            case 'brand':
                $collection->groupByAttribute('goal');
                break;
            default:
                $collection->groupByAttribute('brand');
        }

        /* JOIN LEFT */
        $optionValueTable = Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value');
        switch ($current) {
            case 'goal':  // ingredients

                break;
            case 'brand': // goals
                $collection->getSelect()
                    ->joinLeft(array("optval" => $optionValueTable),
                        "((at_" . $this->nextAttributeName() . ".value = optval.option_id) OR
                          (at_" . $this->nextAttributeName() . ".value LIKE CONCAT('%,',optval.option_id,'%')) OR
                          (at_" . $this->nextAttributeName() . ".value LIKE CONCAT('%',optval.option_id,',%')) OR
                          (at_" . $this->nextAttributeName() . ".value LIKE CONCAT('%,',optval.option_id,',%'))
                         )",
                        array('label_name' => 'value'))
                    ->order('label_name ASC');

                break;
            default: // brands
                $collection->getSelect()
                    ->joinLeft(array("optval" => $optionValueTable), "at_brand.value = optval.option_id", array('label_name' => 'value'))
                    ->order('label_name ASC');
        }

        return $this;
    }

    public function getOptionsHtml()
    {
        $collection    = Mage::getModel('catalog/product')->getCollection();
        $attributeName = $this->nextAttributeName();
        if ($attributeName) {
            $this->_addShopAssistenceFilters($collection);
        }

        $key   = $this->_loadPost($attributeName);

        $html  = '<option value="">' . $this->__('Shop by %s', ucfirst($attributeName)) . '</option>';
        $other = TRUE;
        foreach ($collection as $product) {
            if ($product[$attributeName]) {
                $html .= '<option value="' . $product[$attributeName] . '"' . (($key == $product[$attributeName]) ? ' selected' : '') . '>' . $product['label_name'] . '</option>';
            } else {
                if ($other) {
                    $html .= '<option value="0">' . $this->__('Others') . '</option>';
                }

                $other = FALSE;
            }
        }

        return $html;
    }
    
}