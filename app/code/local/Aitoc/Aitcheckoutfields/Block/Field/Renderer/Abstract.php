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
class Aitoc_Aitcheckoutfields_Block_Field_Renderer_Abstract  extends Mage_Core_Block_Abstract
{
    public function setParams(array $data) {
        foreach($data as $key => $value)
        {
            $this->$key=$value;
        }
        return $this;
    }
    
    public function render()
    {
        return '';
    }
}

?>