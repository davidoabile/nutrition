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
        if ($this->getArg('path')) {
            $path = $this->getArg('path');
            $total = 0;
            $totalSuccess = 0;
            $totalUpdateSuccess=0;
            if (file_exists($path)) {
                if (($fp = fopen($path, 'r'))) {
                    while (($line = fgetcsv($fp, 10000, ','))) {
                        $total++;
                        if (1 && ($total == 1)) {
                            continue;
                        }
                        
                        $requestPath =substr($line[0], 1);
                        $targetPath = substr($line[1], 1);
                        
                        $rewrite = Mage::getModel('core/url_rewrite');
                        $check  = $rewrite->loadByRequestPath($requestPath);
                        // var_dump($check);die();
                        if ($check->getId()) {
                            $check->setTargetPath($targetPath);
                            $check->save();    
                            $totalUpdateSuccess++;
                        }else{
                            $rewrite->setIdPath(uniqid())
                                    ->setTargetPath($targetPath)
                                    ->setOptions('RP')
                                    ->setDescription('')
                                    ->setRequestPath($requestPath)
                                    ->setIsSystem(0)
                                    ->setStoreId(0);
                            try {
                                $rewrite->save();
                                 $totalSuccess++;
                            } catch (Exception $e) {
                                $logException = $e->getMessage();
                                Mage::logException($e);
                            }
                        }
                    }
                    fclose($fp);
                    // unlink($filename);
                }
            }
        }
        echo "Added " . $totalSuccess . ' | ';
        echo "Updated " . $totalUpdateSuccess;
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
