<?php
class Belvg_Local_Model_Observer
{
    public function applyShopAssistantPost(Varien_Event_Observer $observer)
    {
        $assistant  = Mage::app()->getRequest()->getParam('assistant', FALSE);
        if ($assistant) {
            $brand      = $this->_loadPost('brand');
            $goal       = $this->_loadPost('goal');
            $ingredient = $this->_loadPost('ingredient');

            $collection = $observer->getEvent()->getCollection();

            $collection->addAttributeToSelect('brand',       'left');
            $collection->addAttributeToSelect('goal',        'left');
            $collection->addAttributeToSelect('ingredients', 'left');

            /* WHERE */
            if ($brand) {
                $collection->addAttributeToFilter('brand', $brand);
            }

            if ($goal) {
                $collection->addAttributeToFilter('goal', $goal);
            }

            if ($ingredient) {
                $collection->addAttributeToFilter('ingredient', $ingredient);
            }

            //print_r((string)$collection->getSelect()); die;
        }
    }

    protected function _loadPost($key)
    {
        $assistant  = Mage::app()->getRequest()->getParam('assistant');

        if (isset($assistant[$key])) {
            return $assistant[$key];
        } else {
            return '';
        }
    }
}
