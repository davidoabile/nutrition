
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     n/a
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
var AitPopup = Class.create();
AitPopup.prototype = {
    container:null,
    lightbox:null,
    shown: false,
    inputs: {},
    
    initialize: function(id)
    {
        this.container = $(id);
        this.lightbox = $('popup-litebox');
        this.inputs = this.container.getElementsBySelector('input','button');
        
        for(var i=0;i<this.inputs.length;i++) {
            var el = this.inputs[i];
            el.focused = false;
            Event.observe(el, 'focus', function() {this.focused=true});
            Event.observe(el, 'blur', function() {this.focused=false});
        }
        
        Event.observe(this.lightbox, 'click', this.hide.bind(this));
        Event.observe(document, 'keyup', this.checkKey.bind(this));        
    },
    
    checkKey: function(evt) {
        var code;
        if (evt.keyCode) code = evt.keyCode;
        else if (evt.which) code = evt.which;
        if (code == Event.KEY_ESC) {
            this.hide();
        }        
        if(code == Event.KEY_TAB) {
            this.tab();
        }
    },
    
    tab:function() {
        if(!this.shown) return false;
        var haveFocus = false;
        for(var i=0;i<this.inputs.length;i++) {
            if(this.inputs[i].focused) {
                haveFocus = true;
                break;
            }
        }
        if(haveFocus == false)
            this.inputs[0].select();
    },
    
    show: function() {
        this.shown = true;
        this.toggleLitebox(true);
        if(!this.container.hasClassName('show'))
        {
            this.container.addClassName('show');
        }
    },
    
    hide: function() {
        this.shown = false;
        if(this.container.hasClassName('show'))
        {
            this.container.removeClassName('show');
        }
        this.toggleLitebox(false);
    },
    
    toggleLitebox: function(b) {
        this.lightbox.style.display = b ? 'block' : 'none';
    }
}