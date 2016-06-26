<?php

class Wyomind_Ordersexporttool_Model_Observer {

    public function scheduledGenerateFiles($schedule) {

        $errors = array();
        $log = array();
        $log[] = "-------------------- CRON PROCESS --------------------";

        $collection = Mage::getModel('ordersexporttool/profiles')->getCollection();
        $cnt = 0;

        foreach ($collection as $profile) {

            try {

                $log[] = "--> Running profile : " . $profile->getFileName() . ' [#' . $profile->getFileId() . '] <--';


                $cron['curent']['localDate'] = Mage::getSingleton('core/date')->date('l Y-m-d H:i:s');
                $cron['curent']['gmtDate'] = Mage::getSingleton('core/date')->gmtDate('l Y-m-d H:i:s');
                $cron['curent']['localTime'] = Mage::getSingleton('core/date')->timestamp();
                $cron['curent']['gmtTime'] = Mage::getSingleton('core/date')->gmtTimestamp();


                $cron['file']['localDate'] = Mage::getSingleton('core/date')->date('l Y-m-d H:i:s', $profile->getFileUpdatedAt());
                $cron['file']['gmtDate'] = $profile->getFileUpdatedAt();
                $cron['file']['localTime'] = Mage::getSingleton('core/date')->timestamp($profile->getFileUpdatedAt());
                $cron['file']['gmtTime'] = strtotime($profile->getFileUpdatedAt());

                /* Magento getGmtOffset() is bugged and doesn't include daylight saving time, the following workaround is used */
// date_default_timezone_set(Mage::app()->getStore()->getConfig('general/locale/timezone'));
// $date = new DateTime();
//$cron['offset'] = $date->getOffset() / 3600;
                $cron['offset'] = Mage::getSingleton('core/date')->getGmtOffset("hours");



                $log[] = '   * Last update : ' . $cron['file']['gmtDate'] . " GMT / " . $cron['file']['localDate'] . ' GMT' . $cron['offset'];
                $log[] = '   * Current date : ' . $cron['curent']['gmtDate'] . " GMT / " . $cron['curent']['localDate'] . ' GMT' . $cron['offset'];


                $cronExpr = json_decode($profile->getFileScheduledTask());
                $i = 0;
                $done = false;

                foreach ($cronExpr->days as $d) {

                    foreach ($cronExpr->hours as $h) {
                        $time = explode(':', $h);
                        if (date('l', $cron['curent']['gmtTime']) == $d) {
                            $cron['tasks'][$i]['localTime'] = strtotime(Mage::getSingleton('core/date')->date('Y-m-d')) + ($time[0] * 60 * 60) + ($time[1] * 60);
                            $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                        } else {
                            $cron['tasks'][$i]['localTime'] = strtotime("last " . $d, $cron['curent']['localTime']) + ($time[0] * 60 * 60) + ($time[1] * 60);
                            $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                        }

                        if ($cron['tasks'][$i]['localTime'] >= $cron['file']['localTime'] && $cron['tasks'][$i]['localTime'] <= $cron['curent']['localTime'] && $done != true) {

                            $log[] = '   * Scheduled : ' . ($cron['tasks'][$i]['localDate'] . " GMT" . $cron['offset']);

                            if ($profile->generateFile()) {
                                $done = true;
                                $cnt++;
                                $log[] = '   * EXECUTED!';
                            }
                        }

                        $i++;
                    }
                }
            } catch (Exception $e) {
                $log[] = '   * ERROR! ' . ($e->getMessage());
            }
            if (!$done)
                $log[] = '   * SKIPPED!';
        }



        if (Mage::getStoreConfig("ordersexporttool/setting/enable_report")) {
            foreach (explode(',', Mage::getStoreConfig("ordersexporttool/setting/emails")) as $email) {
                try {
                    if ($cnt)
                        mail($email, Mage::getStoreConfig("ordersexporttool/setting/report_title"), "\n" . implode($log, "\n"));
                } catch (Exception $e) {
                    $log[] = '   * EMAIL ERROR! ' . ($e->getMessage());
                }
            }
        };
        if (isset($_GET['oet']))
            echo "<br/>" . implode($log, "<br/>");
        Mage::log("\n" . implode($log, "\n"), null, "OrdersExportTool-cron.log");
    }

    public function _filterFlags($collection, $column) {
        $columns = $this->_block->getColumns();

        $value = $columns['export_flag']->getFilter()->getValue();

        if (!$value) {

            $collection->addFieldToFilter('export_flag', array('eq' => '0'));

            return;
        }

        $collection->addFieldToFilter('export_flag', array('finset' => $value));
    }

    public function addColumnToSalesOrderGrid($observer) {

        $block = $observer->getEvent()->getBlock();
        $this->_block = $block;

        if (get_class($block) == Mage::getStoreConfig("ordersexporttool/system/grid")) {

            $profiles = Mage::getModel('ordersexporttool/profiles')->getCollection();

            $exportation[0] = Mage::helper('ordersexporttool')->__('Not exported');

            foreach ($profiles as $p) {

                $exportation[$p->getFileId()] = $p->getFileName();
            }


            if (version_compare(Mage::getVersion(), '1.3.0', '>')) {
                $block->addColumnAfter('export_flag', array(
                    'header' => Mage::helper('sales')->__('Exported to '),
                    'index' => 'export_flag',
                    'type' => 'options',
                    'width' => '300px',
                    'options' => $exportation,
                    "sortable" => false,
                    'renderer' => "Wyomind_Ordersexporttool_Block_Adminhtml_Renderer_Exportedto",
                    'filter_condition_callback' => array($this, '_filterFlags'),
                        ), 'status'
                );
            } else {
                $block->addColumn('export_flag', array(
                    'header' => Mage::helper('sales')->__('Exported to '),
                    'index' => 'export_flag',
                    'type' => 'options',
                    'width' => '300px',
                    'options' => $exportation,
                    "sortable" => false,
                    'renderer' => "Wyomind_Ordersexporttool_Block_Adminhtml_Renderer_Exportedto",
                    'filter_condition_callback' => array($this, '_filterFlags'),
                        ), 'status'
                );
            }
        } else if (get_class($block) == 'Mage_Adminhtml_Block_Sales_Order_View') {
            $order_ids = array(Mage::app()->getRequest()->getParam("order_id"));
            $block->addButton('export_tool', array(
                'label' => 'Export',
                'onclick' => 'setLocation(\'' . Mage::helper("adminhtml")->getUrl('*/profiles/export', array('order_ids' => serialize($order_ids), "profile_ids" => serialize(false))) . '\')',
                'class' => 'scalable go'
            ));
        }
        return $observer;
    }

    public function addMassAction($observer) {


        $block = $observer->getEvent()->getBlock();
        $this->_block = $block;
        if (get_class($block) == 'Mage_Adminhtml_Block_Widget_Grid_Massaction' && $block->getRequest()->getControllerName() == 'sales_order') {


            $block->addItem('ordersexporttool', array(
                'label' => Mage::helper('sales')->__('Export with OET'),
                'url' => $block->getUrl('*/profiles/export', array('order_ids' => serialize(false), "profile_ids" => serialize(false))),
            ));
        }
    }

    public function addAttributeToOrder($observer) {


        $observer->getOrderItem()->setData("export_to", $observer->getItem()->getData('export_to'));

        return $observer;
    }

    public function addAttributeToQuote($observer) {
        $profile_id = Mage::getModel("catalog/product")->load($observer->getProduct()->getId())->getExportTo();
        if($profile_id==null) $profile_id=0;
		$observer->getQuoteItem()->setData("export_to", $profile_id);
        return $observer;
    }

}
