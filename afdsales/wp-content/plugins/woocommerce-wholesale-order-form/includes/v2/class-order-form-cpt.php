<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('Order_Form_CPT')) {

    class Order_Form_CPT
    {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        private static $_instance;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        public function __construct()
        {
        }

        public static function instance($dependencies = null)
        {

            if (!self::$_instance instanceof self) {
                self::$_instance = new self($dependencies);
            }

            return self::$_instance;

        }

        /**
         * Integration of WC Navigation Bar.
         *
         * @since 1.0.0
         * @access public
         */
        public function wc_navigation_bar()
        {

            if (function_exists('wc_admin_connect_page')) {
                wc_admin_connect_page(
                    array(
                        'id'        => 'woocommerce-wholesale-order-form',
                        'screen_id' => 'woocommerce_page_order-forms',
                        'title'     => __('Order Forms', 'woocommerce-wholesale-order-form'),
                    )
                );
            }

        }

        /*
        |--------------------------------------------------------------------------
        | Add CPT Admin Menu Page
        |--------------------------------------------------------------------------
         */

        /**
         * Add Order Form submenu of WC.
         *
         * @since 1.0.0
         * @access public
         */
        public function add_order_form_submenu()
        {

            if (get_option('wwof_order_form_v2_enable_order_form') == 'yes' && Order_Form_API_KEYS::is_api_key_valid()) {

                $order_forms = new WP_Query(array('post_type' => 'order_form', 'fields' => 'ids'));
                $count       = $order_forms->post_count;
                $title       = $count > 1 ? __('Wholesale Order Forms', 'woocommerce-wholesale-order-form') : __('Wholesale Order Form', 'woocommerce-wholesale-order-form');

                add_submenu_page('woocommerce', $title, $title, 'manage_woocommerce', 'order-forms', array($this, 'order_form_cpt_page'));
            }

        }

        /**
         * Element where to mount react code.
         *
         * @since 1.0.0
         * @access public
         */
        public function order_form_cpt_page()
        {

            echo '<div id="wwof-order-forms-admin"></div>';

        }

        /**
         * Register Order Form CPT.
         *
         * @since 1.0.0
         * @access public
         */
        public function register_order_form_cpt()
        {

            $link_prefix = 'order_forms';

            $labels = array(
                'name'               => __('Order Forms', 'woocommerce-wholesale-order-form'),
                'singular_name'      => __('Order Form', 'woocommerce-wholesale-order-form'),
                'menu_name'          => __('Order Forms', 'woocommerce-wholesale-order-form'),
                'parent_item_colon'  => __('Parent Order Form', 'woocommerce-wholesale-order-form'),
                'all_items'          => __('Order Forms', 'woocommerce-wholesale-order-form'),
                'view_item'          => __('View Order Form', 'woocommerce-wholesale-order-form'),
                'add_new_item'       => __('Add Order Form', 'woocommerce-wholesale-order-form'),
                'add_new'            => __('New Order Form', 'woocommerce-wholesale-order-form'),
                'edit_item'          => __('Edit Order Form', 'woocommerce-wholesale-order-form'),
                'update_item'        => __('Update Order Form', 'woocommerce-wholesale-order-form'),
                'search_items'       => __('Search Order Forms', 'woocommerce-wholesale-order-form'),
                'not_found'          => __('No Order Form found', 'woocommerce-wholesale-order-form'),
                'not_found_in_trash' => __('No Order Forms found in Trash', 'woocommerce-wholesale-order-form'),
            );

            $args = array(
                'label'                 => __('Order Forms', 'woocommerce-wholesale-order-form'),
                'description'           => __('Order Forms CPT', 'woocommerce-wholesale-order-form'),
                'labels'                => $labels,
                'query_var'             => true,
                'rewrite'               => array(
                    'slug'       => $link_prefix,
                    'with_front' => false,
                    'pages'      => false,
                ),
                'can_export'            => true,
                'exclude_from_search'   => true,
                'publicly_queryable'    => true,
                'capability_type'       => 'post',

                // REST API
                'show_in_rest'          => false, // We set to false so it is not included on wp/v2 REST API namespace
                'rest_base'             => 'wwof',
                'rest_controller_class' => 'WWOF_Order_Form_API',
            );

            register_post_type('order_forms', apply_filters('order_forms_cpt_args', $args, $labels));

            do_action('wwof_after_register_order_form_post_type', $link_prefix);

        }

        /**
         * Execute model.
         *
         * @since 1.15
         * @access public
         */
        public function run()
        {

            // WooCommerce Navigation Bar
            add_action('init', array($this, 'wc_navigation_bar'));

            // Order Form WC Submenu
            add_action('admin_menu', array($this, 'add_order_form_submenu'));

            // Register Order Form CPT
            add_action('init', array($this, 'register_order_form_cpt'));

        }

    }

}
