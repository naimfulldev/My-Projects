<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWOF_Bootstrap')) {

    class WWOF_Bootstrap
    {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * Property that holds the single main instance of WWOF_Bootstrap.
         *
         * @since 1.6.6
         * @access private
         * @var WWOF_Bootstrap
         */
        private static $_instance;

        /**
         * Current WWOF version.
         *
         * @since 1.6.6
         * @access private
         * @var int
         */
        private $_wwof_current_version;

        /**
         * WWOF_AJAX object.
         *
         * @since 1.6.6
         * @access private
         * @var WWOF_AJAX
         */
        private $_wwof_ajax;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        /**
         * WWOF_Bootstrap constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWOF_Bootstrap model.
         *
         * @access public
         * @since 1.6.6
         */
        public function __construct($dependencies)
        {

            $this->_wwof_current_version = $dependencies['WWOF_CURRENT_VERSION'];
            $this->_wwof_ajax            = $dependencies['WWOF_AJAX'];

        }

        /**
         * Ensure that only one instance of WWOF_Bootstrap is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWOF_Bootstrap model.
         *
         * @return WWOF_Bootstrap
         * @since 1.6.6
         */
        public static function instance($dependencies = null)
        {

            if (!self::$_instance instanceof self) {
                self::$_instance = new self($dependencies);
            }

            return self::$_instance;

        }

        /**
         * Load plugin text domain.
         *
         * @since 1.2.0
         * @since 1.6.6 Refactor codebase and move to its proper model
         */
        public function wwof_load_plugin_text_domain()
        {

            load_plugin_textdomain('woocommerce-wholesale-order-form', false, WWOF_PLUGIN_BASE_PATH . 'languages/');

        }

        /*
        |------------------------------------------------------------------------------------------------------------------
        | Bootstrap/Shutdown Functions
        |------------------------------------------------------------------------------------------------------------------
         */

        /**
         * Plugin activation hook callback.
         *
         * @param bool $network_wide
         *
         * @since 1.0.0
         * @since 1.6.4 Multisite Compatibility
         * @since 1.6.6 Refactor codebase and move to its proper model
         */
        public function wwof_activate($network_wide)
        {

            global $wpdb;

            if (is_multisite()) {

                if ($network_wide) {

                    // get ids of all sites
                    $blogIDs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

                    foreach ($blogIDs as $blogID) {

                        switch_to_blog($blogID);
                        $this->wwof_activate_action($blogID);

                    }

                    restore_current_blog();

                } else {

                    // activated on a single site, in a multi-site
                    $this->wwof_activate_action($wpdb->blogid);

                }

            } else {

                // activated on a single site
                $this->wwof_activate_action($wpdb->blogid);

            }

        }

        /**
         * Perform actions on plugin activation.
         *
         * @since 1.6.4
         * @since 1.6.6 Refactor codebase and move to its proper model.
         * @since 1.11 Refactor support for multisite setup.
         */
        private function wwof_activate_action()
        {

            /**
             * Previously multisite installs site store license options using normal get/add/update_option functions.
             * These stores the option on a per sub-site basis. We need move these options network wide in multisite setup
             * via get/add/update_site_option functions.
             */
            if (is_multisite()) {

                if ($license_email = get_option(WWOF_OPTION_LICENSE_EMAIL)) {

                    update_site_option(WWOF_OPTION_LICENSE_EMAIL, $license_email);

                    delete_option(WWOF_OPTION_LICENSE_EMAIL);

                }

                if ($license_key = get_option(WWOF_OPTION_LICENSE_KEY)) {

                    update_site_option(WWOF_OPTION_LICENSE_KEY, $license_key);

                    delete_option(WWOF_OPTION_LICENSE_KEY);

                }

                if ($installed_version = get_option(WWOF_OPTION_INSTALLED_VERSION)) {

                    update_site_option(WWOF_OPTION_INSTALLED_VERSION, $installed_version);

                    delete_option(WWOF_OPTION_INSTALLED_VERSION);

                }

            }

            // Set initial settings
            global $WWOF_SETTINGS_DEFAULT_PPP, $WWOF_SETTINGS_DEFAULT_SORT_BY, $WWOF_SETTINGS_DEFAULT_SORT_ORDER;

            // General section settings
            if (get_option('wwof_general_products_per_page') === false) {
                update_option('wwof_general_products_per_page', $WWOF_SETTINGS_DEFAULT_PPP);
            }

            if (get_option('wwof_general_sort_by') === false) {
                update_option('wwof_general_sort_by', $WWOF_SETTINGS_DEFAULT_SORT_BY);
            }

            if (get_option('wwof_general_sort_order') === false) {
                update_option('wwof_general_sort_order', $WWOF_SETTINGS_DEFAULT_SORT_ORDER);
            }

            if (get_option('wwof_general_use_alternate_view_of_wholesale_page') === false) {
                update_option('wwof_general_use_alternate_view_of_wholesale_page', 'no');
            }

            if (get_option('wwof_general_disable_pagination') === false) {
                update_option('wwof_general_disable_pagination', 'no');
            }

            if (get_option('wwof_general_display_product_details_on_popup') === false) {
                update_option('wwof_general_display_product_details_on_popup', 'no');
            }

            // Getting Started Notice
            if (!get_option('wwof_admin_notice_getting_started_show', false)) {
                update_option('wwof_admin_notice_getting_started_show', 'yes');
            }

            // Create wholesale pages
            $this->_wwof_ajax->wwof_create_wholesale_page(null, false);

            // v1.17 Update DB change
            Order_Form_DB::version_1point17_update();

            // v1.21 Update DB change
            Order_Form_DB::version_1point21_update();

            flush_rewrite_rules();

            update_option(WWOF_ACTIVATION_CODE_TRIGGERED, 'yes');

            if (is_multisite()) {
                update_site_option(WWOF_OPTION_INSTALLED_VERSION, $this->_wwof_current_version);
            } else {
                update_option(WWOF_OPTION_INSTALLED_VERSION, $this->_wwof_current_version);
            }

        }

        /**
         * Plugin deactivation hook callback.
         *
         * @param bool $network_wide
         *
         * @since 1.0.0
         * @since 1.6.4 Multisite Compatibility
         * @since 1.6.6 Refactor codebase and move to its proper model
         */
        public function wwof_deactivate($network_wide)
        {

            global $wpdb;

            // check if it is a multisite network
            if (is_multisite()) {

                // check if the plugin has been deactivated on the network or on a single site
                if ($network_wide) {

                    // get ids of all sites
                    $blogIDs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

                    foreach ($blogIDs as $blogID) {

                        switch_to_blog($blogID);
                        $this->wwof_deactivate_action();

                    }

                    restore_current_blog();

                } else {

                    // deactivated on a single site, in a multi-site
                    $this->wwof_deactivate_action();

                }

            } else {

                // deactivated on a single site
                $this->wwof_deactivate_action();

            }

        }

        /**
         * Perform actions on plugin deactivation.
         *
         * @since 1.6.4
         * @since 1.6.6 Refactor codebase and move to its proper model
         */
        private function wwof_deactivate_action()
        {

            flush_rewrite_rules();

        }

        /**
         * Plugin initialization.
         *
         * @since 1.0.1
         * @since 1.6.4 Multisite Compatibility
         * @since 1.6.6 Refactor codebase and move to its proper model
         */
        public function wwof_initialize()
        {

            $activation_flag   = get_option(WWOF_ACTIVATION_CODE_TRIGGERED, false);
            $installed_version = is_multisite() ? get_site_option(WWOF_OPTION_INSTALLED_VERSION, false) : get_option(WWOF_OPTION_INSTALLED_VERSION, false);

            if (version_compare($installed_version, $this->_wwof_current_version, '!=') || $activation_flag != 'yes') {

                if (!function_exists('is_plugin_active_for_network')) {
                    require_once ABSPATH . '/wp-admin/includes/plugin.php';
                }

                $network_wide = is_plugin_active_for_network('woocommerce-wholesale-order-form/woocommerce-wholesale-order-form.bootstrap.php');

                $this->wwof_activate($network_wide);

            }

        }

        /**
         * Execute plugin initialization ( plugin activation ) on every newly created site in a multi site set up
         *
         * @since 1.6.4
         * @since 1.6.6 Refactor codebase and move to its proper model
         */
        public function wwof_multisite_init()
        {

            if (is_plugin_active_for_network('woocommerce-wholesale-order-form/woocommerce-wholesale-order-form.bootstrap.php')) {

                switch_to_blog($blogID);
                $this->wwof_activate($blogID);
                restore_current_blog();

            }

        }

        /**
         * Add plugin listing custom action link ( settings ).
         *
         * @param $links
         * @param $file
         * @return mixed
         *
         * @since 1.0.2
         * @since 1.6.6 Refactor codebase and move to its proper model
         */
        public function wwof_add_actions_links_in_plugin_listing($links, $file)
        {

            if ($file == plugin_basename(WWOF_PLUGIN_DIR . 'woocommerce-wholesale-order-form.bootstrap.php')) {

                if (!is_multisite()) {

                    $license_link = '<a href="options-general.php?page=wwc_license_settings&tab=wwof">' . __('License', 'woocommerce-wholesale-order-form') . '</a>';
                    array_unshift($links, $license_link);

                }

                $settings_link = '<a href="admin.php?page=wc-settings&tab=wwof_settings">' . __('Settings', 'woocommerce-wholesale-order-form') . '</a>';
                array_unshift($links, $settings_link);

                $getting_started          = '<a href="https://wholesalesuiteplugin.com/kb/woocommerce-wholesale-order-form-getting-started-guide/?utm_source=wwof&utm_medium=kb&utm_campaign=wwofgettingstarted" target="_blank">' . __('Getting Started', 'woocommerce-wholesale-order-form') . '</a>';
                $links['getting_started'] = $getting_started;

            }

            return $links;

        }

        /**
         * Remove WWP and WWPP Getting Started notice.
         * We will compile them into 1 in WWOF.
         *
         * @since 1.15.4
         * @access public
         */
        public function remove_getting_started_notice()
        {

            if (!function_exists('is_plugin_active')) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $wwof_notice = get_option('wwof_admin_notice_getting_started_show');
            $wwlc_active = is_plugin_active('woocommerce-wholesale-lead-capture/woocommerce-wholesale-lead-capture.bootstrap.php');

            if (!$wwlc_active && $wwof_notice !== 'no') {

                global $wc_wholesale_prices, $wc_wholesale_prices_premium;

                if ($wc_wholesale_prices) {
                    remove_action('admin_notices', array($wc_wholesale_prices->wwp_bootstrap, 'getting_started_notice'), 10);
                }

                if ($wc_wholesale_prices && $wc_wholesale_prices_premium) {
                    remove_action('admin_notices', array($wc_wholesale_prices_premium->wwpp_bootstrap, 'wwpp_getting_started_notice'), 10);
                }

            }

        }

        /**
         * Getting Started notice on plugin activation.
         *
         * @since 1.15.4
         * @access public
         */
        public function wwof_getting_started_notice()
        {

            require_once WWOF_VIEWS_ROOT_DIR . 'wwof-notice/view-wwof-notices.php';

        }

        /**
         * Hide WWOF getting started notice on close.
         *
         * @since 1.15.4
         * @access public
         */
        public function wwof_getting_started_notice_hide()
        {

            if (!function_exists('is_plugin_active')) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $wwp_active  = is_plugin_active('woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php');
            $wwpp_active = is_plugin_active('woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php');

            if ($wwp_active) {
                update_option('wwp_admin_notice_getting_started_show', 'no');
            }

            if ($wwpp_active) {
                update_option('wwpp_admin_notice_getting_started_show', 'no');
            }

            update_option('wwof_admin_notice_getting_started_show', 'no');

            wp_send_json(array('status' => 'success'));

        }

        /**
         * Execute model.
         *
         * @since 1.6.6
         * @access public
         */
        public function run()
        {

            // Load Plugin Text Domain
            // Change hook from plugins_loaded to init
            // The hook plugins_loaded will not work coz of the update we did in the bootstrap file which is the after_setup_theme hook
            add_action('init', array($this, 'wwof_load_plugin_text_domain'));

            // Register Activation Hook
            register_activation_hook(WWOF_PLUGIN_DIR . 'woocommerce-wholesale-order-form.bootstrap.php', array($this, 'wwof_activate'));

            // Register Deactivation Hook
            register_deactivation_hook(WWOF_PLUGIN_DIR . 'woocommerce-wholesale-order-form.bootstrap.php', array($this, 'wwof_deactivate'));

            // Initialize Plugin
            add_action('init', array($this, 'wwof_initialize'));

            // Execute plugin initialization ( plugin activation ) on every newly created site in a multi site set up
            add_action('wpmu_new_blog', array($this, 'wwof_multisite_init'), 10, 6);

            // Plugin action links
            add_filter('plugin_action_links', array($this, 'wwof_add_actions_links_in_plugin_listing'), 10, 2);

            // Getting Started notice
            add_action('init', array($this, 'remove_getting_started_notice'));
            add_action('admin_notices', array($this, 'wwof_getting_started_notice'), 10);
            add_action('wp_ajax_wwof_getting_started_notice_hide', array($this, 'wwof_getting_started_notice_hide'));

        }
    }
}
