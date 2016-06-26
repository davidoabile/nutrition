<?php


class Moogento_RetailExpress_Model_Retailexpress_Status
{
    const PENDING = 0;
    const SUCCESS = 1;
    const PENDING_RETRY = 2;
    const ERROR = 3;
    const PROCESSING = 4;
    const SUCCESS_MANUAL = 5;

    public static function toOptionArray()
    {
        $helper = Mage::helper('moogento_retailexpress');

        return array(
            self::PENDING => $helper->__('Pending'),
            self::SUCCESS => $helper->__('Success'),
            self::PENDING_RETRY => $helper->__('Failed / Pending retry'),
            self::ERROR => $helper->__('Error'),
            self::SUCCESS_MANUAL => $helper->__('Success (Manual)'),
        );
    }
} 