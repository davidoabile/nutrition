<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of List
 *
 * @author om
 */
class Tatva_Catalogextensions_Block_Weekly_List extends Mage_Catalog_Block_Product_List
{
    protected function _getProductCollection()
    {
        parent::__construct();
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection
            ->addAttributeToFilter('Weekly_specials', '1')
            ->addAttributeToSelect(array('name', 'url_key', 'small_image', 'short_description'))
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addAttributeToFilter('visibility', array('in' => Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds()));
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);

        $this->_productCollection = $collection;

        return $this->_productCollection;
    }
    function get_prod_count()
    {
        //unset any saved limits
        Mage::getSingleton('catalog/session')->unsLimitPage();
        return (isset($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : 9;
    }// get_prod_count

    function get_cur_page()
    {
        return (isset($_REQUEST['p'])) ? intval($_REQUEST['p']) : 1;
    }// get_cur_page

    function get_order()
    {
        return (isset($_REQUEST['order'])) ? ($_REQUEST['order']) : 'ordered_qty';
    }// get_order

    function get_order_dir()
    {
        return (isset($_REQUEST['dir'])) ? ($_REQUEST['dir']) : 'desc';
    }// get_direction

    public function getToolbarHtml()
    {

    }
}

?>
