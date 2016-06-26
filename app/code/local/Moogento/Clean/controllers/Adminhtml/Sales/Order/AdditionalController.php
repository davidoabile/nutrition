<?php

class Moogento_Clean_Adminhtml_Sales_Order_AdditionalController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function checkAction()
    {
        $this->loadLayout(false);
        if (Mage::helper('moogento_clean')->isInstalled('Moogento_Core')) {
            $block = $this->getLayout()->createBlock('moogento_core/adminhtml_sales_order_grid');
        } else {
            $block = $this->getLayout()->createBlock('adminhtml/sales_order_grid');
        }
        $block->setTemplate('');
        $block->toHtml();

        $select = clone $block->getCollection()->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns(new Zend_Db_Expr('MAX(main_table.created_at)'));
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::ORDER);

        $maxDate = $select->query()->fetchColumn();

        $date = Mage::app()->getRequest()->getParam('date');
        $result = array();
        if ($date < $maxDate) {
            $result['need_update'] = 1;
        } else {
            $result['need_update'] = 0;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
} 