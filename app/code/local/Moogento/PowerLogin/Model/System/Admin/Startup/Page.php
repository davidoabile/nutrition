<?php

class Moogento_PowerLogin_Model_System_Admin_Startup_Page
        extends Mage_Adminhtml_Model_System_Config_Source_Admin_Page
{
    
    protected static $options;


    public function toOptionArray()
    {
        self::$options = array();
        self::$options[] = 'Please Select Page...';
       
        $menu    = $this->_buildMenuArray();

        $this->_createOptions(self::$options, $menu);

        $menu = new Varien_Object(array( 'options' =>  self::$options));
        
        Mage::dispatchEvent('moogento_powerlogin_create_options_startup_page', array('menu' => $menu));
        
        return $menu->getOptions();
    }
    
    public static function addOption(array $options, array $node)
    {   
        if(!isset($node['namespace'])){
            $node['namespace'] = null;
        }
        
        if(!isset($node['modulename'])){
            $node['modulename'] = null;
        }

       $options[] = self::prepareNode($node);
       
       return $options;
    }
    
    public static function prepareNode($node)
    {
        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        
       if(isset($node['label'])){
           return array(
               'label' => $node['label'],
               'value' => $node['uri']
               );
       }
        
        $namespace = isset($node['namespace']) ? $node['namespace'] : null;
        $module    = isset($node['modulename']) ? $node['modulename'] : null;
        $uri       = isset($node['uri']) ? $node['uri'] : null;
        
        $node = array();
        $level = 1;
        
        if($namespace && $module){
            $node['label'] = $namespace;
            $node['value'] = array(
                                array(
                                    'label' => str_repeat($nonEscapableNbspChar, ($level++) * 4) . $module,
                                    'value' => array(self::prepareUriLabel($uri, $level))
                                )
            );
        }elseif($namespace && !$module){
             $node['label'] = $namespace;
             $node['value'] = array(self::prepareUriLabel($uri, $level));
     
        } elseif ($module && !$namespace) {
            $node['label'] = $module;
            $node['value'] = array(self::prepareUriLabel($uri, $level));
        } else {
            $node = array(self::prepareUriLabel($uri, $level));
        }
        
        return $node;
    }
    
    public static function prepareUriLabel($uri, $level)
    {
        
        $labels = explode('_', (string)$uri);
        
        return self::createMenu($uri, $labels, $level);
                
    }
    
    public static function createMenu($uri, array $labels, $level = 1)
    {
        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        
        $array = array();
        
        $label  = array_shift($labels);
        
        if(count($labels)){
            $array['label'] = str_repeat($nonEscapableNbspChar, ((int)$level++) * 4) . ucfirst($label);
            $array['value'] = array(self::createMenu($uri, $labels, $level));
        } else {
            $array['label'] = str_repeat($nonEscapableNbspChar, ((int)$level++) * 4) . ucfirst($label);
            $array['value'] = $uri;
        }
        
        return $array;
        
    }
    
}

