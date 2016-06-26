<?php
/**
 * One Step Checkout Manager : One Step Checkout Manager (CFM Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckoutfields
 * @version      1.0.9 - 2.9.8
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckoutfields_Model_Order_Field extends Aitoc_Aitcheckoutfields_Model_Field_Abstract
{
    protected $_eventPrefix = 'aitcfm_order_field';
    
    protected $_fieldType = 'order';

    protected function _construct()
    {
        $this->_init('aitcheckoutfields/order_field');
    }
}