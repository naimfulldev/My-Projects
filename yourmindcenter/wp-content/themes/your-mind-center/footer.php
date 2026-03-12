
<div class="footer_section">
	<div class="container_full">
		<div class="footer_part">
			<div class="footer_logo">
			<a href="<?php echo home_url() ?>" >
			<?php
		      $custom_logo_id = get_theme_mod('footer_logo');
		      if ($custom_logo_id) {                      
		        _e('<img src="' . $custom_logo_id . '" alt="" class="main_logo img-responsive">');
		      } else {                      
		        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
		      }
		      ?></a>
				<!-- <img src="<?php echo get_template_directory_uri() ?>/image/footer_logo.png" alt="logo" class="img-responsive"> -->
			</div>
			<div class="footer_nav">
				<ul class="list-unstyled">
					<?php
					wp_nav_menu( array( 
						'theme_location' => 'footer', 
						'items_wrap' => '%3$s',
						'container' => ''
					) ); 
					?>
				</ul>
			</div>
			<div class="footer_social">
				<ul class="footer_social list-unstyled">
					<li><a href="<?php echo get_theme_mod('instagram_link_footer'); ?>" target="_blank">
						<?php
					      $custom_logo_id = get_theme_mod('insta_logo_footer');
					      if ($custom_logo_id) {                      
					        _e('<img src="' . $custom_logo_id . '" alt="" class="img-responsive">');
					      } else {                      
					        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
					      }
					     ?>
						<!-- <img src="<?php echo get_template_directory_uri() ?>/image/instagram-footer.png" alt="logo" class="img-responsive"> -->
					</a></li>
					<li><a href="<?php echo get_theme_mod('facebook_link_footer'); ?>" target="_blank">
						<!-- <img src="<?php echo get_template_directory_uri() ?>/image/facebook_footer.png" alt="logo" class="img-responsive"> -->
						<?php
					      $custom_logo_id = get_theme_mod('facebook_logo_footer');
					      if ($custom_logo_id) {                      
					        _e('<img src="' . $custom_logo_id . '" alt="" class="img-responsive">');
					      } else {                      
					        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
					      }
					     ?>
					</a></li>
					<li><a href="<?php echo get_theme_mod('youtube_link_footer'); ?>" target="_blank">
						<!-- <img src="<?php echo get_template_directory_uri() ?>/image/youtube-footer.png" alt="logo" class="img-responsive"> -->
						<?php
					      $custom_logo_id = get_theme_mod('youtube_logo_footer');
					      if ($custom_logo_id) {                      
					        _e('<img src="' . $custom_logo_id . '" alt="" class="img-responsive">');
					      } else {                      
					        _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
					      }
					     ?>
					</a></li>
				</ul>
			</div>
		</div>
		<div class="copy_right_part">
			<ul class="list-unstyled">
				<li><?php echo get_theme_mod('copyright_text') ?></li>
				<li><a href="<?php echo home_url().'/'. get_theme_mod('privacy_link'); ?>"><?php echo get_theme_mod('privacy_text') ?></a></li>
				<li><a href="<?php echo home_url().'/'. get_theme_mod('terms_link'); ?>"><?php echo get_theme_mod('terms_text') ?></a></li>
			</ul>
		</div>
	</div>
</div>
<script src="<?php echo get_template_directory_uri() ?>/js/jquery-3.4.1.min.js" type="text/javascript"></script>
<script src="<?php echo get_template_directory_uri() ?>/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo get_template_directory_uri() ?>/js/owl.carousel.js" ></script><!-- 
<script src="<?php echo get_template_directory_uri() ?>/js/email-confirm.js" ></script> -->
<script type="text/javascript">
$(document).ready(function() {
	$('.psychotherapy_slider').owlCarousel({
		loop: true,
		margin: 10,
		autoplay: false,
        autoplayTimeout: 2000,
        smartSpeed: 1000,
		dots: false,
		responsiveClass: true,
		responsive: {
		  0: {
			items: 2.5,
			margin: 20,
			nav: true
		  },
		  600: {
			items: 3,
			nav: false
		  },
		  1000: {
			items: 3.5,
			nav: false,
			margin: 40
		  }
		}
	})
})
</script> 
<script type="text/javascript">
$(document).ready(function() {
	$('.why_choose_slider').owlCarousel({
		loop: true,
		margin: 10,
		autoplay: false,
        autoplayTimeout: 2000,
        smartSpeed: 1000,
		dots: false,
		responsiveClass: true,
		responsive: {
		  0: {
			items: 1,
			nav: true
		  },
		  600: {
			items: 2,
			nav: true
		  },
		  769: {
			items: 3,
			nav: true,
			margin: 25
		  }
		}
	})
})
</script> 
<script>
	jQuery('.navbar-toggler').on('click', function () {
		jQuery(this).toggleClass('icon--active');
	   jQuery('body').toggleClass('menu-open');
	});
	$(function() {
	    $('#MainMenu > li.menu-item-has-children').click(function(e) { // limit click to children of mainmenue
	        var $el = $('ul',this); // element to toggle
	        $('#MainMenu > li.menu-item-has-children > ul').not($el).slideUp(); // slide up other elements
	        $el.stop(true, true).slideToggle(400); // toggle element
	        jQuery(this).toggleClass('current');
	        return false;
	    });
	    $('#MainMenu > li.menu-item-has-children > ul > li').click(function(e) {
	        e.stopPropagation();  // stop events from bubbling from sub menu clicks
	    });
	});
</script>
<?php wp_footer(); ?>
</body>
</html>
