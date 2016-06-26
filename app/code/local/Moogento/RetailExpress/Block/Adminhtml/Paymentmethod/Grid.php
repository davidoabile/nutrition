<?php
/**
 * Moogento_RetailExpress extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Moogento
 * @package        Moogento_RetailExpress
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * RetailExpress Payment method admin grid block
 *
 * @category    Moogento
 * @package     Moogento_RetailExpress
 * @author      Ultimate Module Creator
 */
class Moogento_RetailExpress_Block_Adminhtml_Paymentmethod_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('paymentmethodGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return Moogento_RetailExpress_Block_Adminhtml_Paymentmethod_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('moogento_retailexpress/paymentmethod')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return Moogento_RetailExpress_Block_Adminhtml_Paymentmethod_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                'header' => Mage::helper('moogento_retailexpress')->__('Id'),
                'index'  => 'entity_id',
                'type'   => 'number'
            )
        );
        $this->addColumn(
            'name',
            array(
                'header'    => Mage::helper('moogento_retailexpress')->__('Name'),
                'align'     => 'left',
                'index'     => 'name',
            )
        );
        
        $this->addColumn(
            'status',
            array(
                'header'  => Mage::helper('moogento_retailexpress')->__('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'options' => array(
                    '1' => Mage::helper('moogento_retailexpress')->__('Enabled'),
                    '0' => Mage::helper('moogento_retailexpress')->__('Disabled'),
                )
            )
        );
        $this->addColumn(
            'retail_express_id',
            array(
                'header' => Mage::helper('moogento_retailexpress')->__('Retail express ID'),
                'index'  => 'retail_express_id',
                'type'=> 'number',

            )
        );
        $this->addColumn(
            'magento_payment',
            array(
                'header' => Mage::helper('moogento_retailexpress')->__('Magento payment method'),
                'index'  => 'magento_payment',
                'type'    => 'options',
                'options'    => Mage::helper('moogento_retailexpress')->getPaymentArray(),
            )
        );

        $this->addColumn(
            'loyalty_enabled',
            array(
                'header' => Mage::helper('moogento_retailexpress')->__('Loyalty Enabled'),
                'index'  => 'loyalty_enabled',
                'type'    => 'options',
                    'options'    => array(
                    '1' => Mage::helper('moogento_retailexpress')->__('Yes'),
                    '0' => Mage::helper('moogento_retailexpress')->__('No'),
                )

            )
        );
        $this->addColumn(
            'pos_enabled',
            array(
                'header' => Mage::helper('moogento_retailexpress')->__('POS enabled'),
                'index'  => 'pos_enabled',
                'type'    => 'options',
                    'options'    => array(
                    '1' => Mage::helper('moogento_retailexpress')->__('Yes'),
                    '0' => Mage::helper('moogento_retailexpress')->__('No'),
                )

            )
        );
        $this->addColumn(
            'loyalty_ratio',
            array(
                'header' => Mage::helper('moogento_retailexpress')->__('Loyalty Ratio'),
                'index'  => 'loyalty_ratio',
                'type'=> 'number',

            )
        );
        $this->addColumn(
            'action',
            array(
                'header'  =>  Mage::helper('moogento_retailexpress')->__('Action'),
                'width'   => '100',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('moogento_retailexpress')->__('Edit'),
                        'url'     => array('base'=> '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'is_system' => true,
                'sortable'  => false,
            )
        );
        $this->addExportType('*/*/exportCsv', Mage::helper('moogento_retailexpress')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('moogento_retailexpress')->__('Excel'));
        $this->addExportType('*/*/exportXml', Mage::helper('moogento_retailexpress')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * prepare mass action
     *
     * @access protected
     * @return Moogento_RetailExpress_Block_Adminhtml_Paymentmethod_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('paymentmethod');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'=> Mage::helper('moogento_retailexpress')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('moogento_retailexpress')->__('Are you sure?')
            )
        );
        $this->getMassactionBlock()->addItem(
            'status',
            array(
                'label'      => Mage::helper('moogento_retailexpress')->__('Change status'),
                'url'        => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                    'status' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('moogento_retailexpress')->__('Status'),
                        'values' => array(
                            '1' => Mage::helper('moogento_retailexpress')->__('Enabled'),
                            '0' => Mage::helper('moogento_retailexpress')->__('Disabled'),
                        )
                    )
                )
            )
        );
        $this->getMassactionBlock()->addItem(
            'loyalty_enabled',
            array(
                'label'      => Mage::helper('moogento_retailexpress')->__('Change Loyalty Enabled'),
                'url'        => $this->getUrl('*/*/massLoyaltyEnabled', array('_current'=>true)),
                'additional' => array(
                    'flag_loyalty_enabled' => array(
                        'name'   => 'flag_loyalty_enabled',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('moogento_retailexpress')->__('Loyalty Enabled'),
                        'values' => array(
                                '1' => Mage::helper('moogento_retailexpress')->__('Yes'),
                                '0' => Mage::helper('moogento_retailexpress')->__('No'),
                            )

                    )
                )
            )
        );
        $this->getMassactionBlock()->addItem(
            'pos_enabled',
            array(
                'label'      => Mage::helper('moogento_retailexpress')->__('Change POS enabled'),
                'url'        => $this->getUrl('*/*/massPosEnabled', array('_current'=>true)),
                'additional' => array(
                    'flag_pos_enabled' => array(
                        'name'   => 'flag_pos_enabled',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('moogento_retailexpress')->__('POS enabled'),
                        'values' => array(
                                '1' => Mage::helper('moogento_retailexpress')->__('Yes'),
                                '0' => Mage::helper('moogento_retailexpress')->__('No'),
                            )

                    )
                )
            )
        );
        return $this;
    }

    /**
     * get the row url
     *
     * @access public
     * @param Moogento_RetailExpress_Model_Paymentmethod
     * @return string
     * @author Ultimate Module Creator
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * get the grid url
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * after collection load
     *
     * @access protected
     * @return Moogento_RetailExpress_Block_Adminhtml_Paymentmethod_Grid
     * @author Ultimate Module Creator
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
}
