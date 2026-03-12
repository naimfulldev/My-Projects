<div class="footer_section">
  <div class="container_full">
    <div class="footer_part">
      <div class="footer_block">
        <div class="footer_logo">
          <a href="<?php echo home_url(); ?>">
            <?php
                $custom_logo_id = get_theme_mod('footer_logo');
                if ($custom_logo_id) {                      
                  _e('<img src="' . $custom_logo_id . '" alt="logo" class="img-responsive">');
                } else {                      
                  _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
                }
            ?>
            <!-- <img src="<?php echo get_template_directory_uri() ?>/image/afd_logo.png" alt="logo" class="img-responsive"> -->
          </a>
        </div>
        <div class="footer_contact">
          <a href="tel:<?php echo get_theme_mod('phone_text'); ?>"><?php echo get_theme_mod('phone_text'); ?></a>
          <a href="mailto:<?php echo get_theme_mod('email_text'); ?>"><?php echo get_theme_mod('email_text'); ?></a>
        </div>
      </div>
      <div class="footer_block">
        <?php
          $menu_location = 'footer_products';
          $menu_locations = get_nav_menu_locations();

          $menu_object = (isset($menu_locations[$menu_location]) ? wp_get_nav_menu_object($menu_locations[$menu_location]) : null);

          $menu_name = (isset($menu_object->name) ? $menu_object->name : '');
        ?>
        <h4>products</h4>
        <ul class="list-unstyled">
          <?php
            wp_nav_menu( array( 
              'theme_location' => 'footer_products', 
              'items_wrap' => '%3$s',
              'container' => ''
            ) ); 
          ?>
        </ul>
      </div>
      <div class="footer_block">
        <?php
          $menu_location = 'footer_help';
          $menu_locations = get_nav_menu_locations();

          $menu_object = (isset($menu_locations[$menu_location]) ? wp_get_nav_menu_object($menu_locations[$menu_location]) : null);

          $menu_name = (isset($menu_object->name) ? $menu_object->name : '');
        ?>
        <h4><?php echo $menu_name; ?></h4>
        <ul class="list-unstyled">
          <?php
            wp_nav_menu( array( 
              'theme_location' => 'footer_help', 
              'items_wrap' => '%3$s',
              'container' => ''
            ) ); 
          ?>
        </ul>
      </div>
      <div class="footer_block">
        <?php
          $menu_location = 'footer_company';
          $menu_locations = get_nav_menu_locations();

          $menu_object = (isset($menu_locations[$menu_location]) ? wp_get_nav_menu_object($menu_locations[$menu_location]) : null);

          $menu_name = (isset($menu_object->name) ? $menu_object->name : '');
        ?>
        <h4><?php echo $menu_name; ?></h4>
        <ul class="list-unstyled">
          <?php
            wp_nav_menu( array( 
              'theme_location' => 'footer_company', 
              'items_wrap' => '%3$s',
              'container' => ''
            ) ); 
          ?>
        </ul>
      </div>
      <div class="footer_block">
        <h4>newsletter</h4>
        <p>Join today for exclusive promos & product launches.</p>
        <div class="emailpart-block">
              <input type="email" name="EMAIL" class="sr-input" placeholder="Email Address" required="">
              <input type="submit" value="SIGN UP" class="btn sr-btn">
          </div>
      </div>
    </div>
  </div>
  <hr>
  <div class="container_full">
    <div class="footer_bottom">
      <div class="footer_bottom_left">
        <ul class="list-unstyled">
          <li><a style="pointer-events: none;"><?php echo get_theme_mod('copyright_text'); ?></a></li>
          <li><a href="<?php echo get_theme_mod('privacy_link'); ?>"><?php echo get_theme_mod('privacy_text'); ?></a></li>
          <li><a href="<?php echo get_theme_mod('terms_link'); ?>"><?php echo get_theme_mod('terms_text'); ?></a></li>
        </ul>
      </div>
      <div class="footer_bottom_right">
        <ul class="list-unstyled">
          <li>
            <a href="<?php echo get_theme_mod('insta_link'); ?>" target="_blank">
              <?php
                $custom_logo_id = get_theme_mod('insta_icon');
                if ($custom_logo_id) {                      
                  _e('<img src="' . $custom_logo_id . '" alt="logo" class="img-responsive">');
                } else {                      
                  _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
                }
              ?>
              <!-- <img src="<?php echo get_template_directory_uri() ?>/image/instagram.png" alt="logo" class="img-responsive"> -->
            </a>
          </li>
          <li>
            <a href="<?php echo get_theme_mod('facebook_link'); ?>" target="_blank">
              <?php
                $custom_logo_id = get_theme_mod('facebook_icon');
                if ($custom_logo_id) {                      
                  _e('<img src="' . $custom_logo_id . '" alt="logo" class="img-responsive">');
                } else {                      
                  _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
                }
              ?>
              <!-- <img src="<?php echo get_template_directory_uri() ?>/image/facebook.png" alt="logo" class="img-responsive"> -->
            </a>
          </li>
          <li>
            <a href="<?php echo get_theme_mod('linkedin_link'); ?>" target="_blank">
              <?php
                $custom_logo_id = get_theme_mod('linkedin_icon');
                if ($custom_logo_id) {                      
                  _e('<img src="' . $custom_logo_id . '" alt="logo" class="img-responsive">');
                } else {                      
                  _e('<span class="site-name">' . esc_attr(get_bloginfo('name')) . '</span>'); //class="site-name"not included in css
                }
              ?>
              <!-- <img src="<?php echo get_template_directory_uri() ?>/image/linkedin.png" alt="logo" class="img-responsive"> -->
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
<script src="<?php echo get_template_directory_uri() ?>/js/jquery-3.4.1.min.js" type="text/javascript"></script>
<script src="<?php echo get_template_directory_uri() ?>/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo get_template_directory_uri() ?>/js/owl.carousel.js" ></script>

<script type="text/javascript">
/*$(document).ready(function() {
    $("form.ElementsApp .CardNumberField-input-wrapper input.InputElement").addClass('hide');
    $("form.ElementsApp .CardNumberField-input-wrapper input.InputElement").css("height", "38px !important");
})*/
</script>
<script type="text/javascript">
$(document).ready(function(){
    if(document.getElementById("has_product_featured_img")){
        if(document.getElementById("has_product_featured_img").value == '')
        {
          $( ".single #main .p_product_aside" ).html('<img src="<?php echo get_template_directory_uri() ?>/image/no-image.png" alt="no-img" id="no-img">');
          /*alert("hello");*/
        }
    }
    /*$( "ul.variable-items-wrapper.button-variable-wrapper.reselect-clear li.variable-item" ).click(function() {
        //$("#after-price-variation").html('');
        $("#after-price-variation").html($(".product-type-variable .woocommerce-variation-price").html());
    })*/
  
})
</script>
<script type="text/javascript">
$(document).ready(function() {
  $('.product_featured_slider').owlCarousel({
    loop: true,
    margin: 30,
    autoplay: false,
    smartSpeed: 1000,
        autoplayTimeout: 2000,
    dots: false,
    responsiveClass: true,
    responsive: {
      0: {
      items: 1.5,
      nav: false
      },
      600: {
      items: 2,
      nav: false
      },
      1000: {
      items: 2,
      nav: false,
      margin: 40
      }
    }
  })
})
</script> 
<script>
      jQuery('.navbar-toggler').on('click', function () {
        jQuery('body').toggleClass('menu-open');
    });
</script>
<script>
  $('.accordion.active').siblings('.panel').slideDown('slow');
  var acc = document.getElementsByClassName("accordion");
  var i;
  for (i = 0; i < acc.length; i++) {
  acc[i].addEventListener("click", function() {
  if($(this).hasClass('active')){
  $(this).removeClass('active');
  $(this).siblings('.panel').slideUp('slow');
  }
  else{
  $('.accordion').removeClass('active');
  $(this).addClass('active');
  $('.accordion').siblings('.panel').slideUp('slow');
  $(this).siblings('.panel').slideDown('slow');
  }
  });
  }
</script>
<script>
$(document).ready(function() {
    var options = $('select#pa_size option').not(':first');
    if(options)
    {
        var val=options[0].value; 
        if(val.match(/^\d/))
        {
            console.log(val);
            var arr = options.map(function(_, o) {
                return {
                    t: $(o).text(),
                    v: o.value
                };
            }).get();
            arr.sort(function(o1, o2) {
                return o1.t - o2.t;

            });
            options.each(function(i, o) {
                //console.log(i);
                o.value = arr[i].v;
                $(o).text(arr[i].t);
            });
        }
        else
        {
            var arr = options.map(function(_, o) {
                return {
                    t: $(o).text(),
                    v: o.value
                };
            }).get();
            arr.sort(function(o1, o2) {
                return o1.t > o2.t ? 1 : o1.t < o2.t ? -1 : 0;
            });
            options.each(function(i, o) {
                //console.log(i);
                o.value = arr[i].v;
                $(o).text(arr[i].t);
            });
        }
    }
    
    $('.single-product .p_product_intro .variations_form select#pa_size').parent().addClass("drop-size");
    $('<p class="size-guide">Size Guide</p>').insertAfter(".single-product .p_product_intro .variations_form select#pa_size");
})
</script>
<script type="text/javascript">
  $(document).ready(function(){ 
    $('form.searchwp-live-search-widget-search-form').find(".search-field").each(function(ev)
    {
        if(!$(this).val()) { 
       $(this).attr("placeholder", "Search");
    }
    });
    if (navigator.userAgent.indexOf('Mac') >= 0) {
      $('body').addClass('mac_os')
    }
  });
</script>

<?php wp_footer(); ?>
</body>
</html>