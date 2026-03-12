<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWPP_Wholesale_Price_Requirement')) {

    /**
     * Model that handles the checking if a wholesale user meets the requirements of having wholesale price.
     *
     * @since 1.12.8
     */
    class WWPP_Wholesale_Price_Requirement
    {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * Property that holds the single main instance of WWPP_Wholesale_Price_Requirement.
         *
         * @since 1.12.8
         * @access private
         * @var WWPP_Wholesale_Price_Requirement
         */
        private static $_instance;

        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.12.8
         * @access private
         * @var WWPP_Wholesale_Roles
         */
        private $_wwpp_wholesale_roles;

        /**
         * Class attribute that houses bundle product items from a given cart.
         *
         * @since 1.15.0
         * @access public
         * @var array
         */
        private $_bundle_product_items;

        /**
         * Class attribute that houses composite product items from a given cart.
         *
         * @since 1.15.0
         * @access public
         * @var array
         */
        private $_composite_product_items;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        /**
         * WWPP_Wholesale_Price_Requirement constructor.
         *
         * @since 1.12.8
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Price_Requirement model.
         */
        public function __construct($dependencies)
        {

            $this->_wwpp_wholesale_roles = $dependencies['WWPP_Wholesale_Roles'];

        }

        /**
         * Ensure that only one instance of WWPP_Wholesale_Price_Requirement is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.12.8
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Price_Requirement model.
         * @return WWPP_Wholesale_Price_Requirement
         */
        public static function instance($dependencies)
        {

            if (!self::$_instance instanceof self) {
                self::$_instance = new self($dependencies);
            }

            return self::$_instance;

        }

        /*
        |--------------------------------------------------------------------------
        | Minimum Order Quantity Requirement
        |--------------------------------------------------------------------------
         */

        /**
         * Filter the price to show the minimum order quantity for wholesale users for this specific product.
         *
         * @since 1.4.0
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @since 1.14.5 Bug Fix. Per parent variable product minimum wholesale order quantity requirement not shown. (WWPP-417).
         * @since 1.15.3 Improvement. Move the variable product min order requirement label onto the variable product price instead of the variation price.
         * @since 1.16.0 Add support for wholesale order quantity step.
         * @access public
         *
         * @param string     $wholesale_price_html       Wholesale price markup.
         * @param float      $price                      Product price.
         * @param WC_Product $product                    Product object.
         * @param array      $user_wholesale_role        Array of user wholesale roles.
         * @param string     $wholesale_price_title_text Wholesale price title text.
         * @param string     $raw_wholesale_price        Raw wholesale price.
         * @param string     $source                     Source of the wholesale price being applied.
         * @return string Filtered wholesale price markup.
         */
        public function display_minimum_wholesale_order_quantity($wholesale_price_html, $price, $product, $user_wholesale_role, $wholesale_price_title_text, $raw_wholesale_price, $source)
        {

            if (!empty($user_wholesale_role)) {

                $product_id   = WWP_Helper_Functions::wwp_get_product_id($product);
                $product_type = WWP_Helper_Functions::wwp_get_product_type($product);

                if ($product_id) {

                    if ($product_type === "variable") {

                        $minimum_order  = get_post_meta($product_id, $user_wholesale_role[0] . "_variable_level_wholesale_minimum_order_quantity", true);
                        $order_qty_step = get_post_meta($product_id, $user_wholesale_role[0] . "_variable_level_wholesale_order_quantity_step", true);

                    } else {
                        // variation and simple

                        $minimum_order  = get_post_meta($product_id, $user_wholesale_role[0] . "_wholesale_minimum_order_quantity", true);
                        $order_qty_step = get_post_meta($product_id, $user_wholesale_role[0] . "_wholesale_order_quantity_step", true);

                    }

                    if (isset($minimum_order) && $minimum_order > 0) {

                        if ($product_type === "variable") {
                            $wholesale_price_html .= ' <span class="wholesale_price_minimum_order_quantity" style="display: block;">' . sprintf(__('Min: %1$s of any variation combination', 'woocommerce-wholesale-prices-premium'), $minimum_order) . '</span>';
                        } else // variation and simple
                        {
                            $wholesale_price_html .= ' <span class="wholesale_price_minimum_order_quantity" style="display: block;">' . sprintf(__('Min: %1$s', 'woocommerce-wholesale-prices-premium'), $minimum_order) . '</span>';
                        }

                        if (isset($order_qty_step) && $order_qty_step > 0) {

                            if ($product_type === "variable") {
                                $wholesale_price_html .= ' <span class="wholesale_price_order_quantity_step" style="display: block;">' . sprintf(__('Increments of %1$s', 'woocommerce-wholesale-prices-premium'), $order_qty_step) . '</span>';
                            } else {
                                $wholesale_price_html .= ' <span class="wholesale_price_order_quantity_step" style="display: block;">' . sprintf(__('Increments of %1$s', 'woocommerce-wholesale-prices-premium'), $order_qty_step) . '</span>';
                            }

                        }

                    }

                }

            }

            return $wholesale_price_html;

        }

        /**
         * Set order quantity attribute values for non variable product if one is set.
         *
         * @since 1.4.2
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @since 1.14.5 Bug Fix. Per parent variable product minimum wholesale order quantity requirement not shown. (WWPP-417).
         * @since 1.15.3 Silence notices thrown by function is_shop.
         * @since 1.16.0 Supports wholesale order quantity step.
         * @since 1.24   Fix Wholesale Minimum Order Quantity and Wholesale Order Quantity Step not working on Cart and Checkout.
         * @access public
         *
         * @param array      $args    Quantity field input args.
         * @param WC_Product $product Product object.
         * @return array Filtered quantity field input args.
         */
        public function set_order_quantity_attribute_values($args, $product)
        {

            if (is_null($product)) {
                $product = $GLOBALS['product'];
            }

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            $filtered_args       = $args;

            // Remove restriction for Wholesale Minimum Order Quantity and Wholesale Order Quantity Step on cart and checkout
            // Should work on Single product, cart and checkout page or any page using 'woocommerce_quantity_input_args' filter.
            if (!empty($user_wholesale_role)) {

                // No need for variable product, we don't need it be applied on a price range
                // We need it to be applied per variation for variable products

                $product_id   = WWP_Helper_Functions::wwp_get_product_id($product);
                $product_type = WWP_Helper_Functions::wwp_get_product_type($product);

                if ($product_id && $product_type !== "variable") {

                    $price_arr       = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3($product_id, $user_wholesale_role);
                    $wholesale_price = $price_arr['wholesale_price'];

                    if ($wholesale_price) {

                        $minimum_order_qty = get_post_meta($product_id, $user_wholesale_role[0] . "_wholesale_minimum_order_quantity", true);

                        if ($minimum_order_qty) {

                            if (is_cart()) {

                                $filtered_args['min_value']   = 0;
                                $filtered_args['input_value'] = $filtered_args['input_value'];

                            } else {

                                // Have minimum order quty
                                // For other pages other than cart
                                $filtered_args['min_value']   = 1;
                                $filtered_args['input_value'] = $minimum_order_qty;

                            }

                            $order_qty_step = get_post_meta($product_id, $user_wholesale_role[0] . "_wholesale_order_quantity_step", true);

                            if ($order_qty_step) {

                                /**
                                 * Step will require min qty to be set. If step is set, but min is not, step will be voided.
                                 *
                                 * Ok explanation as to why doing this.
                                 *
                                 * HTML 5 have this behavior for number fields.
                                 * -> If step value is greater or equal than input value, it will base off of min value
                                 * ----> Ex. min : 1 , value : 10 , step : 10 , if you click up arrow key once, the value becomes 11, not 20, coz 1 ( min ) + 10 ( step ) = 11
                                 * -> If step value is less than the input value, it will base off of input value
                                 * ----> Ex. min : 1 , value : 10 , step : 9 , if you click up arrow key once, the value becomes 19, not 10, coz 10 ( input value ) + 9 ( step ) = 19
                                 *
                                 * So to resolve this unexpected behavior, we either set min as blank or value of zero.
                                 * Setting min as blank will allow them to order quantity less than and equal zero. ( but ordering qty less than zero will not add item to cart ).
                                 * Setting min as zero allows them to order quantity with value of zero ( but it will only add 1 qty of this product to cart,  this is similar to shop page, where you can add item without specifying the qty ).
                                 * Setting the min to the min we set however will solve this issue.
                                 *
                                 * Setting value of min to zero or blank will still not allow them to order lower than min qty anyways, that is not within the step multiplier.
                                 *
                                 * So setting step will prevent wholesale customers from buying lower than min qty.
                                 */

                                $filtered_args['step']      = 1;
                                $filtered_args['min_value'] = 0;

                                // If in cart page and "Enforce Product Min/Step On Cart Page" option is enabled then dont allow setting below the min qty.
                                // If "Enforce Product Min/Step On Cart Page" is disabled, allow setting below the min qty.
                                // If user is in other page than cart then always enforce, which is the original behavior.
                                if (is_cart() && get_option('wwpp_settings_enforce_product_min_step_on_cart_page') === 'yes' || !is_cart()) {

                                    $filtered_args['min_value'] = $minimum_order_qty;
                                    $filtered_args['step']      = $order_qty_step;

                                }

                            }

                        }

                    }

                }

            }

            return apply_filters('wwpp_filter_set_product_quantity_value_to_minimum_order_quantity', $filtered_args, $args, $product, $user_wholesale_role);

        }

        /**
         * Mute the min max plugin min qty rule if the current user is a wholesale customer and have its own min qty rule.
         * Basically WWPP overrides min max plugin min set for this product for this wholesale customer.
         *
         * @since 1.16.1
         * @access public
         *
         * @param int    $min_max_min_rule Min max plugin min rule.
         * @param int    $product_id       Product ID.
         * @param string $cart_item_key    Hash key.
         * @param array  $values           Product data array.
         */
        public function mute_min_max_plugin_min_qty_rule($min_max_min_rule, $product_id, $cart_item_key, $values)
        {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if (empty($user_wholesale_role)) {
                return $min_max_min_rule;
            }

            $price_arr       = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3($product_id, $user_wholesale_role);
            $wholesale_price = $price_arr['wholesale_price'];

            if (!$wholesale_price) {
                return $min_max_min_rule;
            }

            $minimum_order_qty = get_post_meta($product_id, $user_wholesale_role[0] . "_wholesale_minimum_order_quantity", true);

            if ($minimum_order_qty) {
                return '';
            }
            // return $minimum_order_qty;
            else {
                return $min_max_min_rule;
            }

        }

        /**
         * Mute the min max plugin order qty rule if the current user is a wholesale customer and have its own step qty rule.
         * Basically WWPP overrides min max plugin min set for this product for this wholesale customer.
         *
         * @since 1.16.1
         * @access public
         *
         * @param int    $min_max_min_rule Min max plugin min rule.
         * @param int    $product_id       Product ID.
         * @param string $cart_item_key    Hash key.
         * @param array  $values           Product data array.
         */
        public function mute_min_max_plugin_group_qty_rule($min_max_group_rule, $product_id, $cart_item_key, $values)
        {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if (empty($user_wholesale_role)) {
                return $min_max_group_rule;
            }

            $product      = wc_get_product($product_id);
            $product_type = WWP_Helper_Functions::wwp_get_product_type($product);

            if (!in_array($product_type, array('simple', 'variation'))) {
                return $min_max_group_rule;
            }

            $price_arr       = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3($product_id, $user_wholesale_role);
            $wholesale_price = $price_arr['wholesale_price'];

            if (!$wholesale_price) {
                return $min_max_group_rule;
            }

            $order_qty_step = get_post_meta($product_id, $user_wholesale_role[0] . "_wholesale_order_quantity_step", true);

            if ($order_qty_step) {
                return '';
            }
            // return $order_qty_step;
            else {
                return $min_max_group_rule;
            }

        }

        /**
         * Mute the changes min max plugin will make on the cart page when accessing cart page as wholesale customer.
         *
         * @since 1.16.1
         * @access public
         */
        public function mute_min_max_qty_field_mods_on_cart_page()
        {

            if (!is_cart()) {
                return;
            }

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if (empty($user_wholesale_role)) {
                return;
            }

            $wc_min_max_quantities = WC_Min_Max_Quantities::get_instance();

            remove_filter('woocommerce_quantity_input_args', array($wc_min_max_quantities, 'update_quantity_args'), 10, 2);
            remove_filter('woocommerce_available_variation', array($wc_min_max_quantities, 'available_variation'), 10, 3);
            remove_action('wp_enqueue_scripts', array($wc_min_max_quantities, 'load_scripts'));

        }

        /**
         * Mute the add product to cart validation of min max plugin if current user is wholesale customer and current product being added have wholesale price.
         *
         * @since 1.16.1
         * @access public
         */
        public function mute_min_max_plugin_add_product_to_cart_validation($pass, $product_id, $quantity)
        {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if (empty($user_wholesale_role)) {
                return $pass;
            }

            $product      = wc_get_product($product_id);
            $product_type = WWP_Helper_Functions::wwp_get_product_type($product);

            if (!in_array($product_type, array('simple', 'variation', 'variable'))) {
                return $pass;
            }

            $price_arr       = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3($product_id, $user_wholesale_role);
            $wholesale_price = $price_arr['wholesale_price'];

            if (!$wholesale_price) {
                return $pass;
            }

            $wc_min_max_quantities = WC_Min_Max_Quantities::get_instance();

            remove_filter('woocommerce_add_to_cart_validation', array($wc_min_max_quantities, 'add_to_cart'), 10, 4);

            return $pass;

        }

        /**
         * Set qty field value to the min set for this variation product. We only use the per variation min, we dont enforce min if it is set per variable product.
         * This function is Min/Max Quantities plugin compatible. We only change the qty field value, not touch the others.
         * Our min set will take precedence over what is set on Min/Max Quantities plugin.
         * The min we set will be set on the qty field value, not the min attribute coz we still allow purchase lower than min set.
         *
         * @since 1.15.3
         * @since 1.16.0 Add support for wholesale order quantity step.
         * @since 1.16.7
         * WWPP-585
         * There are bugs when a variable product have more than 30 variations.
         * 1.) The min order quantity and step values are not set on the quantity field.
         * 2.) The min order quantity and step values notice markup is not shown when variations of a variable product have the same price ( same regular and same wholesale price ).
         *
         * The reason for this is if a variable product have more than 30 variations, it will have different behavior on getting variations data versus when it has only 30 or less variations.
         * When 30 or less variations, it will append the variation data on the form markup as a data attribute.
         * When more than 30, it will fetch the variations data via ajax on the backend.
         * @access public
         *
         * @param array                $args Array of variation arguments.
         * @param WC_Product_Variable  $variable Variable product instance.
         * @param WC_Product_Variation $variation Variation product instance.
         * @return array Filtered array of variation arguments.
         */
        public function enforce_min_order_qty_requirement_on_qty_field($args, $variable, $variation)
        {

            $variation_id        = WWP_Helper_Functions::wwp_get_product_id($variation);
            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            $price_arr           = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3($variation_id, $user_wholesale_role);
            $wholesale_price     = $price_arr['wholesale_price'];
            $custom_price_html   = false;

            if (!empty($user_wholesale_role) && !empty($wholesale_price)) {

                $minimum_order_qty = get_post_meta($variation_id, $user_wholesale_role[0] . "_wholesale_minimum_order_quantity", true);

                if ($minimum_order_qty) {

                    $args['input_value'] = $minimum_order_qty;
                    $args['min_value']   = 1;

                    /**
                     * If price html is not set or have a value of empty string.
                     * It means that the variations of a variable product all have same price ( regular and wholesale ).
                     * We need to return a custom price html containing the min order quantity notice markup.
                     */
                    if (!isset($args['price_html']) || empty($args['price_html'])) {

                        $args['price_html'] = '<span class="wholesale_price_minimum_order_quantity" style="display: block;">' . sprintf(__('Min: %1$s', 'woocommerce-wholesale-prices-premium'), $minimum_order_qty) . '</span>';
                        $custom_price_html  = true;

                    }

                    $order_qty_step = get_post_meta($variation_id, $user_wholesale_role[0] . "_wholesale_order_quantity_step", true);

                    if ($order_qty_step) {

                        $args['wwpp_step_specified'] = true;
                        $args['step']                = $order_qty_step;
                        $args['min_value']           = $minimum_order_qty;

                        /**
                         * Meaning we have generated our own price_html markup, if so, then append the step value notice markup.
                         * We need to make sure we only append if we have a custom price_html, else , we will end up on appending on the original price_html markup generated by woocommerce.
                         */
                        if ($custom_price_html) {
                            $args['price_html'] .= '<span class="wholesale_price_order_quantity_step" style="display: block;">' . sprintf(__('Increments of %1$s', 'woocommerce-wholesale-prices-premium'), $order_qty_step) . '</span>';
                        }

                    }

                }

            }

            return $args;

        }

        /**
         * Extract bundle and composite products from cart items. We will use these later.
         *
         * @since 1.15.0
         * @access public
         *
         * @param $cart_object         WC_Cart object.
         * @param $user_wholesale_role Array of user wholesale role.
         */
        public function extract_bundle_and_composite_products($cart_object, $user_wholesale_role)
        {

            $this->_bundle_product_items    = array();
            $this->_composite_product_items = array();

            foreach ($cart_object->get_cart() as $cart_item_key => $cart_item) {

                if (WWP_Helper_Functions::wwp_get_product_type($cart_item['data']) === 'bundle') {

                    $bundle_product = wc_get_product($product_id = WWP_Helper_Functions::wwp_get_product_id($cart_item['data']));
                    $bundle_items   = $bundle_product->get_bundled_items();

                    foreach ($bundle_items as $bundle_item) {
                        $this->_bundle_product_items[$bundle_item->item_id] = array('is_priced_individually' => $bundle_item->is_priced_individually());
                    }

                } elseif (WWP_Helper_Functions::wwp_get_product_type($cart_item['data']) === 'composite') {

                    $composite_product = wc_get_product($product_id = WWP_Helper_Functions::wwp_get_product_id($cart_item['data']));
                    $composite_items   = $composite_product->get_composite_data();

                    foreach ($composite_items as $composite_item) {

                        $composite_item_obj = new WC_CP_Component($composite_item['component_id'], $cart_item['data']);

                        $this->_composite_product_items[$composite_item['component_id']] = array('is_priced_individually' => $composite_item_obj->is_priced_individually());

                    }

                }

            }

        }

        /**
         * Check if cart item is to be included on cart totals computation. This is our own cart price total and items total computation.
         * We cannot use the get_total function of WC_Cart here coz we are hooking into 'woocommerce_before_calculate_totals' which is too early to use get_total.
         * We need to hook into 'woocommerce_before_calculate_totals' tho, this is important.
         * Basically what we are doing is skipping products on cart that are part or a component of a complex product and they are not priced individually.
         *
         * @since 1.15.0
         * @access public
         *
         * @param boolean $return              Boolean flag that determines either include or exclude current cart item on our custom cart totals computation.
         * @param array   $cart_item           Cart item.
         * @param array   $user_wholesale_role Current user wholesale roles.
         */
        public function filter_if_cart_item_is_included_on_cart_totals_computation($return, $cart_item, $user_wholesale_role)
        {

            /*
             * Only perform the check if wholesale role is not empty and product price is not empty
             * Products with empty price might belong to a composite or a bundle or any complex product set up as non per-product pricing.
             * In these cases, automatically return false. ( Don't apply wholesale pricing ).
             */
            if (empty($user_wholesale_role) || $cart_item['data']->get_price() === '') {
                return false;
            }
            // Not a wholesale user and/or no product price

            if (isset($cart_item['bundled_by']) && $cart_item['bundled_by'] &&
                array_key_exists($cart_item['bundled_item_id'], $this->_bundle_product_items) && !$this->_bundle_product_items[$cart_item['bundled_item_id']]['is_priced_individually']) {
                return false;
            }

            if (isset($cart_item['composite_parent']) && $cart_item['composite_parent'] &&
                array_key_exists($cart_item['composite_item'], $this->_composite_product_items) && !$this->_composite_product_items[$cart_item['composite_item']]['is_priced_individually']) {
                return false;
            }

            return $return;

        }

        /**
         * Check if variable product requirement is meet.
         *
         * @since 1.16.0
         * @access public
         *
         * @param int   $variable_id          Variable Id.
         * @param int   $variation_id         Variation Id.
         * @param array $cart_item            Cart item data.
         * @param int   $variable_total       Variable total.
         * @param string $user_wholesale_role User wholesale role.
         * @return boolean|array True if passed, array of error data on failure.
         */
        private function _check_if_variable_product_requirement_is_meet($variable_id, $variation_id, $cart_item, $variable_total, $user_wholesale_role)
        {

            // Variable Level MOQ
            $variable_level_moq = get_post_meta($variable_id, $user_wholesale_role . '_variable_level_wholesale_minimum_order_quantity', true);
            $variable_level_moq = (is_numeric($variable_level_moq)) ? (int) $variable_level_moq : 0;

            if ($variable_total >= $variable_level_moq) {

                // Variable Level OQS
                $excess_qty = $variable_total - $variable_level_moq;

                if ($excess_qty) {
                    // Variable total is greater than variable level moq

                    $variable_level_oqs = get_post_meta($variable_id, $user_wholesale_role . '_variable_level_wholesale_order_quantity_step', true);
                    $variable_level_oqs = (is_numeric($variable_level_oqs)) ? (int) $variable_level_oqs : 0;

                    if ($variable_level_oqs && $excess_qty % $variable_level_oqs !== 0) {
                        return array('fail_type' => 'variable_level_oqs', 'variable_level_oqs' => $variable_level_oqs, 'variable_level_moq' => $variable_level_moq);
                    }

                }

                // Variation Level MOQ
                $variation_level_moq = get_post_meta($variation_id, $user_wholesale_role . '_wholesale_minimum_order_quantity', true);
                $variation_level_moq = (is_numeric($variation_level_moq)) ? (int) $variation_level_moq : 0;

                if ($cart_item['quantity'] >= $variation_level_moq) {

                    if ($variation_level_moq > 0) {

                        /**
                         * Only do Variation level OQS if Variation level MOQ is more than zero
                         */

                        $excess_qty = $cart_item['quantity'] - $variation_level_moq;

                        if ($excess_qty) {
                            // Variation qty is greater than variation level moq

                            $variation_level_oqs = get_post_meta($variation_id, $user_wholesale_role . '_wholesale_order_quantity_step', true);
                            $variation_level_oqs = (is_numeric($variation_level_oqs)) ? (int) $variation_level_oqs : 0;

                            if ($variation_level_oqs && $excess_qty % $variation_level_oqs !== 0) {
                                return array('fail_type' => 'variation_level_oqs', 'variation_level_oqs' => $variation_level_oqs, 'variation_level_moq' => $variation_level_moq);
                            }

                        }

                    }

                } else {
                    return array('fail_type' => 'variation_level_moq', 'variation_level_moq' => $variation_level_moq);
                }

                return true; // If passed through all filters, return true

            } else {
                return array('fail_type' => 'variable_level_moq', 'variable_level_moq' => $variable_level_moq);
            }

        }

        /**
         * Filter if apply wholesale price per product level. Validate if per product level requirements are meet or not.
         *
         * Important Note: We are retrieving the raw wholesale price, not wholesale price with applied tax. Just the raw
         * wholesale price of the product.
         *
         * @since 1.15.0
         * @since 1.16.0 Add support for wholesale order quantity step.
         * @access public
         *
         * @param boolean $apply_wholesale_price Boolean flag that determines either to apply or not wholesale pricing to the current cart item.
         * @param array   $cart_item             Cart item.
         * @param WC_Cart $cart_object           WC_Cart instance.
         * @param array   $user_wholesale_role   Current user wholesale roles.
         * @return array|boolean Array of error notices on if current cart item fails product requirement, boolean true if passed and should apply wholesale pricing.
         */
        public function filter_if_apply_wholesale_price_per_product_level($apply_wholesale_price, $cart_item, $cart_object, $user_wholesale_role, $wholesale_price)
        {

            global $wc_wholesale_prices_premium;

            if (!$this->filter_if_cart_item_is_included_on_cart_totals_computation(true, $cart_item, $user_wholesale_role)) {
                return false;
            }

            $notice                    = array();
            $product_id                = WWP_Helper_Functions::wwp_get_product_id($cart_item['data']);
            $active_currency           = get_woocommerce_currency();
            $wholesale_price           = WWP_Wholesale_Prices::get_product_wholesale_price_on_cart(WWP_Helper_Functions::wwp_get_product_id($cart_item['data']), $user_wholesale_role, $cart_item, $cart_object);
            $formatted_wholesale_price = $wc_wholesale_prices_premium->wwpp_wholesale_prices->get_product_shop_price_with_taxing_applied(wc_get_product($product_id), $wholesale_price, array('currency' => $active_currency), $user_wholesale_role);

            if (in_array(WWP_Helper_Functions::wwp_get_product_type($cart_item['data']), array('simple', 'bundle', 'composite'))) {

                $moq = get_post_meta($product_id, $user_wholesale_role[0] . '_wholesale_minimum_order_quantity', true);
                $moq = (is_numeric($moq)) ? (int) $moq : 0;

                if ($cart_item['quantity'] < $moq) {
                    $notice = array('type' => 'notice', 'message' => sprintf(__('<span class="wwpp-notice"></span>You did not meet the minimum order quantity <b>(%1$s items)</b> of the product <b>%2$s</b> to activate wholesale pricing <b>(%3$s)</b>. Please increase quantities to the cart to activate adjusted pricing.', 'woocommerce-wholesale-prices-premium'), $moq, $cart_item['data']->get_title(), $formatted_wholesale_price));
                } elseif ($cart_item['quantity'] != $moq && $moq > 0) {

                    $oqs = get_post_meta($product_id, $user_wholesale_role[0] . '_wholesale_order_quantity_step', true);
                    $oqs = (is_numeric($oqs)) ? (int) $oqs : 0;

                    if ($oqs) {

                        $excess_qty = $cart_item['quantity'] - $moq;

                        if ($excess_qty % $oqs !== 0) // Quantity not within the step multiplier
                        {
                            $notice = array('type' => 'notice', 'message' => sprintf(__('<span class="wwpp-notice"></span>You did not meet the correct order quantity, <b>minimum of %1$s</b> and <b>increments of %2$s</b> of the product <b>%3$s</b> to activate wholesale pricing <b>(%4$s)</b> . Please correct quantities to the cart to activate adjusted pricing.', 'woocommerce-wholesale-prices-premium'), $moq, $oqs, $cart_item['data']->get_title(), $formatted_wholesale_price));
                        }

                    }

                }

            } elseif (WWP_Helper_Functions::wwp_get_product_type($cart_item['data']) === 'variation') {

                // Process variable level wholesale minimum order quantity
                $variable_id    = WWP_Helper_Functions::wwp_get_parent_variable_id($cart_item['data']);
                $variation_id   = WWP_Helper_Functions::wwp_get_product_id($cart_item['data']);
                $variable_total = 0;

                // Get total items of a variable product in cart ( Total items of its variations )
                foreach ($cart_object->cart_contents as $cart_item_key => $ci) {
                    if (WWP_Helper_Functions::wwp_get_product_type($ci['data']) === 'variation' && WWP_Helper_Functions::wwp_get_parent_variable_id($ci['data']) == $variable_id) {
                        $variable_total += $ci['quantity'];
                    }
                }

                // Check variable product requirements
                $check_result = $this->_check_if_variable_product_requirement_is_meet($variable_id, $variation_id, $cart_item, $variable_total, $user_wholesale_role[0]);

                if (is_array($check_result)) {

                    // Construct variation attributes
                    $variable_attributes = "";

                    if (is_array($cart_item['variation']) && !empty($cart_item['variation'])) {

                        foreach ($cart_item['variation'] as $attribute => $attributeVal) {

                            $attribute = str_replace('attribute_', '', $attribute);

                            if (strpos($attribute, 'pa_') !== false) {

                                // Attribute based variable product attribute
                                $attribute = str_replace('pa_', '', $attribute);

                                $attributeVal = str_replace('-', ' ', $attributeVal);
                                $attributeVal = ucwords($attributeVal);

                            }

                            $attribute = str_replace('-', ' ', $attribute);
                            $attribute = ucwords($attribute);

                            if (!empty($variable_attributes)) {
                                $variable_attributes .= ", ";
                            }

                            $variable_attributes .= $attribute . ": " . $attributeVal;

                        }

                    }

                    if (!empty($variable_attributes)) {
                        $variable_attributes = "(" . $variable_attributes . ")";
                    }

                    switch ($check_result['fail_type']) {

                        case 'variable_level_moq':
                            $notice = array('type' => 'notice', 'message' => sprintf(__('<span class="wwpp-notice"></span>You did not meet the minimum order quantity <b>(%1$s items of any variation)</b> of the product <b>%2$s</b> to activate wholesale pricing <b>(%3$s)</b> for the variation <b>%4$s</b>. Please increase quantities to the cart to activate adjusted pricing.', 'woocommerce-wholesale-prices-premium'), $check_result['variable_level_moq'], $cart_item['data']->get_title(), $formatted_wholesale_price, $variable_attributes));
                            break;
                        case 'variable_level_oqs':
                            $notice = array('type' => 'notice', 'message' => sprintf(__('<span class="wwpp-notice"></span>You did not meet the correct order quantity, <b>minimum of %1$s</b> and <b>increments of %2$s</b> of the any combination of variations of the product <b>%3$s</b> to activate wholesale pricing <b>(%4$s)</b> for the variation <b>%5$s</b>. Please increase quantities to the cart to activate adjusted pricing.', 'woocommerce-wholesale-prices-premium'), $check_result['variable_level_moq'], $check_result['variable_level_oqs'], $cart_item['data']->get_title(), $formatted_wholesale_price, $variable_attributes));
                            break;
                        case 'variation_level_moq':
                            $notice = array('type' => 'notice', 'message' => sprintf(__('<span class="wwpp-notice"></span>You did not meet the minimum order quantity <b>(%1$s items)</b> of the product <b>%2$s</b> to activate wholesale pricing <b>(%3$s)</b>. Please increase quantities to the cart to activate adjusted pricing.', 'woocommerce-wholesale-prices-premium'), $check_result['variation_level_moq'], $cart_item['data']->get_title() . ' ' . $variable_attributes, $formatted_wholesale_price));
                            break;
                        case 'variation_level_oqs':
                            $notice = array('type' => 'notice', 'message' => sprintf(__('<span class="wwpp-notice"></span>You did not meet the correct order quantity, <b>minimum of %1$s</b> and <b>increments of %2$s</b> of the product <b>%3$s</b> to activate wholesale pricing <b>(%4$s)</b> . Please correct quantities to the cart to activate adjusted pricing.', 'woocommerce-wholesale-prices-premium'), $check_result['variation_level_moq'], $check_result['variation_level_oqs'], $cart_item['data']->get_title() . ' ' . $variable_attributes, $formatted_wholesale_price));
                            break;

                    }

                }

            }

            $notice = apply_filters('wwpp_filter_wholesale_price_per_product_basis_requirement_failure_notice', $notice, $cart_item, $cart_object, $user_wholesale_role);

            return !empty($notice) ? $notice : $apply_wholesale_price;

        }

        /**
         * Filter if apply wholesale price per cart level. Validate if cart level requirements are meet or not.
         *
         * * Important Note: This does not use the raw cart total, this calculate the cart total by using the wholesale price
         * * of each product on the cart. The idea is that so even after the cart is applied with wholesale price, it will
         * * still meet the minimum order price.
         *
         * * Important Note: We are retrieving the raw wholesale price, not wholesale price with applied tax. Just the raw
         * * wholesale price of the product.
         *
         * * Important Note: Minimum order price is purely based on product price. It does not include tax and shipping costs.
         * * Just the total product price on the cart using wholesale price.
         *
         * @since 1.15.0
         * @since 1.16.0 Support per wholesale user settings.
         * @since 1.16.4 Add compatibility with "Minimum sub-total amount" with Aelia currency switcher plugin
         * @access public
         *
         * @param boolean $apply_wholesale_price Boolean flag that determines either to apply or not wholesale pricing per cart level.
         * @param WC_Cart $cart_object           WC_Cart instance.
         * @param array   $user_wholesale_role   Current user wholesale roles.
         * @return array|boolean Array of error notices on if current cart item fails cart requirements, boolean true if passed and should apply wholesale pricing.
         */
        public function filter_if_apply_wholesale_price_cart_level($apply_wholesale_price, $cart_total, $cart_items, $cart_object, $user_wholesale_role)
        {

            $user_id                                = get_current_user_id();
            $minimum_cart_items                     = trim(get_option('wwpp_settings_minimum_order_quantity'));
            $minimum_cart_price                     = trim(get_option('wwpp_settings_minimum_order_price'));
            $minimum_requirements_conditional_logic = get_option('wwpp_settings_minimum_requirements_logic');
            $notices                                = array();
            $cart_total                             = $this->calculate_cart_totals($cart_total, $cart_object, $user_wholesale_role);
            $coupon_codes                           = WC()->cart->applied_coupons;
            $coupon_count                           = count($coupon_codes);
            $acfw_bogo                              = WC()->session->get('acfw_bogo_entries');
            $total_discount                         = 0;

            if (!empty($acfw_bogo)) {
                foreach ($acfw_bogo as $key => $data) {
                    foreach ($data as $data_key => $values) {
                        if ($values['type'] == 'deal') {
                            $total_discount += $values['discount'];
                        }
                    }
                }

                if ($cart_total > $total_discount && $total_discount > 0) {
                    $cart_total = $cart_total - $total_discount;
                }
            }

            // Check if there is an option that overrides wholesale price order requirement per role
            $override_per_wholesale_role = get_option('wwpp_settings_override_order_requirement_per_role', false);

            /**
             * Coupons when applied is not counted as product quantity and should not interfer if the minimum order
             * quantity is set. What we do here is to count all applied coupons in the cart then subtruct it to the
             * total items so we can have an actual product order quantity.
             */
            if ($coupon_count > 0 && $cart_items > $coupon_count) {
                $cart_items = ($cart_items - $coupon_count);
            }

            if ($override_per_wholesale_role === 'yes') {

                $per_wholesale_role_order_requirement = get_option(WWPP_OPTION_WHOLESALE_ROLE_ORDER_REQUIREMENT_MAPPING, array());
                if (!is_array($per_wholesale_role_order_requirement)) {
                    $per_wholesale_role_order_requirement = array();
                }

                if (!empty($user_wholesale_role) && array_key_exists($user_wholesale_role[0], $per_wholesale_role_order_requirement)) {

                    // Use minimum order quantity set for this current wholesale role
                    $minimum_cart_items                     = $per_wholesale_role_order_requirement[$user_wholesale_role[0]]['minimum_order_quantity'];
                    $minimum_cart_price                     = $per_wholesale_role_order_requirement[$user_wholesale_role[0]]['minimum_order_subtotal'];
                    $minimum_requirements_conditional_logic = $per_wholesale_role_order_requirement[$user_wholesale_role[0]]['minimum_order_logic'];

                }

            }

            $user_min_order_qty_applied   = false;
            $user_min_order_price_applied = false;

            // Check if min order qty is overridden per wholesale user
            if (get_user_meta($user_id, 'wwpp_override_min_order_qty', true) === 'yes') {

                $user_min_order_qty = get_user_meta($user_id, 'wwpp_min_order_qty', true);

                if (is_numeric($user_min_order_qty) || empty($user_min_order_qty)) {

                    $minimum_cart_items         = $user_min_order_qty;
                    $user_min_order_qty_applied = true;

                }

            }

            // Check if min order price is overridden per wholesale user
            if (get_user_meta($user_id, 'wwpp_override_min_order_price', true) === 'yes') {

                $user_min_order_price = get_user_meta($user_id, 'wwpp_min_order_price', true);

                if (is_numeric($user_min_order_price) || empty($user_min_order_price)) {

                    $minimum_cart_price           = $user_min_order_price;
                    $user_min_order_price_applied = true;

                }

            }

            // Check if min order logic is overridden per wholesale user
            if ($user_min_order_qty_applied && $user_min_order_price_applied) {

                $user_min_order_logic = get_user_meta($user_id, 'wwpp_min_order_logic', true);

                if (in_array($user_min_order_logic, array('and', 'or'))) {
                    $minimum_requirements_conditional_logic = $user_min_order_logic;
                }

            }

            /**
             * Make min order price requirement compatible with "Aelia Currency Switcher" plugin
             */
            if (WWP_ACS_Integration_Helper::aelia_currency_switcher_active()) {

                $active_currency    = get_woocommerce_currency();
                $shop_base_currency = get_option('woocommerce_currency');

                if ($active_currency != $shop_base_currency) {
                    $minimum_cart_price = WWP_ACS_Integration_Helper::convert($minimum_cart_price, $active_currency, $shop_base_currency);
                }

            }

            if (is_numeric($minimum_cart_items) && (!is_numeric($minimum_cart_price) || strcasecmp($minimum_cart_price, '') == 0 || ((float) $minimum_cart_price <= 0))) {

                $minimum_cart_items = (int) $minimum_cart_items;
                if ($cart_items < $minimum_cart_items) {
                    $notices[] = array('type' => 'notice', 'message' => sprintf(__('<span class="wwpp-notice"></span>You have not met the minimum order quantity of <b>(%1$s)</b> to activate adjusted pricing. Retail  prices will be shown below until the minimum order threshold is met.', 'woocommerce-wholesale-prices-premium'), $minimum_cart_items));
                }

            } elseif (is_numeric($minimum_cart_price) && (!is_numeric($minimum_cart_items) || strcasecmp($minimum_cart_items, '') == 0 || ((int) $minimum_cart_items <= 0))) {

                $minimum_cart_price = (float) $minimum_cart_price;
                if ($cart_total < $minimum_cart_price) {
                    $notices[] = array('type' => 'notice', 'message' => sprintf(__('<span class="wwpp-notice"></span>You have not met the minimum order subtotal of <b>(%1$s)</b> to activate adjusted pricing. Retail  prices will be shown below until the minimum order threshold is met. The cart subtotal calculated with wholesale prices is <b>%2$s</b>', 'woocommerce-wholesale-prices-premium'), WWP_Helper_Functions::wwp_formatted_price($minimum_cart_price), WWP_Helper_Functions::wwp_formatted_price($cart_total)));
                }

            } elseif (is_numeric($minimum_cart_price) && is_numeric($minimum_cart_items)) {

                if (strcasecmp($minimum_requirements_conditional_logic, 'and') == 0) {

                    if ($cart_items < $minimum_cart_items || $cart_total < $minimum_cart_price) {
                        $notices[] = array('type' => 'notice', 'message' => sprintf(__('<span class="wwpp-notice"></span>You have not met the minimum order quantity of <b>(%1$s)</b> and minimum order subtotal of <b>(%2$s)</b> to activate adjusted pricing. Retail prices will be shown below until the minimum order threshold is met. The cart subtotal calculated with wholesale prices is <b>%3$s</b>', 'woocommerce-wholesale-prices-premium'), $minimum_cart_items, WWP_Helper_Functions::wwp_formatted_price($minimum_cart_price), WWP_Helper_Functions::wwp_formatted_price($cart_total)));
                    }

                } else {

                    if ($cart_items < $minimum_cart_items && $cart_total < $minimum_cart_price) {
                        $notices[] = array('type' => 'notice', 'message' => sprintf(__('<span class="wwpp-notice"></span>You have not met the minimum order quantity of <b>(%1$s)</b> or minimum order subtotal of <b>(%2$s)</b> to activate adjusted pricing. Retail prices will be shown below until the minimum order threshold is met. The cart subtotal calculated with wholesale prices is <b>%3$s</b>', 'woocommerce-wholesale-prices-premium'), $minimum_cart_items, WWP_Helper_Functions::wwp_formatted_price($minimum_cart_price), WWP_Helper_Functions::wwp_formatted_price($cart_total)));
                    }

                }

            }

            $notices = apply_filters('wwpp_filter_wholesale_price_requirement_failure_notice', $notices, $minimum_cart_items, $minimum_cart_price, $cart_items, $cart_total, $cart_object, $user_wholesale_role);

            return !empty($notices) ? $notices : $apply_wholesale_price;

        }

        /**
         * Disable the purchasing capabilities of the current wholesale user if not all wholesale requirements are met.
         *
         * @since 1.14.0
         * @since 1.15.3 Add support for WC 3.2 checkout page changes.
         * @access public
         *
         * @param WC_Cart $cart_object         Cart object.
         * @param array   $user_wholesale_role Array of wholesale role keys for the current customer.
         */
        public function disable_purchasing_capabilities($cart_object, $user_wholesale_role)
        {

            if (!empty($user_wholesale_role)) {

                $all_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();
                $only_allow_wholesale_orders    = isset($all_registered_wholesale_roles[$user_wholesale_role[0]]['onlyAllowWholesalePurchases']) ? $all_registered_wholesale_roles[$user_wholesale_role[0]]['onlyAllowWholesalePurchases'] : 'no';

                if ($only_allow_wholesale_orders === 'yes' && (is_cart() || is_checkout())) {

                    add_filter('woocommerce_order_button_html', function ($fields) {

                        return __('<h4>Please adjust your cart to meet all of the wholesale requirements in order to proceed.</h4>', 'woocommerce-wholesale-prices-premium');

                    });

                    remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
                    remove_action('woocommerce_proceed_to_checkout', array($this, 'output_disable_purchase_notice'));
                    add_action('woocommerce_proceed_to_checkout', array($this, 'output_disable_purchase_notice'));

                }

            }

        }

        /**
         * Output disable purchase notice.
         *
         * @since 1.14.9
         * @access public
         */
        public function output_disable_purchase_notice()
        {

            _e('<h4>Please adjust your cart to meet all of the wholesale requirements in order to proceed.</h4>', 'woocommerce-wholesale-prices-premium');

        }

        /**
         * Properly calculate product prices using its wholesale price with taxing applied.
         *
         * @since 1.23.5
         * @since 1.27.1 Make ACFW coupon discount on override compatible
         * @access public
         *
         * @param int               $cart_total             Cart Totals from WWP
         * @param WC_Cart Object    $cart_object            Cart Object
         * @param array             $user_wholesale_role    Wholesale Role
         *
         * @return int
         */
        public function calculate_cart_totals($cart_total, $cart_object, $user_wholesale_role)
        {

            global $wc_wholesale_prices_premium;

            $cart_wholesale_price_total = 0;

            foreach ($cart_object->cart_contents as $cart_item_key => $cart_item) {

                $product_id      = WWP_Helper_Functions::wwp_get_product_id($cart_item['data']);
                $active_currency = get_woocommerce_currency();

                if (isset($cart_item['acfw_add_product_discount_type']) && isset($cart_item['acfw_add_product_discount_value'])) {

                    if ($cart_item['acfw_add_product_discount_type'] == 'override' && $cart_item['acfw_add_product_discount_value'] >= 0) {
                        $wholesale_price = $cart_item['acfw_add_product_discount_value'];
                    }

                } else {
                    $wholesale_price = WWP_Wholesale_Prices::get_product_wholesale_price_on_cart(WWP_Helper_Functions::wwp_get_product_id($cart_item['data']), $user_wholesale_role, $cart_item, $cart_object);
                }

                add_filter('wc_price', array($this, 'return_unformatted_price'), 10, 4);
                $unformatted_price = $wc_wholesale_prices_premium->wwpp_wholesale_prices->get_product_shop_price_with_taxing_applied(wc_get_product($product_id), $wholesale_price, array('currency' => $active_currency), $user_wholesale_role);

                if (!empty($unformatted_price)) {
                    $unformatted_price = $unformatted_price * $cart_item['quantity'];
                    $cart_wholesale_price_total += $unformatted_price;
                }

                remove_filter('wc_price', array($this, 'return_unformatted_price'), 10, 4);

            }

            return !empty($cart_wholesale_price_total) ? $cart_wholesale_price_total : $cart_total;

        }

        /**
         * This is wc_price filter. Instead of formatted price, return the unformatted so we can calculate the wholesale price totals properly.
         *
         * @since 1.23.5
         * @access public
         *
         * @param string    $return             Formatted Price
         * @param int       $price              Raw Price
         * @param array     $args               WC Price Args
         * @param int       $unformatted_price  Unformatted Price
         *
         * @return int
         */
        public function return_unformatted_price($return, $price, $args, $unformatted_price)
        {

            return $unformatted_price;

        }

        /*
        |--------------------------------------------------------------------------
        | Execute Model
        |--------------------------------------------------------------------------
         */

        /**
         * Execute model.
         *
         * @since 1.12.8
         * @access public
         */
        public function run()
        {

            // Add order minimum order quantity data on product wholesale price fields
            add_filter('wwp_filter_wholesale_price_html', array($this, 'display_minimum_wholesale_order_quantity'), 100, 7);
            add_filter('woocommerce_quantity_input_args', array($this, 'set_order_quantity_attribute_values'), 11, 2);
            add_filter('wwof_variation_quantity_input_args', array($this, 'set_order_quantity_attribute_values'), 10, 2);

            // Min max plugin compat
            if (WWP_Helper_Functions::is_plugin_active('woocommerce-min-max-quantities/woocommerce-min-max-quantities.php')) {

                add_filter('wc_min_max_quantity_minimum_allowed_quantity', array($this, 'mute_min_max_plugin_min_qty_rule'), 10, 4);
                add_filter('wc_min_max_quantity_group_of_quantity', array($this, 'mute_min_max_plugin_group_qty_rule'), 10, 4);
                add_filter('woocommerce_add_to_cart_validation', array($this, 'mute_min_max_plugin_add_product_to_cart_validation'), 9, 3);
                add_action('woocommerce_before_cart', array($this, 'mute_min_max_qty_field_mods_on_cart_page'), 99);

            }

            // Set min qty as qty field initial value ( variation )
            add_filter('woocommerce_available_variation', array($this, 'enforce_min_order_qty_requirement_on_qty_field'), 20, 3);

            // Check if current user meets the wholesale price requirement
            add_action('wwp_before_apply_product_wholesale_price_cart_loop', array($this, 'extract_bundle_and_composite_products'), 10, 2);
            add_filter('wwp_include_cart_item_on_cart_totals_computation', array($this, 'filter_if_cart_item_is_included_on_cart_totals_computation'), 10, 3);
            add_filter('wwp_apply_wholesale_price_per_product_level', array($this, 'filter_if_apply_wholesale_price_per_product_level'), 10, 5);
            add_filter('wwp_apply_wholesale_price_cart_level', array($this, 'filter_if_apply_wholesale_price_cart_level'), 10, 5);
            add_action('wwp_wholesale_requirements_not_passed', array($this, 'disable_purchasing_capabilities'), 10, 2);

        }

    }

}