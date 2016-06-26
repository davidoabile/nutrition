<?php
/**
 * Magento
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once '../abstract.php';
require_once 'general/func.crypt.php';
require_once 'general/blowfish.php';

class Mage_ImportCustomers extends Mage_Shell_Abstract
{
	protected $_connection;
	protected $_prefixTable = 'xcart_';
	protected $_defaultLang = 1;
	protected $_rootParentCat = 2; 
	protected $_storeId = 1;
	protected $_websiteId = 1;
	protected $_store = 1;
	protected $_countInstert = 0;
	protected $_countExisted = 0;

	protected $_mapFields = array(
		'general' => array(
			'prefix'	=> 'title',
			'firstname'	=> 'firstname',
			'lastname'	=> 'lastname',
			'email'		=> 'email',
		),
		'shipping_address' => array(
			'prefix' 	=> 's_title',
			'firstname' => 's_firstname',
			'lastname' 	=> 's_lastname',
			'street' 	=> 's_address',
			'city' 		=> 's_city',
			'region' 	=> 's_state',
			'postcode' 	=> 's_zipcode',
			'country_id'=> 's_country',
		),
		'billing_address' => array(
			'prefix' 	=> 'b_title',
			'firstname' => 'b_firstname',
			'lastname' 	=> 'b_lastname',
			'company'	=> 'company',
			'street' 	=> 'b_address',
			'city' 		=> 'b_city',
			'region' 	=> 'b_state',
			'postcode' 	=> 'b_zipcode',
			'telephone'	=> 'phone',
			'fax'		=> 'fax',
			'country_id'=> 'b_country',
		),
	);


	public function __construct() {
        parent::__construct();
 
        // Time limit to infinity
        ini_set('memory_limit', '1024M'); 
        set_time_limit(0);  


        $this->_store = Mage::getModel('core/store')->load($this->_storeId);
    }
	public function run()
   	{	
   		$count = 0;
   		$error = 0;
		$new_db_resource = Mage::getSingleton('core/resource');
		$this->_connection = $new_db_resource->getConnection('xcart');

		$customers = $this->getCustomers();

		foreach ($customers as $customer) {

			echo $count + 1 . ' ';

			if ($this->createCustomer($customer)) {
				$count++;
			} else {
				$error++;
			}
			// if ($count==4)
			// 	break;
		}
		echo "Inserted {$this->_countInstert} customers. \n";
		echo "{$this->_countExisted} existed customers. \n";
		echo "Error {$error} customers. \n";
	}
	public function createCustomer($data)
	{
		if (is_array($data)) {

			echo "Starting {$data['email']} ...";


			$customer = $this->_importCustomer($data);
			if (!$customer->getDefaultBilling()) {
				$this->_importAddress('billing_address', $customer, $data, true, false);
			}
			if (!$customer->getDefaultShipping()){
				$this->_importAddress('shipping_address', $customer, $data, false, true);
			}
			
		}

		return true;
	}
	protected function _getPasswordDecoded($password, $salt)
	{
		$result = text_decrypt($password, $salt);	
		return $result;
	}

	public function getCustomers()
	{
		$query = "SELECT *
					FROM {$this->_prefixTable}customers
					WHERE 1=1";
		
		$results    = $this->_connection->query($query);

		return $results;
	}
	public function _importCustomer($data) 
	{

		$groupId = 1;
		$customer = Mage::getModel('customer/customer');

		$email = trim($data['email']);
		$password = $data['password'];
		$createAt = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s', $data['first_login']);

		//for testing
		if (!$password || $password == " ") {
			$password = "123456";
		}
		$password = $this->_getPasswordDecoded($password, 'fc6b162e60219e3344b1e55e6106bb2f');
		if (!$password || $password == "" || strlen($password) < 6) {
			$password = "123456";
		}
		$customer->setWebsiteId($this->_websiteId);
		$customer->setStore($this->_store);
		$customer->setStoreId($this->_storeId);
		$customer->loadByEmail($email);


		if(!$customer->getId()) {
			$customer->setEmail($email);
			$customer->setFirstname($data['firstname']);
			$customer->setLastname($data['lastname']);
			$customer->setPassword($password);
			$customer->setGroupId($groupId);
			$customer->setCreatedAt($createAt);
			$customer->setUpdatedAt($createAt);
			$customer->setWebsiteId($this->_websiteId);
			$customer->setStore($this->_store);
			$customer->setStoreId($this->_storeId);
			//set customer data
			foreach ($this->_mapFields['general'] as $key => $value) {
				$customer->setData($key, $data[$value]);
			}
			try {

				$customer->setConfirmation(null);
				$customer->save();

				//Make a "login" of new customer
                Mage::getSingleton('customer/session')->loginById($customer->getId());

				echo "Suceeded \n ";
				$this->_countInstert++;
			}
			catch (Exception $ex) {
				Mage::log($ex->getMessage());
			}
			return $customer;
		}



		echo "Existed \n ";

		$this->_countExisted++;

		return $customer;
	}
	public function _importAddress($addressType = 'billing_address', $customer, $data, $defaultBilling = false, $defaultShipping = false) {
		$customerAddress = Mage::getModel('customer/address');

		//set data
		foreach ($this->_mapFields[$addressType] as $key => $value) {
			if ('street' === $key) {
				$customerAddress->setData($key, array(
					'0'	=> $data[$value]
				));
			} else {
				$customerAddress->setData($key, $data[$value]);
			}
		}
		
		$customerAddress->setCustomerId($customer->getId())
			->setIsDefaultBilling($defaultBilling)
			->setIsDefaultShipping($defaultShipping)
			->setSaveInAddressBook(1);
		try {
				$customerAddress->save();
		}
		catch (Exception $ex) {
				Mage::log($ex->getMessage());
		}
	}
	protected function _getCustomerGroup($groupName) {
		$targetGroup = Mage::getModel('customer/group');
		$targetGroup->load($groupName, 'customer_group_code');
		return $targetGroup->getId();
	}
	protected function _importCustomerGroup($groupName) {
		if ($this->_getCustomerGroup($groupName) == null) {
			//Get Customer Group Model
			$customer_group = Mage::getModel('customer/group');
			//Here Set Your Customer Group Code Liked as General,Guest,Reatiler
			$customer_group->setCode($groupName);
			//Here Set Your Customer Group TaxClass id Based on your region setting location
			$customer_group->setTaxClassId(3);
			//Save it now.
			$customer_group->save();
			return $this->_getCustomerGroup($groupName);
		}
		return $this->_getCustomerGroup($groupName);
	}
}

$importCustomer = new Mage_ImportCustomers();
$importCustomer->run();
