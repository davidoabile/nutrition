<?php


class Moogento_PickNScan_Model_Adminhtml_System_Config_Source_Product_Attribute
{
    public function toOptionArray()
    {
        $attributes = Mage::getSingleton('eav/config')
            ->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeCollection();

        $list = array();

        foreach ($attributes as $attribute) {
            if ($attribute->getData('frontend_label')) {
                $inputType = $attribute->getFrontend()->getInputType();
                $code = $attribute->getData('attribute_code');
                if (!$attribute->getData('is_visible')
                    || !$inputType
                    || in_array($inputType, array('gallery', 'media_image', 'image'))
                    || in_array($code, array('tier_price', 'group_price', 'recurring_profile'))
                    || !$attribute->getData('frontend_label')) {
                    continue;
                }
                $list[$code] = $attribute->getData('frontend_label');
            }
        }

        asort($list);

        $finalList = array(array(
            'value' => '',
            'label' => '',
        ));
        foreach ($list as $code => $label) {
            $finalList[] = array(
                'value' => $code,
                'label' => $label,
            );
        }

        return $finalList;
    }
} 