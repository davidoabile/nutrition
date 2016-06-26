<?php

class Balance_State_IndexController extends Mage_Core_Controller_Front_Action{
	
	public function stateAction() {
	    $countrycode = $this->getRequest()->getParam('country');
	    $state = "<option value=''>Please Select</option>";
	    if ($countrycode != '') {
	        $statearray = Mage::getModel('directory/region')->getResourceCollection() ->addCountryFilter($countrycode)->load();
	        foreach ($statearray as $_state) {
	            $state .= "<option value='" . $_state->getCode() . "'>" .  $_state->getDefaultName() . "</option>";
	        }
	    }
	    echo $state;
	}
}


?>