<?php
/**
 * One Step Checkout Manager : One Step Checkout Manager (CFM Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckoutfields
 * @version      1.0.9 - 2.9.8
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
 
/* AITOC static rewrite inserts start */
/* $meta=%default,AdjustWare_Deliverydate,AdjustWare_Orderproducts% */
if(Mage::helper('core')->isModuleEnabled('AdjustWare_Orderproducts')){
    class Aitoc_Aitcheckoutfields_Block_Rewrite_AdminhtmlSalesOrderGrid_Aittmp extends AdjustWare_Orderproducts_Block_Rewrite_AdminSalesOrderGrid {} 
 }elseif(Mage::helper('core')->isModuleEnabled('AdjustWare_Deliverydate')){
    class Aitoc_Aitcheckoutfields_Block_Rewrite_AdminhtmlSalesOrderGrid_Aittmp extends AdjustWare_Deliverydate_Block_Rewrite_AdminhtmlSalesOrderGrid {} 
 }else{
    /* default extends start */
    class Aitoc_Aitcheckoutfields_Block_Rewrite_AdminhtmlSalesOrderGrid_Aittmp extends Mage_Adminhtml_Block_Sales_Order_Grid {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitcheckoutfields_Block_Rewrite_AdminhtmlSalesOrderGrid extends Aitoc_Aitcheckoutfields_Block_Rewrite_AdminhtmlSalesOrderGrid_Aittmp
{
    public function __construct()
    {
        parent::__construct();
        $attributeCollection = $this->getAttributeCollection(true);
        if(count($attributeCollection)>0)
        {
            $this->addExportType('aitcheckoutfields/index/exportexcelcfm',Mage::helper('aitcheckoutfields')->__('EXCEL checkoutfields'));
        }
    }
    protected function  getStoreId()
    {
        $filter   = $this->getParam($this->getVarNameFilter(), null);
        if(is_string($filter)) {
            $data = $this->helper('adminhtml')->prepareFilterString($filter);
            if(isset($data['store_id']))
            {
              return $data['store_id'];
            }
        }
        return -1;
    }
    protected function getAttributeCollection($bCheckCanExport=false)
    {
        $iStoreId = $this->getStoreId();
        $type='aitoc_checkout';
        $oResource = Mage::getResourceModel('eav/entity_attribute');
        $this->type=$type;
        $attributeCollection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter( Mage::getModel('eav/entity')->setType($type)->getTypeId() )
        ;
        if((Mage::registry('aitcheckoutfields_excel'))||($bCheckCanExport))
        {
            $attributeCollection->getSelect()->join(
                array('additional_table' => $oResource->getTable('catalog/eav_attribute')),
                'additional_table.attribute_id=main_table.attribute_id AND ait_in_excel=1'
            );
        }
        else
        {
            $attributeCollection->getSelect()->join(
                array('additional_table' => $oResource->getTable('catalog/eav_attribute')),
                'additional_table.attribute_id=main_table.attribute_id AND ait_filterable=1'
            );
        }
        
        if($iStoreId!=-1)
        {
            $sWhereScope = '(find_in_set("' . $iStoreId . '", main_table.note) OR main_table.note="")';
            $attributeCollection->getSelect()->where($sWhereScope);
        }
        return $attributeCollection;
    }
    
    public function setCollection($collection)
    {
        $attributeCollection = $this->getAttributeCollection();
        $joinTable = Mage::getSingleton('core/resource')->getTableName('aitoc_order_entity_custom');
        $select = $collection->getSelect();
        
        foreach ($attributeCollection->getItems() as $attr)
        {
            if(in_array($attr['frontend_input'],array('select','radio')))
            {
                $option_value_table = Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value');
                $select->joinLeft(
                    array('aitec'.$attr['attribute_id'].'val' => $joinTable),
                    "(main_table.entity_id = aitec{$attr['attribute_id']}val.entity_id AND aitec{$attr['attribute_id']}val.attribute_id = ".$attr['attribute_id'].")",
                    array('aitecval'.$attr['attribute_id']=>"aitec{$attr['attribute_id']}val.value")
                )                
                ->joinLeft(array('aitec'.$attr['attribute_id'] => $option_value_table),
                 "(aitec{$attr['attribute_id']}.option_id = aitec{$attr['attribute_id']}val.value AND aitec{$attr['attribute_id']}.store_id = 0)",
                 array('aitec'.$attr['attribute_id'].'.value' =>"aitec{$attr['attribute_id']}.value") 
                );
            }
            else
            {
                $select->joinLeft(
                    array('aitec'.$attr['attribute_id'].'val' => $joinTable),
                   "(main_table.entity_id = aitec{$attr['attribute_id']}val.entity_id AND aitec{$attr['attribute_id']}val.attribute_id = ".$attr['attribute_id'].")",
                    array('aitec'.$attr['attribute_id'].'val.value'=>"aitec{$attr['attribute_id']}val.value")
                );
            }
        }
        return parent::setCollection($collection);
    }

    protected function _prepareColumns()
    {
        $res = parent::_prepareColumns();
        $action = $this->_columns['action'];
        unset($this->_columns['action']);


        if(Mage::registry('aitcheckoutfields_excel'))
        {
            foreach($this->_columns as $k =>$v)
            {
                if($k!='real_order_id')
                {
                    unset($this->_columns[$k]);
                }
            }
        }


        $attributeCollection = $this->getAttributeCollection();
        $i = 0;
        $checkoutFieldsModel = Mage::getModel('aitcheckoutfields/aitcheckoutfields');

        $store = $this->getColumn('store_id');
        if ($store)
        {
            $store->setData('index', 'store_id');
            //$store->setData('renderer', 'aitcheckoutfields/widget_grid_column_renderer_store');
        }

        foreach ($attributeCollection->getItems() as $attr)
        {   
            $i++;
            $options = $checkoutFieldsModel->getOptionValues($attr['attribute_id']);
           if(Mage::registry('aitcheckoutfields_excel'))
           {
                if($attr['frontend_input']=='date')
                {
                    $attr['frontend_input']= 'text';
                }
           }
            switch ($attr['frontend_input'])
            {
                case 'radio':
                case 'select':
                       $this->addColumn('aitec'.$attr['attribute_id'].'_value', array(
                            'header' => $attr->getData('frontend_label'),
                            'index'  => 'aitec'.$attr['attribute_id'].'.value',
                            'type'   => 'options',
                            'renderer' => 'adminhtml/widget_grid_column_renderer_longtext',
                            //'renderer' => 'aitcheckoutfields/widget_grid_column_renderer_options',
                            'filter' => 'aitcheckoutfields/widget_grid_column_filter_select',
                            'width'  => '100px',
                            'options'=> $options
                        ));
                    
                break;
                /*
                case 'date':
                       $this->addColumn('aitec'.$attr['attribute_id'].'val_value', array(
                            'header' => $attr->getData('frontend_label'),
                            'index'  =>'aitec'.$attr['attribute_id'].'val.value',
                            'type'   => 'date',
                            #'filter' => 'aitcheckoutfields/widget_grid_column_filter_date',
                            'width'  => '100px',
                            'format' => Mage::app()->getLocale()->getDateFormat('medium')
                        ));
                break;
                */
                case 'boolean':
                    $this->addColumn('aitec'.$attr['attribute_id'].'val_value', array(
                        'header' => $attr->getData('frontend_label'),
                        'index'  =>'aitec'.$attr['attribute_id'].'val.value',
                        'type'   => 'options',
                        'width'  => '100px',
                        'filter' => 'aitcheckoutfields/widget_grid_column_filter_yesno',
                        'options' => array(
                            '1' => Mage::helper('catalog')->__('Yes'),
                            '0' => Mage::helper('catalog')->__('No'),
                         ),
                    ));
                break;
                case 'multiselect':
                case 'checkbox':    
                    $this->addColumn('aitec'.$attr['attribute_id'].'val_value', array(
                        'header' => $attr->getData('frontend_label'),
                        'index'  => 'aitec'.$attr['attribute_id'].'val.value',
                        'renderer' => 'aitcheckoutfields/widget_grid_column_renderer_multiselect',
                        'filter' => 'aitcheckoutfields/widget_grid_column_filter_multiselect',
                        'filter_condition_callback' => array($checkoutFieldsModel, 'multiSelectFilter'),
                        'type'   => 'multiselect',
                        'options'=> $options,
                        'width'  => '100px',
                        'sortable' => false
                    ));
                break;
                case 'textarea':
                case 'text':
                    $this->addColumn('aitec'.$attr['attribute_id'].'val_value', array(
                            'header' => $attr->getData('frontend_label'),
                            'index'  => 'aitec'.$attr['attribute_id'].'val.value',
                            'type'   => 'text',
                            'width'  => '100px'
                        ));
                break;
            }
         }
         
        $this->_columns['action'] = $action;
        $this->_columns['action']->setId('action');
        $this->_lastColumnId = 'action';
        
        
        return $res;
    }
    
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
            } else {
                $cond = $column->getFilter()->getCondition();
                if ($field && isset($cond)) {
                    if(false === stripos($field,'aitec'))
                    {
                        $field = 'main_table.' . $field;    
                    }
                    $this->getCollection()->addFieldToFilter($field , $cond);
                }
            }
        }
        return $this;
    }
}