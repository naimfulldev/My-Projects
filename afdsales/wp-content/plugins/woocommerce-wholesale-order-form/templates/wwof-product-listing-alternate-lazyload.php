<?php
/**
 * The template for displaying product listing (lazyload)
 *
 * Override this template by copying it to yourtheme/woocommerce/wwof-product-listing-alternate-lazyload.php
 *
 * @author 		Rymera Web Co
 * @package 	WooCommerceWholeSaleOrderForm/Templates
 * @version     1.14.1
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$thumbnailSize = $product_listing->wwof_get_product_thumbnail_dimension();
$labels        = array(
    'product'         =>  __( 'Product' , 'woocommerce-wholesale-order-form' ),
    'sku'             =>  __( 'SKU' , 'woocommerce-wholesale-order-form' ),
    'price'           =>  __( 'Price' , 'woocommerce-wholesale-order-form' ),
    'stock_quantity'  =>  __( 'Stock Quantity' , 'woocommerce-wholesale-order-form' ),
    'quantity'        =>  __( 'Quantity' , 'woocommerce-wholesale-order-form' )
);

$wholesale_role = $wholesale_prices->_get_current_user_wholesale_role();

// Ensure that available variations always show their price HTML even if the prices are the same
add_filter( 'woocommerce_show_variation_price', function() { return true; } );

while ( $product_loop->have_posts() ) { $product_loop->the_post();

    global $product;
    
    $product_id             = get_the_ID();
    $product                = wc_get_product( $product_id );
    $product_type           = WWOF_Functions::wwof_get_product_type( $product );
    $available_variations   = array();

    if ( $product_type == 'variable' ) {
        
        $available_variations = WWOF_Product_Listing_Helper::wwof_get_available_variations( $product , $product_id , $wholesale_role );

        if ( !empty( $wholesale_role ) ) {
            
            // get wholesale price for all variations
            WWOF_Product_Listing_Helper::wwof_get_variations_wholesale_price( $available_variations , $wholesale_role );

        }

        // update available variations input arguments
        WWOF_Product_Listing_Helper::wwof_update_variations_input_args( $available_variations );
    } ?>

    <tr>
        <td class="product_meta_col" style="display: none !important;" data-product_variations="<?php if ( isset( $available_variations ) ) echo htmlspecialchars( wp_json_encode( $available_variations ) ); ?>">
            <?php echo $product_listing->wwof_get_product_meta( $product ); ?>
        </td>
        <td class="product_title_col">
            <span class="mobile-label"><?php echo $labels[ 'product' ]; ?></span>
            <?php echo $product_listing->wwof_get_product_image( $product , get_the_permalink( $product_id ) , $thumbnailSize ); ?>
            <?php echo $product_listing->wwof_get_product_title( $product , get_the_permalink( $product_id ) ); ?>
            <br />
            <?php echo $product_listing->wwof_get_product_variation_field( $product , $product_type , $available_variations ); ?>
            <?php echo $product_listing->wwof_get_product_variation_selected_options( $product , $product_type ); ?>
            <?php echo $product_listing->wwof_get_product_addons( $product , $product_id , $product_type ); ?>
        </td>
        <td class="product_sku_col <?php echo $product_listing->wwof_get_product_sku_visibility_class(); ?>">
            <span class="mobile-label"><?php echo $labels[ 'sku' ]; ?></span>
            <?php echo $product_listing->wwof_get_product_sku( $product ); ?>
        </td>
        <td class="product_price_col">
            <span class="mobile-label"><?php echo $labels[ 'price' ]; ?></span>
            <span class="price_wrapper">
                <?php echo $wholesale_prices->wwof_get_product_price( $product ); ?>
            </span>
        </td>
        <td class="product_stock_quantity_col <?php echo $product_listing->wwof_get_product_stock_quantity_visibility_class(); ?>">
            <span class="mobile-label"><?php echo $labels[ 'stock_quantity' ]; ?></span>
            <?php echo $product_listing->wwof_get_product_stock_quantity( $product , $product_type ); ?>
        </td>
        <td class="product_quantity_col">
            <span class="mobile-label"><?php echo $labels[ 'quantity' ]; ?></span>
            <?php echo $wholesale_prices->wwof_get_product_quantity_field( $product , $product_id , $product_type , $wholesale_role ); ?>
        </td>
        <td class="product_row_action">
            <?php echo $product_listing->wwof_get_product_row_action_fields( $product , true ); ?>
        </td>
    </tr><?php

}// End while loop
