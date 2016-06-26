<?php
require_once Mage::getModuleDir('', 'Balance_Device') . "/Mobile_Detect.php";
class Balance_Device_Helper_Data extends Mage_Core_Helper_Abstract
{
	function isMobile(){
		$detect = new Mobile_Detect;
		if ($detect->isMobile()) {
			return 1;
		}
		return 0;
	}
}
	 