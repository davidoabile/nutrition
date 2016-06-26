<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://www.moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        OrderController.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    https://www.moogento.com/License.html
*/ 

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales orders controller
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */

// require_once("Mage/Sales/controllers/OrderController.php");

$magento_base_dir = '';
$magento_base_dir = Mage::getBaseDir('app');
require_once($magento_base_dir . "/code/core/Mage/Sales/controllers/OrderController.php");

class Moogento_Pickpack_Sales_OrderController extends Mage_Sales_OrderController
{
    protected function _isAllowed()
    {
        return true;
    }

    public function mooorderinvoiceAction()
    {
        $orderIds = array();
        $orderIds[0] = $this->getRequest()->getParam('order_id');
        $from_shipment = 'order';
        if (!empty($orderIds)) {
            $methodName = 'getPdfDefault'; // (Mage::getStoreConfig('pickpack_options/wonder_invoice/page_template') == 1) ? 'getPdf2' : 'getPdfDefault';
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->$methodName($orderIds, $from_shipment, 'invoice');
            /*
            Mage::dispatchEvent(
                'moo_pp_invoice_pdf_generate_after',
                array('order_ids' => $orderIds)
            );
            */
            return $this->_prepareDownloadResponse('invoice_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function mooordershipmentAction()
    {
        $orderIds = array();
        $orderIds[0] = $this->getRequest()->getParam('order_id');
        $from_shipment = 'order';

        if (!empty($orderIds)) {
            $methodName = 'getPdfDefault'; //(Mage::getStoreConfig('pickpack_options/wonder/page_template') == 1) ? 'getPdf2' : 'getPdfDefault';
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->$methodName($orderIds, $from_shipment, 'pack');
            /*
            Mage::dispatchEvent(
                'moo_pp_pack_pdf_generate_after',
                array('order_ids' => $orderIds)
            );
            */
            return $this->_prepareDownloadResponse('packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
}
?> 
