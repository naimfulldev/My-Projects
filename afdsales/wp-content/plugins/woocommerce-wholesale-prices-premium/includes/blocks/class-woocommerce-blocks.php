<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_WC_Blocks' ) ) {
    
    /**
     * Model that houses the logic relating WWPP_WC_Blocks.
     *
     * @since 1.23.9
     */
    class WWPP_WC_Blocks {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_WC_Blocks.
         *
         * @since 1.23.9
         * @access private
         * @var WWPP_WC_Blocks
         */
        private static $_instance;
        
        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_WC_Blocks constructor.
         *
         * @since 1.23.9
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_WC_Blocks model.
         */
        public function __construct() {}

        /**
         * Ensure that only one instance of WWPP_WC_Blocks is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.23.9
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_WC_Blocks model.
         * @return WWPP_WC_Blocks
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }
        /**
         * Visibility check for WC Blocks.
         *
         * @since 1.23.9
         * @access public
         *
         * @param array     $html       HTML Format
         * @param array     $data       Blocks Data
         * @param object    $product    WC_Product Object
         * @return WWPP_WC_Blocks
         */
        public function grid_item( $html , $data , $product ) {
            
            $user = wp_get_current_user();
            
            // Perform restrictions on frontend for non admin people
            // This filter will also trigger on wc blocks for the preview
            if( !in_array( 'administrator' , $user->roles ) ) {
                
                global $wc_wholesale_prices_premium;
        
                $user_wholesale_role 				= $wc_wholesale_prices_premium->wwpp_wholesale_roles->getUserWholesaleRole();
                $wholesale_role      				= isset( $user_wholesale_role[ 0 ] ) ? $user_wholesale_role[ 0 ] : '';
                $product_cat_ids 					= wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) );
                $product_cat_wholesale_role_filter 	= get_option( WWPP_OPTION_PRODUCT_CAT_WHOLESALE_ROLE_FILTER );
        
                // Product Level Visibility filter
                $visibility = get_post_meta( $product->get_id() , 'wwpp_product_wholesale_visibility_filter' );
                $visibility = empty( $visibility ) ? array() : $visibility;
                
                if( !empty( $visibility ) && !in_array( 'all' , $visibility ) && !in_array( $wholesale_role , $visibility ) )
                    return "";
        
                if( $wholesale_role ) {
                    
                    if( !empty( $product_cat_wholesale_role_filter ) ) {
        
                        $filtered_terms_ids = array();
        
                        foreach ( $product_cat_wholesale_role_filter as $cat_id => $filtered_wholesale_roles )
                            if ( !in_array( $wholesale_role , $filtered_wholesale_roles ) )
                                $filtered_terms_ids[] = $cat_id;
        
                        // Dont show non-wholesale products
                        if( get_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' , false ) === 'yes' ) {
        
                            $wholesale_price = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3( $product->get_id() , $user_wholesale_role );
                            
                            if( $product->get_type() === 'simple' && empty( $wholesale_price[ 'wholesale_price' ] ) )
                                return "";
                            else if( get_post_meta( $product->get_id() , $wholesale_role . '_have_wholesale_price' , true ) !== 'yes' )
                                return "";
        
                        }
                            
                        // Don't show restricted products in category level for visitors
                        if( count( array_intersect( $product_cat_ids , $filtered_terms_ids ) ) > 0 )
                            return "";
        
                    }
                    
        
                } else {
                    
                    $restricted_cat_for_regular_users = array();
        
                    if ( !is_array( $product_cat_wholesale_role_filter ) )
                        $restricted_cat_for_regular_users = array();
                    else {
                        foreach( $product_cat_wholesale_role_filter as $cat_id => $role )
                            $restricted_cat_for_regular_users[] = $cat_id;
                    }
                    
                    // Don't show restricted products in category level for visitors
                    if( count( array_intersect( $product_cat_ids , $restricted_cat_for_regular_users ) ) > 0 )
                        return "";
        
                }
            
            }
                
            return $html;
                    
        }
        
        /*
        |-------------------------------------------------------------------------------------------------------------------
        | Execute Model
        |-------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Execute model.
         *
         * @since 1.23.9
         * @access public
         */
        public function run() {

            add_filter( 'woocommerce_blocks_product_grid_item_html' , array( $this , 'grid_item' ) , 10 , 3 );

        }

    }

}