<?php
/*
  Template Name: resources
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

<div class="helpful_links_section">
  <div class="container_full">
    <div class="helpful_links_part">
      <h2><?php the_field('resources_title'); ?></h2>
      <hr>
      <?php if( have_rows('resources_repeater') ): ?>
      <?php while( have_rows('resources_repeater') ): the_row(); 

          // Get sub field values.
          $title = get_sub_field('title');

      ?>
      <div class="national_part">
        <h3><?php echo $title; ?></h3>
        <?php if( have_rows('resources_description') ): ?>
        <?php while( have_rows('resources_description') ): the_row(); 

            // Get sub field values.
            $title = get_sub_field('title');
            $url = get_sub_field('url');

        ?>
        <p><?php echo $title; ?><a href="<?php echo $url; ?>" target="_blank"><span><?php echo  str_replace( array( "https://", "http://" ), '', $url ); ?></span></a></p>
        <?php endwhile; ?>
      <?php endif; ?>
      </div>
        <?php endwhile; ?>
      <?php endif; ?>
    
    </div>
  </div>
</div>
  
  
<?php include 'contactus.php'; ?>

<?php
get_footer();
?>