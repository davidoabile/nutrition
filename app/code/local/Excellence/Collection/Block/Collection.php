<?php

class Excellence_Collection_Block_Collection extends Mage_Catalog_Block_Product_List {

    protected function _getProductCollection() {
        if (is_null($this->_productCollection)) {
            $collection = Mage::getModel('catalog/product')->getCollection();
            if (count($_GET) > 0) {
                foreach ($_GET as $k => $v) {
                    if ($v != '' && ($k == 'brand' || $k == 'goal' || $k == 'ingredients')) {
                        $collection->addAttributeToFilter($k, array("finset"=>$v));
                    }
                }
            }
            $collection
                    ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                    ->addMinimalPrice()
                    ->addFinalPrice()
                    ->addTaxPercents();

            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }
 protected function _prepareLayout()
    {
        $helper = Mage::helper('zeon_manufacturer');
        // show breadcrumbs
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb(
                'home', 
                array(
                    'label'=>$helper->__('Home'), 
                    'title'=>$helper->__('Go to Home Page'), 
                    'link'=>Mage::getBaseUrl()
                )
            );
            $breadcrumbs->addCrumb(
                'collection', 
                array(
                    'label'=>$helper->__('SHOP ASSISTANT'), 
                    'title'=>$helper->__('SHOP ASSISTANT')
                )
            );
        }
        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->setTitle($helper->getDefaultTitle());
            $head->setKeywords($helper->getDefaultMetaKeywords());
            $head->setDescription($helper->getDefaultMetaDescription());
        }

        return parent::_prepareLayout();
    }

}
