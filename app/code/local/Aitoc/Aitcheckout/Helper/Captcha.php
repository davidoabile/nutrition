<?php
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckout_Helper_Captcha extends Aitoc_Aitcheckout_Helper_Abstract
{
    protected $_enabledllow = null;
    protected $_postForm = null;
    protected $_justConfirmed = false;
    protected $_enabled = null;

    public function getFormId()
    {
        if($this->_postForm === null) {
            $this->_postForm = Mage::app()->getFrontController()->getRequest()->getPost('formId');
        }
        return $this->_postForm;
    }

    public function getCaptcha($formId = false)
    {
        if(!$formId) {
            $formId = $this->getFormId();
        }
        return Mage::helper('captcha')->getCaptcha($formId);
    }

    public function checkIfFormAdditionalInfoAllowed()
    {
        return version_compare(Mage::getVersion(), '1.7.0.0', '>=');
    }

    public function checkIfCaptchaEnabled() {
        if($this->_enabled === null) {
            $this->_enabled = $this->checkIfFormAdditionalInfoAllowed() && Aitoc_Aitsys_Abstract_Service::get()->isModuleActive('Mage_Captcha');
        }
        return $this->_enabled;
    }

    /**
     * Generate new captcha for AJAX requests with errors and captcha enabled
     * @param string $formId
     * @return string Captcha image url
     */
    public function generateNewCaptcha($formId = false)
    {
        if(!$this->checkIfCaptchaEnabled())
        {
            return false;
        }
        if(!$formId) {
            $formId = $this->getFormId();
        }
        $captchaModel = $this->getCaptcha($formId);
        Mage::getSingleton('core/layout')->createBlock($captchaModel->getBlockName())->setFormId($formId)->setIsAjax(true)->toHtml();
        return $captchaModel->getImgSrc();
    }

    /**
     * Validate if captcha is enabled and required and add url to captcha image to $result
     * @param Varien_Object $result
     * @param string $formId
     */
    public function renewCaptcha(Varien_Object $result, $formId = false)
    {
        $formId = $formId ? $formId : $this->getFormId();
        if( $this->checkIfCaptchaEnabled() && $this->getCaptcha($formId)->isRequired()) {
            $result->form_id = $formId;
            $result->captcha_image = $this->generateNewCaptcha($formId);
        }
    }

    public function getConfirmedCaptcha($formId = false) {
        $data = Mage::getSingleton('checkout/session')->getCaptchaConfirmed();
        if($data && $formId) {
            return isset($data[$formId]) ? $data[$formId] : false;
        }
        return $data;
    }

    public function setCaptchaAsConfirmed($formId) {
        $data = $this->getConfirmedCaptcha();
        if(!$data) {
            $data = array();
        }
        $data[$formId] = 1;
        Mage::getSingleton('checkout/session')->setCaptchaConfirmed($data);
        $this->_justConfirmed = true;
        return $this;
    }

    /**
     * Check that captcha was confirmed on current request
     */
    public function isJustConfirmed() {
            return $this->_justConfirmed;
    }

    public function isCaptchaPassed($formId) {
        return (bool)$this->getConfirmedCaptcha($formId);
    }

    public function resetConfirmedCaptchas() {
        Mage::getSingleton('checkout/session')->setCaptchaConfirmed(null);
        return $this;
    }

}