<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute("order", "insurance", array("type"=>"int"));
$installer->addAttribute("quote", "insurance", array("type"=>"int"));
$installer->endSetup();
?>