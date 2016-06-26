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
 * RetailExpress Payment method resource model
 *
 * @category    Moogento
 * @package     Moogento_RetailExpress
 * @author      Ultimate Module Creator
 */
class Moogento_RetailExpress_Model_Resource_Paymentmethod extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * constructor
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function _construct()
    {
        $this->_init('moogento_retailexpress/paymentmethod', 'entity_id');
    }

    protected $_serializableFields
        = array(
            'magento_payment' => array(
                array(),
                array(),
                true
            )
        );
}
