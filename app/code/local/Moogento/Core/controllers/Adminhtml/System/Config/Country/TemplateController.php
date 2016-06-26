<?php


class Moogento_Core_Adminhtml_System_Config_Country_TemplateController extends Mage_Adminhtml_Controller_Action
{
    protected $_templateDefaults = array(
        'US' => '{{if prefix}}{{var prefix}} {{/if}}{{var firstname}} {{if middlename}}{{var middlename}} {{/if}}{{var lastname}}{{if suffix}} {{var suffix}}{{/if}}<br/>
{{if company}}{{var company}}<br />{{/if}}
{{if street1}}{{var street1}}<br />{{/if}}
{{if street2}}{{var street2}}<br />{{/if}}
{{if street3}}{{var street3}}<br />{{/if}}
{{if street4}}{{var street4}}<br />{{/if}}
{{if city}}{{var city}},  {{/if}}{{if region}}{{var region|caps}}, {{/if}}{{if postcode}}{{var postcode}}{{/if}}<br/>
{{var country}}<br/>
{{if telephone}}T: {{var telephone}}<br />{{/if}}
{{if fax}}F: {{var fax}}<br />{{/if}}
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}',
    'GB' => '{{if prefix}}{{var prefix}} {{/if}}{{var firstname}} {{if middlename}}{{var middlename}} {{/if}}{{var lastname}}{{if suffix}} {{var suffix}}{{/if}},<br/>
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
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}',
    'CA' => '{{if prefix}}{{var prefix}} {{/if}}{{var firstname}} {{if middlename}}{{var middlename}} {{/if}}{{var lastname}}{{if suffix}} {{var suffix}}{{/if}}<br/>
{{if company}}{{var company}}<br />{{/if}}
{{if street1}}{{var street1}}<br />{{/if}}
{{if street2}}{{var street2}}<br />{{/if}}
{{if street3}}{{var street3}}<br />{{/if}}
{{if street4}}{{var street4}}<br />{{/if}}
{{if city}}{{var city}} {{/if}}{{if region}}({{var region}}) {{/if}}{{if postcode}} {{var postcode|caps}}{{/if}}<br />
{{var country}}<br/>
{{if telephone}}T: {{var telephone}}<br />{{/if}}
{{if fax}}F: {{var fax}}<br />{{/if}}
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}',
    'DE' => '{{if company}}{{var company}}<br />{{/if}}
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
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}',
    'FR' => '{{if company}}{{var company}}<br />{{/if}}
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
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}',
    'NL' => '{{if prefix}}{{var prefix}} {{/if}}{{var firstname}} {{if middlename}}{{var middlename}} {{/if}}{{var lastname}}{{if suffix}} {{var suffix}}{{/if}}<br/>
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
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}',
    'AU' => '{{if prefix}}{{var prefix}} {{/if}}{{var firstname}} {{if middlename}}{{var middlename}} {{/if}}{{var lastname}}{{if suffix}} {{var suffix}}{{/if}}<br/>
{{if company}}{{var company}}<br />{{/if}}
{{if street1}}{{var street1}}<br />{{/if}}
{{if street2}}{{var street2}}<br />{{/if}}
{{if street3}}{{var street3}}<br />{{/if}}
{{if street4}}{{var street4}}<br />{{/if}}
{{if city}}{{var city|caps}} {{/if}}{{if region}}({{var region|caps}}) {{/if}}{{if postcode}}{{var postcode|caps}} {{/if}}<br />
{{var country}}<br/>
{{if telephone}}T: {{var telephone}}<br />{{/if}}
{{if fax}}F: {{var fax}}<br />{{/if}}
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}',
    'NZ' => '{{if prefix}}{{var prefix}} {{/if}}{{var firstname}} {{if middlename}}{{var middlename}} {{/if}}{{var lastname}}{{if suffix}} {{var suffix}}{{/if}}<br/>
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
{{if vat_id}}VAT: {{var vat_id}}<br />{{/if}}'
    );

    protected function _isAllowed()
    {
        return true;
    }

    public function destroyCountryTemplateAction()
    {
        $countryTemplateId = $this->getRequest()->getParam('id');
        $countryTemplate = Mage::getModel("moogento_core/country_template")->load($countryTemplateId);
        $countryTemplate->delete();
    }

    public function addDefaultsAction()
    {
        foreach ($this->_templateDefaults as $country => $template) {
            $templateModel = Mage::getModel("moogento_core/country_template");
            $templateModel->setData(array(
                'enable' => 1,
                'country_code' => $country,
                'country_template' => $template
            ));
            $templateModel->save();
        }

        $this->_redirectReferer();
    }
} 