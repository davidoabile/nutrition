<?php
/**
 * NWH Setup Attribute
 *
 * @category  NWH
 * @package   NWH_Catalog
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */


$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

if ($installer->tableExists($installer->getTable('catalog_product_entity'))) {
    $installer->run("ALTER TABLE {$this->getTable('catalog_product_entity')}
                    MODIFY COLUMN `entity_id` int(10) unsigned NOT NULL auto_increment");
}

$installer->endSetup(); 