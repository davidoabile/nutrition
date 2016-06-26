<?php

$installer = $this;

$this->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order_grid')}` LIKE 'gift_message_id';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `gift_message_id` int(11) NULL;");
}
      
$installer->run(<<<SQL

    UPDATE
        {$this->getTable('sales/order_grid')}
    SET
        `gift_message_id` =
        (
            SELECT
                `gift_message_id`
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