<?php


class Moogento_RetailExpress_Helper_Api_Product extends Mage_Core_Helper_Abstract
{
    const ATTRIBUTE_CODE_FIELD_NAME = 'retail_express_code';
    const ATTRIBUTE_OPTION_ID_FIELD_NAME = 'retail_express_id';
    const PRODUCT_ID_FIELD_NAME = 'retail_express_id';
    const PRODUCT_UPDATED_FIELD_NAME = 'retail_express_updated_date';

    protected $_attributeValuesCache = array();

    public function getAttributeSettings()
    {
        $prefix = Moogento_RetailExpress_Helper_Api_Attribute::ATTRIBUTE_PREFIX;

        return array(
            'ProductId' => array('type' => 'system'),
            'SKU' => array('type' => 'system'),
            'Code' => array('type' => 'system'),
            'Description' => array('type' => 'system'),
            'BrandId' => array('type' => 'select'),
            'SizeId' => array('type' => 'select'),
            'ColourId' => array('type' => 'select'),
            'SeasonId' => array('type' => 'select'),
            'ProductTypeId' => array('type' => 'system'),
            'Freight' => array('type' => 'field', 'field' => $prefix.'freight'),
            'Weight' => array('type' => 'system'),
            'Length' => array('type' => 'field', 'field' => $prefix.'length'),
            'Breadth' => array('type' => 'field', 'field' => $prefix.'breadth'),
            'Depth' => array('type' => 'field', 'field' => $prefix.'depth'),
            'Custom1' => array('type' => 'field', 'field' => $prefix.'custom1'),
            'Custom2' => array('type' => 'field', 'field' => $prefix.'custom2'),
            'Custom3' => array('type' => 'field', 'field' => $prefix.'custom3'),
            'LastUpdated' => array('type' => 'system'),
            'ShippingCubic' => array('type' => 'field', 'field' => $prefix.'shipping_cubic'),
            'Price' => array('type' => 'system'),
            'Taxable' => array('type' => 'system'),
            'StockAvailable' => array('type' => 'system'),
            'StockOnHand' => array('type' => 'system'),
            'ManageStock' => array('type' => 'system'),
            'MatrixProduct' => array('type' => 'system'),
            'WebSellPrice' => array('type' => 'system'),
            'RRP' => array('type' => 'field', 'field' => $prefix.'rrp'),
            'DefaultPrice' => array('type' => 'system'),
            'DiscountedPrice' => array('type' => 'system'),
            'CustomerDiscountedPrice' => array('type' => 'system'),
            'TaxRate' => array('type' => 'system'),
        );
    }

    public function addProduct($productData)
    {
        $reProductId = $productData['ProductId'];
        $productIdField = Mage::getStoreConfig('moogento_retailexpress/product/retail_express_id') ? Mage::getStoreConfig('moogento_retailexpress/product/retail_express_id') : self::PRODUCT_ID_FIELD_NAME;
        $product = Mage::getModel('catalog/product')->loadByAttribute($productIdField, $reProductId);
        if($product && $product->getId()) {

            $reUpdated = new DateTime();
            $reUpdated->setTimestamp(strtotime($productData['LastUpdated']));

            $mageUpdated = new DateTime();
            $mageUpdated->setTimestamp(strtotime($product->getData(self::PRODUCT_UPDATED_FIELD_NAME)));

            if($reUpdated != $mageUpdated) {
                $this->_updateProduct($product, $productData);
            }
        }
        else {
            $this->_createProduct($productData);
        }
    }

    protected function _createProduct($productData)
    {
        try {
            $reProductId = $productData['ProductId'];
            $productTypeId = $productData['ProductTypeId'];
            $productEntityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();
            $settings = $this->getAttributeSettings();

            $attributeSet = Mage::helper('moogento_retailexpress/api_attribute')->getAttributeSetByReId($productTypeId);
            if($attributeSet) {
                $name = isset($productData['Code']) ? $productData['Code'] : $productData['Description'];
                $product = Mage::getModel('catalog/product');
                $product->setName($name);
                $product->setAttributeSetId($attributeSet->getAttributeSetId());
                $product->setSku($productData['SKU']);
                $product->setRetailExpressId($reProductId);
                $product->setRetailExpressId($reProductId);
                $product->setDescription($productData['Description']);
                if(isset($productData['Weight'])) $product->setWeight($productData['Weight']);
                $product->setOptionsContainer('container1');
                $product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
                $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
                $product->setTaxClassId(0);
                $product->setPrice($productData['DefaultPrice']);
                if(isset($productData['StockAvailable']) && ($productData['StockAvailable'] > 0 )) {
                    $qty = $productData['StockAvailable'];
                    $isInStock = true;
                }
                else {
                    $qty = 0;
                    $isInStock = false;
                }
                $stockData = array(
                    'manage_stock' => (isset($productData['ManageStock']) && $productData['ManageStock']) ? true : false,
                    'is_in_stock' => $isInStock,
                    'qty' => $qty,
                );
                $product->setStockData($stockData);

                $groupId = Mage::helper('moogento_retailexpress/api_attribute')->getReGroupByAttributeSet($attributeSet->getAttributeSetId());
                foreach($productData as $code => $value) {
                    if(isset($settings[$code])) {
                        $setting = $settings[$code];
                        if($setting['type'] == 'field') {
                            $attributeCode  = $setting['field'];
                            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode($productEntityTypeID, $attributeCode);
                            Mage::helper('moogento_retailexpress/api_attribute')->addAttributeToGroup($attributeSet->getAttributeSetId(), $groupId, $attribute->getId());
                            $product->setData($attributeCode, $value);
                        }
                        elseif($setting['type'] == 'select') {
                            $attributeCode = Mage::helper('moogento_retailexpress')->prepareAttributeCode(substr($code, 0, -2));
                            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode($productEntityTypeID, $attributeCode);
                            if($attribute->getId()) {
                                if($valueId = $this->attributeValueExists($attributeCode, $value)) {
                                    Mage::helper('moogento_retailexpress/api_attribute')->addAttributeToGroup($attributeSet->getAttributeSetId(), $groupId, $attribute->getId());
                                    $product->setData($attributeCode, $valueId);
                                }
                            }
                        }
                    }
                }

                $product->save();
            }

        } catch(Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function attributeValueExists($attributeCode, $value)
    {
        if(!isset($this->_attributeValuesCache[$attributeCode][$this->_getCacheKey($value)])) {
            $attribute_model = Mage::getModel('eav/entity_attribute');
            $attribute_options_model = Mage::getModel('moogento_retailexpress/entity_attribute_source_table') ;
            $attributeId = $attribute_model->getIdByCode('catalog_product', $attributeCode);
            $attribute = $attribute_model->load($attributeId);
            $attribute_options_model->setAttribute($attribute);
            $options = $attribute_options_model->getAllOptions(false);

            foreach($options as $option) {
                if ($option['retail_express_id'] == $value) {
                    $this->_attributeValuesCache[$attributeCode][$this->_getCacheKey($value)] = $option['value'];
                    return $option['value'];
                }
            }
        }
        else {
            return $this->_attributeValuesCache[$attributeCode][$this->_getCacheKey($value)];
        }

        return false;
    }

    protected function _prepareProductProperty($code, $dataRow, $product)
    {

    }

    protected function _getCacheKey($code)
    {
        return md5($code);
    }

    protected function _updateProduct($product, $productData)
    {
        $reProductId = $productData['ProductId'];
        $sku = $productData['SKU'];

        $reUpdated = new DateTime();
        $reUpdated->setTimestamp(strtotime($productData['LastUpdated']));

        $product->setRetailExpressUpdatedDate($reUpdated->format('Y-m-d H:i:s'));
        $product->setDescription($productData['Description']);
        $product->setPrice($productData['DefaultPrice']);

        if(isset($productData['StockAvailable']) && ($productData['StockAvailable'] > 0 )) {
            $qty = $productData['StockAvailable'];
            $isInStock = true;
        }
        else {
            $qty = 0;
            $isInStock = false;
        }

        $stockItem = $product->getStockItem();
        $stockItem->setData('manage_stock', $productData['ManageStock']);
        $stockItem->setData('qty', $qty);
        $stockItem->setData('is_in_stock', $isInStock);

        $product->save();
    }
}