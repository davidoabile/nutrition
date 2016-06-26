<?php

class Wyomind_Ordersexporttool_Block_Adminhtml_Renderer_Exportedto extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {


        $data = explode(',', $row->getExportFlag());
        $todo_ids = array();
        $done_ids = array();
        $done = array();
        foreach ($data as $p) {
            if ($p > 0) {
                $done_ids[] = $p;
                $profile = Mage::getModel('ordersexporttool/profiles')->load($p);
                $done[] = "<span id='orderexported-" . $row->getId() . "-" . $profile->getFileId() . "'><span class='ckeckmark'>✔</span>&nbsp;" . $profile->getFileName() . " <a href='#' onclick='javascript:ordersexporttool._delete(" . $row->getId() . "," . $profile->getFileId() . ",\"" . $this->getUrl('*/profiles/change') . "\")'>(✘)</a></span>";
            }
        }



        $todo = array();
        foreach ($row->getAllItems() as $item) {

            if ($item->getExportTo() > 0 && !in_array($item->getExportTo(), $done_ids)) {
                $todo_ids[] = $item->getExportTo();
                $profile = Mage::getModel('ordersexporttool/profiles')->load($item->getExportTo());
                $todo[] = "<span style='color:grey' id='orderexported-" . $row->getId() . "-" . $profile->getFileId() . "'><span class='ckeckmark' style='font-size:20px;vertical-align: sub;'>&#10144;</span>&nbsp;" . $profile->getFileName() . " </span>";
            }
        }
        $html.=implode('<br>', array_merge(array_unique($done),array_unique($todo)));
       
        

       //$order_ids = array($row->getEntityId());

        if (!count($done) && !count($todo)) {
            $html = Mage::helper("ordersexporttool")->__("No profile defined");
        }
//         elseif (count($todo)) {
//            $html .= "<a style='float:right' href='" . $this->getUrl('ordersexporttool/adminhtml_profiles/export', array('order_ids' => serialize($order_ids), "profile_ids" => serialize($todo_ids))) . "'>export</a>";
//        }






        return $html;
    }

}
