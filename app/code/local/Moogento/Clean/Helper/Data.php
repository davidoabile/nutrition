<?php
if (!function_exists('mb_substr')) {
    function mb_substr($string, $offset, $length, $encoding = null) {
        $arr = preg_split("//u", $string);
        $slice = array_slice($arr, $offset + 1, $length);
        return implode("", $slice);
    }
}
if (!function_exists('mb_strtolower')) {
    function mb_strtolower($str) {
        return strtolower($str);
    }
}
class Moogento_Clean_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_THEME = 'moogento_clean/general/theme';
    const XML_PATH_LOGO = 'moogento_clean/general/logo'; // if 'Default Magento' chosen
    const XML_PATH_LOGO_SMALL = 'moogento_clean/general/logo_small'; // if 'Clean' chosen
    const XML_PATH_LOGO_ALT = 'Clean.d by Moogento';
    const XML_PATH_REFRESH = 'moogento_clean/general/grid_refresh';
    const XML_PATH_REFRESH_INTERVAL = 'moogento_clean/general/grid_refresh_interval';

    const XML_PATH_SHOW_GLOBAL_SEARCH = 'moogento_clean/display/show_global_search';
    const XML_PATH_SHOW_MAGENTO_ADS = 'moogento_clean/display/show_magento_ads';
    const XML_PATH_SHOW_COPYRIGHT = 'moogento_clean/display/show_copyright';

    const XML_PATH_SHOW_GRAVATAR = 'moogento_clean/display/show_gravatar';
    const XML_PATH_SHOW_BOTTOM_STATISTICS = 'moogento_clean/display/bottom_statistics';

    const XML_PATH_HEADER_CSS = 'moogento_clean/css/header';
    const XML_PATH_FOOTER_CSS = 'moogento_clean/css/footer';
    const XML_PATH_GRID_CSS = 'moogento_clean/css/grid';
    const XML_PATH_MAIN_CSS = 'moogento_clean/css/main_part';
    const XML_PATH_ACTION_GRID_CSS = 'moogento_clean/css/grid_actions';

    const XML_PATH_CUSTOM_COLORS = 'moogento_clean/colors/enable';
   
    const DEFAULT_MAGENTO_LOGO = 'magento_icon_default.png';
    const DEFAULT_CLEAN_LOGO = 'magento_icon.png';
    
    const GRAVATAR_URL = 'http://www.gravatar.com/avatar';
    const GRAVATAR_URL_SECURE = 'https://secure.gravatar.com/avatar';
	
    /**
     * Options
     *
     * @var array
     */
    protected $_options = array(
        'img_size'    => 80,
        'secure'      => null,
    );
	
	public function getHomeIcon()
	{
		if ( Mage::getStoreConfigFlag(self::XML_PATH_HEADER_CSS) )
		{
			if ( Mage::getStoreConfig(self::XML_PATH_THEME)=='default' )
			{
			 	if ( Mage::getStoreConfig(self::XML_PATH_LOGO) ) 
				{
			        $logo
			            = Mage::getBaseUrl('media') . 'moogento/' . Mage::getStoreConfig(self::XML_PATH_LOGO);
			        $logoPath
			            = BP . DS . 'media' . DS . 'moogento' . DS . Mage::getStoreConfig(self::XML_PATH_LOGO);
				}
				else
				{
		            $logo
		                = Mage::getBaseUrl('skin') . 'adminhtml/default/extended/img/' . self::DEFAULT_MAGENTO_LOGO;
		            $logoPath
		                = BP . DS . 'skin' . DS . 'adminhtml' . DS . 'default' . DS . 'extended' . DS . 'img' . DS . self::DEFAULT_MAGENTO_LOGO;
				}
			}
			elseif ( Mage::getStoreConfig(self::XML_PATH_THEME)=='extended' )
			{
				if ( Mage::getStoreConfig(self::XML_PATH_LOGO_SMALL) )
				{
			        $logo
			            = Mage::getBaseUrl('media') . 'moogento/' . Mage::getStoreConfig(self::XML_PATH_LOGO_SMALL);
			        $logoPath
			            = BP . DS . 'media' . DS . 'moogento' . DS . Mage::getStoreConfig(self::XML_PATH_LOGO_SMALL);
				}
				else
				{
		            $logo
		                = Mage::getBaseUrl('skin') . 'adminhtml/default/extended/img/' . self::DEFAULT_CLEAN_LOGO;
		            $logoPath
		                = BP . DS . 'skin' . DS . 'adminhtml' . DS . 'default' . DS . 'extended' . DS . 'img' . DS . self::DEFAULT_CLEAN_LOGO;
				}
			}
	    } 
		else 
		{
            $logo
                = Mage::getBaseUrl('skin') . 'adminhtml/default/extended/img/' . self::DEFAULT_CLEAN_LOGO;
            $logoPath
                = BP . DS . 'skin' . DS . 'adminhtml' . DS . 'default' . DS . 'extended' . DS . 'img' . DS . self::DEFAULT_CLEAN_LOGO;
		}
		
		
        if (file_exists($logoPath)) {
            return '<img src="'.$logo.'" alt="'.$this->__('Clean').'" class="logo" />';
        } else {
            return false;
        }
	}
	
    public function getStatistics()
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        $show_stats_refunded = Mage::getStoreConfig('moogento_clean/display/bottom_statistics_refunded');
        $show_stats_canceled = Mage::getStoreConfig('moogento_clean/display/bottom_statistics_canceled');
        $show_stats_sold = Mage::getStoreConfig('moogento_clean/display/bottom_statistics_sold');
		
		$sqlBase = "SELECT ";	
		if($show_stats_sold) $sqlBase .= "sum(grand_total) sold, ";
		if($show_stats_canceled) $sqlBase .= "sum(total_canceled) canceled, ";
		if($show_stats_refunded) $sqlBase .= "sum(total_refunded) refunded ";
        $sqlBase .= "FROM {$resource->getTableName('sales/order')}";
		$sqlBase = str_replace(', FROM',' FROM',$sqlBase);

        $data = array();

        $data['day'] = $readConnection->fetchRow($sqlBase . ' WHERE created_at > NOW() - INTERVAL 1 DAY');
        $data['week'] = $readConnection->fetchRow($sqlBase . ' WHERE created_at > NOW() - INTERVAL 7 DAY');
        $data['month'] = $readConnection->fetchRow($sqlBase . ' WHERE created_at > NOW() - INTERVAL 30 DAY');
       
	    $data['day_prev'] = $readConnection->fetchRow($sqlBase . ' WHERE created_at > (NOW()-INTERVAL 2 DAY) - INTERVAL 1 DAY');
        $data['week_prev'] = $readConnection->fetchRow($sqlBase . ' WHERE created_at > (NOW()-INTERVAL 14 DAY) - INTERVAL 7 DAY');
        $data['month_prev'] = $readConnection->fetchRow($sqlBase . ' WHERE created_at > (NOW()-INTERVAL 60 DAY) - INTERVAL 30 DAY');
		
		if($show_stats_sold) {
			$data['day']['sold'] = floor($data['day']['sold']);
			$data['week']['sold'] = floor($data['week']['sold']);
			$data['month']['sold'] = floor($data['month']['sold']);
		
			$data['day_prev']['sold'] = floor($data['day_prev']['sold']);
			$data['week_prev']['sold'] = floor($data['week_prev']['sold']);
			$data['month_prev']['sold'] = floor($data['month_prev']['sold']);
		}
		if($show_stats_canceled) {
			$data['day']['canceled'] = floor($data['day']['canceled']);
			$data['week']['canceled'] = floor($data['week']['canceled']);
			$data['month']['canceled'] = floor($data['month']['canceled']);
		
			$data['day_prev']['canceled'] = floor($data['day_prev']['canceled']);
			$data['week_prev']['canceled'] = floor($data['week_prev']['canceled']);
			$data['month_prev']['canceled'] = floor($data['month_prev']['canceled']);
		}
		if($show_stats_refunded) {
			$data['day']['refunded'] = floor($data['day']['refunded']);
			$data['week']['refunded'] = floor($data['week']['refunded']);
			$data['month']['refunded'] = floor($data['month']['refunded']);
		
			$data['day_prev']['refunded'] = floor($data['day_prev']['refunded']);
			$data['week_prev']['refunded'] = floor($data['week_prev']['refunded']);
			$data['month_prev']['refunded'] = floor($data['month_prev']['refunded']);
		}
			
        $currency_symbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
        $result_html = '';
		
	if($show_stats_sold) {	
		$result_html .= '<span class="stats_section">';

			$result_html .= '<strong>' . $this->__('sold') . '</strong> ';
	        $result_html .= '<span class="group_section group_24h"><span class="stats_label">24h</span><span class="stats_value">';
				$result_html .= ' <span class="';
				if($data['day']['sold'] > $data['day_prev']['sold']) $result_html .= 'prev_worse';
				else $result_html .= 'prev_better';
				$result_html .= '">';
					$result_html .= $data['day']['sold'] * 1;
			        $result_html .= ($data['day']['sold'] == 0) ? "" : $currency_symbol;
					$result_html .= '</span>';
					$result_html .= ' ('.($data['day_prev']['sold'] * 1).')';
	        $result_html .= '</span></span>';
	        $result_html .= '<span class="group_section group_7d"><span class="stats_label">7d</span><span class="stats_value">';
				$result_html .= ' <span class="';
				if($data['week']['sold'] > $data['week_prev']['sold']) $result_html .= 'prev_worse';
				else $result_html .= 'prev_better';
				$result_html .= '">';
					$result_html .= $data['week']['sold'] * 1;
			        $result_html .= ($data['week']['sold'] == 0) ? "" : $currency_symbol;
					$result_html .= '</span>';				
					$result_html .= ' ('.($data['week_prev']['sold'] * 1).')';
	        $result_html .= '</span></span>';
	        $result_html .= '<span class="group_section group_30d"><span class="stats_label">30d</span><span class="stats_value">';
				$result_html .= ' <span class="';
				if($data['month']['sold'] > $data['month_prev']['sold']) $result_html .= 'prev_worse';
				else $result_html .= 'prev_better';
				$result_html .= '">';
			        $result_html .= $data['month']['sold'] * 1;
			        $result_html .= ($data['month']['sold'] == 0) ? "" : $currency_symbol;
					$result_html .= '</span>';
					$result_html .= ' ('.($data['month_prev']['sold'] * 1).')';
	        $result_html .= '</span></span>';
		$result_html .= '</span>';
	}
	if($show_stats_canceled) {	
		$result_html .= '<span class="stats_section">';
			$result_html .= ' <strong>' . $this->__('cancel') . '</strong> ';
	        $result_html .= '<span class="group_section group_24h"><span class="stats_label">24h</span><span class="stats_value">';
				$result_html .= ' <span class="';
				if($data['day']['canceled'] > $data['day_prev']['canceled']) $result_html .= 'prev_worse';
				else $result_html .= 'prev_better';
				$result_html .= '">';
			        $result_html .= $data['day']['canceled'] * 1;
			        $result_html .= ($data['day']['canceled'] == 0) ? "" : $currency_symbol;
					$result_html .= '</span>';
					$result_html .= ' ('.($data['day_prev']['canceled'] * 1).')';
	        $result_html .= '</span></span>';
	        $result_html .= '<span class="group_section group_7d"><span class="stats_label">7d</span><span class="stats_value">';
				$result_html .= ' <span class="';
				if($data['week']['canceled'] > $data['week_prev']['canceled']) $result_html .= 'prev_worse';
				else $result_html .= 'prev_better';
				$result_html .= '">';
			        $result_html .= $data['week']['canceled'] * 1;
			        $result_html .= ($data['week']['canceled'] == 0) ? "" : $currency_symbol;
					$result_html .= '</span>';
					$result_html .= ' ('.($data['week_prev']['canceled'] * 1).')';
	        $result_html .= '</span></span>';
	        $result_html .= '<span class="group_section group_30d"><span class="stats_label">30d</span><span class="stats_value">';
				$result_html .= ' <span class="';
				if($data['month']['canceled'] > $data['month_prev']['canceled']) $result_html .= 'prev_worse';
				else $result_html .= 'prev_better';
				$result_html .= '">';
			        $result_html .= $data['month']['canceled'] * 1;
			        $result_html .= ($data['month']['canceled'] == 0) ? "" : $currency_symbol;
					$result_html .= '</span>';
					$result_html .= ' ('.($data['month_prev']['canceled'] * 1).')';
	        $result_html .= '</span></span>';
		$result_html .= '</span>';
	}
	if($show_stats_refunded) {	
		$result_html .= '<span class="stats_section">';
			$result_html .= ' <strong>' . $this->__('refund') . '</strong> ';
	        $result_html .= '<span class="group_section group_24h"><span class="stats_label">24h</span><span class="stats_value">';
				$result_html .= ' <span class="';
				if($data['day']['refunded'] > $data['day_prev']['refunded']) $result_html .= 'prev_worse';
				else $result_html .= 'prev_better';
                    $result_html .= '">';
			        $result_html .= $data['day']['refunded'] * 1;
			        $result_html .= ($data['day']['refunded'] == 0) ? "" : $currency_symbol;
					$result_html .= '</span>';

					$result_html .= ' ('.($data['day_prev']['refunded'] * 1).')';
	        $result_html .= '</span></span>';
	        $result_html .= '<span class="group_section group_7d"><span class="stats_label">7d</span><span class="stats_value">';
				$result_html .= ' <span class="';
				if($data['week']['refunded'] > $data['week_prev']['refunded']) $result_html .= 'prev_worse';
				else $result_html .= 'prev_better';
				$result_html .= '">';
			        $result_html .= $data['week']['refunded'] * 1;
			        $result_html .= ($data['week']['refunded'] == 0) ? "" : $currency_symbol;
					$result_html .= '</span>';
					$result_html .= ' ('.($data['week_prev']['refunded'] * 1).')';
	        $result_html .= '</span></span>';
	        $result_html .= '<span class="group_section group_30d"><span class="stats_label">30d</span><span class="stats_value">';
				$result_html .= ' <span class="';
				if($data['month']['refunded'] > $data['month_prev']['refunded']) $result_html .= 'prev_worse';
				else $result_html .= 'prev_better';
				$result_html .= '">';
			        $result_html .= $data['month']['refunded'] * 1;
			        $result_html .= ($data['month']['refunded'] == 0) ? "" : $currency_symbol;
					$result_html .= '</span>';
					$result_html .= ' ('.($data['month_prev']['refunded'] * 1).')';
	        $result_html .= '</span></span>';
		$result_html .= '</span><br/>';
	}
       /*
        $currency_symbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
               $result_html = '<strong>' . $this->__('24h/7d/30d') . ' : ' . $this->__('sold') . '</strong>: ';
               $result_html .= $data['day']['sold'] * 1;
               $result_html .= ($data['day']['sold'] == 0) ? "" : $currency_symbol;
               $result_html .= '/';
               $result_html .= $data['week']['sold'] * 1;
               $result_html .= ($data['week']['sold'] == 0) ? "" : $currency_symbol;
               $result_html .= '/';
               $result_html .= $data['month']['sold'] * 1;
               $result_html .= ($data['month']['sold'] == 0) ? "" : $currency_symbol;
               $result_html .= ' <strong>' . $this->__('canceled') . '</strong>: ';
               $result_html .= $data['day']['canceled'] * 1;
               $result_html .= ($data['day']['canceled'] == 0) ? "" : $currency_symbol;
               $result_html .= '/';
               $result_html .= $data['week']['canceled'] * 1;
               $result_html .= ($data['week']['canceled'] == 0) ? "" : $currency_symbol;
               $result_html .= '/';
               $result_html .= $data['month']['canceled'] * 1;
               $result_html .= ($data['month']['canceled'] == 0) ? "" : $currency_symbol;
               $result_html .= ' <strong>' . $this->__('refunded') . '</strong>: ';
               $result_html .= $data['day']['refunded'] * 1;
               $result_html .= ($data['day']['refunded'] == 0) ? "" : $currency_symbol;
               $result_html .= '/';
               $result_html .= $data['week']['refunded'] * 1;
               $result_html .= ($data['week']['refunded'] == 0) ? "" : $currency_symbol;
               $result_html .= '/';
               $result_html .= $data['month']['refunded'] * 1;
               $result_html .= ($data['month']['refunded'] == 0) ? "" : $currency_symbol;*/
       
        return $result_html;
    }
    
    public function isInstalled($moduleName)
    {
        return Mage::getConfig()->getModuleConfig($moduleName)->is('active', 'true');
    }
    
    public function getOrderRowClassesStyle()
    {
        $css = "";
        if($this->isInstalled("Moogento_ShipEasy")) {
            $hover_percent = Mage::getStoreConfig('moogento_shipeasy/colors/hover_highlight') / 100;

            $statuses = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
            foreach ($statuses as $status) {
                $color = Mage::getStoreConfig('moogento_shipeasy/colors/' . $status["status"]);
                if ($color) {
                    $css .= "." . $status["status"] . "{";
                    $css .= "background-color: " . $color . "!important;";
                    $css .= "font-family: " . Mage::getStoreConfig('moogento_shipeasy/fonts/font') . ";";
                    $css .= "font-size: " . Mage::getStoreConfig('moogento_shipeasy/fonts/size') . "px;";
                    $css .= "}";
                    $css .= "." . $status["status"] . ":hover{";
                    $hsl_color = Mage::helper('moogento_shipeasy/color')->hexToHsl($color);
                    $hsl_color["L"]
                                 = (($hsl_color["L"] + $hover_percent * $hsl_color["L"]) < 1)
                        ?
                        ($hsl_color["L"] + $hover_percent * $hsl_color["L"])
                        :
                        ($hsl_color["L"] - $hover_percent * $hsl_color["L"]);
                    $color_hover = '#' . Mage::helper('moogento_shipeasy/color')->hslToHex($hsl_color);
                    $css .= "background-color: " . $color_hover . "!important;";
                    $css .= "}";
                }
            }
        }
        return $css;
    }

    public function getMenuLevel($menu, $level = 0)
    {
        $html = '<ul ' . (!$level ? 'id="nav"' : '') . ' class="minimized">' . PHP_EOL;
        foreach ($menu as $key => $item) {
            $html .= '<li ' . (!empty($item['children']) ? 'onmouseover="Element.addClassName(this,\'over\')" '
                                                           . 'onmouseout="Element.removeClassName(this,\'over\')"' : '') . ' class="'
                     . (!$level && !empty($item['active']) ? ' active' : '') . ' '
                     . $key . ' '
                     . (!empty($item['children']) ? ' parent' : '')
                     . (!empty($level) && !empty($item['last']) ? ' last' : '')
                     . ' level' . $level . '"> <a href="' . $item['url'] . '" '
                     . (!empty($item['title']) ? 'title="' . $item['title'] . '"' : '') . ' '
                     . (!empty($item['click']) ? 'onclick="' . $item['click'] . '"' : '') . ' class="'
                     . ($level === 0 && !empty($item['active']) ? 'active' : '') . '"><span'
					 . ($level === 0 && ( ($key == 'dashboard') || ($key == 'sales') || ($key == 'catalog') || ($key == 'customer') || ($key == 'promo') || ($key == 'system')) ? ' class="moocon">' : '>')
                     . $this->escapeHtml($item['label']) . '</span></a>' . PHP_EOL;

            if (!empty($item['children'])) {
                $html .= $this->getMenuLevel($item['children'], $level + 1);
            }
            $html .= '</li>' . PHP_EOL;
        }
		
		// To only add to top menu
		if($level == 0){
            $html .= '<li id="menu_size" class="move_apart">' . PHP_EOL;
                $html .= '<a onclick="menuMoveApart();">' . PHP_EOL;
                $html .= '</a>' . PHP_EOL;
            $html .= '</li>' . PHP_EOL;
		}
        $html .= '</ul>' . PHP_EOL;

        return $html;
    }
	
    /**
     * Get img size
     *
     * @return int The img size
     */
    public function getImgSize()
    {
        return $this->_options['img_size'];
    }
	
    protected function _getGravatarUrl()
    {
        return Mage::app()->getStore()->isCurrentlySecure() ? self::GRAVATAR_URL_SECURE : self::GRAVATAR_URL;
    }
    
    function getGravatar($user)
    {
        if(is_null($user->getGravatarUrl())){
            $exept = '';
            $url = $this->_getGravatarUrl().'/'.md5($user->getEmail()).'?s='.$this->getImgSize().'&d='.urlencode("//moogento.com/logo/blankAvatar.png");
            $img_path = 'media/admin/gravatar';
            $img = $img_path.'/'.date("Y-m-d_H-i-s").'_'.$user->getId().'.png';
            try{
                if (!file_exists($img_path)){
                    if(!(mkdir($img_path, 0777))){
                        return '<img class="gravatar" src="'.$url.'" alt="" style="vertical-align: middle" width="25" height="25" />';
                    }
                }
                if (!file_exists($img)){
                    $fp = fopen($img, "w");
                    fclose($fp);
                }
                if(!is_writable($img)){
                    return '<img class="gravatar" src="'.$url.'" alt="" style="vertical-align: middle" width="25" height="25" />';
                }
                file_put_contents($img, file_get_contents($url));
                $model = Mage::getModel('admin/user')->load($user->getId());
                $model->setGravatarUrl($img);
                $model->save();
                return '<img class="gravatar" src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).substr($img, 6).'" alt="" style="vertical-align: middle" width="25" height="25" />';
            } catch (Exception $e) {
                $exept = $e->getMessage();
            }
            if($exept != ''){
                return '<img class="gravatar" src="'.$url.'" alt="" style="vertical-align: middle" width="25" height="25" />';
            }
        } else {
            $file_path = substr($user->getGravatarUrl(), 6); 
            if (file_exists(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/'.$file_path)){
                return '<img class="gravatar" src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$file_path.'" alt="" style="vertical-align: middle" width="25" height="25" />';
            } else {
                $model = Mage::getModel('admin/user')->load($user->getId());
                $model->setGravatarUrl(null);
                $model->save();
                $url = $this->_getGravatarUrl().'/'.md5($user->getEmail()).'?s=50&d='.urlencode("//moogento.com/logo/blankAvatar.png");
                return '<img class="gravatar" src="'.$url.'" alt="" style="vertical-align: middle" width="25" height="25" />';
            }
        }
    }
}