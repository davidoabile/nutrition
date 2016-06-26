<?php 
class NWH_SaveInsurance_Block_Adminhtml_Sales_Order_Invoice_Totals extends Mage_Adminhtml_Block_Sales_Order_Invoice_Totals{
    
    const FEE_AMOUNT = 2.95;
    public function formatValue($total)
    {
        if (!$total->getIsFormated()) {
            if($this->getOrder()->getInsurance() AND $total->getCode()=="shipping"){
                return $this->helper('adminhtml/sales')->displayPrices(
                    $this->getOrder(),
                    $total->getBaseValue() + self::FEE_AMOUNT,
                    $total->getValue() + self::FEE_AMOUNT
                );    
            }
            return $this->helper('adminhtml/sales')->displayPrices(
                $this->getOrder(),
                $total->getBaseValue(),
                $total->getValue()
            );
        }
        if($this->getOrder()->getInsurance() AND $total->getCode()=="shipping"){
            return $total->getValue() + self::FEE_AMOUNT;
        }
        return $total->getValue();
    }
}
 ?>