<?php
/*
Plugin Name:    WooCommerce Wholesale Lead Capture
Plugin URI:     https://wholesalesuiteplugin.com/
Description:    WooCommerce extension to provide functionality of capturing wholesale leads.
Author:         Rymera Web Co
Version:        1.17
Author URI:     https://rymera.com.au/
Text Domain:    woocommerce-wholesale-lead-capture
WC requires at least: 4.0
WC tested up to: 5.9
 */

require_once 'woocommerce-wholesale-lead-capture.functions.php';

// Delete code activation flag on plugin deactivate.
register_deactivation_hook(__FILE__, 'wwlc_global_plugin_deactivate');

add_action('after_setup_theme', function () {

    // Check if any wwlc_required_plugins has been registered for a hook
    if (!has_filter('wwlc_required_plugins')) {
        add_filter('wwlc_required_plugins', function ($required_plugins) {

            // Make sure that WWP is require dependency, you can add additional dependency if needed.
            $required_plugins = array('woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php');

            return $required_plugins;

        }, 10, 1);
    }

    $missing_required_plugins = wwlc_check_plugin_dependencies();

    // Check if woocommerce is active
    if (count($missing_required_plugins) <= 0) {

        // Include Necessary Files
        require_once 'woocommerce-wholesale-lead-capture.options.php';
        require_once 'woocommerce-wholesale-lead-capture.plugin.php';

        // Get Instance of Main Plugin Class
        $wc_wholesale_lead_capture            = WooCommerce_Wholesale_Lead_Capture::instance();
        $GLOBALS['wc_wholesale_lead_capture'] = $wc_wholesale_lead_capture;

        // Execute WWLC
        $wc_wholesale_lead_capture->run();

    } else {

        // Call WWLC admin notices
        wwlc_admin_notices_action();

    }

    // allow admin or shop manager to view registration page.
    wwlc_allow_admin_to_view_registration_page();
});
