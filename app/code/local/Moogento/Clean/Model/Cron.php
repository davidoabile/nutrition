<?php


class Moogento_Clean_Model_Cron
{
    public function update()
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates');
        $query = "SELECT id, `date` FROM $aggregatesTable WHERE is_dirty = 1 ORDER BY date DESC LIMIT 50";

        $baseSelect = $this->_prepareUpdateSelect('%Y-%m-%d');
        $offset = Mage::getModel('core/date')->getGmtOffset("hours");

        foreach ($adapter->fetchAll($query) as $row) {
            $select = clone $baseSelect;
            $select->where('created_at + INTERVAL ' . $offset . ' HOUR BETWEEN "' . $row['date'] . ' 00:00:00" AND "' . $row['date'] .' 23:59:59"');

            $updateQuery = "INSERT INTO $aggregatesTable (date, store_id, orders_total, orders_number, orders_revenue, orders_tax, orders_shipping, orders_qty, orders_average_product_price, orders_average_product_number) " . $select .
                           " ON DUPLICATE KEY UPDATE orders_total=VALUES(orders_total), orders_number=VALUES(orders_number), orders_revenue=VALUES(orders_revenue), orders_tax=VALUES(orders_tax),
                           orders_shipping=VALUES(orders_shipping), orders_qty=VALUES(orders_qty),
                           orders_average_product_price=VALUES(orders_average_product_price), orders_average_product_number=VALUES(orders_average_product_number)";

            $adapter->query($updateQuery);

            $adapter->query("UPDATE $aggregatesTable SET is_dirty = 0 WHERE id = " . $row['id']);
        }
    }

    public function updateDay()
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_day');
        $query = "SELECT id, `date` FROM $aggregatesTable WHERE is_dirty = 1 ORDER BY date DESC LIMIT 50";

        $baseSelect = $this->_prepareUpdateSelect('%Y-%m-%d %H:00:00');
        $offset = Mage::getModel('core/date')->getGmtOffset("hours");

        foreach ($adapter->fetchAll($query) as $row) {
            $select = clone $baseSelect;
            $select->where('created_at + INTERVAL ' . $offset . ' HOUR BETWEEN "' . $row['date'] . '" AND "' . $row['date'] .'" + INTERVAL 1 HOUR');

            $updateQuery = "INSERT INTO $aggregatesTable (date, store_id, orders_total, orders_number, orders_revenue, orders_tax, orders_shipping, orders_qty, orders_average_product_price, orders_average_product_number) " . $select .
                           " ON DUPLICATE KEY UPDATE orders_total=VALUES(orders_total), orders_number=VALUES(orders_number), orders_revenue=VALUES(orders_revenue), orders_tax=VALUES(orders_tax),
                           orders_shipping=VALUES(orders_shipping), orders_qty=VALUES(orders_qty),
                           orders_average_product_price=VALUES(orders_average_product_price), orders_average_product_number=VALUES(orders_average_product_number)";

            $adapter->query($updateQuery);

            $adapter->query("UPDATE $aggregatesTable SET is_dirty = 0 WHERE id = " . $row['id']);
        }
    }

    public function updateBestsellers()
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_bestsellers');
        $salesOrderItem   = Mage::getSingleton('core/resource')->getTableName('sales/order_item');
        $salesOrder   = Mage::getSingleton('core/resource')->getTableName('sales/order');
        $offset = Mage::getModel('core/date')->getGmtOffset("hours");

        $query = "SELECT id, `date`, sku FROM $aggregatesTable WHERE is_dirty = 1 ORDER BY date DESC LIMIT 50";

        $baseSelect = "SELECT DATE_FORMAT(i.created_at + INTERVAL $offset HOUR, '%Y-%m-%d'), i.store_id, i.sku, IF(i.parent_item_id IS NULL AND i.base_price = 0, (i.qty_ordered - i.qty_canceled) * pi.base_price, (i.qty_ordered - i.qty_canceled) * i.base_price)
            FROM $salesOrderItem i
            JOIN $salesOrder o on i.order_id = o.entity_id
            LEFT JOIN $salesOrderItem pi on i.parent_item_id = pi.item_id
            WHERE 1=1";
        $statuses = explode(',', Mage::getStoreConfig('moogento_clean/dashboard/chart_statuses'));
        if ($statuses) {
            $baseSelect .= ' AND o.status in ("' . implode('","', $statuses) . '")';
        }

        foreach ($adapter->fetchAll($query) as $row) {
            $select = $baseSelect . " AND i.sku = '{$row['sku']}' GROUP BY DATE_FORMAT(i.created_at + INTERVAL $offset HOUR, '%Y-%m-%d'), store_id, sku";
            $updateQuery = "INSERT INTO $aggregatesTable (date, store_id, sku, amount) " . $select .
                           " ON DUPLICATE KEY UPDATE amount=VALUES(amount)";
            $adapter->query($updateQuery);

            $adapter->query("UPDATE $aggregatesTable SET is_dirty = 0 WHERE id = " . $row['id']);
        }
    }

    protected function _prepareUpdateSelect($dateFormat)
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
        $offset = Mage::getModel('core/date')->getGmtOffset("hours");

        $columns = array(
            new Zend_Db_Expr('DATE_FORMAT(created_at + INTERVAL ' . $offset . ' HOUR, "' . $dateFormat . '")'),
            'store_id',
            new Zend_Db_Expr(
                sprintf('SUM((%s - %s) * %s)',
                    $this->getIfNullSql('o.base_grand_total', 0),
                    $this->getIfNullSql('o.base_total_canceled',0),
                    $this->getIfNullSql('o.base_to_global_rate',0)
                )
            ),
            new Zend_Db_Expr('COUNT(o.entity_id)'),
            new Zend_Db_Expr(
                sprintf('SUM((%s - %s - %s - (%s - %s - %s)) * %s)',
                    $this->getIfNullSql('o.base_total_invoiced', 0),
                    $this->getIfNullSql('o.base_tax_invoiced', 0),
                    $this->getIfNullSql('o.base_shipping_invoiced', 0),
                    $this->getIfNullSql('o.base_total_refunded', 0),
                    $this->getIfNullSql('o.base_tax_refunded', 0),
                    $this->getIfNullSql('o.base_shipping_refunded', 0),
                    $this->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            new Zend_Db_Expr(
                sprintf('SUM((%s - %s) * %s)',
                    $this->getIfNullSql('o.base_tax_amount', 0),
                    $this->getIfNullSql('o.base_tax_canceled', 0),
                    $this->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            new Zend_Db_Expr(
                sprintf('SUM((%s - %s) * %s)',
                    $this->getIfNullSql('o.base_shipping_amount', 0),
                    $this->getIfNullSql('o.base_shipping_canceled', 0),
                    $this->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            new Zend_Db_Expr('SUM(oi.total_qty_ordered)'),
            new Zend_Db_Expr('SUM(oi.total_product_price)/SUM(oi.items_count)'),
            new Zend_Db_Expr('SUM(oi.items_count)/COUNT(o.entity_id)'),
        );

        $select          = $adapter->select();
        $selectOrderItem = $adapter->select();

        $qtyCanceledExpr = $this->getIfNullSql('qty_canceled', 0);
        $cols            = array(
            'order_id'           => 'order_id',
            'total_qty_ordered'  => new Zend_Db_Expr("SUM(qty_ordered - {$qtyCanceledExpr})"),
            'total_product_price'  => new Zend_Db_Expr("SUM(base_price)"),
            'items_count'  => new Zend_Db_Expr("COUNT(1)"),
        );
        $selectOrderItem->from(Mage::getSingleton('core/resource')->getTableName('sales/order_item'), $cols)
                        ->where('parent_item_id IS NULL')
                        ->group('order_id');

        $select->from(array('o' => Mage::getSingleton('core/resource')->getTableName('sales/order')), $columns)
               ->join(array('oi' => $selectOrderItem), 'oi.order_id = o.entity_id', array());

        $statuses = explode(',', Mage::getStoreConfig('moogento_clean/dashboard/chart_statuses'));
        if ($statuses) {
            $select->where('o.status in ("' . implode('","', $statuses) . '")');
        }

        $select->group(array(
            'DATE_FORMAT(created_at + INTERVAL ' . $offset . ' HOUR, "' . $dateFormat . '")',
            'o.store_id',
        ));

        return $select;
    }

    public function updateVisitors()
    {
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));

        $this->_updateVisitorsForHour($date);

        $date->modify('-1hour');

        $this->_updateVisitorsForHour($date);
    }

    protected function _updateVisitorsForHour($date)
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $urlTable   = Mage::getSingleton('core/resource')->getTableName('log/url_table');
        $visitorInfoTable   = Mage::getSingleton('core/resource')->getTableName('log/visitor_info');
        $aggregatesTableVisitors   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_visitors');
        $offset = Mage::getModel('core/date')->getGmtOffset("hours");

        $query = "
            SELECT DISTINCT url.visitor_id, visitor_info.http_user_agent
            FROM $urlTable as url
            JOIN $visitorInfoTable as visitor_info 
                USING(visitor_id)
            WHERE visit_time + INTERVAL $offset HOUR BETWEEN '{$date->format('Y-m-d H:00:00')}' AND '{$date->format('Y-m-d H:59:59')}'
        ";

        $visitors = $adapter->fetchAll($query);

        $array_for_check = array('bot','crawler','feed','tinfoil','shoppimon','favicon','java','nominet','github','hubspot','python','php','paypal','ltx71','MetaURI');
        foreach($visitors as $key => $visitor){
            $flag = false;
            foreach($array_for_check as $val){
                if(stripos($visitor['http_user_agent'], $val) !== false) $flag = true;
            }
            if($flag){
                unset($visitors[$key]);
            }
        }
        $visitors_count = count($visitors);

        if ($visitors) {
            $adapter->query("INSERT INTO {$aggregatesTableVisitors} (date, visitors) values('{$date->format('Y-m-d H:00:00')}', $visitors_count) ON DUPLICATE KEY UPDATE visitors = VALUES(visitors)");
        }
    }
    
    public function updateQuote()
    {
        if($quote_count = Mage::getStoreConfig('moogento_clean/dashboard/quote_count')){
            Mage::getConfig()->saveConfig('moogento_clean/dashboard/quote_today', rand(0,$quote_count-1));
        }
    }

    public function getIfNullSql($expression, $value = 0)
    {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IFNULL((%s), %s)", $expression, $value);
        } else {
            $expression = sprintf("IFNULL(%s, %s)", $expression, $value);
        }

        return new Zend_Db_Expr($expression);
    }
} 