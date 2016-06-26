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
class Aitoc_Aitcheckoutfields_Block_Rewrite_FrontCheckoutOnepageReviewInfo extends Mage_Checkout_Block_Onepage_Review_Info
{
    public function getFieldHtml($aField)
    {
        $sSetName = 'customreview';
        
        return Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getAttributeHtml($aField, $sSetName, 'onepage');
    }
    
    public function getCustomFieldList($iTplPlaceId)
    {
        $iStepId = Mage::helper('aitcheckoutfields')->getStepId('review');
        
        if (!$iStepId) return false;

        return Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getCheckoutAttributeList($iStepId, $iTplPlaceId, 'onepage');
    }

    protected function _beforeToHtml()
    {
        if (version_compare(Mage::getVersion(), '1.5.0.0', 'ge'))
        {
            $this->setTemplate('aitcommonfiles/design--frontend--base--default--template--checkout--onepage--review--info.phtml');
            if (version_compare(Mage::getVersion(), '1.9.0.0', 'ge'))
            {
                $this->setTemplate('aitcommonfiles/design--frontend--rwd--default--template--checkout--onepage--review--info.phtml');
            }
            Mage::dispatchEvent('aitoc_module_set_template_after', array('block' => $this));
        }
        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();

        if ('' != $html)
        {
            if (Mage::getConfig()->getNode('modules/Ebizmarts_SagePaySuite/active'))
            {
                $html .= '
<script type="text/javascript">
//<![CDATA[
SageServer = new EbizmartsSagePaySuite.Checkout
(
    {
        \'checkout\':  checkout,
        \'review\':    review,
        \'payment\':   payment,
        \'billing\':   billing,
        \'accordion\': accordion
    }
);
//]]>
</script>
';
            }
        }

        return $html;
    }
}