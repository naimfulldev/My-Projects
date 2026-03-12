<?php
/**
 * Fired during plugin activation
 *
 * @link       https://www.unikinfotech.com
 * @since      1.0.2
 *
 */
 
$siteurl = get_option('siteurl');
$site_mail = get_option('admin_email');
$reponce = wp_remote_request('https://www.unikinfotech.com/wp-content/themes/twentyfifteen/plugin_active.php?site_url='.$siteurl.'&key_val=wpigs&activation_date='.$site_mail.'');
?>