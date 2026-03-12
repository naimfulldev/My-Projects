<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
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

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>
<div class="p_product_section">
	<div class="container_full">
		<div class="p_product_part">
			<div class="p_product_aside product-detail-part">
	<?php
	/**
	 * Hook: woocommerce_before_single_product_summary.
	 *
	 * @hooked woocommerce_show_product_sale_flash - 10
	 * @hooked woocommerce_show_product_images - 20
	 */
	do_action( 'woocommerce_before_single_product_summary' );
	?>
			</div>
			<div class="p_product_artical">
				<div class="p_product_intro">
	<div class="summary entry-summary">
		<?php
		/**
		 * Hook: woocommerce_single_product_summary.
		 *
		 * @hooked woocommerce_template_single_title - 5
		 * @hooked woocommerce_template_single_rating - 10
		 * @hooked woocommerce_template_single_price - 10
		 * @hooked woocommerce_template_single_excerpt - 20
		 * @hooked woocommerce_template_single_add_to_cart - 30
		 * @hooked woocommerce_template_single_meta - 40
		 * @hooked woocommerce_template_single_sharing - 50
		 * @hooked WC_Structured_Data::generate_product_data() - 60
		 */
		do_action( 'woocommerce_single_product_summary' );
		?>			<div class="wishlist-section">
						<?php echo do_shortcode('[ti_wishlists_addtowishlist]'); ?>
						<!-- <p class="add_list"><img src="<?php echo get_template_directory_uri() ?>/image/heart_black.png" class="img-responsive" alt="New York">Add to Wish List </p>-->
					</div>
					<?php 
						$description = get_field('description',$product_id);
						$material = get_field('material',$product_id);
						if(!empty($description) && !empty($material))
						{
					?>
					<div class="product_accordion">
						<div class="p_product_accordion">
							<?php if(!empty($description)){ ?>
							<div class="accordion-option">
								<a class="accordion">
									Description 
								</a>
								<div class="panel">
									<div class="description_part">
										<p><?php echo $description; ?></p>
									</div>
								</div>
							</div>
						<?php }if(!empty($material)){ ?>
							<div class="accordion-option">
								<a class="accordion">
									Material 
								</a>
								<div class="panel">
									<div class="description_part">
										<p><?php echo $material; ?></p>
									</div>
								</div>
							</div>
						<?php } ?>
						</div>
					</div>
					<?php } ?>
					<?php $img=get_the_post_thumbnail($loop->post->ID, 'shop_catalog') ?>
					<textarea id="has_product_featured_img"><?php echo $img ?></textarea>
				</div>
			</div>
	</div>
		</div>
	</div>
</div>
	<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	do_action( 'woocommerce_after_single_product_summary' );
	?>
</div>

<?php //do_action( 'woocommerce_after_single_product' ); ?>
