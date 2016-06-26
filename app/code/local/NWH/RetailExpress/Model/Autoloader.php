<?php

class NWH_RetailExpress_Model_Autoloader extends Varien_Event_Observer {

    private $_fileExtension = '.php';
    private $_namespace='Shipping';
    private $_includePath;
    private $_namespaceSeparator = '\\';

    protected function _construct($auto = true) {
        $this->_includePath = Mage::getBaseDir('lib');
        if($auto === true ) {
        $this->controllerFrontInitBefore(null);
        }
    }

    public function setNamespace($namespace) {
        $this->_namespace = (string) $namespace;
        return $this;
    }
    /**
     * This an observer function for the event 'controller_front_init_before'.
     * It prepends our autoloader, so we can load the extra libraries.
     *
     * @param Varien_Event_Observer $event
     */
    public function controllerFrontInitBefore( $event ) {
        spl_autoload_register( array($this, 'loadClass'), true, true );
    }

     /**
     * Loads the given class or interface.
     *
     * @param string $className The name of the class to load.
     * @return void
     */
    public function loadClass($className) {
        if (null === $this->_namespace || $this->_namespace . $this->_namespaceSeparator === substr($className, 0, strlen($this->_namespace . $this->_namespaceSeparator))) {
            $fileName = '';
            $namespace = '';
            if (false !== ($lastNsPos = strripos($className, $this->_namespaceSeparator))) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName = str_replace($this->_namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . $this->_fileExtension;
            $filePath = ($this->_includePath !== null ? $this->_includePath . DIRECTORY_SEPARATOR : '') . $fileName;
         
            if(is_file($filePath))            require $filePath;
            
        }
    }
}
