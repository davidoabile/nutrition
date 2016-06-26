<?php

$installer = $this;

$this->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order_grid')}` LIKE 'courierrules_processed';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `courierrules_processed` DATETIME NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order_grid')}` LIKE 'courierrules_rule_id';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `courierrules_rule_id` int(11) NULL DEFAULT NULL;");
}
        
$installer->run(<<<SQL

    UPDATE
        {$this->getTable('sales/order_grid')}
    SET
        `courierrules_processed` =
        (
            SELECT
                `courierrules_processed`
            FROM
                {$this->getTable('sales/order')}
            WHERE
                {$this->getTable('sales/order')}.`entity_id` = {$this->getTable('sales/order_grid')}.`entity_id` 
            GROUP BY 
                {$this->getTable('sales/order_grid')}.`entity_id`
        );
    
    UPDATE
        {$this->getTable('sales/order_grid')}
    SET
        `courierrules_rule_id` =
        (
            SELECT
                `courierrules_rule_id`
            FROM
                {$this->getTable('sales/order')}
            WHERE
                {$this->getTable('sales/order')}.`entity_id` = {$this->getTable('sales/order_grid')}.`entity_id` 
            GROUP BY 
                {$this->getTable('sales/order_grid')}.`entity_id`
        );
    
SQL
);
        
$this->endSetup();