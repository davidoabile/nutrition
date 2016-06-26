<?php

class Onibi_StoreLocator_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function shopAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function searchAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function filterAction() {

        $Objfilter = new Onibi_StoreLocator_Block_Store();
        $html = '';
        foreach ($Objfilter->getStores() as $store) :

            $html .= '<li>';
            $html .= '<div class="location_title font18_blk"><img src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."frontend/default/NWH/images/location_iconred.png" . '" /> <span class="store-name" id="store.' . $store->getId() . '">' . $store->getName() . '</span></div>';
            $html .= ' <a class="font12_red mrgn_t5" href="' .Mage::getBaseUrl(). "onibi_storelocator/index/shop/id/" . $store->getId() . '">Details</a>';
            $html .= '<div class="location_adrs font12_gry mrgn_t5">' . $store->getAddress() . ',' . $store->getCity() . ',' . $store->getZipcode() . ',' . $store->getState() . ',' . $store->getCountryId() . '</div>';
            $html .= '<div class="location_ph font12_red mrgn_t10">';
            if (!is_null($store->getPhone()) && $store->getPhone() != ''):
                $html .= $this->__('Ph: %s', $store->getPhone());
            endif;
            $html .= '</div>';
            $html .= '</li>';


        endforeach;
        echo $html;
    }

}