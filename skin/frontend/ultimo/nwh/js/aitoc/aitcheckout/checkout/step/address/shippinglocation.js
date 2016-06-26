
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
var AitShippinglocation = Class.create(Step,
    {
        afterInit: function()
        {
            this.initEvents(this.container);
        },

        initEvents: function(containerId)
        {
            if ($(containerId))
            {
                $(containerId).select('input', 'select', 'textarea').each(
                    function(input)
                    {
                        Event.stopObserving(input, 'change', this.parent.onChangeStepData);
                        Event.observe(input, 'change', this.onChangeStepData);
                    }.bind(this)
                );
            }
        },

        update: function(event)
        {
            this.ifChecked = true;
            if (event)
            {
                this._validationFormValue(event)
            }
            var billingValidator = new AitValidation(this.parent.container);
            var locationValidator = new AitValidation(this.container);

            if (billingValidator && billingValidator.validate())
            {
                this._updateRequest(this.parent.name);
            }
            else if (locationValidator && locationValidator.validate())
            {
                this._updateRequest(this.name);
            }
        }
    });