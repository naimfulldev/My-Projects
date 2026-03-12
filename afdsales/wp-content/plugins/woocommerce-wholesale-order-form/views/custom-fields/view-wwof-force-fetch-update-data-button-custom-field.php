<?php if (!defined('ABSPATH')) {
    exit;
}
; // Exit if accessed directly ?>

<tr valign="top">
    <th scope="row" class="titledesc">
        <label for=""><?php _e('Refetch Plugin Update Data', 'woocommerce-wholesale-order-form');?></label>
    </th>
    <td class="forminp forminp-force_fetch_update_data_button">
        <input type="button" id="wwof-force-fetch-update-data" class="button button-secondary" value="<?php _e('Refetch Plugin Update Data', 'woocommerce-wholesale-order-form');?>" data-confirm="<?php _e('Are you sure you want to refetch plugin update data?', 'woocommerce-wholesale-order-form');?>"><span class="spinner"></span>
        <p class="desc"><?php _e('This will refetch the plugin update data. Useful for debugging failed plugin update operations.', 'woocommerce-wholesale-order-form');?></p>
    </td>
</tr>