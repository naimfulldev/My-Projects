<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WWP_Wholesale_Prices_Non_Wholesales
{

    /** ===============================================================================================================
     *  Class Properties
     *===============================================================================================================*/

    /**
     * Property that holds single main instance of WWP_Wholesale_Prices_Non_Wholesales
     *
     * @since 1.15.0
     * @access private
     * @var WWP_Wholesale_Prices_Non_Wholesales
     */
    private static $_instance;

    /**
     * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
     *
     * @since 1.15.0
     * @access private
     * @var WWP_Wholesale_Roles
     */
    private $_wwp_wholesale_roles;

    /** ===============================================================================================================
     *  Class Methods
     *===============================================================================================================*/

    /**
     * WWP_Wholesale_Prices_Non_Wholesales constructor.
     *
     * @since 1.3.0
     * @access public
     *
     * @param array $dependencies Array of instance objects of all dependencies of WWP_Wholesale_Prices_Non_Wholesales model.
     */
    public function __construct($dependencies = array())
    {
        if (isset($dependencies['WWP_Wholesale_Roles'])) {
            $this->_wwp_wholesale_roles = $dependencies['WWP_Wholesale_Roles'];
        }
    }

    /**
     * Ensure that only one instance of WWP_Wholesale_Prices_Non_Wholesales is loaded (singleton pattern)
     *
     * @since 1.15.0
     * @access public
     * @param array $dependencies
     * @return WWP_Wholesale_Prices_Non_Wholesales
     */
    public static function instance($dependencies)
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($dependencies);
        }

        return self::$_instance;
    }

    /**
     * This function is responsible for the prices of wholesale roles if each products, this is triggered by "Click to See Wholesale Prices"
     *
     * @since 1.15.0
     * @since 1.15.1 removing function of getting ajax request, we dont need it anymore, since data is now encoded using base64 utf8 and added to html data attribute for fetching later on in js script for faster and better user experience.
     * - rename function from get_product_wholesale_prices_ajax to get_product_wholesale_prices
     *
     * @access public
     * @param {*} $product_id
     * @return string html
     */
    public static function get_product_wholesale_prices($product_id)
    {
        global $wc_wholesale_prices;

        $return_wholesale_price = '';
        $product                = wc_get_product($product_id);
        $wholesale_role_options = array();
        $wholesale_roles        = $wc_wholesale_prices->wwp_wholesale_roles->getAllRegisteredWholesaleRoles();

        if (WWP_Helper_Functions::is_wwpp_active()) {
            $wholesale_role_options = get_option('wwp_wholesale_role_select_chosen');
        } else {
            $wholesale_role_options = array_keys($wholesale_roles);
        }

        if (!empty($wholesale_roles)) {
            $price_arr           = array();
            $wholesale_price     = array();
            $wholesale_role_name = '';
            $raw_wholesale_price = '';
            $product_id          = $product->get_id();

            if (in_array(WWP_Helper_Functions::wwp_get_product_type($product), array('simple', 'variation'))) {

                foreach ($wholesale_roles as $wholesale_role => $data) {
                    if (in_array($wholesale_role, $wholesale_role_options)) {
                        $price_arr[$wholesale_role]                   = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3($product_id, array($wholesale_role));
                        $price_arr[$wholesale_role]['roleName']       = $data['roleName'];
                        $price_arr[$wholesale_role]['wholesale_role'] = $wholesale_role;

                        // Remove wholesale roles with empty or zero value for wholesale_price
                        if ($price_arr[$wholesale_role]['wholesale_price'] <= 0) {
                            unset($price_arr[$wholesale_role]);
                        }
                    }

                }

                foreach ($price_arr as $price) {

                    $wholesale_role_name = trim(str_replace('Wholesale', '', $price['roleName']));
                    $raw_wholesale_price = $price['wholesale_price'];

                    if (strcasecmp($raw_wholesale_price, '') != 0) {
                        $wholesale_price_suffix = WWP_Wholesale_Prices::get_wholesale_price_suffix($product, array($price['wholesale_role']), $price['wholesale_price_with_no_tax']);

                        ob_start();
                        $return_wholesale_price .= "<tr><td class='textalign-left'>Wholesale " . ucwords($wholesale_role_name) . "</td><td class='textalign-right autowidth'>" . str_replace('"', "'", wc_price($raw_wholesale_price)) . " " . $wholesale_price_suffix . "</td></tr>";
                        ob_clean();
                    }

                }

            } elseif (WWP_Helper_Functions::wwp_get_product_type($product) === 'variable') {

                $variations                           = WWP_Helper_Functions::wwp_get_variable_product_variations($product);
                $min_price                            = '';
                $min_wholesale_price_without_taxing   = '';
                $max_price                            = '';
                $max_wholesale_price_without_taxing   = '';
                $some_variations_have_wholesale_price = false;
                $product_variable_price               = array();

                foreach ($wholesale_roles as $wholesale_role => $data) {
                    if (in_array($wholesale_role, $wholesale_role_options)) {
                        foreach ($variations as $variation) {

                            if (!$variation['is_purchasable']) {continue;};

                            $curr_var_price             = $variation['display_price'];
                            $price_var[$wholesale_role] = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3($variation['variation_id'], array($wholesale_role));

                            if (strcasecmp($price_var[$wholesale_role]['wholesale_price'], '') != 0) {

                                $curr_var_price = $price_var[$wholesale_role]['wholesale_price'];

                                if (!$some_variations_have_wholesale_price) {
                                    $some_variations_have_wholesale_price = true;
                                }

                            }

                            if (strcasecmp($min_price, '') == 0 || $curr_var_price < $min_price) {

                                $min_price                          = $curr_var_price;
                                $min_wholesale_price_without_taxing = strcasecmp($price_var[$wholesale_role]['wholesale_price_with_no_tax'], '') != 0 ? $price_var[$wholesale_role]['wholesale_price_with_no_tax'] : '';

                                $product_variable_price[$wholesale_role]['min_price'] = $curr_var_price;

                            }

                            if (strcasecmp($max_price, '') == 0 || $curr_var_price > $max_price) {

                                $max_price                          = $curr_var_price;
                                $max_wholesale_price_without_taxing = strcasecmp($price_var[$wholesale_role]['wholesale_price_with_no_tax'], '') != 0 ? $price_var[$wholesale_role]['wholesale_price_with_no_tax'] : '';

                                $product_variable_price[$wholesale_role]['max_price'] = $curr_var_price;

                            }

                            if ($some_variations_have_wholesale_price && strcasecmp($min_price, '') != 0 && strcasecmp($max_price, '') != 0) {
                                if ($min_price != $max_price && $min_price < $max_price) {
                                    $max_price                                            = $curr_var_price;
                                    $product_variable_price[$wholesale_role]['max_price'] = $curr_var_price;
                                }
                            }

                        }

                        if (!empty($product_variable_price[$wholesale_role])) {
                            $price_arr[$wholesale_role] = array(
                                'wholesale_role'                       => $wholesale_role,
                                'roleName'                             => $data['roleName'],
                                'min_price'                            => $min_price,
                                'min_wholesale_price_without_taxing'   => $min_wholesale_price_without_taxing,
                                'max_price'                            => $max_price,
                                'max_wholesale_price_without_taxing'   => $max_wholesale_price_without_taxing,
                                'some_variations_have_wholesale_price' => $some_variations_have_wholesale_price,
                            );
                        }
                    }

                }

                foreach ($price_arr as $price) {
                    $wholesale_role_name    = trim(str_replace('Wholesale', '', $price['roleName']));
                    $raw_min_price          = $price['min_price'];
                    $raw_max_price          = $price['max_price'];
                    $wsprice                = !empty($price['max_wholesale_price_without_taxing']) ? $price['max_wholesale_price_without_taxing'] : null;
                    $wholesale_price_suffix = WWP_Wholesale_Prices::get_wholesale_price_suffix($product, array($price['wholesale_role']), $wsprice);

                    if (strcasecmp($raw_min_price, '') != 0 && strcasecmp($raw_max_price, '') != 0) {
                        ob_start();
                        $return_wholesale_price .= "<tr><td class='textalign-left'>Wholesale " . ucwords($wholesale_role_name) . "</td><td class='textalign-right autowidth'>" . str_replace('"', "'", wc_price($raw_min_price)) . " - " . str_replace('"', "'", wc_price($raw_max_price)) . " " . $wholesale_price_suffix . "</td></tr>";
                        ob_clean();
                    }

                }

            }

            $html_out = "<div class='popover-wholesale-price-table'><table class='table'><tbody>" . $return_wholesale_price . "</tbody></table></div>";
            if (is_plugin_active('woocommerce-wholesale-lead-capture/woocommerce-wholesale-lead-capture.bootstrap.php')) {
                $html_out .= "<div class='register-link'><a href='" . self::registration_link_filter() . "'><p><strong>" . self::registration_text_filter() . "</strong></p></a></div>";
            }

            return $html_out;
            //echo $html_out;
            //die();

        }
    }

    /**
     * Register custom fields
     *
     * @since 1.15.0
     * @access private
     */
    private function _register_settings_field_options()
    {
        if (get_option('wwp_see_wholesale_prices_replacement_text') == false) {
            update_option('wwp_see_wholesale_prices_replacement_text', 'Click to see wholesale prices');
        }

        if (get_option('wwp_wholesale_role_select_chosen') == false) {
            $wholesale_roles = array('wholesale_customer');
            update_option('wwp_wholesale_role_select_chosen', $wholesale_roles);
        }

        if (get_option('wwp_price_settings_register_text') == false) {
            update_option('wwp_price_settings_register_text', 'Click here to register as a wholesale customer');
        }

        if (get_option('wwp_non_wholesale_show_in_products') == false) {
            update_option('wwp_non_wholesale_show_in_products', 'yes');
        }

        if (get_option('wwp_non_wholesale_show_in_shop') == false) {
            update_option('wwp_non_wholesale_show_in_shop', 'yes');
        }

        if (get_option('wwp_non_wholesale_show_in_wwof') == false) {
            update_option('wwp_non_wholesale_show_in_wwof', 'yes');
        }
    }

    /**
     * This will get the registration wholesale page if WWLC is active/installed from the selected options in WWLC Registration Settings.
     *
     * @since 1.15.0
     * @since 1.15.1
     * @access public
     * @return permalink for registration page
     */
    public static function registration_link_filter()
    {
        $register_link = (get_option('wwlc_general_registration_page') ? get_option('wwlc_general_registration_page') : '');

        if (is_plugin_active('woocommerce-wholesale-lead-capture/woocommerce-wholesale-lead-capture.bootstrap.php')) {
            return apply_filters('wwp_non_wholesale_registration_link_filter', get_permalink($register_link));
        } else {
            return apply_filters('wwp_non_wholesale_registration_link_filter', get_permalink(get_the_ID()));
        }
    }

    /**
     * This will display registration text message for non wholesale users to register as a wholesale customer
     *
     * @since 1.15.0
     * @since 1.15.1
     * @access public
     * @return string registration text message which is filterable
     */
    public static function registration_text_filter()
    {
        $registration_text = get_option('wwp_price_settings_register_text');

        return apply_filters('wwp_non_wholesale_registration_text_filter', __($registration_text, 'woocommerce-wholesale-prices'));
    }

    /**
     * This will load all scripts needed for proper functionalities
     *
     * @since 1.15.0
     * @since 1.15.3 load scripts only in front end
     * @access private
     */
    private function _load_script_non_wholesale()
    {
        // Load Custom JS script
        wp_enqueue_script('wwp-prices-non-wholesale_js', WWP_JS_URL . 'app/wwp-prices-non-wholesales.js', array('jquery'), WWP_Helper_Functions::get_wwp_version(), true);

        // Load Localize script
        wp_localize_script('wwp-prices-non-wholesale_js', 'wwp_non_wholesale_var', array(
            'popover_header_title' => apply_filters('wwp_popover_header_title', __('Wholesale Prices', 'woocommerce-wholesale-prices')),
            'is_wwlc_active'       => is_plugin_active('woocommerce-wholesale-lead-capture/woocommerce-wholesale-lead-capture.bootstrap.php') ? 'true' : 'false',
        ));
    }

    /**
     * This function display's "Click to See Wholesale Prices" on Shops, Single Products, Upsells, Cross sells
     * Wholesale Order Form, this function will also trigger popover Wholesale Price Box if click.
     *
     * @since 1.15.0
     * @since 1.15.1 added function get_product_wholesale_prices
     * @access public
     * @return string $message containing html string
     */
    public static function display_replacement_message_to_non_wholesale()
    {
        global $product, $wc_wholesale_prices, $pagenow;

        if (empty($product)) {
            return;
        }

        $is_wwof_active                  = is_plugin_active('woocommerce-wholesale-order-form/woocommerce-wholesale-order-form.bootstrap.php');
        $show_wholesale_prices_text      = 'no';
        $product_id                      = $product->get_id();
        $has_bundled_items               = 0;
        $page                            = '';
        $wholesale_role_general_discount = get_option('WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING', array());
        $wholesale_price_options         = array();
        $show_in_product                 = get_option('wwp_non_wholesale_show_in_products');
        $show_in_shop                    = get_option('wwp_non_wholesale_show_in_shop');
        $show_in_wwof                    = get_option('wwp_non_wholesale_show_in_wwof');

        if (WWP_Helper_Functions::is_wwpp_active()) {
            $wholesale_price_options = get_option('wwp_wholesale_role_select_chosen');
        } else {
            $wholesale_price_options = array_keys($wc_wholesale_prices->wwp_wholesale_roles->getAllRegisteredWholesaleRoles());
        }

        $message = apply_filters('wwp_display_non_wholesale_replacement_message', __(empty(get_option('wwp_see_wholesale_prices_replacement_text')) ? 'Click to See Wholesale Prices' : get_option('wwp_see_wholesale_prices_replacement_text')), 'woocommerce-wholesale-prices');

        if (!empty($message)) {

            $data = json_encode(htmlspecialchars(self::get_product_wholesale_prices($product_id), ENT_QUOTES, 'UTF-8'));

            $message = '<a href="#" role="button" type="button" data-trigger="focus" class="popover_wholesale_replacement_message nostyle" rel="popover" data-toggle="popover" data-product_id="' . $product_id . '" data-wholesale_price_box=' . $data . '><span style="margin-top:1.0em; margin-bottom:1.5em; display:inline-block; border:none;">' . __($message, 'woocommerce-wholesale-prices') . '</span></a>';
        }

        // Check for bundle products child, dont show "Click to see wholesale prices"
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            if ($product->is_type('bundle')) {
                $bundle_product    = new WC_Product_Bundle($product_id);
                $bundled_items     = $bundle_product->get_bundled_item_ids();
                $has_bundled_items = count($bundled_items);
            }
        }

        if (!empty($wholesale_price_options)) {

            // For PHP 8.0.8 Compatibility
            if (is_array($wholesale_price_options) || is_object($wholesale_price_options)) {

                foreach ($wholesale_price_options as $wholesale_role) {
                    $wholesale_price = get_post_meta($product_id, $wholesale_role . '_wholesale_price', true);

                    $ignore_cat_level               = 'no';
                    $ignore_role_level              = 'no';
                    $have_wholesale_price_cat_level = 'no';

                    if (WWP_Helper_Functions::is_wwpp_active()) {
                        $ignore_cat_level               = get_post_meta($product_id, 'wwpp_ignore_cat_level_wholesale_discount', true);
                        $ignore_role_level              = get_post_meta($product_id, 'wwpp_ignore_role_level_wholesale_discount', true);
                        $have_wholesale_price_cat_level = get_post_meta($product_id, $wholesale_role . '_have_wholesale_price_set_by_product_cat', true);
                    }

                    if (get_post_meta($product_id, $wholesale_role . '_have_wholesale_price', true) === 'yes' && $wholesale_price > 0) {

                        $show_wholesale_prices_text = 'yes';

                        if ($ignore_role_level === 'yes') {
                            $show_wholesale_prices_text = 'no';
                        }

                        break;

                    } elseif ($have_wholesale_price_cat_level === 'yes') {

                        $show_wholesale_prices_text = 'yes';

                        if ($ignore_cat_level === 'yes') {
                            $show_wholesale_prices_text = 'no';
                        }

                        break;

                    } elseif (WWP_Helper_Functions::is_wwpp_active() && !empty($wholesale_role_general_discount)) {

                        $show_wholesale_prices_text = 'yes';

                        if ($ignore_role_level === 'yes') {
                            $show_wholesale_prices_text = 'no';
                        }

                        break;

                    } else {
                        if (WWP_Helper_Functions::wwp_get_product_type($product) === 'variable') {

                            if (WWP_Helper_Functions::is_wwpp_active()) {

                                $variations = WWP_Helper_Functions::wwp_get_variable_product_variations($product);

                                if (get_post_meta($product_id, $wholesale_role . '_have_wholesale_price', true) === 'yes') {

                                    foreach ($variations as $variation) {

                                        if (get_post_meta($variation['variation_id'], $wholesale_role . '_wholesale_price', true) > 0) {

                                            $show_wholesale_prices_text = 'yes';
                                            break;
                                        }
                                    }

                                    if ($ignore_role_level === 'yes') {
                                        $show_wholesale_prices_text = 'no';
                                        break;
                                    }

                                }

                                if ($have_wholesale_price_cat_level === 'yes' && get_post_meta($product_id, $wholesale_role . '_have_wholesale_price', true) === 'yes') {

                                    $show_wholesale_prices_text = 'yes';

                                    if ($ignore_cat_level === 'yes') {
                                        $show_wholesale_prices_text = 'no';
                                    }

                                    break;
                                }

                                if (!empty($wholesale_role_general_discount)) {

                                    $show_wholesale_prices_text = 'yes';

                                    if ($ignore_role_level === 'yes') {
                                        $show_wholesale_prices_text = 'no';
                                    }

                                    break;

                                }

                            } else {

                                if (get_post_meta($product_id, $wholesale_role . '_have_wholesale_price', true) === 'yes' && empty(get_post_meta($product_id, $wholesale_role . '_have_wholesale_price_set_by_product_cat', true))) {

                                    $show_wholesale_prices_text = 'yes';

                                    if ($ignore_role_level === 'yes') {
                                        $show_wholesale_prices_text = 'no';
                                    }

                                    break;

                                }

                            }

                        } else {

                            $show_wholesale_prices_text = 'no';
                            break;
                        }
                    }

                }
            }
        } else {
            $show_wholesale_prices_text = 'no';
        }

        // Display "Click to See Wholesale Price" based on location, if not permitted then don't display it
        if ((is_product() && $show_in_product == 'yes') || (is_shop() && $show_in_shop == 'yes') || ($show_in_wwof == 'yes' && $is_wwof_active)) {
            // Display "Click to See Wholesale Price" if product type falls in the array collection and has wholesale price, if not then don't display it.
            if ($pagenow !== 'edit.php') {

                if ($show_wholesale_prices_text == 'yes' && !empty($wholesale_price_options) && $has_bundled_items == 0) {
                    return '<div class="show_wholesale_prices_text" style="width: 100%;">' . $message . '</div>';
                }

            }
        }

    }

    /**
     * This function is responsible in executing all actions needed to run our application
     *
     * @since 1.15.0
     * @access public
     */
    public function run()
    {
        // Register Settings
        $this->_register_settings_field_options();
    }

}