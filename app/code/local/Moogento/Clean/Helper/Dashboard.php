<?php

class Moogento_Clean_Helper_Dashboard extends Mage_Core_Helper_Abstract
{
    public function getDashboardReportData($data_name_value)
    {
        $result = Mage::getModel('moogento_clean/dashboard_report')->load($data_name_value, 'data_name');
        $result = Mage::helper('core')->jsonDecode($result->getValue());
        return $result;
    }
    
    function getPithyQuote() {
        $quote = array();
        $quote[] = '"Do or do not, there is no try." <em>-- Yoda</em>';
        $quote[] = 'You Can Do It!';
        $quote[] = 'The best way to predict the future is to invent it. <em>-- Alan Kay</em>';
        $quote[] = 'You miss 100% of the shots you don’t take. <em>-- Wayne Gretzky</em>';
        $quote[] = 'Good things come to people who wait, but only what\'s left by those that hustled!';
        $quote[] = 'Success is walking from failure to failure with no loss of enthusiasm. <em>--Winston Churchill</em>';
        $quote[] = 'If you\'re going through hell keep going. <em>--Winston Churchill</em>';
        $quote[] = 'The harder I work, the more luck I seem to have. <em>--Thomas Jefferson</em>';
		$quote[] = 'If people aren’t laughing at your dreams…then you\'re not dreaming big enough! <em>-― Grayson Marshall</em>';

        Mage::getConfig()->saveConfig('moogento_clean/dashboard/quote_count', count($quote));
        if(is_null($quote_today = Mage::getStoreConfig('moogento_clean/dashboard/quote_today'))){
            $today_value = rand(0,count($quote)-1);
            Mage::getConfig()->saveConfig('moogento_clean/dashboard/quote_today', $today_value);
            return $quote[$today_value];
        } else {
            if(isset($quote[$quote_today])){
                return $quote[$quote_today];
            } else {
                $today_value = rand(0,count($quote)-1);
                Mage::getConfig()->saveConfig('moogento_clean/dashboard/quote_today', $today_value);
                return $quote[$today_value];                
            }
        }
        
        
    }
    
    public function getDatePeriods()
    {
        return array(
            '24h' => $this->__('Last 24 Hours'),
            '7d'  => $this->__('Last 7 Days'),
            '1m'  => $this->__('Current Month'),
            '1y'  => $this->__('1Y'),
            '2y'  => $this->__('2Y')
        );
    }
}