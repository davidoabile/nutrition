<?php

$installer = $this;

$this->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order_grid')}` LIKE 'courierrules_tracking';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `courierrules_tracking` varchar(255) NULL DEFAULT null;");
}
      
$installer->run(<<<SQL

    UPDATE
        {$this->getTable('sales/order_grid')}
    SET
        `courierrules_tracking` =
        (
            SELECT
                `courierrules_tracking`
            FROM
                {$this->getTable('sales/order')}
            WHERE
                {$this->getTable('sales/order')}.`entity_id` = {$this->getTable('sales/order_grid')}.`entity_id` 
            GROUP BY 
                {$this->getTable('sales/order_grid')}.`entity_id`
        );

SQL
);

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/shipment_track')}` LIKE 'from_courierrule';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/shipment_track')}` ADD COLUMN `from_courierrule` tinyint(1) NOT NULL DEFAULT 0;");
}

$this->endSetup();