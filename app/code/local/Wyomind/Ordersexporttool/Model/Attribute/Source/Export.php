<?php

class Wyomind_Ordersexporttool_Model_Attribute_Source_Export extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions() {
        if (is_null($this->_options)) {
            $collection = Mage::getModel("ordersexporttool/profiles")->getCollection();
            foreach ($collection as $profile) {
                $this->_options[] = array('label' => $profile->getFileName() . " [" . $profile->getFileId() . "]", "value" => $profile->getFileId());
            }
        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

}
