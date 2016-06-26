<?php

class Wyomind_Ordersexporttool_Adminhtml_TreeviewController extends Mage_Adminhtml_Controller_Action {

    public function downAction() {


        $type = $this->getRequest()->getParam("type");
        $i = $this->getRequest()->getParam("i");
        $order_id = $this->getRequest()->getParam("order_id");
        $nodes = array();

        if ($type == 'profiles') {
            $profiles = Mage::helper('core')->jsonDecode($this->getRequest()->getParam("profiles"));
            foreach ($profiles as $i => $elt) {
                $nodes[] = "{
                    'id':'" . $i . "-order-" . $order_id . "profile-" . $elt["id"] . "',
                    'txt': '" . Mage::getModel('ordersexporttool/profiles')->load($elt["id"])->getFileName() . " [" . $elt["id"] . "]',
                    'onopenpopulate' : myOpenPopulate,
                    'openlink' : '" . Mage::getUrl('*/treeview/down', array("type" => 'product', "i" => $i, "order" => $order_id, "profile_id" => $elt["id"], "products" => Mage::helper('core')->jsonEncode($elt['product']))) . "',
                    'canhavechildren' : true
                    }";
            }
        } else if ($type == "profiles_by_product") {
            $profiles = Mage::helper('core')->jsonDecode($this->getRequest()->getParam("profiles"));
            $product_id = Mage::helper('core')->jsonDecode($this->getRequest()->getParam("product_id"));
            foreach ($profiles as $i => $elt) {
                if (in_array($product_id, $elt['product'])) {
                    $nodes[] = "{
                    'id':'" . $i . "-order-" . $order_id . "profile-" . $elt["id"] . "',
                    'txt': '" . Mage::getModel('ordersexporttool/profiles')->load($elt["id"])->getFileName() . " [" . $elt["id"] . "]',
                    'onopenpopulate' : myOpenPopulate,
                    'canhavechildren' : false
                    }";
                }
            }
        } else {
            $products = Mage::helper('core')->jsonDecode($this->getRequest()->getParam("products"));
            $profile_id = $this->getRequest()->getParam("profile_id");
            foreach ($products as $elt) {
                $product = Mage::getModel('catalog/product')->load($elt);
                $nodes[] = "{
                    'id':'" . $i . "-order-" . $order_id . "profile-" . $profile_id . "product-" . $elt . "',
                    'txt': '" . $product->getName() . " [" . $product->getSku() . "]',
                    'onopenpopulate' : myOpenPopulate,
                    'canhavechildren' : false
                    }";
            }
        }

        die("[" . implode(",", $nodes) . "]");
    }
	protected function _isAllowed() {
        return true;
    }

}
