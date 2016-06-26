<?php
/**
 * Magento
 */
require_once 'abstract.php';
class Mage_UpdateRegionId extends Mage_Shell_Abstract
{
    public $_count=0;
    public $_region=array();
    public $_region_attb=0;
    public $_region_id_attb=0;
    public $_country_id_attb=0;

    public $_readConnection=null;
    public $_writeConnection=null;


    public $_magento_region=null;
    public $_magento_region_id=null;

    public $_table_varchar=null;
    public $_table_int=null;

    public function __construct() {
        parent::__construct();

        // Time limit to infinity
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
    }
    public function run()
    {

        $resource = Mage::getSingleton('core/resource');
        $this->_readConnection = $resource->getConnection('core_read');
        $this->_writeConnection = $resource->getConnection('core_write');


        $attribute_code = "region";
        $attribute_details =
            Mage::getSingleton("eav/config")->getAttribute('customer_address',    $attribute_code);
        $attribute = $attribute_details->getData();
        $this->_region_attb= $attribute['attribute_id'];

        $attribute_code = "region_id";
        $attribute_details =
            Mage::getSingleton("eav/config")->getAttribute('customer_address',    $attribute_code);
        $attribute = $attribute_details->getData();
        $this->_region_id_attb= $attribute['attribute_id'];

        $attribute_code = "country_id";
        $attribute_details =
            Mage::getSingleton("eav/config")->getAttribute('customer_address',    $attribute_code);
        $attribute = $attribute_details->getData();
        $this->_country_id_attb= $attribute['attribute_id'];

        $this->_table_varchar = $resource->getTableName('customer_address_entity_varchar');
        $this->_table_int = $resource->getTableName('customer_address_entity_int');

        $collection = Mage::getModel('directory/region')->getResourceCollection()
        ->addCountryFilter("AU")
        ->load();
        $_tp_array=array();

        $_tp_array_id=array();
        foreach($collection as $cl){
            $_tp_array[$cl->getCode()]=$cl->getDefaultName();
            $_tp_array_id[$cl->getCode()] = $cl->getRegionId();
        }
        $_tp_array['MELBORNE']='Victoria';
        $_tp_array['SYDNEY']='New South Wales';
        $_tp_array['VICTORIA']='Victoria';

        $_tp_array_id['MELBORNE']=$_tp_array_id['VIC'];
        $_tp_array_id['SYDNEY']=$_tp_array_id['NSW'];
        $_tp_array_id['VICTORIA']=$_tp_array_id['VIC'];
        $this->_magento_region=$_tp_array;
        $this->_magento_region_id=$_tp_array_id;

        //return;
        /*delete data of region id in table  customer_address_entity_int*/
        $query='DELETE FROM '.$this->_table_int.' WHERE attribute_id='.$this->_region_id_attb;
        $this->_writeConnection->query($query);
        $query='ALTER  TABLE  '.$this->_table_int.' AUTO_INCREMENT = 1';
        $this->_writeConnection->query($query);

        echo 'starting ..... '."\n";
        $address= Mage::getModel('customer/address')->getCollection();
        //$address->getSelect()->limit(50);
        Mage::getSingleton('core/resource_iterator')->walk($address->getSelect(), array(array($this, 'addressCallback')));
        echo "finished"."\n";
        //Mage::log($this->_region, Zend_Log::DEBUG, 'bi_debug.log');
    }


    // callback method
    public function addressCallback($args)
    {
        /*$address=Mage::getModel('customer/address');
        $address->setData($args['row']);*/
        $query='SELECT * FROM '.$this->_table_varchar.'  where entity_id = '.(int)$args['row']['entity_id'].' and attribute_id IN ('.$this->_region_attb.','.$this->_country_id_attb.')';
        $varchar = $this->_readConnection->fetchAll($query);
        $country=null;
        $region=null;
        foreach($varchar as $vc){
            if($vc['attribute_id'] == $this->_country_id_attb) $country = $vc['value'];
            if($vc['attribute_id'] == $this->_region_attb) $region = $vc['value'];
        }

        if($country == "AU"){

            /*Update region value on table  customer_address_entity_varchar*/
            if(!empty($region)){
                $region_name=$this->_magento_region[strtoupper($region)];
                $region_id=$this->_magento_region_id[strtoupper($region)];
                if(empty($region_id)){
                    foreach($this->_magento_region as $code => $mgt_rg){
                        if(strtoupper($mgt_rg) == strtoupper($region))
                            $region_id=$this->_magento_region_id[strtoupper($code)];

                    }
                }
                if(!empty($region_id)){
                    /*Update region id  value on table  customer_address_entity_int*/
                    $query2='INSERT INTO '.$this->_table_int.' (entity_type_id,attribute_id,entity_id,value) VALUES (2,'.$this->_region_id_attb.','.(int)$args['row']['entity_id'].','.(int)$region_id.')';
                    $this->_writeConnection->query($query2);
                }
                if(!empty($region_name)){
                 $query1='UPDATE '.$this->_table_varchar.' SET value="'.$region_name.'" WHERE entity_id = '.(int)$args['row']['entity_id'].' and attribute_id='.$this->_region_attb;
                 $this->_writeConnection->query($query1);
                 }

            }
        }
        $this->_count++;
        echo $this->_count."\n";
        //echo (int)$args['row']['entity_id']."\n";
        //$address=Mage::getModel('customer/address')->load($args['row']['entity_id']);

        /*if($address->getCountryId() == "AU"){
            $region=$address->getRegion();
            if (!in_array($region, $this->_region)) {
                array_push($this->_region,$region);
                $this->_count++;
                echo $this->_count."\n";
            }
            //Mage::log($address->getRegion(), Zend_Log::DEBUG, 'bi_debug.log');

        }*/
    }



}
$importCustomer = new Mage_UpdateRegionId();
$importCustomer->run();