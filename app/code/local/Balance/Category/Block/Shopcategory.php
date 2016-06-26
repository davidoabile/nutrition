<?php
class Balance_Category_Block_Shopcategory extends Mage_Core_Block_Template
{
    protected $_currentChildCategories = null;

    public function renderCategoriesMenuHtml($_categories){
    	$html = '<div class="nav-panel--dropdown nav-panel full-width" id="box-shopbycategory">
<div class="nav-panel-inner"> 
<div class="nav-block--center grid12-12">
    	';
    	$html .= '<ul class="level0 nav-submenu dd-itemgrid dd-itemgrid-4col nav-ul-left nav-mobile acco parent" id="shopbycategory">';
    	foreach ($_categories as $key => $_category) {
    		$count = $_category->getChildrenCategories()->count();
    		if ($count > 0) {
    			$html .= '<li class="nav-item level1 nav-1-1 first nav-item--only-subcategories"><a href="'.$_category->getUrl().'"><span>'.$_category->getName().'</span></a><span class="opener"></span>';
    		}else{
    			$html .= '<li class="nav-item level1 nav-1-1 first nav-item--only-subcategories"><a href="'.$_category->getUrl().'"><span>'.$_category->getName().'</span></a>';
    		}
    		
    		$html .= $this->renderItemsHtml(1,$_category);
    		$html .= "</li>";
    	}
    	$html .= '</ul></div></div></div>';
    	return $html;
    }

    function renderItemsHtml($level=0,$parent){
    	$html = "";
    	if ($parent) {
    		$_categories = $parent->getChildrenCategories();
    		if ($_categories->count()) {
    			$html = '<ul class="level'.$level.' nav-submenu nav-panel" style="display:none">';
		    	foreach ($_categories as $key => $_category) {
		    		$html .= '<li class="nav-item level'.$level.' nav-1-1 first nav-item--only-subcategories"><a href="'.$_category->getUrl().'"><span>'.$_category->getName().'</span></a>';
		    		$html .= $this->renderItemsHtml($level+1,$_category);
		    		$html .= "</li>";
		    	}
		    	$html .= '</ul>';
		    	
    		}
    	}
    	return $html;
    }

     public function getCurrentChildCategories()
    {
        if (null === $this->_currentChildCategories) {
            $layer = Mage::getSingleton('catalog/layer');
            $category = $layer->getCurrentCategory();
            $this->_currentChildCategories = $category->getChildrenCategories();
            $productCollection = Mage::getResourceModel('catalog/product_collection');
            $layer->prepareProductCollection($productCollection);
            $productCollection->addCountToCategories($this->_currentChildCategories);
        }
        return $this->_currentChildCategories;
    }

     public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', Mage::registry('current_category'));
        }
        return $this->getData('current_category');
    }
}

?>