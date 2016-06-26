<?php
require_once '../abstract.php';

/**
 * Magento Compiler Shell Script
 *
 * @category    Mage
 * @package     Mage_Shell
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Shell_Compiler extends Mage_Shell_Abstract
{
    public function run()
    {
        $collection = Mage::getModel('cms/page')->getCollection();
        foreach($collection as $cms){
            $url=str_replace(".html","",$cms->getIdentifier());
            $this->showdata($url);
            $cms->setStores(array(1));
            $cms->setIdentifier($url);
            $cms->save();
        }
    }
    public  function showdata($data){
        var_dump($data);
        echo '\n';
    }
}

$shell = new Mage_Shell_Compiler();
$shell->run();
