<?php 

class Moogento_ShipEasy_Adminhtml_Sales_GridController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function addressSaveAction()
    {
        $addressId  = $this->getRequest()->getParam('address_id');
        $address    = Mage::getModel('sales/order_address')->load($addressId);
        $data       = $this->getRequest()->getPost();
        if ($data && $address->getId()) {
            $address->addData($data);
            try {
                $address->implodeStreetAddress()
                    ->save();
                $order = $address->getOrder();
                $order->save();
                
                if($this->getRequest()->getPost('type_billship') == 'bill'){
                    $billing_address = $order->getBillingAddress()->getFormated(true);
                    if (!Mage::getStoreConfigFlag('moogento_shipeasy/grid/billing_name_expanded')) {
                        $result_name = explode("<br/>", $billing_address);
                        $this->getResponse()->setBody($result_name[0]);
                    } else {
                        $this->getResponse()->setBody($billing_address);
                    }
                } else {
                    $shipping_address = $order->getShippingAddress()->getFormated(true);
                    if (!Mage::getStoreConfigFlag('moogento_shipeasy/grid/shipping_name_expanded')) {
                        $result_name = explode("<br/>", $shipping_address);
                        $this->getResponse()->setBody($result_name[0]);
                    } else {
                        $this->getResponse()->setBody($shipping_address);
                    }
                }
            } catch (Exception $e) {
                Mage::logException($e);
                /*$this->_getSession()->addException(
                    $e,
                    Mage::helper('sales')->__('An error occurred while updating the order address. The address has not been changed.')
                );*/
                $this->getResponse()->setBody(Mage::helper('sales')->__('An error occurred while updating the order address. The address has not been changed.'));
            }
        } else {
            $this->getResponse()->setBody(Mage::helper('sales')->__('An error occurred while updating the order address. The address has not been changed.'));
        }
    }
    
    public function showAddressFormAction()
    {
        $order_id  = $this->getRequest()->getParam('order_id');
        $type =  $this->getRequest()->getParam('type');
        $type = substr($type, 0, 4);
        $row = Mage::getModel('sales/order')->load($order_id);
        
        $result_text = '<div class="bill_ship_form">';
        $result_text .= $this->getLayout()->createBlock('moogento_shipeasy/adminhtml_sales_order_grid_billship')
                ->setOrder($row)
                ->setType($type)
                ->toHtml();
        $result_text .= '</div>';
        $this->getResponse()->setBody($result_text);
    }
    
}
