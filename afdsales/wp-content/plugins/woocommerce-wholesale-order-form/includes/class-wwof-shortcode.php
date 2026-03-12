<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWOF_Shortcode' ) ) {

	class WWOF_Shortcode {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

		/**
         * Property that holds the single main instance of WWOF_Shortcode.
         *
         * @since 1.6.6
         * @access private
         * @var WWOF_Shortcode
         */
		private static $_instance;


        /**
         * WWOF_Product_Listing Object
         *
         * @since 1.6.6
         * @access private
         * @var WWOF_Product_Listing
         */
        private $_wwof_product_listings;

		/*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWOF_Shortcode constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWOF_Shortcode model.
         *
         * @access public
         * @since 1.6.6
         */
		public function __construct( $dependencies ) {

			$this->_wwof_product_listings = $dependencies[ 'WWOF_Product_Listing' ];

		}

        /**
         * Ensure that only one instance of WWOF_Shortcode is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWOF_Shortcode model.
         *
         * @return WWOF_Shortcode
         * @since 1.6.6
         */
        public static function instance( $dependencies = null  ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

	    /**
	     * Product listing shortcode.
	     *
	     * @return string
	     * @since 1.0.0
	     * @since 1.6.6 Refactor codebase and move to its proper model
		 * @since 1.15  Added user permissions check. Enable Order Form v2.
	     */
	    public function wwof_sc_product_listing( $atts ) {
			
			global $wc_wholesale_order_form;

            $user_has_access = $wc_wholesale_order_form->_wwof_permissions->wwof_user_has_access();

			// Check if the user has permission
			if ( $user_has_access ) {

				// Extract atts
				$atts = shortcode_atts( array(
							'show_search' 	=> 1,
							'categories'  	=> 0,
							'products'   	=> 0
						) , $atts );

				// To buffer the output
				ob_start();

				require ( WWOF_VIEWS_ROOT_DIR . 'shortcodes/wwof-shortcode-product-listing.php' );

				// To return the buffered output
				return ob_get_clean();
			
			} else {

				ob_start();

                // User don't have permission
                $title      = trim( stripslashes( strip_tags( get_option( 'wwof_permissions_noaccess_title' ) ) ) );
                $message    = trim( stripslashes( get_option( 'wwof_permissions_noaccess_message' ) ) );
                $login_url  = trim( get_option( 'wwof_permissions_noaccess_login_url' ) );

                if ( empty( $title ) )
                    $title = __( 'Access Denied' , 'woocommerce-wholesale-order-form' );

                if ( empty( $message ) )
                    $message = __( "You do not have permission to view wholesale product listing" , 'woocommerce-wholesale-order-form' );

                if ( empty( $login_url ) )
                    $login_url = wp_login_url(); ?>

                <div id="wwof_access_denied">
                    <h2 class="content-title"><?php echo $title; ?></h2>
                    <?php echo do_shortcode( html_entity_decode( $message ) ); ?>
                    <p class="login-link-container"><a class="login-link" href="<?php echo $login_url; ?>"><?php _e( 'Login Here' , 'woocommerce-wholesale-order-form' ); ?></a></p>
                </div><?php

				if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

					// To return the buffered output
					echo ob_get_clean();
					die();

				} else
					return ob_get_clean();

			}
			
	    }

	    /**
	     * Apply certain classes to body tag wherever page/post the shortcode [wwof_product_listing] is applied.
	     *
	     * @param $classes
	     *
	     * @return mixed
	     * @since 1.0.0
	     * @since 1.6.6 Refactor codebase and move to its proper model
	     */
	    public function wwof_sc_body_classes( $classes ) {

	        global $post;

	        if ( isset( $post->post_content ) && has_shortcode( $post->post_content , 'wwof_product_listing' ) ) {

	            $classes [] = 'wwof-woocommerce';
	            $classes [] = 'woocommerce';
	            $classes [] = 'woocommerce-page';

	        }

	        return apply_filters( 'wwof_filter_body_classes' , $classes );

	    }

	    /**
	     * Execute model.
	     *
	     * @since 1.6.6
	     * @access public
	     */
	    public function run() {

		    // Register Short Codes
		    add_shortcode( 'wwof_product_listing' , array( $this , 'wwof_sc_product_listing' ) );
		    add_filter( 'body_class' , array( $this , 'wwof_sc_body_classes' ) );

	    }
	}
}
