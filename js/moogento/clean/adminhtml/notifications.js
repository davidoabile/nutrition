if ('Notification' in window) {
    jQuery(document).ready(function ($) {
        setInterval(function () {
            if (Notification.permission.toLowerCase() == "granted") {
                $.ajax({
                    type: "GET",
                    url: noticationControllerURL,
                    success: function (result_data) {
                        var _result = jQuery.parseJSON(result_data);
                        if (_result.type) {
                            var mailNotification = new Notification("Moogento_Clean", {
                                icon: noticationImage,
                                tag: "ache-mail",
                                body: _result.message,
                            });
                        }
                    },
                });
            }
        }, noticationCheckingPeriod);
    });
}