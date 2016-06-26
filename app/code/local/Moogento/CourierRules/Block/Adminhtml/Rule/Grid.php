<?php


class Moogento_CourierRules_Block_Adminhtml_Rule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('moogento_courierrules/rule_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
                'header'=> 'name',
                'type'  => 'text',
                'index' => 'name',
            ));

        $this->addColumn('sort', array(
                'header'=> 'sort',
                'type'  => 'text',
                'index' => 'sort',
            ));

        $this->addColumn('scope', array(
                'header'=> 'scope',
                'type'  => 'text',
                'index' => 'scope',
            ));

        $this->addColumn('shipping_method', array(
                'header'=> 'shipping_method',
                'type'  => 'text',
                'index' => 'shipping_method',
            ));

        $this->addColumn('custom_shipping_method', array(
                'header'=> 'custom_shipping_method',
                'type'  => 'text',
                'renderer' => 'moogento_courierrules/adminhtml_column_renderer_custom_shipping',
                'index' => 'custom_shipping_method',
            ));



        $this->addColumn('shipping_zone', array(
                'header'=> 'shipping_zone',
                'index' => 'shipping_zone',
                'renderer' => 'moogento_courierrules/adminhtml_column_renderer_zone',
            ));

        $this->addColumn('min_weight', array(
                'header'=> 'min_weight',
                'type'  => 'text',
                'index' => 'min_weight',
            ));

        $this->addColumn('max_weight', array(
                'header'=> 'max_weight',
                'type'  => 'text',
                'index' => 'max_weight',
            ));

        $this->addColumn('min_amount', array(
            'header'=> 'min_amount',
            'type'  => 'text',
            'index' => 'min_amount',
        ));

        $this->addColumn('max_amount', array(
            'header'=> 'max_amount',
            'type'  => 'text',
            'index' => 'max_amount',
        ));

        if (Mage::getStoreConfigFlag('courierrules/settings/use_product_attribute')) {
            $this->addColumn('product_attribute', array(
                'header'=> 'product_attribute',
                'type'  => 'text',
                'index' => 'product_attribute',
                'renderer' => 'moogento_courierrules/adminhtml_column_renderer_attribute',
                'separator' => ','
            ));
        }

        $this->addColumn('courierrules_method', array(
                'header'=> 'courierrules_method',
                'type'  => 'text',
                'index' => 'courierrules_method',
            ));

        $this->addColumn('target_custom', array(
                'header'=> 'custom_courierrules_method',
                'type'  => 'text',
                'renderer' => 'moogento_courierrules/adminhtml_column_renderer_custom_rule',
                'index' => 'target_custom',
            ));

        if (Mage::getStoreConfigFlag('courierrules/settings/quantity_all_items')) {
            $this->addColumn('quantity_all_items', array(
                'header'=> 'quantity_all_items',
                'type'  => 'text',
                'index' => 'quantity_all_items',
                'separator' => ','
            ));
        }

        if (Mage::getStoreConfigFlag('courierrules/settings/quantity_free_discount_items')) {
            $this->addColumn('quantity_free_discount_items', array(
                'header'=> 'quantity_free_discount_items',
                'type'  => 'text',
                'index' => 'quantity_free_discount_items',
                'separator' => ','
            ));
        }

        if (Mage::getStoreConfigFlag('courierrules/settings/use_product_attribute')) {
            $this->addColumn('product_attribute', array(
                'header'=> 'product_attribute',
                'type'  => 'text',
                'index' => 'product_attribute',
                'separator' => ','
            ));
        }
        $this->addColumn('tracking_pool', array(
                'header'=> 'tracking_pool',
                'index' => 'tracking_id',
                'renderer' => 'moogento_courierrules/adminhtml_column_renderer_tracking',
            ));

        return parent::_prepareColumns();
    }

} 