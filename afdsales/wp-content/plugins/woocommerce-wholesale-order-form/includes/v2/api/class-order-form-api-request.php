<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

require WWOF_PLUGIN_DIR . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

if (!class_exists('WWOF_API_Request')) {

    class WWOF_API_Request
    {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * Products Per Page
         *
         * @since 1.19
         * @access private
         */
        private $products_per_page = 10;

        /**
         * Products Per Page
         *
         * @since 1.19
         * @access private
         */
        private $categories_per_page = 100;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        public function __construct()
        {

            // Get Products
            add_action('wp_ajax_nopriv_wwof_api_get_products', array($this, 'get_products'));
            add_action('wp_ajax_wwof_api_get_products', array($this, 'get_products'));

            // Get Product categories
            add_action('wp_ajax_nopriv_wwof_api_get_categories', array($this, 'get_categories'));
            add_action('wp_ajax_wwof_api_get_categories', array($this, 'get_categories'));

            // Regular Variations
            add_action('wp_ajax_nopriv_wwof_api_get_variations', array($this, 'get_variations'));
            add_action('wp_ajax_wwof_api_get_variations', array($this, 'get_variations'));

            // Wholesale Variations
            add_action('wp_ajax_nopriv_wwof_api_get_wholesale_variations', array($this, 'get_wholesale_variations'));
            add_action('wp_ajax_wwof_api_get_wholesale_variations', array($this, 'get_wholesale_variations'));

            // Sort by sku is not supported by WC API so we will make our own integration.
            add_filter('rest_product_collection_params', array($this, 'insert_sku_collection_param'), 10, 2);
            add_filter('woocommerce_get_catalog_ordering_args', array($this, 'add_sku_sorting'), 10, 3);

            // Update add to cart response for new OF inc/excl tax.
            add_filter('wwof_ajax_add_to_cart_response', array($this, 'update_cart_subtotal'));

            // Quantity Restriction Toggle Off/On
            add_filter('wwof_quantity_validation', array($this, 'toggle_quantity_restriction'));

            // Show variations individually
            add_filter('wwp_rest_wholesale_products', array($this, 'show_variations_individually'), 10, 2);

            // Alter pre_get_posts API request
            add_action('pre_get_posts', array($this, 'pre_get_posts_api_request'));

            // Order Form Search
            add_filter('woocommerce_rest_product_object_query', array($this, 'order_form_search'), 10, 2);

        }

        /**
         * Get products. If user is wholesale customer then use wwpp api else use custom wwof api endpoint.
         *
         * @since 1.15
         * @return array
         */
        public function get_products()
        {

            try {

                $user_roles = Order_Form_Helpers::get_user_roles();

                // User is admin and shop managers then show all products using wc products endpoint
                if (!empty($user_roles) && (in_array('administrator', $user_roles) || in_array('shop_manager', $user_roles))) {
                    $this->get_regular_products();
                } else {

                    $wholesale_role = WWOF_API_Helpers::is_wholesale_customer();

                    if (empty($wholesale_role) && isset($_POST['wholesale_role'])) {
                        $wholesale_role = $_POST['wholesale_role'];
                    }

                    $wwp_data         = Order_Form_Helpers::get_wwp_data();
                    $wwpp_data        = Order_Form_Helpers::get_wwpp_data();
                    $wwp_min_version  = Order_Form_Requirements::MIN_WWP_VERSION;
                    $wwpp_min_version = Order_Form_Requirements::MIN_WWPP_VERSION;

                    $wholesale_role = sanitize_text_field($wholesale_role);

                    // WWP and WWPP Min Requirement
                    if ($wwp_data &&
                        Order_Form_Helpers::is_wwp_active() &&
                        Order_Form_Helpers::is_wwpp_active() &&
                        version_compare($wwp_data['Version'], $wwp_min_version, '>=') &&
                        version_compare($wwpp_data['Version'], $wwpp_min_version, '>=')
                    ) {
                        $this->get_wholesale_products($wholesale_role);
                    } else {
                        $this->get_regular_products();
                    }

                }

            } catch (HttpClientException $e) {

                $this->get_regular_products();

            }

        }

        /**
         * Get regular products using WWOF API custom endpoint.
         *
         * @since 1.15
         * @return array
         */
        public function get_regular_products()
        {

            try {

                $api_keys = Order_Form_API_KEYS::get_keys();

                $woocommerce = new Client(
                    site_url(),
                    $api_keys['consumer_key'],
                    $api_keys['consumer_secret'],
                    [
                        'version'           => 'wc/v3',
                        'query_string_auth' => is_ssl() ? true : false,
                        'verify_ssl'        => false,
                        'wp_api'            => true,
                    ]
                );

                // Search Text
                $search = isset($_POST['search']) ? $_POST['search'] : '';
                $search = sanitize_text_field($search);

                // Filter by category
                $cat_obj     = isset($_POST['category']) && !empty($_POST['category']) ? get_term_by('slug', $_POST['category'], 'product_cat') : '';
                $category_id = is_a($cat_obj, 'WP_Term') ? $cat_obj->term_id : '';

                // Show Variations Individually feature
                $show_variations_individually = isset($_POST['form_settings']) && isset($_POST['form_settings']['show_variations_individually']) ? $_POST['form_settings']['show_variations_individually'] : false;

                // Allow SKU Search
                $allow_sku_search = isset($_POST['allow_sku_search']) && boolval($_POST['allow_sku_search']) == 'true' ? 'yes' : 'no';

                // Show Zero Inventory
                $show_zero_inventory = isset($_POST['form_settings']) && isset($_POST['form_settings']['show_zero_inventory_products']) && $_POST['form_settings']['show_zero_inventory_products'] == 'true' ? 'yes' : 'no';

                $args = array(
                    'per_page'                     => isset($_POST['per_page']) ? $_POST['per_page'] : $this->products_per_page,
                    'search'                       => $search,
                    'category'                     => $category_id ? $category_id : WWOF_API_Helpers::filtered_categories(isset($_POST['form_settings']) ? $_POST['form_settings'] : []),
                    'page'                         => isset($_POST['page']) ? $_POST['page'] : 1,
                    'order'                        => isset($_POST['sort_order']) && !empty($_POST['sort_order']) ? $_POST['sort_order'] : 'desc',
                    'orderby'                      => isset($_POST['sort_by']) && !empty($_POST['sort_by']) ? $_POST['sort_by'] : 'date',
                    'status'                       => 'publish',
                    'show_categories'              => true,
                    'show_variations_individually' => $show_variations_individually == 'true' ? 'yes' : 'no', // Only used in WWOF
                    'allow_sku_search'             => $allow_sku_search, // Only used in WWOF
                    'show_zero_inventory'          => $show_zero_inventory // Only used in WWOF
                );

                if (!empty($_POST['products'])) {
                    if (!empty($args['include'])) {
                        $args['include'] = array_merge($args['include'], $_POST['products']);
                    } else {
                        $args['include'] = explode(',', $_POST['products']);
                    }

                }

                if (get_option('wwof_filters_product_category_filter')) {
                    $products_to_include = WWOF_API_Helpers::include_products_from_category();
                    if (!empty($args['include'])) {
                        $args['include'] = array_merge($args['include'], $products_to_include);
                    } else {
                        $args['include'] = $products_to_include;
                    }

                } else if (get_option('wwof_filters_exclude_product_filter')) {
                    $args['exclude'] = get_option('wwof_filters_exclude_product_filter');
                }

                if (isset($_POST['searching']) && $_POST['searching'] === 'no' && get_option('wwof_general_default_product_category_search_filter')) {
                    $category = get_term_by('slug', get_option('wwof_general_default_product_category_search_filter'), 'product_cat');
                    if ($category && filter_var($_POST['show_all'], FILTER_VALIDATE_BOOLEAN) !== true) {
                        $args['category'] = $category->term_id;
                    }

                }

                $results = $woocommerce->get('products', $args);

                $response       = $woocommerce->http->getResponse();
                $headers        = WWOF_API_Helpers::get_header_data($response->getHeaders());
                $total_pages    = $headers['total_pages'];
                $total_products = $headers['total_products'];

                wp_send_json(
                    array(
                        'status'                    => 'success',
                        'products'                  => $results,
                        'variations'                => $this->get_variations($results, true),
                        'lazy_load_variations_data' => $this->lazy_load_variations_data($results),
                        'settings'                  => array(),
                        'total_page'                => $total_pages,
                        'total_products'            => $total_products,
                        'cart_subtotal'             => $this->get_cart_subtotal(),
                        'cart_url'                  => wc_get_cart_url(),
                    )
                );

            } catch (HttpClientException $e) {

                wp_send_json(
                    array(
                        'status'  => 'error',
                        'message' => $e->getMessage(), // error
                    )
                );

            }

        }

        /**
         * Get wholesale products using WWPP API custom endpoint.
         * Note: not yet used will use this in the next phase.
         *
         * @since 1.15
         * @return array
         */
        public function get_wholesale_products($wholesale_role)
        {

            try {

                $api_keys = Order_Form_API_KEYS::get_keys();

                $woocommerce = new Client(
                    site_url(),
                    $api_keys['consumer_key'],
                    $api_keys['consumer_secret'],
                    [
                        'version'           => 'wholesale/v1',
                        'query_string_auth' => is_ssl() ? true : false,
                        'verify_ssl'        => false,
                        'wp_api'            => true,
                    ]
                );

                // Search Text
                $search = isset($_POST['search']) ? $_POST['search'] : '';
                $search = sanitize_text_field($search);

                // Filter by category
                $cat_obj     = isset($_POST['category']) && !empty($_POST['category']) ? get_term_by('slug', $_POST['category'], 'product_cat') : '';
                $category_id = is_a($cat_obj, 'WP_Term') ? $cat_obj->term_id : '';

                // Show Variations Individually feature
                $show_variations_individually = isset($_POST['form_settings']) && isset($_POST['form_settings']['show_variations_individually']) ? $_POST['form_settings']['show_variations_individually'] : false;

                // Allow SKU Search
                $allow_sku_search = isset($_POST['allow_sku_search']) && boolval($_POST['allow_sku_search']) == 'true' ? 'yes' : 'no';

                // Show Zero Inventory
                $show_zero_inventory = isset($_POST['form_settings']) && isset($_POST['form_settings']['show_zero_inventory_products']) && $_POST['form_settings']['show_zero_inventory_products'] == 'true' ? 'yes' : 'no';

                $args = array(
                    'wholesale_role'               => $wholesale_role,
                    'per_page'                     => isset($_POST['per_page']) ? $_POST['per_page'] : $this->products_per_page,
                    'search'                       => $search,
                    'category'                     => $category_id ? $category_id : WWOF_API_Helpers::filtered_categories(isset($_POST['form_settings']) ? $_POST['form_settings'] : []),
                    'page'                         => isset($_POST['page']) ? $_POST['page'] : 1,
                    'order'                        => isset($_POST['sort_order']) && !empty($_POST['sort_order']) ? $_POST['sort_order'] : 'desc',
                    'orderby'                      => isset($_POST['sort_by']) && !empty($_POST['sort_by']) ? $_POST['sort_by'] : 'date',
                    'status'                       => 'publish',
                    'show_categories'              => true,
                    'uid'                          => isset($_POST['uid']) ? intval($_POST['uid']) : get_current_user_id(),
                    'show_meta_data'               => true,
                    'show_variations_individually' => $show_variations_individually == 'true' ? 'yes' : 'no', // Only used in WWOF
                    'allow_sku_search'             => $allow_sku_search, // Only used in WWOF
                    'show_zero_inventory'          => $show_zero_inventory // Only used in WWOF
                );

                if (!empty($args['orderby']) && $args['orderby'] == 'sku') {

                    add_filter("woocommerce_rest_product_object_query", function ($args, $request) {

                        $args['orderby_meta_key'] = '_sku';
                        $args['orderby']          = 'meta_value';
                        return $args;
                    }, 10, 2);

                }

                $results = $woocommerce->get('products', $args);

                $response       = $woocommerce->http->getResponse();
                $headers        = WWOF_API_Helpers::get_header_data($response->getHeaders());
                $total_pages    = $headers['total_pages'];
                $total_products = $headers['total_products'];

                wp_send_json(
                    array(
                        'status'                    => 'success',
                        'products'                  => $results,
                        'variations'                => $this->get_wholesale_variations($results, $wholesale_role, true),
                        'lazy_load_variations_data' => $this->lazy_load_variations_data($results, $wholesale_role),
                        'settings'                  => array(),
                        'total_page'                => $total_pages,
                        'total_products'            => $total_products,
                        'cart_subtotal'             => $this->get_cart_subtotal(),
                        'cart_url'                  => wc_get_cart_url(),
                    )
                );

            } catch (HttpClientException $e) {

                wp_send_json(array(
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ));

            }

        }

        /**
         * Get categories via WC API.
         *
         * @since 1.15
         * @return array
         */
        public function get_categories()
        {

            try {

                $api_keys = Order_Form_API_KEYS::get_keys();

                $woocommerce = new Client(
                    site_url(),
                    $api_keys['consumer_key'],
                    $api_keys['consumer_secret'],
                    [
                        'version'           => 'wc/v3',
                        'query_string_auth' => is_ssl() ? true : false,
                        'verify_ssl'        => false,
                        'wp_api'            => true,
                    ]
                );

                $args = array(
                    'per_page' => $this->categories_per_page,
                );

                // WWOF Product Category Filter Option
                $categories = get_option('wwof_filters_product_category_filter');
                $cat_ids    = array();
                if ($categories) {
                    foreach ($categories as $slug) {
                        $category = get_term_by('slug', $slug, 'product_cat');
                        if ($category) {
                            $cat_ids[] = $category->term_id;
                        }

                    }
                    if ($cat_ids) {
                        $args['include'] = $cat_ids;
                    }

                }

                // WWOF Product Categories Shortcode Attribute
                if (!empty($_POST['categories']) && is_string($_POST['categories'])) {
                    if (!empty($args['include'])) {
                        $args['include'] = array_merge($args['include'], explode(',', $_POST['categories']));
                    } else {
                        $args['include'] = explode(',', $_POST['categories']);
                    }
                }

                $results = $woocommerce->get('products/categories', $args);

                $category_hierarchy = array();
                WWOF_API_Helpers::assign_category_children($results, $category_hierarchy);

                wp_send_json(
                    array(
                        'status'     => 'success',
                        'categories' => $category_hierarchy,
                    )
                );

            } catch (HttpClientException $e) {

                wp_send_json(
                    array(
                        'status'  => 'error',
                        'message' => $e->getMessage(),
                    )
                );

            }

        }

        /**
         * Get product variations via WC API endpoint.
         *
         * @since 1.15
         * @param array $products
         * @return array
         */
        public function get_variations($products = array())
        {

            $variations = array();
            $api_keys   = Order_Form_API_KEYS::get_keys();

            $woocommerce = new Client(
                site_url(),
                $api_keys['consumer_key'],
                $api_keys['consumer_secret'],
                [
                    'version'           => 'wc/v3',
                    'query_string_auth' => is_ssl() ? true : false,
                    'verify_ssl'        => false,
                    'wp_api'            => true,
                ]
            );

            // Fetch variations per variable product LIMIT 20
            if (!empty($products)) {

                // Fetch all variations per variable product
                foreach ($products as $product) {

                    if ($product->type === 'variable') {

                        try {

                            $args = array(
                                'orderby'  => 'menu_order',
                                'order'    => 'asc',
                                'per_page' => $this->get_variations_per_page(),
                            );

                            $results = $woocommerce->get('products/' . $product->id . '/variations', $args);

                            if ($results) {

                                foreach ($results as $index => $variation) {
                                    $variation_obj          = wc_get_product($variation->id);
                                    $results[$index]->price = $variation_obj->get_price_html();
                                }

                                $variations[$product->id] = $results;

                            }

                        } catch (HttpClientException $e) {

                            // Prints Error: Not a wholesale product. [wholesale_rest_cannot_view]
                            // We won't log any error message here just to avoid confusion.
                            // Only use error log when debuggin issues.

                        }

                    }

                }

                return $variations;

            } else if (isset($_POST['product_id'])) {

                // Lazy Loading on scroll combo variation
                try {

                    $current_page = sanitize_text_field($_POST['current_page']);
                    $product_id   = sanitize_text_field($_POST['product_id']);

                    $args = array(
                        'orderby'  => 'menu_order',
                        'order'    => 'asc',
                        'status'   => 'publish',
                        'page'     => $current_page,
                        'per_page' => $this->get_variations_per_page(),
                    );

                    $results = $woocommerce->get('products/' . $product_id . '/variations', $args);

                    if ($results) {

                        foreach ($results as $index => $variation) {
                            $variation_obj          = wc_get_product($variation->id);
                            $results[$index]->price = $variation_obj->get_price_html();
                        }

                        $variations = $results;

                    }

                } catch (HttpClientException $e) {

                    // Prints Error: Not a wholesale product. [wholesale_rest_cannot_view]
                    // We won't log any error message here just to avoid confusion.
                    // Only use error log when debuggin issues.

                }

                if (defined('DOING_AJAX') && DOING_AJAX) {

                    wp_send_json(
                        array(
                            'status'     => 'success',
                            'variations' => $variations,
                        )
                    );

                }

            }

        }

        /**
         * Get Wholesale Variations.
         *
         * @since 1.16
         * @return array
         */
        public function get_wholesale_variations($products = array(), $wholesale_role = "")
        {

            $variations = array();
            $api_keys   = Order_Form_API_KEYS::get_keys();

            $woocommerce = new Client(
                site_url(),
                $api_keys['consumer_key'],
                $api_keys['consumer_secret'],
                [
                    'version'           => 'wholesale/v1',
                    'query_string_auth' => is_ssl() ? true : false,
                    'verify_ssl'        => false,
                    'wp_api'            => true,
                ]
            );

            // Fetch variations per variable product LIMIT 20
            if (!empty($products)) {
                foreach ($products as $product) {
                    if ($product->type === 'variable') {
                        try {

                            $args = array(
                                'orderby'        => 'menu_order',
                                'order'          => 'asc',
                                'wholesale_role' => $wholesale_role,
                                'uid'            => isset($_POST['uid']) ? intval($_POST['uid']) : get_current_user_id(),
                                'per_page'       => $this->get_variations_per_page(),
                                'show_meta_data' => true,
                            );

                            $results = $woocommerce->get('products/' . $product->id . '/variations', $args);

                            if ($results) {

                                foreach ($results as $index => $variation) {
                                    $variation_obj          = wc_get_product($variation->id);
                                    $results[$index]->price = $variation_obj->get_price_html();
                                }

                                $variations[$product->id] = $results;
                            }

                        } catch (HttpClientException $e) {

                            // Prints Error: Not a wholesale product. [wholesale_rest_cannot_view]
                            // We won't log any error message here just to avoid confusion.
                            // Only use error log when debuggin issues.
                            // error_log(print_r($e->getMessage(), true));

                        }
                    }
                }

                return $variations;

            } else if (isset($_POST['product_id'])) {
                // Lazy Loading on scroll combo variation

                try {

                    $current_page   = sanitize_text_field($_POST['current_page']);
                    $uid            = isset($_POST['uid']) ? sanitize_text_field($_POST['uid']) : get_current_user_id();
                    $product_id     = sanitize_text_field($_POST['product_id']);
                    $wholesale_role = isset($_POST['wholesale_role']) ? sanitize_text_field($_POST['wholesale_role']) : $wholesale_role;

                    $args = array(
                        'orderby'        => 'menu_order',
                        'order'          => 'asc',
                        'wholesale_role' => $wholesale_role,
                        'uid'            => intval($uid),
                        'per_page'       => $this->get_variations_per_page(),
                        'page'           => $current_page,
                        'show_meta_data' => true,
                    );

                    $results = $woocommerce->get('products/' . $product_id . '/variations', $args);

                    if ($results) {

                        foreach ($results as $index => $variation) {
                            $variation_obj          = wc_get_product($variation->id);
                            $results[$index]->price = $variation_obj->get_price_html();
                        }

                        $variations = $results;
                    }

                } catch (HttpClientException $e) {
                    // Prints Error: Not a wholesale product. [wholesale_rest_cannot_view]
                    // We won't log any error message here just to avoid confusion.
                    // Only use error log when debuggin issues.
                    // error_log(print_r($e->getMessage(), true));
                }

                if (defined('DOING_AJAX') && DOING_AJAX) {

                    wp_send_json(
                        array(
                            'status'     => 'success',
                            'variations' => $variations,
                        )
                    );

                }

            }

        }

        /**
         * Get cart subtotal.
         *
         * @since 1.16
         * @return string
         */
        public function get_cart_subtotal()
        {

            global $wc_wholesale_prices_premium;

            if (isset($_REQUEST['form_settings']) && !empty($_REQUEST['form_settings']['tax_display'])) {
                $tax_display = $_REQUEST['form_settings']['tax_display'];
            } else {
                // Always use the WC setting "Display prices in the shop" if no override is set in Subtotal Tax Display component
                if ($wc_wholesale_prices_premium) {
                    remove_filter('option_woocommerce_tax_display_shop', array($wc_wholesale_prices_premium->wwpp_tax, 'wholesale_tax_display_shop'), 10, 1);
                }

                $tax_display = get_option('woocommerce_tax_display_shop');

                if ($wc_wholesale_prices_premium) {
                    add_filter('option_woocommerce_tax_display_shop', array($wc_wholesale_prices_premium->wwpp_tax, 'wholesale_tax_display_shop'), 10, 1);
                }

            }

            $subtotal_pretext = isset($_REQUEST['form_settings']) && !empty($_REQUEST['form_settings']['subtotal_pretext']) ? $_REQUEST['form_settings']['subtotal_pretext'] : '';
            $subtotal_suffix  = isset($_REQUEST['form_settings']) && !empty($_REQUEST['form_settings']['subtotal_suffix']) ? $_REQUEST['form_settings']['subtotal_suffix'] : '';

            ob_start();

            if (!empty(WC()->cart) && WC()->cart->get_cart_contents_count()) {

                switch ($tax_display) {
                    case 'excl':
                        $subtotal_suffix = !empty($subtotal_suffix) ? $subtotal_suffix : WC()->countries->ex_tax_or_vat();
                        echo wp_sprintf('%s %s <small> %s</small>', $subtotal_pretext, wc_price(WC()->cart->cart_contents_total), $subtotal_suffix);
                        break;
                    case 'incl':
                        $subtotal_suffix = !empty($subtotal_suffix) ? $subtotal_suffix : WC()->countries->inc_tax_or_vat();
                        echo wp_sprintf('%s %s <small> %s</small>', $subtotal_pretext, wc_price(WC()->cart->cart_contents_total + WC()->cart->tax_total), $subtotal_suffix);

                }

            }

            return ob_get_clean();

        }

        /**
         * Set additional param to WWP API request to handle sort by sku.
         *
         * @param array $params
         * @param string $post_type
         * @since 1.17
         * @return array
         */
        public function insert_sku_collection_param($params, $post_type)
        {

            $params['orderby']['enum'][] = 'sku';

            return $params;
        }

        /**
         * Add sort by sku.
         *
         * @param array $args
         * @param string $orderby
         * @param string $order
         * @since 1.20
         * @return array
         */
        public function add_sku_sorting($args, $orderby, $order)
        {

            $orderby_value = isset($_GET['orderby']) ? wc_clean($_GET['orderby']) : '';

            if ('sku' == $orderby_value) {
                add_filter('posts_clauses', array($this, 'sku_sorting_query'));
            }

            return $args;
        }

        /**
         * Sort by sku query.
         *
         * @param array $args
         * @since 1.20
         * @return array
         */
        public function sku_sorting_query($args)
        {

            global $wpdb;

            $order = isset($_GET['order']) ? wc_clean($_GET['order']) : 'ASC';

            $args['join'] .= " LEFT JOIN {$wpdb->postmeta} wwof_pm1 ON ( $wpdb->posts.ID = wwof_pm1.post_id && wwof_pm1.meta_key = '_sku' ) ";
            $args['orderby'] = " wwof_pm1.meta_value $order ";

            return $args;

        }

        /**
         * Update add to cart response for new OF inc/excl tax.
         * New OF should not be based on the old setting but the new option in subtotal component tax display option.
         *
         * @param array $args
         * @since 1.17
         * @return array
         */
        public function update_cart_subtotal($response)
        {

            if (isset($response['status']) && $response['status'] === 'success' && isset($_REQUEST['form_settings'])) {
                $response['cart_subtotal_markup'] = $this->get_cart_subtotal();
            }

            return $response;

        }

        /**
         * Quantity Restriction Toggle Off/On
         *
         * @param bool $value Min Order Qty and Step Restriction. If empty or false means restricted. If true restriction is off and the product can be added to cart.
         * @since 1.17
         * @return bool
         */
        public function toggle_quantity_restriction($value)
        {

            if (isset($_REQUEST['form_settings']) && isset($_REQUEST['form_settings']['quantity_restriction'])) {
                if ($_REQUEST['form_settings']['quantity_restriction'] == 'false') {
                    return true;
                }
            }

            return $value;

        }

        /**
         * Lazy variations data. Set defaults.
         *
         * @param array  $products
         * @since 1.19
         * @return array
         */
        public function lazy_load_variations_data($products = array(), $wholesale_role = '')
        {

            $data = array();
            if (!empty($products)) {
                foreach ($products as $product) {
                    if ($product->type === 'variable') {
                        $totals             = WWOF_API_Helpers::get_variations_total_by_variable_id($product->id, $wholesale_role);
                        $data[$product->id] = array(
                            'current_page'     => 1,
                            'total_variations' => $totals,
                            'total_page'       => ceil($totals / $this->get_variations_per_page()),
                        );
                    }
                }
            }

            return $data;

        }

        /**
         * Toggle show variations individually.
         *
         * @param array  $wholesale_products
         * @param string  $wholesale_role
         *
         * @since 1.19
         * @return array
         */
        public function show_variations_individually($wholesale_products, $wholesale_role)
        {

            if (isset($_REQUEST['show_variations_individually']) && $_REQUEST['show_variations_individually'] == 'yes') {
                return WWOF_API_Helpers::get_variations_to_show_individually($wholesale_products, $wholesale_role);
            }

            return $wholesale_products;

        }

        /**
         * Hook into pre_get_posts.
         * - Show variations individually.
         * - Show zero inventory products
         *
         * @param WP_Query  $query
         *
         * @since 1.20
         */
        public function pre_get_posts_api_request($query)
        {

            global $wpdb;

            $is_rest = apply_filters('wwof_rest', defined('REST_REQUEST') && REST_REQUEST);

            if ($is_rest) {

                // Show Variations Individually
                if (isset($_REQUEST['show_variations_individually']) && $_REQUEST['show_variations_individually'] == 'yes') {

                    // Return variations
                    $query->set('post_type', array('product', 'product_variation'));

                    // Exclude variable products
                    $tax_query        = is_array($query->get('tax_query')) ? $query->get('tax_query') : array();
                    $variable_term_id = WWOF_Product_Listing_Helper::get_variable_product_term_taxonomy_id();
                    $tax_query        = array_merge($tax_query, array(array(
                        'taxonomy' => 'product_type',
                        'field'    => 'term_id',
                        'terms'    => array($variable_term_id),
                        'operator' => 'NOT IN',
                    )));

                    $query->set('tax_query', $tax_query);

                }

                // Show Zero Inventory
                if (isset($_REQUEST['show_zero_inventory']) && $_REQUEST['show_zero_inventory'] !== 'yes') {

                    $meta_query = is_array($query->get('meta_query')) ? $query->get('meta_query') : array();
                    $meta_query = array_merge($meta_query, array(array(
                        'key'     => '_stock_status',
                        'value'   => 'instock',
                        'compare' => '=',
                    )));

                    $query->set('meta_query', $meta_query);
                }

                // Exclude not supported products
                // Get Excluded IDs ( Exclude Bundle and Composite product types since we do not support these yet )
                $excluded_products1 = WWOF_Product_Listing_Helper::wwof_get_excluded_product_ids();

                // Get all products that has product visibility to hidden
                $excluded_products2 = WWOF_Product_Listing_Helper::wwof_get_excluded_hidden_products();

                // Merge excluded products ( Bundle, Composite and Hidden Products)
                $excluded_products = array_merge($excluded_products1, $excluded_products2);
                $post__in          = is_array($query->get('post__in')) ? $query->get('post__in') : array();

                if (!empty($post__in) && !empty($excluded_products)) {
                    $post__in = array_diff($post__in, $excluded_products);
                }

                $query->set('post__in', apply_filters('wwof_order_form_included_products', $post__in, $query));
                $query->set('post__not_in', apply_filters('wwof_order_form_excluded_products', $excluded_products, $query));

            }

        }

        /**
         * Set variations per page.
         * Combo dropdown will return show 20 results per page.
         * Standard dropdown will return 100 results per page.
         *
         * @since 1.19
         * @return int
         */
        public function get_variations_per_page()
        {

            $variations_per_page = 100;
            $selector_type       = 'standard';

            if (isset($_POST['form_settings'])) {

                // If selector style is not set, meaning it is the default combo style
                if (
                    !isset($_POST['form_settings']['variation_selector_style']) ||
                    (
                        isset($_POST['form_settings']['variation_selector_style']) &&
                        $_POST['form_settings']['variation_selector_style'] == 'combo'
                    )
                ) {
                    $variations_per_page = 20;
                    $selector_type       = 'combo';
                }
            }

            return apply_filters('wwof_v2_variations_per_page', $variations_per_page, $selector_type);
        }

        /**
         * Perform search. Override the wp query search.
         * Reasoning is so we can have flexibility on what to search.
         * Searching by variation and returning the variable is quite difficult.
         *
         * @param array             $args       Query args
         * @param WP_REST_Request   $request    WP Rest Request Object
         *
         * @since 1.20
         * @return array
         */
        public function order_form_search($args, $request)
        {

            if (!empty($request['search'])) {

                global $wpdb;

                $args['s'] = "";
                $search    = $request['search'];

                // Show Variations Individually
                $show_variations_individually = isset($_REQUEST['show_variations_individually']) && $_REQUEST['show_variations_individually'] == 'yes' ? true : false;

                // Search by post type
                $post_type = $show_variations_individually ? 'IN ("product", "product_variation")' : '= "product"';

                // Per form regular search to Title, Content and Excerpt
                $regular_search_query = $wpdb->prepare("SELECT DISTINCT p.ID
                                                            FROM $wpdb->posts p
                                                            INNER JOIN $wpdb->postmeta pm1 ON (p.ID = pm1.post_id)
                                                            WHERE p.post_type $post_type
                                                            AND p.post_status = 'publish'
                                                            AND (
                                                                p.post_title LIKE %s
                                                                OR p.post_content LIKE %s
                                                                OR p.post_excerpt LIKE %s
                                                            )
                                                        ", '%' . $search . '%', '%' . $search . '%', '%' . $search . '%');

                $searched_products = $wpdb->get_results($regular_search_query);
                $post__in          = array();

                if (!empty($searched_products)) {
                    foreach ($searched_products as $product) {
                        $post__in[] = $product->ID;
                    }
                }

                // Peform sku search
                if (isset($_REQUEST['allow_sku_search']) && $_REQUEST['allow_sku_search'] == 'yes') {

                    $search_sku_query = $wpdb->prepare("SELECT DISTINCT p.ID, p.post_parent
                                                            FROM $wpdb->posts p
                                                            INNER JOIN $wpdb->postmeta pm1 ON (p.ID = pm1.post_id)
                                                            WHERE p.post_type IN ('product', 'product_variation')
                                                            AND p.post_status = 'publish'
                                                            AND pm1.meta_key = '_sku' AND pm1.meta_value LIKE %s
                                                        ", '%' . $_REQUEST['search'] . '%');

                    $product_with_searched_skus = $wpdb->get_results($search_sku_query);
                    $searched_sku_products      = array();

                    if (!empty($product_with_searched_skus)) {
                        foreach ($product_with_searched_skus as $product) {
                            // Show Variations Individually is enabled
                            if ($show_variations_individually) {
                                $searched_sku_products[] = $product->ID;
                            } else {
                                $searched_sku_products[] = !empty($product->post_parent) ? $product->post_parent : $product->ID;
                            }
                        }
                    }

                    $post__in = array_unique(array_merge($post__in, $searched_sku_products));

                }

                $args['post__in'] = !empty($post__in) ? $post__in : array(0); // Return empty if no results

            }

            return $args;

        }

    }

}

return new WWOF_API_Request;
