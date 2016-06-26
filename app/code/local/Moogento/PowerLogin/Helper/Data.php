<?php
class Moogento_PowerLogin_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getSiteName()
    {
        $storeName = Mage::getStoreConfig('general/store_information/name');
        if ($storeName) {
            return $storeName;
        }

        $url = Mage::getStoreConfig('web/unsecure/base_url');
        $data = parse_url($url,  PHP_URL_HOST);
        $data = explode('.', $data);
        if (count($data) > 2) {
            array_shift($data);
        }

        return ucfirst(implode('.', $data));
    }

    public function getBackgroundCss()
    {
        $type = Mage::getStoreConfig('moogento_powerlogin/background/type');

        $css = 'body {';

        switch ($type) {
            case Moogento_PowerLogin_Model_Adminhtml_System_Config_Source_Background_Type::DEFAULT_BG:
                $css .= 'background-color: #fff;';
                $css .= 'background-image: url(' . Mage::getDesign()->getSkinUrl('moogento/powerlogin/images/default-bkg.jpg') . ');';
                $css .= 'background-repeat: no-repeat;';
                $css .= 'background-position: bottom center;';
                $css .= 'background-size: cover;';
                break;
            case Moogento_PowerLogin_Model_Adminhtml_System_Config_Source_Background_Type::CUSTOM:
                $css .= 'background-color: #fff;';
                $css .= 'background-image: url(' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'moogento/' . Mage::getStoreConfig('moogento_powerlogin/background/image') . ');';
                $css .= 'background-repeat: ' . Mage::getStoreConfig('moogento_powerlogin/background/repeat') .';';
                $css .= 'background-position: ' . Mage::getStoreConfig('moogento_powerlogin/background/horizontal_align') . ' ' . Mage::getStoreConfig('moogento_powerlogin/background/vertical_align') . ';';
                $css .= 'background-size: ' . Mage::getStoreConfig('moogento_powerlogin/background/size') . ';';
                break;
            case Moogento_PowerLogin_Model_Adminhtml_System_Config_Source_Background_Type::COLOR:
                $css .= 'background-color: ' . Mage::getStoreConfig('moogento_powerlogin/background/color') . ';';
                break;
            default:
                $css .= 'background: none';
        }

        $css .= '}';
        return $css;
    }

    public function getLogoData()
    {
        if (Mage::getStoreConfig('moogento_powerlogin/logo/image')) {
            $logo
                = Mage::getBaseUrl('media') . 'moogento/' . Mage::getStoreConfig('moogento_powerlogin/logo/image');
            $logoPath
                = BP . DS . 'media' . DS . 'moogento' . DS . Mage::getStoreConfig('moogento_powerlogin/logo/image');
            $position = Mage::getStoreConfig('moogento_powerlogin/logo/position');
        } else {
            if (Mage::getConfig()->getModuleConfig('Moogento_Clean')->is('active', 'true')) {
                $logo
                    = Mage::getBaseUrl('skin') . 'adminhtml/default/default/moogento/powerlogin/clean_logo.png';
                $logoPath
                    = BP . DS . 'skin' . DS . 'adminhtml' . DS . 'default' . DS . 'default' . DS . 'moogento' . DS . 'powerlogin' . DS . 'clean_logo.png';
            } else {
                $logo
                    = Mage::getBaseUrl('skin') . 'adminhtml/default/default/moogento/powerlogin/moogento_logo.png';
                $logoPath
                    = BP . DS . 'skin' . DS . 'adminhtml' . DS . 'default' . DS . 'default' . DS . 'moogento' . DS . 'powerlogin' . DS . 'moogento_logo.png';
            }
            $position = 'login_top';
        }

        if (file_exists($logoPath)) {

            return array(
                'logo' => $logo,
                'logoPath' => $logoPath,
                'imageSize' => getimagesize($logoPath),
                'inBox' => strpos($position, 'login') !== false,
                'inMiddle' => strpos($position, 'center') !== false,
                'position' => $position,
            );
        } else {
            return false;
        }
    }
}
	 