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
class Mage_Shell_Balance_ImportUrlRewrite extends Mage_Shell_Abstract
{
    public function run()
    {
        $url1=Mage::getModel('core/url_rewrite')->getResourceCollection()->addFieldToFilter( 'id_path', 'category/2603' )->getFirstItem();
        $url1->delete();
        $url=Mage::getModel('core/url_rewrite')->getResourceCollection()->addFieldToFilter( 'request_path', 'increase-energy.html' )->getFirstItem();
        $url->setIdPath('category/2603');
        $url->setTargetPath('catalog/category/view/id/2603');
        $url->setCategoryId('2603');
        $url->save();
    }
    
     /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f importUrlRewrite.php -- [options]

  --path <path>           Path to csv file

USAGE;
    }
}

$shell = new Mage_Shell_Balance_ImportUrlRewrite();
$shell->run();
