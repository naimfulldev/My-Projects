<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWOF_Cache' ) ) {
    
    /**
     * Model that houses the logic relating caching.
     *
     * @since 1.14.1
     */
    class WWOF_Cache {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWOF_Cache.
         *
         * @since 1.14.1
         * @access private
         * @var WWOF_Cache
         */
        private static $_instance;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWOF_Cache constructor.
         *
         * @since 1.14.1
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWOF_Cache model.
         */
        public function __construct( $dependencies ) {}

        /**
         * Ensure that only one instance of WWOF_Cache is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.14.1
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWOF_Cache model.
         * @return WWOF_Cache
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Get the transient cache name of the current user or role.
         * 
         * @since 1.14.1
         * @access public
         * 
         * @param string   $user_wholesale_role   Wholesale Role
         * 
         * @return string
         */
        public function get_cache_name( $user_wholesale_role ) {

            // If Override per user is enabled
            // Unique cache per user
            $current_user_id                    = get_current_user_id();
            $wwpp_override_discount_per_user    = get_user_meta( $current_user_id , 'wwpp_override_wholesale_discount' , true );
            $transient_name                     = '';

            // Per user transient cache
            if( $wwpp_override_discount_per_user == 'yes' )
                $transient_name = 'wwof_cached_products_ids_' . $current_user_id;
            else {
                // Per wholesale or just regular visitor transients cache
                $transient_name = 'wwof_cached_products_ids';
                $transient_name .= !empty( $user_wholesale_role ) ? '_' . $user_wholesale_role : '';
            }

            return $transient_name;

        }

        /**
         * Hook on WC product create or update.
         * 
         * @since 1.14.1
         * @access public
         * 
         * @param int   $product_id   Prouct ID
         */
        public function product_update( $product_id ) {

            $wwof_product_cache_option = get_option( 'wwof_enable_product_cache' );

            if( $wwof_product_cache_option == 'yes' )
                $this->wwof_clear_product_transients_cache();

        }

        /**
         * Hook on WC variation update.
         * 
         * @since 1.14.1
         * @access public
         * 
         * @param int   $variation_id   Variation ID
         */
        public function wc_update_product_variation( $variation_id ) {

            $wwof_product_cache_option = get_option( 'wwof_enable_product_cache' );

            if( $wwof_product_cache_option == 'yes' )
                $this->wwof_clear_product_transients_cache();

        }

        /**
         * Hook on Product Category Add, Update, Delete.
         * 
         * @since 1.14.1
         * @access public
         * 
         * @param int   $term_id
         * @param int   $taxonomy_term_id
         */
        public function product_cat_update( $term_id , $taxonomy_term_id ) {

            $wwof_product_cache_option = get_option( 'wwof_enable_product_cache' );

            if( $wwof_product_cache_option == 'yes' )
                $this->wwof_clear_product_transients_cache();

        }

        /**
         * Hook on User update.
         * 
         * @since 1.14.1
         * @access public
         * 
         * @param int       $user_id        User ID
         * @param object    $old_user_data  WP_User Object
         */
        public function profile_update( $user_id , $old_user_data ) {
            
            $wwof_product_cache_option = get_option( 'wwof_enable_product_cache' );

            if( $wwof_product_cache_option == 'yes' )
                $this->wwof_clear_product_transients_cache();

        }

        /**
         * On post or page update.
         * 
         * @since 1.14.1
         * @access public
         * 
         * @param int   $post_id    Page or post ID
         */
        public function post_or_page_update( $post_id ) {

            $wwof_product_cache_option = get_option( 'wwof_enable_product_cache' );

            if( $wwof_product_cache_option == 'yes' )
                $this->wwof_clear_product_transients_cache();

        }

        /**
         * On general discount update.
         * 
         * @since 1.14.1
         * @access public
         */
        public function wwpp_general_discount_update() {
            
            $wwof_product_cache_option = get_option( 'wwof_enable_product_cache' );

            if( $wwof_product_cache_option == 'yes' )
                $this->wwof_clear_product_transients_cache();

        }

        /**
         * Delete product IDs cached transients.
         * 
         * @since 1.14.1
         * @since 1.15.1 Clear cache feature when theres an update to the product
         * 
         * @param string    $transient_name
         * @access public
         */
        public function wwof_clear_product_transients_cache( $transient_name = null ) {
            
            global $wpdb;
            
            // Clear cache product variations. Cache feature was introduced in 1.14.1
            $this->wwof_clear_product_variations_cache();

            if( $transient_name != null ) {

                delete_transient( $transient_name );

            } else {

                $results = $wpdb->get_results( "SELECT option_name FROM $wpdb->options
                            WHERE option_name LIKE '_transient_wwof_cached_products_ids%'", ARRAY_A );
                
                // Delete transients
                if( !empty( $results ) ) {
                    
                    foreach( $results as $key => $name ) {

                        $transient_name = str_replace( '_transient_' , '' , $name[ 'option_name' ] );
                        delete_transient( $transient_name );

                    }

                }

            }

        }

        /**
         * Delete product variations cache. The cache is stored as meta.
         * 
         * @since 1.14.1
         * 
         * @access public
         */
        public function wwof_clear_product_variations_cache() {

            global $wpdb;

            $products = wc_get_products( array( 'type' => 'variable' , 'return' => 'ids' ) );
            
            if( !empty( $products ) ) {

                foreach( $products as $product_id ) {

                    delete_post_meta( $product_id , 'wwof_cached_variations' );

                    $cached_variations = $wpdb->get_results( "SELECT post_id, meta_key FROM $wpdb->postmeta
                            WHERE post_id = $product_id
                            AND meta_key LIKE 'wwof_cached_variations_%'", ARRAY_A );
                            
                    if( !empty( $cached_variations ) ) {
                        foreach( $cached_variations as $key => $variations )
                            delete_post_meta( $variations[ 'post_id' ] , $variations[ 'meta_key' ] );

                    }

                }

            }

        }

        
        /**
         * On WC Tax Settings update, clear WWOF cache.
         * 
         * @since 1.15.5
         * @access public
         */
        public function wc_tax_settings_update() {
            
            $wwof_product_cache_option = get_option( 'wwof_enable_product_cache' );

            if( $wwof_product_cache_option == 'yes' )
                $this->wwof_clear_product_transients_cache();

        }

        /*
        |-------------------------------------------------------------------------------------------------------------------
        | AJAX
        |-------------------------------------------------------------------------------------------------------------------
        */
        
        /**
         * This will purge all product transients cache for wholesale customers including regular/visitors cache.
         * 
         * @since 1.14.1
         * @access public
         */
        public function ajax_clear_product_transients_cache() {

            if ( !defined( "DOING_AJAX" ) || !DOING_AJAX )
                $response = array( 'status' => 'fail' , 'error_msg' => __( 'Invalid AJAX Operation' , 'woocommerce-wholesale-order-form' ) );
            elseif ( !check_ajax_referer( 'wwof_clear_product_transients_cache' , 'ajax-nonce' , false ) )
                $response = array( 'status' => 'fail' , 'error_msg' => __( 'Security check failed' , 'woocommerce-wholesale-order-form' ) );
            else {

                // Clear cache query ids
                $this->wwof_clear_product_transients_cache();
                
                $response = array( 'status' => 'success' , 'success_msg' => __( 'Successfully cleared all products transients cache' , 'woocommerce-wholesale-order-form' ) );

            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                wp_send_json( $response );
            else
                return $response;

        }
        
        /**
         * Register ajax handlers.
         * 
         * @since 1.14.1
         * @access public
         */
        public function register_ajax_handlers() {

            add_action( 'wp_ajax_wwof_clear_product_transients_cache' , array( $this , 'ajax_clear_product_transients_cache' ) );

        }

        
        /*
        |-------------------------------------------------------------------------------------------------------------------
        | Execute Model
        |-------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Execute model.
         *
         * @since 1.14.1
         * @access public
         */
        public function run() {

            add_action( 'init' , array( $this , 'register_ajax_handlers' ) );

            // On product create or update, remove transient if caching is enabled so that the front end will be up to date.
            // This will re-run the query and build cache when user visits page with product listing like product short code, widget or shop page.
            add_action( 'woocommerce_update_product'    , array( $this , 'product_update' ) , 10 , 1 );

            // Variation Add/Update
            add_action( 'woocommerce_update_product_variation'  , array( $this , 'wc_update_product_variation' ) , 10 , 1 ); 

            // Delete product listing cache on Product Category Create, Update, Delete
            add_action( 'edited_product_cat'            , array( $this , 'product_cat_update' ) , 10 , 2 );
            add_action( 'create_product_cat'            , array( $this , 'product_cat_update' ) , 10 , 2 );
            add_action( 'delete_product_cat'            , array( $this , 'product_cat_update' ) , 10 , 2 );

            // Delete product listing cache on profile update. They might update the override wholesale price per user.
            add_action( 'profile_update'                , array( $this , 'profile_update' ) , 10 , 2 );

            // When a post or page is updated. Clear cache to have updated listings.
            add_action( 'save_post'                     , array( $this , 'post_or_page_update' ) , 10 , 1 );

            // Clear cache if wwpp general discount is updated. The hook is added on WWPP 1.23.2
            add_action( 'wwpp_add_wholesale_role_general_discount_mapping'      , array( $this , 'wwpp_general_discount_update' ) );
            add_action( 'wwpp_edit_wholesale_role_general_discount_mapping'     , array( $this , 'wwpp_general_discount_update' ) );
            add_action( 'wwpp_delete_wholesale_role_general_discount_mapping'   , array( $this , 'wwpp_general_discount_update' ) );

            // On Tax Update, Clear WWOF Cache
            add_action( 'woocommerce_settings_save_tax'   , array( $this , 'wc_tax_settings_update' ) );
            
        }

    }

}