<?php
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckout_Model_System_Config_Source_DefaultShipping
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $methods = array(array('value'=>'','label'=>Mage::helper('adminhtml')->__('No default shipping method')));

        $activeCarriers = Mage::getSingleton('shipping/config')->getActiveCarriers();
        foreach($activeCarriers as $carrierCode => $carrierModel)
        {
            $options = array();
            if( $carrierMethods = $carrierModel->getAllowedMethods() )
            {
                foreach ($carrierMethods as $methodCode => $method)
                {
                    $code= $carrierCode.'_'.$methodCode;
                    $options[]=array('value'=>$code,'label'=>$method);

                }
                $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');

            }
            $methods[]=array('value'=>$options,'label'=>$carrierTitle);
        }
        return $methods;
    }

}