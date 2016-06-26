<?php

Mage::getModel('core/config')->saveConfig('courierrules/tracking/email_notification', Mage::getStoreConfig('trans_email/ident_' . Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY) . '/email'));