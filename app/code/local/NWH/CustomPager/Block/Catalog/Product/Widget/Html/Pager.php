<?php
class NWH_CustomPager_Block_Catalog_Product_Widget_Html_Pager extends Mage_Catalog_Block_Product_Widget_Html_Pager
{
	 public function getPagerUrl($params=array())
    {
        $urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        $_enableSeo = Mage::getStoreConfig("web/seo/use_rewrites");
        if ($_enableSeo) {
        	$_suffix = Mage::getStoreConfig("catalog/seo/category_url_suffix");
	        $_currentUrl = $this->getUrl('*/*/*', $urlParams);
	        $_currentUrl = str_replace("?np",$_suffix."?np", $_currentUrl);
	        return $_currentUrl;
        }
        return $this->getUrl('*/*/*', $urlParams);
       
    }
}
			