<?php

class Moogento_PowerLogin_Model_Observer
{

    public function redirect_user_to_home_page($observer)
    {
        $user = $observer->getUser();

        $homepage = $user->getHomePage() ? $this->getStartupPageUrl($user->getHomePage()) : $this->getStartupPageUrl($user->getRole()->getHomePage());
        
        if ($homepage) {
            Mage::app()->getResponse()->setRedirect(str_replace('index.php//', 'index.php/admin/', Mage::helper('adminhtml')->getUrl($homepage)))->sendResponse();
            die();
        }

        return;
    }
    
    public function getStartupPageUrl($homepage)
    {
        $startupPage = $homepage;
        $aclResource = 'admin/' . $startupPage;
        
        if(strpos($startupPage, '_') !== false){
            return $aclResource;
        }
        
        if (Mage::getSingleton('admin/session')->isAllowed($aclResource)) {
            $nodePath = 'menu/' . join('/children/', explode('/', $startupPage)) . '/action';
            $url = (string) Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode($nodePath);
            if ($url) {
                return $url;
            }
        }
        return null;
    }

    public function controller_action_layout_load_before($observer)
    {
        if (Mage::getStoreConfigFlag('moogento_powerlogin/settings/use_custom_login')) {
            $update = $observer->getEvent()->getLayout()->getUpdate();
            if (in_array('adminhtml_index_login', $update->getHandles())) {
                $update->addHandle('moogento_powerlogin_custom_login');
            }
        }
    }

}
