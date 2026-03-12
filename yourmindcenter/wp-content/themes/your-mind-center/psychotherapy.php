<?php
/*
  Template Name: pyschotherapy
 */
 get_header();
 ?>
<!---------------Body-start------------->
<div class="blog_section_banner">
  <div class="blog_banner_img">
    <?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); ?>
    <img src="<?php echo $image[0]; ?>" alt="logo" class="img-responsive">
  </div>
  <div class="blog_banner_info">
    <h2><?php the_title(); ?></h2>
  </div>
</div>
<div class="our_philosophy_section">
  <div class="container_full">
    <div class="our_philosophy_part">
      <h2><?php the_field('philosophy_title'); ?></h2>
      <hr>
      <p><?php the_field('philosophy_description'); ?></p>
    </div>
    <?php if( have_rows('treatment_repeater') ): 
      $row = 1;?>
    <?php while( have_rows('treatment_repeater') ): the_row(); 

        // Get sub field values.
        $title = get_sub_field('treatment_title');
        $description = get_sub_field('treatment_description');
        //$items = get_sub_field('treatment_items');
        $image = get_sub_field('treatment_image');

    ?>
    <?php
        if($row%2 == 1) {
    ?>
    <div class="our_philosophy_block">
      <div class="our_philosophy_img">
        <img src="<?php echo $image; ?>" alt="logo" class="img-responsive">
      </div>
      <div class="our_philosophy_info">
        <h3><?php echo $title; ?></h3>
        <p><?php echo $description; ?></p>
        <ul class="anxiety_part list-unstyled">
          <?php if( have_rows('treatment_items') ): ?>
          <?php while( have_rows('treatment_items') ): the_row(); 

              // Get sub field values.
              $title = get_sub_field('title');
              
          ?>
          <li><?php echo $title; ?></li>

          <?php endwhile; ?>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    <?php
    }
    else
    {
        ?>
    <div class="our_philosophy_block our_philosophy_block1">
      <div class="our_philosophy_img">
        <img src="<?php echo $image; ?>" alt="logo" class="img-responsive">
      </div>
      <div class="our_philosophy_info">
        <h3><?php echo $title; ?></h3>
        <p><?php echo $description; ?></p>
        <ul class="anxiety_part list-unstyled">
          <?php if( have_rows('treatment_items') ): ?>
          <?php while( have_rows('treatment_items') ): the_row(); 

              // Get sub field values.
              $title = get_sub_field('title');
              
          ?>
          <li><?php echo $title; ?></li>

          <?php endwhile; ?>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    <?php
        }
        ++$row;
    ?>
    <?php endwhile; ?>
  <?php endif; ?>
  </div>
</div>
  
<?php include 'contactus.php'; ?>

<?php
get_footer();
?>