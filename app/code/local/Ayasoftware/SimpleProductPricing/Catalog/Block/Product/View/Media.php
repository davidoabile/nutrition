<?php

/**
 * @category    Ayasoftware
 * @package     Ayasoftware_SimpleProductPricing
 * @author      EL HASSAN MATAR <support@ayasoftware.com>
 */
class Ayasoftware_SimpleProductPricing_Catalog_Block_Product_View_Media extends Mage_Catalog_Block_Product_View_Media {

    public function getGalleryUrl($image = null) {

        if ($zoomType = Mage::getStoreConfig('spp/media/zoomtype')) {
            switch ($zoomType) {
                case 1:
                    $params = array(
                        'id' => $this->getProduct()->getId(),
                    );
                    if ($image) {
                        $params['image'] = $image->getValueId();
                        return $this->getUrl('*/*/gallery', $params);
                    }
                    return $this->getUrl('*/*/gallery', $params);
                default:
                    return parent::getGalleryUrl($image);
            }
        }
    }

}
