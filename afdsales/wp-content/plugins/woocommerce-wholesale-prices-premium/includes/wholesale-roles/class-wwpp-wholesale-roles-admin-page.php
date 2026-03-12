<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWPP_Wholesale_Roles_Admin_Page')) {

    /**
     * Model that houses the logic of wholesale roles admin page.
     *
     * @since 1.14.0
     */
    class WWPP_Wholesale_Roles_Admin_Page {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * Property that holds the single main instance of WWPP_Wholesale_Roles_Admin_Page.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Wholesale_Roles_Admin_Page
         */
        private static $_instance;

        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Wholesale_Roles
         */
        private $_wwpp_wholesale_roles;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        /**
         * WWPP_Wholesale_Roles_Admin_Page constructor.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Roles_Admin_Page model.
         */
        public function __construct($dependencies) {

            $this->_wwpp_wholesale_roles = $dependencies['WWPP_Wholesale_Roles'];

        }

        /**
         * Ensure that only one instance of WWPP_Wholesale_Roles_Admin_Page is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Roles_Admin_Page model.
         * @return WWPP_Wholesale_Roles_Admin_Page
         */
        public static function instance($dependencies) {

            if (!self::$_instance instanceof self) {
                self::$_instance = new self($dependencies);
            }

            return self::$_instance;

        }

        /**
         * Register wholesale roles admin page menu.
         *
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         */
        public function register_wholesale_roles_admin_page_menu() {

            // Register wholesale roles admin page menu (Append to woocommerce admin area)
            add_submenu_page(
                'woocommerce',
                __('WooCommerce Wholesale Prices | Wholesale Roles', 'woocommerce-wholesale-prices-premium'),
                __('Wholesale Roles', 'woocommerce-wholesale-prices-premium'),
                apply_filters('wwpp_can_access_admin_menu_cap', 'manage_options'),
                'wwpp-wholesale-roles-page',
                array($this, "view_wholesale_roles_admin_page")
            );

        }

        /**
         * View for wholesale roles page.
         *
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         */
        public function view_wholesale_roles_admin_page() {

            $all_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

            if (count($all_registered_wholesale_roles) <= 1) {
                $wholesale_roles_total_text = sprintf(__('<span class="wholesale-roles-count">%1$s</span> item', 'woocommerce-wholesale-prices-premium'), count($all_registered_wholesale_roles));
            } else {
                $wholesale_roles_total_text = sprintf(__('<span class="wholesale-roles-count">%1$s</span> items', 'woocommerce-wholesale-prices-premium'), count($all_registered_wholesale_roles));
            }

            // Move the main wholesale role always on top of the array
            foreach ($all_registered_wholesale_roles as $key => $arr) {

                if (array_key_exists('main', $arr) && $arr['main']) {

                    $main_wholesale_role = $all_registered_wholesale_roles[$key];
                    unset($all_registered_wholesale_roles[$key]);
                    $all_registered_wholesale_roles = array($key => $main_wholesale_role) + $all_registered_wholesale_roles;
                    break;

                }

            }

            require_once WWPP_VIEWS_PATH . 'wholesale-roles/view-wwpp-wholesale-roles-admin-page.php';

        }

        /**
         * Add new wholesale role.
         *
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @since 1.23.2 Don't allow inputting wholesale role key with numeric only value since wp dont allow numeric roles.
         *               Existing wholesale role with just numeric will be manually updated by the customer.
         * @since 1.23.9 Removed shippingClassName and shippingClassTermId when creating a role.
         * @access public
         *
         * @param null|array $new_role New wholesale role data.
         * @return array Operation status.
         */
        public function add_new_wholesale_role($new_role = null) {

            if (defined('DOING_AJAX') && DOING_AJAX) {
                $new_role = $_POST['newRole'];
            }

            $response = array();

            global $wp_roles;

            if (!isset($wp_roles)) {
                $wp_roles = new WP_Roles();
            }

            $allUserRoles = $wp_roles->get_names();
            $allRoles = array();

            if ($allUserRoles) {
                foreach ($allUserRoles as $role_key => $role_name) {
                    $allRoles[strtolower($role_key)] = $role_name;
                }
            }

            if (preg_match("/^\d+$/", $new_role['roleKey'])) {

                $response['status'] = 'error';
                $response['error_message'] = sprintf(__('You can’t use only numbers in a wholesale role key.', 'woocommerce-wholesale-prices-premium'), $new_role['roleKey']);

            } else if (!array_key_exists(strtolower($new_role['roleKey']), $allRoles)) {

                // Add plugin custom roles and capabilities
                $this->_wwpp_wholesale_roles->addCustomRole($new_role['roleKey'], $new_role['roleName']);
                $this->_wwpp_wholesale_roles->registerCustomRole(
                    $new_role['roleKey'],
                    $new_role['roleName'],
                    array(
                        'desc' => $new_role['roleDesc'],
                        'onlyAllowWholesalePurchases' => $new_role['onlyAllowWholesalePurchases'],
                    ));
                $this->_wwpp_wholesale_roles->addCustomCapability($new_role['roleKey'], 'have_wholesale_price');

                $response['status'] = 'success';

            } else {

                $response['status'] = 'error';
                $response['error_message'] = sprintf(__('Wholesale Role (%1$s) already exist, make sure role key and preferably role name are unique', 'woocommerce-wholesale-prices-premium'), $new_role['roleKey']);

            }

            if (defined('DOING_AJAX') && DOING_AJAX) {

                @header('Content-Type: application/json; charset=' . get_option('blog_charset'));
                echo wp_json_encode($response);
                wp_die();

            } else {
                return $response;
            }

        }

        /**
         * Edit wholesale role.
         *
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @since 1.23.9 Removed shippingClassName and shippingClassTermId when updating a role.
         * @access public
         *
         * @param null|array $role Role data.
         * @return array Operation status.
         */
        public function edit_wholesale_role($role = null) {

            if (defined('DOING_AJAX') && DOING_AJAX) {
                $role = $_POST['role'];
            }

            global $wpdb;

            $wp_roles = get_option($wpdb->prefix . 'user_roles');

            if (!is_array($wp_roles)) {

                global $wp_roles;
                if (!isset($wp_roles)) {
                    $wp_roles = new WP_Roles();
                }

                $wp_roles = $wp_roles->roles;

            }

            if (array_key_exists($role['roleKey'], $wp_roles)) {

                // Update role in WordPress record
                $wp_roles[$role['roleKey']]['name'] = $role['roleName'];
                update_option($wpdb->prefix . 'user_roles', $wp_roles);

                // Update role in registered wholesale roles record
                $registered_wholesale_roles = unserialize(get_option(WWP_OPTIONS_REGISTERED_CUSTOM_ROLES));

                $registered_wholesale_roles[$role['roleKey']]['roleName'] = $role['roleName'];
                $registered_wholesale_roles[$role['roleKey']]['desc'] = $role['roleDesc'];
                $registered_wholesale_roles[$role['roleKey']]['onlyAllowWholesalePurchases'] = $role['onlyAllowWholesalePurchases'];

                update_option(WWP_OPTIONS_REGISTERED_CUSTOM_ROLES, serialize($registered_wholesale_roles));

                $response = array('status' => 'success');

            } else {

                // Specified role to edit doesn't exist
                $response = array(
                    'status' => 'error',
                    'error_message' => sprintf(__('Specified Wholesale Role (%1$s) Does not Exist', 'woocommerce-wholesale-prices-premium'), $role['roleKey']),
                );

            }

            if (defined('DOING_AJAX') && DOING_AJAX) {

                @header('Content-Type: application/json; charset=' . get_option('blog_charset'));
                echo wp_json_encode($response);
                wp_die();

            } else {
                return array($response);
            }

        }

        /**
         * Delete wholesale role.
         *
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param null|string $role_key Wholesale role key.
         * @return array Operation status.
         */
        public function delete_wholesale_role($role_key = null) {

            if (defined('DOING_AJAX') && DOING_AJAX) {
                $role_key = $_POST['roleKey'];
            }

            $err_msg = $this->check_settings_for_used_wholesale_role($role_key);

            if ($err_msg) {
                $message = '<ol>';
                foreach ($err_msg as $msg) {
                    $message .= '<li>' . $msg . '</li>';
                }
                $message .= '</ol>';
                $response = array(
                    'status' => 'error',
                    'error_title' => __('Please remove the role first in the following settings:', 'woocommerce-wholesale-prices-premium'),
                    'error_message' => $message,
                );

            } else {

                $users = new WP_User_Query(array(
                    'role' => $role_key,
                    'fields' => 'ID',
                ));

                if ($users->get_total() > 0) {

                    $response = array(
                        'status' => 'error',
                        'error_title' => __('Unable to delete, role is being used.', 'woocommerce-wholesale-prices-premium'),
                        'error_message' => sprintf(__('There are %1$s user(s) who are using "%2$s" role. Please update the role manually to a different one first before you can delete.<br/><br/><a href="%3$s" target="_blank">Check Users</a>', 'woocommerce-wholesale-prices-premium'), $users->get_total(), $role_key, get_site_url() . '/wp-admin/users.php?role=' . $role_key),
                    );

                } else {

                    // Remove plugin custom roles and capabilities
                    $this->_wwpp_wholesale_roles->removeCustomCapability($role_key, 'have_wholesale_price');
                    $this->_wwpp_wholesale_roles->removeCustomRole($role_key);
                    $this->_wwpp_wholesale_roles->unregisterCustomRole($role_key);

                    $response = array('status' => 'success');

                }

            }

            if (defined('DOING_AJAX') && DOING_AJAX) {

                @header('Content-Type: application/json; charset=' . get_option('blog_charset'));
                echo wp_json_encode($response);
                wp_die();

            } else {
                return $response;
            }

        }

        /**
         * Check areas where the wholesale role key is used in the Wholesale Prices settings.
         *
         * @since 1.21
         * @access public
         *
         * @param string $role_key Wholesale role key.
         * @return array
         */
        public function check_settings_for_used_wholesale_role($role_key) {

            $settings = array();

            // Minimum Order Requirements / Override per wholesale role
            if (get_option('wwpp_settings_override_order_requirement_per_role') == 'yes') {
                $min_order_requirements = get_option(WWPP_OPTION_WHOLESALE_ROLE_ORDER_REQUIREMENT_MAPPING, array());
                if (array_key_exists($role_key, $min_order_requirements)) {
                    $settings[] = __('Minimum Order Requirements / Override per wholesale role', 'woocommerce-wholesale-prices-premium');
                }

            }

            // Wholesale Role / Tax Exemption Mapping
            $tax_exemption_mapping = get_option(WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING, array());
            if (array_key_exists($role_key, $tax_exemption_mapping)) {
                $settings[] = __('Tax Exemption Mapping', 'woocommerce-wholesale-prices-premium');
            }

            // Wholesale Role / Tax Class Mapping
            $tax_class_mapping = get_option(WWPP_OPTION_WHOLESALE_ROLE_TAX_CLASS_OPTIONS_MAPPING, array());
            if (array_key_exists($role_key, $tax_class_mapping)) {
                $settings[] = __('Tax Class Mapping', 'woocommerce-wholesale-prices-premium');
            }

            // Wholesale Role/Shipping Method Mapping
            $shipping_method_mapping = get_option(WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_ZONE_METHOD_MAPPING, array());
            if ($shipping_method_mapping) {
                foreach ($shipping_method_mapping as $index => $shipping_method) {
                    if ($shipping_method['wholesale_role'] === $role_key) {
                        $settings[] = __('Shipping Method Mapping', 'woocommerce-wholesale-prices-premium');
                        break;
                    }
                }
            }

            // General Discount Options
            $general_discount = get_option(WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING, array());
            if (array_key_exists($role_key, $general_discount)) {
                $settings[] = __('General Discount Options', 'woocommerce-wholesale-prices-premium');
            }

            // General Quantity Based Discounts
            $general_qty_based_discounts = get_option(WWPP_OPTION_WHOLESALE_ROLE_CART_QTY_BASED_DISCOUNT_MAPPING, array());
            if ($general_qty_based_discounts) {
                foreach ($general_qty_based_discounts as $index => $qty_discount) {
                    if ($qty_discount['wholesale_role'] === $role_key) {
                        $settings[] = __('General Quantity Based Discounts', 'woocommerce-wholesale-prices-premium');
                        break;
                    }
                }
            }

            // Wholesale Role / Payment Gateway
            $payment_gateway = get_option(WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING, array());
            if (array_key_exists($role_key, $payment_gateway)) {
                $settings[] = __('Payment Gateway', 'woocommerce-wholesale-prices-premium');
            }

            // Wholesale Role / Payment Gateway Surcharge
            $payment_gateway_surcharge = get_option(WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING, array());
            if ($payment_gateway_surcharge) {
                foreach ($payment_gateway_surcharge as $index => $gateway_surcharge) {
                    if ($gateway_surcharge['wholesale_role'] === $role_key) {
                        $settings[] = __('Payment Gateway Surcharge', 'woocommerce-wholesale-prices-premium');
                        break;
                    }
                }
            }

            return $settings;

        }

        /*
        |---------------------------------------------------------------------------------------------------------------
        | Execute model
        |---------------------------------------------------------------------------------------------------------------
         */

        /**
         * Register model ajax handlers.
         *
         * @since 1.14.0
         * @access public
         */
        public function register_ajax_handler() {

            add_action("wp_ajax_wwppAddNewWholesaleRole", array($this, 'add_new_wholesale_role'));
            add_action("wp_ajax_wwppEditWholesaleRole", array($this, 'edit_wholesale_role'));
            add_action("wp_ajax_wwpDeleteWholesaleRole", array($this, 'delete_wholesale_role'));

        }

        /**
         * Execute model.
         *
         * @since 1.14.0
         * @access public
         */
        public function run() {

            add_action('admin_menu', array($this, 'register_wholesale_roles_admin_page_menu'));

            add_action('init', array($this, 'register_ajax_handler'));

        }

    }

}