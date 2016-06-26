<?php
class Excellence_Fee_Model_Fee extends Varien_Object{
	const FEE_AMOUNT = 2.95;

	public static function getFee(){
		return self::FEE_AMOUNT;
	}
	public static function canApply($address){
		//put here your business logic to check if fee should be applied or not
		//if($address->getAddressType() == 'billing'){
		 $insrance = Mage::getSingleton("core/session")->getInsuranceAdd();
		if($insrance == 1){
		return true;
		}
	}
}