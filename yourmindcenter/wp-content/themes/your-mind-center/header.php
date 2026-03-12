<!DOCTYPE html>
<html lang="en" <?php language_attributes(); ?> dir="ltr">
     <head>
          <meta charset="<?php bloginfo('charset');?>">
          <!-- <title> <?php the_title(); ?> -Your Mind Center</title> -->
          			
		  <meta name="viewport" content="width=device-width, initial-scale=1">
          <link rel="stylesheet" href="<?php echo get_template_directory_uri() ?>/css/bootstrap.min.css">
          <link rel="stylesheet" href="<?php echo get_template_directory_uri() ?>/css/owl.carousel.min.css">
		  <link rel="stylesheet" href="<?php echo get_template_directory_uri() ?>/css/custom.css">
		  <link rel="stylesheet" href="<?php echo get_template_directory_uri() ?>/css/responsive.css">
		  <link rel="stylesheet" href="<?php echo get_stylesheet_uri();?>">
		<?php wp_head();?>
		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-283560384-1"></script>
		 <script>
		         window.dataLayer = window.dataLayer || [];
		         function gtag(){dataLayer.push(arguments);}
		         gtag('js', new Date());
		         gtag('config', 'UA-283560384-1');
		</script>
     </head>

<body <?php body_class();?>>
<!---------------Header-start------------->
<div class="top_bar_part">
	<div class="top_bar">
		<div class="top_left">
			<h4 class="call_us">
				<?php
			      $custom_logo_id = get_theme_mod('callus_logo_header');
			      if ($custom_logo_id) {                      
			        _e('<img src="' . $custom_logo_id . '" alt="" class="img-responsive">');
			      } else {                      
			        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
			      }
			     ?>
				<!-- <img src="<?php echo get_template_directory_uri() ?>/image/phone-call.png" alt="social_icon" class="img-responsive"> -->
				<span><?php echo get_theme_mod('callus_btn_lable') ?>:</span> <a href="tel:<?php echo get_theme_mod('callus_details') ?>"><?php echo get_theme_mod('callus_details') ?></a></h4>
			<h4>
				<?php
			      $custom_logo_id = get_theme_mod('address_logo_header');
			      if ($custom_logo_id) {                      
			        _e('<img src="' . $custom_logo_id . '" alt="" class="img-responsive">');
			      } else {                      
			        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
			      }
			     ?>
				<!-- <img src="<?php echo get_template_directory_uri() ?>/image/pin.png" alt="social_icon" class="img-responsive"> -->
				<span><?php echo get_theme_mod('location_btn_lable') ?>:</span> <?php echo get_theme_mod('location_details') ?></h4>
		</div>
		<div class="top_right">
			<ul class="social list-unstyled">
				<li><a href="<?php echo get_theme_mod('instagram_link_header'); ?>" target="_blank">
					<?php
					      $custom_logo_id = get_theme_mod('insta_logo_header');
					      if ($custom_logo_id) {                      
					        _e('<img src="' . $custom_logo_id . '" alt="" class="img-responsive">');
					      } else {                      
					        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
					      }
					     ?>
					<!-- <img src="<?php echo get_template_directory_uri() ?>/image/instagram.png" alt="social_icon" class="img-responsive"> -->
				</a></li>
				<li><a href="<?php echo get_theme_mod('facebook_link_header'); ?>" target="_blank">
					<?php
					      $custom_logo_id = get_theme_mod('facebook_logo_header');
					      if ($custom_logo_id) {                      
					        _e('<img src="' . $custom_logo_id . '" alt="" class="img-responsive">');
					      } else {                      
					        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
					      }
					     ?>
					<!-- <img src="<?php echo get_template_directory_uri() ?>/image/facebook.png" alt="social_icon" class="img-responsive"> -->
				</a></li>
				<li><a href="<?php echo get_theme_mod('youtube_link_header'); ?>" target="_blank">
					<?php
					      $custom_logo_id = get_theme_mod('youtube_logo_header');
					      if ($custom_logo_id) {                      
					        _e('<img src="' . $custom_logo_id . '" alt="" class="img-responsive">');
					      } else {                      
					        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
					      }
					     ?>
					<!-- <img src="<?php echo get_template_directory_uri() ?>/image/youtube.png" alt="social_icon" class="img-responsive"> -->
				</a></li>
			</ul>
		</div>
	</div>
</div>
<div class="main_header">
	<div class="header_part">
		<nav class="navbar navbar-expand-lg navbar-light">
		  <div class="container-fluid">
		    <a class="navbar-brand" href="<?php echo home_url(); ?>">
		    	<?php
		      $custom_logo_id = get_theme_mod('header_logo');
		      if ($custom_logo_id) {                      
		        _e('<img src="' . $custom_logo_id . '" alt="" class="main_logo img-responsive">');
		      } else {                      
		        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
		      }
		      ?>
		      <?php
		      $custom_logo_id = get_theme_mod('mobile_logo');
		      if ($custom_logo_id) {                      
		        _e('<img src="' . $custom_logo_id . '" alt="" class="mob_logo img-responsive">');
		      } else {                      
		        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
		      }
		      ?>
		    	<!-- <img src="<?php echo get_template_directory_uri() ?>/image/main_logo.png" alt="logo" class="main_logo img-responsive">
		    	<img src="<?php echo get_template_directory_uri() ?>/image/mob_logo.png" alt="logo" class="mob_logo img-responsive"> -->
		    </a>
		    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
		      <span class="navbar-toggler-icon"></span>
		    </button>
		    <div class="collapse navbar-collapse" id="navbarNav">
		      <ul class="navbar-nav" id="MainMenu">
		        <!-- <li class="nav-item">
		          <a class="nav-link active" aria-current="page" href="#">Services</a>
		        </li> -->
		        <?php
				wp_nav_menu( array( 
					'theme_location' => 'header', 
					'items_wrap' => '%3$s',
					'container' => ''
				) ); 
				?>
		      </ul>
		      <div class="header_btn">
		      	<a href="<?php echo home_url().'/'. get_theme_mod('consultation_link'); ?>" class="consultation_btn"><?php echo get_theme_mod('consultation_btn_lable') ?></a>
		      </div>
		    </div>
		  </div>
		</nav>
	</div>
</div>
<?php $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
echo $_SERVER['REQUEST_URI']; ?>
<!---------------Header-end------------->