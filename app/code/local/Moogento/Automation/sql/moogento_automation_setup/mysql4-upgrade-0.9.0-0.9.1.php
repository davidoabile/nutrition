<?php

$this->startSetup();
$installer = $this;
$query = <<<HEREDOC
INSERT INTO {$this->getTable('moogento_automation/processing_flag')}
    SELECT null, 'send_complete_email', entity_id FROM {$this->getTable('sales/order')} WHERE state = 'complete';
HEREDOC;
$installer->run($query);

$this->endSetup();