<?php 

/**
 * Magento
 */



require_once 'abstract.php';

class Mage_UpdateTax extends Mage_Shell_Abstract
{
	
	public function run()
   	{		
		/**
		 * Get the resource model
		 */
		$resource = Mage::getSingleton('core/resource');

		/**
		 * Retrieve the write connection
		 */
		$writeConnection = $resource->getConnection('core_write');

		/**
		 * Retrieve our table name
		 */
		$table = $resource->getTableName('catalog_product_entity_int');
		$attribute_id=Mage::getModel('catalog/product')->getResource()->getAttribute('tax_class_id')->getAttributeId();   

		$query = "UPDATE {$table} SET value = '4' WHERE attribute_id = ". (int)$attribute_id;
			 
			/**
			 * Execute the query
			 */
			 echo 'start updating tax class : tax good for all product ...';
		  $writeConnection->query($query);
		  echo 'finish update tax class!';
	}
}
$shell = new Mage_UpdateTax();
$shell->run();


?>