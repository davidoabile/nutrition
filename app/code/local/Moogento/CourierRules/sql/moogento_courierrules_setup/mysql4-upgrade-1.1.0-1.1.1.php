<?php

$installer = $this;

$this->startSetup();

$installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/zone')}` CHANGE  `countries` `countries` TEXT NULL DEFAULT NULL;");

$this->endSetup();