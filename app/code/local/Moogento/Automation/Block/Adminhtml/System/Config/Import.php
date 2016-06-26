<?php


class Moogento_Automation_Block_Adminhtml_System_Config_Import 
    extends Mage_Adminhtml_Block_Widget 
    implements  Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'moogento/shipeasy/system/config/import.phtml';

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
        $head->addJs('moogento/general/jquery.switchButton.js');
        $head->addJs('moogento/general/knockout.js');
        $head->addJs('moogento/general/knockout.bindings.js');

        $head->addJs('moogento/shipeasy/config/import.js');

        $head->addCss('moogento/general/config.css');
        $head->addCss('moogento/general/chosen.min.css');
        $head->addCss('moogento/general/jqueryui/jquery-ui.min.css');
	    $head->addCss('moogento/general/font-awesome/css/font-awesome.min.css');
	    $head->addCss('moogento/stockeasy/import_options.css');

        return parent::_prepareLayout();
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    protected function _getJson()
    {
        return Mage::getStoreConfig('moogento_automation/config/import_shipeasy_csv_options') ? Mage::getStoreConfig('moogento_automation/config/import_shipeasy_csv_options') : '[]';
    }

    protected function _getAttributes()
    {
        $attributes = Mage::getSingleton('eav/config')
            ->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeCollection();

        $list = array(
	        array('value' => '', 'label' => ''),
        );

        foreach ($attributes as $attribute) {
            if ($attribute->getData('frontend_label')) {
                $inputType = $attribute->getFrontend()->getInputType();
                $code = $attribute->getData('attribute_code');
                if (!$attribute->getData('is_visible')
                    ||  !$inputType
                    || !in_array($inputType, array('boolean', 'date', 'datetime', 'multiline', 'multiselect', 'price', 'select', 'text', 'textarea', 'weight'))
                    || in_array($code, array('tier_price', 'group_price', 'recurring_profile'))) {
                    continue;
                }
                $data = array(
                    'code' => $code,
                    'type' => $inputType,
                    'label' => $attribute->getData('frontend_label'),
                );

                try {
                    if ($inputType == 'select') {
                        $data['options'] = $attribute->getSource()->getAllOptions(true, true);
                    } else if ($inputType == 'multiselect') {
                        $data['options'] = $attribute->getSource()->getAllOptions(false, true);
                    }
                } catch (Exception $e) {
                    continue;
                }
                $list[] = $data;
            }
        }

        usort($list, array($this, 'sortAttributes'));

        return Mage::helper('core')->jsonEncode($list);
    }

    public function sortAttributes($a, $b)
    {
        if ($a['label'] == $b['label']) return 0;
        return $a['label'] < $b['label'] ? -1 : 1;
    }
} 