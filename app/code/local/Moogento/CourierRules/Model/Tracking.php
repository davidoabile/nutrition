<?php

class Moogento_CourierRules_Model_Tracking extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('moogento_courierrules/tracking');
    }

    public function getCodes()
    {
        $codes = $this->getData('codes');
        return preg_split('((\r)?\n|,|;|\|)', $codes);
    }

    public function useCode()
    {
        $codes = $this->getCodes();
        $code = array_shift($codes);
        if (count($codes) <= $this->getWarnLow()) {
            $this->_sendWarnEmail();
        }
        $this->setCodes(implode("\n", $codes));
        $this->save();

        return $code;
    }

    protected function _sendWarnEmail()
    {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        Mage::getModel('core/email_template')
            ->sendTransactional(
                Mage::getStoreConfig('courierrules/email/warn_low'),
                Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY),
                Mage::getStoreConfig('courierrules/tracking/email_notification'),
                null,
                array(
                    'pool_name'  => $this->getName(),
                )
            );

        $translate->setTranslateInline(true);
    }
} 