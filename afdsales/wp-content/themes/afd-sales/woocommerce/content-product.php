<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<div class="product_block">
<!-- <li <?php wc_product_class( '', $product ); ?>> -->
	<?php
	/**
	 * Hook: woocommerce_before_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_open - 10
	 */
	do_action( 'woocommerce_before_shop_loop_item' );

	/**
	 * Hook: woocommerce_before_shop_loop_item_title.
	 *
	 * @hooked woocommerce_show_product_loop_sale_flash - 10
	 * @hooked woocommerce_template_loop_product_thumbnail - 10
	 */
	?>
	<div class="product_img">
	<?php
	do_action( 'woocommerce_before_shop_loop_item_title' );
	?>
	</div>
	<div class="product_info">
	<div class="product_info_title">
	<?php
	/**
	 * Hook: woocommerce_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_product_title - 10
	 */

	do_action( 'woocommerce_shop_loop_item_title' );

	/**
	 * Hook: woocommerce_after_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_rating - 5
	 * @hooked woocommerce_template_loop_price - 10
	 */
	do_action( 'woocommerce_after_shop_loop_item_title' );

	/**
	 * Hook: woocommerce_after_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_close - 5
	 * @hooked woocommerce_template_loop_add_to_cart - 10
	 */
	//do_action( 'woocommerce_after_shop_loop_item' );
	?>
	</div>	
		<?php 
            $available_variations=$product->get_attribute( 'color' ); // get all attributes by variations
            $array = explode(',', $available_variations);
            //print_r($array);
            $count = count($array);
            //echo $count;
            if($count > 1){
        ?>
		<div class="product_info_colors">
			<p><?php echo count($array); ?> MORE COLORS</p>
		</div>
		<?php }else if($count == 1){ if(!empty($array[0])){ ?>
		<div class="product_info_colors">
			<p><?php echo strtoupper($array[0]); ?></p>
		</div>
		<?php } } ?>
	</div>
	<?php $current_tags = get_the_terms( get_the_ID(), 'product_tag' ); ?>
	<?php if($current_tags[0]->slug == 'best-seller'){ ?>
        <div class="best_seller">
            <h4>BEST SELLER</h4>
        </div>
    <?php } ?>
<!-- </li> -->
</div>
