
<?php 
class Ayasoftware_SimpleProductPricing_Checkout_Block_Cart_Item_Renderer_Configurable extends Mage_Checkout_Block_Cart_Item_Renderer_Configurable {
	
	
	 /**
     * Get item product name
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->getProduct()->getName(); 
    }
	
	
}