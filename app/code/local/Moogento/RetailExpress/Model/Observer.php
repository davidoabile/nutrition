<?php


class Moogento_RetailExpress_Model_Observer
{
    public function moogento_core_order_grid_columns($observer)
    {
        /** @var Moogento_Core_Block_Adminhtml_Sales_Order_Grid $grid */
        $grid = $observer->getEvent()->getGrid();

        $grid->addCustomColumn('retail_express_id', array(
            'header' => Mage::helper('moogento_retailexpress')->__('Retail Express ID'),
            'index' => 'retail_express_id',
            'renderer' => 'moogento_retailexpress/adminhtml_column_renderer_id',
        ));
        $grid->addCustomColumn('retail_express_status', array(
            'header'  => Mage::helper('moogento_retailexpress')->__('Retail Express Status'),
            'index'   => 'retail_express_status',
            'type'    => 'options',
            'options' => Moogento_RetailExpress_Model_Retailexpress_Status::toOptionArray(),
            'renderer' => 'moogento_retailexpress/adminhtml_column_renderer_status',
            'filter' => 'moogento_retailexpress/adminhtml_column_filter_status',
            'column_css_class' => 'a-center',
        ));
    }


    public function core_block_abstract_to_html_after($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View_Info) {
            $transport = $observer->getEvent()->getTransport();
            $html = $transport->getHtml();
            $retailBlock = Mage::app()->getLayout()->createBlock('moogento_retailexpress/adminhtml_order_detail');
            $retailBlock->setOrder($block->getOrder());
            $html .= $retailBlock->toHtml();
            $transport->setHtml($html);
        }
    }

    public function controller_action_predispatch_adminhtml_sales_order_index($observer)
    {
        if (Mage::getStoreConfigFlag('moogento_retailexpress/general/show_fail_message')) {
            Mage::getSingleton('adminhtml/session')->addError('Automatic export of orders to retailExpress was disabled due to many errors during one run');
        }
    }
} 