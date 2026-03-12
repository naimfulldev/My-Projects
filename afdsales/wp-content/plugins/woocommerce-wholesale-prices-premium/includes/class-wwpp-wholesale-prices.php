<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

class WWPP_Wholesale_Prices
{

    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Property that holds the single main instance of WWPP_Wholesale_Prices.
     *
     * @since 1.12.8
     * @access private
     * @var WWPP_Wholesale_Prices
     */
    private static $_instance;

    /**
     * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
     *
     * @since 1.16.0
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
     * WWPP_Wholesale_Prices constructor.
     *
     * @since 1.16.0
     * @access public
     *
     * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Prices model.
     */
    public function __construct($dependencies = array())
    {

        $this->_wwpp_wholesale_roles = $dependencies['WWPP_Wholesale_Roles'];

    }

    /**
     * Ensure that only one instance of WWPP_Wholesale_Prices is loaded or can be loaded (Singleton Pattern).
     *
     * @since 1.12.8
     * @deprecated Deprecated on 1.16.0
     * @access public
     *
     * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Prices model.
     * @return WWPP_Wholesale_Prices
     */
    public static function getInstance($dependencies = array())
    {

        if (!self::$_instance instanceof self) {
            self::$_instance = new self($dependencies);
        }

        return self::$_instance;

    }

    /**
     * Ensure that only one instance of WWPP_Wholesale_Prices is loaded or can be loaded (Singleton Pattern).
     *
     * @since 1.16.0
     * @access public
     *
     * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Prices model.
     * @return WWPP_Wholesale_Prices
     */
    public static function instance($dependencies = array())
    {

        if (!self::$_instance instanceof self) {
            self::$_instance = new self($dependencies);
        }

        return self::$_instance;

    }

    /**
     * Get curent user wholesale role.
     *
     * @since 1.16.0
     * @access private
     *
     * @return string User role string or empty string.
     */
    private function _get_current_user_wholesale_role()
    {

        $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

        $wholesale_role = (is_array($user_wholesale_role) && !empty($user_wholesale_role)) ? $user_wholesale_role[0] : '';

        return apply_filters('wwpp_get_current_wholesale_role', $wholesale_role);

    }

    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Per Product Level Order Qty Wholesale Discount
    |-------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Display quantity based discount markup on single product pages.
     *
     * @since 1.6.0
     * @since 1.7.0 Add Aelia currency switcher plugin integration
     * @since 1.16.0
     * Renamed from 'displayOrderQuantityBasedWholesalePricing' to 'render_per_product_level_order_quantity_based_wholesale_discount_table_markup'.
     * Refactor codebase.
     * @since 1.19   If quantity based pricing is enabled via variable level then show the markup discount table else show the variation. (WWPP-592).
     * @access public
     * @see _print_wholesale_price_order_quantity_table
     *
     * @param string     $wholesale_price_html       Wholesale price html.
     * @param string     $price                      Active price html( non wholesale ).
     * @param WC_Product $product                    WC_Product object.
     * @param array      $user_wholesale_role        Array user wholesale roles.
     * @param string     $wholesale_price_title_text Wholesale price title text.
     * @param string     $raw_wholesale_price        Raw wholesale price.
     * @param string     $source                     Source of the wholesale price being applied.
     * @return string Filtered wholesale price html.
     */
    public function render_per_product_level_order_quantity_based_wholesale_discount_table_markup($wholesale_price_html, $price, $product, $user_wholesale_role, $wholesale_price_title_text, $raw_wholesale_price, $source)
    {

        // Only apply this to single product pages and proper ajax request
        // When a variable product have lots of variations, WC will not load variation data on variable product page load on front end
        // Instead it will load variations data as you select them on the variations select box
        // We need to support this too

        if (!empty($user_wholesale_role) &&
            ((get_option('wwpp_settings_hide_quantity_discount_table', false) !== 'yes' && (is_product() || (defined('DOING_AJAX') && DOING_AJAX)) && in_array(WWP_Helper_Functions::wwp_get_product_type($product), array('simple', 'composite', 'bundle', 'variation'))) ||
                apply_filters('render_order_quantity_based_wholesale_pricing', false))) {

            // condition check for WWOF
            if (apply_filters('wwof_hide_table_on_wwof_form', false)) {
                return $wholesale_price_html;
            }

            $product_id = WWP_Helper_Functions::wwp_get_product_id($product);

            // Make sure that wholesale price being applied is per product level
            if (!empty($raw_wholesale_price) && $source === 'per_product_level') {

                $enabled = 'no';

                if ($product->is_type('variation')) {
                    $enabled = get_post_meta($product->get_parent_id(), WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE, true);
                }

                // If the variable qty based discount is enabled we use its mapping else we use the per variation mapping
                if ($enabled == 'yes') {

                    $mapping = get_post_meta($product->get_parent_id(), WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING, true);

                } else {

                    $enabled = get_post_meta($product->get_id(), WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE, true);
                    $mapping = get_post_meta($product->get_id(), WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING, true);

                }

                if (!is_array($mapping)) {
                    $mapping = array();
                }

                // Table view
                $mapping_table_html = '';

                if ($enabled == 'yes' && !empty($mapping)) {
                    ob_start();

                    /*
                     * Get the base currency mapping. The base currency mapping well determine what wholesale
                     * role and range pairing a product has wholesale price with.
                     */
                    $base_currency_mapping = $this->_get_base_currency_mapping($mapping, $user_wholesale_role);

                    if (WWP_ACS_Integration_Helper::aelia_currency_switcher_active()) {

                        $base_currency   = WWP_ACS_Integration_Helper::get_product_base_currency($product_id);
                        $active_currency = get_woocommerce_currency();

                        // No point on doing anything if have no base currency mapping
                        if (!empty($base_currency_mapping)) {

                            if ($base_currency == $active_currency) {

                                /*
                                 * If active currency is equal to base currency, then we just need to pass
                                 * the base currency mapping.
                                 */
                                $this->_print_wholesale_price_order_quantity_table($raw_wholesale_price, $base_currency_mapping, array(), $mapping, $product, $user_wholesale_role, true, $base_currency, $active_currency);

                            } else {

                                $specific_currency_mapping = $this->_get_specific_currency_mapping($mapping, $user_wholesale_role, $active_currency, $base_currency_mapping);

                                $this->_print_wholesale_price_order_quantity_table($raw_wholesale_price, $base_currency_mapping, $specific_currency_mapping, $mapping, $product, $user_wholesale_role, false, $base_currency, $active_currency);

                            }

                        }

                    } else {

                        // Default without Aelia currency switcher plugin

                        if (!empty($base_currency_mapping)) {
                            $this->_print_wholesale_price_order_quantity_table($raw_wholesale_price, $base_currency_mapping, array(), $mapping, $product, $user_wholesale_role, true, get_woocommerce_currency(), get_woocommerce_currency());
                        }

                    }

                    $mapping_table_html = ob_get_clean();

                }

                $wholesale_price_html .= $mapping_table_html;

            }

        }

        return $wholesale_price_html;

    }

    /**
     * Print wholesale pricing per order quantity table.
     *
     * @since 1.7.0
     * @since 1.7.1 Apply taxing on the wholesale price on the per order quantity wholesale pricing table.
     * @since 1.16.0
     * Rename from '_printWholesalePricePerOrderQuantityTable' to '_print_wholesale_price_order_quantity_table'.
     * Refactor codebase.
     * @since 1.16.4 Bug fix not able to set percent discount for non base currency (WWPP-570).
     * @access private
     * @see render_per_product_level_order_quantity_based_wholesale_discount_table_markup
     *
     * @param $wholesalePrice
     * @param $baseCurrencyMapping
     * @param $specificCurrencyMapping
     * @param $mapping
     * @param $product
     * @param $userWholesaleRole
     * @param $isBaseCurrency
     * @param $baseCurrency
     * @param $activeCurrency
     */
    private function _print_wholesale_price_order_quantity_table($wholesale_price, $base_currency_mapping, $specific_currency_mapping, $mapping, $product, $user_wholesale_role, $is_base_currency, $base_currency, $active_currency)
    {

        $desc = WWP_Helper_Functions::wwp_get_product_type($product) === 'variation' ? __('Quantity based discounts available based on how many of this variation is in your cart.', 'woocommerce-wholesale-prices-premium') : __('Quantity based discounts available based on how many of this product is in your cart.', 'woocommerce-wholesale-prices-premium');

        $headers = apply_filters('wwpp_quantity_based_discount_headers', array(
            'qty'   => __('Qty', 'woocommerce-wholesale-prices-premium'),
            'price' => __('Price', 'woocommerce-wholesale-prices-premium'),
            'save'  => __('Save', 'woocommerce-wholesale-prices-premium'),
        ), 'per_product_level');

        // Description
        $qty_table = '<div class="qty-based-discount-table-description">';
        $qty_table .= '<p class="desc">' . apply_filters('wwpp_per_product_level_qty_discount_table_desc', $desc) . '</p>';
        $qty_table .= '</div>';

        // Qty Table
        $qty_table .= '<table class="order-quantity-based-wholesale-pricing-view table-view" data-wholesale_price="' . $wholesale_price . '" data-product_quantity_mapping="' . htmlspecialchars(json_encode($mapping), ENT_QUOTES, 'UTF-8') . '">';
        $qty_table .= '<thead>';
        $qty_table .= '<tr>';

        do_action('wwpp_action_before_wholesale_price_table_per_order_quantity_heading_view', $mapping, $product, $user_wholesale_role);

        // Headers
        foreach ($headers as $header) {
            $qty_table .= '<th>' . $header . '</th>';

        }

        do_action('wwpp_action_after_wholesale_price_table_per_order_quantity_heading_view', $mapping, $product, $user_wholesale_role);

        $qty_table .= '</tr>';
        $qty_table .= '</thead>';
        $qty_table .= '<tbody>';

        if (!$is_base_currency) {

            // Specific currency
            foreach ($base_currency_mapping as $base_map) {

                /**
                 * Even if this is a not a base currency, we will still rely on the base currency "RANGE".
                 * Because some range that are present on the base currency, may not be present in this current currency.
                 * But this current currency still has a wholesale price for that range, its wholesale price will be derived
                 * from base currency wholesale price by converting it to this current currency.
                 *
                 * Also if a wholesale price is set for this current currency range ( ex. 10 - 20 ) but that range
                 * is not present on the base currency mapping. We don't recognize this specific product on this range
                 * ( 10 - 20 ) as having wholesale price. User must set wholesale price on the base currency for the
                 * 10 - 20 range for this to be recognized as having a wholesale price.
                 */

                $qty  = $base_map['start_qty'];
                $save = '';

                if (!empty($base_map['end_qty'])) {
                    $qty .= ' - ' . $base_map['end_qty'];
                } else {
                    $qty .= '+';
                }

                $price = '';

                /**
                 * First check if a price is set for this wholesale role : range pair in the specific currency mapping.
                 * If wholesale price is present, then use it.
                 */
                foreach ($specific_currency_mapping as $specific_map) {

                    if ($specific_map[$active_currency . '_start_qty'] == $base_map['start_qty'] && $specific_map[$active_currency . '_end_qty'] == $base_map['end_qty']) {

                        if (isset($specific_map[$active_currency . '_price_type'])) {

                            if ($specific_map[$active_currency . '_price_type'] == 'fixed-price') {
                                $price = WWP_Helper_Functions::wwp_formatted_price($specific_map[$active_currency . '_wholesale_price'], array('currency' => $active_currency));
                            } elseif ($specific_map[$active_currency . '_price_type'] == 'percent-price') {

                                $price = $wholesale_price - (($specific_map[$active_currency . '_wholesale_price'] / 100) * $wholesale_price);

                                /**
                                 * No need to use
                                 * $price = WWP_Helper_Functions::wwp_formatted_price( $price  , array( 'currency' => $active_currency ) );
                                 * to convert the price because the $wholesale_price variable is already using the converted wholesale price
                                 * WWPP-558
                                 */

                            }

                        } else {
                            $price = WWP_Helper_Functions::wwp_formatted_price($specific_map[$active_currency . '_wholesale_price'], array('currency' => $active_currency));
                        }

                    }

                }

                /**
                 * Now if there is no mapping for this specific wholesale role : range pair inn the specific currency mapping,
                 * since this range is present on the base map mapping. We derive the price by converting the price set on the
                 * base currency mapping to this active currency.
                 */
                if (!$price) {

                    if (isset($base_map['price_type'])) {

                        if ($base_map['price_type'] == 'fixed-price') {

                            $map_wholesale_price = round($base_map['wholesale_price'], 2);
                            $map_wholesale_price = WWPP_Helper_Functions::woocs_exchange($map_wholesale_price);
                            $price               = WWP_ACS_Integration_Helper::convert($map_wholesale_price, $active_currency, $base_currency);
                            $wholesale_price_tax = $this->get_product_shop_price_with_taxing_applied($product, $map_wholesale_price, array('currency' => $active_currency), $user_wholesale_role, false); // Check if we apply or not apply tax to price
                            $save                = round(100 - (round($wholesale_price_tax, 2) / floatval($product->get_price()) * 100), 1) . '%';

                        } elseif ($base_map['price_type'] == 'percent-price') {

                            $price = $wholesale_price - (($base_map['wholesale_price'] / 100) * $wholesale_price);
                            $save  = $base_map['wholesale_price'] . '%';

                            /**
                             * No need to use
                             * $price = WWP_ACS_Integration_Helper::convert( $price , $active_currency , $base_currency );
                             * to convert the price because the $wholesale_price variable is already using the converted wholesale price
                             * WWPP-558
                             */

                        }

                    } else {
                        $price = WWP_ACS_Integration_Helper::convert($base_map['wholesale_price'], $active_currency, $base_currency);
                    }

                    $price = $this->get_product_shop_price_with_taxing_applied($product, $price, array('currency' => $active_currency), $user_wholesale_role);

                }

                $qty_table .= '<tr>';
                do_action('wwpp_action_before_wholesale_price_table_per_order_quantity_entry_view', $base_map, $product, $user_wholesale_role);

                if (isset($headers['qty'])) {
                    $qty_table .= '<td>' . $qty . '</td>';
                }
                if (isset($headers['price'])) {
                    $qty_table .= '<td>' . $price . '</td>';
                }
                if (isset($headers['save'])) {
                    $qty_table .= '<td>' . $save . '</td>';
                }

                do_action('wwpp_action_after_wholesale_price_table_per_order_quantity_entry_view', $base_map, $product, $user_wholesale_role);
                $qty_table .= '</tr>';

            }

        } else {

            /**
             * Base currency.
             * Also the default if Aelia currency switcher plugin isn't active.
             */
            foreach ($base_currency_mapping as $map) {

                $qty  = $map['start_qty'];
                $save = '';

                if (!empty($map['end_qty'])) {
                    $qty .= ' - ' . $map['end_qty'];
                } else {
                    $qty .= '+';
                }

                if (isset($map['price_type'])) {

                    // We only apply taxing on fixed price, we don't need taxing on percent price since the wholesale price is already taxed
                    if ($map['price_type'] == 'fixed-price') {

                        $map_wholesale_price = round($map['wholesale_price'], 2);
                        $map_wholesale_price = WWPP_Helper_Functions::woocs_exchange($map_wholesale_price);
                        $price               = $this->get_product_shop_price_with_taxing_applied($product, $map_wholesale_price, array('currency' => $base_currency), $user_wholesale_role);
                        $wholesale_price_tax = $this->get_product_shop_price_with_taxing_applied($product, $map_wholesale_price, array('currency' => $active_currency), $user_wholesale_role, false); // Check if we apply or not apply tax to price
                        $save                = round(100 - (round($wholesale_price_tax, 2) / floatval($wholesale_price) * 100), 2) . '%';

                    } elseif ($map['price_type'] == 'percent-price') {

                        $price = round($wholesale_price - (($map['wholesale_price'] / 100) * $wholesale_price), 2);
                        $price = WWP_Helper_Functions::wwp_formatted_price($price);
                        $save  = round($map['wholesale_price'], 2) . '%';

                    }

                } else {
                    $price = $this->get_product_shop_price_with_taxing_applied($product, $map['wholesale_price'], array('currency' => $base_currency), $user_wholesale_role);
                }

                $qty_table .= '<tr>';
                do_action('wwpp_action_before_wholesale_price_table_per_order_quantity_entry_view', $map, $product, $user_wholesale_role);
                if (isset($headers['qty'])) {
                    $qty_table .= '<td>' . $qty . '</td>';
                }

                if (isset($headers['price'])) {
                    $qty_table .= '<td>' . $price . '</td>';
                }

                if (isset($headers['save'])) {
                    $qty_table .= '<td>' . $save . '</td>';
                }

                do_action('wwpp_action_after_wholesale_price_table_per_order_quantity_entry_view', $map, $product, $user_wholesale_role);
                $qty_table .= '</tr>';
            }

        }

        $qty_table .= '</tbody>';

        $qty_table .= '</table>'; //.order-quantity-based-wholesale-pricing-view table-view

        echo apply_filters('wwpp_qty_based_table_product_level', $qty_table, $wholesale_price, $mapping, $product, $user_wholesale_role);

    }

    /**
     * Apply quantity based discount on products on cart.
     *
     * @since 1.6.0
     * @since 1.7.0 Add Aelia currency switcher plugin integration
     * @since 1.16.0
     * Rename from 'applyOrderQuantityBasedWholesalePricing' to 'apply_product_level_order_quantity_based_wholesale_pricing'.
     * Refactor codebase.
     *
     * @param array   $wholesale_price_arr Wholesale price array data.
     * @param int     $product_id          Product Id.
     * @param array   $user_wholesale_role Array of user wholesale role.
     * @param WC_Cart $cart_item           WC_Cart object.
     * @return array Filtered wholesale price array data.
     */
    public function apply_product_level_order_quantity_based_wholesale_pricing($wholesale_price_arr, $product_id, $user_wholesale_role, $cart_item)
    {

        // Quantity based discount depends on a wholesale price being set on the per product level
        // If none is set, then, quantity based discount will not be applied even if it is defined
        if (!empty($user_wholesale_role) && !empty($wholesale_price_arr['wholesale_price'])) {

            $product = wc_get_product($product_id);
            $enabled = 'no';

            if ($product->is_type('variation')) {
                $enabled = get_post_meta($product->get_parent_id(), WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE, true);
            }

            // If the variable qty based discount is enabled we use its mapping else we use the per variation mapping
            if ($enabled == 'yes') {

                $mapping = get_post_meta($product->get_parent_id(), WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING, true);

            } else {

                $enabled = get_post_meta($product_id, WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE, true);
                $mapping = get_post_meta($product_id, WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING, true);

            }

            if (!is_array($mapping)) {
                $mapping = array();
            }

            if ($enabled == 'yes' && !empty($mapping)) {

                /*
                 * Get the base currency mapping. The base currency mapping well determine what wholesale
                 * role and range pairing a product has wholesale price with.
                 */
                $base_currency_mapping = $this->_get_base_currency_mapping($mapping, $user_wholesale_role);

                if (WWP_ACS_Integration_Helper::aelia_currency_switcher_active()) {

                    $base_currency   = WWP_ACS_Integration_Helper::get_product_base_currency($product_id);
                    $active_currency = get_woocommerce_currency();

                    if ($base_currency == $active_currency) {

                        $wholesale_price_arr['wholesale_price'] = $this->_get_wholesale_price_from_mapping($wholesale_price_arr['wholesale_price'], $base_currency_mapping, array(), $cart_item, $base_currency, $active_currency, true);
                        $wholesale_price_arr['source']          = 'per_product_level_qty_based';

                    } else {

                        // Get specific currency mapping
                        $specific_currency_mapping = $this->_get_specific_currency_mapping($mapping, $user_wholesale_role, $active_currency, $base_currency_mapping);

                        $wholesale_price_arr['wholesale_price'] = $this->_get_wholesale_price_from_mapping($wholesale_price_arr['wholesale_price'], $base_currency_mapping, $specific_currency_mapping, $cart_item, $base_currency, $active_currency, false);
                        $wholesale_price_arr['source']          = 'per_product_level_qty_based';

                    }

                } else {

                    $wholesale_price_arr['wholesale_price'] = $this->_get_wholesale_price_from_mapping($wholesale_price_arr['wholesale_price'], $base_currency_mapping, array(), $cart_item, get_woocommerce_currency(), get_woocommerce_currency(), true);
                    $wholesale_price_arr['source']          = 'per_product_level_qty_based';

                }

            }

        }

        return $wholesale_price_arr;

    }

    /**
     * Get the wholesale price of a wholesale role for the appropriate range from the wholesale price per order
     * quantity mapping that is appropriate for the current items on the current wholesale user's cart.
     *
     * @since 1.7.0
     * @since 1.16.0
     * Renamed from '_getWholesalePriceFromMapping' to '_get_wholesale_price_from_mapping'.
     * Refactor codebase.
     * @since 1.19   If quantity based pricing is enabled via variable level then apply discount to all variation if the total reaches the quantity range else use variation mapping discount. (WWPP-592).
     *
     * @param string  $wholesale_price           Wholesale Price.
     * @param array   $base_currency_mapping     Base currency mapping.
     * @param array   $specific_currency_mapping Specific currency mapping.
     * @param array   $cart_item                 Cart item data.
     * @param string  $base_currency             Base currency.
     * @param string  $active_currency           Active currency.
     * @param boolean $is_base_currency          Is base currency.
     * @return string Filtered wholesale price.
     */
    private function _get_wholesale_price_from_mapping($wholesale_price, $base_currency_mapping, $specific_currency_mapping, $cart_item, $base_currency, $active_currency, $is_base_currency)
    {

        if (in_array(WWP_Helper_Functions::wwp_get_product_type($cart_item['data']), array('simple', 'variation'))) {
            $cart_item['quantity'] = $this->tally_product_qty_in_shopping_cart($cart_item);
        }

        if (!$is_base_currency) {

            foreach ($base_currency_mapping as $baseMap) {

                $price = "";

                /*
                 * First check if a price is set for this wholesale role : range pair in the specific currency mapping.
                 * If wholesale price is present, then use it.
                 */
                foreach ($specific_currency_mapping as $specificMap) {

                    if ($cart_item['quantity'] >= $specificMap[$active_currency . '_start_qty'] &&
                        (empty($specificMap[$active_currency . '_end_qty']) || $cart_item['quantity'] <= $specificMap[$active_currency . '_end_qty']) &&
                        $specificMap[$active_currency . '_wholesale_price'] != '') {

                        if (isset($specificMap[$active_currency . '_price_type'])) {

                            if ($specificMap[$active_currency . '_price_type'] == 'fixed-price') {
                                $price = $specificMap[$active_currency . '_wholesale_price'];
                            } elseif ($specificMap[$active_currency . '_price_type'] == 'percent-price') {
                                $price = round($wholesale_price - (($specificMap[$active_currency . '_wholesale_price'] / 100) * $wholesale_price), 2);
                            }

                        } else {
                            $price = $specificMap[$active_currency . '_wholesale_price'];
                        }

                    }

                }

                /*
                 * Now if there is no mapping for this specific wholesale role : range pair in the specific currency mapping,
                 * since this range is present on the base map mapping. We derive the price by converting the price set on the
                 * base currency mapping to this active currency.
                 */
                if (!$price) {

                    if ($cart_item['quantity'] >= $baseMap['start_qty'] &&
                        (empty($baseMap['end_qty']) || $cart_item['quantity'] <= $baseMap['end_qty']) &&
                        $baseMap['wholesale_price'] != '') {

                        if (isset($baseMap['price_type'])) {

                            if ($baseMap['price_type'] == 'fixed-price') {
                                $price = WWP_ACS_Integration_Helper::convert($baseMap['wholesale_price'], $active_currency, $base_currency);
                            } elseif ($baseMap['price_type'] == 'percent-price') {

                                $price = round($wholesale_price - (($baseMap['wholesale_price'] / 100) * $wholesale_price), 2);

                                /**
                                 * No need to use
                                 * $price = WWP_ACS_Integration_Helper::convert( $price , $active_currency , $base_currency );
                                 * to convert the price because the $wholesale_price variable is already using the converted wholesale price
                                 * WWPP-558
                                 */

                            }

                        } else {
                            $price = WWP_ACS_Integration_Helper::convert($baseMap['wholesale_price'], $active_currency, $base_currency);
                        }

                    }

                }

                if ($price) {

                    $wholesale_price = $price;
                    break;

                }

            }

        } else {

            foreach ($base_currency_mapping as $map) {

                if ($cart_item['quantity'] >= $map['start_qty'] &&
                    (empty($map['end_qty']) || $cart_item['quantity'] <= $map['end_qty']) &&
                    $map['wholesale_price'] != '') {

                    if (isset($map['price_type'])) {

                        if ($map['price_type'] == 'fixed-price') {
                            $wholesale_price = $map['wholesale_price'];
                        } elseif ($map['price_type'] == 'percent-price') {
                            $wholesale_price = round($wholesale_price - (($map['wholesale_price'] / 100) * $wholesale_price), 2);
                        }

                    } else {
                        $wholesale_price = $map['wholesale_price'];
                    }

                    break;

                }

            }

        }

        return $wholesale_price;

    }

    /**
     * Get the base currency mapping from the wholesale price per order quantity mapping.
     *
     * @since 1.7.0
     * @since 1.16.0
     * Renamed 'getBaseCurrencyMapping' to '_get_base_currency_mapping'.
     * Refactor codebase.
     *
     * @param array $mapping             Quantity discount mapping data.
     * @param array $user_wholesale_role Arry of user wholesale roles.
     * @return array Base currency mapping.
     */
    private function _get_base_currency_mapping($mapping, $user_wholesale_role)
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
     * @since 1.7.0
     * @since 1.16.0
     * Renamed from '_getSpecificCurrencyMapping' to '_get_specific_currency_mapping'.
     * Refactor codebase.
     *
     * @param array  $mapping               Quantity discount mapping data.
     * @param array  $user_wholesale_role   Arry of user wholesale roles.
     * @param string $active_currency       Active currency.
     * @param array  $base_currency_mapping Base currency mapping.
     * @return array Specific currency mapping.
     */
    private function _get_specific_currency_mapping($mapping, $user_wholesale_role, $active_currency, $base_currency_mapping)
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
     * This functions get's product variable price suffix, when '{price_including_tax}', '{price_excluding_tax}' tags are used in the 'Price display suffix'. This will not return any price computation. This fixes price suffix when apply in product variable if '{price_including_tax}', '{price_excluding_tax}' tags are used.
     *
     * @since 1.27.1
     * @access public
     * @param WC_Product    $product                  WC_Product object
     * @param array         $user_wholesale_role      User wholesale role
     * @param string        $wc_price_suffix          contains price suffix
     * @return string       Wholesale price suffix
     */
    public function get_wholesale_price_display_suffix_filter($wc_price_suffix, $product, $user_wholesale_role)
    {
        if (!empty($user_wholesale_role)) {

            // Get product wholesale price
            $price_arr = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3(WWP_Helper_Functions::wwp_get_product_id($product), $user_wholesale_role);

            // Get product wholesale raw price, if empty then assign produtcs regular price
            $price_base = !empty($price_arr['wholesale_price_raw']) ? $price_arr['wholesale_price_raw'] : $product->get_regular_price();

            // Get price suffix
            $suffix              = get_option('woocommerce_price_display_suffix');
            $price_suffix_option = get_option('wwpp_settings_override_price_suffix');
            $wc_price_suffix     = !empty($price_suffix_option) ? $price_suffix_option : $suffix;

            // Check if product type is variable
            if (WWP_Helper_Functions::wwp_get_product_type($product) === 'variable') {

                // Get variable product price display
                $product_price_display = get_option('wwpp_settings_variable_product_price_display');

                // Get variable product variations
                $variations                         = WWP_Helper_Functions::wwp_get_variable_product_variations($product);
                $min_price                          = '';
                $min_wholesale_price_without_taxing = '';
                $max_price                          = '';
                $max_wholesale_price_without_taxing = '';
                $price                              = '';

                foreach ($variations as $variation) {
                    if (!$variation['is_purchasable']) {continue;}

                    $curr_var_price = $variation['display_price'];
                    $price_arr      = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3($variation['variation_id'], $user_wholesale_role);

                    if (strcasecmp($price_arr['wholesale_price'], '') != 0) {

                        $curr_var_price = $price_arr['wholesale_price'];

                    }

                    if (strcasecmp($min_price, '') == 0 || $curr_var_price < $min_price) {

                        $min_price                          = $curr_var_price;
                        $min_wholesale_price_without_taxing = strcasecmp($price_arr['wholesale_price_with_no_tax'], '') != 0 ? $price_arr['wholesale_price_with_no_tax'] : '';

                    }

                    if (strcasecmp($max_price, '') == 0 || $curr_var_price > $max_price) {

                        $max_price                          = $curr_var_price;
                        $max_wholesale_price_without_taxing = strcasecmp($price_arr['wholesale_price_with_no_tax'], '') != 0 ? $price_arr['wholesale_price_with_no_tax'] : '';

                    }

                }

                // Check variable price display
                if ($product_price_display == 'minimum') {
                    $price = $min_price;
                } elseif ($product_price_display == 'maximum') {
                    $price = $max_price;
                } else {
                    $price = $max_price;
                }

                // Check if price suffix contain including tax tag {price_including_tax}
                if (strpos($wc_price_suffix, "{price_including_tax}") !== false) {

                    // Get formatted wholesale price with tax
                    $wholesale_price_incl_tax = WWP_Helper_Functions::wwp_formatted_price(WWP_Helper_Functions::wwp_get_price_including_tax($product, array('qty' => 1, 'price' => $price)));

                    // Replace {price_including_tax} tag with wholesale price with tax
                    $wc_price_suffix = str_replace("{price_including_tax}", $wholesale_price_incl_tax, $wc_price_suffix);
                }

                // Check if price suffix contain excluding tax tag {price_excluding_tax}
                if (strpos($wc_price_suffix, "{price_excluding_tax}") !== false) {

                    // Get formatted wholesale price without tax
                    $wholesale_price_excl_tax = WWP_Helper_Functions::wwp_formatted_price(WWP_Helper_Functions::wwp_get_price_excluding_tax($product, array('qty' => 1, 'price' => $price)));

                    // Replace {price_excluding_tax} tag with wholesale price without tax
                    $wc_price_suffix = str_replace("{price_excluding_tax}", $wholesale_price_excl_tax, $wc_price_suffix);
                }

                $wc_price_suffix = ' <small class="woocommerce-price-suffix wholesale-price-suffix">' . $wc_price_suffix . '</small>';

            } elseif (in_array(WWP_Helper_Functions::wwp_get_product_type($product), array('simple', 'variation'))) {

                // Check if price suffix contain including tax tag {price_including_tax}
                if (strpos($wc_price_suffix, "{price_including_tax}") !== false) {

                    // Get formatted wholesale price with tax
                    $wholesale_price_incl_tax = WWP_Helper_Functions::wwp_formatted_price(WWP_Helper_Functions::wwp_get_price_including_tax($product, array('qty' => 1, 'price' => $price_base)));

                    // Replace {price_including_tax} tag with wholesale price with tax
                    $wc_price_suffix = str_replace("{price_including_tax}", $wholesale_price_incl_tax, $wc_price_suffix);
                }

                // Check if price suffix contain excluding tax tag {price_excluding_tax}
                if (strpos($wc_price_suffix, "{price_excluding_tax}") !== false) {

                    // Get formatted wholesale price without tax
                    $wholesale_price_excl_tax = WWP_Helper_Functions::wwp_formatted_price(WWP_Helper_Functions::wwp_get_price_excluding_tax($product, array('qty' => 1, 'price' => $price_base)));

                    // Replace {price_excluding_tax} tag with wholesale price without tax
                    $wc_price_suffix = str_replace("{price_excluding_tax}", $wholesale_price_excl_tax, $wc_price_suffix);
                }

                $wc_price_suffix = ' <small class="woocommerce-price-suffix wholesale-price-suffix">' . $wc_price_suffix . '</small>';
            }
        }

        return $wc_price_suffix;

    }

    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Apply wholesale prices on shop and cart for custom product types
    |-------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Filter callback that alters the product price, it embeds the wholesale price of a product for a wholesale user ( Custom product types ).
     *
     * @since 1.8.0 Partial support for composite product.
     * @since 1.9.0 Partial support for bundle product.
     * @since 1.16.0
     * Renamed from 'wholesalePriceHTMLFilter' to 'custom_product_type_wholesale_price_html_filter'.
     * Refactor codebase.
     * Supports new wholesale price model.
     * @access public
     *
     * @param string     $price   Product price.
     * @param WC_Product $product WC_Product instance.
     * @return Filtered product price.
     */
    public function custom_product_type_wholesale_price_html_filter($price, $product)
    {

        $user_wholesale_role = $this->_get_current_user_wholesale_role();

        if (!empty($user_wholesale_role) && !empty($price)) {

            $raw_wholesale_price = '';
            $wholesale_price     = '';
            $source              = '';

            if (in_array(WWP_Helper_Functions::wwp_get_product_type($product), array('composite', 'bundle'))) {

                $price_arr           = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3(WWP_Helper_Functions::wwp_get_product_id($product), array($user_wholesale_role));
                $raw_wholesale_price = $price_arr['wholesale_price'];
                $source              = $price_arr['source'];

                if (strcasecmp($raw_wholesale_price, '') != 0) {
                    $wholesale_price = WWP_Helper_Functions::wwp_formatted_price($raw_wholesale_price) . WWP_Wholesale_Prices::get_wholesale_price_suffix($product, $user_wholesale_role, $price_arr['wholesale_price_with_no_tax']);
                }

            }

            if (strcasecmp($wholesale_price, '') != 0) {

                $wholesale_price_html = apply_filters('wwp_product_original_price', '<del class="original-computed-price">' . $price . '</del>', $wholesale_price, $price, $product, array($user_wholesale_role));

                $wholesale_price_title_text = __('Wholesale Price:', 'woocommerce-wholesale-prices-premium');
                $wholesale_price_title_text = apply_filters('wwp_filter_wholesale_price_title_text', $wholesale_price_title_text);

                $wholesale_price_html .= '<span style="display: block;" class="wholesale_price_container">
                                            <span class="wholesale_price_title">' . $wholesale_price_title_text . '</span>
                                            <ins>' . $wholesale_price . '</ins>
                                        </span>';

                return apply_filters('wwp_filter_wholesale_price_html', $wholesale_price_html, $price, $product, array($user_wholesale_role), $wholesale_price_title_text, $raw_wholesale_price, $source);

            }

        }

        return $price;

    }

    /**
     * Apply wholesale price upon adding product to cart ( Custom Product Types ).
     *
     * @since 1.8.0
     * @since 1.15.0 Use 'get_product_wholesale_price_on_cart' function of class WWP_Wholesale_Prices.
     * @since 1.16.0
     * Renamed from 'applyCustomProductTypeWholesalePrice'  to 'apply_custom_product_type_wholesale_price'.
     * Refactor codebase.
     * @access public
     *
     * @param string $wholesale_price Wholesale price.
     * @param array  $cat_item        Cart item data.
     * @param array  $user_wholesale_role Array of user wholesale role.
     * @return string Filtered wholesale price.
     */
    public function apply_custom_product_type_wholesale_price($wholesale_price, $cart_item, $user_wholesale_role, $cart_object)
    {

        if (in_array(WWP_Helper_Functions::wwp_get_product_type($cart_item['data']), array('composite', 'bundle'))) {
            $wholesale_price = WWP_Wholesale_Prices::get_product_wholesale_price_on_cart(WWP_Helper_Functions::wwp_get_product_id($cart_item['data']), $user_wholesale_role, $cart_item, $cart_object);
        }

        return $wholesale_price;

    }

    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Mesc wholesale price related operations
    |-------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Override the price suffix for wholesale users only.
     *
     * @since 1.24.5    Refactor code. Codes are transferred to WWP. By default if WC price suffix is used it will be used in the wholesale price as well.
     *                  If "Wholesale Price Suffix" option is set then override wholesale price suffix.
     * @access public
     *
     * @param string     $suffix        Price display suffix.
     * @return string    Wholesale Price Suffix.
     */
    public function override_wholesale_price_suffix($suffix)
    {

        $price_suffix_option = get_option('wwpp_settings_override_price_suffix');

        if (!empty($price_suffix_option)) {
            return $price_suffix_option;
        }

        return $suffix;

    }

    /**
     * Override the price suffix for regular prices viewed by wholesale customers.
     *
     * @since 1.14.7
     * @since 1.16.0
     * Renamed from 'overrideRegularPriceSuffixForWholesaleRoles' to 'override_regular_price_suffix_for_wholesale_roles'.
     * Refactor codebase.
     * Add support for '{price_including_tax}' and '{price_excluding_tax}' placeholders.
     * @access public
     *
     * @param string     $price_suffix_html   Price suffix markup.
     * @param WC_Product $product             WC Product instance.
     * @param string     $price               Product price.
     * @param int        $qty                 Quantity.
     * @return string Filtered price suffix markup.
     */
    public function override_regular_price_suffix_for_wholesale_roles($price_suffix_html, $product, $price = null, $qty = 1)
    {

        if (empty($price_suffix_html)) {
            return $price_suffix_html;
        }
        // Called on a variable product price range

        if (is_null($price)) {
            $price = $product->get_price();
        } elseif ($price === 'range' && is_object($product) && $product->is_type('bundle')) {
            // bundled product price
            $price = $product->get_bundle_price();
        }

        $user_wholesale_role = $this->_get_current_user_wholesale_role();

        if (!empty($user_wholesale_role)) {

            $price_suffix_option = get_option('wwpp_settings_override_price_suffix_regular_price');
            if (empty($price_suffix_option)) {
                $price_suffix_option = get_option('woocommerce_price_display_suffix');
            }

            $wholesale_suffix_for_regular_price = $price_suffix_option;
            $has_match                          = false;

            if (strpos($wholesale_suffix_for_regular_price, "{price_including_tax}") !== false) {

                $product_price_incl_tax             = WWP_Helper_Functions::wwp_formatted_price(WWP_Helper_Functions::wwp_get_price_including_tax($product, array('qty' => 1, 'price' => $price)));
                $wholesale_suffix_for_regular_price = str_replace("{price_including_tax}", $product_price_incl_tax, $wholesale_suffix_for_regular_price);
                $has_match                          = true;

            }

            if (strpos($wholesale_suffix_for_regular_price, '{price_excluding_tax}') !== false) {

                $product_price_excl_tax             = WWP_Helper_Functions::wwp_formatted_price(WWP_Helper_Functions::wwp_get_price_excluding_tax($product, array('qty' => 1, 'price' => $price)));
                $wholesale_suffix_for_regular_price = str_replace("{price_excluding_tax}", $product_price_excl_tax, $wholesale_suffix_for_regular_price);
                $has_match                          = true;

            }

            // We check if the product is bundle then we set wholesale price suffix if override
            if ($product->is_type('bundle')) {

                $wholesale_price_suffix = get_option('wwpp_settings_override_price_suffix');
                if (!empty($wholesale_price_suffix)) {
                    return ' <small class="woocommerce-price-suffix wholesale-user-regular-price-suffix">' . $wholesale_price_suffix . '</small>';
                }

            }

            return $has_match ? ' <small class="woocommerce-price-suffix wholesale-user-regular-price-suffix">' . $wholesale_suffix_for_regular_price . '</small>' : ' <small class="woocommerce-price-suffix">' . $price_suffix_option . '</small>';

        }

        return $price_suffix_html;

    }

    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Helper Functions
    |-------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Get the price of a product on shop pages with taxing applied (Meaning either including or excluding tax
     * depending on the settings of the shop).
     *
     * @since 1.7.1
     * @since 1.16.0 Renamed from 'getProductShopPriceWithTaxingApplied' to 'get_product_shop_price_with_taxing_applied'.
     * @access public
     *
     * @param WC_Product        $product        The Product Object
     * @param string            $price          The product price
     * @param array             $wc_price_arg   Price args
     * @param bool              $formatted      Whether to return formatted price or plain price. Default is formatted price.
     * @return string
     */
    public function get_product_shop_price_with_taxing_applied($product, $price, $wc_price_arg = array(), $user_wholesale_role = "", $formatted = true)
    {

        if (get_option('woocommerce_calc_taxes', false) === 'yes') {

            $woocommerce_tax_display_shop = get_option('woocommerce_tax_display_shop', false); // (WooCommerce) Display Prices in the Shop
            $wholesale_tax_display_shop   = get_option('wwpp_settings_incl_excl_tax_on_wholesale_price', false); // (Wholesale) Display Prices in the Shop
            $tax_exempted                 = !empty($user_wholesale_role) ? WWPP_Helper_Functions::is_user_wwpp_tax_exempted(get_current_user_id(), $user_wholesale_role[0]) : '';

            if ($tax_exempted === 'yes') {

                // Wholesale user is tax exempted so no matter what, the user will always see tax exempted prices
                $filtered_price = WWP_Helper_Functions::wwp_get_price_excluding_tax($product, array('qty' => 1, 'price' => $price));

            } else {

                if ($wholesale_tax_display_shop === 'incl') {
                    $filtered_price = WWP_Helper_Functions::wwp_get_price_including_tax($product, array('qty' => 1, 'price' => $price));
                } elseif ($wholesale_tax_display_shop === 'excl') {
                    $filtered_price = WWP_Helper_Functions::wwp_get_price_excluding_tax($product, array('qty' => 1, 'price' => $price));
                } elseif (empty($wholesale_tax_display_shop)) {

                    if ($woocommerce_tax_display_shop === 'incl') {
                        $filtered_price = WWP_Helper_Functions::wwp_get_price_including_tax($product, array('qty' => 1, 'price' => $price));
                    } else {
                        $filtered_price = WWP_Helper_Functions::wwp_get_price_excluding_tax($product, array('qty' => 1, 'price' => $price));
                    }

                }

            }

            if ($formatted) {
                $filtered_price = WWP_Helper_Functions::wwp_formatted_price($filtered_price, $wc_price_arg);
            }
            return apply_filters('wwpp_filter_product_shop_price_with_taxing_applied', $filtered_price, $price, $product);

        }

        if ($formatted) {
            $price = WWP_Helper_Functions::wwp_formatted_price($price, $wc_price_arg);
        }

        return $price;

    }

    /**
     * Tally all same products that has different prices. Example of this is product addons separate the product when added to cart because of its different addon price types.
     * We need to do this for wholesale quantity discount options we have.
     *
     * @since 1.19
     * @since 1.20 Added compatibility to product addons. Rename function from per_variable_product_variation_total_shopping_cart to tally_product_qty_in_shopping_cart.
     * @access public
     *
     * @param array $cart_item
     * @return int
     */
    public function tally_product_qty_in_shopping_cart($cart_item)
    {

        global $woocommerce;

        $parent_qty_disc_enabled    = get_post_meta($cart_item['product_id'], WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE, true);
        $variation_qty_disc_enabled = isset($cart_item['variation_id']) ? get_post_meta($cart_item['variation_id'], WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE, true) : '';
        $cart_items                 = $woocommerce->cart->get_cart();
        $qty_total                  = 0;

        foreach ($cart_items as $item) {

            if ($parent_qty_disc_enabled == 'yes' && $item['product_id'] == $cart_item['product_id']) {

                // Modify quantity total to count all variations under the variable product or same product that is separated by product addon that has different price types.
                $qty_total += $item['quantity'];

            } else if ($variation_qty_disc_enabled == 'yes' && $item['variation_id'] == $cart_item['variation_id']) {

                // If qty discount is set per variations, we need to tally each quantity or if addons are added to cart that has different price types.
                $qty_total += $item['quantity'];

            }

        }

        return empty($qty_total) ? $cart_item['quantity'] : $qty_total;

    }

    /**
     * Always use regular price option for variable price range
     *
     * @since 1.24.5
     * @access public
     *
     * @param string $price
     * @param WC_Product $product
     * @return string
     */
    public function always_use_regular_price_option_for_variable_product($price, $product)
    {

        $user_wholesale_role = $this->_get_current_user_wholesale_role();
        $use_regular_price   = get_option('wwpp_settings_explicitly_use_product_regular_price_on_discount_calc');

        if (!empty($user_wholesale_role) && $use_regular_price === 'yes' && get_post_meta($product->get_id(), $user_wholesale_role[0] . '_have_wholesale_price', true) == 'yes') {

            $prices        = $product->get_variation_prices(true);
            $min_reg_price = current($prices['regular_price']);
            $max_reg_price = end($prices['regular_price']);

            if ($min_reg_price !== $max_reg_price) {
                $price = wc_format_price_range($min_reg_price, $max_reg_price);
            } else {
                $price = wc_price($min_reg_price);
            }

            return apply_filters('always_use_regular_price_option_for_variable_product', $price, $product);
        }

        return apply_filters('wwpp_woocommerce_variable_price_html', $price, $product);

    }

    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Execute model.
    |-------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Execute model.
     *
     * @since 1.16.0
     * @access public
     */
    public function run()
    {

        // Per Product Level Order Qty Wholesale Discount
        add_filter('wwp_filter_wholesale_price_html', array($this, 'render_per_product_level_order_quantity_based_wholesale_discount_table_markup'), 10, 7);
        add_filter('wwp_filter_wholesale_price_cart', array($this, 'apply_product_level_order_quantity_based_wholesale_pricing'), 10, 4);

        // Apply wholesale prices on shop and cart for custom product types
        add_filter('woocommerce_get_price_html', array($this, 'custom_product_type_wholesale_price_html_filter'), 10, 2);
        add_filter('wwp_filter_get_custom_product_type_wholesale_price', array($this, 'apply_custom_product_type_wholesale_price'), 10, 4);

        // Apply filters to override the default wholesale price suffix if the Override Price Suffix in the settings is set
        add_filter('wwp_wholesale_price_suffix', array($this, 'override_wholesale_price_suffix'), 10, 1);
        add_filter('woocommerce_get_price_suffix', array($this, 'override_regular_price_suffix_for_wholesale_roles'), 10, 4);

        // Always use regular price option for variable price range
        add_filter('woocommerce_variable_price_html', array($this, 'always_use_regular_price_option_for_variable_product'), 10, 2);

        // Apply filter for product variables on price suffix
        add_filter('wwp_filter_wholesale_price_display_suffix', array($this, 'get_wholesale_price_display_suffix_filter'), 10, 3);

    }

}