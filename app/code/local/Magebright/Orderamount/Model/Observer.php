<?php 
class Magebright_Orderamount_Model_Observer
{
	private $_helper;
    public function __construct() 
    {
        $this->_helper = Mage::helper('orderamount');
    }
	public function addAfter($observer){
	}
}