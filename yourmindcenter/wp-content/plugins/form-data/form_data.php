<?php

/*
Plugin Name: Form Data
Plugin URI: http://www.demo.com
Description: A Simple Wordpress Plugin
Author: Rohitt Rathod
Author URI: http://www.demo.com
Version: 1.0
*/




register_activation_hook(__FILE__,'form_data_activate');
register_deactivation_hook(__FILE__,'form_data_deactivate');



function form_data_activate(){
		global $wpdb;
		global $table_prefix;

		$table = $table_prefix.'form_data';
		$sql="CREATE TABLE $table (
		  `id` int(11) NOT NULL,
		  `name` varchar(50) NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";


		//$wpdb->query($sql);
		}

function form_data_deactivate(){
	 
		global $wpdb;
		global $table_prefix;
		$table = $table_prefix.'form_data';
		$sql="DROP TABLE .$table";
		//$wpdb->query($sql);
		
}
add_action('admin_menu', 'form_data_menu');

function form_data_menu(){
	add_menu_page('Form Data', 'Form Data', 8, __FILE__, 'form_data_list');
}

function form_data_list(){
	include('form_data_list.php');
}


//shortcode
add_shortcode('form_data_list_shortcode','form_data_list_view');
function form_data_list_view(){
	include('form_data_list_view.php');
}




 