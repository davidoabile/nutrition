<?php

class Moogento_CourierRules_Block_Adminhtml_Configuration_Rules extends Mage_Adminhtml_Block_Widget implements
    Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'moogento/courierrules/rules.phtml';

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function initForm()
    {
        return $this;
    }

    protected function _prepareLayout()
    {
        /** @var Mage_Page_Block_Html_Head $head */
        $head = $this->getLayout()->getBlock('head');

        $head->addJs('moogento/general/jquery.min.js');
        $head->addJs('moogento/general/jquery-ui.min.js');
        $head->addJs('moogento/general/chosen.jquery.min.js');
        $head->addJs('moogento/general/knockout.js');
        $head->addJs('moogento/general/knockout-sortable.min.js');
        $head->addJs('moogento/general/jquery.switchButton.js');

        $head->addJs('moogento/courierrules/base.js');
        $head->addJs('moogento/courierrules/rules.js');

        $head->addCss('moogento/general/chosen.min.css');
        $head->addCss('moogento/general/config.css');
        $head->addCss('moogento/courierrules/courierrules.css');
        $head->addCss('moogento/general/jqueryui/jquery-ui-1.10.4.custom.min.css');

        return parent::_prepareLayout();
    }

    protected function _getRulesJson()
    {
        $collection = Mage::getModel('moogento_courierrules/rule')->getCollection();

        $collection->addOrder('sort', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        return $collection->asJson();
    }

    protected function _getZones()
    {
        return Mage::getModel('moogento_courierrules/zone')->getResourceCollection()
            ->load()
            ->toOptionArray();
    }

    protected function _getTracking()
    {
        return Mage::getModel('moogento_courierrules/tracking')->getResourceCollection()
            ->load()
            ->toOptionArray();
    }

    protected function _getStoreSelectOptions()
    {
        $curWebsite = $this->getRequest()->getParam('website');
        $curStore   = $this->getRequest()->getParam('store');

        $storeModel = Mage::getSingleton('adminhtml/system_store');
        /* @var $storeModel Mage_Adminhtml_Model_System_Store */

        $url = Mage::getModel('adminhtml/url');

        $options = array();

        foreach ($storeModel->getWebsiteCollection() as $website) {
            $websiteShow = false;
            foreach ($storeModel->getGroupCollection() as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                $groupShow = false;
                foreach ($storeModel->getStoreCollection() as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $options['website_' . $website->getCode()] = array(
                            'label'    => $website->getName(),
                            'style'    => 'padding-left:16px; background:#DDD; font-weight:bold;',
                        );
                    }
                    if (!$groupShow) {
                        $groupShow = true;
                        $options['group_' . $group->getId() . '_open'] = array(
                            'is_group'  => true,
                            'is_close'  => false,
                            'label'     => $group->getName(),
                            'style'     => 'padding-left:32px;'
                        );
                    }
                    $options['store_' . $store->getCode()] = array(
                        'label'    => $store->getName(),
                        'style'    => '',
                    );
                }
                if ($groupShow) {
                    $options['group_' . $group->getId() . '_close'] = array(
                        'is_group'  => true,
                        'is_close'  => true,
                    );
                }
            }
        }

        return $options;
    }

    protected function _getAllShippingMethods()
    {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();

        foreach($methods as $_ccode => $_carrier)
        {
            $_methodOptions = array();
            if($_methods = $_carrier->getAllowedMethods())
            {
                foreach($_methods as $_mcode => $_method)
                {
                    $_code = $_ccode . '_' . $_mcode;
                    $_methodOptions[] = array('value' => $_code, 'label' => $_method);
                }

                if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                    $_title = $_ccode;

                $options[] = array('value' => $_methodOptions, 'label' => $_title);
            }
        }

        return $options;
    }

    protected function _getConfig()
    {
        $settings = Mage::getStoreConfig('courierrules/settings');
        if (isset($settings['predefined_options'])) {
            $settings['predefined_options'] = explode(',', $settings['predefined_options']);
        }

        return Mage::helper('core')->jsonEncode($settings);
    }

    protected function _getProductAttributes()
    {
        $attributes = Mage::getSingleton('eav/config')
            ->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeCollection();

        $list = array();

        foreach ($attributes as $attribute) {
            if ($attribute->getData('frontend_label')) {
                $inputType = $attribute->getFrontend()->getInputType();
                $code = $attribute->getData('attribute_code');
                if (!$attribute->getData('is_visible')
                        ||  !$inputType
                        || in_array($inputType, array('gallery', 'media_image', 'image'))
                        || in_array($code, array('tier_price', 'group_price', 'recurring_profile'))) {
                    continue;
                }
                $list[$code] = $attribute->getData('frontend_label');
            }
        }

        asort($list);

        return $list;
    }

    /*protected function _renderProductAttribute()
    {
        $attributeCode = Mage::getStoreConfig('courierrules/settings/product_attribute');
        $attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($attributeCode);

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('fake', array());

        foreach ($this->_getAdditionalElementTypes() as $code => $className) {
            $fieldset->addType($code, $className);
        }

        $inputType      = $attribute->getFrontend()->getInputType();
        $rendererClass  = $attribute->getFrontend()->getInputRendererClass();
        if (!empty($rendererClass)) {
            $fieldType  = $inputType . '_' . $attribute->getAttributeCode();
            $fieldset->addType($fieldType, $rendererClass);
        }

        $element = $fieldset->addField($attribute->getAttributeCode(), $inputType,
            array(
                'name'      => $attribute->getAttributeCode(),
                'label'     => $attribute->getFrontend()->getLabel(),
                'class'     => 'input-text',
                'data-bind' => "value: product_attribute, attr: {name: buildName('product_attribute'), id: buildId('product_attribute')}"
            )
        )
        ->setEntityAttribute($attribute);

        if ($inputType == 'select') {
            $element->setValues($attribute->getSource()->getAllOptions(true, true));
            $element->setData('data-bind', "value: product_attribute, attr: {name: buildName('product_attribute'), id: buildId('product_attribute')}, chosen: {width: '150px'}");
        } else if ($inputType == 'multiselect') {
            $element->setValues($attribute->getSource()->getAllOptions(false, true));
            $element->setCanBeEmpty(true);
            $element->setData('data-bind', "selectedOptions: product_attribute, attr: {name: buildName('product_attribute', true), id: buildId('product_attribute')}, chosen: {width: '150px'}");
        } else if ($inputType == 'date') {
            $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
            $element->setData('data-bind', "attr: {name: buildName('product_attribute'), id: buildId('product_attribute')}, datepicker: {value: product_attribute}");
        } else if ($inputType == 'multiline') {
            $element->setLineCount($attribute->getMultilineCount());
        }

        return $element->getElementHtml();
    }*/

    protected function _renderProductAttribute($product_attribute)
    {
        $attributeCode = Mage::getStoreConfig('courierrules/settings/'.$product_attribute);
        $attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($attributeCode);

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('fake', array());

        foreach ($this->_getAdditionalElementTypes() as $code => $className) {
            $fieldset->addType($code, $className);
        }
        
        $inputType      = $attribute->getFrontend()->getInputType();
        $rendererClass  = $attribute->getFrontend()->getInputRendererClass();
        if (!empty($rendererClass)) {
            $fieldType  = $inputType . '_' . $attribute->getAttributeCode();
            $fieldset->addType($fieldType, $rendererClass);
        }

        $element = $fieldset->addField($attribute->getAttributeCode(), $inputType,
            array(
                'name'      => $attribute->getAttributeCode(),
                'label'     => $attribute->getFrontend()->getLabel(),
                'class'     => 'input-text',
                'data-bind' => "value: ".$product_attribute.", attr: {name: buildName('".$product_attribute."'), id: buildId('".$product_attribute."')}"
            )
        )
        ->setEntityAttribute($attribute);

        if ($inputType == 'select') {
            $element->setValues($attribute->getSource()->getAllOptions(true, true));
            $element->setData('data-bind', "value: ".$product_attribute.", attr: {name: buildName('".$product_attribute."'), id: buildId('".$product_attribute."')}, chosen: {width: '150px'}");
        } else if ($inputType == 'multiselect') {
            $element->setValues($attribute->getSource()->getAllOptions(false, true));
            $element->setCanBeEmpty(true);
            $element->setData('data-bind', "selectedOptions: ".$product_attribute.", attr: {name: buildName('".$product_attribute."', true), id: buildId('".$product_attribute."')}, chosen: {width: '150px'}");
        } else if ($inputType == 'date') {
            $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
            $element->setData('data-bind', "attr: {name: buildName('".$product_attribute."'), id: buildId('".$product_attribute."')}, datepicker: {value: ".$product_attribute."}");
            $element->setFormat(Mage::app()->getLocale()->getDateFormatWithLongYear());
        } else if ($inputType == 'datetime') {
            $element->setFormat(
                Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
            );
        } else if ($inputType == 'multiline') {
            $element->setLineCount($attribute->getMultilineCount());
        }

        return $element->getElementHtml();
    }

    protected function _getAdditionalElementTypes()
    {
        $result = array(
            'select'      => 'Moogento_CourierRules_Block_Adminhtml_Form_Element_Select',
            'multiselect' => 'Moogento_CourierRules_Block_Adminhtml_Form_Element_Multiselect',
            'text'        => 'Moogento_CourierRules_Block_Adminhtml_Form_Element_Text',
            'price'       => 'Moogento_CourierRules_Block_Adminhtml_Form_Element_Text',
            'weight'      => 'Moogento_CourierRules_Block_Adminhtml_Form_Element_Text',
            'date'        => 'Moogento_CourierRules_Block_Adminhtml_Form_Element_Date',
        );

        $response = new Varien_Object();
        $response->setTypes(array());
        Mage::dispatchEvent('adminhtml_catalog_product_edit_element_types', array('response' => $response));

        foreach ($response->getTypes() as $typeName => $typeClass) {
            $result[$typeName] = $typeClass;
        }

        return $result;
    }

    protected function _getCourierRulesMethods()
    {
        $list = new Varien_Object();
        $methods = array();


        $predefinedOptions = Mage::getStoreConfig('courierrules/settings/predefined_options');
        if ($predefinedOptions) {
            $predefinedOptions = explode(',', $predefinedOptions);
            $toAdd = array(
                'label' => $this->__('Predefined Options'),
                'value' => array(),
            );
            foreach ($predefinedOptions as $option) {
                $toAdd['value'][] = array(
                    'label' => $option,
                    'value' => $option,
                );
            }
            $methods[] = $toAdd;
        }

        $list->setMethods($methods);

        Mage::dispatchEvent('moogento_courierrules_config_courierrules_list', array('list' => $list));

        return $list->getMethods();
    }
} 