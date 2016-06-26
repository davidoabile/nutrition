<?php 

class Moogento_Clean_Model_System_Config_Source_Deliveryrateorders
{
    public function toOptionArray()
    {
        $list = array();

        for($i = 0; $i < 4; $i++){
            array_push($list, array('value' => pow ( 10 , $i ), 'label' => pow ( 10 , $i )));
        }
        return $list;
    }

}
