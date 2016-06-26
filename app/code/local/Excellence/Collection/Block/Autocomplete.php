<?php
/**
 * Magento
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
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Autocomplete queries list
 */
require_once 'Mage/CatalogSearch/Block/Autocomplete.php';
class Excellence_Collection_Block_Autocomplete extends Mage_CatalogSearch_Block_Autocomplete
{
    protected $_suggestData = null;

    protected function _toHtml()
    {
        
        $html = '';

        if (!$this->_beforeToHtml()) {
            return $html;
        }

        $suggestData = $this->getSuggestProducts();
        if (!($count = count($suggestData))) {
            return $html;
        }

        $count--;

        $html = '<ul>';
        foreach ($suggestData as $index => $item) {
            
            if ($index == 0) {
                $item['row_class'] .= ' first';
            }

            if ($index == $count) {
                $item['row_class'] .= ' last';
            }
            $imageThumbnail = "<div style='float: left; margin-right: 7px;'><img height='50' width='50' src=\"{$this->helper('catalog/image')->init($item, 'small_image')}\"  /></div>";
            
            $html .=  '<li title="'.$this->escapeHtml($item['name']).'" class="'.$item['row_class'].'">'.$imageThumbnail.'
                <div><a class="font12_gry">'.$this->escapeHtml($item['name']).'</a></div></li>';
        }

        $html.= '</ul>';
        
        
        return $html;
    }

     public function getSuggestProducts()     
     {


        $query = Mage::helper('catalogsearch')->getQuery();
        $query->setStoreId(Mage::app()->getStore()->getId());

                if ($query->getRedirect()){
                    $query->save();
                }
                else {
                    $query->prepare();
                }
            Mage::helper('catalogsearch')->checkNotes();


          $results=$query->getResultCollection();//->setPageSize(5);



        //$results=Mage::getResourceModel('catalogsearch/search_collection')->addSearchFilter(Mage::app()->getRequest()->getParam('q'));

        $results->addAttributeToFilter('visibility', array('neq' => 1));
        $results->setPageSize(10);    
        
        $results->addAttributeToSelect('description');
        $results->addAttributeToSelect('name');
        $results->addAttributeToSelect('thumbnail');
        $results->addAttributeToSelect('small_image');
        $results->addAttributeToSelect('url_key');


        return $results;
    }
    
    public function getSuggestData()
    {
        if (!$this->_suggestData) {
            $collection = $this->helper('catalogsearch')->getSuggestCollection();
            $query = $this->helper('catalogsearch')->getQueryText();
            $counter = 0;
            $data = array();
            foreach ($collection as $item) {
                
                
                
                $_data = array(
                    'title' => $item->getQueryText(),
                    'row_class' => (++$counter)%2?'odd':'even',
                    'num_of_results' => $item->getNumResults()
                );

                if ($item->getQueryText() == $query) {
                    array_unshift($data, $_data);
                }
                else {
                    $data[] = $_data;
                }
            }
            $this->_suggestData = $data;
        }
        return $this->_suggestData;
    }
/*
 *
*/
}
