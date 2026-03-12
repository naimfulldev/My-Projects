<?php
	//session_start();
	add_theme_support('menus');
	function my_primary_menu()
	{
	 register_nav_menus(array(
	 'header'=>__('Header Menu'),
	 'footer' => __('Footer Menu')
	 ));
	}
	add_action('init','my_primary_menu');
	add_theme_support('widgets');
	add_theme_support('custom-logo');
	add_theme_support( 'title-tag' );
	
	add_theme_support( 'post-thumbnails' ); 	//	it's required for support image fetch
	add_filter('nav_menu_css_class', 'add_active_class', 10, 2 );
	function add_active_class($classes, $item) {
	  if( $item->menu_item_parent == 0 &&
	    in_array( 'current-menu-item', $classes ) ||
	    in_array( 'current-menu-ancestor', $classes ) ||
	    in_array( 'current-menu-parent', $classes ) ||
	    in_array( 'current_page_parent', $classes ) ||
	    in_array( 'current_page_ancestor', $classes ) ||
	    in_array( 'current-menu-parent-item', $classes ) ||
		in_array('current_url', $classes)
	    ) {
	    $classes[] = "active";
	  }
	  return $classes;
	}

	/*function register_scripts() {
	  if ( !is_admin() ) {
	    // include your script
	    wp_enqueue_script( 'email-confirm',get_template_directory_uri() . '/js/email-confirm.js' );
	  }
	}
	add_action( 'wp_enqueue_scripts', 'register_scripts' );*/

	add_filter( 'wpcf7_validate_email*', 'custom_email_confirmation_validation_filter', 20, 2 );
	function custom_email_confirmation_validation_filter( $result, $tag ) {
	  if ( 'confirm-email' == $tag->name ) {
	    $your_email = isset( $_POST['email'] ) ? trim( $_POST['email'] ) : '';
	    $your_email_confirm = isset( $_POST['confirm-email'] ) ? trim( $_POST['confirm-email'] ) : '';
	    if ( $your_email != $your_email_confirm ) {
	      $result->invalidate( $tag, "Are you sure this is the correct address?" );
	    }
	  }
	  return $result;
	}

	add_action('customize_register', 'your_mind_center_theme_customizer');
	function your_mind_center_theme_customizer($wp_customize)
	{
		//header start
		$wp_customize->add_section('header_section', array(
		'title'          => 'Header Section',
		'priority'   => 31
		));
		//web-logo
		$wp_customize->add_setting('header_logo', array(
			'default'  => '',
			'type'     => 'theme_mod',
			'capability' => 'edit_theme_options',
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'custom1_logo', array(
			'label' => __( 'Web Logo', 'your-mind-center' ), //__(Footer Name,themename)
			'section' => 'header_section', //(Section in which it stands Header,Footer etc.)
			'settings' => 'header_logo',
		)));
		//mobile-logo
		$wp_customize->add_setting('mobile_logo', array(
			'default'  => '',
			'type'     => 'theme_mod',
			'capability' => 'edit_theme_options',
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'custom_logo', array(
			'label' => __( 'Mobile Logo', 'your-mind-center' ), //__(Footer Name,themename)
			'section' => 'header_section', //(Section in which it stands Header,Footer etc.)
			'settings' => 'mobile_logo',
		)));

		//instagram-logo
		$wp_customize->add_setting('callus_logo_header', array(
			'default'  => '',
			'type'     => 'theme_mod',
			'capability' => 'edit_theme_options',
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'custom9_logo', array(
			'label' => __( 'CallUs Icon', 'your-mind-center' ), //__(Footer Name,themename)
			'section' => 'header_section', //(Section in which it stands Header,Footer etc.)
			'settings' => 'callus_logo_header',
		)));
		//CallUs button text
		$wp_customize->add_setting('callus_btn_lable', array(
			'default'  => '',
		));	
		$wp_customize->add_control('callus_btn_lable', array(
			'label'   => 'Call Us Button Text',
			'section' => 'header_section',
			'type'    => 'text',
		));
		//CallUs Details
		$wp_customize->add_setting('callus_details', array(
			'default'  => '',
		));	
		$wp_customize->add_control('callus_details', array(
			'label'   => 'Call Us Details',
			'section' => 'header_section',
			'type'    => 'text',
		));
		//address-logo
		$wp_customize->add_setting('address_logo_header', array(
			'default'  => '',
			'type'     => 'theme_mod',
			'capability' => 'edit_theme_options',
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'custom8_logo', array(
			'label' => __( 'Address Icon', 'your-mind-center' ), //__(Footer Name,themename)
			'section' => 'header_section', //(Section in which it stands Header,Footer etc.)
			'settings' => 'address_logo_header',
		)));
		//Location button text
		$wp_customize->add_setting('location_btn_lable', array(
			'default'  => '',
		));	
		$wp_customize->add_control('location_btn_lable', array(
			'label'   => 'Location Button Text',
			'section' => 'header_section',
			'type'    => 'text',
		));
		//Location Details
		$wp_customize->add_setting('location_details', array(
			'default'  => '',
		));	
		$wp_customize->add_control('location_details', array(
			'label'   => 'Location Details',
			'section' => 'header_section',
			'type'    => 'text',
		));

		//instagram-logo
		$wp_customize->add_setting('insta_logo_header', array(
			'default'  => '',
			'type'     => 'theme_mod',
			'capability' => 'edit_theme_options',
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'custom5_logo', array(
			'label' => __( 'Instagram Icon', 'your-mind-center' ), //__(Footer Name,themename)
			'section' => 'header_section', //(Section in which it stands Header,Footer etc.)
			'settings' => 'insta_logo_header',
		)));
		//instgram link
		$wp_customize->add_setting('instagram_link_header', array(
			'default'  => '',
		));	
		$wp_customize->add_control('instagram_link_header', array(
			'label'   => 'Instagram button Link',
			'section' => 'header_section',
			'type'    => 'url',
		));
		//facebook-logo
		$wp_customize->add_setting('facebook_logo_header', array(
			'default'  => '',
			'type'     => 'theme_mod',
			'capability' => 'edit_theme_options',
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'custom6_logo', array(
			'label' => __( 'Facebook Icon', 'your-mind-center' ), //__(Footer Name,themename)
			'section' => 'header_section', //(Section in which it stands Header,Footer etc.)
			'settings' => 'facebook_logo_header',
		)));
		//facebook link
		$wp_customize->add_setting('facebook_link_header', array(
			'default'  => '',
		));	
		$wp_customize->add_control('facebook_link_header', array(
			'label'   => 'Facebook button Link',
			'section' => 'header_section',
			'type'    => 'url',
		));
		//youtube-logo
		$wp_customize->add_setting('youtube_logo_header', array(
			'default'  => '',
			'type'     => 'theme_mod',
			'capability' => 'edit_theme_options',
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'custom7_logo', array(
			'label' => __( 'LinkedIn Icon', 'your-mind-center' ), //__(Footer Name,themename)
			'section' => 'header_section', //(Section in which it stands Header,Footer etc.)
			'settings' => 'youtube_logo_header',
		)));
		//youtube link
		$wp_customize->add_setting('youtube_link_header', array(
			'default'  => '',
		));	
		$wp_customize->add_control('youtube_link_header', array(
			'label'   => 'LinkedIn button Link',
			'section' => 'header_section',
			'type'    => 'url',
		));

		

		//cunsultation button text
		$wp_customize->add_setting('consultation_btn_lable', array(
			'default'  => '',
		));	
		$wp_customize->add_control('consultation_btn_lable', array(
			'label'   => 'Consultation Button Text',
			'section' => 'header_section',
			'type'    => 'text',
		));
		//cunsultation Link
		$wp_customize->add_setting('consultation_link', array(
			'default'  => '',
		));	
		$wp_customize->add_control('consultation_link', array(
			'label'   => 'Consultation Link',
			'section' => 'header_section',
			'type'    => 'url',
		));

		//header end

		//footer start
		//footer logo
		$wp_customize->add_section('footer_section', array(
			'title'          => 'Footer Section',
			'priority'   => 31
		));

		$wp_customize->add_setting('footer_logo', array(
			'default'  => '',
			'type'     => 'theme_mod',
			'capability' => 'edit_theme_options',
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'header_logo', array(
			'label' => __( 'footer Logo', 'your-mind-center' ), //__(Footer Name,themename)
			'section' => 'footer_section', //(Section in which it stands Header,Footer etc.)
			'settings' => 'footer_logo',
		)));

		//copyright text
		$wp_customize->add_setting('copyright_text', array(
			'default'  => '',
		));	
		$wp_customize->add_control('copyright_text', array(
			'label'   => 'Copyright Text',
			'section' => 'footer_section',
			'type'    => 'text',
		));

		//Privacy Button text
		$wp_customize->add_setting('privacy_text', array(
			'default'  => '',
		));	
		$wp_customize->add_control('privacy_text', array(
			'label'   => 'Privacy button text',
			'section' => 'footer_section',
			'type'    => 'text',
		));

		// Privacy Link
		$wp_customize->add_setting('privacy_link', array(
			'default'  => '',
		));	
		$wp_customize->add_control('privacy_link', array(
			'label'   => 'Privacy button Link',
			'section' => 'footer_section',
			'type'    => 'url',
		));

		//Terms Button text
		$wp_customize->add_setting('terms_text', array(
			'default'  => '',
		));	
		$wp_customize->add_control('terms_text', array(
			'label'   => 'Terms button text',
			'section' => 'footer_section',
			'type'    => 'text',
		));

		// Terms Link
		$wp_customize->add_setting('terms_link', array(
			'default'  => '',
		));	
		$wp_customize->add_control('terms_link', array(
			'label'   => 'Terms button link',
			'section' => 'footer_section',
			'type'    => 'url',
		));

		//contact section Title
		$wp_customize->add_setting('contact_section_title', array(
			'default'  => '',
		));	
		$wp_customize->add_control('contact_section_title', array(
			'label'   => 'ContactUS Section Title',
			'section' => 'footer_section',
			'type'    => 'textarea',
		));
		//contact button text
		$wp_customize->add_setting('contact_btn_lable', array(
			'default'  => '',
		));	
		$wp_customize->add_control('contact_btn_lable', array(
			'label'   => 'ContactUS Button Text',
			'section' => 'footer_section',
			'type'    => 'text',
		));

		//Contact Button Link
		$wp_customize->add_setting('contact_link', array(
			'default'  => '',
		));	
		$wp_customize->add_control('contact_link', array(
			'label'   => 'ContactUS Link',
			'section' => 'footer_section',
			'type'    => 'text',
		));

		//instagram-logo
		$wp_customize->add_setting('insta_logo_footer', array(
			'default'  => '',
			'type'     => 'theme_mod',
			'capability' => 'edit_theme_options',
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'custom2_logo', array(
			'label' => __( 'Instagram Logo', 'your-mind-center' ), //__(Footer Name,themename)
			'section' => 'footer_section', //(Section in which it stands Header,Footer etc.)
			'settings' => 'insta_logo_footer',
		)));
		//instgram link
		$wp_customize->add_setting('instagram_link_footer', array(
			'default'  => '',
		));	
		$wp_customize->add_control('instagram_link_footer', array(
			'label'   => 'Instagram button Link',
			'section' => 'footer_section',
			'type'    => 'url',
		));
		//facebook-logo
		$wp_customize->add_setting('facebook_logo_footer', array(
			'default'  => '',
			'type'     => 'theme_mod',
			'capability' => 'edit_theme_options',
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'custom3_logo', array(
			'label' => __( 'Facebook Logo', 'your-mind-center' ), //__(Footer Name,themename)
			'section' => 'footer_section', //(Section in which it stands Header,Footer etc.)
			'settings' => 'facebook_logo_footer',
		)));
		//facebook link
		$wp_customize->add_setting('facebook_link_footer', array(
			'default'  => '',
		));	
		$wp_customize->add_control('facebook_link_footer', array(
			'label'   => 'Facebook button Link',
			'section' => 'footer_section',
			'type'    => 'url',
		));
		//youtube-logo
		$wp_customize->add_setting('youtube_logo_footer', array(
			'default'  => '',
			'type'     => 'theme_mod',
			'capability' => 'edit_theme_options',
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'custom4_logo', array(
			'label' => __( 'LinkedIn Logo', 'your-mind-center' ), //__(Footer Name,themename)
			'section' => 'footer_section', //(Section in which it stands Header,Footer etc.)
			'settings' => 'youtube_logo_footer',
		)));
		//youtube link
		$wp_customize->add_setting('youtube_link_footer', array(
			'default'  => '',
		));	
		$wp_customize->add_control('youtube_link_footer', array(
			'label'   => 'LinkedIn button Link',
			'section' => 'footer_section',
			'type'    => 'url',
		));
		

		
		//footer end
	}
	// Remove <p> and <br/> from Contact Form 7
	add_filter('wpcf7_autop_or_not', '__return_false');


	// Product Custom Post Type
function product_init() {
    // set up product labels
    $labels = array(
        'name' => 'Products',
        'singular_name' => 'Product',
        'add_new' => 'Add New Product',
        'add_new_item' => 'Add New Product',
        'edit_item' => 'Edit Product',
        'new_item' => 'New Product',
        'all_items' => 'All Products',
        'view_item' => 'View Product',
        'search_items' => 'Search Products',
        'not_found' =>  'No Products Found',
        'not_found_in_trash' => 'No Products found in Trash', 
        'parent_item_colon' => '',
        'menu_name' => 'Products',
    );
    
    // register post type
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'show_ui' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => array('slug' => 'product'),
        'query_var' => true,
        'menu_icon' => 'dashicons-admin-post',
        'supports' => array(
            'title',
            'editor',
            'excerpt',
            'trackbacks',
            'custom-fields',
            'comments',
            'revisions',
            'thumbnail',
            'author',
            'page-attributes'
        )
    );
    register_post_type( 'product', $args );
    
    // register taxonomy
    register_taxonomy('product_category', 'product', array('hierarchical' => true, 'label' => 'Category', 'query_var' => true, 'rewrite' => array( 'slug' => 'product-category' )));
}
add_action( 'init', 'product_init' );


//contact form custom validation specific form

add_action( 'wpcf7_before_send_mail', 'wpcf7_add_text_to_mail_body' );

/*function wpcf7_add_text_to_mail_body($contact_form){

 $form_id = $contact_form->posted_data['_wpcf7'];
 if ($form_id == 447): // 123 => Your Form ID.
     echo "<script>alert('447');</script>";
 endif;

}*/

/*add_action( 'wpcf7_mail_sent', 'wp190913_wpcf7' );

function wp190913_wpcf7( $contact_form ) {

    // Not my desired form? bail
    if ( $contact_form->id !== $myform_id )
        return;

    // Do stuff for my contact form
}*/

/*function my_wpcf7_validate_text( $result, $tag ) {

    $type = $tag['type'];
    $name = $tag['your-name'];
    $value = $_POST[$name] ;

    if ( strpos( $name , 'your-name' ) !== false ){
        $regex = '/^[a-zA-Z]+$/';
        $Valid = preg_match($regex,  $value, $matches );
        if ( $Valid > 0 ) {
        } else {
            $result->invalidate( $tag, wpcf7_get_message( 'invalid_name' ) );
        }
    }
    return $result;
}
add_filter( 'wpcf7_validate_text*', 'my_wpcf7_validate_text' , 10, 2 );

add_filter( 'wpcf7_messages', 'mywpcf7_text_messages' );
function mywpcf7_text_messages( $messages ) {
    return array_merge( $messages, array(
        'invalid_name' => array(
            'description' => __( "Name is invalid", 'contact-form-7' ),
            'default' => __( 'Name seems invalid.', 'contact-form-7' )
        )
    ));
}*/

add_filter( 'wpcf7_validate_text*', 'custom_textarea_validation_filter', 10, 2 );

function custom_textarea_validation_filter( $result, $tag ) {
  //$form_id = $contact_form->posted_data['_wpcf7'];
  //if($contact_form->id() == 447){
	  $tag = new WPCF7_Shortcode($tag);
	  $result = (object)$result;

	  $name = 'your-name';

	  if ( $name == $tag->name ) {
	    $project_synopsis = $_POST[$name];

	    if (  $project_synopsis == 'a' ) {
	      $result->invalidate( $tag, "Please write a quick project synopsis." );
	    }
	  }

	  return $result;
  /*}
  else{return;}*/
}