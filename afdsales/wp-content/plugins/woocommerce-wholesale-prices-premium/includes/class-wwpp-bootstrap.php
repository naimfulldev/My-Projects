<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWPP_Bootstrap')) {

    /**
     * Model that houses the logic of bootstrapping the plugin.
     *
     * @since 1.13.0
     */
    class WWPP_Bootstrap
    {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * Property that holds the single main instance of WWPP_Bootstrap.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Bootstrap
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

        /**
         * Model that houses the logic relating to payment gateways.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Wholesale_Role_Payment_Gateway
         */
        private $_wwpp_wholesale_role_payment_gateway;

        /**
         * Array of registered wholesale roles.
         *
         * @since 1.13.0
         * @access private
         * @var array
         */
        private $_registered_wholesale_roles;

        /**
         * Current WWP version.
         *
         * @since 1.13.3
         * @access private
         * @var int
         */
        private $_wwpp_current_version;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        /**
         * WWPP_Bootstrap constructor.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Bootstrap model.
         */
        public function __construct($dependencies)
        {

            $this->_wwpp_wholesale_roles                = $dependencies['WWPP_Wholesale_Roles'];
            $this->_wwpp_wholesale_role_payment_gateway = $dependencies['WWPP_Wholesale_Role_Payment_Gateway'];
            $this->_wwpp_current_version                = $dependencies['WWPP_CURRENT_VERSION'];

            $this->_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

        }

        /**
         * Ensure that only one instance of WWPP_Bootstrap is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Bootstrap model.
         * @return WWPP_Bootstrap
         */
        public static function instance($dependencies)
        {

            if (!self::$_instance instanceof self) {
                self::$_instance = new self($dependencies);
            }

            return self::$_instance;

        }

        /*
        |------------------------------------------------------------------------------------------------------------------
        | Internationalization and Localization
        |------------------------------------------------------------------------------------------------------------------
         */

        /**
         * Load plugin text domain.
         *
         * @since 1.2.0
         * @since 1.13.0 Refactor codebase and move to its dedicated model.
         * @access public
         */
        public function load_plugin_text_domain()
        {

            load_plugin_textdomain('woocommerce-wholesale-prices-premium', false, WWPP_PLUGIN_BASE_PATH . 'languages/');

        }

        /*
        |------------------------------------------------------------------------------------------------------------------
        | Bootstrap/Shutdown Functions
        |------------------------------------------------------------------------------------------------------------------
         */

        /**
         * Plugin activation hook callback.
         *
         * @since 1.0.0
         * @since 1.12.5 Add flush rewrite rules
         * @since 1.13.0 Add multisite support
         * @access public
         */
        public function activate($network_wide)
        {

            global $wpdb;

            if (is_multisite()) {

                if ($network_wide) {

                    // get ids of all sites
                    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

                    foreach ($blog_ids as $blog_id) {

                        switch_to_blog($blog_id);
                        $this->_activate($blog_id);

                    }

                    restore_current_blog();

                } else {
                    $this->_activate($wpdb->blogid);
                }
                // activated on a single site, in a multi-site

            } else {
                $this->_activate($wpdb->blogid);
            }
            // activated on a single site

        }

        /**
         * Plugin activation codebase.
         *
         * @since 1.13.0
         * @since 1.17 Refactor support for multisite setup.
         * @access private
         *
         * @param int $blog_id Site id.
         */
        private function _activate($blog_id)
        {

            /**
             * Previously multisite installs site store license options using normal get/add/update_option functions.
             * These stores the option on a per sub-site basis. We need move these options network wide in multisite setup
             * via get/add/update_site_option functions.
             */
            if (is_multisite()) {

                if ($license_email = get_option(WWPP_OPTION_LICENSE_EMAIL)) {

                    update_site_option(WWPP_OPTION_LICENSE_EMAIL, $license_email);

                    delete_option(WWPP_OPTION_LICENSE_EMAIL);

                }

                if ($license_key = get_option(WWPP_OPTION_LICENSE_KEY)) {

                    update_site_option(WWPP_OPTION_LICENSE_KEY, $license_key);

                    delete_option(WWPP_OPTION_LICENSE_KEY);

                }

                if ($installed_version = get_option(WWPP_OPTION_INSTALLED_VERSION)) {

                    update_site_option(WWPP_OPTION_INSTALLED_VERSION, $installed_version);

                    delete_option(WWPP_OPTION_INSTALLED_VERSION);

                }

            }

            // Getting Started Notice
            if (!get_option('wwpp_admin_notice_getting_started_show', false)) {
                update_option('wwpp_admin_notice_getting_started_show', 'yes');
            }

            if (!get_option('wwpp_settings_wholesale_price_title_text', false)) {
                update_option('wwpp_settings_wholesale_price_title_text', 'Wholesale Price:');
            }

            if (!get_option('wwpp_settings_variable_product_price_display', false)) {
                update_option('wwpp_settings_variable_product_price_display', 'price-range');
            }

            // Initialize product visibility related meta
            wp_schedule_single_event(time(), WWPP_CRON_INITIALIZE_PRODUCT_WHOLESALE_VISIBILITY_FILTER);

            // Set all existing payment tokens as not default
            $this->_wwpp_wholesale_role_payment_gateway->undefault_existing_payment_tokens();

            flush_rewrite_rules();

            update_option('wwpp_option_activation_code_triggered', 'yes');

            if (is_multisite()) {
                update_site_option('wwpp_option_installed_version', $this->_wwpp_current_version);
            } else {
                update_option('wwpp_option_installed_version', $this->_wwpp_current_version);
            }

            // Clear WC Transients on activation
            // This is very important, we need to do this specially with the advent of version 1.15.0
            // Required by these functions ( filter_available_variable_product_variations )
            // If we don't clear the product transients, 'woocommerce_get_children' won't be triggered therefore 'filter_available_variable_product_variations' function will not be executed.
            // The reason being is WC will just use the transient data.
            // We only need to do this on plugin activation tho, as every subsequent product update, it will clear the transient for that specific product.
            // Only clear the product transient
            if (function_exists('wc_delete_product_transients')) {
                wc_delete_product_transients();
            }

        }

        /**
         * The main purpose for this function as follows.
         * Get all products
         * Check if product has no 'wwpp_product_wholesale_visibility_filter' meta key yet
         * If above is true, then set a meta for the current product with a key of 'wwpp_product_wholesale_visibility_filter' and value of 'all'
         *
         * This in turn specify that this product is available for viewing for all users of the site.
         * and yup, the sql statement below does all that.
         *
         * @since 1.4.2
         * @since 1.13.0 Refactor codebase and move to its own model.
         * @since 1.14.0 Make it handle ajax callback 'wp_ajax_wwpp_initialize_product_visibility_meta'.
         * @since 1.23.9 Set <wholesale_role>_have_wholesale_price meta into the parent group product.
         * @access public
         *
         * @return bool Operation status.
         */
        public function initialize_product_visibility_filter_meta()
        {

            global $wpdb, $wc_wholesale_prices_premium;

            /*
             * In version 1.13.0 we refactored the Wholesale Exclusive Variation feature.
             * Now it is an enhanced select box instead of the old check box.
             * This gives us more flexibility including the 'all' value if no wholesale role is selected.
             * In light to this, we must migrate the old <wholesale_role>_exclusive_variation data to the new 'wwpp_product_visibility_filter'.
             */
            foreach ($this->_registered_wholesale_roles as $role_key => $role) {

                $wpdb->query("
                    INSERT INTO $wpdb->postmeta ( post_id , meta_key , meta_value )
                    SELECT $wpdb->posts.ID , 'wwpp_product_wholesale_visibility_filter' , '" . $role_key . "'
                    FROM $wpdb->posts
                    WHERE $wpdb->posts.post_type IN ( 'product_variation' )
                    AND $wpdb->posts.ID IN (
                        SELECT $wpdb->posts.ID
                        FROM $wpdb->posts
                        INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
                        WHERE meta_key = '" . $role_key . "_exclusive_variation'
                        AND meta_value = 'yes'
                    )
                ");

            }

            /*
             * Initialize wwpp_product_wholesale_visibility_filter meta
             * This meta is in charge of product visibility. We need to set this to 'all' as mostly
             * all imported products will not have this meta. Meaning, all imported products
             * with no 'wwpp_product_wholesale_visibility_filter' meta set is visible to all users by default.
             */
            $wpdb->query("
                INSERT INTO $wpdb->postmeta ( post_id , meta_key , meta_value )
                SELECT $wpdb->posts.ID , 'wwpp_product_wholesale_visibility_filter' , 'all'
                FROM $wpdb->posts
                WHERE $wpdb->posts.post_type IN ( 'product' , 'product_variation' )
                AND $wpdb->posts.ID NOT IN (
                    SELECT $wpdb->posts.ID
                    FROM $wpdb->posts
                    INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
                    WHERE meta_key = 'wwpp_product_wholesale_visibility_filter' )
            ");

            /*
             * Address instances where the wwpp_product_wholesale_visibility_filter meta is present but have empty value.
             * This can possibly occur when importing products using external tool that tries to import meta data but fails to properly save the data.
             * Ticket : WWPP-434
             */
            $wpdb->query("
                UPDATE $wpdb->postmeta
                SET meta_value = 'all'
                WHERE meta_key = 'wwpp_product_wholesale_visibility_filter'
                AND meta_value = ''
            ");

            /*
             * Properly set {wholesale_role}_have_wholesale_price meta
             * There will be cases where users import products from external sources and they
             * "set up" wholesale prices via external tools prior to importing
             * We need to handle those cases.
             */
            foreach ($this->_registered_wholesale_roles as $role_key => $role) {

                // We need to delete prior to inserting, else we will have a stacked meta, same multiple meta for a single post
                $wpdb->query("
                    DELETE FROM $wpdb->postmeta
                    WHERE meta_key = '{$role_key}_have_wholesale_price'
                ");

                // Delete Variations with wholesale price meta. To avoid duplicates or non-existing variation id post.
                $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '{$role_key}_variations_with_wholesale_price'");

                $wpdb->query("
                    INSERT INTO $wpdb->postmeta ( post_id , meta_key , meta_value )
                    SELECT $wpdb->posts.ID , '{$role_key}_have_wholesale_price' , 'yes'
                    FROM $wpdb->posts
                    WHERE $wpdb->posts.post_type = 'product'
                    AND $wpdb->posts.ID IN (

                            SELECT DISTINCT $wpdb->postmeta.post_id
                            FROM $wpdb->postmeta
                            WHERE (
                                    ( meta_key = '{$role_key}_wholesale_price' AND meta_value > 0  )
                                    OR
                                    ( meta_key = '{$role_key}_variations_with_wholesale_price' AND meta_value != '' )
                                    or
                                    ( meta_key = '{$role_key}_have_wholesale_price_set_by_product_cat' AND meta_value = 'yes' )
                                )

                        )
                ");

            }

            // EXTRAS VISIBILITY FOR OTHER PRODUCT TYPES THAT WAS NOT COVERED ABOVE (GROUP, BUNDLE)

            // Get grouped products.
            $args = array(
                'type'   => 'grouped',
                'return' => 'ids',
                'limit'  => -1,
            );

            $grouped_products = wc_get_products($args);

            if (!empty($grouped_products)) {

                // Set parent group product <wholesale_role>_have_wholesale_price so that it will be visible when "Only Show Wholesale Products To Wholesale Users" is enabled
                foreach ($grouped_products as $product_id) {
                    $wc_wholesale_prices_premium->wwpp_grouped_product->insert_have_wholesale_price_meta($product_id);
                }

            }

            // Get bundled products.
            $bundle_args = array(
                'type'   => 'bundle',
                'return' => 'ids',
                'limit'  => -1,
            );

            $bundled_products = wc_get_products($bundle_args);

            if (!empty($bundled_products)) {

                // Set parent group product <wholesale_role>_have_wholesale_price so that it will be visible when "Only Show Wholesale Products To Wholesale Users" is enabled
                foreach ($bundled_products as $bundle_product_id) {
                    $wc_wholesale_prices_premium->wwpp_wc_bundle_product->set_bundle_product_visibility_meta($bundle_product_id);
                }

            }

            // Get all variations then check if it has Wholesale Price set in Product Level then insert <wholesale_role>_variations_with_wholesale_price and variation as value to the parent variable meta.
            $variations = $wpdb->get_results("SELECT ID, post_parent FROM $wpdb->posts
											WHERE post_status = 'publish'
													AND post_type = 'product_variation'
                                            ", ARRAY_A);

            if (!empty($variations)) {

                foreach ($variations as $variation) {
                    $variation_id = $variation['ID'];
                    $parent_id    = $variation['post_parent'];

                    foreach ($this->_registered_wholesale_roles as $wholesale_role => $role) {
                        // Re-insert meta if needed
                        $wc_wholesale_prices_premium->wwpp_product_visibility->set_variations_with_wholesale_price_meta($variation_id, $parent_id, $wholesale_role);
                    }

                }

            }

            // Get all terms
            // Set <wholesale_role>_have_wholesale_price and <wholesale_role>_have_wholesale_price_set_by_product_cat on category level discounts
            $product_terms = get_terms(
                array(
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => false,
                )
            );

            foreach ($product_terms as $term) {

                $category_discount             = get_option('taxonomy_' . $term->term_id);
                $wholesale_role_with_discounts = array();

                foreach ($this->_registered_wholesale_roles as $role_key => $role) {
                    if (
                        isset($category_discount[$role_key . '_wholesale_discount']) &&
                        !empty($category_discount[$role_key . '_wholesale_discount'])
                    ) {
                        $wholesale_role_with_discounts[] = $role_key;
                    }
                }

                if (!empty($category_discount)) {

                    $category_discount_products = wc_get_products(array(
                        'category' => array($term->slug),
                    ));

                    if (!empty($category_discount_products)) {

                        foreach ($category_discount_products as $product) {

                            $product_id = $product->get_id();

                            foreach ($wholesale_role_with_discounts as $role_key) {

                                update_post_meta($product_id, $role_key . '_have_wholesale_price', 'yes');
                                update_post_meta($product_id, $role_key . '_have_wholesale_price_set_by_product_cat', 'yes');

                            }

                        }

                    }

                }

            }

            // Clear product id cache
            $wc_wholesale_prices_premium->wwpp_cache->clear_product_transients_cache();

            // Clear WC Product Transients Cache
            // Added in WWPP 1.26.1
            if (function_exists('wc_delete_product_transients')) {
                wc_delete_product_transients();
            }

            if (defined('DOING_AJAX') && DOING_AJAX) {

                @header('Content-Type: application/json; charset=' . get_option('blog_charset'));
                echo wp_json_encode(array('status' => 'success'));
                wp_die();

            } else {
                return true;
            }

        }

        /**
         * New option to remove all unused product meta data when a role is removed.
         *
         * @since 1.23.9
         * @access public
         */
        public function wwpp_clear_unused_product_meta()
        {

            global $wpdb;

            $existing_roles          = array();
            $wwpp_existing_meta_keys = array();

            foreach ($this->_registered_wholesale_roles as $role_key => $role) {
                $existing_roles[]          = $role_key;
                $wwpp_existing_meta_keys[] = $role_key . '_wholesale_price';
                $wwpp_existing_meta_keys[] = $role_key . '_have_wholesale_price';
                $wwpp_existing_meta_keys[] = $role_key . '_wholesale_minimum_order_quantity';
                $wwpp_existing_meta_keys[] = $role_key . '_wholesale_order_quantity_step';
            }

            $wwwpp_fields = $wpdb->get_results("
                SELECT $wpdb->postmeta.*
                FROM $wpdb->postmeta
                WHERE $wpdb->postmeta.meta_key LIKE '%_wholesale_price'
                    OR $wpdb->postmeta.meta_key LIKE '%_have_wholesale_price'
                    OR $wpdb->postmeta.meta_key LIKE '%_wholesale_minimum_order_quantity'
                    OR $wpdb->postmeta.meta_key LIKE '%_wholesale_order_quantity_step'
                    OR $wpdb->postmeta.meta_key = 'wwpp_product_wholesale_visibility_filter'
                    OR $wpdb->postmeta.meta_key = 'wwpp_post_meta_quantity_discount_rule_mapping'
            ");

            if (!empty($wwwpp_fields)) {

                foreach ($wwwpp_fields as $index => $obj) {

                    // Delete unused meta keys
                    switch ($obj->meta_key) {

                        case 'wwpp_product_wholesale_visibility_filter':
                            if ($obj->meta_value != 'all' && !in_array($obj->meta_value, $existing_roles)) {
                                delete_post_meta($obj->post_id, $obj->meta_key, $obj->meta_value);
                            }

                            break;

                        case 'wwpp_post_meta_quantity_discount_rule_mapping':
                            $mapping = maybe_unserialize($obj->meta_value);
                            if ($mapping) {
                                foreach ($mapping as $key => $map) {
                                    if (!in_array($map['wholesale_role'], $existing_roles)) {
                                        unset($mapping[$key]);
                                    }

                                }
                            }

                            update_post_meta($obj->post_id, $obj->meta_key, $mapping);
                            break;

                        default:
                            if (!in_array($obj->meta_key, $wwpp_existing_meta_keys)) {
                                delete_post_meta($obj->post_id, $obj->meta_key);
                            }

                    }

                }

            }

            // Initialize visibility meta
            $this->initialize_product_visibility_filter_meta();

            if (defined('DOING_AJAX') && DOING_AJAX) {

                @header('Content-Type: application/json; charset=' . get_option('blog_charset'));
                echo wp_json_encode(array('status' => 'success'));
                wp_die();

            } else {
                return true;
            }

        }

        /**
         * Plugin deactivation hook callback.
         *
         * @since 1.0.0
         * @since 1.12.5 Add flush rewrite rules.
         * @since 1.13.0 Add multisite support.
         * @access public
         */
        public function deactivate($network_wide)
        {

            global $wpdb;

            // check if it is a multisite network
            if (is_multisite()) {

                // check if the plugin has been activated on the network or on a single site
                if ($network_wide) {

                    // get ids of all sites
                    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

                    foreach ($blog_ids as $blog_id) {

                        switch_to_blog($blog_id);
                        $this->_deactivate($wpdb->blogid);

                    }

                    restore_current_blog();

                } else {
                    $this->_deactivate($wpdb->blogid); // activated on a single site, in a multi-site
                }

            } else {
                $this->_deactivate($wpdb->blogid); // activated on a single site
            }

            // Remove _have_wholesale_price meta
            // Since 1.25.2 Temporarily commenting this out. This causes issues for variable products.
            // TODO: Continue to improve this next version
            // $this->remove_have_wholesale_price_meta_on_deactivation();
        }

        /**
         * Remove <wholesale_role>_have_wholesale_price on plugin deactivation only if <wholesale_role>_wholesale_price has empty value.
         * This is a fix in the api update where the fetched non wholesale products will still return coz of that meta.
         *
         * @since 1.24.8
         * @access public
         */
        public function remove_have_wholesale_price_meta_on_deactivation()
        {

            global $wpdb;

            foreach ($this->_registered_wholesale_roles as $role_key => $role) {

                $args = array(
                    'post_type'      => array('product', 'product_variation'),
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                    'meta_query'     => array(
                        array(
                            'key'     => $role_key . '_have_wholesale_price',
                            'value'   => 'yes',
                            'compare' => '=',
                        ),
                        array(
                            'relation' => 'OR',
                            array(
                                'key'     => $role_key . '_wholesale_price',
                                'value'   => '',
                                'compare' => '=',
                            ),
                            array(
                                'key'     => $role_key . '_wholesale_price',
                                'value'   => 'gebbirish',
                                'compare' => 'NOT EXISTS',
                            ),
                        ),
                    ),
                );

                $query = new WP_Query($args);

                if (!empty($query->posts)) {

                    $ids = implode("','", $query->posts);
                    $wpdb->query("
                        DELETE FROM $wpdb->postmeta
                        WHERE post_id IN ('" . $ids . "') AND
                            meta_key = '{$role_key}_have_wholesale_price'
                    ");

                }

            }
        }

        /**
         * Plugin deactivation codebase.
         *
         * @since 1.13.0
         * @access public
         *
         * @param int $blog_id Site id.
         */
        private function _deactivate($blog_id)
        {

            flush_rewrite_rules();

            wc_delete_product_transients();

        }

        /**
         * Method to initialize a newly created site in a multi site set up.
         *
         * @since 1.13.0
         * @access public
         *
         * @param int    $blog_id Blog ID.
         * @param int    $user_id User ID.
         * @param string $domain  Site domain.
         * @param string $path    Site path.
         * @param int    $site_id Site ID. Only relevant on multi-network installs.
         * @param array  $meta    Meta data. Used to set initial site options.
         */
        public function new_mu_site_init($blog_id, $user_id, $domain, $path, $site_id, $meta)
        {

            if (is_plugin_active_for_network('woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php')) {

                switch_to_blog($blog_id);
                $this->_activate($blog_id);
                restore_current_blog();

            }

        }

        /**
         * Plugin initializaton.
         *
         * @since 1.2.9
         * @since 1.13.0 Add multi-site support.
         */
        public function initialize()
        {

            // Check if activation has been triggered, if not trigger it
            // Activation codes are not triggered if plugin dependencies are not present and this plugin is activated.
            $installed_version = is_multisite() ? get_site_option('wwpp_option_installed_version', false) : get_option('wwpp_option_installed_version', false);

            if (version_compare($installed_version, $this->_wwpp_current_version, '!=') || get_option('wwpp_option_activation_code_triggered', false) !== 'yes') {

                if (!function_exists('is_plugin_active_for_network')) {
                    require_once ABSPATH . '/wp-admin/includes/plugin.php';
                }

                $network_wide = is_plugin_active_for_network('woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php');
                $this->activate($network_wide);

                $this->clear_unused_role_properties();

                // Initialize visibility meta
                $this->initialize_product_visibility_filter_meta();

            }

        }

        /**
         * Remove 'shippingClassName' and 'shippingClassTermId' from role properties on plugin update.
         *
         * @since 1.23.9
         */
        public function clear_unused_role_properties()
        {

            if ($this->_wwpp_current_version === '1.23.9') {

                $all_registered_wholesale_roles  = unserialize(get_option(WWP_OPTIONS_REGISTERED_CUSTOM_ROLES));
                $all_registered_wholesale_roles2 = $all_registered_wholesale_roles;

                foreach ($all_registered_wholesale_roles2 as $role_key => $data) {

                    unset($data['shippingClassName']);
                    unset($data['shippingClassTermId']);
                    $all_registered_wholesale_roles[$role_key] = $data;

                }

                update_option(WWP_OPTIONS_REGISTERED_CUSTOM_ROLES, serialize($all_registered_wholesale_roles));

            }

        }

        /**
         * Getting Started notice on plugin activation.
         *
         * @since 1.24
         * @access public
         */
        public function wwpp_getting_started_notice()
        {

            // Check if current user is admin or shop manager
            // Check if getting started option is 'yes'
            if ((current_user_can('administrator') || current_user_can('shop_manager')) && (get_option('wwpp_admin_notice_getting_started_show') === 'yes' || get_option('wwpp_admin_notice_getting_started_show') === false)) {

                $screen = get_current_screen();

                // Check if WWS license page
                // Check if products pages
                // Check if woocommerce pages ( wc, products, analytics )
                // Check if plugins page
                if ($screen->id === 'settings_page_wwc_license_settings' || $screen->post_type === 'product' || in_array($screen->parent_base, array('woocommerce', 'plugins'))) {
                    ?>

                    <div class="updated notice wwpp-getting-started">
                        <p><img src="<?php echo WWP_IMAGES_URL; ?>wholesale-suite-activation-notice-logo.png" alt=""/></p>
                        <p><?php _e('Thank you for purchasing WooCommerce Wholesale Prices Premium – you now have a whole range of extra wholesale pricing, product and ordering features available.', 'woocommerce-wholesale-prices-premium');?>
                        <p><?php _e('A great place to get started is with our official guide to the Premium add-on. Click through below and it will take you through all you need to know and where to get extra assistance if you need it.', 'woocommerce-wholesale-prices-premium');?>
                        <p><a href="https://wholesalesuiteplugin.com/kb/woocommerce-wholesale-prices-premium-getting-started-guide/?utm_source=wwpp&utm_medium=kb&utm_campaign=wwppgettingstarted" target="_blank">
                            <?php _e('Read the Getting Started guide', 'woocommerce-wholesale-prices-premium');?>
                            <span class="dashicons dashicons-arrow-right-alt" style="margin-top: 5px"></span>
                        </a></p>
                        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss this notice.', 'woocommerce-wholesale-prices-premium');?></span></button>
                    </div><?php

                }

            }

        }

        /**
         * Remove WWP Getting Started notice.
         *
         * @since 1.24
         * @access public
         */
        public function remove_wwp_getting_started_notice()
        {

            global $wc_wholesale_prices;

            if ($wc_wholesale_prices) {
                remove_action('admin_notices', array($wc_wholesale_prices->wwp_bootstrap, 'getting_started_notice'), 10);
            }

        }

        /**
         * Hide WWPP getting started notice on close.
         *
         * @since 1.24
         * @access public
         */
        public function wwpp_getting_started_notice_hide()
        {

            // Hide WWP and WWPP notices
            update_option('wwp_admin_notice_getting_started_show', 'no');
            update_option('wwpp_admin_notice_getting_started_show', 'no');

            wp_send_json(array('status' => 'success'));

        }

        /*
        |---------------------------------------------------------------------------------------------------------------
        | Plugin Custom Action Links
        |---------------------------------------------------------------------------------------------------------------
         */

        /**
         * Add plugin listing custom action link ( settings ).
         *
         * @since 1.0.2
         * @since 1.12.8 Rename 'Plugin Settings' and 'License Settings' to just 'Settings' and 'Licence' respectively.
         * @since 1.14.0 Move to its proper model.
         * @access public
         *
         * @param array  $links Array of links.
         * @param string $file  Plugin basename.
         * @return array Filtered array of links.
         */
        public function add_plugin_listing_custom_action_links($links, $file)
        {

            // If WWP min requirement is not met don't display this extra links when WWPP is activated.
            if (get_option('wwp_running') !== 'yes') {
                return $links;
            }

            if ($file == plugin_basename(WWPP_PLUGIN_PATH . 'woocommerce-wholesale-prices-premium.bootstrap.php')) {

                if (!is_multisite()) {

                    $license_link = '<a href="options-general.php?page=wwc_license_settings&tab=wwpp">' . __('License', 'woocommerce-wholesale-prices-premium') . '</a>';
                    array_unshift($links, $license_link);

                }

                $settings_link = '<a href="admin.php?page=wc-settings&tab=wwp_settings">' . __('Settings', 'woocommerce-wholesale-prices-premium') . '</a>';
                array_unshift($links, $settings_link);

                $getting_started          = '<a href="https://wholesalesuiteplugin.com/kb/woocommerce-wholesale-prices-premium-getting-started-guide/?utm_source=wwpp&utm_medium=kb&utm_campaign=wwppgettingstarted" target="_blank">' . __('Getting Started', 'woocommerce-wholesale-prices-premium') . '</a>';
                $links['getting_started'] = $getting_started;

            }

            return $links;

        }

        /*
        |---------------------------------------------------------------------------------------------------------------
        | Execute Model
        |---------------------------------------------------------------------------------------------------------------
         */

        /**
         * Register model ajax handlers.
         *
         * @since 1.14.0
         * @access public
         */
        public function register_ajax_handler()
        {

            add_action("wp_ajax_wwpp_initialize_product_visibility_meta", array($this, 'initialize_product_visibility_filter_meta'));
            add_action("wp_ajax_wwpp_clear_unused_product_meta", array($this, 'wwpp_clear_unused_product_meta'));

        }

        /**
         * Execute model.
         *
         * @since 1.13.0
         * @access public
         */
        public function run()
        {

            // Load Plugin Text Domain
            add_action('plugins_loaded', array($this, 'load_plugin_text_domain'));

            register_activation_hook(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'woocommerce-wholesale-prices-premium' . DIRECTORY_SEPARATOR . 'woocommerce-wholesale-prices-premium.bootstrap.php', array($this, 'activate'));
            register_deactivation_hook(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'woocommerce-wholesale-prices-premium' . DIRECTORY_SEPARATOR . 'woocommerce-wholesale-prices-premium.bootstrap.php', array($this, 'deactivate'));

            // Execute plugin initialization ( plugin activation ) on every newly created site in a multi site set up
            add_action('wpmu_new_blog', array($this, 'new_mu_site_init'), 10, 6);

            // Initialize Plugin
            add_action('init', array($this, 'initialize'));

            add_action(WWPP_CRON_INITIALIZE_PRODUCT_WHOLESALE_VISIBILITY_FILTER, array($this, 'initialize_product_visibility_filter_meta'));

            add_action('init', array($this, 'register_ajax_handler'));

            add_filter('plugin_action_links', array($this, 'add_plugin_listing_custom_action_links'), 10, 2);

            // Getting Started notice
            add_action('init', array($this, 'remove_wwp_getting_started_notice'));
            add_action('admin_notices', array($this, 'wwpp_getting_started_notice'), 10);
            add_action('wp_ajax_wwpp_getting_started_notice_hide', array($this, 'wwpp_getting_started_notice_hide'));

        }

    }

}
