<?php


class Moogento_CourierRules_Block_Adminhtml_Zone_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('moogento_courierrules/zone_collection');
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

        $this->addColumn('countries', array(
                'header' => 'countries',
                'index' => 'countries',
                'type'  => 'options',
                'renderer' => 'moogento_courierrules/adminhtml_column_renderer_array',
                'separator' => ','
            ));

        $this->addColumn('zip_codes', array(
                'header' => 'zip_codes',
                'index' => 'zip_codes',
                'type'  => 'options',
                'renderer' => 'moogento_courierrules/adminhtml_column_renderer_array',
                'separator' => ','
            ));

        return parent::_prepareColumns();
    }

} 