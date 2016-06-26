<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Config.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Model_Sales_Order_Config extends Mage_Sales_Model_Order_Config
{
    protected $_statesWithStatuses = null;

    public function getStatusState($status)
    {
        $this->_getAllStates();

        $states = $this->getStates();
        $result = array();

        foreach ($states as $state_code => $state_label) {
        	$statuses = $this->getStateStatuses($state_code,true);
        	foreach($statuses as $status_code => $status_label)
        	{
        		if($status == $status_code)
        		{
					return $state_code;
        		}
        	}
			$result[$state_code]['status'] = $this->getStateStatuses($state_code,true);
        }


        return 'not_found';
    }

    public function getStates()
    {
        $states = array();
        foreach ($this->getNode('states')->children() as $state) {
            $label = (string) $state->label;
            $states[$state->getName()] = Mage::helper('sales')->__($label);
        }
        return $states;
    }

    protected function _getAllStates()
    {
        if (is_null($this->_statesWithStatuses)) {
            $this->_statesWithStatuses = array();
            foreach ($this->getNode('states')->children() as $state) {
                $name = $state->getName();
                $this->_statesWithStatuses[$name] = array();
                foreach ($state->statuses->children() as $status) {
                    $this->_statesWithStatuses[$name][] = $status->getName();
                }
            }

        }

        return $this->_statesWithStatuses;
    }

}
