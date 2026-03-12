<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWOF_WWP_Wholesale_Prices')) {

    class WWOF_WWP_Wholesale_Prices
    {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * Property that holds the single main instance of WWOF_WWP_Wholesale_Prices.
         *
         * @since 1.6.6
         * @access private
         * @var WWOF_WWP_Wholesale_Prices
         */
        private static $_instance;

        /**
         * Model that houses the logic of retrieving information relating to WWOF Product Listings.
         *
         * @since 1.6.6
         * @access private
         * @var WWOF_Product_Listing
         */
        private $_wwof_product_listings;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        /**
         * WWOF_WWP_Wholesale_Prices constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWOF_WWP_Wholesale_Prices model.
         *
         * @access public
         * @since 1.6.6
         */
        public function __construct($dependencies)
        {

            $this->_wwof_product_listings = $dependencies['WWOF_Product_Listing'];

        }

        /**
         * Ensure that only one instance of WWOF_WWP_Wholesale_Prices is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWOF_WWP_Wholesale_Prices model.
         *
         * @return WWOF_WWP_Wholesale_Prices
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
         * Display wholesale price requirement message at the top of the search box wholesale ordering form.
         *
         * @return mixed
         *
         * @since 1.6.0
         * @since 1.6.1 Display the requirement only if the logged-in user is in the scope of the wwp registered custom user roles.
         * @since 1.6.6 Refactor codebase and move to its proper model
         * @since 1.14  Show minimum order quantity message if set via the per user settings.
         *              Order of priority will be Override Per User, Override Per Wholesale Role then General Minimum Order Quantity General Setting.
         */
        public function wwof_display_wholesale_price_requirement()
        {

            // Option to disable showing wholesale price requirement
            if (apply_filters('wwof_display_wholesale_price_requirement', true) == false) {
                return;
            }

            global $wc_wholesale_prices;

            if (empty($wc_wholesale_prices)) {
                return;
            }

            $wholesale_role = $wc_wholesale_prices->wwp_wholesale_roles->getUserWholesaleRole();
            $message = '';

            if (empty($wholesale_role)) {
                return;
            }

            $min_req_data = $this->get_min_requirement_data($wholesale_role[0]);
            extract($min_req_data);

            if ((!empty($min_order_quantity) || !empty($min_order_price))) {
                $message = $this->wwof_get_wholesale_price_requirement_message($min_order_quantity, $min_order_price, $min_req_logic);
            }

            if (!empty($message)) {

                $notice = array('msg' => $message, 'type' => 'notice');
                $notice = apply_filters('wwof_display_wholesale_price_requirement_notice_msg', $notice);

                wc_print_notice($notice['msg'], $notice['type']);

            }

        }

        /**
         * Get the minimum order requirement data.
         *
         * @param string $wholesale_role    The wholesale role of current login user.
         *
         * @since 1.17
         * @return array
         */
        public function get_min_requirement_data($wholesale_role)
        {

            $wwpp_order_requirement_mapping = get_option('wwpp_option_wholesale_role_order_requirement_mapping');
            $user_id = apply_filters('wwof_min_requirement_current_user_id', get_current_user_id());

            if (get_user_meta($user_id, 'wwpp_override_min_order_qty', true) == 'yes') {

                // Use override per user
                $min_order_quantity = get_user_meta($user_id, 'wwpp_min_order_qty', true);
                $min_order_price = '';
                $min_req_logic = '';

                if (get_user_meta($user_id, 'wwpp_override_min_order_price', true) == 'yes') {

                    $min_order_price = get_user_meta($user_id, 'wwpp_min_order_price', true);
                    $min_req_logic = get_user_meta($user_id, 'wwpp_min_order_logic', true);

                }

            } else if (get_option('wwpp_settings_override_order_requirement_per_role') == 'yes' && isset($wwpp_order_requirement_mapping[$wholesale_role])) {

                // Override per wholesale role option in the general setting
                $min_order_quantity = $wwpp_order_requirement_mapping[$wholesale_role]['minimum_order_quantity'];
                $min_order_price = $wwpp_order_requirement_mapping[$wholesale_role]['minimum_order_subtotal'];
                $min_req_logic = $wwpp_order_requirement_mapping[$wholesale_role]['minimum_order_logic'];

            } else {

                // Use general setting
                $min_order_quantity = get_option('wwpp_settings_minimum_order_quantity');
                $min_order_price = get_option('wwpp_settings_minimum_order_price');
                $min_req_logic = get_option('wwpp_settings_minimum_requirements_logic');

            }

            return array(
                'min_order_quantity' => $min_order_quantity,
                'min_order_price' => $min_order_price,
                'min_req_logic' => $min_req_logic,
            );
        }

        /**
         * Get the price of a product on shop pages with taxing applied (Meaning either including or excluding tax
         * depending on the settings of the shop).
         *
         * @since 1.4.1
         * @since 1.6.6 Refactor codebase and move to its proper model
         *
         * @param $product
         * @param $price
         * @param $wc_price_arg
         * @return mixed
         */
        public function wwof_get_product_shop_price_with_taxing_applied($product, $price, $wc_price_arg = array())
        {

            $taxes_enabled = get_option('woocommerce_calc_taxes');
            $wholesale_tax_display_shop = get_option('wwpp_settings_incl_excl_tax_on_wholesale_price');
            $woocommerce_tax_display_shop = get_option('woocommerce_tax_display_shop');

            if ($taxes_enabled == 'yes' && $wholesale_tax_display_shop == 'incl') {
                $filtered_price = wc_price(WWOF_Functions::wwof_get_price_including_tax($product, array('qty' => 1, 'price' => $price)));
            } elseif ($wholesale_tax_display_shop == 'excl') {
                $filtered_price = wc_price(WWOF_Functions::wwof_get_price_excluding_tax($product, array('qty' => 1, 'price' => $price)), $wc_price_arg);
            } else {

                if ($taxes_enabled == 'yes' && $woocommerce_tax_display_shop == 'incl') {
                    $filtered_price = wc_price(WWOF_Functions::wwof_get_price_including_tax($product, array('qty' => 1, 'price' => $price)));
                } else {
                    $filtered_price = wc_price(WWOF_Functions::wwof_get_price_excluding_tax($product, array('qty' => 1, 'price' => $price)), $wc_price_arg);
                }

            }

            return apply_filters('wwpp_filter_product_shop_price_with_taxing_applied', $filtered_price, $price, $product);

        }

        /**
         * Get product price.
         *
         * Version 1.3.2 change set:
         * We determine if a variation is active or not is by also checking the inventory status of the parent variable
         * product.
         *
         * @since 1.0.0
         * @since 1.3.0 Added feature to display wholesale price per order quantity as a list.
         * @since 1.3.2
         * @since 1.6.6 Refactor codebase and move to its proper model.
         * @since 1.7.0 Refactor codebase, remove unnecessary codes, make it more efficient and easy to maintain.
         * @since 1.8.1 Refactor codebase to allow support for changes on WWPP 1.16.1
         *
         * @param $product
         * @return string
         */
        public function wwof_get_product_price($product)
        {

            $discount_per_order_qty_html = "";
            $price_html = "";
            $hide_wholesale_discount = get_option("wwof_general_hide_quantity_discounts"); // Option to hide Product Quantity Based Wholesale Pricing

            do_action('before_wwof_get_product_price', $product);

            if (WWOF_Functions::wwof_get_product_type($product) == 'simple' || WWOF_Functions::wwof_get_product_type($product) == 'variation') {

                if ($hide_wholesale_discount === 'yes') {

                    add_filter('wwof_hide_table_on_wwof_form', '__return_true');
                    add_filter('wwof_hide_per_category_table_on_wwof_form', '__return_true');
                    add_filter('wwof_hide_per_wholesale_role_table_on_wwof_form', '__return_true');

                    $price_html = '<span class="price">' . $product->get_price_html() . '</span>';

                    remove_filter('wwof_hide_table_on_wwof_form', '__return_true');
                    remove_filter('wwof_hide_per_category_table_on_wwof_form', '__return_true');
                    remove_filter('wwof_hide_per_wholesale_role_table_on_wwof_form', '__return_true');

                } else {
                    $price_html = '<span class="price">' . $product->get_price_html() . '</span>';
                }

            }

            do_action('after_wwof_get_product_price', $product);

            $price_html = apply_filters('wwof_filter_product_item_price', $price_html, $product);

            return $price_html;

        }

        /**
         * Get product quantity field.
         *
         * @param WC_Product    $product
         * @param int           $product_id
         * @param string        $product_type
         * @param array         $wholesale_role
         *
         * @return string
         * @since 1.0.0
         * @since 1.6.6 Refactor codebase and move to its proper model
         * @since 1.7.0 added support for WooCommerce min/max quantities plugin.
         * @since 1.4.1 New paramater $product_id, $product_type, $wholesale_role for efficiency.
         */
        public function wwof_get_product_quantity_field($product, $product_id, $product_type, $wholesale_role)
        {

            // TODO: dynamically change max value depending on product stock ( specially when changing variations of a variable product )

            $initial_value = 1;
            $min_order_qty_html = '';

            // We only do this if WWPP is installed and active
            if (class_exists('WooCommerceWholeSalePrices') && class_exists('WooCommerceWholeSalePricesPremium')) {

                global $wc_wholesale_prices_premium, $wc_wholesale_prices;

                // We only do this if wholesale user
                if (!empty($wholesale_role)) {

                    if ($product_type != 'variable') {

                        $wholesale_price = WWOF_Functions::wwof_get_wholesale_price($product, $wholesale_role);

                        if (is_numeric($wholesale_price)) {

                            $min_order_qty = get_post_meta($product_id, $wholesale_role[0] . '_wholesale_minimum_order_quantity', true);
                            if ($min_order_qty) {
                                $initial_value = $min_order_qty;
                            }

                        }

                    }

                } // Wholesale Role Check

            } // WWPP check

            if ($product->is_in_stock()) {

                $input_args = WWOF_Product_Listing_Helper::get_product_quantity_input_args($product);
                $min = (isset($input_args['min_value']) && $input_args['min_value']) ? $input_args['min_value'] : 1;
                $max = (isset($input_args['max_value']) && $input_args['max_value']) ? $input_args['max_value'] : '';
                $tab_index_counter = isset($_REQUEST['tab_index_counter']) ? $_REQUEST['tab_index_counter'] : '';
                $stock_quantity = $product->get_stock_quantity();

                // prepare quantity input args.
                $quantity_args = array(
                    'input_value' => ($initial_value % $min > 0) ? $min : $initial_value,
                    'step' => (isset($input_args['step']) && $input_args['step']) ? $input_args['step'] : 1,
                    'min_value' => $min,
                    'max_value' => $max,
                );

                // if managing stock and max is not set, then set max to stock quantity.
                if ($product->managing_stock() == 'yes' && $stock_quantity && !$max && !$product->backorders_allowed()) {
                    $quantity_args['max_value'] = $stock_quantity;
                }

                $quantity_field = woocommerce_quantity_input($quantity_args, $product, false);

                // add tab index attribute.
                $quantity_field = str_replace('type="number"', 'type="number" tabindex="' . $tab_index_counter . '" input_value="' . $initial_value . '"', $quantity_field);

            } else {
                $quantity_field = '<span class="out-of-stock">' . __('Out of Stock', 'woocommerce-wholesale-order-form') . '</span>';
            }

            $quantity_field = $min_order_qty_html . $quantity_field;

            $quantity_field = apply_filters('wwof_filter_product_item_quantity', $quantity_field, $product);

            return $quantity_field;

        }

        /**
         * Get the message.
         *
         * @return string
         *
         * @param $wholesale_min_order_quantity
         * @param $wholesale_min_order_price
         * @param $wholesale_min_req_logic
         * @since 1.6.1
         * @since 1.6.6 Refactor codebase and move to its proper model
         */
        public function wwof_get_wholesale_price_requirement_message($wholesale_min_order_quantity, $wholesale_min_order_price, $wholesale_min_req_logic)
        {

            $message = '';

            /**
             * Make min order price requirement compatible with "Aelia Currency Switcher" plugin
             */
            if (WWOF_ACS_Integration_Helper::aelia_currency_switcher_active()) {

                $active_currency = get_woocommerce_currency();
                $shop_base_currency = get_option('woocommerce_currency');

                if ($active_currency != $shop_base_currency) {
                    $wholesale_min_order_price = WWOF_ACS_Integration_Helper::convert($wholesale_min_order_price, $active_currency, $shop_base_currency);
                }

            }

            if (!empty($wholesale_min_order_quantity) && !empty($wholesale_min_order_price) && !empty($wholesale_min_req_logic)) {
                $message = sprintf(__('<b>NOTE:</b> A minimum order quantity of <b>%1$s</b> %2$s minimum order subtotal of <b>%3$s</b> is required to activate wholesale pricing in the cart.', 'woocommerce-wholesale-order-form'), $wholesale_min_order_quantity, $wholesale_min_req_logic, $this->formatted_price($wholesale_min_order_price));
            } elseif (!empty($wholesale_min_order_quantity)) {
                $message = sprintf(__('<b>NOTE:</b> A minimum order quantity of <b>%1$s</b> is required to activate wholesale pricing in the cart.', 'woocommerce-wholesale-order-form'), $wholesale_min_order_quantity);
            } elseif (!empty($wholesale_min_order_price)) {
                $message = sprintf(__('<b>NOTE:</b> A minimum order subtotal of <b>%1$s</b> is required to activate wholesale pricing in the cart.', 'woocommerce-wholesale-order-form'), $this->formatted_price($wholesale_min_order_price));
            }

            return apply_filters('wwof_wholesale_order_requirements_message', $message, $wholesale_min_order_quantity, $wholesale_min_order_price, $wholesale_min_req_logic);

        }

        /**
         * Format Price.
         *
         * @since 1.15.6
         *
         * @param $wholesale_min_order_price
         * @return array
         */
        public function formatted_price($wholesale_min_order_price)
        {

            if (WWOF_Functions::is_plugin_active('woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php')) {
                $wholesale_min_order_price = WWP_Helper_Functions::wwp_formatted_price($wholesale_min_order_price);
            } else {
                $wholesale_min_order_price = wc_price($wholesale_min_order_price);
            }

            return $wholesale_min_order_price;

        }

        /**
         * Get the base currency mapping from the wholesale price per order quantity mapping.
         *
         * @since 1.3.1
         * @since 1.6.6 Refactor codebase and move to its proper model
         *
         * @param $mapping
         * @param $user_wholesale_role
         * @return array
         */
        private function wwof_get_base_currency_mapping($mapping, $user_wholesale_role)
        {

            $base_currency_mapping = array();

            foreach ($mapping as $map) {

                // Skip non base currency mapping
                if (array_key_exists('currency', $map)) {
                    continue;
                }

                // Skip mapping not meant for the current user wholesale role
                if ($user_wholesale_role[0] != $map['wholesale_role']) {
                    continue;
                }

                $base_currency_mapping[] = $map;

            }

            return $base_currency_mapping;

        }

        /**
         * Get the specific currency mapping from the wholesale price per order quantity mapping.
         *
         * @since 1.3.1
         * @since 1.6.6 Refactor codebase and move to its proper model
         *
         * @param $mapping
         * @param $user_wholesale_role
         * @param $active_currency
         * @param $base_currency_mapping
         * @return array
         */
        private function wwof_get_specific_currency_mapping($mapping, $user_wholesale_role, $active_currency, $base_currency_mapping)
        {

            // Get specific currency mapping
            $specific_currency_mapping = array();

            foreach ($mapping as $map) {

                // Skip base currency
                if (!array_key_exists('currency', $map)) {
                    continue;
                }

                // Skip mappings that are not for the active currency
                if (!array_key_exists($active_currency . '_wholesale_role', $map)) {
                    continue;
                }

                // Skip mapping not meant for the currency user wholesale role
                if ($user_wholesale_role[0] != $map[$active_currency . '_wholesale_role']) {
                    continue;
                }

                // Only extract out mappings for this current currency that has equivalent mapping
                // on the base currency.
                foreach ($base_currency_mapping as $base_map) {

                    if ($base_map['start_qty'] == $map[$active_currency . '_start_qty'] && $base_map['end_qty'] == $map[$active_currency . '_end_qty']) {

                        $specific_currency_mapping[] = $map;
                        break;

                    }

                }

            }

            return $specific_currency_mapping;

        }

        /**
         * Show or Hide wholesale price requirement printed above the order form.
         *
         * @since 1.8.5
         *
         * @param bool $value
         * @return bool
         */
        public function wwof_show_hide_wholesale_price_requirement($value)
        {

            return get_option('wwof_display_wholesale_price_requirement', 'yes') == 'yes' ? $value : false;

        }

        /**
         * Update totals to include prduct add-ons.
         * Source: Product_Addon_Display->totals()
         *
         * @since 1.8.5
         *
         * @param text          $price_html
         * @param WC_Product    $product
         * @return text
         */
        public function wwof_show_addon_sub_total($price_html, $product)
        {

            global $Product_Addon_Display;

            if ($Product_Addon_Display != null && (get_class($Product_Addon_Display) == 'WC_Product_Addons_Display' || get_class($Product_Addon_Display) == 'Product_Addon_Display_Legacy')) {

                $post_id = WWOF_Functions::wwof_get_product_id($product);

                ob_start();

                // Reset the global product so that it won't spit any error in logs
                unset($GLOBALS['product']);
                $GLOBALS['product'] = $product;

                $Product_Addon_Display->display($post_id);
                $product_addons = ob_get_clean();

                if (trim($product_addons) == '') {
                    return $price_html;
                }

                if (!isset($product) || $product->get_id() != $post_id) {
                    $the_product = wc_get_product($post_id);
                } else {
                    $the_product = $product;
                }

                if (is_object($the_product)) {
                    $tax_display_mode = get_option('woocommerce_tax_display_shop');
                    $display_price = 'incl' === $tax_display_mode ? wc_get_price_including_tax($the_product) : wc_get_price_excluding_tax($the_product);
                } else {
                    $display_price = '';
                    $raw_price = 0;
                }

                if ('no' === get_option('woocommerce_prices_include_tax')) {
                    $tax_mode = 'excl';
                    $raw_price = wc_get_price_excluding_tax($the_product);
                } else {
                    $tax_mode = 'incl';
                    $raw_price = wc_get_price_including_tax($the_product);
                }

                if (class_exists('WWP_Wholesale_Prices')) {

                    global $wc_wholesale_prices;

                    $wholesale_role = $wc_wholesale_prices->wwp_wholesale_roles->getUserWholesaleRole();

                    $display_price = WWOF_Functions::wwof_get_wholesale_price($the_product, $wholesale_role);
                    $raw_price = $display_price;

                }

                $display_totals = '<div class="product-addons-total" data-show-sub-total="' . (apply_filters('woocommerce_product_addons_show_grand_total', true, $the_product) ? 1 : 0) . '" data-type="' . esc_attr($the_product->get_type()) . '" data-tax-mode="' . esc_attr($tax_mode) . '" data-tax-display-mode="' . esc_attr($tax_display_mode) . '" data-price="' . esc_attr($display_price) . '" data-raw-price="' . esc_attr($raw_price) . '" data-product-id="' . esc_attr($post_id) . '"></div>';

                return $price_html . $display_totals;

            }

            return $price_html;

        }

        /**
         * Return product category ids that has wholesale role filter set for the current user.
         *
         * @param string    $wholesale_role Current wholesale role of user
         *
         * @since 1.8.8
         * @return array
         */
        public function get_restricted_product_cat_ids_for_wholesale_user($wholesale_role)
        {

            $product_cat_wholesale_role_filter = get_option(WWPP_OPTION_PRODUCT_CAT_WHOLESALE_ROLE_FILTER, array());
            $filtered_terms_ids = array();

            foreach ($product_cat_wholesale_role_filter as $term_id => $filtered_wholesale_roles) {
                if (!in_array($wholesale_role, $filtered_wholesale_roles)) {
                    $filtered_terms_ids[] = $term_id;
                }
            }

            return $filtered_terms_ids;

        }

        /**
         * Products that are not allowed to show for the current user.
         *
         * @since 1.8.8
         *
         * @param string    $role
         * @return array
         */
        public function get_all_products_restricted_via_category($role)
        {

            global $wpdb;

            $filtered_terms_ids = $this->get_restricted_product_cat_ids_for_wholesale_user($role);
            $restricted_products = array();
            $restricted_variations = array();

            if ($filtered_terms_ids) {
                $results = $wpdb->get_results("SELECT p.ID FROM $wpdb->posts p
                                                LEFT JOIN $wpdb->term_relationships tr ON (p.ID = tr.object_id)
                                                WHERE p.post_status = 'publish'
                                                    AND p.post_type = 'product'
                                                    AND p.post_parent NOT IN ( SELECT p2.ID FROM $wpdb->posts p2 WHERE p2.post_type = 'product' AND p2.post_status != 'publish' )
                                                    AND tr.term_taxonomy_id IN ( " . implode(',', $filtered_terms_ids) . " )", ARRAY_A);
                foreach ($results as $product) {
                    $restricted_products[] = $product['ID'];
                }
            }

            if ($restricted_products) {
                $results2 = $wpdb->get_results("SELECT p.ID FROM $wpdb->posts p
                                                WHERE p.post_status = 'publish'
                                                    AND p.post_type = 'product_variation'
                                                    AND p.post_parent IN ( " . implode(',', $restricted_products) . " )", ARRAY_A);

                foreach ($results2 as $variation) {
                    $restricted_variations[] = $variation['ID'];
                }
            }

            return array_unique(array_merge($restricted_products, $restricted_variations));

        }

        /**
         * Products that have wholesale price set.
         *
         * @since 1.8.8
         *
         * @param string    $role
         * @return array
         */
        public function get_all_wholesale_products($role)
        {

            $wholesale_products = array();
            $wholesale_variations = array();
            $meta_sql_query = "AND ( ( pm1.meta_key = 'wwpp_product_wholesale_visibility_filter' AND pm1.meta_value IN ( '" . $role . "', 'all' ) )
                                            AND (
                                                    ( pm2.meta_key = '" . $role . "_have_wholesale_price' AND pm2.meta_value = 'yes' )
                                                    OR
                                                    ( pm2.meta_key = '" . $role . "_wholesale_price' AND CAST( pm2.meta_value AS SIGNED ) > 0 )
                                                )
                                        )";
            if ($role) {

                global $wpdb;

                $results = $wpdb->get_results("SELECT DISTINCT p.ID FROM $wpdb->posts p
                                    INNER JOIN $wpdb->postmeta pm1 ON ( p.ID = pm1.post_id )
                                    INNER JOIN $wpdb->postmeta pm2 ON ( p.ID = pm2.post_id )
                                    WHERE p.post_status = 'publish'
                                        AND p.post_type = 'product'" .
                    $meta_sql_query, ARRAY_A);

                if ($results) {
                    foreach ($results as $product) {
                        $wholesale_products[] = $product['ID'];
                    }
                }

                $wholesale_variations = array();

                if ($wholesale_products && get_option('wwof_general_list_product_variation_individually', 'no') === 'yes') {
                    $results2 = $wpdb->get_results("SELECT p.ID FROM $wpdb->posts p
                                                    INNER JOIN $wpdb->postmeta pm1 ON ( p.ID = pm1.post_id )
                                                    INNER JOIN $wpdb->postmeta pm2 ON ( p.ID = pm2.post_id )
                                                    WHERE p.post_status = 'publish'
                                                        AND p.post_type = 'product_variation'
                                                        AND p.post_parent IN ( " . implode(',', $wholesale_products) . " )" .
                        $meta_sql_query, ARRAY_A);

                    foreach ($results2 as $variation) {
                        $wholesale_variations[] = $variation['ID'];
                    }
                }

                return array_unique(array_merge($wholesale_products, $wholesale_variations));

            }

            return array();

        }

        /**
         * Products that have wholesale discount set via category.
         *
         * @since 1.8.8
         *
         * @param string    $role
         * @return array
         */
        public function get_all_wholesale_products_from_category($role)
        {

            global $wpdb;

            $terms = get_terms(array(
                'taxonomy' => 'product_cat',
                'fields' => 'ids',
            ));

            $wholesale_products_via_cat = array();

            if ($terms) {

                foreach ($terms as $term_id) {

                    $term_meta = get_option("taxonomy_$term_id");

                    if ($term_meta[$role . '_wholesale_discount']) {

                        $wholesale_products = array();
                        $wholesale_variations = array();

                        $results = $wpdb->get_results("SELECT p.ID FROM $wpdb->posts p
                                INNER JOIN $wpdb->term_relationships tr ON ( p.ID = tr.object_id )
                                WHERE p.post_status = 'publish'
                                    AND p.post_type = 'product'
                                    AND tr.term_taxonomy_id = $term_id", ARRAY_A);

                        if ($results) {
                            foreach ($results as $product) {
                                $wholesale_products[] = $product['ID'];
                            }
                        }

                        if ($wholesale_products) {
                            $results2 = $wpdb->get_results("SELECT p.ID FROM $wpdb->posts p
                                                            INNER JOIN $wpdb->postmeta pm1 ON ( p.ID = pm1.post_id )
                                                            INNER JOIN $wpdb->postmeta pm2 ON ( p.ID = pm2.post_id )
                                                            WHERE p.post_status = 'publish'
                                                                AND p.post_type = 'product_variation'
                                                                AND p.post_parent IN ( " . implode(',', $wholesale_products) . " )", ARRAY_A);

                            foreach ($results2 as $variation) {
                                $wholesale_variations[] = $variation['ID'];
                            }
                        }

                        $wholesale_products_via_cat = array_unique(array_merge($wholesale_products_via_cat, $wholesale_products, $wholesale_variations));

                    }

                }

                return array_values($wholesale_products_via_cat);

            }

            return array();

        }

        /**
         * This function will replace WWPP pre_get_posts_arg.
         * Customized function when List product variation individually" option is enabled
         *
         * @since 1.8.8
         * @since 1.14.1 Deprecated function
         * @access public
         *
         * @param array $query_args Query args array.
         * @return array
         */
        public function wwof_pre_get_posts_arg($query_args)
        {

            WWOF_Functions::deprecated_function(debug_backtrace(), 'wwof_pre_get_posts_arg', '1.14.1');

            return $query_args;

            // We only do this if WWP and WWPP are installed and active
            if (class_exists('WooCommerceWholeSalePrices') && class_exists('WooCommerceWholeSalePricesPremium')) {

                global $wc_wholesale_prices, $wc_wholesale_prices_premium;

                // When "List product variation individually" option is enabled
                if (get_option('wwof_general_list_product_variation_individually', 'no') === 'yes') {

                    $user_wholesale_role = $wc_wholesale_prices->wwp_wholesale_roles->getUserWholesaleRole();
                    $user_wholesale_role = (is_array($user_wholesale_role) && !empty($user_wholesale_role)) ? $user_wholesale_role[0] : '';
                    $wholesale_role_discount = get_option(WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING, array());
                    $override_discount_per_user = get_user_meta(get_current_user_id(), 'wwpp_override_wholesale_discount', true);

                    $restricted_products = $this->get_all_products_restricted_via_category($user_wholesale_role);

                    // Check if general wholesale role discount is not set for this current user wholesale role
                    if (!isset($wholesale_role_discount[$user_wholesale_role])) {

                        if (!isset($query_args['post__in'])) {
                            $query_args['post__in'] = array();
                        }

                        if (!isset($query_args['post__not_in'])) {
                            $query_args['post__not_in'] = array();
                        }

                        $wwpp_settings_only_show_wholesale_products_to_wholesale_users = get_option('wwpp_settings_only_show_wholesale_products_to_wholesale_users', false);

                        // Check if override per user is not set and user is not admin and user is not shop manager and is wholesale user
                        if ($override_discount_per_user != 'yes' && !current_user_can('manage_options') && !current_user_can('manage_woocommerce') && $user_wholesale_role) {

                            // If only show wholesale products to wholesale users option is enabled
                            if ($wwpp_settings_only_show_wholesale_products_to_wholesale_users === 'yes') {

                                $wholesale_products = $this->get_all_wholesale_products($user_wholesale_role);
                                $wholesale_products_via_cat = $this->get_all_wholesale_products_from_category($user_wholesale_role);
                                $merged_wholesale_products = array_unique(array_merge($wholesale_products, $wholesale_products_via_cat));

                                $query_args['post__in'] = !empty($merged_wholesale_products) ? array_intersect($query_args['post__in'], $merged_wholesale_products) : array();

                            }

                        }

                        // Don't show restricted products
                        if (!empty($restricted_products)) {

                            $query_args['post__in'] = array_diff($query_args['post__in'], $restricted_products);
                            $query_args['post__not_in'] = array_intersect($query_args['post__not_in'], $restricted_products);

                        }

                        // Filter wholesale products
                        if (isset($query_args['searched_keyword'])) {

                            $search_sku = get_option('wwof_general_allow_product_sku_search');
                            $search_the_sku = ($search_sku == 'yes') ? true : false;
                            $search_products = WWOF_Product_Listing_Helper::get_search_products($query_args['searched_keyword'], $search_the_sku);
                            $filter_search = array_intersect($query_args['post__in'], $search_products);

                            if (!empty($filter_search)) {
                                $query_args['post__in'] = $filter_search;
                            } else {
                                $query_args['post__in'] = array(0);
                            }

                        }

                        // Set post__in to empty products if no products found
                        if (empty($query_args['post__in'])) {
                            $query_args['post__in'] = array(0);
                        }

                    }

                }

            }

            return $query_args;

        }

        /**
         * Remove hook if "List product variation individually" option is enabled.
         * Add a customized 'wwof_filter_product_listing_query_arg' hook which is run in this function wwof_pre_get_posts_arg.
         *
         * @since 1.8.8
         * @since 1.14.1 Removed 'wwof_product_listing_price_filter_meta_query' filter. All covered by wwof_product_args filter.
         *               Removed 'wwof_filter_product_listing_query_arg' filter. All covered by wwof_product_args filter.
         *
         * @access public
         */
        public function wwof_remove_wwpp_pre_get_posts_arg_hook()
        {

            // We only do this if WWP and WWPP are installed and active
            if (class_exists('WooCommerceWholeSalePrices') && class_exists('WooCommerceWholeSalePricesPremium')) {

                global $wc_wholesale_prices, $wc_wholesale_prices_premium;

                remove_filter('wwof_filter_product_listing_query_arg', array($wc_wholesale_prices_premium->wwpp_query, 'pre_get_posts_arg'));

            }

        }

        /**
         * The initial query which have a list of product ID's to be used for the main query of Order Form.
         * We will filter which products the user can only view on this filter.
         *
         * @since 1.14.1
         * @access public
         */
        public function wwof_product_query_args($args, $filters)
        {

            // WWP and WWPP
            $wwpp_override_discount_per_user = get_user_meta(get_current_user_id(), 'wwpp_override_wholesale_discount', true);
            $only_show_wholesale_products = $this->_is_only_show_wholesale_products_to_wholesale_users_enabled_in_wwpp();
            $user_wholesale_role = $this->_get_current_user_wholesale_role();
            $user_wholesale_role = isset($user_wholesale_role[0]) ? $user_wholesale_role[0] : '';
            $wwpp_products = array();

            // Tax query init
            $filtered_term_ids = array();

            // If user is not admin/shop manager. Admin should see all products.
            if (!current_user_can('manage_woocommerce')) {
                // User is wholesale customer AND
                // only show wholesale products to wholesale users is enabled AND
                // general discount is not set
                if (!empty($user_wholesale_role) && $only_show_wholesale_products && !$this->_wholesale_user_have_general_role_discount($user_wholesale_role)) {
                    // If there is a default general wholesale discount set for this wholesale role then all products are considered wholesale for this dude
                    // If no mapping is present, then we only show products with wholesale prices specific for this wholesale role
                    // If there are defined products in the shortcode attributes then we use it instead of getting all products
                    if (empty($args['post__in'])) {

                        $wwpp_products = $this->optimized_meta_query(true, $user_wholesale_role, $args);
                        $args['post__in'] = $wwpp_products;
                        // meta and tax query is performed in optimized_meta_query function, no need to perform again
                        $args['meta_query'] = array();
                        $args['tax_query'] = array();

                    } else if (!empty($args['post__in']) && !in_array('0', $args['post__in'])) {

                        $args['post__in'] = $this->filter_products($args['post__in'], $args, $user_wholesale_role, $only_show_wholesale_products, $wwpp_override_discount_per_user);
                        $args['meta_query'] = array();
                        $args['tax_query'] = array();

                    }

                } else {

                    // If there are defined products in the shortcode attributes then we use it
                    if (empty($args['post__in']) && empty($wwpp_products)) {
                        $wwpp_products = $this->optimized_meta_query(false, $user_wholesale_role, $args);
                        $args['post__in'] = $wwpp_products;
                        // meta and tax query is performed in optimized_meta_query function, no need to perform again
                        $args['meta_query'] = array();
                        $args['tax_query'] = array();

                    } else if (!empty($args['post__in']) && !in_array('0', $args['post__in'])) {

                        $args['post__in'] = $this->filter_products($args['post__in'], $args, $user_wholesale_role, $only_show_wholesale_products, $wwpp_override_discount_per_user);
                        $args['meta_query'] = array();
                        $args['tax_query'] = array();

                    }

                }

            } else { // If user is Administrator or shop managers

                // If there are defined products in the shortcode attributes then we use it
                // give all access to products for admin
                if (empty($args['post__in']) && empty($wwpp_products)) {

                    $wwpp_products = $this->optimized_meta_query(false, $user_wholesale_role, $args);
                    $args['post__in'] = $wwpp_products;
                    // meta and tax query is performed in optimized_meta_query function, no need to perform again
                    $args['meta_query'] = array();
                    $args['tax_query'] = array();

                } else if (!empty($args['post__in']) && !in_array('0', $args['post__in'])) {

                    $args['post__in'] = $this->filter_products($args['post__in'], $args, $user_wholesale_role, $only_show_wholesale_products, $wwpp_override_discount_per_user);
                    $args['meta_query'] = array();
                    $args['tax_query'] = array();

                }
            }

            if (get_option('wwof_general_list_product_variation_individually', 'no') === 'yes') {
                $args['post_type'] = array('product', 'product_variation');
            }

            return $args;

        }

        /**
         * Filter products if category filter is triggered or if default category is set.
         * Filter restricted category.
         * Filter by wholesale price for current user.
         * Filter exclude product option
         * List products individually
         *
         * @since 1.14.1
         * @access public
         *
         * @param bool      $restricted_products                List of products available for the current user
         * @param array     $args                               WP Query args
         * @param array     $user_wholesale_role                Current holesale role
         * @param string    $only_show_wholesale_products       Only Show Wholesale Products To Wholesale Users option
         * @param string    $wwpp_override_discount_per_user    Override per user option
         *
         * @return array
         */
        public function filter_products($restricted_products, $args, $user_wholesale_role, $only_show_wholesale_products, $wwpp_override_discount_per_user)
        {
            if (empty($restricted_products)) {
                return array(0);
            }

            $list_product_variation_individually = get_option('wwof_general_list_product_variation_individually', 'no');
            $wwof_exclude_prod_filter = get_option('wwof_filters_exclude_product_filter');

            // Set meta query
            if (!empty($user_wholesale_role) && !empty($restricted_products) && $only_show_wholesale_products && $wwpp_override_discount_per_user !== 'yes') {

                if (!isset($args['meta_query'])) {
                    $args['meta_query'] = array();
                }

                $args['meta_query'][] = array(
                    'relation' => 'OR',
                    array(
                        'key' => $user_wholesale_role . '_have_wholesale_price',
                        'value' => 'yes',
                        'compare' => '=',
                    ),
                    array( // WWPP-158 : Compatibility with WooCommerce Show Single Variations
                        'key' => $user_wholesale_role . '_wholesale_price',
                        'value' => 0,
                        'compare' => '>',
                        'type' => 'NUMERIC',
                    ),
                );

            }

            // Product level visibility / Restrictions
            // Only do this if shortcode products is set to avoid loading time issue for large shops. ex 12k products
            if (class_exists('WooCommerceWholeSalePricesPremium') && !empty($args['post__in'])) {

                $wwpp_visibility[] = array(
                    'key' => WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER,
                    'value' => array($user_wholesale_role, 'all'),
                    'compare' => 'IN',
                );

                $args['meta_query'] = array_merge(
                    $args['meta_query'],
                    $wwpp_visibility
                );

            }

            // Category level visibility / Restrictions
            if (!empty($user_wholesale_role) && !empty($this->_product_cat_wholesale_role_filter()) && WWOF_Functions::is_wwpp_active()) {
                $filtered_term_ids = $this->get_restricted_product_cat_ids_for_wholesale_user($user_wholesale_role);
            } else {
                $filtered_term_ids = array_keys($this->_product_cat_wholesale_role_filter());
            }

            // Set tax query
            if (!empty($filtered_term_ids)) {

                if (!isset($args['tax_query'])) {
                    $args['tax_query'] = array();
                }

                $args['tax_query'][] = array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => array_map('intval', $filtered_term_ids),
                    'operator' => 'NOT IN',
                );

            }

            // Tax query will not work for variations so we will use the parent to check if its visible to current user
            $restricted_args = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_query' => $args['meta_query'],
                'tax_query' => $args['tax_query'],
                'post__in' => $restricted_products,
                'post__not_in' => !empty($wwof_exclude_prod_filter) ? $wwof_exclude_prod_filter : array(),
            );

            $restricted_query = new WP_Query($restricted_args);
            $restricted_products_filtered = $restricted_query->posts;

            // Get variations if List Product Variation Individually is enabled
            if ($list_product_variation_individually == 'yes' && !empty($restricted_products_filtered)) {

                global $wpdb;

                $restricted_variations = array();

                // Wholesale Customers and "Only Show Wholesale Products To Wholesale Customers" is enabled
                if ((!empty($user_wholesale_role) && $only_show_wholesale_products)) {

                    $variations = $wpdb->get_results("SELECT p.ID, p.post_parent FROM $wpdb->posts p
                        INNER JOIN $wpdb->postmeta pm1 ON ( p.ID = pm1.post_id )
                        INNER JOIN $wpdb->postmeta pm2 ON ( p.ID = pm2.post_id )
                        WHERE p.post_status = 'publish'
                            AND p.post_type = 'product_variation'
                            AND ( pm1.meta_key = 'wwpp_product_wholesale_visibility_filter' AND pm1.meta_value IN ( '" . $user_wholesale_role . "', 'all' ) )
                            AND (
                                    ( pm2.meta_key = '" . $user_wholesale_role . "_have_wholesale_price' AND pm2.meta_value = 'yes' )
                                    OR
                                    ( pm2.meta_key = '" . $user_wholesale_role . "_wholesale_price' AND CAST( pm2.meta_value AS SIGNED ) > 0 )
                                )
                            AND p.post_parent IN ( " . implode(',', array_map('intval', $restricted_products_filtered)) . " )", ARRAY_A);

                    $variations_from_category = $wpdb->get_results("SELECT p.ID, p.post_parent FROM $wpdb->posts p
                            INNER JOIN $wpdb->postmeta pm1 ON ( p.ID = pm1.post_id )
                            WHERE p.post_status = 'publish'
                                AND p.post_type = 'product_variation'
                                AND ( pm1.meta_key = 'wwpp_product_wholesale_visibility_filter' AND pm1.meta_value IN ( '" . $user_wholesale_role . "', 'all' ) )
                                AND p.post_parent IN (
                                    SELECT $wpdb->posts.ID
                                    FROM $wpdb->posts
                                    INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
                                    WHERE meta_key = '" . $user_wholesale_role . "_have_wholesale_price_set_by_product_cat'
                                        AND meta_value ='yes'
                                    AND $wpdb->posts.ID IN(" . implode(',', array_map('intval', $restricted_products_filtered)) . ")
                                )", ARRAY_A);

                    $variations = array_merge($variations, $variations_from_category);

                } else {

                    // Regular Customers
                    $variations = $wpdb->get_results("SELECT p.ID, p.post_parent FROM $wpdb->posts p
                    WHERE p.post_status = 'publish'
                        AND p.post_type = 'product_variation'
                        AND p.post_parent IN ( " . implode(',', array_map('intval', $restricted_products_filtered)) . " )", ARRAY_A);
                }

                if (!empty($variations)) {

                    $variable_products = array();

                    if (current_user_can('manage_woocommerce') || empty($user_wholesale_role) || (is_user_logged_in() && !empty($user_wholesale_role))) {

                        foreach ($variations as $variation) {
                            if (!WWOF_Functions::is_wwpp_active() || current_user_can('manage_woocommerce')) {
                                $restricted_variations[] = $variation['ID'];
                                $variable_products[] = $variation['post_parent'];
                            } else {

                                $role_variation_is_visible = get_post_meta($variation['ID'], 'wwpp_product_wholesale_visibility_filter');

                                if (empty($role_variation_is_visible) || in_array('all', $role_variation_is_visible) || in_array($user_wholesale_role, $role_variation_is_visible)) {
                                    $restricted_variations[] = $variation['ID'];
                                }
                                $variable_products[] = $variation['post_parent'];
                            }
                        }

                    }

                    // Remove variable product
                    $variable_products = array_unique($variable_products);
                    $restricted_products_filtered = array_diff($restricted_products_filtered, $variable_products);

                }

                // Insert wholesale variations
                $restricted_products_filtered = array_unique(array_merge($restricted_products_filtered, $restricted_variations));

            }

            return empty($restricted_products_filtered) ? array(0) : $restricted_products_filtered;

        }

        /**
         * Identical with WWPP_Query optimized_meta_query function.
         * Gather meta query here so we can cache if the feature is enabled.
         * Different ways transient cache gets deleted:
         * 1. Transients was set to expire weekly
         * 2. Transients can be purge via clear cache in the settings.
         * 3. Transients are cleard during product update
         * 4. Transients are cleard during category update
         * 5. Transients are cleared when updating wholesale price via the general discount
         * 6. A transient specific for user gets cleared when updating the profile incase discount is set per user.
         *
         * Transients Cache naming
         * 1. visitors              = wwof_cached_products_ids
         * 2. wholesale_users users = wwof_cached_products_ids_<wholesale_role_key>
         * 3. per user              = wwof_cached_products_ids_<user_id>
         *
         * @since 1.14.1
         * @access public
         *
         * @param bool      $only_show_wholesale_products   If "Only Show Wholesale Products To Wholesale Users" setting is enabled
         * @param string    $user_wholesale_role            Wholesale Role of current user.
         * @param array     $args                           Query Args of initial query
         *
         * @return array
         */
        public function optimized_meta_query($only_show_wholesale_products, $user_wholesale_role, $args)
        {

            global $wc_wholesale_order_form;

            // Cache name
            $transient_name = $wc_wholesale_order_form->_wwof_cache->get_cache_name($user_wholesale_role);

            // Contains cached ids
            $wwof_cached_products_ids = get_transient($transient_name);

            // Cache option check
            $wwof_product_cache_option = get_option('wwof_enable_product_cache');

            // Override per user is enabled
            $wwpp_override_discount_per_user = get_user_meta(get_current_user_id(), 'wwpp_override_wholesale_discount', true);

            if ($wwof_product_cache_option == 'yes' && !empty($wwof_cached_products_ids)) {

                // Using Cache
                return $this->filter_products($wwof_cached_products_ids, $args, $user_wholesale_role, $only_show_wholesale_products, $wwpp_override_discount_per_user);

            } else {
                // If cache is disabled / building cache

                // Product level visibility / Restrictions
                if (class_exists('WooCommerceWholeSalePricesPremium')) {
                    $wwpp_visibility = array();

                    if (!current_user_can('manage_woocommerce')) {
                        $wwpp_visibility[] = array(
                            'key' => WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER,
                            'value' => array($user_wholesale_role, 'all'),
                            'compare' => 'IN',
                        );
                    }

                    $args['meta_query'] = array_merge(
                        $args['meta_query'],
                        $wwpp_visibility
                    );

                }

                $restricted_args = array(
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                    'meta_query' => $args['meta_query'],
                );

                $restricted_query = new WP_Query($restricted_args);
                $restricted_products = $restricted_query->posts;

                // Set product ids cache. Persistent cache
                if (!empty($restricted_products) && $wwof_product_cache_option == 'yes') {

                    set_transient($transient_name, $restricted_products, WEEK_IN_SECONDS);
                }

                // Delete after a week

                return empty($restricted_products) ? array(0) : $this->filter_products($restricted_products, $args, $user_wholesale_role, $only_show_wholesale_products, $wwpp_override_discount_per_user);

            }

        }

        /**
         * Check if a wholesale user have an entry on general role discount mapping.
         * WooCommerce > Settings > Wholesale Prices > Discount > General Discount Options
         *
         * @since 1.12.8
         * @since 1.16.0 Refactor code base to get wholesale discount wholesale role level from 'WWPP_Wholesale_Price_Wholesale_Role' model.
         * @access private
         *
         * @param string $user_wholesale_role User Wholesale Role Key.
         * @return boolean Whether wholesale user have mapping entry or not.
         */
        private function _wholesale_user_have_general_role_discount($user_wholesale_role)
        {

            if (class_exists('WooCommerceWholeSalePricesPremium')) {

                global $wc_wholesale_prices_premium;
                $user_wholesale_discount = $wc_wholesale_prices_premium->wwpp_wholesale_price_wholesale_role->get_user_wholesale_role_level_discount(get_current_user_id(), $user_wholesale_role);

                return !empty($user_wholesale_discount['discount']);

            }

            return false;

        }

        /**
         * Get category role restrictions
         *
         * @since 1.14.1
         * @access public
         */
        private function _product_cat_wholesale_role_filter()
        {

            return class_exists('WooCommerceWholeSalePricesPremium') ? get_option(WWPP_OPTION_PRODUCT_CAT_WHOLESALE_ROLE_FILTER, array()) : array();

        }

        /**
         * Get the wholesale role of current user
         *
         * @since 1.14.1
         * @access public
         */
        public function _get_current_user_wholesale_role()
        {

            if (class_exists('WooCommerceWholeSalePrices')) {

                global $wc_wholesale_prices;

                $wholesale_role = $wc_wholesale_prices->wwp_wholesale_roles->getUserWholesaleRole();

                return (is_array($wholesale_role) && !empty($wholesale_role)) ? array_values($wholesale_role) : '';

            }

            return '';

        }

        /**
         * Check if WWPP enabled only show wholesale prices to wholesale users
         *
         * @since 1.14.1
         * @access public
         */
        private function _is_only_show_wholesale_products_to_wholesale_users_enabled_in_wwpp()
        {

            return class_exists('WooCommerceWholeSalePricesPremium') && get_option('wwpp_settings_only_show_wholesale_products_to_wholesale_users', false) == 'yes' ? true : false;

        }

        /**
         * Removes filter woocommerce_get_children if shop manager login so that it will show all product variations without restrictions.
         *
         * @since 1.19.1
         * @access private
         */
        private function _remove_wwpp_woocommerce_get_children_filter()
        {

            if (current_user_can('shop_manager') && WWOF_Functions::is_wwpp_active()) {
                global $wc_wholesale_prices_premium;

                remove_filter('woocommerce_get_children', array($wc_wholesale_prices_premium->wwpp_wholesale_price_variable_product, 'filter_available_variable_product_variations'), 10, 2);
            }
        }

        /**
         * Execute model.
         *
         * @since 1.6.6
         * @access public
         */
        public function run()
        {
            // remove wwpp filter for woocommerce_get_children if WWPP is active
            $this->_remove_wwpp_woocommerce_get_children_filter();

            // Display wholesale price requirement message at the top of the search box wholesale ordering form.
            add_action('wwof_action_before_product_listing_filter', array($this, 'wwof_display_wholesale_price_requirement'), 10, 1);

            // Enable / Disable showing minimum order subtotal on ordering form
            add_filter('wwof_display_wholesale_price_requirement', array($this, 'wwof_show_hide_wholesale_price_requirement'), 10, 1);

            add_filter('wwof_filter_product_item_price', array($this, 'wwof_show_addon_sub_total'), 10, 2);

            // Remove 'wwof_filter_product_listing_query_arg' filter in WWPP_Query
            // We will do our own query via wwof_product_args for wwof cache options.
            add_action('init', array($this, 'wwof_remove_wwpp_pre_get_posts_arg_hook'), 10);

            // Perform query cache, save product ids in transients to save running expensive query operation on next load.
            add_filter('wwof_product_args', array($this, 'wwof_product_query_args'), 10, 2);

        }

    }

}