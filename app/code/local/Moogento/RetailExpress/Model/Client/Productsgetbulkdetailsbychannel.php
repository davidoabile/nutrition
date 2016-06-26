<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 10.02.15
 * Time: 20:18
 */

class Moogento_RetailExpress_Model_Client_Productsgetbulkdetailsbychannel
{
    public $ChannelId;

    public $LastUpdated;

    public function __construct()
    {
        $this->ChannelId = Mage::getStoreConfig('moogento_retailexpress/general/channel_id');

        $this->LastUpdated;
    }

    public function setChannelId($channelId)
    {
        $this->ChannelId = $channelId;
    }
    
    public function setLastUpdated($lastUpdated)
    {
        $this->LastUpdated = $lastUpdated;
}
}
