jQuery(document).ready(function ($) {

    var $wwof_getting_started = $(".wwof-getting-started");

    $wwof_getting_started.find('button.notice-dismiss').click(function (e) {

        $wwof_getting_started.fadeOut("fast", function () {
            jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "wwof_getting_started_notice_hide"
                },
                dataType: "json"
            })
                .done(function (data, textStatus, jqXHR) {
                    // notice is now hidden
                })

        });

    });

});