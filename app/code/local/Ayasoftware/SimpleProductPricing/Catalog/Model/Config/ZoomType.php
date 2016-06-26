<?php
/**
 * @category    Ayasoftware
 * @package     Ayasoftware_SimpleProductPricing
 * @author      EL HASSAN MATAR <support@ayasoftware.com>
 */
class Ayasoftware_SimpleProductPricing_Catalog_Model_Config_ZoomType extends Mage_Core_Model_Config_Data {
    
    const ZOOM_1 = 1;
    const ZOOM_2 = 2;
    const ZOOM_3 = 3;

    /**
     * Fills the select field with values
     * 
     * @return array
     */
    public function toOptionArray() {    	
        return array(            
            self::ZOOM_1 => Mage::helper('spp')->__('Standard Zoom'),
            self::ZOOM_2 => Mage::helper('spp')->__('RWD Zoom'),    
            self::ZOOM_3 => Mage::helper('spp')->__('No Zoom'),    
          );
    }
}