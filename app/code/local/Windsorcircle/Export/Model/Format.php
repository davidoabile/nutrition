<?php
class Windsorcircle_Export_Model_Format extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('windsorcircle_export/format');

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
    }

    protected function createHeaders()
    {
        $returnArray = array('ID', 'StoreID', 'Active', 'VSKU', 'PSKU', 'Product_Type', 'Title', 'Description', 'Link',
            'Image_link', 'Price', 'Sale_price', 'Sale_Price_Effective_Date', 'Brand', 'Availability', 'Quantity',
            'Shipping_Weight');

        $customAttributes = Mage::helper('windsorcircle_export')->getCustomAttributes('product');
        foreach ($customAttributes as $customAttribute) {
            $returnArray['custom_product_' . $customAttribute] = 'custom_product_' . $customAttribute;
        }

        return $returnArray;
    }

    /**
     * Put Order Data in tab-delimited format
     * @param  array  $orderData
     * @throws Exception
     * @return string String of Order Data File
     */
    public function formatOrderData(array $orderData, $prefix = '_Orders_'){
        $file = Mage::getBaseDir('tmp') . DS . Mage::getStoreConfig('windsorcircle_export_options/messages/client_name') . $prefix . Mage::registry('windsor_file_timestamp') . '.txt';
        $handle = fopen($file, 'w');

        if($handle == false){
            throw new Exception('Cannot create Orders file');
        }

        // Output data
        foreach($orderData as $order){
            fputcsv($handle, $order, "\t");
        }

        fclose($handle);

        return $file;
    }

    /**
     * Put Order Details Data in tab-delimited format
     * @param  array  $orderDetailsData
     * @throws Exception
     * @return string String of Order Details Data File
     */
    public function formatOrderDetailsData(array $orderDetailsData, $prefix = '_Order_'){
        $file = Mage::getBaseDir('tmp') . DS . Mage::getStoreConfig('windsorcircle_export_options/messages/client_name') . $prefix . 'Details_' . Mage::registry('windsor_file_timestamp') . '.txt';
        $handle = fopen($file, 'w');

        if($handle == false){
            throw new Exception('Cannot create Order Details File');
        }

        // Output data
        foreach($orderDetailsData as $orderDetails){
            foreach($orderDetails as $item){
                fputcsv($handle, $item, "\t");
            }
        }

        fclose($handle);

        return $file;
    }

    /**
     *
     * Create product data from last export date and add to lastexport file
     * @param string $lastExport
     * @param string $lastExpDate
     * @throws Exception
     */
    public function createProductData($lastExport,$lastExpDate)
    {
        $handle = fopen($lastExport, 'a');

        if($handle == false){
            throw new Exception('Cannot create Products file');
        }

        $headers[] = $this->createHeaders();

        //don't put headers if the file was already created earlier
        if($lastExpDate == "0000-00-00 00:00:00") {
            fputcsv($handle, $headers[0] , "\t");
        }

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $products = Mage::getModel('catalog/product')->getCollection()
                            ->addAttributeToSelect(array_unique(array_merge($this->getDefaultAttributes(), Mage::helper('windsorcircle_export')->getCustomAttributes('product'))), 'left');

        // If no custom attribute for brand then we will use default brand attribute
        $attribute = Mage::getStoreConfig('windsorcircle_export_options/messages/brand_attribute');
        if (empty($attribute)) {
            $brand = Mage::getModel('catalog/resource_eav_attribute')
                        ->loadByCode('catalog_product', 'brand');
            if($brand->getId() !== null) {
                $products->addAttributeToSelect('brand', 'left');
            }
        } else {
            $brand = Mage::getModel('catalog/resource_eav_attribute')
                        ->loadByCode('catalog_product', $attribute);
            if($brand->getId() !== null) {
                $products->addAttributeToSelect($attribute, 'left');
            }
        }

        $products->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')
            ->joinField('backorders', 'cataloginventory/stock_item', 'backorders', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')
            ->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')
            ->joinField('super_link', 'catalog/product_super_link', 'parent_id', 'product_id=entity_id', null, 'left')
            ->joinField('relation', 'catalog/product_relation', 'parent_id', 'child_id=entity_id', null, 'left')
            ->joinField('parent_sku', 'catalog/product', 'sku', 'entity_id=super_link', null, 'left')
            ->joinField('parent_sku_relation', 'catalog/product', 'sku', 'entity_id=relation', null, 'left')
            ->addAttributeToSort('type_id', 'ASC');

        if($lastExpDate != "0000-00-00 00:00:00") {
            $products->addAttributeToFilter('created_at',array('gteq' => $lastExpDate));
        }

        Mage::getSingleton('core/resource_iterator')->walk($products->getSelect(), array(array($this, 'productCallback')), array('arg1' => '====', 'handle' => $handle));

        fclose($handle);

        return;
    }

    /**
     *
     * Product Call Back Function for magento's core resource iterator walk function
     * @param array $args
     */
    public function productCallback($args) {
        $product = Mage::getModel('catalog/product');
        $product->setData($args['row']);
        Mage::getSingleton('windsorcircle_export/products')->getProductsAdvanced($product, $args['handle']);
    }

    /**
     * Put Product Data in tab-delimited format
     * @param array $products
     * @throws Exception
     * @return string String of Product Data File
     */
    public function formatProductData(array $products){
        $file = Mage::getBaseDir('tmp') . DS . Mage::getStoreConfig('windsorcircle_export_options/messages/client_name') . '_Products_' . Mage::registry('windsor_file_timestamp') . '.txt';
        $handle = fopen($file, 'w');

        if($handle == false){
            throw new Exception('Cannot create Products file');
        }

        // Output data
        foreach($products as $product){
            fputcsv($handle, $product, "\t");
        }

        fclose($handle);

        return $file;
    }

    /**
     * Get Products data
     * @return string File of export
     */
    public function advancedFormatProductData($additionalProductIds = array()) {

        $lastExportFolder = Mage::getBaseDir('media') . DS . 'windsorcircle_export';
        $lastExport = $lastExportFolder . DS . 'lastexport.txt';
        $updatedProd = $lastExportFolder . DS . 'updated.txt';
        if (!file_exists($lastExportFolder)) { mkdir($lastExportFolder); }

        $newExport = Mage::getBaseDir('tmp') . DS . Mage::getStoreConfig('windsorcircle_export_options/messages/client_name') . '_Products_' . Mage::registry('windsor_file_timestamp') . '.txt';
        if (!file_exists($lastExport)) {
            $lastExpDate = "0000-00-00 00:00:00";
            $this->createProductData($lastExport,$lastExpDate);
            if (file_exists($updatedProd)) { unlink($updatedProd); }
        } else {
            $lastExpDate = date("Y-m-d H:i:s", filemtime($lastExport));
            $this->createProductData($lastExport,$lastExpDate);
            //next step: refresh and delete
            $this->applyPatch($updatedProd,$lastExport, $additionalProductIds);
        }
        //copy to tmp folder
        copy($lastExport,$newExport);

        return $newExport;
    }

    /**
     * This is called from the Background controller and gets product data only if the updated.txt file is set or
     * the lastexport.txt file is not present
     *
     * @param array $additionalProductIds
     *
     * @return string
     * @throws Exception
     */
    public function getProductDataIfUpdated($additionalProductIds = array()) {
        $lastExportFolder = Mage::getBaseDir('media') . DS . 'windsorcircle_export';
        $lastExport = $lastExportFolder . DS . 'lastexport.txt';
        $updatedProd = $lastExportFolder . DS . 'updated.txt';
        if (!file_exists($lastExportFolder)) { mkdir($lastExportFolder); }

        $newExport = Mage::getBaseDir('tmp') . DS . Mage::getStoreConfig('windsorcircle_export_options/messages/client_name') . '_Products_' . Mage::registry('windsor_file_timestamp') . '.txt';
        if (!file_exists($lastExport)) {
            $lastExpDate = "0000-00-00 00:00:00";
            $this->createProductData($lastExport, $lastExpDate);
            if (file_exists($updatedProd)) { unlink($updatedProd); }

            //copy to tmp folder
            copy($lastExport,$newExport);

            return $newExport;
        } elseif (file_exists($updatedProd) || !empty($additionalProductIds)) {
            $this->applyPatch($updatedProd, $lastExport, $additionalProductIds);

            //copy to tmp folder
            copy($lastExport,$newExport);

            return $newExport;
        }

        return;
    }

    /**
     * Refresh existing file using the file with updated products ids
     * @param $patch_file_name
     * @param $data_file_name
     * @param array $additonalProductIds
     * @throws Exception
     */
    public function applyPatch($patch_file_name, $data_file_name, $additonalProductIds = array()) {
        $patch = array();

        if(file_exists($patch_file_name)) {
            $patchFile = file($patch_file_name);
            foreach($patchFile as $line) {
                preg_match('/(-|!)(\d+)/', $line, $match);
                if(isset($match[1]) && !empty($match[1])) {
                     $patch[$match[1]][$match[2]] = $match[2];
                }
            }
            if (!empty($additonalProductIds)) {
                foreach ($additonalProductIds as $additonalProductId) {
                    $patch['!'][$additonalProductId] = $additonalProductId;
                }
            }
        } else if (!empty($additonalProductIds)) {
            foreach ($additonalProductIds as $additonalProductId) {
                $patch['!'][$additonalProductId] = $additonalProductId;
            }
        } else {
            return;
        }

        if(isset($patch) && count($patch) > 0 && file_exists($data_file_name)) {
            try {
                $handle = fopen($data_file_name, "r");
                $newhandle = fopen($data_file_name.'-new.txt', "w+");
                if (!$handle) {
                    throw new Exception('! Cannot load Products file !');
                }

                fputcsv($newhandle, $this->createHeaders(), "\t");

                $products = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect(array_unique(array_merge($this->getDefaultAttributes(), Mage::helper('windsorcircle_export')->getCustomAttributes('product'))), 'left');

                // If no custom attribute for brand then we will use default brand attribute
                $attribute = Mage::getStoreConfig('windsorcircle_export_options/messages/brand_attribute');
                if (empty($attribute)) {
                    $brand = Mage::getModel('catalog/resource_eav_attribute')
                                ->loadByCode('catalog_product', 'brand');
                    if($brand->getId() !== null) {
                        $products->addAttributeToSelect('brand', 'left');
                    }
                } else {
                    $brand = Mage::getModel('catalog/resource_eav_attribute')
                                ->loadByCode('catalog_product', $attribute);
                    if($brand->getId() !== null) {
                        $products->addAttributeToSelect($attribute, 'left');
                    }
                }

                $products->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')
                    ->joinField('backorders', 'cataloginventory/stock_item', 'backorders', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')
                    ->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')
                    ->joinField('super_link', 'catalog/product_super_link', 'parent_id', 'product_id=entity_id', null, 'left')
                    ->joinField('relation', 'catalog/product_relation', 'parent_id', 'child_id=entity_id', null, 'left')
                    ->joinField('parent_sku', 'catalog/product', 'sku', 'entity_id=super_link', null, 'left')
                    ->joinField('parent_sku_relation', 'catalog/product', 'sku', 'entity_id=relation', null, 'left')
                    ->addAttributeToFilter('entity_id', array('in' => $patch['!']));

                Mage::getSingleton('core/resource_iterator')->walk($products->getSelect(), array(array($this, 'productCallback')), array('arg1' => '====', 'handle' => $newhandle));

                while(($data = fgetcsv($handle, 0, "\t")) != false) {
                    if(intval($data[0]) > 0 ) {
                        if(isset($patch['-'][$data[0]]) || isset($patch['!'][$data[0]])) {
                            $data = null;
                        }

                        if ($data) {
                            fputcsv($newhandle, $data, "\t");
                        }
                    }
                }

                unset($patch);
                fclose($handle);
                fclose($newhandle);
            } catch(Exception $e) {
                die($e->getMessage());
            }

            if(file_exists($patch_file_name)) {
                unlink($patch_file_name);
            }
            unlink($data_file_name);
            rename($data_file_name.'-new.txt', $data_file_name);
        }
    }

    /**
     * Get Default Attributes for Products
     *
     * @return array
     */
    protected function getDefaultAttributes()
    {
        return array('name', 'description', 'price', 'special_price', 'special_from_date', 'special_to_date', 'image',
            'small_image', 'status', 'weight', 'weight_type', 'url_path');
    }
}
