<?php
/**
 * Location extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright 2013 Andrew Kett. (http://www.andrewkett.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://andrewkett.github.io/Ak_Locator/
 */

/* @var $installer ak_locator_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/* Create table 'ak_locator/location' */
$table = $installer->getConnection()
    //->newTable('ak_locator_search_override')
    ->newTable($installer->getTable('ak_locator/search_override'))
    ->addColumn('string', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        'primary'   => true,
    ), 'Search String')
    ->addColumn('params', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Overide Parameters')
    ->setComment('Custom Search Table');

$installer->getConnection()->createTable($table);

$installer->endSetup();
