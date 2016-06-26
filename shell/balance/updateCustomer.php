<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once dirname(__FILE__) . '/../abstract.php';

/**
 * Magento Compiler Shell Script
 *
 * @category    Mage
 * @package     Mage_Shell
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Shell_Balance_UpdateCustomer extends Mage_Shell_Abstract
{
    public function run()
    {
        ini_set("memory_limit", "1024M");
        //get all customer
        $count = 0;
        $listCustomer = Mage::getModel('customer/customer')->getCollection();
        if ($listCustomer->getSize()) {
            foreach ($listCustomer as $key => $customer) {
                $flag_ship = false;
                $flag_bill = false;
                $customer = Mage::getModel("customer/customer")->load($customer->getId());
                $default_billing = $customer->getDefaultBillingAddress();
                $default_shipping = $customer->getDefaultShippingAddress();
                $address = $customer->getAddress();
                if ($default_shipping) {
                    if($default_shipping->getEntityId()){
                        if(!$default_shipping->getFirstname()) $default_shipping->setFirstname($customer->getFirstname());
                        if(!$default_shipping->getLastname()) $default_shipping->setLastname($customer->getLastname());
                        $default_shipping->save();
                        $flag_ship = true;
                    }
                }
                if ($default_billing) {
                    if($default_billing->getEntityId()){
                        if(!$default_billing->getFirstname()) $default_billing->setFirstname($customer->getFirstname());
                        if(!$default_billing->getLastname()) $default_billing->setLastname($customer->getLastname());
                        $default_billing->save();
                        $flag_bill = true;
                    }
                }
               if($flag_bill || $flag_ship) {
                    $count++;
                    echo $count . " Updated " . $customer->getEmail() . " Customer \n";
                }
            }
        }
        echo "Updated " . $count . " Customer \n";
    }
    

}

$shell = new Mage_Shell_Balance_UpdateCustomer();
$shell->run();
