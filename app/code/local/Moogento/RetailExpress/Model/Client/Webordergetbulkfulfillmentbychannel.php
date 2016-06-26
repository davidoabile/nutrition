<?php


class Moogento_RetailExpress_Model_Client_Webordergetbulkfulfillmentbychannel
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
        $this->LastUpdated = date_format(new DateTime($lastUpdated), 'Y-m-d\TH:i:s.000\Z');
    }
} 