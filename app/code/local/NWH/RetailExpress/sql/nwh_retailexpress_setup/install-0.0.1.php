<?php

/**
 * NWH
 *
 * NOTICE OF LICENSE
 *
 * David

 */
/**
 * Install product link types
 */
$this->startSetup();
$this->run("ALTER TABLE `{$this->getTable('core/store')}` ADD  `channel_id` INT NOT NULL DEFAULT 0");
$this->endSetup();
