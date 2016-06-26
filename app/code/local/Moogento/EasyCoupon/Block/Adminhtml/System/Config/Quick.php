<?php

class Moogento_EasyCoupon_Block_Adminhtml_System_Config_Quick
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'moogento/easycoupon/system/config/quick.phtml';

    protected function _prepareLayout()
    {
        /** @var Mage_Page_Block_Html_Head $head */
        $head = $this->getLayout()->getBlock('head');

        $head->addJs('moogento/general/jquery.min.js');
        $head->addJs('moogento/general/chosen.jquery.min.js');
        $head->addJs('moogento/general/knockout.js');
        $head->addJs('moogento/general/knockout.bindings.js');

        $head->addJs('moogento/easycoupon/typeahead.bundle.js');
        $head->addJs('moogento/easycoupon/quick.js');

        $head->addCss('moogento/general/chosen.min.css');
        $head->addCss('moogento/easycoupon/config.css');
        return parent::_prepareLayout();
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    protected function _getStoreSelectOptions()
    {
        $storeModel = Mage::getSingleton('adminhtml/system_store');
        /* @var $storeModel Mage_Adminhtml_Model_System_Store */
        $options = array();
        foreach ($storeModel->getWebsiteCollection() as $website) {
            $websiteShow = false;
            foreach ($storeModel->getGroupCollection() as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                $groupShow = false;
                foreach ($storeModel->getStoreCollection() as $store) {
                    if (!$store->getIsActive()) {
                        continue;
                    }
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $options['website_' . $website->getCode()] = array(
                            'label'    => $website->getName(),
                            'url'      =>  $website->getDefaultStore()->getBaseUrl(),
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
                        'url'      => $store->getBaseUrl(),
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
} 