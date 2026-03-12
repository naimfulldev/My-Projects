<?php
/*
  Template Name: front-page
 */
 get_header();
 ?>
<!---------------Body-start------------->
<?php if( have_rows('main_banner') ): ?>
  <?php while( have_rows('main_banner') ): the_row(); 

      // Get sub field values.
      $title = get_sub_field('title');
      $description = get_sub_field('description');
      $link = get_sub_field('button');
      $image1 = get_sub_field('image');
      $image2 = get_sub_field('mobile_banner_image');

  ?>
<div class="main-banner">
  <div class="banner_img">
    <img src="<?php echo $image1; ?>" alt="logo" class="desk_banner img-responsive">
    <img src="<?php echo $image2; ?>" alt="logo" class="mob_banner img-responsive">
  </div>
  <div class="banner-info">
    <div class="banner-description">
      <h1><?php echo $title; ?></h1>
      <h2><?php echo $description; ?></h2>
      <a href="<?php echo $link['url']; ?>" class="book_consultation"><?php echo $link['title']; ?></a>
    </div>
  </div>
</div>
<?php endwhile; ?>
    <?php endif; ?>

<div class="psychotherapy_section">
  <div class="psychotherapy_part">
    <?php if( have_rows('main_banner_repeater') ): ?>
  <?php while( have_rows('main_banner_repeater') ): the_row(); 

      // Get sub field values.
      $title = get_sub_field('title');
      $image = get_sub_field('image');

  ?>
    <div class="psychotherapy_block">
      <h4><img src="<?php echo $image; ?>" alt="logo" class="img-responsive"><?php echo $title; ?></h4>
    </div>
    
    <?php endwhile; ?>
    <?php endif; ?>
  </div>
  <div class="psychotherapy_part_slider">
    <div class="psychotherapy_slider owl-carousel">
      <?php if( have_rows('main_banner_repeater') ): ?>
  <?php while( have_rows('main_banner_repeater') ): the_row(); 

      // Get sub field values.
      $title = get_sub_field('title');
      $image = get_sub_field('image');

  ?>
    <div class="psychotherapy_block">
      <h4><img src="<?php echo $image; ?>" alt="logo" class="img-responsive"><?php echo $title; ?></h4>
    </div>
    
    <?php endwhile; ?>
    <?php endif; ?>
    </div>
  </div>
</div>

<?php if( have_rows('about_section') ): ?>
  <?php while( have_rows('about_section') ): the_row(); 

      // Get sub field values.
      $title = get_sub_field('title');
      $image = get_sub_field('image');
      $description = get_sub_field('description');
      $link = get_sub_field('button');

  ?>
<div class="about_practice_section">
  <div class="about_practice_part">
    <div class="about_practice_img">
      <img src="<?php echo $image; ?>" alt="logo" class="img-responsive">
    </div>
    <div class="about_practice_description">
      <div class="about_practice_info">
        <h2><?php echo $title; ?></h2>
        <hr>
        <p><?php echo $description; ?></p>
        <a href="<?php echo $link['url']; ?>" class="learn_more_btn"><?php echo $link['title']; ?></a>
      </div>
    </div>
  </div>
</div>
<?php endwhile; ?>
    <?php endif; ?>

<?php if( have_rows('our_services_section') ): ?>
  <?php while( have_rows('our_services_section') ): the_row(); 

      // Get sub field values.
      $title = get_sub_field('title');
      $description = get_sub_field('description');

  ?>
<div class="our_services_section">
  <div class="container_full">
    <div class="our_services_part">
      <h2><?php echo $title; ?></h2>
      <hr>
      <p><?php echo $description; ?></p>
    </div>
    <div class="our_services_block">
      <?php if( have_rows('our_services_repeater') ): ?>
      <?php while( have_rows('our_services_repeater') ): the_row(); 

          // Get sub field values.
          $title = get_sub_field('title');
          $description = get_sub_field('description');
          $link = get_sub_field('button');
          $image = get_sub_field('image');

      ?>
      <div class="our_services_grid">
        <div class="our_services_img">
          <img src="<?php echo $image; ?>" alt="logo" class="img-responsive">
        </div>
        <div class="our_services_info1">
          <div class="our_services_info">
            <h3><?php echo $title; ?></h3>
            <hr>
            <p><?php echo $description; ?></p>
            <a href="<?php echo $link['url']; ?>" class="learn_more_btn"><?php echo $link['title']; ?></a>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    <?php endif; ?>
      
    </div>
  </div>
</div>
<?php endwhile; ?>
    <?php endif; ?>

<?php if( have_rows('why_choose_us_section') ): ?>
  <?php while( have_rows('why_choose_us_section') ): the_row(); 

      // Get sub field values.
      $title = get_sub_field('title');
      $link = get_sub_field('button');
      $image = get_sub_field('image');
      $mob_image = get_sub_field('mobile_background_image');

  ?>
<div class="why_choose_us_section">
  <div class="why_choose_us_bg">
    <div class="why_choose_us_bg_img">
      <img src="<?php echo $image; ?>" alt="logo" class="choose_desk_bg img-responsive">
      <img src="<?php echo $mob_image; ?>" alt="logo" class="choose_mob_bg img-responsive">
    </div>  
    <div class="why_choose_us_bg_info">
      <div class="container_full">
        <div class="why_choose_us_part">
          <h2><?php echo $title; ?></h2>
          <hr>
        </div>
        <div class="why_choose_us_block">
          <?php if( have_rows('why_choose_us_repeater') ): ?>
            <?php while( have_rows('why_choose_us_repeater') ): the_row(); 

                // Get sub field values.
                $title = get_sub_field('title');
                $description = get_sub_field('description');

            ?>
          <div class="why_choose_us_grid">
            <h4><?php echo $title; ?></h4>
            <hr>
            <p><?php echo $description; ?></p>
          </div>
          <?php endwhile; ?>
          <?php endif; ?>
          
        </div>
        <div class="why_choose_us_block_slider">
          <div class="why_choose_slider owl-carousel">
            <?php if( have_rows('why_choose_us_repeater') ): ?>
            <?php while( have_rows('why_choose_us_repeater') ): the_row(); 

                // Get sub field values.
                $title = get_sub_field('title');
                $description = get_sub_field('description');

            ?>
          <div class="why_choose_us_grid">
            <h4><?php echo $title; ?></h4>
            <hr>
            <p><?php echo $description; ?></p>
          </div>
          <?php endwhile; ?>
          <?php endif; ?>
            
          </div>
        </div>
        <a href="<?php echo $link['url']; ?>" class="learn_more_btn"><?php echo $link['title']; ?></a>
      </div>
    </div>
  </div>
</div>
<?php endwhile; ?>
    <?php endif; ?>


<div class="latest_articles_section">
  <div class="container_full">
    <?php if( have_rows('latest_articles_section') ): ?>
    <?php while( have_rows('latest_articles_section') ): the_row(); 

        // Get sub field values.
        $title = get_sub_field('title');
        
    ?>
    <div class="latest_articles_tital">
      <h2><?php echo $title; ?></h2>
      <hr>
    </div>
    <?php endwhile; ?>
    <?php endif; ?>
    <div class="latest_articles_part">
      <?php
        $lastposts = get_posts( array(

            'posts_per_page'   =>  3,
            'order'            => 'DESC',
            'post_type'        => 'blogs',
            'post_status'      => 'publish'
        ) );
      ?>
       <?php   if ( $lastposts ) {
          foreach ( $lastposts as $post ) :
              setup_postdata( $post ); 
        ?>
      <div class="latest_articles_block">
        <div class="latest_articles_img">
          <?php $image = wp_get_attachment_url( get_post_thumbnail_id($post->ID),'single-post-thumbnail');  ?>
          <a href="<?php the_permalink($post->ID); ?>"><img src="<?php echo $image; ?>" alt="logo" class="img-responsive"></a>
        </div>
        <div class="latest_articles_info">
          <a href="<?php the_permalink($post->ID); ?>"><h3><?php the_title(); ?> </h3></a>
          <p><?php echo wp_trim_words( get_the_content(), 24, '...' ); ?> 
          <!-- <a href="<?php the_permalink($post->ID); ?>">Read More</a> --></p>
        </div>
      </div>
      <?php
        endforeach; 
        wp_reset_postdata();
        }
      ?>
    </div>
    <div class="latest_articles_part_slider">
      <div class="why_choose_slider owl-carousel">

        <?php
        $lastposts = get_posts( array(

            'posts_per_page'   =>  3,
            'order'            => 'DESC',
            'post_type'        => 'blogs',
            'post_status'      => 'publish'
        ) );
      ?>
       <?php   if ( $lastposts ) {
          foreach ( $lastposts as $post ) :
              setup_postdata( $post ); 
        ?>
      <div class="latest_articles_block">
        <div class="latest_articles_img">
          <?php $image = wp_get_attachment_url( get_post_thumbnail_id($post->ID),'single-post-thumbnail');  ?>
          <a href="<?php the_permalink($post->ID); ?>"><img src="<?php echo $image; ?>" alt="logo" class="img-responsive"></a>
        </div>
        <div class="latest_articles_info">
          <a href="<?php the_permalink($post->ID); ?>"><h3><?php the_title(); ?> </h3></a>
          <p><?php echo wp_trim_words( get_the_content(), 25, '...' ); ?>
          <!-- <a href="<?php the_permalink($post->ID); ?>">Read More</a> --></p>
          
        </div>
      </div>
      <?php
        endforeach; 
        wp_reset_postdata();
        }
      ?>
        
      </div>
    </div>
  </div>
</div>


<?php include 'contactus.php'; ?>

<?php
get_footer();
?>