jQuery(document).ready(function ($) {
  var errorMessageDuration = "10000",
    successMessageDuration = "5000";

  // Help Section
  $("#wwof_help_create_wholesale_page")
    .removeAttr("disabled") // On load
    .click(function () {
      var $this = $(this);

      $this
        .attr("disabled", "disabled")
        .siblings(".spinner")
        .css("display", "inline-block");

      wwofBackEndAjaxServices
        .createWholesalePage()
        .done(function (data, textStatus, jqXHR) {
          if (data.status == "success") {
            toastr.success("", WPMessages.success_message, {
              closeButton: true,
              showDuration: successMessageDuration,
            });
          } else {
            toastr.error(data.error_message, WPMessages.failure_message, {
              closeButton: true,
              showDuration: errorMessageDuration,
            });

            console.log(WPMessages.failure_message);
            console.log(jqXHR);
            console.log("----------");
          }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
          toastr.error(jqXHR.responseText, WPMessages.failure_message, {
            closeButton: true,
            showDuration: errorMessageDuration,
          });

          console.log(WPMessages.failure_message);
          console.log(jqXHR);
          console.log("----------");
        })
        .always(function () {
          $this
            .removeAttr("disabled")
            .siblings(".spinner")
            .css("display", "none");
        });
    });

  $("#wwof-force-fetch-update-data").click(function (e) {
    
    var $this = $(this);

    if (!confirm($(this).data("confirm"))) {
      e.stopImmediatePropagation();
      e.preventDefault();
      return;
    }

    $this
      .attr("disabled", "disabled")
      .siblings(".spinner")
      .css("display", "inline-block")
      .css("visibility", "visible");

    wwofBackEndAjaxServices
      .forceFetchUpdateData()
      .done(function (data, textStatus, jqXHR) {
        if (data.status == "success") {
          toastr.success(
            "",
            wwof_settings_debug_var.success_force_fetch_update_data_txt,
            { closeButton: true, showDuration: successMessageDuration }
          );
        } else {
          toastr.error(
            "",
            wwof_settings_debug_var.failed_force_fetch_update_data_txt,
            { closeButton: true, showDuration: errorMessageDuration }
          );

          console.log(wwof_settings_debug_var.failed_force_fetch_update_data_txt);
          console.log(data);
          console.log("----------");
        }
      })
      .fail(function (jqXHR, textStatus, errorThrown) {
        toastr.error("", wwof_settings_debug_var.failed_force_fetch_update_data_txt, {
          closeButton: true,
          showDuration: errorMessageDuration,
        });

        console.log(wwof_settings_debug_var.failed_force_fetch_update_data_txt);
        console.log(jqXHR);
        console.log("----------");
      })
      .always(function () {
        $this
          .removeAttr("disabled")
          .siblings(".spinner")
          .css("display", "none")
          .css("visibility", "hidden");
      });
  });
});
