<?php

class Moogento_PickNScan_Model_Resource_Picking_Aggregated extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('moogento_pickscan/picking_aggregated', 'id');
    }

    public function aggregate()
    {
        $this->_getWriteAdapter()->truncateTable($this->getMainTable());

        $sql = <<<HEREDOC

        INSERT INTO `{$this->getMainTable()}`
            SELECT
                NULL,
                DATE(p.finished) as period,
                o.`store_id`,
                p.`user_id`,
                count(1),
                sum(p.`items_count`),
                sum(p.`substituted_count`),
                sum(p.`ignored_count`),
                sum(UNIX_TIMESTAMP(finished) - UNIX_TIMESTAMP(started)),
                max(UNIX_TIMESTAMP(finished)) - min(UNIX_TIMESTAMP(started))
        FROM {$this->getTable('moogento_pickscan/picking')} p
            INNER JOIN {$this->getTable('sales/order')} o ON p.entity_id = o.entity_id
        WHERE finished is not null
        GROUP BY o.store_id, p.user_id, period
HEREDOC;

        $this->_getWriteAdapter()->query($sql);

        return $this;
    }
} 