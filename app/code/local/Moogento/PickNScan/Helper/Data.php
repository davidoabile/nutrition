<?php

class Moogento_PickNScan_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_users = null;
    protected $_images = array();

    public function isAllowed($action, $base = 'moogento/pickscan')
    {
        return Mage::getSingleton('admin/session')->isAllowed($base . '/' . $action);
    }

    public function getUsers()
    {
        if (is_null($this->_users)) {
            foreach (Mage::getModel('admin/user')->getCollection() as $user) {
                $this->_users[$user->getUserId()]  = $user->getFirstname() . ' ' . $user->getLastname();
            }
        }

        return $this->_users;
    }

    public function isAvailable()
    {
        return Mage::helper('core')->isModuleEnabled('Moogento_Pickpack');
    }

    public function assignOrder($order, $userId) {
        if (!count($order->getAllVisibleItems()) || $order->getIsVirtual()) {
            return false;
        }
        $picking = Mage::getModel('moogento_pickscan/picking')->load($order->getEntityId());

        if ($picking->getEntityId()) {
            if ($picking->getUserId() == $userId) return $picking;
            return false;
        }
        $users = $this->getUsers();
        $picking->setData(array(
            'entity_id' => $order->getEntityId(),
            'user_id' => $userId,
            'results' => $this->__('Assigned to ') . $users[$userId],
        ));
        $picking->isObjectNew(true);
        $picking->save();
        $picking->isObjectNew(false);

        return $picking;
    }

    public function getColumnValue($order)
    {
        $picking = Mage::getModel('moogento_pickscan/picking')->load($order->getEntityId());
        $order->setPicking($picking);
        if ($order->getResults()) {
            $result = str_replace("Started", "Start", $order->getResults());
            $result = str_replace("Finished", "Finish", $result);
            return $result;
        } else {
            return Mage::helper('moogento_pickscan')->__('Not Assigned');
        }
    }

    public function getAssignedOrders($useQuickPick = true)
    {
        $user = Mage::getSingleton('admin/session');
        $userId = $user->getUser()->getUserId();
        $limit = Mage::getStoreConfig('moogento_pickscan/settings/assign_limit');

        $collection = Mage::getModel('moogento_pickscan/picking')->getCollection();
        $collection->addFieldToFilter('user_id', $userId);
        $collection->addFieldToFilter('finished', array('null' => true));
        $collection->getSelect()->limit($limit);
        $collection->load();

        $count = $collection->count();
        if ($count > 0) {
            if ($useQuickPick && $collection->count() < Mage::getStoreConfig('moogento_pickscan/settings/assign_limit')) {
                try {
                    if ($this->_quickPick($limit - $count)) {
                        return $this->getAssignedOrders(false);
                    }
                } catch (Exception $e) {

                }
            }
            return $collection;
        } else {
            if ($useQuickPick && $this->_quickPick()) {
                return $this->getAssignedOrders(false);
            }
            return array();
        }
    }

    protected function _quickPick($limit = null)
    {
        $sort = Mage::getStoreConfig('moogento_pickscan/condition/sort');
        $dir = Mage::getStoreConfig('moogento_pickscan/condition/dir');
        $filter = Mage::getStoreConfig('moogento_pickscan/condition/filter');

        if (!$filter) {
            throw new Mage_Core_Exception($this->__('You need to save a Preset Filter in <a href="%s">config</a> to use pickAssigned functionality', Mage::helper('adminhtml')->getUrl('*/system_config/edit', array('section' => 'moogento_pickscan'))));
        }
        $layout = Mage::getModel('core/layout');

        $block = $layout->createBlock('moogento_core/adminhtml_sales_order_grid');
        $block->setTemplate('');
        $block->toHtml();

        $columns = $block->getColumns();

        $collection = Mage::getResourceModel('sales/order_grid_collection');
        Mage::dispatchEvent('moogento_core_order_grid_collection_prepare',
            array('grid' => $block, 'collection' => $collection));

        if ($sort && isset($columns[$sort]) && $columns[$sort]->getIndex()) {
            $dir = (strtolower($dir)=='desc') ? 'desc' : 'asc';
            $columnIndex = $columns[$sort]->getFilterIndex() ?
                $columns[$sort]->getFilterIndex() : $columns[$sort]->getIndex();
            $collection->setOrder($columnIndex, strtoupper($dir));
        }

        if ($filter) {
            $data = Mage::helper('adminhtml')->prepareFilterString($filter);
            foreach ($columns as $columnId => $column) {
                if (isset($data[$columnId])
                    && (!empty($data[$columnId]) || strlen($data[$columnId]) > 0)
                    && $column->getFilter()
                ) {
                    $column->getFilter()->setValue($data[$columnId]);
                    $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
                    if ($column->getFilterConditionCallback()) {
                        call_user_func($column->getFilterConditionCallback(), $collection, $column);
                    } else {
                        $cond = $column->getFilter()->getCondition();
                        if ($field && isset($cond)) {
                            $collection->addFieldToFilter($field , $cond);
                        }
                    }
                }
            }
        }

        if (Mage::helper('core')->isModuleEnabled('Moogento_shipEasy')) {
            $collection->getSelect()->where('main_table.order_id not in (select entity_id from ' . Mage::getSingleton('core/resource')->getTableName('moogento_pickscan/picking') . ' WHERE user_id is not NULL)');
            $collection->getSelect()->group('main_table.order_id');
        } else {
            $collection->getSelect()->where('main_table.entity_id not in (select entity_id from ' . Mage::getSingleton('core/resource')->getTableName('moogento_pickscan/picking') . ' WHERE user_id is not NULL)');
        }
        if (is_null($limit)) {
            $limit = Mage::getStoreConfig('moogento_pickscan/settings/assign_limit');
        }
        $collection->getSelect()->limit($limit);

        $collection->load();
        if (count($collection) > 0) {
            $user = Mage::getSingleton('admin/session');
            $userId = $user->getUser()->getUserId();
            foreach ($collection as $order) {
                $this->assignOrder($order, $userId);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function getOrderJsonData($order) {
        $data = array(
            'id' => $order->getIncrementId(),
            'inner_id' => $order->getId(),
            'products' => array(),
            'customer_comments' => array(),
        );

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($order->getStoreId());

        $sortCode = Mage::getStoreConfig('moogento_pickscan/settings/sort_by');
        $sortLimit = Mage::getStoreConfig('moogento_pickscan/settings/sort_by_limit');
        $barcodeCode = Mage::getStoreConfig('moogento_pickscan/settings/barcode');
        $custom1Code = Mage::getStoreConfig('moogento_pickscan/settings/custom_1');
        $custom2Code = Mage::getStoreConfig('moogento_pickscan/settings/custom_2');
        $custom3Code = Mage::getStoreConfig('moogento_pickscan/settings/custom_2');
        $custom4Code = Mage::getStoreConfig('moogento_pickscan/settings/custom_2');
        $titleCode = Mage::getStoreConfig('moogento_pickscan/settings/show_in_title');
        if ($titleCode == 'custom') {
            $titleCode = Mage::getStoreConfig('moogento_pickscan/settings/show_in_title_custom');
        }

        $customer_comments = Mage::getStoreConfigFlag('moogento_pickscan/settings/customer_comments');
        $admin_comments = Mage::getStoreConfigFlag('moogento_pickscan/settings/admin_comments');

        if ($customer_comments && $order->getGiftMessageId()) {
            $giftMessage = Mage::helper('giftmessage/message')->getGiftMessage(
                $order->getGiftMessageId()
            );
            $data['customer_comments'] = array(
                '<span class="comment-quote">' . $giftMessage->getMessage() . '</span><br/>' .
                'From: ' . $giftMessage->getSender() . '&nbsp;&nbsp;&nbsp;&nbsp;' . 'To: ' . $giftMessage->getRecipient(),
            );
        }

        if ($admin_comments) {
            $data['admin_comments'] = array();
            foreach ($order->getStatusHistoryCollection() as $comment) {
                if ($comment->getComment()) {
                    $data['admin_comments'][] = '<span class="comment-quote">' . $comment->getComment() . '</span> (at ' . $comment->getCreatedAt() . ')';
                }
            }
        }

        $allowanceMultiplier = (float)Mage::getStoreConfig('moogento_pickscan/manual_substitution/allowance_multiplier');
        $data['currency'] = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())->getSymbol();

        $excludedProducts = array();
        if (Mage::getStoreConfig('moogento_pickscan/settings/skip_products')) {
            $excludedProducts = preg_split("/\r?\n/",
                Mage::getStoreConfig('moogento_pickscan/settings/skip_products'));
            array_walk($excludedProducts, array($this, 'cleanSkus'));
        }

        /** @var Mage_Sales_Model_Order_Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getIsVirtual()) {
                continue;
            }
            if (in_array($item->getSku(), $excludedProducts)) {
                continue;
            }

            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $children = $item->getChildrenItems();
                $simpleProduct = false;
                if (count($children) > 0) {
                    $simpleProduct = $children[0]->getProduct();
                }
                $options = $item->getProductOptions();
                if ($simpleProduct && !in_array($simpleProduct->getSku(), $excludedProducts)) {
                    $tmpData = array(
                        'id' => $simpleProduct->getId(),
                        'sku' => $simpleProduct->getSku(),
                        'barcode' => $this->_getAttributeValue($simpleProduct, $barcodeCode),
                        'name' => $simpleProduct->getName(),
                        'qty' => (int)$item->getQtyOrdered(),
                        'attributes_info' => $options['attributes_info'],
                        'custom_sort' => $this->_getAttributeValue($simpleProduct, $sortCode),
                        'custom_sort_limit' => $sortLimit,
                        'custom_1' => $custom1Code ? $this->_getAttributeValue($simpleProduct, $custom1Code) : '',
                        'custom_2' => $custom2Code ? $this->_getAttributeValue($simpleProduct, $custom2Code) : '',
                        'custom_3' => $custom2Code ? $this->_getAttributeValue($simpleProduct, $custom3Code) : '',
                        'custom_4' => $custom2Code ? $this->_getAttributeValue($simpleProduct, $custom4Code) : '',
                        'image' => $this->_getProductImage($product),
                        'titleText' => $this->_getAttributeValue($simpleProduct, $titleCode),
                        'price' => round($item->getPrice(), 2),
                        'allowance_price' => round($item->getPrice() * $allowanceMultiplier,  2),
                    );

                    if ($customer_comments && $item->getGiftMessageId()) {
                        $giftMessage = Mage::helper('giftmessage/message')->getGiftMessage(
                            $item->getGiftMessageId()
                        );
                        $tmpData['customer_comments'] = array(
                            '<span class="comment-quote">' . $giftMessage->getMessage() . '</span><br/>' .
                            'From: ' . $giftMessage->getSender() . '&nbsp;&nbsp;&nbsp;&nbsp;' . 'To: ' . $giftMessage->getRecipient(),
                        );
                    }

                    $data['products'][] = $tmpData;
                }
            } else if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $options = $item->getProductOptions();
                $selections = $options['info_buyRequest']['bundle_option'];

                if ($customer_comments && $item->getGiftMessageId()) {
                    $giftMessage = Mage::helper('giftmessage/message')->getGiftMessage(
                        $item->getGiftMessageId()
                    );
                    $data_customer_comments = array(
                        '<span class="comment-quote">' . $giftMessage->getMessage() . '</span><br/>' .
                        'From: ' . $giftMessage->getSender() . '&nbsp;&nbsp;&nbsp;&nbsp;' . 'To: ' . $giftMessage->getRecipient(),
                    );
                } else {
                    $data_customer_comments = array();
                }

                foreach ($options['bundle_options'] as $optionData) {
                    $selection = Mage::getModel('bundle/selection')->load($selections[$optionData['option_id']]);
                    $product = Mage::getModel('catalog/product')->load($selection->getProductId());
                    if (in_array($product->getSku(), $excludedProducts)) {
                        continue;
                    }
                    $tmpData = array(
                        'id' => $product->getId(),
                        'sku' => $product->getSku(),
                        'barcode' => $this->_getAttributeValue($product, $barcodeCode),
                        'name' => $product->getName(),
                        'qty' => (int)$optionData['value'][0]['qty'],
                        'custom_sort' => $this->_getAttributeValue($product, $sortCode),
                        'custom_sort_limit' => $sortLimit,
                        'custom_1' => $custom1Code ? $this->_getAttributeValue($product, $custom1Code) : '',
                        'custom_2' => $custom2Code ? $this->_getAttributeValue($product, $custom2Code) : '',
                        'custom_3' => $custom2Code ? $this->_getAttributeValue($product, $custom3Code) : '',
                        'custom_4' => $custom2Code ? $this->_getAttributeValue($product, $custom4Code) : '',
                        'image' => $this->_getProductImage($product),
                        'customer_comments' => $data_customer_comments,
                        'titleText' => $this->_getAttributeValue($product, $titleCode),
                        'price' => round($product->getPrice(), 2),
                        'allowance_price' => round($product->getPrice() * $allowanceMultiplier, 2),
                    );
                    $data['products'][] = $tmpData;
                }
            } else {
                if ($product->getId() && !in_array($product->getSku(), $excludedProducts)) {
                    $tmpData = array(
                        'id' => $product->getId(),
                        'sku' => $product->getSku(),
                        'barcode' => $this->_getAttributeValue($product, $barcodeCode),
                        'name' => $product->getName(),
                        'qty' => (int)$item->getQtyOrdered(),
                        'custom_sort' => $this->_getAttributeValue($product, $sortCode),
                        'custom_sort_limit' => $sortLimit,
                        'custom_1' => $custom1Code ? $this->_getAttributeValue($product, $custom1Code) : '',
                        'custom_2' => $custom2Code ? $this->_getAttributeValue($product, $custom2Code) : '',
                        'custom_3' => $custom2Code ? $this->_getAttributeValue($product, $custom3Code) : '',
                        'custom_4' => $custom2Code ? $this->_getAttributeValue($product, $custom4Code) : '',
                        'image' => $this->_getProductImage($product),
                        'titleText' => $this->_getAttributeValue($product, $titleCode),
                        'price' => round($product->getPrice() ,2),
                        'allowance_price' => round($product->getPrice() * $allowanceMultiplier, 2),
                    );

                    if ($customer_comments && $item->getGiftMessageId()) {
                        $giftMessage = Mage::helper('giftmessage/message')->getGiftMessage(
                            $item->getGiftMessageId()
                        );
                        $tmpData['customer_comments'] = array(
                            '<span class="comment-quote">' . $giftMessage->getMessage() . '</span><br/>' .
                            'From: ' . $giftMessage->getSender() . '&nbsp;&nbsp;&nbsp;&nbsp;' . 'To: ' . $giftMessage->getRecipient(),
                        );
                    }

                    $data['products'][] = $tmpData;
                }
            }
        }
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        if (!count($data['products'])) return false;
        return $data;
    }

    protected function _getProductImage($product)
    {
        $url = Mage::helper('catalog/image')->init($product, 'image');
        $url->placeholder(Mage::getBaseDir('skin')."/adminhtml/default/default/moogento/general/images/default_image.png");
        $url = (string)$url;
        if (!$product->getImage() || $product->getImage() == 'no_selection') {
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . "/adminhtml/default/default/moogento/general/images/default_image.png";
        }

        if (!isset($this->_images[$url])) {
            $model = Mage::getModel('catalog/product_image');
            $model->setDestinationSubdir('image');
            $file = $product->getData($model->getDestinationSubdir());
            $model->setBaseFile($file);
            $file_path = (!$file || $file == "no_selection") ? (Mage::getBaseDir('skin')."/adminhtml/default/default/moogento/general/images/default_image.png") : $model->getNewFile();

            $imageData = base64_encode(file_get_contents($file_path));
            $src = 'data: '. $this->mime_content_type($model->getNewFile()).';base64,'.$imageData;

            $this->_images[$url] = $src;
        }

        return $url;
    }

    protected function mime_content_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

// images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

// archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

// audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

// adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

// ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

// open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $filenames = explode('.',$filename);
        $ext = strtolower(array_pop($filenames));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }

    public function getImagesOffline()
    {
        return $this->_images;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param $code
     * @return string
     */
    protected function _getAttributeValue($product, $code)
    {
        $attribute = $product->getResource()->getAttribute($code);
        $inputType = $attribute->getFrontend()->getInputType();
        $value = $product->getData($code);

        if ($inputType == 'multiselect') {
            if (!is_array($value)) {
                $value = array($value);
            }
            $result = array();
            foreach ($value as $val) {
                $result[] = $attribute->getSource()->getOptionText($val) ? $attribute->getSource()->getOptionText($val) : 'N.A.';
            }
            return implode(',', $result);
        } else if ($attribute->usesSource()) {
            return $attribute->getSource()->getOptionText($value) ? $attribute->getSource()->getOptionText($value) : 'N.A.';
        }

        return $value ? $value : 'N.A.';
    }

    public function getSubstitutionFlag()
    {
        $flagData = array();
        $flag = Mage::getStoreConfig('moogento_pickscan/manual_substitution/set_flag');
        if ($flag) {
            $flagData['number'] = $flag;
            $flagData['flag_label'] =  $flag == 1 ? Mage::helper('moogento_pickscan')->__('Flags') : ($flag == 2 ? Mage::helper('moogento_pickscan')->__('Custom') : Mage::helper('moogento_pickscan')->__('Printed?'));
            $flagData['value'] = Mage::getStoreConfig('moogento_pickscan/manual_substitution/set_flag_' . $flag);
            if ($flagData['value'] == 'custom') {
                $flagData['value_custom'] = Mage::getStoreConfig('moogento_pickscan/manual_substitution/set_flag_' . $flag . '_custom');
            }
            if (strpos($flagData['value'], '|') !== false) {
                list($label, $color) = explode('|', $flagData['value']);
                $flagData['label'] = $label;

            } else {
                $flagData['label'] = $flagData['value'];
            }

            return $flagData;
        } else {
            return false;
        }
    }

    public function getIgnoreFlag()
    {
        $flagData = array();
        $flag = Mage::getStoreConfig('moogento_pickscan/ignore_error/set_flag');
        if ($flag) {
            $flagData['number'] = $flag;
            $flagData['flag_label'] =  $flag == 1 ? Mage::helper('moogento_pickscan')->__('Flags') : ($flag == 2 ? Mage::helper('moogento_pickscan')->__('Custom') : Mage::helper('moogento_pickscan')->__('Printed?'));
            $flagData['value'] = Mage::getStoreConfig('moogento_pickscan/ignore_error/set_flag_' . $flag);
            if ($flagData['value'] == 'custom') {
                $flagData['value_custom'] = Mage::getStoreConfig('moogento_pickscan/ignore_error/set_flag_' . $flag . '_custom');
            }
            if (strpos($flagData['value'], '|') !== false) {
                list($label, $color) = explode('|', $flagData['value']);
                $flagData['label'] = $label;

            } else {
                $flagData['label'] = $flagData['value'];
            }

            return $flagData;
        } else {
            return false;
        }
    }

    public function getCustomPreset($att_number = 1)
    {

        $preset_return = array();
        if($att_number == 1)
            $configSuffix = 'szy_custom_attribute_preset';
        else
            if($att_number == 2)
                $configSuffix = 'szy_custom_attribute2_preset';
            else
                $configSuffix = 'szy_custom_attribute3_preset';
        $configPresets = Mage::getStoreConfig('moogento_shipeasy/grid/' . $configSuffix);
        $configPresets = explode("\n", $configPresets);

        $presets = array();
        foreach($configPresets as $preset) {
            $preset = trim($preset);
            if (empty($preset)) {
                continue;
            }
            if (strpos($preset, '|') !== false) {
                list($label, $color) = explode('|', $preset);
                $presets[$preset] = $label;

            } else {
                $presets[$preset] = $preset;
            }
        }
        $presets['custom'] = 'New Value';

        return $presets;
    }

    public static function setPickscanFilter($collection, $column)
    {
        if (mb_strtolower(trim($column->getFilter()->getValue())) == mb_strtolower(Mage::helper('moogento_pickscan')->__('Not Assigned'))) {
            $collection->getSelect()->joinLeft(
                array('pick_scan' => Mage::getSingleton('core/resource')->getTableName('moogento_pickscan/picking')),
                'pick_scan.entity_id = main_table.entity_id',
                array('results')
            );
            $collection->addFieldToFilter(
                "pick_scan.results",
                array(
                    'null' => true
                )
            );
        } else {
            $collection->getSelect()->join(
                array('pick_scan' => Mage::getSingleton('core/resource')->getTableName('moogento_pickscan/picking')),
                'pick_scan.entity_id = main_table.entity_id',
                array('results')
            );
            $collection->addFieldToFilter(
                "pick_scan.results",
                array(
                    'like' => "%{$column->getFilter()->getValue()}%"
                )
            );
        }
    }

    public static function setPickFilter($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value) {
            if ($value < 0) {
                $collection->getSelect()->where('pick.entity_id IS NULL');
            } else {
                $collection->addFieldToFilter(
                    "pick.status",
                    array(
                        'eq' => $value
                    )
                );
            }
        }
    }

    public function getBackgroundCss()
    {
        $type = Mage::getStoreConfig('moogento_pickscan/background/type');

        $css = 'body {';

        switch ($type) {
            case Moogento_PickNScan_Model_Adminhtml_System_Config_Source_Background_Type::DEFAULT_BG:
                $css .= 'background-color: #fff;';
                $css .= 'background-image: url(' . Mage::getDesign()->getSkinUrl('moogento/pickscan/images/default-bkg.jpg') . ');';
                $css .= 'background-repeat: no-repeat;';
                $css .= 'background-position: bottom center;';
                $css .= 'background-size: cover;';
                break;
            case Moogento_PickNScan_Model_Adminhtml_System_Config_Source_Background_Type::CUSTOM:
                $css .= 'background-color: #fff;';
                $css .= 'background-image: url(' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'moogento/' . Mage::getStoreConfig('moogento_pickscan/background/image') . ');';
                $css .= 'background-repeat: ' . Mage::getStoreConfig('moogento_pickscan/background/repeat') .';';
                $css .= 'background-position: ' . Mage::getStoreConfig('moogento_pickscan/background/horizontal_align') . ' ' . Mage::getStoreConfig('moogento_pickscan/background/vertical_align') . ';';
                $css .= 'background-size: ' . Mage::getStoreConfig('moogento_pickscan/background/size') . ';';
                break;
            case Moogento_PickNScan_Model_Adminhtml_System_Config_Source_Background_Type::COLOR:
                $css .= 'background-color: ' . Mage::getStoreConfig('moogento_pickscan/background/color') . ';';
                break;
            default:
                $css .= 'background: none';
        }

        $css .= '}';
        return $css;
    }

    public function getAuthData()
    {
        $result = array();

        foreach (Mage::getResourceModel('admin/user_collection') as $user) {
            $key = substr(md5($user->getUsername()) ,0, 6);
            $result[$key] = $user->getFirstname() . ' ' . $user->getLastname();
        }

        return $result;
    }

    public function cleanSkus(&$sku) {
        $sku = trim($sku);
    }
}