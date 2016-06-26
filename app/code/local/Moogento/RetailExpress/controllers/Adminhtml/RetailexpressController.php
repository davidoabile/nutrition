<?php


class Moogento_RetailExpress_Adminhtml_RetailexpressController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function catalogAction()
    {
        $connector = Mage::getModel('moogento_retailexpress/connector');

        $date = date('U') - 86400000;
        $connector->ProductsGetBulkDetailsByChannel(1, date('Y-m-d\Th:m:s', $date));
        //$connector->OutletsGetByChannel(1);
        //$connector->CustomerGetDetails(10);
    }

    public function stockAction()
    {
        $connector = Mage::getModel('moogento_retailexpress/connector');

        $productId = '124138';

        $connector->ProductsGetDetailsStockPricingByChannel(1, $productId);
    }

    public function testAction()
    {
        $connector = Mage::getModel('moogento_retailexpress/connector');

        try {
            //$result = $connector->CustomerGetBulkDetails('yesterday');
            var_dump($connector->ProductsGetBulkDetailsByChannel(Mage::getStoreConfig('moogento_retailexpress/general/channel_id'), date('Y-m-d\Th:m:s', strtotime('-1month')), false));
            die();
            $this->_getSession()->addError('Connection success');
        } catch (Exception $e) {
            $this->_getSession()->addError('Connection problem:' . $e->getMessage());
        }
        $this->_redirectReferer();
    }
}