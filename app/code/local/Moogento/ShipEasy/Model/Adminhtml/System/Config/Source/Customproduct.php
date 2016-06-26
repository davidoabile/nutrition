<?php
class Moogento_ShipEasy_Model_Adminhtml_System_Config_Source_Customproduct
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
                        ||  !$inputType
                        || in_array($inputType, array('gallery', 'media_image', 'image'))
                        || in_array($code, array('tier_price', 'group_price', 'recurring_profile', 'sku'))) {
                    continue;
                }
                array_push($list, array('value' => $code, 'label'=>$attribute->getData('frontend_label')));
            }
        }

        usort($list, array($this, '_sort'));
        return $list;
    }

    protected function _sort($a, $b)
    {

        if (mb_strtolower($a['label']) == mb_strtolower($b['label'])) return 0;
        return mb_strtolower($a['label']) < mb_strtolower($b['label']) ? -1 : 1;
    }
}
