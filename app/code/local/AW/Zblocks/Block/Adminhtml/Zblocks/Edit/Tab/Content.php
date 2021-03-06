<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Zblocks
 * @version    2.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Zblocks_Block_Adminhtml_Zblocks_Edit_Tab_Content extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_zblocks_edit_tab_content';
        $this->_blockGroup = 'zblocks';
        $this->_headerText = $this->__('Content Manager');
        $this->_addButtonLabel = $this->__('Add Item');

        parent::__construct();

        $this->_buttons[0]['add']['onclick'] = $this->getRequest()->getParam('id')
            ?('setLocation(\''.$this->getUrl('*/*/editContent', array('block_id' => $this->getRequest()->getParam('id'))).'\')')
            :('alert(\''.$this->__('Please save this block before adding content items. Press &quot;Save And Continue Edit&quot; button to save the block.').'\')');
    }
}