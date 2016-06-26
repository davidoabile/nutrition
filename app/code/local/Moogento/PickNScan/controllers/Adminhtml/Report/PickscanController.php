<?php
/**
 * Created by PhpStorm.
 * User: werewolf
 * Date: 17.08.14
 * Time: 21:15
 */

class Moogento_PickNScan_Adminhtml_Report_PickscanController extends Mage_Adminhtml_Controller_Report_Abstract
{
    protected function _isAllowed()
    {
        return true;
    }

    public function indexAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Picking'));

        $this->_initAction()
            ->_setActiveMenu('report/pickscan');

        $gridBlock = $this->getLayout()->getBlock('adminhtml_report_picking.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    public function _initReportAction($blocks)
    {
        if (!is_array($blocks)) {
            $blocks = array($blocks);
        }

        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
        $requestData = $this->_filterDates($requestData, array('from', 'to'));
        $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        $params = new Varien_Object();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));

        if ($params->getPeriodType()) {
            switch ($params->getPeriodType()) {
                case 'today':
                    $params->setTo($date->format('Y-m-d'));
                    $params->setFrom($date->format('Y-m-d'));
                    break;
                case 'yesterday':
                    $date = $date->modify('-1day');
                    $params->setTo($date->format('Y-m-d'));
                    $params->setFrom($date->format('Y-m-d'));
                    break;
                case '7days':
                    $params->setTo($date->format('Y-m-d'));
                    $date = $date->modify('-7days');
                    $params->setFrom($date->format('Y-m-d'));
                    break;
                case '30days':
                    $params->setTo($date->format('Y-m-d'));
                    $date = $date->modify('-30days');
                    $params->setFrom($date->format('Y-m-d'));
                    break;
            }
        }

        foreach ($blocks as $block) {
            if ($block) {
                $block->setPeriodType($params->getData('period_type'));
                $block->setFilterData($params);
            }
        }

        return $this;
    }
} 