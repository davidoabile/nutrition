<?php
class Belvg_Local_Block_Cmsblock extends Mage_Core_Block_Template
{
    protected function _construct()
    {
    	
        if ($this->getIdentifier() != 'footer_links_account') {
        	
           /* $this->addData(array(
                'cache_lifetime'    => 9999999999,
                'cache_tags'        => array('footer_cmsblock_' . $this->getIdentifier()),
                'cache_key' => $this->getIdentifier()
            ));*/
        }
    }   

    public function getTitle()
    {
        return Mage::helper('local')->getStaticBlockTitle($this->getIdentifier());
    }

    public function getContent()
    {
        return Mage::helper('local')->getStaticBlockHtml($this->getIdentifier());
    }
}