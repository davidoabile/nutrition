MobileCart.prototype.postAwGiftcardForm = function (url) {
    mobileCart.disableAllOverlays();
    mobileCart.setLoader(true);
    var gotoUrl = this.awGiftcardFormSubmitUrl;
    if (typeof(url) != 'undefined'){
        gotoUrl = url;
    }
    ajaxWrapper(gotoUrl ,{
        method:'post',
        onSuccess: function(transport){
            if (transport && transport.responseText){
                mobileCart.updateContent(transport);
                mobileCart.setLoader(false);
            }
        },
        onFailure: function(){
            mobileCart.setLoader(false);
        },
        parameters: Form.serialize($(this.aw_giftcard_form_id))
    });
}
MobileCart.prototype.registerAwGiftcard = function (giftcard_form_id, giftcard_url) {
    this.aw_giftcard_form = $j('#'+giftcard_form_id);
    this.aw_giftcard_form_id = giftcard_form_id;
    this.awGiftcardFormSubmitUrl = giftcard_url;
    this.aw_giftcard_form.submit(function(event){
        mobileCart.postAwGiftcardForm();
        return false;
    });
}