<?php
/*
Plugin Name:            WooCommerce Wholesale Order Form
Plugin URI:             https://wholesalesuiteplugin.com/
Description:            WooCommerce Extension to Provide Wholesale Product Listing Functionality
Author:                 Rymera Web Co
Version:                1.21.1
Author URI:             http://rymera.com.au/
Text Domain:            woocommerce-wholesale-order-form
Requires at least:      5.0
Tested up to:           5.8
WC requires at least:   4.0
WC tested up to:        5.9
 */

require_once 'includes/class-wwof-functions.php';

// Delete code activation flag on plugin deactivate.
register_deactivation_hook(__FILE__, array(new WWOF_Functions, 'wwof_global_plugin_deactivate'));

add_action('after_setup_theme', function () {
    $missing_required_plugins = WWOF_Functions::wwof_check_plugin_dependencies();

    // Check if woocommerce is active
    if (count($missing_required_plugins) <= 0) {

        // Include Necessary Files
        require_once 'woocommerce-wholesale-order-form.options.php';
        require_once 'woocommerce-wholesale-order-form.plugin.php';
        require_once 'includes/v2/class-order-form-helpers.php';

        // Get Instance of Main Plugin Class
        $wc_wholesale_order_form            = WooCommerce_WholeSale_Order_Form::instance();
        $GLOBALS['wc_wholesale_order_form'] = $wc_wholesale_order_form;

        /*
        |-------------------------------------------------------------------------------------------------------------------
        | Settings
        |-------------------------------------------------------------------------------------------------------------------
         */

        // Register Settings Page
        add_filter('woocommerce_get_settings_pages', array($wc_wholesale_order_form, 'wwof_plugin_settings'));

        /*
        |---------------------------------------------------------------------------------------------------------------
        | Execute WWOF
        |---------------------------------------------------------------------------------------------------------------
         */
        $wc_wholesale_order_form->run();

    } else {

        /**
         * Provide admin notice to users that a required plugin dependency of WooCommerce Wholesale Order Form plugin is missing.
         *
         * @since 1.6.3
         * @since 1.6.6 Underscore cased the function name and variables.
         */
        function wwof_admin_notices()
        {
            global $missing_required_plugins;
            $adminNoticeMsg = '';

            if (!$missing_required_plugins) {
                $missing_required_plugins = WWOF_Functions::wwof_check_plugin_dependencies();
            }

            foreach ($missing_required_plugins as $plugin) {

                $pluginFile = $plugin['plugin-base'];
                $sptFile    = trailingslashit(WP_PLUGIN_DIR) . plugin_basename($pluginFile);

                $sptInstallText = '<a href="' . wp_nonce_url('update.php?action=install-plugin&plugin=' . $plugin['plugin-key'], 'install-plugin_' . $plugin['plugin-key']) . '">' . __('Click here to install from WordPress.org repo &rarr;', 'woocommerce-wholesale-order-form') . '</a>';

                if (file_exists($sptFile)) {
                    $sptInstallText = '<a href="' . wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $pluginFile . '&amp;plugin_status=all&amp;s', 'activate-plugin_' . $pluginFile) . '" title="' . __('Activate this plugin', 'woocommerce-wholesale-order-form') . '" class="edit">' . __('Click here to activate &rarr;', 'woocommerce-wholesale-order-form') . '</a>';
                }

                $adminNoticeMsg .= sprintf(__('<br/>Please ensure you have the <a href="%1$s" target="_blank">%2$s</a> plugin installed and activated.<br/>', 'woocommerce-wholesale-order-form'), 'http://wordpress.org/plugins/' . $plugin['plugin-key'] . '/', str_replace('Woocommerce', 'WooCommerce', $plugin['plugin-name']));
                $adminNoticeMsg .= $sptInstallText . '<br/>';
            }

            echo '<div class="error">';
            echo '<p>';
            echo __('<b>WooCommerce Wholesale Order Form</b> plugin missing dependency.<br/>', 'woocommerce-wholesale-order-form');
            echo $adminNoticeMsg;
            echo '</p>';
            echo '</div>';

        };

        // Call admin notices
        add_action('admin_notices', 'wwof_admin_notices');

    }

});
