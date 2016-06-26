<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of newPHPClass
 *
 * @author david
 */
class Moogento_RetailExpress_Model_Client_ProductGetDetailsStockPricingByChannel {

    public $ChannelId;
    public $ProductId;
    public $CustomerId = 0; 
    public $PriceGroupId = 0;
    
    public function __construct() {
        $this->ChannelId = Mage::getStoreConfig('moogento_retailexpress/general/channel_id');
    }

    public function setChannelId($channelId) {
        $this->ChannelId = $channelId;
    }

     public function setProductId($productId)
    {
        $this->ProductId = $productId;
    }
   

}
