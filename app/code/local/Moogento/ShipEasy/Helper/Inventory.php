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
 * File        Inventory.php
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_ShipEasy_Helper_Inventory extends Mage_Core_Helper_Abstract
{
    const RESULT_ALL_AVAILABLE = 1;
    const RESULT_PARTIALLY_AVAILABLE = 0;
    const RESULT_ALL_UNAVAILABLE = -1;

    protected function _checkItemQty($stockItem, $qty, $stockWarningQty = 0)
    {
        if (!$stockItem->getManageStock()) {
            return self::RESULT_ALL_AVAILABLE;
        }

        if ($stockItem->getQty() <= $stockWarningQty) {
            return self::RESULT_ALL_UNAVAILABLE;
        }

        if (($stockItem->getQty() > $stockWarningQty) && (($stockItem->getQty() - $qty) < $stockWarningQty)) {
            return self::RESULT_PARTIALLY_AVAILABLE;
        }

        return self::RESULT_ALL_AVAILABLE;
    }

    public function checkAvailability($productId, $requestedQty, $criteria = 'status', $stockWarningQty = 0)
    {
        $stockItem    = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);

        if (!$stockItem->getItemId()) {
            return self::RESULT_ALL_UNAVAILABLE;
        }

        $result      = self::RESULT_ALL_UNAVAILABLE;
        if ($criteria == 'status') {
            $isInStock = $stockItem->getIsInStock();
            if ($isInStock) {
                $result = self::RESULT_ALL_AVAILABLE;
            }
        } else {
            $result = $this->_checkItemQty($stockItem, $requestedQty, $stockWarningQty);
        }

        return $result;
    }
}
