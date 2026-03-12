<?php
/**
Plugin Name: Advanced Woocommerce Product Gallery Slider
Plugin URI: https://wordpress.org/plugins/woo-product-gallery-images-slider/
Description: Instantly transform the gallery on your WooCommerce Product page into a fully Responsive Stunning Carousel Slider.
Author: UnikInfotech
Version: 2.0.0
Author URI: http://www.unikinfotech.com
License: GPL2
	 
*/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Check Condition For Woocommerce Active
 */
	 if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )  ){
	add_action( 'admin_notices', 'wpgis_woocommerce_inactive_notice'  );
	return;
	}
	
	function wpgis_woocommerce_inactive_notice() {
		if ( current_user_can( 'activate_plugins' ) ) :
			if ( !class_exists( 'WooCommerce' ) ) :
				?>
				<div id="message" class="error">
					<p>
						<?php
						printf(
							__( '<strong><span>wpgis required the woocommerce plugin: <em><a href="http://localhost/tf/teddyapp/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=woocommerce&amp;TB_iframe=true&amp;width=640&amp;height=500" class="thickbox">Woocommerce</a></em>.</span></strong>', 'wpgis' )
							
						);
						?>
					</p>
				</div>		
				<?php
			endif;
		endif;
	}
/**
 * wc Version Check function
 */
function wpgis_version_check( $version = '3.0' ) {
	if ( class_exists( 'WooCommerce' ) ) {
		global $woocommerce;
		if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
			return true;

		}
	}
	return false;
}

/**
 * Woocommerce actions
 */

add_action( 'after_setup_theme', 'remove_wpgis_support' );

// Remove default support > woocommerce 3.0 = >

function remove_wpgis_support() {

$zoom_zoom_start = wpgis_get_option( 'zoom_start', 'zoom_magify'); // Setting api Zoom Option

if($zoom_zoom_start == 'false') :
	remove_theme_support( 'wc-product-gallery-zoom' );
else: 
  add_theme_support( 'wc-product-gallery-zoom' );
endif; 
	
	remove_theme_support( 'wc-product-gallery-lightbox' );
	remove_theme_support( 'wc-product-gallery-slider' );

}


add_action('plugins_loaded','after_woo_hooks');

function after_woo_hooks() {
	
 remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );

 add_action( 'woocommerce_before_single_product_summary', 'wpgis_pgs', 20 );
 
//remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );



}

/**
 * Add Product Video URL fields to media uploader
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */
 
function wpgis_add_video_url( $form_fields, $post ) {
	$form_fields['wpgis-video-url'] = array(
		'label' => 'Video URL',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'wpgis_video_url', true ),
		'helps' => 'wpgis Product Video URL',
	);

	

	return $form_fields;
}

/**
 * Save values of Product Video URL fields to media uploader
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function wpgis_add_video_url_save( $post, $attachment ) {
	if( isset( $attachment['wpgis-video-url'] ) )
		update_post_meta( $post['ID'], 'wpgis_video_url', $attachment['wpgis-video-url'] );

	
	return $post;
}

add_filter( 'attachment_fields_to_edit', 'wpgis_add_video_url', 10, 2 );
add_filter( 'attachment_fields_to_save', 'wpgis_add_video_url_save', 10, 2 );

/**
* Dequeue the jQuery UI script.
*
* Hooked to the wp_print_scripts action, with a late priority (100),
* so that it is after the script was enqueued.
*/
function wpgis_dequeue_script() {
wp_dequeue_script( 'cornerstone-site-head' );
wp_dequeue_script( 'cornerstone-site-body' );
}

$themeco = wpgis_get_option( 'themeco', 'wpgis_advance','false');
if($themeco == 'true'){

add_action( 'wp_print_scripts', 'wpgis_dequeue_script', 100 );
	
}

/**
 * Register the JS & CSS for the public-facing side of the site.
 *
 */
	function wpgis_enqueue_files() {
		if(is_product()){

		wp_enqueue_script( 'slick-js', plugin_dir_url( __FILE__ ) . 'assets/slick.min.js', array( 'jquery' ),'1.3', false );
		wp_enqueue_script( 'venobox-js', plugin_dir_url( __FILE__ ) . 'assets/venobox.min.js', array( 'jquery' ),'1.3', false );

		wp_enqueue_style( 'dashicons');
		wp_enqueue_style( 'slick-wpgis', plugin_dir_url( __FILE__ ) . 'assets/slick-theme.css', array(),  '1.3' );	
		wp_enqueue_style( 'wpgis', plugin_dir_url( __FILE__ ) . 'assets/wpgis.css', array(),  '1.3' );
		}
		
		}
	
	
add_action( 'wp_enqueue_scripts','wpgis_enqueue_files' );




function wpgis_pgs() {

	$wpgis_advance_layout_pb = wpgis_get_option( 'layout_pb', 'wpgis_advance','false');

	if($wpgis_advance_layout_pb == 'false'){
		require_once dirname( __FILE__ ) . '/inc/pgs.php';
	}
	else{
		ob_start();
		require_once dirname( __FILE__ ) . '/inc/pgs.php';
		$output = ob_get_clean();
		return $output;
	}

	
}

$wpgis_advance_layout_pb = wpgis_get_option( 'layout_pb', 'wpgis_advance','false');

if($wpgis_advance_layout_pb == 'true'){

	add_shortcode( 'wpgis_vc', 'wpgis_pgs' );
	add_action( 'vc_before_init', 'wpgis_vc_map' );

	function wpgis_vc_map() {
	   vc_map( array(
	      "name" => __( "wpgis Product Gallery", "wpgis" ),
	      "base" => "wpgis_vc",
		  "description" => __("Product Gallery Slider","wpgis"),
	      "category" => __( "WooCommerce", "wpgis"),
	     
	    
	   ) );
	}
}


/**
 * Setting Options
 * # https://github.com/tareq1988/wordpress-settings-api-class
 */
require_once dirname( __FILE__ ) . '/inc/class.settings-api.php';
require_once dirname( __FILE__ ) . '/inc/wpgis-settings.php';

new WeDevs_Settings_API_Test();


/**
 * Get the value of a settings field
 *
 * @param string $option settings field name
 * @param string $section the section name this field belongs to
 * @param string $default default text if it's not found
 * 
 * @return mixed
 */
function wpgis_get_option( $option, $section, $default = '' ) {

    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }

    return $default;
}




// Plugin Activation
function plugin_activation() {
    require_once plugin_dir_path(__FILE__) . 'inc/activator.php';
}
register_activation_hook(__FILE__, 'plugin_activation');
// Plugin Deactivation
function plugin_deactivation() {
	require_once plugin_dir_path(__FILE__) . 'inc/deactivator.php';
}
register_deactivation_hook(__FILE__, 'plugin_deactivation');