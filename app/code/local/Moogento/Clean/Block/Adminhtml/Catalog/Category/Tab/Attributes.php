<?php


class Moogento_Clean_Block_Adminhtml_Catalog_Category_Tab_Attributes extends Mage_Adminhtml_Block_Catalog_Category_Tab_Attributes
{
    public function __construct() {
        parent::__construct();
    }

    protected function _getAdditionalElementTypes()
    {

        return array_merge(
            parent::_getAdditionalElementTypes(),
            array(
                'date' => Mage::getConfig()->getBlockClassName('moogento_clean/helper_date'),
                'datetime' => Mage::getConfig()->getBlockClassName('moogento_clean/helper_datetime')
            )
        );
    }
} 