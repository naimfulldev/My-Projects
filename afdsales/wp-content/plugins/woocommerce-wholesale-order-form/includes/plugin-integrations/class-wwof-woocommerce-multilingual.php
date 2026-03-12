<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWOF_WooCommerce_Multilingual' ) ) {

    /**
     * Model that houses the logic of integrating with 'WooCommerce Multilingual' plugin.
     *
     * @since 1.15.5
     */
    class WWOF_WooCommerce_Multilingual {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWOF_WooCommerce_Multilingual.
         *
         * @since 1.15.5
         * @access private
         * @var WWOF_WooCommerce_Multilingual
         */
        private static $_instance;
        
        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWOF_WooCommerce_Multilingual constructor.
         *
         * @since 1.15.5
         * @access public
         *
         * @param array
         */
        public function __construct() {}

        /**
         * Ensure that only one instance of WWOF_WooCommerce_Multilingual is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.15.5
         * @access public
         *
         * @param array
         * @return WWOF_WooCommerce_Multilingual
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * For some reason when you select a currency in the order form page, the currency is always set to the default one ( being reset to default for some reason ).
         * Fixed it by grabbing the correct currency on script loader and stored as transient first ( the correct currency ) so we can set it here to get correct price conversion.
         *
         * @since 1.15.5
         * @access public
         *
         * @param array $product WC_Product
         */
        public function set_client_currency_session() {

            global $woocommerce_wpml;
            
            // Set correct currency in wc session
            $client_currency_transient = get_transient( 'client_currency' );
            $woocommerce_wpml->multi_currency->set_client_currency( $client_currency_transient );

        }

        /**
         * Save the selected client currency into a transient which will be used later before getting the product price so that the conversion is correct.
         *
         * @since 1.15.5
         * @access public
         *
         * @param array $product WC_Product
         */
        public function set_client_currency_transient() {

            global $woocommerce_wpml;
            
            $client_currency_transient  = get_transient( 'client_currency' );
            $selected_currency          = $woocommerce_wpml->multi_currency->get_client_currency();
            
            if( empty( $client_currency_transient ) ) {

                set_transient( 'client_currency' , $selected_currency , WEEK_IN_SECONDS ); // Delete after a week

            } else if( $selected_currency !== $client_currency_transient ) {

                delete_transient( 'client_currency' );
                set_transient( 'client_currency' , $selected_currency , WEEK_IN_SECONDS ); // Delete after a week

            }

        }

        /**
         * WPML Multi Currency compatibility.
         *
         * @since 1.15.5
         * @access public
         */
        public function allow_multi_currencies_calculations_for_ajax_requests() {
            
            $actions[] = 'wwof_display_product_listing';
            $actions[] = 'wwof_get_product_details';
            $actions[] = 'wwof_add_product_to_cart';
            $actions[] = 'wwof_add_products_to_cart';
            $actions[] = 'wwof_get_variation_quantity_input_args';
            
            return $actions;
            
        }

        /*
        |--------------------------------------------------------------------------
        | Execute Model
        |--------------------------------------------------------------------------
        */

        /**
         * Execute model. Compatible with WooCommerce Multilingual 4.7.6
         *
         * @since 1.15.5
         * @access public
         */
        public function run() {
            
            if ( WWOF_Functions::is_wc_multilingual_active() ) {

                global $woocommerce_wpml;

                if( $woocommerce_wpml->settings['enable_multi_currency'] != WCML_MULTI_CURRENCIES_INDEPENDENT ) return;

                // Set currenct currency transient / WC Multilingual Compat
                add_action( 'wwof_load_front_end_styles_and_scripts'    , array( $this , 'set_client_currency_transient' ) );

                // Set currency in wc session
                add_action( 'wwof_action_before_product_listing'        , array( $this , 'set_client_currency_session' ) );
                
                // Allow WPML multicurrency calculations on our WWOF AJAX
                add_filter( 'wcml_multi_currency_ajax_actions'          , array( $this , 'allow_multi_currencies_calculations_for_ajax_requests' ) );

            }
            
        }

    }

}
