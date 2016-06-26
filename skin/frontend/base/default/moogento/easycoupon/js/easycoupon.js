function easy_setCookie(name, value, exdays) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + exdays);
    Mage.Cookies.set(name, value, exdate);
}

document.observe("dom:loaded", function() {
    function showBar() {
        new Effect.SlideDown('ezcoupon_container', {
            transition: Effect.Transitions.linear
        });
    }
    function hideBar() {
        new Effect.SlideUp('ezcoupon_container', {
            transition: Effect.Transitions.linear
        });
    }
    var $ezcoupon_container = $('ezcoupon_container');
    if ($ezcoupon_container) {
        if (Mage.Cookies.get("esycpn") != 'disable_bounce') {
            showBar();
        }
        else {
            hideBar();
        }
        $("ezycoupon_close").observe('click', function () {
	            easy_setCookie("esycpn", "disable_bounce", 10);
	            hideBar();
        });
    }
});