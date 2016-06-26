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
* File        Image.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 

class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $html = '';
        $html .= '<div class="status_div">';
            $html .= '<span class="status_showing_data">';
                $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
                $html .= $statuses[$row->getStatus()];
            $html .= '</span>';
            $html .= '</br>';
            $html .= '<span class="status_form"  style="display:none;">';
                $html .= '<select name="status" class="szy_status_change" data-url="'. Mage::getModel('adminhtml/url')->getUrl('*/sales_order_process/updateStatusByJs', array()). '" data-orderid="'. $row->getId() .'" data-status="'. $row->getStatus().'">';
                foreach($statuses as $index=>$value){
                    if($row->getStatus() == $index){ 
                        $html .= '<option value="'.$index.'" selected="selected">'.$value.'</option>';
                    } else {
                        $html .= '<option value="'.$index.'">'.$value.'</option>';
                    }
                }
                $html .= '</select>';
                $html .= '</br>';
                $html .= '<input type="checkbox" value="1" class="input-checkbox szy_status_notify"> ';
                $html .= $this->__('Notify Customer?');
                $html .= '</br>';
                $html .= '<button class="button szy_status_button_edit"><span>';
                $html .= $this->__('Change status');
                $html .= '</span></button>';
                $html .= '<button class="button szy_status_button_close"><span>';
                $html .= $this->__('Cancel');
                $html .= '</span></button>';
            $html .= '</span>';
        $html .= '</div>';
        $html .= '<span class="edit-icon"></span>';

        return $html;
    }
    
}
