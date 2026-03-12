<?php

session_start();
add_theme_support('menus');
function my_primary_menu()
{
	register_nav_menus(array(
		'header' => __('Header Menu'),
		'footer_products' => __('Footer Products Menu'),
		'footer_help' => __('Footer Help Menu'),
		'footer_company' => __('Footer Company Menu')
	));
}
add_action('init', 'my_primary_menu');
add_theme_support('widgets');
add_theme_support('custom-logo');
add_theme_support('title-tag');
add_theme_support('woocommerce');
add_theme_support('wc-product-gallery-zoom');
add_theme_support('wc-product-gallery-lightbox');
add_theme_support('wc-product-gallery-slider');
add_theme_support('post-thumbnails'); 	//	it's required for support image fetch
function add_link_atts($atts)
{
	$atts['class'] = "nav-link";
	return $atts;
}
add_filter('nav_menu_link_attributes', 'add_link_atts');
add_filter('nav_menu_css_class', function ($classes) {
	$classes[] = 'nav-item';
	return $classes;
}, 10, 1);


add_action('customize_register', 'afd_sales_theme_customizer');
function afd_sales_theme_customizer($wp_customize)
{
	//header start
	$wp_customize->add_section('header_section', array(
		'title' => 'Header Section',
		'priority' => 31
	));

	//create account text
	$wp_customize->add_setting('announcement_bar_text', array(
		'default' => '',
	));
	$wp_customize->add_control('announcement_bar_text', array(
		'label' => 'Announcement Bar Text',
		'section' => 'header_section',
		'type' => 'text',
	));


	//web-logo
	$wp_customize->add_setting('header_logo', array(
		'default' => '',
		'type' => 'theme_mod',
		'capability' => 'edit_theme_options',
	));

	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'custom1_logo', array(
		'label' => __('Logo', 'afd_sales'), //__(Footer Name,themename)
		'section' => 'header_section', //(Section in which it stands Header,Footer etc.)
		'settings' => 'header_logo',
	)));


	//my_account_icon
	$wp_customize->add_setting('my_account_icon', array(
		'default' => '',
		'type' => 'theme_mod',
		'capability' => 'edit_theme_options',
	));

	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'custom2_logo', array(
		'label' => __('Login Icon', 'afd_sales'), //__(Footer Name,themename)
		'section' => 'header_section', //(Section in which it stands Header,Footer etc.)
		'settings' => 'my_account_icon',
	)));


	//my_account text
	$wp_customize->add_setting('my_account_text', array(
		'default' => '',
	));
	$wp_customize->add_control('my_account_text', array(
		'label' => 'Login Text',
		'section' => 'header_section',
		'type' => 'text',
	));

	//wishlist_icon
	$wp_customize->add_setting('wishlist_icon', array(
		'default' => '',
		'type' => 'theme_mod',
		'capability' => 'edit_theme_options',
	));

	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'custom3_logo', array(
		'label' => __('Wishlist Icon', 'afd_sales'), //__(Footer Name,themename)
		'section' => 'header_section', //(Section in which it stands Header,Footer etc.)
		'settings' => 'wishlist_icon',
	)));


	//wishlist text
	$wp_customize->add_setting('wishlist_text', array(
		'default' => '',
	));
	$wp_customize->add_control('wishlist_text', array(
		'label' => 'Wishlist Text',
		'section' => 'header_section',
		'type' => 'text',
	));

	//search_icon
	$wp_customize->add_setting('search_icon', array(
		'default' => '',
		'type' => 'theme_mod',
		'capability' => 'edit_theme_options',
	));

	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'custom4_logo', array(
		'label' => __('Search Icon', 'afd_sales'), //__(Footer Name,themename)
		'section' => 'header_section', //(Section in which it stands Header,Footer etc.)
		'settings' => 'search_icon',
	)));

	//cart_icon
	$wp_customize->add_setting('cart_icon', array(
		'default' => '',
		'type' => 'theme_mod',
		'capability' => 'edit_theme_options',
	));

	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'custom5_logo', array(
		'label' => __('Cart Icon', 'afd_sales'), //__(Footer Name,themename)
		'section' => 'header_section', //(Section in which it stands Header,Footer etc.)
		'settings' => 'cart_icon',
	)));

	//footer top
	$wp_customize->add_section('footer_top_section', array(
		'title' => 'Footer Top Section',
		'priority' => 31
	));

	//footer icon
	$wp_customize->add_setting('footer_logo', array(
		'default' => '',
		'type' => 'theme_mod',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'custom6_logo', array(
		'label' => __('Footer Logo', 'afd_sales'), //__(Footer Name,themename)
		'section' => 'footer_top_section', //(Section in which it stands Header,Footer etc.)
		'settings' => 'footer_logo',
	)));

	//phone text
	$wp_customize->add_setting('phone_text', array(
		'default' => '',
	));
	$wp_customize->add_control('phone_text', array(
		'label' => 'Phone Number',
		'section' => 'footer_top_section',
		'type' => 'text',
	));

	//email text
	$wp_customize->add_setting('email_text', array(
		'default' => '',
	));
	$wp_customize->add_control('email_text', array(
		'label' => 'Email',
		'section' => 'footer_top_section',
		'type' => 'text',
	));

	//footer bottom
	$wp_customize->add_section('footer_bottom_section', array(
		'title' => 'Footer Bottom Section',
		'priority' => 31
	));

	//copyright text
	$wp_customize->add_setting('copyright_text', array(
		'default' => '',
	));
	$wp_customize->add_control('copyright_text', array(
		'label' => 'Copyright Text',
		'section' => 'footer_bottom_section',
		'type' => 'text',
	));

	//privacy text
	$wp_customize->add_setting('privacy_text', array(
		'default' => '',
	));
	$wp_customize->add_control('privacy_text', array(
		'label' => 'Privacy Text',
		'section' => 'footer_bottom_section',
		'type' => 'text',
	));

	//privacy link
	$wp_customize->add_setting('privacy_link', array(
		'default' => '',
	));
	$wp_customize->add_control('privacy_link', array(
		'label' => 'Privacy Link',
		'section' => 'footer_bottom_section',
		'type' => 'url',
	));

	//terms text
	$wp_customize->add_setting('terms_text', array(
		'default' => '',
	));
	$wp_customize->add_control('terms_text', array(
		'label' => 'Terms Text',
		'section' => 'footer_bottom_section',
		'type' => 'text',
	));

	//terms link
	$wp_customize->add_setting('terms_link', array(
		'default' => '',
	));
	$wp_customize->add_control('terms_link', array(
		'label' => 'Terms Link',
		'section' => 'footer_bottom_section',
		'type' => 'url',
	));


	//insta_icon
	$wp_customize->add_setting('insta_icon', array(
		'default' => '',
		'type' => 'theme_mod',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'custom7_logo', array(
		'label' => __('Instagram Icon', 'afd_sales'), //__(Footer Name,themename)
		'section' => 'footer_bottom_section', //(Section in which it stands Header,Footer etc.)
		'settings' => 'insta_icon',
	)));

	//insta link
	$wp_customize->add_setting('insta_link', array(
		'default' => '',
	));
	$wp_customize->add_control('insta_link', array(
		'label' => 'Instagram Link',
		'section' => 'footer_bottom_section',
		'type' => 'url',
	));

	//facebook_icon
	$wp_customize->add_setting('facebook_icon', array(
		'default' => '',
		'type' => 'theme_mod',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'custom8_logo', array(
		'label' => __('Facebook Icon', 'afd_sales'), //__(Footer Name,themename)
		'section' => 'footer_bottom_section', //(Section in which it stands Header,Footer etc.)
		'settings' => 'facebook_icon',
	)));

	//facebook link
	$wp_customize->add_setting('facebook_link', array(
		'default' => '',
	));
	$wp_customize->add_control('facebook_link', array(
		'label' => 'Facebook Link',
		'section' => 'footer_bottom_section',
		'type' => 'url',
	));

	//linkedin_icon
	$wp_customize->add_setting('linkedin_icon', array(
		'default' => '',
		'type' => 'theme_mod',
		'capability' => 'edit_theme_options',
	));
	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'custom9_logo', array(
		'label' => __('LinkedIN Icon', 'afd_sales'), //__(Footer Name,themename)
		'section' => 'footer_bottom_section', //(Section in which it stands Header,Footer etc.)
		'settings' => 'linkedin_icon',
	)));

	//linkedin link
	$wp_customize->add_setting('linkedin_link', array(
		'default' => '',
	));
	$wp_customize->add_control('linkedin_link', array(
		'label' => 'LinkedIN Link',
		'section' => 'footer_bottom_section',
		'type' => 'url',
	));

}

//product tag heirarchical true
function my_woocommerce_make_tags_hierarchical($args)
{
	$args['hierarchical'] = true;
	return $args;
}
;
add_filter('woocommerce_taxonomy_args_product_tag', 'my_woocommerce_make_tags_hierarchical');

//create max variation
define('WC_MAX_LINKED_VARIATIONS', 1500);


//quantity input change to select box
function woocommerce_quantity_input($args = array(), $product = null, $echo = true)
{

	if (is_null($product)) {
		$product = $GLOBALS['product'];
	}

	$defaults = array(
		'input_id' => uniqid('quantity_'),
		'input_name' => 'quantity',
		'input_value' => '1',
		'classes' => apply_filters('woocommerce_quantity_input_classes', array('input-text', 'qty', 'text'), $product),
		'max_value' => 10,
		'min_value' => apply_filters('woocommerce_quantity_input_min', 0, $product),
		'step' => apply_filters('woocommerce_quantity_input_step', 1, $product),
		'pattern' => apply_filters('woocommerce_quantity_input_pattern', has_filter('woocommerce_stock_amount', 'intval') ? '[0-9]*' : ''),
		'inputmode' => apply_filters('woocommerce_quantity_input_inputmode', has_filter('woocommerce_stock_amount', 'intval') ? 'numeric' : ''),
		'product_name' => $product ? $product->get_title() : '',
	);

	$args = apply_filters('woocommerce_quantity_input_args', wp_parse_args($args, $defaults), $product);

	// Apply sanity to min/max args - min cannot be lower than 0.
	$args['min_value'] = max($args['min_value'], 0);
	// Note: change 20 to whatever you like
	$args['max_value'] = 0 < $args['max_value'] ? $args['max_value'] : 15;

	// Max cannot be lower than min if defined.
	if ('' !== $args['max_value'] && $args['max_value'] < $args['min_value']) {
		$args['max_value'] = $args['min_value'];
	}

	$options = '';
	if ($args['input_value'] > $args['max_value'] && !empty($args['input_value'])) {
		for ($count = $args['min_value']; $count <= $args['max_value']; $count = $count + $args['step']) {

			$options .= '<option value="' . $count . '"' . $selected . '>' . $count . '</option>';

		}
		$selected = 'selected';
		$options .= '<option value="' . $args['input_value'] . '"' . $selected . '>' . $args['input_value'] . '</option>';
	} else {
		for ($count = $args['min_value']; $count <= $args['max_value']; $count = $count + $args['step']) {

			// Cart item quantity defined?
			if ('' !== $args['input_value'] && $args['input_value'] >= 1 && $count == $args['input_value']) {
				$selected = 'selected';
			} else
				$selected = '';

			$options .= '<option value="' . $count . '"' . $selected . '>' . $count . '</option>';

		}
	}

	$string = '<div class="quantity"><select name="' . $args['input_name'] . '">' . $options . '</select></div>';

	if ($echo) {
		echo $string;
	} else {
		return $string;
	}

}
add_filter('woocommerce_quantity_input_args', 'woocommerce_quantity_input_args_callback', 10, 2);
function woocommerce_quantity_input_args_callback($args, $product)
{
	$args['max_value'] = 10;

	return $args;
}

//shop page sorting
//price hight to low 
add_filter('woocommerce_catalog_orderby', 'misha_rename_default_sorting_options');
function misha_rename_default_sorting_options($options)
{

	unset($options['menu_order']); // remove
	$options['menu_order'] = 'Featured'; // rename

	unset($options['date']); // remove
	//$options[ 'date' ] = 'Newest'; // rename

	unset($options['price']); // remove
	$options['price'] = 'Price (Low-High)'; // rename

	unset($options['price-desc']); // remove
	$options['price-desc'] = 'Price (High-Low)'; // rename

	return $options;

}


//alphabetical order sorting
function alphabetical_shop_ordering($sort_args)
{
	$orderby_value = isset($_GET['orderby']) ? woocommerce_clean($_GET['orderby']) : apply_filters('woocommerce_default_catalog_orderby', get_option('woocommerce_default_catalog_orderby'));
	if ('alphabetical' == $orderby_value) {
		$sort_args['orderby'] = 'title';
		$sort_args['order'] = 'asc';
		$sort_args['meta_key'] = '';
	}
	if ('alphabeticall' == $orderby_value) {
		$sort_args['orderby'] = 'title';
		$sort_args['order'] = 'desc';
		$sort_args['meta_key'] = '';
	}
	return $sort_args;
}
add_filter('woocommerce_get_catalog_ordering_args', 'alphabetical_shop_ordering');

function custom_wc_catalog_orderby($sortby)
{
	$sortby['alphabetical'] = 'Alphabetical (A-Z)';
	$sortby['alphabeticall'] = 'Alphabetical (Z-A)';
	return $sortby;
}
add_filter('woocommerce_default_catalog_orderby_options', 'custom_wc_catalog_orderby');
add_filter('woocommerce_catalog_orderby', 'custom_wc_catalog_orderby');


add_filter('woocommerce_catalog_orderby', 'misha_remove_default_sorting_options');

function misha_remove_default_sorting_options($options)
{
	$options['date'] = 'Newest';
	unset($options['popularity']);
	//unset( $options[ 'menu_order' ] );
	unset($options['rating']);
	//unset( $options[ 'date' ] );
	//unset( $options[ 'price' ] );
	//unset( $options[ 'price-desc' ] );

	return $options;

}

// Filters
add_filter('woocommerce_get_catalog_ordering_args', 'custom_woocommerce_get_catalog_ordering_args');
add_filter('woocommerce_default_catalog_orderby_options', 'custom_woocommerce_catalog_orderby');
add_filter('woocommerce_catalog_orderby', 'custom_woocommerce_catalog_orderby');

// Apply custom args to main query
function custom_woocommerce_get_catalog_ordering_args($args)
{

	$orderby_value = isset($_GET['orderby']) ? woocommerce_clean($_GET['orderby']) : apply_filters('woocommerce_default_catalog_orderby', get_option('woocommerce_default_catalog_orderby'));

	if ('oldest_to_recent' == $orderby_value) {
		$args['orderby'] = 'date';
		$args['order'] = 'ASC';
	}

	return $args;
}

// Create new sorting method
function custom_woocommerce_catalog_orderby($sortby)
{

	$sortby['oldest_to_recent'] = __('Oldest', 'woocommerce');

	return $sortby;
}

/*update cart in header */
add_filter('woocommerce_add_to_cart_fragments', 'header_add_to_cart_fragment', 30, 1);
function header_add_to_cart_fragment($fragments)
{
	global $woocommerce;

	ob_start();

	?>
	<a href="<?php echo home_url('cart'); ?>" class="count-cart" name="update_cart">
		<span id="count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
		<!-- <span><?php global $woocommerce;
		echo $woocommerce->cart->get_cart_total(); ?></span> -->
	</a>

	<?php
	$fragments['a.count-cart'] = ob_get_clean();
	//print_r($fragments);
	return $fragments;
}

//search widget
function widget_init()
{

	register_sidebar(
		array(
			'name' => __('Search', 'afd-sales'), // __(Widget Area Name,Theme Name)
			'id' => 'sidebar1',
			'before_widget' => '<section >',
			'after_widget' => '</section>',

		)
	);
}
add_action('widgets_init', 'widget_init');


//change title seperator of any page
add_filter('document_title_separator', 'wpse_set_document_title_separator');

function wpse_set_document_title_separator($sep)
{
	return ('|');
}


//Add Custom Data as Metadata to the Order Items
add_action('woocommerce_before_order_itemmeta', 'my_custom_checkout_field_display_admin_order_meta', 10, 3);
function my_custom_checkout_field_display_admin_order_meta($item_id, $item, $product)
{

	//var_dump($product->get_attributes());
	$value = $product->get_attribute('color');
	if (!empty($value)) {
		echo '<p><strong>' . __('Color') . ':</strong> ' . $value . '</p>';
	}
	$value1 = $product->get_attribute('size');
	if (!empty($value1)) {
		echo '<p><strong>' . __('Size') . ':</strong> ' . $value1 . '</p>';
	}

}
