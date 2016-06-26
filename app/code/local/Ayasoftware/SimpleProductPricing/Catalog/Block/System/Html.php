<?php
/**
 * WDCA
 *  
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Ayasoftware
 * @package    Ayasoftware_SQLupdate
 * @copyright  Copyright (c) 2008-2010 Ayasoftware (http://www.ayasoftware.com)
 * @license    Commercial
 */ 


class Ayasoftware_SimpleProductPricing_Catalog_Block_System_Html extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
	protected $_dummyElement;
	protected $_fieldRenderer;
	protected $_values;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
		
		$html = "";
        $html .= "<div style=\" margin-bottom: 12px; width: 490px;\">".
                 "<img src='http://ayasoftware.com/sites/default/files/garland_logo.jpg' alt='AyaSoftware' id='logo'> <br />".
                 "This extension was provided by   Ayasoftware.com. <a href='http://www.ayasoftware.com/' target='_blank'>Click here</a>.<br /> ".
                 "Report bugs to support@ayasoftware.com".
        $html .= "" ;

        return $html;
    }
}
