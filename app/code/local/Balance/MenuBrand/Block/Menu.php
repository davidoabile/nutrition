<?php

class Balance_MenuBrand_Block_Menu extends Mage_Core_Block_Template {

    function getCollectionBrands() {
       // $arrayCollection = array();
        $data = array();
        /* $name = 'manufacturer';
          $attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter($name)->getFirstItem();
          $attributeId = $attributeInfo->getAttributeId();
          $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
          $attributeOptions = $attribute->getSource()->getAllOptions(false);
          if ($attributeOptions) {
          foreach ($attributeOptions as $key => $item) {
          $arrayCollection[$item['value']] = $item['label'];  //ucfirst(strtolower($item['label']));
          }
          }
          natcasesort($arrayCollection);
         * 
         */
        $manufacturerCollectionObj = Mage::getResourceModel('attributeSplash/page_collection')
                ->addFieldToSelect(array('option_id', 'display_name', 'url_key'))
                ->addFieldToFilter("attribute_code", 'manufacturer')
                ->addFieldToFilter("is_enabled", '1');

        $manufacturerCollection =  array();
        foreach ($manufacturerCollectionObj as $k => $v) {
            $data = $v->getData();
            $manufacturerCollection[$data['option_id']] = $data;
        }
        natcasesort($manufacturerCollection);
        // asort($arrayCollection);
        $str = "A-B-C-D-E-F-G-H-I-J-K-L-M-N-O-P-Q-R-S-T-U-V-W-X-Y-Z";
        $data = array();
        $alphaParent = explode("-", $str);
        foreach ($alphaParent as $key => $parent) {
            foreach ($manufacturerCollection as $key_data => $item) {
                if (strtoupper(substr($item['display_name'], 0, 1)) == $parent) {
                    $data[$parent][$key_data] = $item;
                }
            }
        }
        return $data;
    }

    function getUrlSplash($options_id, $data = array()) {
        $url = "";
        if (count($data) > 0) {
            if (!empty($data['url_key'])) {
                $url = '/' .  $data['url_key'] . '.html';
            } else {
                $url = $this->getUrl() . 'catalogsearch/advanced/result/?manufacturer=' . (int) $options_id;
            }
        } else {// silly way of running queries in a loop...
            $collection = Mage::getResourceModel('attributeSplash/page_collection')->addFieldToFilter("attribute_code", intval($options_id))
                    ->addStoreFilter(Mage::app()->getStore())
                    ->setPageSize(1)
                    ->setCurPage(1)
            ;
            if ($collection->getSize()) {
                $splash = $collection->getFirstItem();
                $url = $splash->getUrl();
            } else {
                $url = $this->getUrl() . 'catalogsearch/advanced/result/?manufacturer=' . $options_id;
            }
        }
        return $url;
    }

}

?>