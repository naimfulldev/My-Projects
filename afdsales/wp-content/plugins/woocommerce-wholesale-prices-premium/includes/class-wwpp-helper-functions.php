<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWPP_Helper_Functions')) {

    /**
     * Model that house various generic plugin helper functions.
     *
     * @since 1.12.8
     */
    final class WWPP_Helper_Functions
    {

        /**
         * Check if specific user is wwpp tax exempted.
         *
         * @since 1.16.0
         * @access public
         *
         * @param string $user_wholesale_role User wholesale role.
         * @param int    $user_id             User id.
         * @return boolean True if wwpp tax exempted, False otherwise.
         */
        public static function is_user_wwpp_tax_exempted($user_id, $user_wholesale_role)
        {

            $wwpp_tax_exempted = get_option('wwpp_settings_tax_exempt_wholesale_users');

            $wholesale_role_tax_option_mapping = get_option(WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING, array());
            if (!is_array($wholesale_role_tax_option_mapping)) {
                $wholesale_role_tax_option_mapping = array();
            }

            if (array_key_exists($user_wholesale_role, $wholesale_role_tax_option_mapping)) {
                $wwpp_tax_exempted = $wholesale_role_tax_option_mapping[$user_wholesale_role]['tax_exempted'];
            }

            $user_tax_exempted = get_user_meta($user_id, 'wwpp_tax_exemption', true);
            if ($user_tax_exempted !== 'global' && in_array($user_tax_exempted, array('yes', 'no'))) {
                $wwpp_tax_exempted = $user_tax_exempted;
            }

            return $wwpp_tax_exempted;

        }

        /**
         * Filter a given price to make sure it is a valid price value if
         * WC currency options is set other than the default.
         * Defaults are, thousand separator is comma, decimal separator is a dot.
         *
         * @since 1.16.7
         * @access public
         *
         * @param string $price Price.
         * @return string Filtered price.
         */
        public static function filter_price_for_custom_wc_currency_options($price)
        {

            $thousand_sep = get_option('woocommerce_price_thousand_sep');
            $decimal_sep  = get_option('woocommerce_price_decimal_sep');

            if ($thousand_sep) {
                $price = str_replace($thousand_sep, '', $price);
            }

            if ($decimal_sep) {
                $price = str_replace($decimal_sep, '.', $price);
            }

            return $price;

        }

        /**
         * Check if the current user have override per user wholesale discount.
         *
         * @since 1.23.4
         * @access public
         *
         * @param array $user_wholesale_role   Wholesale Role.
         *
         * @return bool
         */
        public static function _wholesale_user_have_override_per_user_discount($user_wholesale_role)
        {

            $user_id = apply_filters('wwpp_get_current_user_id', get_current_user_id());

            if (!empty($user_id) && get_user_meta($user_id, 'wwpp_override_wholesale_discount', true) == 'yes') {

                $wholesale_role_discount = get_user_meta($user_id, 'wwpp_wholesale_discount', true);

                // Check first if Per User wholesale discount is set
                if (!empty($wholesale_role_discount) && is_numeric($wholesale_role_discount)) {
                    return true;
                }

            }

            return false;

        }

        /**
         * Check if the current wholesale user have general discount set.
         *
         * @since 1.23.4
         * @access public
         *
         * @param string $user_wholesale_role   Wholesale Role.
         *
         * @return bool
         */
        public static function _wholesale_user_have_general_role_discount($user_wholesale_role)
        {

            global $wc_wholesale_prices_premium;

            $user_wholesale_discount = $wc_wholesale_prices_premium->wwpp_wholesale_price_wholesale_role->get_user_wholesale_role_level_discount(get_current_user_id(), $user_wholesale_role);

            return !empty($user_wholesale_discount['discount']) ? true : false;

        }

        /**
         * Show the correct pricing when woocommerce multilingual is enabled.
         * Show correct discount when set per product, per category, general and override per user.
         *
         * @since 1.23.5
         * @access public
         *
         * @param int       $price     Product price for current currency.
         * @param object    $product   WC Product Object.
         *
         * @return int
         */
        public static function get_product_default_currency_price($price, $product)
        {

            if (WWP_Helper_Functions::is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')) {

                global $woocommerce_wpml, $woocommerce;

                if ($woocommerce_wpml->settings['enable_multi_currency'] != WCML_MULTI_CURRENCIES_INDEPENDENT) {
                    return $price;
                }

                if ($woocommerce->session != null) {

                    $product_id       = WWP_Helper_Functions::wwp_get_product_id($product);
                    $default_currency = wcml_get_woocommerce_currency_option();
                    $current_currency = $woocommerce_wpml->multi_currency->get_client_currency();

                    if (!empty($current_currency) && !empty($default_currency)) {

                        if ($current_currency != $default_currency) {

                            $helper = new WWPP_Helper_Functions();

                            add_filter('woocommerce_product_get_price', array($helper, 'get_regular_price'), 10, 2);
                            add_filter('woocommerce_product_variation_get_price', array($helper, 'get_regular_price'), 10, 2);
                            $price = apply_filters('wcml_product_price_by_currency', $product_id, $default_currency);
                            remove_filter('woocommerce_product_variation_get_price', array($helper, 'get_regular_price'), 10, 2);
                            remove_filter('woocommerce_product_get_price', array($helper, 'get_regular_price'), 10, 2);

                        }

                    }

                }

            }

            return $price;

        }

        /**
         * WCML uses get_price reason why Use Regular Price feature is not working.
         * When filter is used grab the price from the regular instead of the sale price.
         *
         * @since 1.23.5
         * @access public
         *
         * @param int       $price     Product price.
         * @param object    $product   WC Product Object.
         *
         * @return int
         */
        public function get_regular_price($price, $product)
        {

            $use_regular_price = get_option('wwpp_settings_explicitly_use_product_regular_price_on_discount_calc');

            return $use_regular_price == 'yes' ? $product->get_regular_price() : $price;

        }

        /**
         * Check if WWOF is active
         *
         * @since 1.24
         * @access public
         *
         * @return bool
         */
        public static function is_wwof_active()
        {

            return WWP_Helper_Functions::is_plugin_active('woocommerce-wholesale-order-form/woocommerce-wholesale-order-form.bootstrap.php') ? true : false;

        }

        /**
         * Check if WWLC is active
         *
         * @since 1.24
         * @access public
         *
         * @return bool
         */
        public static function is_wwlc_active()
        {

            return WWP_Helper_Functions::is_plugin_active('woocommerce-wholesale-lead-capture/woocommerce-wholesale-lead-capture.bootstrap.php') ? true : false;

        }

        /**
         * WOOCS Compatibility. Calculate prices based on the selected currency.
         *
         * @since 1.26.2
         * @access public
         *
         * @param int       $price      Product Price.
         * @access public
         * @return int
         */
        public static function woocs_exchange($price)
        {

            global $WOOCS;

            return $WOOCS ? $WOOCS->woocs_exchange_value($price) : $price;

        }

        /**
         * Check if the specific product is restricted in the category level.
         *
         * @since 1.27
         * @access public
         *
         * @param int       $product_id         The product id.
         * @param string    $wholesale_role     The wholesale role.
         * @access public
         * @return bool
         */
        public static function is_product_restricted_in_category($product_id, $wholesale_role)
        {
            global $post;

            $product_cat_terms                 = get_the_terms($product_id, 'product_cat');
            $product_cat_wholesale_role_filter = get_option(WWPP_OPTION_PRODUCT_CAT_WHOLESALE_ROLE_FILTER);
            $has_blocked_cat                   = false;

            // Wholesale role product category filter
            if (!empty($product_cat_terms) && !empty($product_cat_wholesale_role_filter)) {

                $product_cat_term_ids = array();
                foreach ($product_cat_terms as $pct) {
                    $product_cat_term_ids[] = $pct->term_id;
                }

                if (!empty($wholesale_role)) {

                    foreach ($product_cat_term_ids as $t_id) {

                        if (array_key_exists($t_id, $product_cat_wholesale_role_filter) && !in_array($wholesale_role, $product_cat_wholesale_role_filter[$t_id])) {

                            $has_blocked_cat = true;
                            break;

                        }

                    }

                } else {

                    $filtered_cat_term_ids = array_keys($product_cat_wholesale_role_filter);
                    $blocked_cat_ids       = array_intersect($product_cat_term_ids, $filtered_cat_term_ids);

                    if (!empty($blocked_cat_ids)) {
                        $has_blocked_cat = true;
                    }

                }

            }

            return $has_blocked_cat;

        }

    }

}
