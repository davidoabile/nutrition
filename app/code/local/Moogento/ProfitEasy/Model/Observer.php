<?php

class Moogento_ProfitEasy_Model_Observer
{

	public function moogento_core_order_grid_columns($observer)
	{
        /** @var Moogento_Core_Block_Adminhtml_Sales_Order_Grid $grid */
        $grid = $observer->getEvent()->getGrid();

        $grid->addCustomColumn('order_profit', array(
            'header'  => Mage::helper('moogento_profiteasy')->__('Profit'),
            'index'   => 'profit_amount',
            'type'  	=> 'currency',
            'currency' 	=> 'base_currency_code',
            'frame_callback' => array($this, 'decorateStatus'),
            'after' => 'grand_total',
        ));

        $grid->addCustomColumn('shipping_costs', array(
            'header'  => Mage::helper('moogento_profiteasy')->__('$ ship'),
            'index'   => 'shipping_costs',
            'type'  	=> 'currency',
            'currency' 	=> 'base_currency_code',
            'update_url' => Mage::app()->getStore()->getUrl('*/order_costs/shipping'),
            'renderer' => 'moogento_profiteasy/adminhtml_sales_order_grid_column_renderer_shipping_costs',
            'filter_condition_callback' => 'Moogento_ProfitEasy_Helper_Data::setShippingCostsFilter',
        ));
	}

    public function moogento_core_order_grid_html_additional($observer)
    {
        $additional = $observer->getEvent()->getAdditional();

        $html = $additional->getHtml();
        $html .= '<script>';
        $html .= <<<HEREDOC
        jQuery(function($){
             $('#anchor-content').on('keypress', '.order-shipping-costs', function(e){
                 if (e.which == 13) {
                     var el = $(this), td = el.parents('td');
                     showSpinner(td);

                    jQuery.ajax({
                        type: 'POST',
                        dataType: 'json',
                        data: {order_id: el.data('id'),shipping_amount: el.val(),form_key: FORM_KEY},
                        url: el.data('url'),
                        success: function(response) {
                            hideSpinner(td);
                            var bg = td.css('backgroundColor');
                            td.animate({
                                backgroundColor: "green"
                            }, 1000, function() {
                                td.animate({
                                    backgroundColor: bg
                                });
                            });
                        },
                        failure: function() {
                                 hideSpinner(td);
                                 alert('Something errored out...');
                             }
                    });
                 }
             })
        });
HEREDOC;
        $html .= '</script>';

        $additional->setHtml($html);
    }

    public function moogento_core_order_grid_collection_prepare($observer)
    {
        $collection = $observer->getEvent()->getCollection();
        if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/shipping_costs_show')) {
            $collection->addExpressionFieldToSelect('shipping_costs', 'IFNULL(shipping_cost, base_shipping_amount)', array());
        }
    }

	public function decorateStatus($value, $row, $column, $isExport)
    {
        $profit = $row->getProfitAmount();
        if ($profit > 0) {
            $class = 'grid-severity-notice';
        } elseif ($profit == 0) {
            $class = 'grid-severity-minor';
        } else {
            $class = 'grid-severity-critical';
        }
		
		$profit_calculated = '';
		if($row->getData('profit_calculated') == 0) {
			$profit_calculated = '<i class="fa fa-clock-o" title="Waiting for profitEasy processing..." style="cursor:help;"></i> &nbsp;';
		}
			
        return '<span class="' . $class . '" style="font-weight:100;letter-spacing:0.05em;text-shadow:0px 0px 1px #222;"><span>' . $profit_calculated . $value . '</span></span>';
    }

    public function coreBlockAbstractPrepareLayoutAfter(Varien_Event_Observer $observer)
    {
        if (Mage::app()->getFrontController()->getAction()->getFullActionName() === 'adminhtml_dashboard_index')
        {
            $block = $observer->getBlock();
            if ($block->getNameInLayout() === 'dashboard')
            {
                $block->getChild('topSearches')->setUseAsDashboardHook(true);
            }
        }
    }

    public function core_block_abstract_to_html_after(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfigFlag('moogento_profiteasy/dashboard/show_panel') && Mage::app()->getFrontController()->getAction()->getFullActionName() === 'adminhtml_dashboard_index')
        {
            if ($observer->getBlock()->getUseAsDashboardHook())
            {
                $html = $observer->getTransport()->getHtml();
                $profit_block = $observer->getBlock()->getLayout()->createBlock('moogento_profiteasy/adminhtml_dashboard_profit');
                $html .= $profit_block->toHtml();
                $observer->getTransport()->setHtml($html);
            }
        }
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View_Info) {
            $transport = $observer->getEvent()->getTransport();
            $html = $transport->getHtml();
            $additionalBlock = Mage::app()->getLayout()->createBlock('adminhtml/template');
            $additionalBlock->setTemplate('moogento/profiteasy/profit.phtml');
            $additionalBlock->setParentBlock($block);
            $additionalBlock->setOrder($block->getOrder());
            $html .= $additionalBlock->toHtml();
            $transport->setHtml($html);
        }
    }

    public function controller_action_predispatch_adminhtml_system_config_save($observer)
    {
        $request = Mage::app()->getRequest();
        $section = $request->getParam('section');

        switch ($section) {
            case 'moogento_profiteasy':
                $post_data = $request->getPost('profiteasy_costs', array());

                $ids = array(0);
                foreach ($post_data as $id => $data) {
                    $model = Mage::getModel('moogento_profiteasy/costs')->load($id);
                    $model->addData($data);
                    $model->save();
                    $ids[] = $model->getId();
                }
                $write = Mage::getSingleton('core/resource')->getConnection('core_write');

                $costsTable = Mage::getSingleton('core/resource')->getTableName('moogento_profiteasy/costs');
                $orderTable = Mage::getSingleton('core/resource')->getTableName('sales/order');
                $orderGridTable = Mage::getSingleton('core/resource')->getTableName('sales/order_grid');

                $query = "DELETE FROM {$costsTable} WHERE id not in (" . implode(',', $ids) . ")";
                $write->query($query);

                $query = "UPDATE {$orderTable} SET profit_calculated = 0";
                $write->query($query);
                $query = "UPDATE {$orderGridTable} SET profit_calculated = 0";
                $write->query($query);
                break;
        }
    }

    public function moogento_shipeasy_import_csv($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $importData = $observer->getEvent()->getImportData();
        $result = $observer->getEvent()->getResult();

        $actualShipping = Mage::getStoreConfig('moogento_profiteasy/csv_import/actual_shipping_field');
        if (!$actualShipping) {
            $actualShipping = 'actual_shipping_cost';
        }
        if (isset($importData[$actualShipping]) && $importData[$actualShipping]) {
            $order->setShippingCost((float)$importData[$actualShipping]);
            $order->setData('profit_calculated', 0);
            $result->setProcessed(true);
        }

        $additionalCosts = Mage::getStoreConfig('moogento_profiteasy/csv_import/additional_costs_field');
        if (!$additionalCosts) {
            $additionalCosts = 'additional_costs';
        }
        if (isset($importData[$additionalCosts]) && $importData[$additionalCosts]) {
            $additionalCostsLabel = Mage::getStoreConfig('moogento_profiteasy/csv_import/additional_costs_label_field');
            if (!$additionalCostsLabel) {
                $additionalCostsLabel = 'additional_costs_label';
            }
            $label = isset($importData[$additionalCostsLabel]) ? $importData[$additionalCostsLabel] : '';

            $addAsNewLine = Mage::getStoreConfig('moogento_profiteasy/csv_import/additional_costs_new_line_field');
            if (!$addAsNewLine) {
                $addAsNewLine = 'additional_costs_new_line';
            }
            $newLine = isset($importData[$addAsNewLine]) ? (bool)$importData[$addAsNewLine] : !$label;
            $model = Mage::getModel('moogento_profiteasy/costs_order');
            if (!$newLine) {
                $collection = Mage::getResourceModel('moogento_profiteasy/costs_order_collection')
                    ->addFieldToFilter('label', $label)
                    ->addFieldToFilter('order_id', $order->getId());

                $model = $collection->getFirstItem();
            }

            $model->setOrderId($order->getId());
            $model->setLabel($label);
            $model->setCost((float)$importData[$additionalCosts]);

            $calculationField = Mage::getStoreConfig('moogento_profiteasy/csv_import/additional_costs_calculation_field');
            if (!$calculationField) {
                $calculationField = 'additional_costs_calculation';
            }
            $calculation = Moogento_ProfitEasy_Helper_Data::CALCULATE_FIXED;
            if (isset($importData[$calculationField])) {
                $calculation = trim($importData[$calculationField]);
                if ($calculation != Moogento_ProfitEasy_Helper_Data::CALCULATE_FIXED && $calculation != Moogento_ProfitEasy_Helper_Data::CALCULATE_PERCENT) {
                    $calculation = Moogento_ProfitEasy_Helper_Data::CALCULATE_FIXED;
                }
            }
            $model->setCalculationType($calculation);

            $overrideField = Mage::getStoreConfig('moogento_profiteasy/csv_import/additional_costs_override_field');
            if (!$overrideField) {
                $overrideField = 'additional_costs_override';
            }
            if (isset($importData[$overrideField])) {
                $collection = Mage::getResourceModel('moogento_profiteasy/costs_collection');
                $collection->addFieldToFilter('label', trim($importData[$overrideField]));
                $rule = $collection->getFirstItem();
                if ($rule->getId()) {
                    $model->setRuleId($rule->getId());
                } else {
                    $model->setRuleId(null);
                }
            } else {
                $model->setRuleId(null);
            }

            try {
                $model->save();
                $order->setData('profit_calculated', 0);
                $result->setProcessed(true);
            } catch (Exception $e) {
                $result->setError($e->getMessage());
            }
        }
    }
}