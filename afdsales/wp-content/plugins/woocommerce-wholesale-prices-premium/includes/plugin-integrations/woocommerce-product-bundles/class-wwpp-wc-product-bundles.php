<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWPP_WC_Product_Bundles')) {

    /**
     * Model that houses the logic of integrating with 'WooCommerce Product Bundles' plugin.
     *
     * Bundle products just inherits from simple product so that's why they are very similar.
     * So most of the codebase here are just reusing the codes from simple product.
     *
     * @since 1.13.0
     */
    class WWPP_WC_Product_Bundles
    {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * Property that holds the single main instance of WWPP_WC_Composite_Product.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_WC_Composite_Product
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
         * Model that houses logic of admin custom fields for simple products.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Admin_Custom_Fields_Simple_Product
         */
        private $_wwpp_admin_custom_fields_simple_product;

        /**
         * Model that houses the logic of wholesale prices.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Wholesale_Prices
         */
        private $_wwpp_wholesale_prices;

        /**
         * Model that houses the logic of applying product category level wholesale pricing.
         *
         * @since 1.14.0
         * @access public
         * @var WWPP_Wholesale_Price_Product_Category
         */
        private $_wwpp_wholesale_price_product_category;

        /**
         * Model that houses the logic of product wholesale price on per wholesale role level.
         *
         * @since 1.16.0
         * @access private
         * @var WWPP_Wholesale_Price_Wholesale_Role
         */
        private $_wwpp_wholesale_price_wholesale_role;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        /**
         * WWPP_WC_Composite_Product constructor.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_WC_Composite_Product model.
         */
        public function __construct($dependencies)
        {

            $this->_wwpp_wholesale_roles                    = $dependencies['WWPP_Wholesale_Roles'];
            $this->_wwpp_admin_custom_fields_simple_product = $dependencies['WWPP_Admin_Custom_Fields_Simple_Product'];
            $this->_wwpp_wholesale_prices                   = $dependencies['WWPP_Wholesale_Prices'];
            $this->_wwpp_wholesale_price_product_category   = $dependencies['WWPP_Wholesale_Price_Product_Category'];
            $this->_wwpp_wholesale_price_wholesale_role     = $dependencies['WWPP_Wholesale_Price_Wholesale_Role'];

        }

        /**
         * Ensure that only one instance of WWPP_WC_Composite_Product is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_WC_Composite_Product model.
         * @return WWPP_WC_Composite_Product
         */
        public static function instance($dependencies)
        {

            if (!self::$_instance instanceof self) {
                self::$_instance = new self($dependencies);
            }

            return self::$_instance;

        }

        /**
         * Add bundle product wholesale price field.
         *
         * @since 1.13.0
         * @access public
         */
        public function add_wholesale_price_fields()
        {

            global $post, $wc_wholesale_prices;

            $product = wc_get_product($post->ID);

            if (WWP_Helper_Functions::wwp_get_product_type($product) === 'bundle') {
                $wc_wholesale_prices->wwp_admin_custom_fields_simple_product->add_wholesale_price_fields();
            }

        }

        /**
         * Save bundle product wholesale price field.
         *
         * @since 1.9.0
         * @since 1.13.0 Refactored codebase and move to its dedicated model.
         * @access public
         *
         * @param int $post_id Product id.
         */
        public function save_wholesale_price_fields($post_id)
        {

            global $wc_wholesale_prices;

            $wc_wholesale_prices->wwp_admin_custom_fields_simple_product->save_wholesale_price_fields($post_id, 'bundle');

        }

        /**
         * Save bundle product wholesale minimum order quantity field.
         *
         * @since 1.9.0
         * @since 1.13.0 Refactored codebase and move to its dedicated model.
         * @access public
         *
         * @param $post_id Product id.
         */
        public function save_minimum_order_quantity_fields($post_id)
        {

            /**
             * Bundle products are very similar to simple products in terms of their fields structure.
             * Therefore we can reuse the code we have on saving wholesale minimum order quantity for simple products to bundle products.
             * BTW the adding of custom wholesale minimum order quantity field to bundle products are already handled by this function 'add_minimum_order_quantity_fields' on 'WWPP_Admin_Custom_Fields_Simple_Product'. Read the desc of the function.
             */
            $this->_wwpp_admin_custom_fields_simple_product->save_minimum_order_quantity_fields($post_id, 'bundle');

        }

        /**
         * Save order quantity step custom field value for bundle products on product edit page.
         *
         * @since 1.16.3
         * @access public
         *
         * @param int $post_id Product id.
         */
        public function save_order_quantity_step_fields($post_id)
        {

            /**
             * Bundle products are very similar to simple products in terms of their fields structure.
             * Therefore we can reuse the code we have on saving wholesale order quantity step for simple products to bundle products.
             * BTW the adding of custom wholesale order quantity step field to bundle products are already handled by this function 'add_order_quantity_step_fields' on 'WWPP_Admin_Custom_Fields_Simple_Product'. Read the desc of the function.
             */
            $this->_wwpp_admin_custom_fields_simple_product->save_order_quantity_step_fields($post_id, 'bundle');

        }

        /**
         * Filter bundled items of a bundle product and check if the current user is allowed to view the bundled item.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array             $bundled_items     Array bundled items.
         * @param WC_Product_Bundle $wc_product_bundle Bundle product instance.
         * @return array Filtered array bundled items.
         */
        public function filter_bundled_items($bundled_items, $wc_product_bundle)
        {

            $bundle_id = $wc_product_bundle->get_id();

            // If parent bundle has "Disregard Product Category Level Wholesale Discount" enabled then override child products
            if (get_post_meta($bundle_id, 'wwpp_ignore_cat_level_wholesale_discount', true) == 'yes') {
                add_filter('wwpp_disregard_cat_level_discount', function ($value) {
                    return 'yes';
                }, 10, 1);
            }

            // If parent bundle has "Disregard Wholesale Role Level Wholesale Discount" enabled then override child products
            if (get_post_meta($bundle_id, 'wwpp_ignore_role_level_wholesale_discount', true) == 'yes') {
                add_filter('wwpp_disregard_role_level_discount', function ($value) {
                    return 'yes';
                }, 10, 1);
            }

            if (current_user_can('administrator')) {
                return $bundled_items;
            }

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            $user_wholesale_role = !empty($user_wholesale_role) ? $user_wholesale_role[0] : '';

            // Parent has wholesale discount then return its children (children will be considered wholesale)
            $wholesale_price = get_post_meta($bundle_id, $user_wholesale_role . '_wholesale_price', true);

            if (!empty($wholesale_price) && $wholesale_price > 0) {
                return $bundled_items;
            }

            // If discount is based of category then show children
            if (!empty($user_wholesale_role)) {
                $discount_from_category = get_post_meta($bundle_id, $user_wholesale_role . '_have_wholesale_price_set_by_product_cat', true);
                if ($discount_from_category == 'yes') {
                    return $bundled_items;
                }
            }

            foreach ($bundled_items as $bundle_id => $bundled_item) {

                $product_id = $bundled_item->item_data['product_id'];

                if (!$this->is_bundle_item_available_for_current_user($product_id, $user_wholesale_role)) {
                    unset($bundled_items[$bundle_id]);
                }

            }

            return $bundled_items;

        }

        /**
         * Check if current bundle item is available for the current user.
         *
         * @since 1.13.0
         * @since 1.16.0 Refactor code base to get wholesale discount wholesale role level from 'WWPP_Wholesale_Price_Wholesale_Role' model.
         * @access public
         *
         * @param int    $product_id          Product id.
         * @param string $user_wholesale_role User wholesale role.
         * @return boolean True if current user have access to the current bundle item, false otherwise.
         */
        public function is_bundle_item_available_for_current_user($product_id, $user_wholesale_role)
        {

            $have_wholesale_price = "yes";

            $curr_product_wholesale_filter = get_post_meta($product_id, WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER);
            if (!is_array($curr_product_wholesale_filter)) {
                $curr_product_wholesale_filter = array();
            }

            // If Logged-in as wholesale user and "Only Show.." is enabled
            if (!empty($user_wholesale_role) && get_option('wwpp_settings_only_show_wholesale_products_to_wholesale_users', false) === 'yes') {

                $product = wc_get_product($product_id);

                if (!empty($product) && $product->get_type() == 'simple') {

                    $price_arr = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3($product_id, array($user_wholesale_role));

                    switch ($price_arr['source']) {
                        case 'per_product_level':
                            if (empty($price_arr['wholesale_price'])) {
                                return false;
                            }
                            break;
                        case 'wholesale_role_level':
                            if (get_post_meta($product_id, 'wwpp_ignore_role_level_wholesale_discount', true) == 'yes') {
                                return false;
                            }
                            break;
                        case 'product_category_level':
                            if (get_post_meta($product_id, 'wwpp_ignore_cat_level_wholesale_discount', true) == 'yes') {
                                return false;
                            }
                            break;
                    }

                } elseif (!empty($product) && $product->get_type() == 'variable') {

                    $variation_with_wholesale_price = [];

                    foreach ($product->get_children() as $variation) {

                        $price_arr = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3($variation, array($user_wholesale_role));

                        switch ($price_arr['source']) {
                            case 'per_product_level':
                                if (!empty($price_arr['wholesale_price'])) {
                                    $variation_with_wholesale_price[] = $variation;
                                }
                                break;
                            case 'wholesale_role_level':
                                if (get_post_meta($product_id, 'wwpp_ignore_role_level_wholesale_discount', true) != 'yes') {
                                    $variation_with_wholesale_price[] = $variation;
                                }
                                break;
                            case 'product_category_level':
                                if (get_post_meta($product_id, 'wwpp_ignore_cat_level_wholesale_discount', true) != 'yes') {
                                    $variation_with_wholesale_price[] = $variation;
                                }
                                break;
                        }
                    }

                    if (empty($variation_with_wholesale_price)) {
                        return false;
                    }
                }

                $user_wholesale_discount = $this->_wwpp_wholesale_price_wholesale_role->get_user_wholesale_role_level_discount(get_current_user_id(), $user_wholesale_role);

                if ($user_wholesale_role && empty($user_wholesale_discount['discount'])) {
                    $have_wholesale_price = get_post_meta($product_id, $user_wholesale_role . '_have_wholesale_price', true);
                }

            }

            return ((in_array('all', $curr_product_wholesale_filter) || in_array($user_wholesale_role, $curr_product_wholesale_filter)) && $have_wholesale_price === "yes");

        }

        /**
         * The purpose of this is to aid in properly calculating the total price of a bundle product if it has wholesale price.
         * If we dont add this filter callback, bundle product will use the bundle product's original base price instead of the wholesale price in calculation.
         * Prior to v1.13.0, we add a note to the single bundle page that the computation of total is wrong and therefore they should check the cart page instead.
         * But due to changes on Product Bundle codebase, we can now successfully properly compute the total with wholesale pricing.
         *
         * @since 1.13.0
         * @since 1.16.4 Bug fix WWPP-564
         * @access public
         *
         * @param array             $bundle_price_data Array of bundle price data.
         * @param WC_Product_Bundle $wc_product_bundle Product Product bundle object.
         */
        public function filter_bundle_product_base_price($bundle_price_data, $wc_product_bundle)
        {

            if (!$wc_product_bundle->contains('priced_individually')) {
                return $bundle_price_data;
            }

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if (empty($user_wholesale_role)) {
                return $bundle_price_data;
            }

            $bundle_id = $wc_product_bundle->get_id();

            // If parent bundle has "Disregard Product Category Level Wholesale Discount" enabled then override child products
            if (get_post_meta($bundle_id, 'wwpp_ignore_cat_level_wholesale_discount', true) == 'yes') {
                add_filter('wwpp_disregard_cat_level_discount', function ($value) {
                    return 'yes';
                }, 10, 1);
            }

            // If parent bundle has "Disregard Wholesale Role Level Wholesale Discount" enabled then override child products
            if (get_post_meta($bundle_id, 'wwpp_ignore_role_level_wholesale_discount', true) == 'yes') {
                add_filter('wwpp_disregard_role_level_discount', function ($value) {
                    return 'yes';
                }, 10, 1);
            }

            $price_arr                       = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3(WWP_Helper_Functions::wwp_get_product_id($wc_product_bundle), $user_wholesale_role);
            $wholesale_price                 = $price_arr['wholesale_price_with_no_tax']; // Make sure to use 'wholesale_price_with_no_tax' as bundle plugin will apply taxing on the $bundle_price_data[ 'base_price' ]. WWPP-564
            $bundle_price_data['base_price'] = $wholesale_price ? $wholesale_price : $bundle_price_data['base_price'];

            return $bundle_price_data;

        }

        /**
         * Filter the bundle item price. The purpose of this is to aid in properly calculating the total price of bundle product if it has wholesale price.
         * Same concept as the comment of 'filter_bundle_product_base_price' function above.
         * If we dont add this filter callback, bundle product will use the bundle price instead of the wholesale price in calculation.
         * There is a cache to this solution though, because bundle plugins hook too early, that is get_price, the modified price we return here is also set as the original price of the bundle item.
         * That is why we need the 'hide_original_d' function below to compensiate for this issue.
         *
         * @since 1.13.0
         * @since 1.16.4    Bug fix WWPP-564
         * @since 1.25      Compatibility with WooCommerce Currency Switcher
         * @since 1.26.1    Refactor codes. Fix Bundle Discount. Fix compat with WOOCS.
         *
         * @access public
         *
         * @param int               $bundled_item_price         Bundle Item Price.
         * @param WC_Product        $product                    Product Object.
         * @param int               $discount                   Bundle Item Disocunt
         * @param WC_Bundled_Item   $bundled_item               Bundle Item Object
         * @access public
         * @return int
         */
        public function filter_bundle_item_price($bundled_item_price, $product, $discount, $bundled_item)
        {

            global $WOOCS;

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            // Stop redundant call
            if (property_exists($product, 'wholesale_price_data')) {
                if (property_exists($product, 'discounted_wholesale_price') && $product->discounted_wholesale_price > 0) {
                    return $product->discounted_wholesale_price;
                } else {
                    return $product->wholesale_price_data['wholesale_price_raw'];
                }
            }

            if (empty($user_wholesale_role)) {

                return $bundled_item_price;
            }

            $wholesale_price = '';

            // Compatibility with WOOCS
            // Avoid double currency conversion
            if ($WOOCS) {
                $_REQUEST['woocs_block_price_hook'] = true;
            }

            // The reason why we need the code below is to avoid infinite filter call loop
            // Bundles plugin adds filters on get_price() and get_regular_price() which is executed inside the callbacks of the 'wwp_filter_wholesale_price_shop' hook
            // Therefore before executing the callbacks of that hook we must remove the filters bundles plugin attached
            // This filters will be auto attached by bundle plugin as necessary
            // Happens for regular product, the helper function WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3 will perform infinite loop
            WC_PB_Product_Prices::remove_price_filters();
            $wholesale_price_data = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3(WWP_Helper_Functions::wwp_get_product_id($product), $user_wholesale_role);
            WC_PB_Product_Prices::add_price_filters($bundled_item);

            if ($WOOCS) {
                unset($_REQUEST['woocs_block_price_hook']);
            }

            if (isset($wholesale_price_data['wholesale_price']) && $wholesale_price_data['wholesale_price'] > 0) {

                $product->wholesale_price_data = $wholesale_price_data;
                $wholesale_price               = $wholesale_price_data['wholesale_price_raw']; // Make sure to use 'wholesale_price_raw' as bundle plugin will apply taxing on the price we return from this function.

                if ($discount > 0) {

                    $discounted_wholesale_price          = $wholesale_price_data['wholesale_price_raw'] - ($wholesale_price_data['wholesale_price_raw'] * ($discount / 100));
                    $product->discounted_wholesale_price = $discounted_wholesale_price;
                    $wholesale_price                     = $discounted_wholesale_price;

                }

            }

            return $wholesale_price ? $wholesale_price : $bundled_item_price;

        }

        /**
         * With the advent of WC 2.7, product attributes are not directly accessible anymore.
         * We need to refactor how we retrive the id of the product.
         * Note this filter callback is only for WC less than 2.7
         *
         * @since 1.3.1
         * @access public
         *
         * @param int        $product_id Product id.
         * @param WC_Product $product    Product object.
         * @return int Product id.
         */
        public function get_product_id($product_id, $product)
        {

            if (version_compare(WC()->version, '3.0.0', '<')) {
                return $product->id;
            }

            return $product_id;

        }

        /**
         * Add support for quick edit.
         *
         * @since 1.14.4
         * @access public
         *
         * @param Array  $allowed_product_types list of allowed product types.
         * @param string $field                 wholesale custom field.
         */
        public function support_for_quick_edit_fields($allowed_product_types, $field)
        {

            $supported_fields = array(
                'wholesale_price_fields',
                'wholesale_minimum_order_quantity',
            );

            if (in_array($field, $supported_fields)) {
                $allowed_product_types[] = 'bundle';
            }

            return $allowed_product_types;
        }

        /**
         * We need to do this because variable product bundle items are treated differently compared to simple products.
         * One way is that, bundle plugin uses 'get_available_variations' function of a variable product to get the data of its variations.
         * Now it uses that data instead on calculating the total of the bundle product ( when variable product is priced individually ).
         * The issue with this is we are not attaching any callback to the 'get_available_variations' function of a variable product coz there are no any filters inside that function.
         * So the price data that function returns are the original price data ( data we have not filtered ).
         * That is why on the front end you will the correct pricing but wrong total calculation.
         * So the solution is whenever bundle plugin loads the variations data via json on the front end.
         * We modify the data via js script. We need to do this coz there are no filters available for us to attach to be able to do the fix on the backend (PHP).
         *
         * @since 1.14.5
         * @since 1.16.3
         * With product bundle 5.7.7, they edited there markup again on the front end, which results to this bug WWPP-560, this is fixed in 1.16.3
         * Also integrate the step feature for variable products that are made as bundled item of a bundle product WWPP-532
         * @since 1.16.4 Bug fix WWPP-564
         *
         * @access public
         *
         * @param int             $bundled_product_id Product id of the bundle item.
         * @param WC_Bundled_Item $bundled_item       Bundle item instance.
         */
        public function filter_variable_bundle_variations_data($bundled_product_id, $bundled_item)
        {

            // Only do this if variable product bundle item is priced individually
            if ($bundled_item->item_data['priced_individually'] !== 'yes') {
                return;
            }

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if (!empty($user_wholesale_role)) {

                $variations_wholesale_prices = null;
                $variations                  = WWP_Helper_Functions::wwp_get_variable_product_variations($bundled_item->product);

                foreach ($variations as $variation) {

                    WC_PB_Product_Prices::remove_price_filters();
                    $price_arr = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3($variation['variation_id'], $user_wholesale_role);
                    WC_PB_Product_Prices::add_price_filters($bundled_item);

                    $wholesale_price = $price_arr['wholesale_price_raw']; // Make sure to use wholesale price with no taxing applied. Bundle plugin will apply taxing for prices here as well.

                    // Priced Invidually with Discount Percentage
                    if ($bundled_item->get_discount() && $wholesale_price > 0) {

                        $wholesale_price = $wholesale_price - ($wholesale_price * ($bundled_item->get_discount() / 100));

                    }

                    $variations_wholesale_prices[$variation['variation_id']] = (float) $wholesale_price > 0 ? $wholesale_price : 0;

                }

                if ($variations_wholesale_prices) {?>

<script type="text/javascript">
/**
 * Code should be inside document ready so all required markup have been successfully rendered.
 */
jQuery(document).ready(function($) {

    var variations_wholesale_prices = {
            <?php foreach ($variations_wholesale_prices as $var_id => $wholesale_price) {echo "$var_id : $wholesale_price,";}?>
        },
        product_variations_element = jQuery(
            '.bundled_item_cart_content[data-product_id="<?php echo $bundled_product_id; ?>"][data-bundle_id="<?php echo $bundled_item->bundle_id; ?>"][data-bundled_item_id="<?php echo $bundled_item->get_id(); ?>"]'
        ),
        product_variations_qty_field = product_variations_element.find(
            ".bundled_qty[name='bundle_quantity_<?php echo $bundled_item->get_id(); ?>']"),
        product_variations_data = product_variations_element.data("product_variations");

    for (var i = 0; i < product_variations_data.length; i++) {

        var price = variations_wholesale_prices[product_variations_data[i].id ? product_variations_data[i].id :
            product_variations_data[i].variation_id];
        product_variations_data[i].display_price = price > 0 ? price : product_variations_data[i].display_price;
        product_variations_data[i].price = price > 0 ? price : product_variations_data[i].price;

        /**
         * If min qty is set on a variation on wwpp side, just set initial value of the qty field to that min qty
         * Do not set min attribute of qty field to that min qty
         * The reason is to make the behavior of variations inside a bundled variable product to variations of independent variable products
         * where min qty is set as initial value but still allows customers to order lower than min qty.
         */

        if (product_variations_data[i].min_value && product_variations_data[i].min_value > 1) {

            var min_value = parseInt(product_variations_data[i].min_value, 10);

            if (product_variations_data[i].step && product_variations_data[i].step > 1) {

                /**
                 * If step is supplied and min qty is also supplied on the variation on wwpp
                 * Then this is where we restrict the qty field
                 * So similar to variations inside an independent variable product, variations
                 */

                var step = parseInt(product_variations_data[i].step, 10);

                product_variations_data[i].step = step;
                product_variations_data[i].min_qty = min_value;

            }

        }

    }

    jQuery(
            '.bundled_item_cart_content[data-product_id="<?php echo $bundled_product_id; ?>"][data-bundle_id="<?php echo $bundled_item->bundle_id; ?>"][data-bundled_item_id="<?php echo $bundled_item->get_id(); ?>"]'
        )
        .data("product_variations", product_variations_data);

    function wwpp_update_qty_field_attributes() {

        var $variations_form = $(this),
            variation_id = $variations_form.find(".single_variation_wrap .variation_id").attr('value');

        for (var i = 0; i < product_variations_data.length; i++) {

            if (product_variations_data[i].variation_id == variation_id) {

                /**
                 * If min qty is set on a variation on wwpp side, just set initial value of the qty field to that min qty
                 * Do not set min attribute of qty field to that min qty
                 * The reason is to make the behavior of variations inside a bundled variable product to variations of independent variable products
                 * where min qty is set as initial value but still allows customers to order lower than min qty.
                 */

                if (product_variations_data[i].input_value)
                    product_variations_qty_field.attr("value", parseInt(product_variations_data[i].input_value,
                        10));
                else if (product_variations_data[i].min_qty)
                    product_variations_qty_field.attr("value", parseInt(product_variations_data[i].min_qty,
                        10));
                else
                    product_variations_qty_field.attr("value", 1);

                if (product_variations_data[i].min_value && product_variations_data[i].min_value > 1) {

                    var min_value = parseInt(product_variations_data[i].min_value, 10);

                    product_variations_qty_field.attr("value", min_value);

                    if (product_variations_data[i].step && product_variations_data[i].step > 1) {

                        /**
                         * If step is supplied and min qty is also supplied on the variation on wwpp
                         * Then this is where we restrict the qty field
                         * So similar to variations inside an independent variable product, variations
                         */

                        var step = parseInt(product_variations_data[i].step, 10);

                        product_variations_qty_field.attr("min", min_value);
                        product_variations_qty_field.attr("step", step);

                    } else {

                        product_variations_qty_field.attr("min", 1);
                        product_variations_qty_field.attr("step", 1);

                    }

                } else {

                    product_variations_qty_field.attr("min", 1);
                    product_variations_qty_field.attr("step", 1);

                }

                break;

            }

        }

    }

    $("body").on("woocommerce_variation_has_changed", ".variations_form", wwpp_update_qty_field_attributes);
    $("body").on("found_variation", ".variations_form",
        wwpp_update_qty_field_attributes); // Only triggered on ajax complete

});
</script>

<?php }

            }

        }

        /**
         * In the event variable products have the same regular price, it wont show a per variation price html.
         * That will be a problem if the wholesale price is different across variations but have the same regular price.
         * Coz there will be no html markup that we can hook to show the wholesale price per variation.
         * That is the purpose of this code.
         *
         * @since 1.14.5
         * @since 1.16.0 Supports new wholesale price model.
         * @access public
         *
         * @param int             $bundled_product_id Product id of the bundle item.
         * @param WC_Bundled_Item $bundled_item       Bundle item instance.
         */
        public function filter_per_variation_price_html($bundled_product_id, $bundled_item)
        {

            // Only do this if variable product bundle item is priced individually
            if ($bundled_item->item_data['priced_individually'] !== 'yes') {
                return;
            }

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if (!empty($user_wholesale_role)) {

                $variations_arr                                              = array();
                $product                                                     = $bundled_item->product;
                $has_per_order_quantity_wholesale_price_mapping              = false;
                $has_per_cat_level_order_quantity_wholesale_discount_mapping = false;
                $variations                                                  = WWP_Helper_Functions::wwp_get_variable_product_variations($product);

                foreach ($variations as $variation) {

                    $variationProduct = wc_get_product($variation['variation_id']);
                    $currVarPrice     = $variation['display_price'];
                    $minimumOrder     = get_post_meta($variation['variation_id'], $user_wholesale_role[0] . "_wholesale_minimum_order_quantity", true); // Per variation level
                    WC_PB_Product_Prices::remove_price_filters();
                    $price_arr = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3($variation['variation_id'], $user_wholesale_role);
                    WC_PB_Product_Prices::add_price_filters($bundled_item);
                    $wholesale_price = $price_arr['wholesale_price_raw'];

                    // Priced Invidually with Discount Percentage
                    if ($bundled_item->get_discount() && $wholesale_price > 0) {
                        $wholesale_price = $wholesale_price - ($wholesale_price * ($bundled_item->get_discount() / 100));
                    }

                    $source = $price_arr['source'];

                    // Per parent variable level
                    if (!$minimumOrder) {
                        $minimumOrder = get_post_meta($bundled_product_id, $user_wholesale_role[0] . "_variable_level_wholesale_minimum_order_quantity", true);
                    }

                    // Always default to 1
                    if (!$minimumOrder) {
                        $minimumOrder = 1;
                    }

                    // Check if product have per product level order quantity based wholesale price
                    if (is_numeric($wholesale_price) && !$has_per_order_quantity_wholesale_price_mapping) {

                        $enabled = get_post_meta($variation['variation_id'], WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE, true);
                        $mapping = get_post_meta($variation['variation_id'], WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING, true);
                        if (!is_array($mapping)) {
                            $mapping = array();
                        }

                        $has_mapping_entry = false;
                        foreach ($mapping as $map) {
                            if (isset($map['wholesale_role']) && $map['wholesale_role'] === $user_wholesale_role[0]) {
                                $has_mapping_entry = true;
                            }
                        }

                        if ($enabled == 'yes' && $has_mapping_entry) {
                            $has_per_order_quantity_wholesale_price_mapping = true;
                        }

                    }

                    /**
                     * WWPP-373
                     * Check if product have product category level wholesale pricing set.
                     * Have category level discount.
                     * We do not need to check for the per qty based discount on cat level as checking the base cat discount is enough
                     */
                    if (is_numeric($wholesale_price) && !$has_per_cat_level_order_quantity_wholesale_discount_mapping) {

                        $base_term_id_and_discount = $this->_wwpp_wholesale_price_product_category->get_base_term_id_and_wholesale_discount(WWP_Helper_Functions::wwp_get_product_id($product), $user_wholesale_role);

                        if (!is_null($base_term_id_and_discount['term_id']) && !is_null($base_term_id_and_discount['discount'])) {
                            $has_per_cat_level_order_quantity_wholesale_discount_mapping = true;
                        }

                    }

                    // Only pass through to wc_price if a numeric value given otherwise it will spit out $0.00
                    if (is_numeric($wholesale_price)) {

                        $wholesalePriceTitleText = __('Wholesale Price:123', 'woocommerce-wholesale-prices-premium');
                        $wholesalePriceTitleText = apply_filters('wwp_filter_wholesale_price_title_text', $wholesalePriceTitleText);

                        $wholesalePriceHTML = apply_filters('wwp_product_original_price', '<del class="original-computed-price">' . WWP_Helper_Functions::wwp_formatted_price($currVarPrice) . $product->get_price_suffix() . '</del>', $wholesale_price, $currVarPrice, $product, $user_wholesale_role);

                        $wholesalePriceHTML .= '<span style="display: block;" class="wholesale_price_container">
                                                    <span class="wholesale_price_title">' . $wholesalePriceTitleText . '</span>
                                                    <ins>' . WWP_Helper_Functions::wwp_formatted_price($wholesale_price) . WWP_Wholesale_Prices::get_wholesale_price_suffix($product, $user_wholesale_role, $price_arr['wholesale_price_with_no_tax']) . '</ins>
                                                </span>';

                        $wholesalePriceHTML = apply_filters('wwp_filter_wholesale_price_html', $wholesalePriceHTML, $currVarPrice, $variationProduct, $user_wholesale_role, $wholesalePriceTitleText, $wholesale_price, $source);

                        $wholesalePriceHTML = '<span class="price">' . $wholesalePriceHTML . '</span>';

                        $priceHTML         = $wholesalePriceHTML;
                        $hasWholesalePrice = true;

                    } else {

                        $priceHTML         = '<p class="price">' . WWP_Helper_Functions::wwp_formatted_price($currVarPrice) . $product->get_price_suffix() . '</p>';
                        $hasWholesalePrice = false;

                    }

                    $variations_arr[] = array(
                        'variation_id'        => $variation['variation_id'],
                        'minimum_order'       => (int) $minimumOrder,
                        'raw_regular_price'   => (float) $currVarPrice,
                        'raw_wholesale_price' => (float) $wholesale_price,
                        'price_html'          => $priceHTML,
                        'has_wholesale_price' => $hasWholesalePrice,
                    );

                }?>

<script>
jQuery(document).ready(function($) {

    if ($(
            '.bundled_item_cart_content[data-product_id="<?php echo $bundled_product_id; ?>"][data-bundle_id="<?php echo $bundled_item->bundle_id; ?>"][data-bundled_item_id="<?php echo $bundled_item->get_id(); ?>"]'
        )
        .find(".wholesale_price_container").length <= 0) {

        function update_variation_price_html() {

            var WWPPVariableProductPageVars = {
                    variations: <?php echo json_encode($variations_arr); ?>
                },
                $variations_form = $(
                    '.bundled_item_cart_content[data-product_id="<?php echo $bundled_product_id; ?>"][data-bundle_id="<?php echo $bundled_item->bundle_id; ?>"][data-bundled_item_id="<?php echo $bundled_item->get_id(); ?>"]'
                ),
                variation_id = $variations_form.find(".single_variation_wrap .variation_id").attr('value'),
                $single_variation = $variations_form.find(".single_variation"),
                $qty_field = $variations_form.find(".variations_button .qty");

            for (var i = 0; i < WWPPVariableProductPageVars.variations.length; i++)
                if (WWPPVariableProductPageVars.variations[i]['variation_id'] == variation_id &&
                    $single_variation.find(".price").length <= 0)
                    $single_variation.prepend(WWPPVariableProductPageVars.variations[i]['price_html']);

        }

        $("body").on("woocommerce_variation_has_changed",
            '.bundled_item_cart_content[data-product_id="<?php echo $bundled_product_id; ?>"][data-bundle_id="<?php echo $bundled_item->bundle_id; ?>"][data-bundled_item_id="<?php echo $bundled_item->get_id(); ?>"]',
            update_variation_price_html);
        $("body").on("found_variation",
            '.bundled_item_cart_content[data-product_id="<?php echo $bundled_product_id; ?>"][data-bundle_id="<?php echo $bundled_item->bundle_id; ?>"][data-bundled_item_id="<?php echo $bundled_item->get_id(); ?>"]',
            update_variation_price_html); // Only triggered on ajax complete

    }

});
</script>

<?php

            }

        }

        /**
         * Set visibility meta on product bundle save.
         *
         * @since 1.24.6
         * @access public
         *
         * @param int $post_id Post ( Product ) Id.
         */
        public function set_bundle_product_visibility_meta($post_id)
        {

            $bundled_product = wc_get_product($post_id);

            if (!empty($bundled_product) && $bundled_product->get_type() == 'bundle') {

                $this->set_bundle_visiblity_meta($post_id, $bundled_product);

            } else {

                global $wpdb;

                $bundled_items_table = $wpdb->prefix . 'woocommerce_bundled_items';
                $result              = $wpdb->get_row("SELECT bundle_id FROM $bundled_items_table
                            WHERE product_id = $post_id");

                if (!empty($result)) {

                    $bundled_product = wc_get_product($result->bundle_id);
                    if (!empty($bundled_product) && $bundled_product->get_type() == 'bundle') {
                        $this->set_bundle_visiblity_meta($result->bundle_id, $bundled_product);
                    }

                }

            }

        }

        /**
         * Set visibility meta on product bundle save.
         *
         * @since 1.24.6
         * @access public
         *
         * @param int $bundle_id Post ( Product ) Id.
         * @param obj $bundled_product Bundle Product Object.
         */
        public function set_bundle_visiblity_meta($bundle_id, $bundled_product)
        {

            global $wc_wholesale_prices;

            $wholesale_roles       = $wc_wholesale_prices->wwp_wholesale_roles->getAllRegisteredWholesaleRoles();
            $has_category_discount = false;

            // Check if bundle has category discount
            // Delete _have_wholesale_price meta
            foreach ($wholesale_roles as $role_key => $data) {

                $category_discount = get_post_meta($bundle_id, $role_key . '_have_wholesale_price_set_by_product_cat', true);

                if ($category_discount == 'yes') {
                    $has_category_discount = true;
                } else {
                    delete_post_meta($bundle_id, $role_key . '_have_wholesale_price');
                }

            }

            if ($has_category_discount) {
                return;
            }

            $bundled_items                 = $bundled_product->get_bundled_items();
            $have_wholesale_price_products = array();

            foreach ($bundled_items as $bundle_item_id => $bundled_item) {

                $product_id = $bundled_item->item_data['product_id'];
                $product    = wc_get_product($product_id);

                foreach ($wholesale_roles as $role_key => $data) {

                    $disregard = false;

                    if ($product->get_type() == 'simple') {

                        $price_arr = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3($product_id, array($role_key));

                        switch ($price_arr['source']) {
                            case 'wholesale_role_level':
                                if (
                                    get_post_meta($bundle_id, 'wwpp_ignore_role_level_wholesale_discount', true) == 'yes' ||
                                    get_post_meta($product_id, 'wwpp_ignore_role_level_wholesale_discount', true) == 'yes'
                                ) {
                                    $disregard = true;
                                }
                                break;
                            case 'product_category_level':
                                if (
                                    get_post_meta($bundle_id, 'wwpp_ignore_cat_level_wholesale_discount', true) == 'yes' ||
                                    get_post_meta($product_id, 'wwpp_ignore_cat_level_wholesale_discount', true) == 'yes'
                                ) {
                                    $disregard = true;
                                }
                                break;
                        }

                        if ($disregard != true && !empty($price_arr['wholesale_price'])) {
                            $have_wholesale_price_products[$product_id][] = $role_key;
                        }

                    } else {

                        $have_wholesale_price = false;

                        if (
                            get_post_meta($product_id, $role_key . '_have_wholesale_price', true) == 'yes' &&
                            get_post_meta($product_id, $role_key . '_have_wholesale_price_set_by_product_cat', true) == 'yes'
                        ) {

                            $have_wholesale_price = true;

                            if (
                                get_post_meta($bundle_id, 'wwpp_ignore_cat_level_wholesale_discount', true) == 'yes' ||
                                get_post_meta($product_id, 'wwpp_ignore_cat_level_wholesale_discount', true) == 'yes'
                            ) {
                                $disregard = true;
                            }

                        } else if (WWPP_Helper_Functions::_wholesale_user_have_general_role_discount($role_key)) {

                            $have_wholesale_price = true;

                            if (
                                get_post_meta($bundle_id, 'wwpp_ignore_role_level_wholesale_discount', true) == 'yes' ||
                                get_post_meta($product_id, 'wwpp_ignore_role_level_wholesale_discount', true) == 'yes'
                            ) {
                                $disregard = true;
                            }

                        }

                        if ($have_wholesale_price && $disregard != true) {
                            $have_wholesale_price_products[$product_id][] = $role_key;
                        }

                    }

                }

            }

            if (!empty($have_wholesale_price_products)) {

                delete_post_meta($bundle_id, '_children_has_no_wholesale_prices');

                foreach ($have_wholesale_price_products as $pid => $roles) {

                    foreach ($roles as $role) {
                        update_post_meta($bundle_id, $role . '_have_wholesale_price', 'yes');
                    }

                }

            } else {

                update_post_meta($bundle_id, '_children_has_no_wholesale_prices', 'yes');

                foreach ($wholesale_roles as $role_key => $data) {
                    update_post_meta($bundle_id, $role_key . '_have_wholesale_price', 'no');
                }

            }

        }

        /**
         * Excluded Bundle Products.
         * Check if the bundle is priced individually, bundle has wholesale price but the childrens don't have.
         * The bundle should not be visible if Only show.. option is enabled.
         *
         * @since 1.24.6
         * @access public
         */
        public function excluded_bundle_products()
        {

            // Get bundled products.
            $bundle_args = array(
                'type'   => 'bundle',
                'return' => 'ids',
            );

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            $bundled_products    = wc_get_products($bundle_args);
            $excluded_bundles    = array();

            if (!empty($bundled_products)) {

                foreach ($bundled_products as $bundle_product_id) {

                    $children_has_wholesale_prices = get_post_meta($bundle_product_id, '_children_has_no_wholesale_prices', true);

                    if ($children_has_wholesale_prices == 'no') {
                        $excluded_bundles[] = $bundle_product_id;
                    }

                }

            }

            return apply_filters('wwpp_excluded_bundle_products', $excluded_bundles);

        }

        /**
         * Re-initialize visibility meta when removing or adding new general wholesale discount.
         *
         * @since 1.24.7
         * @access public
         *
         * @param array $bundled_item Bundle Item.
         */
        public function re_initialize_visibility_meta($bundled_item)
        {

            global $wc_wholesale_prices_premium;
            $wc_wholesale_prices_premium->wwpp_bootstrap->initialize_product_visibility_filter_meta();

        }

        /**
         * Filter bundle item pricing on initialize hooks. Prevents warning from popping up when wholesale prices are from category or general discount.
         *
         * @since 1.24.7
         * @access public
         *
         * @param array $bundled_item Bundle Item.
         */
        public function filter_bundle_item_pricing($bundled_item)
        {

            add_filter('woocommerce_bundled_item_price', array($this, 'filter_bundle_item_price'), 10, 4);

        }

        /**
         * Override wholesale price display. Update wholesale price based on additional discount set per bundled item.
         * Note: Category and General discount is being discounted more than once. Reason why the displayed price is odd.
         *
         * @since 1.26.2
         * @access public
         *
         * @param string        $wholesale_price_html           Wholesale Price HTML.
         * @param string        $price                          Product Price.
         * @param WC_Product    $product                        Product Object
         * @param array         $user_wholesale_role            Wholesale Role
         * @param string        $wholesale_price_title_text     Wholesale Price Text
         * @param int           $raw_wholesale_price            Raw Wholesale Price
         * @param string        $source                         Wholesale Discount Source
         * @access public
         * @return string
         */
        public function filter_wholesale_price_html($wholesale_price_html, $price, $product, $user_wholesale_role, $wholesale_price_title_text, $raw_wholesale_price, $source)
        {

            global $WOOCS;

            if (property_exists($product, 'wholesale_price_data')) {

                $wholesale_price_data = $product->wholesale_price_data;
                $wholesale_price      = '';

                if (isset($wholesale_price_data['wholesale_price']) && $wholesale_price_data['wholesale_price'] > 0 && property_exists($product, 'discounted_wholesale_price') && $product->discounted_wholesale_price > 0) {

                    if ($WOOCS || $wholesale_price_data['source'] !== 'per_product_level') {

                        remove_filter('woocommerce_bundled_item_price', array($this, 'filter_bundle_item_price'), 10, 4);
                        $wholesale_data  = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3(WWP_Helper_Functions::wwp_get_product_id($product), $user_wholesale_role);
                        $wholesale_price = $wholesale_data['wholesale_price_raw'];

                    } else {
                        $wholesale_price = $product->discounted_wholesale_price;
                    }

                    $regular_price = apply_filters('wwp_pass_wholesale_price_through_taxing', $product->get_regular_price(), WWP_Helper_Functions::wwp_get_product_id($product), $user_wholesale_role);
                    $item_price    = apply_filters('wwp_pass_wholesale_price_through_taxing', $product->bundled_item_price, WWP_Helper_Functions::wwp_get_product_id($product), $user_wholesale_role);
                    $price         = wc_format_sale_price(wc_price($regular_price), wc_price($item_price));

                } else {

                    remove_filter('woocommerce_bundled_item_price', array($this, 'filter_bundle_item_price'), 10, 4);
                    $wholesale_data  = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3(WWP_Helper_Functions::wwp_get_product_id($product), $user_wholesale_role);
                    $wholesale_price = $wholesale_data['wholesale_price_raw'];

                    $regular_price = apply_filters('wwp_pass_wholesale_price_through_taxing', $product->get_regular_price(), WWP_Helper_Functions::wwp_get_product_id($product), $user_wholesale_role);
                    $price         = wc_price($regular_price);

                }

                $wholesale_price = apply_filters('wwp_pass_wholesale_price_through_taxing', $wholesale_price, WWP_Helper_Functions::wwp_get_product_id($product), $user_wholesale_role);
                $wholesale_price = WWP_Helper_Functions::wwp_formatted_price($wholesale_price);
                $wholesale_price .= WWP_Wholesale_Prices::get_wholesale_price_suffix($product, $user_wholesale_role, $wholesale_price, false);

                $wholesale_price_html = '<span style="display: block;" class="wholesale_price_container">
                                                    <span class="wholesale_price_title">' . $wholesale_price_title_text . '</span>
                                                    <ins>' . $wholesale_price . '</ins>
                                                </span>';

                return apply_filters('wwpp_bundle_item_wholesale_price', '<del class="original-computed-price">' . $price . $product->get_price_suffix() . '</del>' . $wholesale_price_html);

            }

            return $wholesale_price_html;

        }

        /*
        |--------------------------------------------------------------------------
        | Execute Model
        |--------------------------------------------------------------------------
         */

        /**
         * Execute model.
         *
         * @since 1.13.0
         * @access public
         */
        public function run()
        {

            if (WWP_Helper_Functions::is_plugin_active('woocommerce-product-bundles/woocommerce-product-bundles.php')) {

                add_action('woocommerce_product_options_pricing', array($this, 'add_wholesale_price_fields'), 11);
                add_action('woocommerce_process_product_meta_bundle', array($this, 'save_wholesale_price_fields'), 20, 1);
                add_action('woocommerce_process_product_meta_bundle', array($this, 'save_minimum_order_quantity_fields'), 20, 1);
                add_action('woocommerce_process_product_meta_bundle', array($this, 'save_order_quantity_step_fields'), 20, 1);

                add_filter('woocommerce_bundle_price_data', array($this, 'filter_bundle_product_base_price'), 10, 2);

                add_filter('woocommerce_bundled_items', array($this, 'filter_bundled_items'), 10, 2);

                add_action('woocommerce_bundled_single_variation', array($this, 'filter_variable_bundle_variations_data'), 10, 2);
                add_action('woocommerce_bundled_single_variation', array($this, 'filter_per_variation_price_html'), 10, 2);

                // WC 2.7
                add_filter('wwp_third_party_product_id', array($this, 'get_product_id'), 10, 2);

                // Quick edit support
                add_filter('wwp_quick_edit_allowed_product_types', array($this, 'support_for_quick_edit_fields'), 10, 2);

                // Only perform setting wholesale price if bundle pricing filters are set
                add_action('woocommerce_bundled_product_price_filters_added', array($this, 'filter_bundle_item_pricing'), 10, 1);

                // On save post, set visibility meta
                add_action('save_post', array($this, 'set_bundle_product_visibility_meta'), 20, 1);

                // Re-initialize visibility meta when general discount is removed or added
                add_action('wwpp_add_wholesale_role_general_discount_mapping', array($this, 're_initialize_visibility_meta'), 10, 1);
                add_action('wwpp_delete_wholesale_role_general_discount_mapping', array($this, 're_initialize_visibility_meta'), 10, 1);

                // Apply additional discount to wholesale price if Bundle Item have Discount set in the bundle item setting.
                add_filter('wwp_filter_wholesale_price_html', array($this, 'filter_wholesale_price_html'), 5, 7);

            }

        }

    }

}