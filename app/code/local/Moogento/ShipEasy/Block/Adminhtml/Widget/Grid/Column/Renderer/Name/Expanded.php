<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Expanded.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Name_Expanded
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    protected $_format = null;

    public function render(Varien_Object $row)
    {
        $address = null;
        if ($this->getColumn()->getAddressType() == 'both') {
            $shippingAddress = $row->getShippingAddress();
            if (!$shippingAddress || !$shippingAddress->getId()) {
                $address = $shippingAddress;
            } else {
                $address = $row->getBillingAddress();
            }
        } else if ($this->getColumn()->getAddressType() == 'shipping') {
            $shippingAddress = $row->getShippingAddress();
            if (!$shippingAddress || !$shippingAddress->getId()) {
                return '&nbsp;';
            }
            $address = $shippingAddress;
        } else {
            $address = $row->getBillingAddress();
        }

        return '<div class="bill_ship_for_form" data-url="'.$this->getUrl('*/sales_grid/showAddressForm', array('order_id' => $row->getId(), 'type' => $this->getColumn()->getIndex())).'">'.$this->_formatAddress($address).'</div><span class="edit-icon"></span>';
    }

    public function renderExport(Varien_Object $row)
    {
        $address = null;
        if ($this->getColumn()->getAddressType() == 'both') {
            $shippingAddress = $row->getShippingAddress();
            if (!$shippingAddress || !$shippingAddress->getId()) {
                $address = $shippingAddress;
            } else {
                $address = $row->getBillingAddress();
            }
        } else if ($this->getColumn()->getAddressType() == 'shipping') {
            $shippingAddress = $row->getShippingAddress();
            if (!$shippingAddress || !$shippingAddress->getId()) {
                return '&nbsp;';
            }
            $address = $shippingAddress;
        } else {
            $address = $row->getBillingAddress();
        }

        return $address->getFormated(false);
    }

    protected function _getFieldsAttribute()
    {
        $fields = array();
        switch ($this->getColumn()->getAddressType()) {
            case 'shipping':
                $fields = explode(",", Mage::getStoreConfig('moogento_shipeasy/grid/shipping_name_fields'));
                break;
            default:
                $fields = explode(",", Mage::getStoreConfig('moogento_shipeasy/grid/billing_name_fields'));
        }
        if (in_array('region_field', $fields)) {
            array_splice( $fields, array_search('region_field', $fields), 0, array('region', 'region_id') );
        }

        return $fields;
    }

    protected function _getAllFieldsAttribute()
    {
        $addressForm = Mage::getModel('customer/form')
                           ->setFormCode('adminhtml_customer_address')
                           ->setStore(Mage::app()->getStore()->getId())
                           ->setEntity(Mage::getModel('customer/address'));
        $attributes = $addressForm->getAttributes();

        $list = array();
        foreach($attributes as $attribute){
            $list[] = $attribute->getAttributeCode();
        }
        return $list;
    }

    protected function _getFormat($country_id = "")
    {
        $fields = $this->_getFieldsAttribute();
        $allFields = $this->_getAllFieldsAttribute();
        $toRemove = array_diff($allFields, $fields);
        $format = Mage::helper("moogento_core")->getTemplate($country_id);

        foreach ($toRemove as $field) {
            switch ($field) {
                case 'street':
                    $format = preg_replace('|{{if street1}}(.*?){{/if}}|im', '', $format);
                    $format = preg_replace('|{{depend street2}}(.*?){{/depend}}|im', '', $format);
                    $format = preg_replace('|{{depend street3}}(.*?){{/depend}}|im', '', $format);
                    $format = preg_replace('|{{depend street4}}(.*?){{/depend}}|im', '', $format);
                    break;
                default:
                    $format = preg_replace('|{{depend ' . $field .'}}(.*?){{/depend}}|im', '', $format);
                    $format = preg_replace('|{{if ' . $field .'}}(.*?){{/if}}|im', '', $format);
                    $format = str_replace('{{var ' . $field .'}}', '', $format);
            }
        }
        $format = preg_replace("|(\r)?\n|im", '', $format);
        $format = preg_replace('|(<br(.*?)>){2,}|im', '', $format);

        return $format;
    }

    protected function _formatAddress($address)
    {
        $format = $this->_getFormat($address->getCountryId());

        $type = new Varien_Object();
        $type->setCode('default')
            ->setDefaultFormat($format);

        $type->setRenderer(
            Mage::helper('customer/address')
                ->getRenderer('customer/address_renderer_default')->setType($type)
        );

        return $type->getRenderer()->render($address);
    }
}
