<?php
/**
 * Moogento_RetailExpress extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Moogento
 * @package        Moogento_RetailExpress
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * RetailExpress Payment method admin controller
 *
 * @category    Moogento
 * @package     Moogento_RetailExpress
 * @author      Ultimate Module Creator
 */
class Moogento_RetailExpress_Adminhtml_Retailexpress_PaymentmethodController extends Moogento_RetailExpress_Controller_Adminhtml_RetailExpress
{
    /**
     * init the retailexpress payment method
     *
     * @access protected
     * @return Moogento_RetailExpress_Model_Paymentmethod
     */
    protected function _initPaymentmethod()
    {
        $paymentmethodId  = (int) $this->getRequest()->getParam('id');
        $paymentmethod    = Mage::getModel('moogento_retailexpress/paymentmethod');
        if ($paymentmethodId) {
            $paymentmethod->load($paymentmethodId);
        }
        Mage::register('current_paymentmethod', $paymentmethod);
        return $paymentmethod;
    }

    /**
     * default action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('moogento_retailexpress')->__('RetailExpress'))
             ->_title(Mage::helper('moogento_retailexpress')->__('RetailExpress Payment methods'));
        $this->renderLayout();
    }

    /**
     * grid action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * edit retailexpress payment method - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function editAction()
    {
        $paymentmethodId    = $this->getRequest()->getParam('id');
        $paymentmethod      = $this->_initPaymentmethod();
        if ($paymentmethodId && !$paymentmethod->getId()) {
            $this->_getSession()->addError(
                Mage::helper('moogento_retailexpress')->__('This retailexpress payment method no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getPaymentmethodData(true);
        if (!empty($data)) {
            $paymentmethod->setData($data);
        }
        Mage::register('paymentmethod_data', $paymentmethod);
        $this->loadLayout();
        $this->_title(Mage::helper('moogento_retailexpress')->__('RetailExpress'))
             ->_title(Mage::helper('moogento_retailexpress')->__('RetailExpress Payment methods'));
        if ($paymentmethod->getId()) {
            $this->_title($paymentmethod->getName());
        } else {
            $this->_title(Mage::helper('moogento_retailexpress')->__('Add retailexpress payment method'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new retailexpress payment method action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save retailexpress payment method - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('paymentmethod')) {
            try {
                $paymentmethod = $this->_initPaymentmethod();
                $paymentmethod->addData($data);
                $paymentmethod->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('moogento_retailexpress')->__('RetailExpress Payment method was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $paymentmethod->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPaymentmethodData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('moogento_retailexpress')->__('There was a problem saving the retailexpress payment method.')
                );
                Mage::getSingleton('adminhtml/session')->setPaymentmethodData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('moogento_retailexpress')->__('Unable to find retailexpress payment method to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete retailexpress payment method - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $paymentmethod = Mage::getModel('moogento_retailexpress/paymentmethod');
                $paymentmethod->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('moogento_retailexpress')->__('RetailExpress Payment method was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('moogento_retailexpress')->__('There was an error deleting retailexpress payment method.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('moogento_retailexpress')->__('Could not find retailexpress payment method to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete retailexpress payment method - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massDeleteAction()
    {
        $paymentmethodIds = $this->getRequest()->getParam('paymentmethod');
        if (!is_array($paymentmethodIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('moogento_retailexpress')->__('Please select retailexpress payment methods to delete.')
            );
        } else {
            try {
                foreach ($paymentmethodIds as $paymentmethodId) {
                    $paymentmethod = Mage::getModel('moogento_retailexpress/paymentmethod');
                    $paymentmethod->setId($paymentmethodId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('moogento_retailexpress')->__('Total of %d retailexpress payment methods were successfully deleted.', count($paymentmethodIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('moogento_retailexpress')->__('There was an error deleting retailexpress payment methods.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass status change - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massStatusAction()
    {
        $paymentmethodIds = $this->getRequest()->getParam('paymentmethod');
        if (!is_array($paymentmethodIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('moogento_retailexpress')->__('Please select retailexpress payment methods.')
            );
        } else {
            try {
                foreach ($paymentmethodIds as $paymentmethodId) {
                $paymentmethod = Mage::getSingleton('moogento_retailexpress/paymentmethod')->load($paymentmethodId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d retailexpress payment methods were successfully updated.', count($paymentmethodIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('moogento_retailexpress')->__('There was an error updating retailexpress payment methods.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass Enabled change - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massEnabledAction()
    {
        $paymentmethodIds = $this->getRequest()->getParam('paymentmethod');
        if (!is_array($paymentmethodIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('moogento_retailexpress')->__('Please select retailexpress payment methods.')
            );
        } else {
            try {
                foreach ($paymentmethodIds as $paymentmethodId) {
                $paymentmethod = Mage::getSingleton('moogento_retailexpress/paymentmethod')->load($paymentmethodId)
                    ->setEnabled($this->getRequest()->getParam('flag_enabled'))
                    ->setIsMassupdate(true)
                    ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d retailexpress payment methods were successfully updated.', count($paymentmethodIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('moogento_retailexpress')->__('There was an error updating retailexpress payment methods.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass Loyalty Enabled change - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massLoyaltyEnabledAction()
    {
        $paymentmethodIds = $this->getRequest()->getParam('paymentmethod');
        if (!is_array($paymentmethodIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('moogento_retailexpress')->__('Please select retailexpress payment methods.')
            );
        } else {
            try {
                foreach ($paymentmethodIds as $paymentmethodId) {
                $paymentmethod = Mage::getSingleton('moogento_retailexpress/paymentmethod')->load($paymentmethodId)
                    ->setLoyaltyEnabled($this->getRequest()->getParam('flag_loyalty_enabled'))
                    ->setIsMassupdate(true)
                    ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d retailexpress payment methods were successfully updated.', count($paymentmethodIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('moogento_retailexpress')->__('There was an error updating retailexpress payment methods.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass POS enabled change - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massPosEnabledAction()
    {
        $paymentmethodIds = $this->getRequest()->getParam('paymentmethod');
        if (!is_array($paymentmethodIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('moogento_retailexpress')->__('Please select retailexpress payment methods.')
            );
        } else {
            try {
                foreach ($paymentmethodIds as $paymentmethodId) {
                $paymentmethod = Mage::getSingleton('moogento_retailexpress/paymentmethod')->load($paymentmethodId)
                    ->setPosEnabled($this->getRequest()->getParam('flag_pos_enabled'))
                    ->setIsMassupdate(true)
                    ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d retailexpress payment methods were successfully updated.', count($paymentmethodIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('moogento_retailexpress')->__('There was an error updating retailexpress payment methods.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * export as csv - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportCsvAction()
    {
        $fileName   = 'paymentmethod.csv';
        $content    = $this->getLayout()->createBlock('moogento_retailexpress/adminhtml_paymentmethod_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as MsExcel - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportExcelAction()
    {
        $fileName   = 'paymentmethod.xls';
        $content    = $this->getLayout()->createBlock('moogento_retailexpress/adminhtml_paymentmethod_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as xml - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportXmlAction()
    {
        $fileName   = 'paymentmethod.xml';
        $content    = $this->getLayout()->createBlock('moogento_retailexpress/adminhtml_paymentmethod_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @access protected
     * @return boolean
     * @author Ultimate Module Creator
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/moogento_retailexpress/paymentmethod');
    }

    public function importAction()
    {
        $connector = Mage::getModel('moogento_retailexpress/connector');

        $date = date('U') - 86400000;
        try {
            $connector->importPaymentMethods(Mage::getStoreConfig('moogento_retailexpress/general/channel_id'),
                date('Y-m-d\Th:m:s', $date));
            $this->_getSession()->addSuccess(Mage::helper('moogento_retailexpress')->__('Payment methods imported successfully'));
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('moogento_retailexpress')->__($e->getMessage()));
        }

        $this->_redirectReferer();
    }
}
