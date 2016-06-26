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
* File        Email.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 



class Moogento_ShipEasy_Block_Adminhtml_Sales_Order_Grid_Email
    extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('moogento/shipeasy/sales/order/grid/email.phtml');
    }

    public function getEmailsList()
    {
        $_order = $this->getOrder();
        $customer_email_list = $_order->getCustomerEmailList();
        $result = "";
        if ($_order->getCustomerEmailList()){
            $email_list = array_filter(explode(" ", $customer_email_list));
            
            reset($email_list);
            if($dop_mail = $_order->getEmailFromAdmin()){
                array_unshift($email_list, $dop_mail);
            }
            $first_key = key($email_list);
            foreach (array_filter($email_list) as $index => $email){
                if($index == $first_key){
                    $class = "now_customer_email_list";
                } else {
                    $class = "last_customer_email_list";
                    if(Mage::getStoreConfig('moogento_shipeasy/grid/szy_email_only_main')) $class .= ' hide_email';
                }                
                $result .= '<div class="'.$class.'">' . $email . '</div>';
            }
            
        } else {
            $result .= $_order->getSzyCustomerEmail();
        }
        
        return $result;
    }

}
