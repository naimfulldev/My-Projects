jQuery(document).ready(function ($) {
  $("#wwof_clear_product_caching").click(function () {
    var $this = $(this);

    $this
      .attr("disabled", "disabled")
      .siblings(".spinner")
      .css("visibility", "visible");

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "wwof_clear_product_transients_cache",
        "ajax-nonce":
          wwof_settings_cache_args.nonce_wwof_clear_product_transients_cache,
      },
      dataType: "json",
    })
      .done(function (data) {
        if (data.status === "success") alert(data.success_msg);
        else {
          alert(data.error_msg);
          console.log(data);
        }
      })
      .fail(function (jqxhr) {
        alert(
          wwof_settings_cache_args.i18n_fail_query_args_transients_clear_cache
        );
        console.log(jqxhr);
      })
      .always(function () {
        $this
          .removeAttr("disabled")
          .siblings(".spinner")
          .css("visibility", "hidden");
      });
  });
});
