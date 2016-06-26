<?php

class Moogento_EasyCoupon_Model_Resource_Shortlink extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_serializableFields   = array(
        'skus' => array(
            array(),
            array(),
            true
        ),
    );

    protected function _construct()
    {
        $this->_init('moogento_easycoupon/shortlink', 'id');
    }
} 