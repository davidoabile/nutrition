<?php
class Balance_Category_Model_Entity_Attribute_Source_Boolean extends Mage_Eav_Model_Entity_Attribute_Source_Boolean
{
    /**
     * Add Value Sort To Collection Select
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @param string $dir
     *
     * @return Mage_Eav_Model_Entity_Attribute_Source_Table
     */
    public function addValueSortToCollection($collection, $dir = Varien_Db_Select::SQL_ASC)
    {
        $valueTable1 = $this->getAttribute()->getAttributeCode() . '_t1';
        $valueTable2 = $this->getAttribute()->getAttributeCode() . '_t2';
        $collection->getSelect()
            ->joinLeft(
                array($valueTable1 => $this->getAttribute()->getBackend()->getTable()), "e.entity_id={$valueTable1}.entity_id"
                . " AND {$valueTable1}.attribute_id='{$this->getAttribute()->getId()}'"
                . " AND {$valueTable1}.store_id=0", array())
            ->joinLeft(
                array($valueTable2 => $this->getAttribute()->getBackend()->getTable()), "e.entity_id={$valueTable2}.entity_id"
                . " AND {$valueTable2}.attribute_id='{$this->getAttribute()->getId()}'"
                . " AND {$valueTable2}.store_id='{$collection->getStoreId()}'", array()
            );
        $valueExpr = $collection->getSelect()->getAdapter()
            ->getCheckSql("{$valueTable2}.value_id > 0", "{$valueTable2}.value", "{$valueTable1}.value");

        Mage::getResourceModel('eav/entity_attribute_option')
            ->addOptionValueToCollection($collection, $this->getAttribute(), $valueExpr);

        $collection->getSelect()
            ->order("{$this->getAttribute()->getAttributeCode()} {$dir}");

        return $this;
    }

}