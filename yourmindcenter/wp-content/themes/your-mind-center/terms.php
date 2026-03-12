<?php
/*
  Template Name: terms
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

<?php
    $title = get_field('title');
    $description = get_field('description');
    
?>
<div class="our_philosophy_section contact_section">
  <div class="container_full">
    <div class="our_philosophy_part">
      <h2><?php echo $title ?></h2>
      <hr>
      <?php echo $description ?>
    </div>
  </div>
</div>


<?php include 'contactus.php'; ?>

<?php
get_footer();
?>