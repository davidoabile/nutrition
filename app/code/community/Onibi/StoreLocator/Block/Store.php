<?php
class Onibi_StoreLocator_Block_Store extends Mage_Core_Block_Template
{
    public function getDefaultMarker(){
        $defaultMarker = '';
        if(!is_null(Mage::getStoreConfig('storelocator/general/mapicon')) && Mage::getStoreConfig('storelocator/general/mapicon') != ''){
            $defaultMarker = 'storelocator/markers/'.Mage::getStoreConfig('storelocator/general/mapicon');
        }
        return $defaultMarker;
    }
    public function getStore(){
    	$id = $this->getRequest()->getParam("id");
    	$store = Mage::getModel('onibi_storelocator/store')->load($id);
    	return $store;
    }
    public function getPostValues($field){
    	$searchPost = $this->getRequest()->getPost();
    	if(empty($searchPost)) return false;
    	return $searchPost[$field];
    }
    public function getSearchValue_OLD(){
    $searchPost = $this->getRequest()->getPost();
	    if(!empty($searchPost)){
			$postCode = $searchPost["search-postcode"];
			$country = $searchPost["search-country"];
			if($postCode != ""){
				return $postCode;
			}else if($postCode == "" && $country != ""){
				return $this->getCountryByCode($country);
			}
		}
    }
   
   public  function getSearchValue(){
    $searchPost = $this->getRequest()->getPost();
	    if(!empty($searchPost)){
			$postCode = $searchPost["search-postcode"];
			$country = $searchPost["search-state"];
			if($postCode != ""){
				return $postCode;
			}else if($postCode == "" && $country != ""){
				//return $this->getCountryByCode($country);
				return $country;
			}
		}
    }
   
    public function getCountries(){
    	$allStores = Mage::getModel('onibi_storelocator/store')->getCollection()
            ->addFieldToFilter('status',1)
            ->addStoreFilter($this->getCurrentStore())
            ->addFieldToSelect(
                array(
                    'entity_id',
                    'name',
                    'address',
                    'zipcode',
                    'city',
                    'country_id',
                    'phone',
                    'fax',
                    'email',
                    'description',
                    'store_url',
                    'image',
                    'marker',
                    'lat',
                    'long'));
    	$countries = array();
    	foreach($allStores as $store){
    		$countries[] = 	$store->getCountryId();
    	}	
    	return array_unique($countries);
    }
    public function getStates(){
    	$allStores1 = Mage::getModel('onibi_storelocator/store')->getCollection()
            ->addFieldToFilter('status',1)
            ->addStoreFilter($this->getCurrentStore())
            ->addFieldToSelect(
                array(
                    'entity_id',
                    'name',
                    'address',
                    'zipcode',
                    'city',
                    'state',
                    'country_id',
                    'phone',
                    'fax',
                    'email',
                    'description',
                    'store_url',
                    'image',
                    'marker',
                    'lat',
                    'long'));
    	$states = array();
    	foreach($allStores1 as $store){
    		if($store->getState()){
    		$states[] = $store->getState();
    		}
    	}	
    	return array_unique($states);
    }
    public function getStores(){
       
    	$searchPost = $this->getRequest()->getPost();
    	
        $stores = Mage::getModel('onibi_storelocator/store')->getCollection()
            ->addFieldToFilter('status',1)
            ->addStoreFilter($this->getCurrentStore())
            ->addFieldToSelect(
                array(
                    'entity_id',
                    'name',
                    'address',
                    'zipcode',
                    'city',
                    'country_id',
                    'state',
                    'phone',
                    'fax',
                    'email',
                    'description',
                    'store_url',
                    'image',
                    'marker',
                    'lat',
                    'long'));
	if(!empty($searchPost)){
		$postCode = $searchPost["search-postcode"];
		$country = $searchPost["search-country"];
		$state = $searchPost["search-state"];
		$country = '';
		if($postCode != ""){
			$stores->addFieldToFilter('zipcode',$postCode );
		}else if($postCode == "" && $country != ""){
			$stores->addFieldToFilter('country_id',$country);
		}else if($postCode == "" && $country == "" &&  $state != ""){
			$stores->addFieldToFilter('state',$state);
		}
		
	}
        $storesCollection = new Varien_Data_Collection();
        foreach($stores as $store){
           if(!is_null($store->getCountryId())){
               $store->setCountryId($this->getCountryByCode($store->getCountryId()));
           }else{
               $store->setCountryId($this->__('NC'));
           }

            if(!is_null($store->getImage()) || $store->getImage() != ''){
                $imgUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$store->getImage();
            }elseif (!is_null(Mage::getStoreConfig('storelocator/general/defaultimage')) && Mage::getStoreConfig('storelocator/general/defaultimage') != ''){
                $imgUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'storelocator/images/'.Mage::getStoreConfig('storelocator/general/defaultimage');
            }else{
                $imgUrl = $this->getLogoSrc();
            }
            $store->setImage($imgUrl);
           $storesCollection->addItem($store);
        }
        
        return $storesCollection;
    }
    
    
    public function getStores_stateWise(){
    	$searchPost = $this->getRequest()->getPost();
    	
        $stores = Mage::getModel('onibi_storelocator/store')->getCollection()
            ->addFieldToFilter('status',1)
            ->addStoreFilter($this->getCurrentStore())
            ->addFieldToSelect(
                array(
                    'entity_id',
                    'name',
                    'address',
                    'zipcode',
                    'city',
                    'state',
                    
                    'phone',
                    'fax',
                    'email',
                    'description',
                    'store_url',
                    'image',
                    'marker',
                    'lat',
                    'long'));
	if(!empty($searchPost)){
		$postCode = $searchPost["search-postcode"];
		$state = $searchPost["search-state"];
		if($postCode != ""){
			$stores->addFieldToFilter('zipcode',$postCode );
		}else if($postCode == "" && $state != ""){
			$stores->addFieldToFilter('state',$state);
		}
	}
        $storesCollection = new Varien_Data_Collection();
        foreach($stores as $store){
         
            if(!is_null($store->getImage()) || $store->getImage() != ''){
                $imgUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$store->getImage();
            }elseif (!is_null(Mage::getStoreConfig('storelocator/general/defaultimage')) && Mage::getStoreConfig('storelocator/general/defaultimage') != ''){
                $imgUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'storelocator/images/'.Mage::getStoreConfig('storelocator/general/defaultimage');
            }else{
                $imgUrl = $this->getLogoSrc();
            }
            $store->setImage($imgUrl);
           $storesCollection->addItem($store);
        }
        
        return $storesCollection;
    }
    
    

    public function getGoogleApiUrl(){
        $apiUrl = Mage::getStoreConfig('storelocator/general/apiurl');
        if(is_null($apiUrl))
            $apiUrl = "http://maps.googleapis.com/maps/api/js?v=3";
        $apiKey = "&key=".Mage::getStoreConfig('storelocator/general/apikey');
        $apiSensor = Mage::getStoreConfig('storelocator/general/apisensor');
        $sensor = ($apiSensor == 0) ? 'false' : 'true';
        $urlGoogleApi = $apiUrl."&sensor=".$sensor.$apiKey."&callback=initialize&libraries=places";
        
        return $urlGoogleApi;
    }
    
    /**
     * retrieve current store
     *
     * @return Mage_Core_Model_Store
     */
    public function getCurrentStore()
    {
        return Mage::app()->getStore()->getId();
    }

    public function getCountryByCode($code){
        return Mage::getModel('directory/country')->loadByCode($code)->getName();
    }

    public function getLogoSrc()
    {
        if (empty($this->_data['logo_src'])) {
            $this->_data['logo_src'] = Mage::getStoreConfig('design/header/logo_src');
        }
        return $this->getSkinUrl($this->_data['logo_src']);
    }
}