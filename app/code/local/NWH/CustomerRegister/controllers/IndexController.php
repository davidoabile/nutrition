<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class NWH_CustomerRegister_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $name = 'coomer';
        $postcodeColletion = Mage::getModel('nwh_common/region')->getCollection()
                ->addFieldToSelect(['locality', 'postcode', 'seqno'])
                ->distinct(true);
        $postcodeColletion->getSelect()->join(array('region' => 'directory_country_region'), 'region.default_name = region1', array('code', 'default_name', 'region_id'));

        $postcodeColletion->addFieldToFilter('locality', array('like' => '%'. $name . '%'));
        $result = [];
        if ($postcodeColletion->count()) {
            foreach ($postcodeColletion as $k => $stock) {
                $result[] = $stock->getData();
            }
        }
        var_dump($result);
        exit;
    }

    public function autopopulateAction() {
        $search = $this->getRequest()->getParam('query', 'coomera');
        
        $postcodeColletion = Mage::getModel('nwh_common/region')->getCollection()
                ->addFieldToSelect(['locality', 'postcode', 'seqno'])
                ->distinct(true);
        $postcodeColletion->getSelect()->join(array('region' => 'directory_country_region'), 'region.default_name = region1', array('code', 'default_name', 'region_id'));

        $postcodeColletion->addFieldToFilter('locality', array('like' => '%'. $search . '%'));
        $result = [];
        if ($postcodeColletion->count()) {
            foreach ($postcodeColletion as $k => $geo) {
                $result[] = array('value' =>  $geo->getLocality() , 'data' => $geo->getData());
            }
        }
        $result[] = array('value' =>  'Create New' , 'data' => ['code' => 'N/A', 'postcode' => 0, 'default_name' => 'N/A']);
         $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('suggestions' => $result)));
    }

}
