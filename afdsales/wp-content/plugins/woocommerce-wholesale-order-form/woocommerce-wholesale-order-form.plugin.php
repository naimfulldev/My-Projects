<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

require_once WWOF_INCLUDES_ROOT_DIR . 'class-wwof-wws-license-manager.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'class-wwof-wws-update-manager.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'class-wwof-scripts.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'class-wwof-bootstrap.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'class-wwof-permissions.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'class-wwof-shortcode.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'class-wwof-aelia-currency-switcher-integration-helper.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'class-wwof-product-listing-helper.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'class-wwof-product-listing.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'class-wwof-wwp-wholesale-prices.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'class-wwof-ajax.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'class-wwof-cache.php';

// V2
require_once WWOF_INCLUDES_ROOT_DIR . 'v2/class-order-form-scripts.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'v2/class-order-form-cpt.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'v2/class-order-form-shortcode.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'v2/class-order-form-requirements.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'v2/class-order-form-db.php';

// V2 API
require_once WWOF_INCLUDES_ROOT_DIR . 'v2/api/class-order-form-api-request.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'v2/api/controllers/class-rest-api-order-form-controller.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'v2/api/controllers/class-rest-api-settings-controller.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'v2/api/controllers/class-rest-api-order-form-api-keys-controller.php';
require_once WWOF_INCLUDES_ROOT_DIR . 'v2/api/controllers/class-rest-api-i18n-controller.php';

// WWOF API Helpers
require_once WWOF_INCLUDES_ROOT_DIR . 'v2/api/helpers/class-order-form-api-helpers.php';

// Third Party Plugin Integration
require_once WWOF_INCLUDES_ROOT_DIR . 'plugin-integrations/class-wwof-woocommerce-multilingual.php';

class WooCommerce_WholeSale_Order_Form
{

    /*
    |------------------------------------------------------------------------------------------------------------------
    | Class Members
    |------------------------------------------------------------------------------------------------------------------
     */
    private static $_instance;

    public $_wwof_license_manager;
    public $_wwof_update_manager;
    public $_wwof_scripts;
    public $_wwof_bootstrap;
    public $_wwof_ajax;
    public $_wwof_shortcode;
    public $_wwof_product_listings;
    public $_wwof_permissions;
    public $_wwof_wwp_wholesale_prices;
    public $_wwof_cache;

    // V2
    public $_order_form_cpt;
    public $_order_form_scripts;
    public $_order_form_shortcode;
    public $_order_form_requirements;

    // Third party plugin integrations
    public $_wwof_woocommerce_multilingual;

    const VERSION = '1.21.1';

    /*
    |------------------------------------------------------------------------------------------------------------------
    | Mesc Functions
    |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {

        $this->_wwof_license_manager = WWOF_WWS_License_Manager::instance();
        $this->_wwof_update_manager  = WWOF_WWS_Update_Manager::instance();
        $this->_wwof_scripts         = WWOF_Scripts::instance(array(
            'WWOF_CURRENT_VERSION' => self::VERSION,
        ));
        $this->_wwof_permissions      = WWOF_Permissions::instance();
        $this->_wwof_product_listings = WWOF_Product_Listing::instance(array(
            'WWOF_Permissions' => $this->_wwof_permissions,
        ));
        $this->_wwof_wwp_wholesale_prices = WWOF_WWP_Wholesale_Prices::instance(array(
            'WWOF_Product_Listing' => $this->_wwof_product_listings,
        ));
        $this->_wwof_ajax = WWOF_AJAX::instance(array(
            'WWOF_Product_Listing'      => $this->_wwof_product_listings,
            'WWOF_Permissions'          => $this->_wwof_permissions,
            'WWOF_WWP_Wholesale_Prices' => $this->_wwof_wwp_wholesale_prices,
        ));
        $this->_wwof_bootstrap = WWOF_Bootstrap::instance(array(
            'WWOF_CURRENT_VERSION' => self::VERSION,
            'WWOF_AJAX'            => $this->_wwof_ajax,
        ));
        $this->_wwof_shortcode = WWOF_Shortcode::instance(array(
            'WWOF_Product_Listing' => $this->_wwof_product_listings,
        ));
        $this->_wwof_cache = WWOF_Cache::instance(array());

        // Multilingual Compat
        $this->_wwof_woocommerce_multilingual = WWOF_WooCommerce_Multilingual::instance(array());

        // V2
        $this->_order_form_cpt          = Order_Form_CPT::instance(array());
        $this->_order_form_scripts      = Order_Form_Scripts::instance(array('WWOF_CURRENT_VERSION' => self::VERSION));
        $this->_order_form_shortcode    = Order_Form_Shortcode::instance(array('WWOF_Product_Listing' => $this->_wwof_product_listings));
        $this->_order_form_requirements = Order_Form_Requirements::instance(array());

    }

    /**
     * Singleton Pattern.
     *
     * @since 1.0.0
     *
     * @return WooCommerce_WholeSale_Order_Form
     */
    public static function instance()
    {

        if (!self::$_instance instanceof self) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /*
    |------------------------------------------------------------------------------------------------------------------
    | Settings
    |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Initialize plugin settings.
     *
     * @param $settings
     *
     * @return array
     * @since 1.0.0
     * @since 1.6.6 Underscore cased the function name and variables.
     */
    public function wwof_plugin_settings($settings)
    {

        $settings[] = include WWOF_INCLUDES_ROOT_DIR . 'class-wwof-settings.php';

        return $settings;

    }

    /*
    |------------------------------------------------------------------------------------------------------------------
    | Deprecated methods as of version 1.6.6
    |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Get product thumbnail dimension.
     *
     * @deprecated 1.6.6
     *
     * @return array
     */
    public function getProductThumbnailDimension()
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getProductThumbnailDimension', '1.6.6', 'WWOF_Product_Listing::wwof_get_product_thumbnail_dimension');
        return $this->_wwof_product_listings->wwof_get_product_thumbnail_dimension();

    }

    /**
     * Get product meta.
     *
     * @deprecated 1.6.6
     *
     * @param $product
     * @return mixed
     */
    public function getProductMeta($product)
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getProductMeta', '1.6.6', 'WWOF_Product_Listing::wwof_get_product_meta');
        return $this->_wwof_product_listings->wwof_get_product_meta($product);

    }

    /**
     * Get product thumbnail.
     *
     * @deprecated 1.6.6
     *
     * @param $product
     * @param $permalink
     * @param $imageSize
     * @return string
     */
    public function getProductImage($product, $permalink, $imageSize)
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getProductImage', '1.6.6', 'WWOF_Product_Listing::wwof_get_product_image');
        return $this->_wwof_product_listings->wwof_get_product_image($product, $permalink, $image_size);

    }

    /**
     * Get product title.
     *
     * @deprecated 1.6.6
     *
     * @param $product
     * @param $permalink
     * @return string
     */
    public function getProductTitle($product, $permalink)
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getProductTitle', '1.6.6', 'WWOF_Product_Listing::wwof_get_product_title');
        return $this->_wwof_product_listings->wwof_get_product_title($product, $permalink);

    }

    /**
     * Get product variation field.
     *
     * @deprecated 1.6.6
     *
     * @param $product
     * @return string
     */
    public function getProductVariationField($product)
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getProductVariationField', '1.6.6', 'WWOF_Product_Listing::wwof_get_product_variation_field');
        return $this->_wwof_product_listings->wwof_get_product_variation_field($product);

    }

    /**
     * Get product add-ons.
     *
     * @deprecated 1.6.6
     *
     * @param $product
     * @return string;
     */
    public function getProductAddons($product)
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getProductAddons', '1.6.6', 'WWOF_Product_Listing::wwof_get_product_addons');
        return $this->_wwof_product_listings->wwof_get_product_addons($product);

    }

    /**
     * Get product sku.
     *
     * @deprecated 1.6.6
     *
     * @param $product
     * @return string
     */
    public function getProductSku($product)
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getProductSku', '1.6.6', 'WWOF_Product_Listing::wwof_get_product_sku');
        return $this->_wwof_product_listings->wwof_get_product_sku($product);

    }

    /**
     * Return product sku visibility classes.
     *
     * @deprecated 1.6.6
     *
     * @return mixed
     */
    public function getProductSkuVisibilityClass()
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getProductSkuVisibilityClass', '1.6.6', 'WWOF_Product_Listing::wwof_get_product_sku_visibility_class');
        return $this->_wwof_product_listings->wwof_get_product_sku_visibility_class();

    }

    /**
     * Get product price.
     *
     * @deprecated 1.6.6
     *
     * @param $product
     * @return string
     */
    public function getProductPrice($product)
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getProductPrice', '1.6.6', 'WWOF_WWP_Wholesale_Prices::wwof_get_product_price');
        return $this->_wwof_wwp_wholesale_prices->wwof_get_product_price($product);

    }

    /**
     * Get product stock quantity.
     *
     * @deprecated 1.6.6
     *
     * @param $product
     * @return string
     */
    public function getProductStockQuantity($product)
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getProductStockQuantity', '1.6.6', 'WWOF_Product_Listing::wwof_get_product_stock_quantity');
        return $this->_wwof_product_listings->wwof_get_product_stock_quantity($product);

    }

    /**
     * Return product stock quantity visibility class.
     *
     * @deprecated 1.6.6
     *
     * @return mixed
     */
    public function getProductStockQuantityVisibilityClass()
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getProductStockQuantityVisibilityClass', '1.6.6', 'WWOF_Product_Listing::wwof_get_product_stock_quantity_visibility_class');
        return $this->_wwof_product_listings->wwof_get_product_stock_quantity_visibility_class();

    }

    /**
     * Get product quantity field.
     *
     * @deprecated 1.6.6
     *
     * @param $product
     * @return string
     */
    public function getProductQuantityField($product)
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getProductQuantityField', '1.6.6', 'WWOF_WWP_Wholesale_Prices::wwof_get_product_quantity_field');
        return $this->_wwof_wwp_wholesale_prices->wwof_get_product_quantity_field($product);

    }

    /**
     * Get product row actions fields.
     *
     * @deprecated 1.6.6
     *
     * @param $product
     * @param $alternate
     * @return string
     */
    public function getProductRowActionFields($product, $alternate = false)
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getProductRowActionFields', '1.6.6', 'WWOF_Product_Listing::wwof_get_product_row_action_fields');
        return $this->_wwof_product_listings->wwof_get_product_row_action_fields($product, $alternate);

    }

    /**
     * Get cart sub total (including/excluding) tax.
     *
     * @deprecated 1.6.6
     *
     * @return string
     */
    public function getCartSubtotal()
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getCartSubtotal', '1.6.6', 'WWOF_Product_Listing::wwof_get_gallery_listing_pagination');
        return $this->_wwof_product_listings->wwof_get_cart_subtotal();

    }

    /**
     * Get wholesale product listing pagination.
     *
     * @deprecated 1.6.6
     *
     * @param $paged
     * @param $max_num_pages
     * @param $search
     * @param $cat_filter
     * @return mixed
     */
    public function getGalleryListingPagination($paged, $max_num_pages, $search, $cat_filter)
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getGalleryListingPagination', '1.6.6', 'WWOF_Product_Listing::wwof_get_gallery_listing_pagination');
        return $this->_wwof_product_listings->wwof_get_gallery_listing_pagination($paged, $max_num_pages, $search, $cat_filter);

    }

    /**
     * Check if site user has access to view the wholesale product listing page
     *
     * since 1.0.0
     *
     * @return bool
     */
    public function userHasAccess()
    {

        WWOF_Functions::deprecated_function(debug_backtrace(), 'WooCommerce_WholeSale_Order_Form::getGalleryListingPagination', '1.6.6', 'WWOF_Permissions::wwof_user_has_access');
        return $this->_wwof_permissions->wwof_user_has_access();

    }

    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Execution WWOF
    |
    | This will be the new way of executing the plugin.
    |-------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Execute WWOF. Triggers the execution codes of the plugin models.
     *
     * @since 1.6.6
     * @since 1.14  Custom API Endpoint for WWOF so we can override WP_Query args specific to WWOF needs.
     * @access public
     */
    public function run()
    {

        $this->_wwof_license_manager->run();
        $this->_wwof_update_manager->run();
        $this->_wwof_scripts->run();
        $this->_wwof_bootstrap->run();
        $this->_wwof_ajax->run();
        $this->_wwof_shortcode->run();
        $this->_wwof_wwp_wholesale_prices->run();
        $this->_wwof_cache->run();

        // Third party plugin integrations
        $this->_wwof_woocommerce_multilingual->run();

        // V2
        $this->_order_form_cpt->run();
        $this->_order_form_scripts->run();
        $this->_order_form_shortcode->run();
        $this->_order_form_requirements->run();

    }

}
