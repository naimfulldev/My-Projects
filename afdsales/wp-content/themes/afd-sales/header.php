<!DOCTYPE html>
<html  <?php language_attributes(); ?> lang="en" dir="ltr">
     <head>
          <meta charset="<?php bloginfo('charset');?>">
          <!--<title><?php if(is_home()){ wp_title(); }else{ wp_title(); }?></title>-->
		  <meta name="viewport" content="width=device-width, initial-scale=1">
          <link rel="stylesheet" href="<?php echo get_template_directory_uri() ?>/css/bootstrap.min.css">
          <link rel="stylesheet" href="<?php echo get_template_directory_uri() ?>/css/owl.carousel.min.css">
		  <link rel="stylesheet" href="<?php echo get_template_directory_uri() ?>/css/custom.css">
		  <link rel="stylesheet" href="<?php echo get_template_directory_uri() ?>/css/responsive.css">
		  <link rel="stylesheet" href="<?php echo get_stylesheet_uri();?>">
	 	  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css"> 
			<?php wp_head();?>
     </head>
<body <?php body_class();?>>
<!---------------Header-start------------->
<div class="top_bar">
	<p><?php echo get_theme_mod('announcement_bar_text'); ?> <a href="<?php echo home_url('wholesale-registration-page');?>"> Create an Account </a></p>
</div>
<div class="middle_header">
	<div class="container_full">
		<ul class="list-unstyled">
			<li>
				<a href="<?php echo home_url('my-account');?>">
					<?php
					      $custom_logo_id = get_theme_mod('my_account_icon');
					      if ($custom_logo_id) {                      
					        _e('<img src="' . $custom_logo_id . '" alt="logo" class="img-responsive">');
					      } else {                      
					        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
					      }
				      ?>
					<!-- <img src="<?php echo get_template_directory_uri() ?>/image/user.png" alt="logo" class="img-responsive"> -->
					<?php echo get_theme_mod('my_account_text'); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo home_url('wishlist');?>">
					<?php
					      $custom_logo_id = get_theme_mod('wishlist_icon');
					      if ($custom_logo_id) {                      
					        _e('<img src="' . $custom_logo_id . '" alt="logo" class="img-responsive">');
					      } else {                      
					        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
					      }
				      ?>
					<!-- <img src="<?php echo get_template_directory_uri() ?>/image/heart.png" alt="logo" class="img-responsive"> -->
					<?php echo get_theme_mod('wishlist_text'); ?>
				</a>
			</li>
		</ul>
	</div>
</div>
<div class="main_header">
	<div class="container_full">
		<div class="header_part">
			<nav class="navbar navbar-expand-lg navbar-light">
			  <div class="header_part_new">
			    <a class="navbar-brand" href="<?php echo home_url(); ?>">
			    		<?php
					      $custom_logo_id = get_theme_mod('header_logo');
					      if ($custom_logo_id) {                      
					        _e('<img src="' . $custom_logo_id . '" alt="logo" class="img-responsive">');
					      } else {                      
					        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
					      }
				      ?>
			    		<!-- <img src="<?php echo get_template_directory_uri() ?>/image/afd_logo.png" alt="logo" class="img-responsive"> -->
			    	</a>
			    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			      <span class="navbar-toggler-icon"></span>
			    </button>
			    <div class="collapse navbar-collapse" id="navbarNav">
			      <ul class="navbar-nav">
			        <?php
					wp_nav_menu( array( 
						'theme_location' => 'header', 
						'items_wrap' => '%3$s',
						'container' => ''
					) ); 
				    ?>
			      </ul>
			    </div>
			    <div class="shoping_bag_part">
			    	<div class="search-part">
			    	    <?php
                            if(is_active_sidebar('sidebar1')){
                              dynamic_sidebar('sidebar1');
                            }
                          ?>
				        <!--<input type="text" name="s" value="" class="form-control" placeholder="Search" autocomplete="off">-->
				        <!--<span>
				        	<?php
					      $custom_logo_id = get_theme_mod('search_icon');
					      if ($custom_logo_id) {                      
					        _e('<img src="' . $custom_logo_id . '" alt="logo" class="img-responsive">');
					      } else {                      
					        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
					      }
				      	?>
				        	  <img src="<?php echo get_template_directory_uri() ?>/image/search.png" alt="logo" class="img-responsive"> 
				        </span>-->
			        </div>
			      	<a href="<?php echo home_url('cart');?>" class="shoping_bag">
			      		<?php
					      $custom_logo_id = get_theme_mod('cart_icon');
					      if ($custom_logo_id) {                      
					        _e('<img src="' . $custom_logo_id . '" alt="logo" class="img-responsive">');
					      } else {                      
					        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
					      }
				      	?>
				    </a>
				    <a href="<?php echo home_url('cart');?>" class="count-cart" name="update_cart">
					      <span id="count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
			      		<!--<img src="<?php echo get_template_directory_uri() ?>/image/shoping_bag.png" alt="logo" class="img-responsive">-->
			      	</a>
			    </div>
			  </div>
			</nav>
		</div>
	</div>
</div>
<!---------------Header-end------------->