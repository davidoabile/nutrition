<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Import.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Helper_Track_Import extends Mage_Core_Helper_Abstract
{
    protected function _addParams(&$xml, $params)
    {
        $lastVarPosition = strrpos($xml, '</var>') + 6;
        $firstXmlPart = substr($xml, 0 , $lastVarPosition);
        $secondXmlPart = substr($xml, $lastVarPosition);

        $xml = $firstXmlPart;
        foreach($params as $key => $value) {
            $xml .= '<var name="'.$key.'"><![CDATA['.$value.']]></var>';
        }
        $xml .= $secondXmlPart;

        return $xml;
    }

    public function getImportProfile($path, $fileName, $additionalParseParams = array())
    {
        $profile = Mage::getModel('moogento_shipeasy/dataflow_profile')->load(0);

        if ((int)$profile->getProfileId() !== 0 ){
            die('error');
        }

        $actionsXml = $profile->getActionsXml();
        $actionsXml = str_replace('{{path}}', $path, $actionsXml);
        $actionsXml = str_replace('{{filename}}', $fileName, $actionsXml);

        if (count($additionalParseParams)) {
            $this->_addParams($actionsXml, $additionalParseParams);
        }

        $profile->setActionsXml($actionsXml);

        $guiData = $profile->getGuiData();
        $guiData['file']['filename'] = $fileName;
        $guiData['file']['path'] = $path;
        $profile->setGuiData($guiData);

        return $profile;
    }
}
