<?php

$this->startSetup();
$installer = $this;
$installer->run("
INSERT IGNORE INTO `{$this->getTable('moogento_core/country_template')}`
(enable, country_code, country_template) VALUES 
(1, 'US', '{{if prefix}}{{var prefix}} {{/if}}{{var firstname}} {{if middlename}}{{var middlename}} {{/if}}{{var lastname}}{{if suffix}} {{var suffix}}{{/if}}<br/>
{{if company}}{{var company}}<br />{{/if}}
{{if street1}}{{var street1}}<br />{{/if}}
{{if street2}}{{var street2}}<br />{{/if}}
{{if street3}}{{var street3}}<br />{{/if}}
{{if street4}}{{var street4}}<br />{{/if}}
{{if city}}{{var city}},  {{/if}}{{if region}}{{var region|caps}}, {{/if}}{{if postcode}} {{var postcode}}{{/if}}<br/>
{{var country}}<br/>
{{if telephone}}T: {{var telephone}}<br />{{/if}}
{{if fax}}F: {{var fax}}<br />{{/if}}
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}'), 
(1, 'GB', '{{if prefix}}{{var prefix}} {{/if}}{{var firstname}} {{if middlename}}{{var middlename}} {{/if}}{{var lastname}}{{if suffix}} {{var suffix}}{{/if}},<br/>
{{if company}}{{var company}},<br />{{/if}}
{{if street1}}{{var street1}},<br />{{/if}}
{{if street2}}{{var street2}},<br />{{/if}}
{{if street3}}{{var street3}},<br />{{/if}}
{{if street4}}{{var street4}},<br />{{/if}}
{{if city}}{{var city}},<br />{{/if}}
{{if region}}{{var region}},<br />{{/if}}
{{if postcode}}{{var postcode|caps}}<br />{{/if}}
{{var country}}<br/>
{{if telephone}}T: {{var telephone}}<br />{{/if}}
{{if fax}}F: {{var fax}}<br />{{/if}}
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}'),
(1, 'CA', '{{if prefix}}{{var prefix}} {{/if}}{{var firstname}} {{if middlename}}{{var middlename}} {{/if}}{{var lastname}}{{if suffix}} {{var suffix}}{{/if}}<br/>
{{if company}}{{var company}}<br />{{/if}}
{{if street1}}{{var street1}}<br />{{/if}}
{{if street2}}{{var street2}}<br />{{/if}}
{{if street3}}{{var street3}}<br />{{/if}}
{{if street4}}{{var street4}}<br />{{/if}}
{{if city}}{{var city}} {{/if}}{{if region}}({{var region}}) {{/if}}{{if postcode}} {{var postcode|caps}}{{/if}}<br />
{{var country}}<br/>
{{if telephone}}T: {{var telephone}}<br />{{/if}}
{{if fax}}F: {{var fax}}<br />{{/if}}
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}'),
(1, 'DE', '{{if company}}{{var company}}<br />{{/if}}
{{if prefix}}{{var prefix}} {{/if}}{{var firstname}} {{if middlename}}{{var middlename}} {{/if}}{{var lastname}}{{if suffix}} {{var suffix}}{{/if}}<br/>
{{if street1}}{{var street1}}<br />{{/if}}
{{if street2}}{{var street2}}<br />{{/if}}
{{if street3}}{{var street3}}<br />{{/if}}
{{if street4}}{{var street4}}<br />{{/if}}
{{if postcode}}{{var postcode}} {{/if}}{{if city}}{{var city}}{{/if}}<br />
{{if region}}({{var region}})<br />{{/if}}
{{var country}}<br/>
{{if telephone}}T: {{var telephone}}<br />{{/if}}
{{if fax}}F: {{var fax}}<br />{{/if}}
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}'),
(1, 'FR', '{{if company}}{{var company}}<br />{{/if}}
{{if prefix}}{{var prefix}} {{/if}}{{var firstname}} {{if middlename}}{{var middlename}} {{/if}}{{var lastname}}{{if suffix}} {{var suffix}}{{/if}}<br/>
{{if street1}}{{var street1}}<br />{{/if}}
{{if street2}}{{var street2}}<br />{{/if}}
{{if street3}}{{var street3}}<br />{{/if}}
{{if street4}}{{var street4}}<br />{{/if}}
{{if postcode}}{{var postcode}} {{/if}}{{if city}}{{var city|caps}}{{/if}}<br />
{{if region}}({{var region}})<br />{{/if}}
{{var country}}<br/>
{{if telephone}}T: {{var telephone}}<br />{{/if}}
{{if fax}}F: {{var fax}}<br />{{/if}}
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}'),
(1, 'NL', '{{if prefix}}{{var prefix}} {{/if}}{{var firstname}} {{if middlename}}{{var middlename}} {{/if}}{{var lastname}}{{if suffix}} {{var suffix}}{{/if}}<br/>
{{if company}}{{var company}}<br />{{/if}}
{{if street1}}{{var street1}}<br />{{/if}}
{{if street2}}{{var street2}}<br />{{/if}}
{{if street3}}{{var street3}}<br />{{/if}}
{{if street4}}{{var street4}}<br />{{/if}}
{{if postcode}}{{var postcode|caps}} {{/if}}{{if city}}{{var city|caps}}{{/if}}<br />
{{if region}}({{var region}})<br />{{/if}}
{{var country}}<br/>
{{if telephone}}T: {{var telephone}}<br />{{/if}}
{{if fax}}F: {{var fax}}<br />{{/if}}
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}'),
(1, 'AU', '{{if prefix}}{{var prefix}} {{/if}}{{var firstname}} {{if middlename}}{{var middlename}} {{/if}}{{var lastname}}{{if suffix}} {{var suffix}}{{/if}}<br/>
{{if company}}{{var company}}<br />{{/if}}
{{if street1}}{{var street1}}<br />{{/if}}
{{if street2}}{{var street2}}<br />{{/if}}
{{if street3}}{{var street3}}<br />{{/if}}
{{if street4}}{{var street4}}<br />{{/if}}
{{if city}}{{var city|caps}} {{/if}}{{if region}}({{var region|caps}}) {{/if}}{{if postcode}}{{var postcode|caps}} {{/if}}<br />
{{var country}}<br/>
{{if telephone}}T: {{var telephone}}<br />{{/if}}
{{if fax}}F: {{var fax}}<br />{{/if}}
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}'),
(1, 'NZ', '{{if prefix}}{{var prefix}} {{/if}}{{var firstname}} {{if middlename}}{{var middlename}} {{/if}}{{var lastname}}{{if suffix}} {{var suffix}}{{/if}}<br/>
{{if company}}{{var company}}<br />{{/if}}
{{if street1}}{{var street1}}<br />{{/if}}
{{if street2}}{{var street2}}<br />{{/if}}
{{if street3}}{{var street3}}<br />{{/if}}
{{if street4}}{{var street4}}<br />{{/if}}
{{if city}}{{var city}} {{/if}}{{if postcode}}{{var postcode}}{{/if}}<br />
{{if region}}{{var region}}<br />{{/if}}
{{var country}}<br/>
{{if telephone}}T: {{var telephone}}<br />{{/if}}
{{if fax}}F: {{var fax}}<br />{{/if}}
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}');
");

$this->endSetup();