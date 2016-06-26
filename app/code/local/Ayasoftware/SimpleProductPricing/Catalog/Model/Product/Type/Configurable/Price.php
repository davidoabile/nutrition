<?php
/**
 * @category    Ayasoftware
 * @package     Ayasoftware_SimpleProductPricing
 * @author      EL HASSAN MATAR <support@ayasoftware.com>
 */
class Ayasoftware_SimpleProductPricing_Catalog_Model_Product_Type_Configurable_Price extends Mage_Catalog_Model_Product_Type_Price {

    
    //Force tier pricing to be empty for configurable products:
    public function getTierPrice($qty = null, $product) {
        return array();
    }
}