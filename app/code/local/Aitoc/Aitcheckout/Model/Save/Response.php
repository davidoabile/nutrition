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
class Aitoc_Aitcheckout_Model_Save_Response
{
    
    protected $_data = array();
    
    public function addStepResponse($step, $response)
    {
        $this->_data[$step] = $response;
        return $this;    
    }   
    
    public function isValid()
    {
        return true;    
    } 
    
    public function toArray()
    {
        if ($this->isValid())
        {
            return $this->_data;
        }        
    } 
    
}