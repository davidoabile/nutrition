<?php


class Moogento_RetailExpress_Helper_Api_Attribute extends Mage_Core_Helper_Abstract
{
    const ATTRIBUTE_CODE_FIELD_NAME = 'retail_express_code';
    const ATTRIBUTE_OPTION_ID_FIELD_NAME = 'retail_express_id';
    const PRODUCT_ID_FIELD_NAME = 'retail_express_id';
    const ATTRIBUTE_PREFIX = 're_';

    const DEFAULT_PRODUCT_ATTRIBUTE_SET = 4;

    public function addAttributeSet($attributeSetData)
    {
        $productTypeId = $attributeSetData['ProductTypeId'];
        $reName = $attributeSetData['ProductTypeName'];

        $attributeSet = $this->getAttributeSetByReId($productTypeId);

        if(!$attributeSet->getAttributeSetId()) {
            $cloneSetId = self::DEFAULT_PRODUCT_ATTRIBUTE_SET;
            $entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();

            $set = Mage::getModel('eav/entity_attribute_set');
            $set->setEntityTypeId($entityTypeId);
            $set->setAttributeSetName($reName);
            $set->setRetailExpressId($productTypeId);
            $set->validate();
            $set->save();
            $set->initFromSkeleton($cloneSetId);
            $set->save();

            /*
            $modelGroup = Mage::getModel('eav/entity_attribute_group');
            $modelGroup->setAttributeGroupName('Retail Express')
                ->setAttributeSetId($set->getId())
                ->setSortOrder(100);
            $modelGroup->save();
            */
            return $set;
        }

        return $attributeSet;
    }


    public function addAttributeToGroup($setId, $groupId, $attributeId, $sortOrder = null)
    {
        $entityType = Mage::getModel('catalog/product')->getResource()->getTypeId();
        $db =  Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getSingleton('core/resource')->getTableName('eav/entity_attribute');

        $data = array(
            'entity_type_id'        => $entityType,
            'attribute_set_id'      => $setId,
            'attribute_group_id'    => $groupId,
            'attribute_id'          => $attributeId,
        );

        $bind = array(
            'entity_type_id'    => $entityType,
            'attribute_set_id'  => $setId,
            'attribute_id'      => $attributeId
        );

        $select = $db->select()
            ->from($table)
            ->where('entity_type_id = :entity_type_id')
            ->where('attribute_set_id = :attribute_set_id')
            ->where('attribute_id = :attribute_id');
        $row = $db->fetchRow($select, $bind);
        if ($row) {
            // update
            if ($sortOrder !== null) {
                $data['sort_order'] = $sortOrder;
            }

            $db->update(
                $table,
                $data,
                $db->quoteInto('entity_attribute_id=?', $row['entity_attribute_id'])
            );
        }
        else {
            if ($sortOrder === null) {
                $select = $db->select()
                    ->from($table, 'MAX(sort_order)')
                    ->where('entity_type_id = :entity_type_id')
                    ->where('attribute_set_id = :attribute_set_id')
                    ->where('attribute_id = :attribute_id');

                $sortOrder = $db->fetchOne($select, $bind) + 10;
            }
            $sortOrder = is_numeric($sortOrder) ? $sortOrder : 1;
            $data['sort_order'] = $sortOrder;
            $db->insert($table, $data);
        }

        return $this;
    }

    public function getAttributeSetByReId($reId)
    {
        return Mage::getModel("eav/entity_attribute_set")->getCollection()->addFieldToFilter("retail_express_id", $reId)->getFirstItem();
    }

    public function addAttribute($attributeDataIn)
    {
        foreach($attributeDataIn as $reCode => $attributeData ) {
            $attributes = Mage::getResourceModel('eav/entity_attribute_collection')->addFieldToFilter(self::ATTRIBUTE_CODE_FIELD_NAME, $reCode);
            $attribute = $attributes->getFirstItem();

            if (!$attribute->getId()) {
                $attribute = $this->_createAttribute($reCode, $attributeData);
            }
            $this->_updateAttribute($attribute, $attributeData);
            break;
        }
    }

    public function addSeason($attributeData)
    {
        /*
        var_dump('!!! Season !!!');
        echo "<br>";
        foreach($attributeData as $value) {
            var_dump($value);
            echo "<br>";
        }
        echo "<br><br>";
        */
    }

    public function _addPaymentMethod($attributeData)
    {
        /*
        var_dump('!!! Payment Method !!!');
        echo "<br>";
        foreach($attributeData as $value) {
            var_dump($value);
            echo "<br>";
        }
        echo "<br><br>";
        */
    }

    public function _createAttribute($reCode, $attributeData)
    {
        $attributeCode = Mage::helper('moogento_retailexpress')->prepareAttributeCode($reCode);
        $attributeName = $reCode;

        $productTypes = -1;
        $setInfo = -1;

        if($attributeName == '' || $attributeCode == '')
        {
            $this->log("Can't import the attribute with an empty label or code.  LABEL= [$labelText]  CODE= [$attributeCode]");
            return false;
        }

        if($productTypes === -1)
            $productTypes = array('simple');

        if($setInfo !== -1 && (isset($setInfo['SetID']) == false || isset($setInfo['GroupID']) == false))
        {
            $this->log("Please provide both the set-ID and the group-ID of the attribute-set if you'd like to subscribe to one.");
            return false;
        }

        $properties = $this->getPropertyArray('select');

        // Valid product types: simple, grouped, configurable, virtual, bundle, downloadable, giftcard
        $properties['apply_to']       = implode(',', $productTypes);
        $properties['attribute_code'] = $attributeCode;
        $properties['frontend_label'] = array(
            0 => $attributeName,
            1 => '',
            3 => '',
            2 => '',
            4 => '',
        );

        $attribute = Mage::getModel('catalog/resource_eav_attribute');
        $attribute->setData(self::ATTRIBUTE_CODE_FIELD_NAME, $reCode);
        $attribute->addData($properties);

        if($setInfo !== -1) {
            $attribute->setAttributeSetId($setInfo['SetID']);
            $attribute->setAttributeGroupId($setInfo['GroupID']);
        }

        $entityTypeID = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
        $attribute->setEntityTypeId($entityTypeID);
        $attribute->setIsUserDefined(1);

        try {
            $attribute->save();
        } catch(Exception $ex) {
            if($ex->getCode() != 400) {
                echo "ERROR: " . $attributeCode . '<br>';
                var_dump($ex->getMessage());
            }
            return false;
        }

        return $attribute;
    }


    public function getPropertyArray($type)
    {
        $attributeData = array(
            'is_global'                     => '0',
            'frontend_input'                => 'text',
            'default_value_text'            => '',
            'default_value_yesno'           => '0',
            'default_value_date'            => '',
            'default_value_textarea'        => '',
            'is_unique'                     => '0',
            'is_required'                   => '0',
            'frontend_class'                => '',
            'is_searchable'                 => '0',
            'is_visible_in_advanced_search' => '0',
            'is_comparable'                 => '0',
            'is_used_for_promo_rules'       => '0',
            'is_html_allowed_on_front'      => '1',
            'is_visible_on_front'           => '1',
            'backend_model'                 => '',
            'used_in_product_listing'       => '1',
            'used_for_sort_by'              => '0',
            'is_configurable'               => '0',
            'is_filterable'                 => '0',
            'is_filterable_in_search'       => '0',
            'backend_type'                  => 'varchar',
            'default_value'                 => '',
        );

        switch ($type) {
            case 'select':
                $attributeData['frontend_input'] = 'select';
                $attributeData['backend_type'] = 'int';
                $attributeData['is_configurable'] = '1';
                $attributeData['is_filterable'] = '1';
                $attributeData['is_filterable_in_search'] = '1';
                $attributeData['is_comparable'] = '1';
                break;
            case 'boolean':
                $attributeData['frontend_input'] = 'boolean';
                $attributeData['backend_type'] = 'int';
                $attributeData['backend_model'] = 'customer/attribute_backend_data_boolean';
                $attributeData['is_filterable'] = '1';
                $attributeData['is_filterable_in_search'] = '1';
                $attributeData['is_comparable'] = '1';
                break;
        }

        return $attributeData;
    }

    public function createAttributeCode($name)
    {

    }

    public function _updateAttribute($attribute, $attributeData)
    {
        $attributeOptionsModel = Mage::getModel('eav/entity_attribute_source_table') ;
        $attributeOptionsModel->setAttribute($attribute);
        $options = $attributeOptionsModel->getAllOptions(false);

        $existValues = array();
        foreach($options as $option) {
            $existValues[$option['value']] = $option['label'];
        }

        $reCode = $attribute->getRetailExpressCode();
        foreach($attributeData as $value) {
            $reId = $value[$reCode . 'Id'];
            $reValue = $value[$reCode . 'Name'];
            $reOrder = isset($value['ListOrder']) ? $value['ListOrder'] : 0;

            $id = array_search($reValue, $existValues);
            if($id === false) {
                $id = $this->addAttributeValue($attribute, $reId, $reValue, $reOrder);
            }
        }
    }

    public function addAttributeValue($attribute, $key, $value, $reOrder)
    {
        $attributeCode = $attribute->getAttributeCode();

        if(is_string($attributeCode)) {
            $attribute = Mage::getModel('eav/entity_attribute');
            $attributeId = $attribute->getIdByCode('catalog_product', $attributeCode);
            $attribute = $attribute->load($attributeId);
        }
        else {
            $attribute = $attributeCode;
        }

        $option['attribute_id'] = $attribute->getId();
        $option['value']['any_option_name'][0] = $value;
        $option['value']['any_option_name'][1] = $value;
        $option['retail_express_id'] = $key;
        $option['order'] = $reOrder;

        $setup = new Moogento_RetailExpress_Model_Entity_Setup('core_setup');
        $setup->addAttributeOption($option);

        $attributeOptionsModel = Mage::getModel('moogento_retailexpress/entity_attribute_source_table');
        $attributeOptionsModel->setAttribute($attribute);
        //$option = $attributeOptionsModel->getOptionById1c($key);
        $options = $attributeOptionsModel->getAllOptions(false);

        foreach($options as $option) {
            if (($option['retail_express_id'] == $key) || ($option['label'] == $value)) {
                return $option['value'];
            }
        }

        return false;
    }

    public function getReGroupByAttributeSet($setId)
    {
        $defaultGroupId = false;

        $groups = Mage::getModel('eav/entity_attribute_group')->getResourceCollection();
        $groups->setAttributeSetFilter($setId)
            ->setSortOrder()
            ->load();


        foreach($groups as $group) {
            if($group->getAttributeGroupName() === 'Retail Express') {
                $reGroupId = $group->getAttributeGroupId();
            }
            elseif($group->getAttributeGroupName() === 'Default') {
                $defaultGroupId = $group->getAttributeGroupId();
            }
        }
        return isset($reGroupId) ? $reGroupId : $defaultGroupId;
    }
} 