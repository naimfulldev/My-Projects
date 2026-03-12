jQuery(document).ready(function ($) {
    /**=================================================================================================================
     * Variables
     =================================================================================================================*/
    var $wwp_show_wholesale_price_chkbox = $(
            "#wwp_prices_settings_show_wholesale_prices_to_non_wholesale"
        ),
        $fieldset = $wwp_show_wholesale_price_chkbox.closest("fieldset"),
        show_in_shop_check = "",
        show_in_product_check = "",
        show_in_wwof_check = "";

    /**=================================================================================================================
     * Functions
     =================================================================================================================*/

    // Check if it will show "Click to See Wholesale Prices" in single products page
    if (wwp_non_wholesale_settings_js_var.show_in_products == "yes") {
        show_in_product_check = "checked";
    } else {
        show_in_product_check = "";
    }

    // Check if it will show "Click to See Wholesale Prices" in Shops Archives
    if (wwp_non_wholesale_settings_js_var.show_in_shop == "yes") {
        show_in_shop_check = "checked";
    } else {
        show_in_shop_check = "";
    }

    // Check if it will show "Click to See Wholesale Prices" in Wholesale Order Form
    // Precondition: WWOF Plugin should be activated
    if (wwp_non_wholesale_settings_js_var.show_in_wwof == "yes") {
        show_in_wwof_check = "checked";
    } else {
        show_in_wwof_check = "";
    }

    /**
     * This function is responsible for rendering controls in the Price options of the Wholesale Price settings
     * @since 1.15.0
     * @returns string containing html tags and fields
     */
    function render_show_wholesale_price_to_non_wholesales() {
        var wholesale_roles = "",
            wholesale_role_selection = "",
            selected = "",
            register_text_notice = "",
            register_text_label_inactive = "",
            wwof_notice_inactive_text = "";

        wholesale_roles = wwp_non_wholesale_settings_js_var.wholesale_roles;
        wholesale_roles_options =
            wwp_non_wholesale_settings_js_var.wholesale_role_options;
        // Check if wwlc is active/installed, if not warn store owner
        if (!wwp_non_wholesale_settings_js_var.is_wwlc_active) {
            register_text_notice =
                "<div class='notice  wwp-wwlc-inactive'><p><i class='fa fa-star checked'></i><strong> Recommended Plugin: WooCommerce Wholesale Lead Capture</strong></p><p>Lead Capture adds an additional \"register text\" link to the wholesale prices box on the front end to help you capture even more wholesale leads.</p><p>" +
                wwp_non_wholesale_settings_js_var.wwlc_admin_notice +
                "</p><p>Bonus: Wholesale Prices lite users get <strong class='discounted'>50% off regular price</strong>, automatically applied at checkout.</p></div>";
            register_text_label_inactive =
                "style='color: rgba(44,51,56,.5) !important;'";
        }

        if (!wwp_non_wholesale_settings_js_var.is_wwof_active) {
            wwof_notice_inactive_text =
                "<small>To use this option, you must have <strong>WooCommerce Wholesale Order Form</strong> plugin installed and activated.</small>";
        }

        // Get wholesale roles
        if (wwp_non_wholesale_settings_js_var.is_wwpp_active) {
            for (var key in wholesale_roles) {

            if (wholesale_roles_options.indexOf(key) >= 0) {
                selected = "selected";
            } else {
                selected = "";
            }

            wholesale_role_selection +=
                '<option value="' +
                key +
                '" ' +
                selected +
                ">" +
                wholesale_roles[key]["roleName"] +
                "</option>";
            }

        }else{
            
            selected = "selected";

            for (var key in wholesale_roles) {

                wholesale_role_selection +=
                '<option value="' +
                key +
                '" ' +
                selected +
                ">" +
                wholesale_roles[key]["roleName"] +
                "</option>";
            }
        }

        // Control container
        var control_container =
            "<!--Begin: #wwp-non-wholesale-settings-->" +
            "<div id='wwp-non-wholesale-settings' style='max-width: 680px !important;'>" +
            "<!--Begin: .wwp-non-wholesale-setting-controls -->" +
            "<div class='wwp-non-wholesale-setting-controls'>" +
            "<table class='form-table'>" +
            "<tbody>" +
            //----------------------------------------------------------------------------------------------------------
            // Show Wholesale Prices in pages
            //----------------------------------------------------------------------------------------------------------
            "<tr valign='top'><th class='titledesc' scope='row'>Locations</th><td>" +
            "<label for='wwp_non_wholesale_show_in_shop' style='padding-right: 20px;'><input id='wwp_non_wholesale_show_in_shop' name='wwp_non_wholesale_show_in_shop' class='wwp_non_wholesale_show_in_shop' type='checkbox' value='yes'" +
            show_in_shop_check +
            "> Shop Archives</label>" +
            "</td></tr>" +
            "<tr valign='top'><th class='titledesc' scope='row'></th><td>" +
            "<label for='wwp_non_wholesale_show_in_products'><input id='wwp_non_wholesale_show_in_products' name='wwp_non_wholesale_show_in_products' class='wwp_non_wholesale_show_in_products' type='checkbox' value='yes' " +
            show_in_product_check +
            "> Single Product</label>" +
            "</td></tr>" +
            "<tr valign='top'><th class='titledesc' scope='row'></th><td>" +
            "<label for='wwp_non_wholesale_show_in_wwof'><input id='wwp_non_wholesale_show_in_wwof' name='wwp_non_wholesale_show_in_wwof' class='wwp_non_wholesale_show_in_wwof' type='checkbox' value='yes' " +
            show_in_wwof_check +
            "> <span id='wwof_label_text_span'>Wholesale Order Form</span> <p class='description'>" +
            wwof_notice_inactive_text +
            "</p></label>" +
            "</td></tr>" +
            //----------------------------------------------------------------------------------------------------------
            // Wholesale Prices replacement text
            //----------------------------------------------------------------------------------------------------------
            "<tr valign='top'>" +
            "<th class='titledesc' scope='row'>" +
            "<label for='wwp_see_wholesale_prices_replacement_text'>Click to See Wholesale Prices Text" +
            wwp_non_wholesale_settings_js_var.wwp_see_wholesale_prices_replacement_text_tooltip +
            "</label>" +
            "</th>" +
            "<td class='forminp forminp-text' style='padding-right:0;'>" +
            "<input id='wwp_see_wholesale_prices_replacement_text' class='wwp_see_wholesale_prices_replacement_text' name='wwp_see_wholesale_prices_replacement_text' type='text' style='width:100%' placeholder='' value='" +
            wwp_non_wholesale_settings_js_var.wwp_see_wholesale_prices_replacement_text +
            "'>" +
            "</td>" +
            "</tr>" +
            //----------------------------------------------------------------------------------------------------------
            // Wholesale Role Selection
            //----------------------------------------------------------------------------------------------------------
            "<tr valign='top'>" +
            "<th class='titledesc' scope='row'>" +
            "<label for='wwp_wholesale_role_select_chosen'>Wholesale Role(s)" +
            wwp_non_wholesale_settings_js_var.wwp_wholesale_role_select_chosen_tooltip +
            "</label>" +
            "</th>" +
            "<td class='forminp forminp-multiselect' style='padding-right:0;'>" +
            "<select id='wwp_wholesale_role_select_chosen' name='wwp_wholesale_role_select_chosen[]' data-placeholder='" +
            wwp_non_wholesale_settings_js_var.wholesale_role_data_placeholder_txt +
            "' class='wwp_wholesale_role_select_chosen' multiple='multiple' style='width:100%'>" +
            wholesale_role_selection +
            "</select>" +
            "</td>" +
            "</tr>" +
            //----------------------------------------------------------------------------------------------------------
            // Register text
            //----------------------------------------------------------------------------------------------------------
            "<tr valign='top'>" +
            "<th class='titledesc' scope='row'>" +
            "<label for='wwp_price_settings_register_text' " +
            register_text_label_inactive +
            ">Register Text" +
            wwp_non_wholesale_settings_js_var.wwp_price_settings_register_text_tooltip +
            "</label>" +
            "</th>" +
            "<td class='forminp forminp-text' style='padding-right:0;'>" +
            "<input id='wwp_price_settings_register_text' class='wwp_price_settings_register_text' name='wwp_price_settings_register_text' type='text' style='width:100%' placeholder='' value='" +
            wwp_non_wholesale_settings_js_var.wwp_price_settings_register_text +
            "'>" +
            "</td>" +
            "</tr>" +
            "</tbody>" +
            "</table>" +
            "</div>" +
            //----------------------------------------------------------------------------------------------------------
            // Register text - Warning notice will be shown if wwlc is inactive/not installed.
            //----------------------------------------------------------------------------------------------------------
            register_text_notice +
            "</div>" +
            "<!--End: #wwp-non-wholesale-settings -->";

        // Append Control container
        $fieldset.append(control_container);
    }

    /**=================================================================================================================
     * Events
     =================================================================================================================*/
    $(document).on("click", "input[type='checkbox']", function () {
        if (this.checked) {
            this.setAttribute("checked", "checked");
        } else {
            this.removeAttribute("checked");
        }
    });

    function run_events() {
        // Initialize Chosen
        $(".wwp_wholesale_role_select_chosen").chosen({ width: "100%" });

        // Check if WWPP is not active , make wholesale role selection read-only
        $(".wwp_wholesale_role_select_chosen").on(
            "chosen:updated",
            function () {
                if (
                    wwp_non_wholesale_settings_js_var.is_wwpp_active.length == 0
                ) {
                    $(".wwp_wholesale_role_select_chosen").attr(
                        "disabled",
                        "disabled"
                    );
                    $(".wwp_wholesale_role_select_chosen")
                        .data("chosen")
                        .search_field_disabled();
                } else {
                    if (
                        $(".wwp_wholesale_role_select_chosen").attr("disabled")
                    ) {
                        $(".wwp_wholesale_role_select_chosen").removeAttr(
                            "disabled"
                        );
                    }
                }
            }
        );
        $(".wwp_wholesale_role_select_chosen").trigger("chosen:updated");

        // Initialize tooltip
        $(".woocommerce-help-tip").tipTip({
            attribute: "data-tip",
            fadeIn: 50,
            fadeOut: 50,
            delay: 200,
        });

        // Trigger Checkbox change function to show wholesale price to non-wholesale users
        $wwp_show_wholesale_price_chkbox.trigger("change");

        // Check if WWLC is active/installed, if not disable registration text
        if (wwp_non_wholesale_settings_js_var.is_wwlc_active) {
            $("#wwp_price_settings_register_text").prop("disabled", false);
        } else {
            $("#wwp_price_settings_register_text").prop("disabled", true);
        }

        // Check if WWOF is active/installed, if not disable option to show wholesale prices for WWOF
        if (wwp_non_wholesale_settings_js_var.is_wwof_active) {
            $("#wwp_non_wholesale_show_in_wwof").prop("disabled", false);
            $("#wwof_label_text_span").prop("disabled", false);
        } else {
            $("#wwp_non_wholesale_show_in_wwof").prop("disabled", true);
            $("#wwof_label_text_span").prop("disabled", true);
            $("#wwof_label_text_span").css("color", "rgba(44,51,56,.5)");
        }
    }

    $wwp_show_wholesale_price_chkbox.change(function () {
        if ($(this).is(":checked")) {
            $("#wwp-non-wholesale-settings").slideDown();
        } else {
            $("#wwp-non-wholesale-settings").slideUp();
        }
    });

    /**=================================================================================================================
     * Page Load
     =================================================================================================================*/

    // Render controls
    render_show_wholesale_price_to_non_wholesales();

    // Run Events
    run_events();
});
