<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWLC_WWS_License_Manager')) {

    class WWLC_WWS_License_Manager {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * Property that holds the single main instance of WWLC_WWS_License_Manager.
         *
         * @since 1.6.3
         * @access private
         * @var WWLC_WWS_License_Manager
         */
        private static $_instance;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        /**
         * WWLC_WWS_License_Manager constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_WWS_License_Manager model.
         *
         * @access public
         * @since 1.6.3
         */
        public function __construct($dependencies) {}

        /**
         * Ensure that only one instance of WWLC_WWS_License_Manager is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_WWS_License_Manager model.
         *
         * @return WWLC_WWS_License_Manager
         * @since 1.6.3
         */
        public static function instance($dependencies = null) {

            if (!self::$_instance instanceof self) {
                self::$_instance = new self($dependencies);
            }

            return self::$_instance;

        }

        /*
        |---------------------------------------------------------------------------------------------------------------
        | WooCommerce WholeSale Suit License Settings
        |---------------------------------------------------------------------------------------------------------------
         */

        /**
         * Register general wws license settings page in a multi-site environment.
         *
         * @since 1.11
         * @access public
         */
        public function register_ms_wws_licenses_settings_menu() {

            /*
             * Since we don't have a primary plugin to add this license settings, we have to check first if other plugins
             * belonging to the WWS plugin suite has already added a license settings page.
             */
            if (!defined('WWS_LICENSE_SETTINGS_PAGE')) {

                if (!defined('WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN')) {
                    define('WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN', 'wwlc');
                }

                add_menu_page(
                    __("WWS License", "woocommerce-wholesale-lead-capture"),
                    __("WWS License", "woocommerce-wholesale-lead-capture"),
                    "manage_sites",
                    "wws-ms-license-settings",
                    array(self::instance(), "generate_wws_licenses_settings_page")
                );

                // We define this constant with the text domain of the plugin who added the settings page.
                define('WWS_LICENSE_SETTINGS_PAGE', 'woocommerce-wholesale-lead-capture');

            }

        }

        /**
         * Register general wws license settings page.
         *
         * @since 1.0.1
         * @access public
         */
        public function register_wws_license_settings_menu() {

            /*
             * Since we don't have a primary plugin to add this license settings, we have to check first if other plugins
             * belonging to the WWS plugin suite has already added a license settings page.
             */
            if (!defined('WWS_LICENSE_SETTINGS_PAGE')) {

                if (!defined('WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN')) {
                    define('WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN', 'wwlc');
                }

                // Register WWS Settings Menu
                add_submenu_page(
                    'options-general.php', // Settings
                    __('WooCommerce WholeSale Suit License Settings', 'woocommerce-wholesale-lead-capture'),
                    __('WWS License', 'woocommerce-wholesale-lead-capture'),
                    'manage_options',
                    'wwc_license_settings',
                    array(self::instance(), "generate_wws_licenses_settings_page")
                );

                // We define this constant with the text domain of the plugin who added the settings page.
                define('WWS_LICENSE_SETTINGS_PAGE', 'woocommerce-wholesale-lead-capture');

            }

        }

        /**
         * Register general wws license settings page.
         *
         * @since 1.0.1
         */
        public function generate_wws_licenses_settings_page() {

            require_once WWLC_PLUGIN_DIR . 'views/wws-license-settings/view-wwlc-general-wws-settings-page.php';

        }

        /**
         * Add WWLC specific WWS license header markup.
         *
         * @since 1.0.1
         * @access public
         */
        public function wwcLicenseSettingsHeader() {

            ob_start();

            if (isset($_GET['tab'])) {
                $tab = $_GET['tab'];
            } else {
                $tab = WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN;
            }

            global $wp;

            if (is_multisite()) {

                $wwlc_license_settings_url = get_site_url() . "/wp-admin/network/admin.php?page=wws-ms-license-settings&tab=wwlc";

            } else {

                $wwlc_license_settings_url = get_site_url() . "/wp-admin/options-general.php?page=wwc_license_settings&tab=wwlc";

            }?>

			<a href="<?php echo $wwlc_license_settings_url; ?>" class="nav-tab <?php echo ($tab == "wwlc") ? "nav-tab-active" : ""; ?>"><?php _e('Wholesale Lead', 'woocommerce-wholesale-lead-capture');?></a>

			<?php echo ob_get_clean();

        }

        /**
         * Add WWLC specific WWS license settings markup.
         *
         * @since 1.0.1
         * @access public
         */
        public function wwcLicenseSettingsPage() {

            ob_start();

            require_once WWLC_PLUGIN_DIR . 'views/wws-license-settings/view-wwlc-wws-settings-page.php';

            echo ob_get_clean();

        }

        /*
        |---------------------------------------------------------------------------------------------------------------
        | AJAX
        |---------------------------------------------------------------------------------------------------------------
         */

        /**
         * Save wwlc license details.
         *
         * @param null $license_details
         * @return bool
         *
         * @since 1.0.1
         * @since 1.11 Updated to use new license manager
         */
        public function ajax_activate_license($license_details = null) {

            if (!defined("DOING_AJAX") || !DOING_AJAX) {

                $response = array('status' => 'fail', 'error_msg' => __('Invalid AJAX Operation', 'woocommerce-wholesale-lead-capture'));

            } elseif (!isset($_POST['license_email']) || !isset($_POST['license_key']) || !isset($_POST['ajax_nonce'])) {

                $response = array('status' => 'fail', 'error_msg' => __('Required parameters not supplied', 'woocommerce-wholesale-lead-capture'));

            } elseif (!check_ajax_referer('wwlc_activate_license', 'ajax_nonce', false)) {

                $response = array('status' => 'fail', 'error_msg' => __('Security check failed', 'woocommerce-wholesale-lead-capture'));

            } else {

                $activation_email = trim($_POST['license_email']);
                $license_key = trim($_POST['license_key']);
                $activation_url = add_query_arg(array(
                    'activation_email' => urlencode($activation_email),
                    'license_key' => $license_key,
                    'site_url' => home_url(),
                    'software_key' => 'WWLC',
                    'multisite' => is_multisite() ? 1 : 0,
                ), apply_filters('wwlc_license_activation_url', WWLC_LICENSE_ACTIVATION_URL));

                // Store data even if not valid license
                if (is_multisite()) {

                    update_site_option(WWLC_OPTION_LICENSE_EMAIL, $activation_email);
                    update_site_option(WWLC_OPTION_LICENSE_KEY, $license_key);

                } else {

                    update_option(WWLC_OPTION_LICENSE_EMAIL, $activation_email);
                    update_option(WWLC_OPTION_LICENSE_KEY, $license_key);

                }

                $option = array(
                    'timeout' => 10, //seconds
                    'headers' => array('Accept' => 'application/json'),
                );

                $result = wp_remote_retrieve_body(wp_remote_get($activation_url, $option));

                if (empty($result)) {

                    if (is_multisite()) {
                        delete_site_option(WWLC_LICENSE_EXPIRED);
                    } else {
                        delete_option(WWLC_LICENSE_EXPIRED);
                    }

                    $response = array('status' => 'fail', 'error_msg' => __('Failed to activate license. Failed to connect to activation access point. Please contact plugin support.', 'woocommerce-wholesale-lead-capture'));

                } else {

                    $result = json_decode($result);

                    if (empty($result) || !property_exists($result, 'status')) {

                        if (is_multisite()) {
                            delete_site_option(WWLC_LICENSE_EXPIRED);
                        } else {
                            delete_option(WWLC_LICENSE_EXPIRED);
                        }

                        $response = array('status' => 'fail', 'error_msg' => __('Failed to activate license. Activation access point return invalid response. Please contact plugin support.', 'woocommerce-wholesale-lead-capture'));

                    } else {

                        if ($result->status === 'success') {

                            if (is_multisite()) {

                                delete_site_option(WWLC_LICENSE_EXPIRED);
                                update_site_option(WWLC_LICENSE_ACTIVATED, 'yes');

                            } else {

                                delete_option(WWLC_LICENSE_EXPIRED);
                                update_option(WWLC_LICENSE_ACTIVATED, 'yes');

                            }

                            $response = array('status' => $result->status, 'success_msg' => $result->success_msg);

                        } else {

                            if (is_multisite()) {
                                update_site_option(WWLC_LICENSE_ACTIVATED, 'no');
                            } else {
                                update_option(WWLC_LICENSE_ACTIVATED, 'no');
                            }

                            $response = array('status' => $result->status, 'error_msg' => $result->error_msg);

                            // Remove any locally stored update data if there are any
                            $wp_site_transient = get_site_transient('update_plugins');

                            if ($wp_site_transient) {

                                $wwlc_plugin_basename = 'woocommerce-wholesale-lead-capture/woocommerce-wholesale-lead-capture.php';

                                if (isset($wp_site_transient->checked) && is_array($wp_site_transient->checked) && array_key_exists($wwlc_plugin_basename, $wp_site_transient->checked)) {
                                    unset($wp_site_transient->checked[$wwlc_plugin_basename]);
                                }

                                if (isset($wp_site_transient->response) && is_array($wp_site_transient->response) && array_key_exists($wwlc_plugin_basename, $wp_site_transient->response)) {
                                    unset($wp_site_transient->response[$wwlc_plugin_basename]);
                                }

                                set_site_transient('update_plugins', $wp_site_transient);

                                wp_update_plugins();

                            }

                            // Check if this license is expired
                            if (property_exists($result, 'expiration_timestamp')) {

                                $response['expired_date'] = date('Y-m-d', $result->expiration_timestamp);

                                if (is_multisite()) {
                                    update_site_option(WWLC_LICENSE_EXPIRED, $result->expiration_timestamp);
                                } else {
                                    update_option(WWLC_LICENSE_EXPIRED, $result->expiration_timestamp);
                                }

                            } else {

                                if (is_multisite()) {
                                    delete_site_option(WWLC_LICENSE_EXPIRED);
                                } else {
                                    delete_option(WWLC_LICENSE_EXPIRED);
                                }

                            }

                        }

                    }

                }

            }

            @header('Content-Type: application/json; charset=' . get_option('blog_charset'));
            echo wp_json_encode($response);
            wp_die();

        }

        /**
         * AJAX dismiss activate notice.
         *
         * @since 1.11
         * @access public
         */
        public function ajax_dismiss_activate_notice() {

            if (!defined("DOING_AJAX") || !DOING_AJAX) {

                $response = array('status' => 'fail', 'error_msg' => __('Invalid AJAX Operation', 'woocommerce-wholesale-lead-capture'));

            } else {

                if (is_multisite()) {
                    update_site_option(WWLC_ACTIVATE_LICENSE_NOTICE, 'yes');
                } else {
                    update_option(WWLC_ACTIVATE_LICENSE_NOTICE, 'yes');
                }

                $response = array('status' => 'success');

            }

            @header('Content-Type: application/json; charset=' . get_option('blog_charset'));
            echo wp_json_encode($response);
            wp_die();

        }

        /*
        |---------------------------------------------------------------------------------------------------------------
        | Admin Notice
        |---------------------------------------------------------------------------------------------------------------
         */

        /**
         * Activate license notice.
         *
         * @since 1.11
         * @access public
         */
        public function activate_license_notice() {

            if (is_multisite()) {

                $site_activated = get_site_option(WWLC_LICENSE_ACTIVATED);
                $license_notice_muted = get_site_option(WWLC_ACTIVATE_LICENSE_NOTICE);

            } else {

                $site_activated = get_option(WWLC_LICENSE_ACTIVATED);
                $license_notice_muted = get_option(WWLC_ACTIVATE_LICENSE_NOTICE);

            }

            if ($site_activated !== 'yes' && $license_notice_muted !== 'yes') {

                global $wp;

                if (is_multisite()) {

                    $wwlc_license_settings_url = get_site_url() . "/wp-admin/network/admin.php?page=wws-ms-license-settings&tab=wwlc";

                } else {

                    $wwlc_license_settings_url = get_site_url() . "/wp-admin/options-general.php?page=wwc_license_settings&tab=wwlc";

                } ?>

                <div class="notice notice-error is-dismissible wwlc-activate-license-notice">
                    <p class="wwlc-activate-license-notice" style="font-size: 16px;">
                        <?php echo sprintf(__('Please <b><a href="%1$s">activate</a></b> your copy of <b>WooCommerce Wholesale Lead Capture</b> to get the latest updates and have access to support.', 'woocommerce-wholesale-lead-capture'), $wwlc_license_settings_url); ?>
                    </p>
                </div>

                <script>
                    jQuery( document ).ready( function( $ ) {

                        $( '.wwlc-activate-license-notice' ).on( 'click' , '.notice-dismiss' , function() {
                            $.post( window.ajaxurl, { action : 'wwlc_slmw_dismiss_activate_notice' } );
                        } );

                    } );
                </script>

            <?php }

        }

        /*
        |--------------------------------------------------------------------------
        | Execute license manager
        |--------------------------------------------------------------------------
         */

        /**
         * Execute model.
         *
         * @since 1.11
         * @access public
         */
        public function run() {

            // Ajax
            add_action('wp_ajax_wwlc_activate_license', array($this, 'ajax_activate_license'));
            add_action('wp_ajax_wwlc_slmw_dismiss_activate_notice', array($this, 'ajax_dismiss_activate_notice'));

            if (is_multisite()) {

                // Network admin notice
                add_action('network_admin_notices', array($this, 'activate_license_notice'));

                // Access license page if wwp and wwlc are network active and accesing via the main blog url. Subsites will be blocked.
                if (is_plugin_active_for_network('woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php') &&
                    is_plugin_active_for_network('woocommerce-wholesale-lead-capture/woocommerce-wholesale-lead-capture.bootstrap.php') &&
                    get_current_blog_id() === 1) {

                    // Add WooCommerce Wholesale Suit License Settings In Multi-Site Environment
                    add_action("network_admin_menu", array($this, 'register_ms_wws_licenses_settings_menu'));

                    // Add WWS License Settings Header Tab Item
                    add_action("wws_action_license_settings_tab", array($this, 'wwcLicenseSettingsHeader'));

                    // Add WWS License Settings Page (WWLC)
                    add_action("wws_action_license_settings_wwlc", array($this, 'wwcLicenseSettingsPage'));

                }

            } else {

                // Admin Notice
                add_action('admin_notices', array($this, 'activate_license_notice'));

                // Add WooCommerce Wholesale Suit License Settings
                add_action("admin_menu", array($this, 'register_wws_license_settings_menu'));

                // Add WWS License Settings Header Tab Item
                add_action("wws_action_license_settings_tab", array($this, 'wwcLicenseSettingsHeader'));

                // Add WWS License Settings Page (WWLC)
                add_action("wws_action_license_settings_wwlc", array($this, 'wwcLicenseSettingsPage'));

            }

        }

    }

}