<?php
/**
 * Created by PhpStorm.
 * User: werewolf
 * Date: 19.08.14
 * Time: 22:42
 */

class Moogento_PickNScan_Model_Observer
{
    public function moogento_core_sales_order_prepare($event)
    {
        if (!Mage::helper('moogento_pickscan')->isAvailable()) return;

        $block = $event->getBlock();


        if (Mage::getStoreConfigFlag('moogento_pickscan/settings/show_button')) {
            $adminSession = Mage::getSingleton('admin/session');
            
			if ($adminSession->isAllowed('moogento/pickscan/pick')) {
                $block->addButton(
                    'pickscan',
                    array(
                        'label' => Mage::helper('moogento_pickscan')->__('Pick: Manual'),
                        'onclick' => 'clickButtonPickScan()',
                        'class' => 'scan'
                    )
                );
            }
			
			if ($adminSession->isAllowed('moogento/pickscan/quickpick')) {
                $block->addButton(
                    'quickpick',
                    array(
                        'label' => Mage::helper('moogento_pickscan')->__('Pick: Assigned'),
                        'onclick' => "clickButtonQuickPick()",
                        'class' => 'scan'
                    )
                );
            }

            if ($adminSession->isAllowed('moogento/pickscan/pack')) {
                $block->addButton(
                    'pack',
                    array(
                        'label' => Mage::helper('moogento_pickscan')->__('Pack'),
                        'onclick' => "setLocation('" . $block->getUrl('*/sales_order_pickscan/pack') . "')",
                        'class' => 'scan'
                    )
                );
            }
        }
    }

    public function moogento_core_order_grid_actions($observer)
    {
        if (!Mage::helper('moogento_pickscan')->isAvailable()) return;

        $grid = $observer->getEvent()->getGrid();

        $users = array();
        foreach (Mage::getModel('admin/user')->getCollection() as $user) {
            $users[] = array(
                'value' => $user->getUserId(),
                'label' => $user->getFirstname() . ' ' . $user->getLastname(),
            );
        }


        $grid->getMassactionBlock()->addItem('assign_orders', array(
            'label' => "Pick'n'Scan: " . Mage::helper('moogento_pickscan')->__('Assign Order(s) to User'),
            'url' => $grid->getUrl('*/sales_order_pickscan/assign'),
            'additional' => array(
                'user' => array(
                    'name' => 'user_id',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('catalog')->__('User'),
                    'values' => $users,
                )
            )
            ));
        $grid->getMassactionBlock()->addItem('clear_assign_orders', array(
            'label' => "Pick'n'Scan: " . Mage::helper('moogento_pickscan')->__('Clear Pick\'n\'Scan Status'),
            'url' => $grid->getUrl('*/sales_order_pickscan/unassign'),
        ));
    }

    public function moogento_core_order_grid_columns($observer)
    {
        /** @var Moogento_Core_Block_Adminhtml_Sales_Order_Grid $grid */
        $grid = $observer->getEvent()->getGrid();

        $grid->addCustomColumn('moogento_pickscan', array(
            'header' => Mage::helper('moogento_pickscan')->__("Pick'n'Scan"),
            'index' => 'results',
            'filter_index' => 'pick.results',
            'type' => 'text',
            'renderer' => 'moogento_pickscan/adminhtml_widget_grid_column_renderer_pickscan',
            'filter_condition_callback' => 'Moogento_PickNScan_Helper_Data::setPickscanFilter',
        ));
        $grid->addCustomColumn('moogento_pickscan_pick', array(
            'header' => Mage::helper('moogento_pickscan')->__('Pick'),
            'index' => 'moogento_pickscan_pick',
            'type' => 'text',
            'renderer' => 'moogento_pickscan/adminhtml_widget_grid_column_renderer_pick',
            'filter' => 'moogento_pickscan/adminhtml_widget_grid_column_filter_pick',
            'filter_condition_callback' => 'Moogento_PickNScan_Helper_Data::setPickFilter',
            'filter_index' => 'pick.status',
            'column_css_class' => 'a-center',
        ));
        $grid->addCustomColumn('moogento_pickscan_assigned', array(
            'header' => Mage::helper('moogento_pickscan')->__('Assigned'),
            'index' => 'assigned_to',
            'type' => 'options',
            'options' => Mage::helper('moogento_pickscan')->getUsers(),
            'filter_index' => 'pick.user_id',
            'column_css_class' => '',
        ));
    }

    public function moogento_admin_create_options_startup_page($event)
    {
        $options = $event->getMenu()->getOptions();

        $nodeAssigned = array(
            'label'      => 'PickNScan: pickAssigned',
            'uri'        => 'sales_order_pickscan/index/quickpick/1',
        );

        $nodeAssigned = Moogento_PowerLogin_Model_System_Admin_Startup_Page::prepareNode($nodeAssigned);

        $nodeManual = array(
            'label'      => 'PickNScan: pickManual',
            'uri'        => 'sales_order_pickscan/index',
        );

        $nodeManual = Moogento_PowerLogin_Model_System_Admin_Startup_Page::prepareNode($nodeManual);

        $nodePack = array(
            'label'      => 'PickNScan: pack',
            'uri'        => 'sales_order_pickscan/pack',
        );

        $nodePack = Moogento_PowerLogin_Model_System_Admin_Startup_Page::prepareNode($nodePack);

        array_splice( $options, 1, 0, array($nodeAssigned, $nodeManual, $nodePack));

        $event->getMenu()->setOptions($options);
    }


    public function aggregate()
    {
        Mage::getModel('moogento_pickscan/picking_aggregated')->aggregate();
    }

    public function moogento_core_order_grid_collection_prepare($event)
    {
        $event->getCollection()->getSelect()->joinLeft(
            array('pick' => Mage::getSingleton('core/resource')->getTableName('moogento_pickscan/picking')),
            'pick.entity_id = main_table.entity_id',
            array('results', 'pick_status' => 'status', 'assigned_to' => 'user_id')
        );
    }

    public function moogento_core_order_grid_columns_prepare($observer)
    {
        $columnId = $observer->getEvent()->getColumnId();
        /** @var Varien_Object $data */
        $data = $observer->getEvent()->getDataObject();

        if ($columnId == 'status') {
            $data->setData('filter_index', 'main_table.status');
        }
    }
    
} 