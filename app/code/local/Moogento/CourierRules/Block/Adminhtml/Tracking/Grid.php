<?php


class Moogento_CourierRules_Block_Adminhtml_Tracking_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('moogento_courierrules/tracking_collection');
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

        $this->addColumn('codes', array(
            'header'=> 'codes',
            'type'  => 'text',
            'index' => 'codes',
            ));

        $this->addColumn('warn_low', array(
                'header'=> 'warn_low',
                'type'  => 'text',
                'index' => 'warn_low',
            ));

        return parent::_prepareColumns();
    }

} 