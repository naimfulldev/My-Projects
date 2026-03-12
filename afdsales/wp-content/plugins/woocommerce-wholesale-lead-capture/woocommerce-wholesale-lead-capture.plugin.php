<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WooCommerce_Wholesale_Lead_Capture')) {

    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-wws-license-manager.php';
    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-wws-update-manager.php';
    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-bootstrap.php';
    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-scripts.php';
    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-ajax.php';
    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-forms.php';
    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-user-account.php';
    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-user-custom-fields.php';
    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-emails.php';
    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-registration-form-custom-fields.php';
    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-shortcode.php';
    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-cron.php';
    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-dashboard-widget.php';
    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-upgrade-account.php';
    require_once WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-invisible-recaptcha.php';

    class WooCommerce_Wholesale_Lead_Capture
    {

        /*
        |--------------------------------------------------------------------------------------------------------------
        | Class Members
        |--------------------------------------------------------------------------------------------------------------
         */

        private static $_instance;

        public $_wwlc_license_manager;
        public $_wwlc_update_manager;
        public $_wwlc_bootstrap;
        public $_wwlc_scripts;
        public $_wwlc_forms;
        public $_wwlc_user_account;
        public $_wwlc_user_custom_fields;
        public $_wwlc_emails;
        public $_wwlc_wws_license_setting;
        public $_wwlc_registration_form_custom_fields;
        public $_wwlc_shortcode;
        public $_wwlc_ajax;
        public $_wwlc_cron;
        public $_wwlc_dashboard_widget;
        public $_wwlc_upgrade_account;

        // Plugin Integrations
        public $_wwlc_invisible_recaptcha;

        const VERSION = '1.17';

        /*
        |--------------------------------------------------------------------------------------------------------------
        | Mesc Functions
        |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Class constructor.
         *
         * @since 1.0.0
         */
        public function __construct()
        {

            $this->_wwlc_license_manager = WWLC_WWS_License_Manager::instance();
            $this->_wwlc_update_manager  = WWLC_WWS_Update_Manager::instance();

            $this->_wwlc_forms = WWLC_Forms::instance();

            $this->_wwlc_scripts = WWLC_Scripts::instance(array(
                'WWLC_Forms'   => $this->_wwlc_forms,
                'WWLC_Version' => self::VERSION,
            ));

            $this->_wwlc_user_account = WWLC_User_Account::instance();

            $this->_wwlc_emails = WWLC_Emails::instance(array(
                'WWLC_User_Account' => $this->_wwlc_user_account,
            ));

            $this->_wwlc_registration_form_custom_fields = WWLC_Registration_Form_Custom_Fields::instance();

            $this->_wwlc_bootstrap = WWLC_Bootstrap::instance(array(
                'WWLC_Forms'           => $this->_wwlc_forms,
                'WWLC_CURRENT_VERSION' => self::VERSION,
            ));

            $this->_wwlc_user_custom_fields = WWLC_User_Custom_Fields::instance(array(
                'WWLC_User_Account' => $this->_wwlc_user_account,
                'WWLC_Emails'       => $this->_wwlc_emails,
            ));

            $this->_wwlc_shortcode = WWLC_Shortcode::instance(array(
                'WWLC_Forms' => $this->_wwlc_forms,
            ));

            $this->_wwlc_ajax = WWLC_AJAX::instance(array(
                'WWLC_Bootstrap'                       => $this->_wwlc_bootstrap,
                'WWLC_User_Account'                    => $this->_wwlc_user_account,
                'WWLC_Emails'                          => $this->_wwlc_emails,
                'WWLC_Forms'                           => $this->_wwlc_forms,
                'WWLC_Registration_Form_Custom_Fields' => $this->_wwlc_registration_form_custom_fields,
            ));

            $this->_wwlc_cron             = WWLC_Cron::instance();
            $this->_wwlc_dashboard_widget = WWLC_Dashboard_Widget::instance();

            $this->_wwlc_upgrade_account = WWLC_Upgrade_Account::instance(array(
                'WWLC_User_Account' => $this->_wwlc_user_account,
            ));

            $this->_wwlc_invisible_recaptcha = WWLC_Invisible_Recaptcha::instance();
        }

        /**
         * Singleton Pattern.
         *
         * @return WooCommerce_Wholesale_Lead_Capture
         * @since 1.0.0
         */
        public static function instance()
        {

            if (!self::$_instance instanceof self) {
                self::$_instance = new self;
            }

            return self::$_instance;
        }

        /*
        |---------------------------------------------------------------------------------------------------------------
        | Settings
        |---------------------------------------------------------------------------------------------------------------
         */

        /**
         * Initialize plugin settings.
         *
         * @since 1.0.0
         */
        public function initializePluginSettings($settings)
        {

            $settings[] = include WWLC_INCLUDES_ROOT_DIR . "class-wwlc-settings.php";

            return $settings;
        }

        /**
         * Check if in wwlc license settings page.
         *
         * @return bool
         *
         * @since 1.1.1
         */
        public function checkIfInWWLCSettingsPage()
        {

            if (isset($_GET['page']) && $_GET['page'] == 'wwc_license_settings' && isset($_GET['tab']) && $_GET['tab'] == 'wwlc') {
                return true;
            } else {
                return false;
            }

        }

        /*
        |-------------------------------------------------------------------------------------------------------------------
        | Execution WWLC
        |
        | This will be the new way of executing the plugin.
        |-------------------------------------------------------------------------------------------------------------------
         */

        /**
         * Execute WWLC. Triggers the execution codes of the plugin models.
         *
         * @since 1.6.3
         * @access public
         */
        public function run()
        {

            // Register Settings Page
            add_filter("woocommerce_get_settings_pages", array($this, 'initializePluginSettings'));

            $this->_wwlc_license_manager->run();
            $this->_wwlc_update_manager->run();
            $this->_wwlc_bootstrap->run();
            $this->_wwlc_scripts->run();
            $this->_wwlc_user_account->run();
            $this->_wwlc_ajax->run();
            $this->_wwlc_shortcode->run();
            $this->_wwlc_user_custom_fields->run();
            $this->_wwlc_emails->run();
            $this->_wwlc_cron->run();
            $this->_wwlc_forms->run();
            $this->_wwlc_dashboard_widget->run();
            $this->_wwlc_upgrade_account->run();

            // Plugin Integrations
            $this->_wwlc_invisible_recaptcha->run();

        }
    }
}
