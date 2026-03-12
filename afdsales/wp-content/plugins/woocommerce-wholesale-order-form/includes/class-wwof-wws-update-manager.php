<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

/**
 * Model that houses the logic of updating the plugin.
 *
 * @since 1.11
 */
class WWOF_WWS_Update_Manager
{

    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Property that holds the single main instance of WWOF_WWS_Update_Manager.
     *
     * @since 1.11
     * @access private
     * @var WWOF_WWS_Update_Manager
     */
    private static $_instance;

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.11
     * @access public
     */
    public function __construct()
    {}

    /**
     * Ensure that only one instance of this class is loaded or can be loaded ( Singleton Pattern ).
     *
     * @since 1.11
     * @access public
     *
     * @return WWOF_WWS_Update_Manager
     */
    public static function instance()
    {

        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;

    }

    /**
     * Hijack the WordPress 'set_site_transient' function for 'update_plugins' transient.
     * So now we don't have our own cron to check for updates, we just rely on when WordPress check updates for plugins and themes,
     * and if WordPress does then sets the 'update_plugins' transient, then we hijack it and check for our own plugin update.
     *
     * @since 1.11
     * @access public
     *
     * @param array $update_plugins Update plugins data.
     */
    public function update_check($update_plugins)
    {

        /**
         * Function wp_update_plugins calls set_site_transient( 'update_plugins' , ... ) twice, yes twice
         * so we need to make sure we are on the last call before checking plugin updates
         * the last call will have the checked object parameter
         */
        if (isset($update_plugins->checked)) {
            $this->ping_for_new_version(false);
        }

        /**
         * We try to inject plugin update data if it has any
         * This is to fix the issue about plugin info appearing/disappearing
         * when update page in WordPress is refreshed
         */
        $result = $this->inject_plugin_update(); // Inject new update data if there are any

        if ($result && isset($update_plugins->response) && is_array($update_plugins->response) && !array_key_exists($result['key'], $update_plugins->response)) {
            $update_plugins->response[$result['key']] = $result['value'];
        }

        return $update_plugins;

    }

    /**
     * Ping plugin for new version. Ping static file first, if indeed new version is available, trigger update data request.
     *
     * @since 1.11
     * @since 1.19.4 We added new parameter $force. This will serve as a flag if we are going to "forcefully" fetch the latest update data from the server.
     *
     * @param $force Flag to determine whether to "forcefully" fetch the latest update data from the server.
     * @access public
     */
    public function ping_for_new_version($force = false)
    {

        $license_activated = is_multisite() ? get_site_option(WWOF_LICENSE_ACTIVATED) : get_option(WWOF_LICENSE_ACTIVATED);

        if ($license_activated !== 'yes') {

            if (is_multisite()) {
                delete_site_option(WWOF_UPDATE_DATA);
            } else {
                delete_option(WWOF_UPDATE_DATA);
            }

            return;

        }

        $retrieving_udpate_data = is_multisite() ? get_site_option(WWOF_RETRIEVING_UPDATE_DATA) : get_option(WWOF_RETRIEVING_UPDATE_DATA);
        if ($retrieving_udpate_data === 'yes') {
            return;
        }

        /**
         * @since 1.19.4
         *
         * Only attempt to get the existing saved update data when the operation is not forced.
         * Else, if it is forced, we ignore the existing update data if any
         * and forcefully fetch the latest update data from our server.
         */
        if (!$force) {
            $update_data = is_multisite() ? get_site_option(WWOF_UPDATE_DATA) : get_option(WWOF_UPDATE_DATA);
        } else {
            $update_data = null;
        }

        /**
         * @since 1.19.4
         * Remove get_headers call of the download url from update data.
         * Comment get_headers call of the download url from update data.
         * We suspected that the commented code below is one of the reasons that is stressing the update server.
         * We think that since the latest update of wp, the auto update feature, it will fire up the update check over and over again.
         * This will in turn fire the commented code below, which will trigger the update server to log the request even if it is just a
         * header request. Log means logging the request into the db table. So the php and db is executed by the commented code below.
         * This scenario somehow is worsen via multi site setups.
         */

        // if ($update_data) {

        //     if (isset($update_data->download_url)) {

        //         $file_headers = @get_headers($update_data->download_url);

        //         if (strpos($file_headers[0], '404') !== false) {

        //             // For some reason the update url is not valid anymore, delete the update data.
        //             if (is_multisite()) {
        //                 delete_site_option(WWOF_UPDATE_DATA);
        //             } else {
        //                 delete_option(WWOF_UPDATE_DATA);
        //             }

        //             $update_data = null;

        //         }

        //     } else {

        //         // For some reason the update url is not valid anymore, delete the update data.
        //         if (is_multisite()) {
        //             delete_site_option(WWOF_UPDATE_DATA);
        //         } else {
        //             delete_option(WWOF_UPDATE_DATA);
        //         }

        //         $update_data = null;

        //     }

        // }

        /**
         * Even if the update data is still valid, we still go ahead and do static json file ping.
         * The reason is on WooCommerce 3.3.x , it seems WooCommerce do not regenerate the download url every time you change the downloadable zip file on WooCommerce store.
         * The side effect is, the download url is still valid, points to the latest zip file, but the update info could be outdated coz we check that if the download url
         * is still valid, we don't check for update info, and since the download url will always be valid even after subsequent release of the plugin coz WooCommerce is reusing the url now
         * then there will be a case our update info is outdated. So that is why we still need to check the static json file, even if update info is still valid.
         */

        $option = array(
            'timeout' => 10, //seconds coz only static json file ping
            'headers' => array('Accept' => 'application/json'),
        );

        $response = wp_remote_retrieve_body(wp_remote_get(apply_filters('wwof_plugin_new_version_ping_url', WWOF_STATIC_PING_FILE), $option));

        if (!empty($response)) {

            $response = json_decode($response);

            if (!empty($response) && property_exists($response, 'version')) {

                $installed_version = is_multisite() ? get_site_option(WWOF_OPTION_INSTALLED_VERSION) : get_option(WWOF_OPTION_INSTALLED_VERSION);

                if ((!$update_data && version_compare($response->version, $installed_version, '>')) ||
                    ($update_data && version_compare($response->version, $update_data->latest_version, '>'))) {

                    if (is_multisite()) {
                        update_site_option(WWOF_RETRIEVING_UPDATE_DATA, 'yes');
                    } else {
                        update_option(WWOF_RETRIEVING_UPDATE_DATA, 'yes');
                    }

                    // Fetch software product update data
                    if (is_multisite()) {
                        $this->_fetch_software_product_update_data(get_site_option(WWOF_OPTION_LICENSE_EMAIL), get_site_option(WWOF_OPTION_LICENSE_KEY), home_url());
                    } else {
                        $this->_fetch_software_product_update_data(get_option(WWOF_OPTION_LICENSE_EMAIL), get_option(WWOF_OPTION_LICENSE_KEY), home_url());
                    }

                    if (is_multisite()) {
                        delete_site_option(WWOF_RETRIEVING_UPDATE_DATA);
                    } else {
                        delete_option(WWOF_RETRIEVING_UPDATE_DATA);
                    }

                } elseif ($update_data && version_compare($update_data->latest_version, $installed_version, '==')) {

                    /**
                     * We delete the option data if update is already installed
                     * We encountered a bug when updating the plugin via the dashboard updates page
                     * The update is successful but the update notice does not disappear
                     */
                    if (is_multisite()) {
                        delete_site_option(WWOF_UPDATE_DATA);
                    } else {
                        delete_option(WWOF_UPDATE_DATA);
                    }

                }

            }

        }

    }

    /**
     * Fetch software product update data.
     *
     * @since 1.11
     * @access public
     *
     * @param string $activation_email Activation email.
     * @param string $license_key      License key.
     * @param string $site_url         Site url.
     */
    private function _fetch_software_product_update_data($activation_email, $license_key, $site_url)
    {

        $update_check_url = add_query_arg(array(
            'activation_email' => urlencode($activation_email),
            'license_key'      => $license_key,
            'site_url'         => $site_url,
            'software_key'     => 'WWOF',
            'multisite'        => is_multisite() ? 1 : 0,
        ), apply_filters('wwof_software_product_update_data_url', WWOF_UPDATE_DATA_URL));

        $option = array(
            'timeout' => 30, // seconds for worst case the server is choked and takes little longer to get update data ( this is an ajax end point )
            'headers' => array('Accept' => 'application/json'),
        );

        $result = wp_remote_retrieve_body(wp_remote_get($update_check_url, $option));

        if (!empty($result)) {

            $result = json_decode($result);

            if (!empty($result) && $result->status === 'success' && !empty($result->software_update_data)) {

                if (is_multisite()) {
                    update_site_option(WWOF_UPDATE_DATA, $result->software_update_data);
                } else {
                    update_option(WWOF_UPDATE_DATA, $result->software_update_data);
                }

            } else {

                if (is_multisite()) {
                    delete_site_option(WWOF_UPDATE_DATA);
                } else {
                    delete_option(WWOF_UPDATE_DATA);
                }

                if (!empty($result) && $result->status === 'fail' &&
                    isset($result->error_key) &&
                    in_array($result->error_key, array('invalid_license', 'expired_license'))) {

                    // Invalid license
                    if (is_multisite()) {
                        update_site_option(WWOF_LICENSE_ACTIVATED, 'no');
                    } else {
                        update_option(WWOF_LICENSE_ACTIVATED, 'no');
                    }

                    // Check if license is expired
                    if ($result->error_key === 'expired_license') {

                        if (is_multisite()) {
                            update_site_option(WWOF_LICENSE_EXPIRED, $result->expiration_timestamp);
                        } else {
                            update_option(WWOF_LICENSE_EXPIRED, $result->expiration_timestamp);
                        }

                    } else {

                        if (is_multisite()) {
                            delete_site_option(WWOF_LICENSE_EXPIRED);
                        } else {
                            delete_option(WWOF_LICENSE_EXPIRED);
                        }

                    }

                }

            }

        }

    }

    /**
     * Inject plugin update info to plugin update details page.
     * Note, this is only triggered when there is a new update and the "View version <new version here> details" link is clicked.
     * In short, the pure purpose for this is to provide details and info the update info popup.
     *
     * @since 1.11
     * @access public
     *
     * @param false|object|array $result The result object or array. Default false.
     * @param string             $action The type of information being requested from the Plugin Install API.
     * @param object             $args   Plugin API arguments.
     * @return array Plugin update info.
     */
    public function inject_plugin_update_info($result, $action, $args)
    {

        $license_activated = is_multisite() ? get_site_option(WWOF_LICENSE_ACTIVATED) : get_option(WWOF_LICENSE_ACTIVATED);

        if ($license_activated === 'yes' && $action == 'plugin_information' && isset($args->slug) && $args->slug == 'woocommerce-wholesale-order-form') {

            $software_update_data = is_multisite() ? get_site_option(WWOF_UPDATE_DATA) : get_option(WWOF_UPDATE_DATA);

            if ($software_update_data) {

                $update_info = new \StdClass;

                $update_info->name               = 'WooCommerce Wholesale Order Form';
                $update_info->slug               = 'woocommerce-wholesale-order-form';
                $update_info->version            = $software_update_data->latest_version;
                $update_info->tested             = $software_update_data->tested_up_to;
                $update_info->last_updated       = $software_update_data->last_updated;
                $update_info->homepage           = $software_update_data->home_page;
                $update_info->author             = sprintf('<a href="%s" target="_blank">%s</a>', $software_update_data->author_url, $software_update_data->author);
                $update_info->download_link      = $software_update_data->download_url;
                $update_info->disable_autoupdate = true;
                $update_info->sections           = array(
                    'description'  => $software_update_data->description,
                    'installation' => $software_update_data->installation,
                    'changelog'    => $software_update_data->changelog,
                    'support'      => $software_update_data->support,
                );

                $update_info->icons = array(
                    '1x'      => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwof-icon-128x128.jpg',
                    '2x'      => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwof-icon-256x256.jpg',
                    'default' => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwof-icon-256x256.jpg',
                );

                $update_info->banners = array(
                    'low'  => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwof-banner-772x250.jpg',
                    'high' => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwof-banner-1544x500.jpg',
                );

                return $update_info;

            }

        }

        return $result;

    }

    /**
     * When wordpress fetch 'update_plugins' transient ( Which holds various data regarding plugins, including which have updates ),
     * we inject our plugin update data in, if any. It is saved on WWOF_UPDATE_DATA option.
     * It is important we dont delete this option until the plugin have successfully updated.
     * The reason is we are hooking ( and we should do it this way ), on transient read.
     * So if we delete this option on first transient read, then subsequent read will not include our plugin update data.
     *
     * It also checks the validity of the update url. There could be edge case where we stored the update data locally as an option,
     * then later on the store, the product was deleted or any action occurred that would deem the update data invalid.
     * So we check if update url is still valid, if not, we remove the locally stored update data.
     *
     * @since 1.11
     * Refactor codebase to adapt being called on set_site_transient function.
     * We don't need to check for software update data validity as its already been checked on ping_for_new_version
     * and this function is immediately called right after that.
     * @access public
     *
     * @param array $updates Plugin updates data.
     * @return array Filtered plugin updates data.
     */
    public function inject_plugin_update()
    {

        $license_activated = is_multisite() ? get_site_option(WWOF_LICENSE_ACTIVATED) : get_option(WWOF_LICENSE_ACTIVATED);
        if ($license_activated !== 'yes') {
            return false;
        }

        $software_update_data = is_multisite() ? get_site_option(WWOF_UPDATE_DATA) : get_option(WWOF_UPDATE_DATA);

        if ($software_update_data) {

            $update = new \stdClass();

            $update->id                 = $software_update_data->download_id;
            $update->slug               = 'woocommerce-wholesale-order-form';
            $update->plugin             = WWOF_PLUGIN_BASE_NAME;
            $update->new_version        = $software_update_data->latest_version;
            $update->url                = WWOF_PLUGIN_SITE_URL;
            $update->package            = $software_update_data->download_url;
            $update->tested             = $software_update_data->tested_up_to;
            $update->disable_autoupdate = true;

            $update->icons = array(
                '1x'      => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwof-icon-128x128.jpg',
                '2x'      => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwof-icon-256x256.jpg',
                'default' => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwof-icon-256x256.jpg',
            );

            $update->banners = array(
                '1x'      => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwof-banner-772x250.jpg',
                '2x'      => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwof-banner-1544x500.jpg',
                'default' => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwof-banner-1544x500.jpg',
            );

            return array(
                'key'   => WWOF_PLUGIN_BASE_NAME,
                'value' => $update,
            );

        }

        return false;

    }

    /**
     * Delete the plugin update data after the plugin successfully updated.
     *
     * References:
     * https://stackoverflow.com/questions/24187990/plugin-update-hook
     * https://codex.wordpress.org/Plugin_API/Action_Reference/upgrader_process_complete
     *
     * @since 1.11
     * @access public
     *
     * @param Plugin_Upgrader $upgrader_object Plugin_Upgrader instance.
     * @param array           $options         Options.
     */
    public function after_plugin_update($upgrader_object, $options)
    {

        if ($options['action'] == 'update' && $options['type'] == 'plugin' && isset($options['plugins']) && is_array($options['plugins'])) {

            foreach ($options['plugins'] as $each_plugin) {

                if ($each_plugin == WWOF_PLUGIN_BASE_NAME) {

                    if (is_multisite()) {
                        delete_site_option(WWOF_UPDATE_DATA);
                    } else {
                        delete_option(WWOF_UPDATE_DATA);
                    }

                    break;

                }

            }

        }

    }

    /**
     * Disable auto update for plugin.
     * Servers are bombarded with download requests for plugin updates from the same customers with each requests gap are only a few minutes.
     * There's a chance that this issue is caused by WP auto update script. This will ensure that our premium plugins will not be auto updated.
     *
     * @since 1.19.3
     * @access public
     */
    public function disable_auto_update($update, $item)
    {
        if (isset($item->plugin) && WWOF_PLUGIN_BASE_NAME === $item->plugin) {
            return false;
        }

        return $update;
    }

    /**
     * Force fetch the latest update data from our server.
     *
     * @since 1.19.4
     * @access public
     */
    public function wwof_force_fetch_update_data()
    {

        /**
         * Force check and fetch the update data of the plugin.
         * Will save the update data into the WWOF_UPDATE_DATA option.
         */
        $this->ping_for_new_version(true); // Force check

        /**
         * Get the update data from the WWOF_UPDATE_DATA option.
         * Returned data is pre-formatted.
         */
        $update_data = $this->inject_plugin_update(); // Inject new update data if there are any

        if ($update_data) {

            /**
             * Get wp update transient data.
             * Automatically unserializes the returned value.
             */
            $update_transient = is_multisite() ? get_site_option("_site_transient_update_plugins", false) : get_option("_site_transient_update_plugins", false);

            if ($update_transient && isset($update_transient->response) && is_array($update_transient->response)) {

                // Inject into the wp update data our latest plugin update data
                $update_transient->response[$update_data['key']] = $update_data['value'];

                // Update wp update data transient
                if (is_multisite()) {
                    update_site_option("_site_transient_update_plugins", $update_transient);
                } else {
                    update_option("_site_transient_update_plugins", $update_transient);
                }

            }

        }

        if (defined('DOING_AJAX') && DOING_AJAX) {

            @header('Content-Type: application/json; charset=' . get_option('blog_charset'));
            echo wp_json_encode(array('status' => 'success'));
            wp_die();

        } else {
            return true;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Execute update manager
    |--------------------------------------------------------------------------
     */

    /**
     * Execute Model.
     *
     * @since 1.11
     * @access public
     */
    public function run()
    {

        add_filter('pre_set_site_transient_update_plugins', array($this, 'update_check'));
        add_filter('plugins_api', array($this, 'inject_plugin_update_info'), 10, 3);
        add_action('upgrader_process_complete', array($this, 'after_plugin_update'), 10, 2);
        add_filter('auto_update_plugin', array($this, 'disable_auto_update'), 10, 2);
        add_action("wp_ajax_wwof_force_fetch_update_data", array($this, 'wwof_force_fetch_update_data'));

    }

}
