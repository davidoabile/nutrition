<?php

class Moogento_EasyCoupon_Block_Adminhtml_Shortlink_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('shortlink_grid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('moogento_easycoupon/shortlink_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('short_url', array(
            'header'   => Mage::helper('moogento_easycoupon')->__('Short URL'),
            'type'     => 'text',
            'index'    => 'shortlink',
            'renderer' => 'moogento_easycoupon/adminhtml_column_renderer_url_short',
        ));

        $this->addColumn('full_url', array(
            'header'   => Mage::helper('moogento_easycoupon')->__('Full URL'),
            'type'     => 'text',
            'sortable' => false,
            'filter'   => false,
            'renderer' => 'moogento_easycoupon/adminhtml_column_renderer_url_full',
        ));


        $this->addColumn('action',
            array(
                'header'    => Mage::helper('moogento_easycoupon')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'     => Mage::helper('moogento_easycoupon')->__('Delete'),
                        'url'         => array('base' => '*/easycoupon_shortlink/delete'),
                        'field'       => 'id',
                        'data-column' => 'action',
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('moogento_easycoupon')->__('Delete'),
            'url'   => $this->getUrl('*/easycoupon_shortlink/massDelete'),
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}