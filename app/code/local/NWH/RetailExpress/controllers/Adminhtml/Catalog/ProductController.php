<?php
/**
* NWH
*
* NOTICE OF LICENSE
* David Oabile
*/

require_once(Mage::getModuleDir('controllers','Mage_Adminhtml').DS.'Catalog'.DS.'ProductController.php');

class NWH_RetailExpress_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{
    /**
     * Get customd products grid and serializer block
     */
    public function stocklevelsAction()
    {
       $product =  $this->_initProduct();
       
    }

    /**
     * Get custom products grid
     */
    public function stocklevelsGridAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.custom')
            ->setProductsRelated($this->getRequest()->getPost('products_custom', null));
        $this->renderLayout();
    }

}
