<?php 
class NWH_SaveInsurance_Model_Observer{
	function updateInsurance($observer){
		$order = $observer->getEvent()->getOrder();
		$checkInsurance = Mage::getSingleton("core/session")->getInsuranceAdd();
		$order->setData("insurance",$checkInsurance);
		$order->save();
	}
}
 ?>
