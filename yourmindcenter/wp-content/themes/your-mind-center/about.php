<?php
/*
  Template Name: about
 */
 get_header();

 if(strpos($actual_link, '/about/') !== true){
    echo "404 error";
 }

 ?>
<!---------------Body-start------------->
<?php echo do_shortcode('[contact-form-7 id="447" title="Custom"]'); ?>
<?php echo do_shortcode('[contact-form-7 id="448" title="Test"]'); ?>
<div class="blog_section_banner">
  <div class="blog_banner_img">
    <?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); ?>
    <img src="<?php echo $image[0]; ?>" alt="logo" class="img-responsive">
  </div>
  <div class="blog_banner_info">
    <h2><?php the_title(); ?></h2>
  </div>
</div>
<?php if( have_rows('personal_info_group') ): ?>
<?php while( have_rows('personal_info_group') ): the_row(); 
      $title = get_sub_field('title');
      $description = get_sub_field('description');
      $image = get_sub_field('image');
?>
<div class="a_about_section">
  <div class="container_full">
    <div class="a_about_part">
      <div class="a_about_img">
        <img src="<?php echo $image; ?>" alt="logo" class="img-responsive">
      </div>
      <div class="a_about_info">
        <h2><?php echo $title; ?></h2>
        <hr>
        <?php echo $description; ?> 
      </div>
    </div>
  </div>
</div>
<?php endwhile; ?>
<?php endif; ?>

<div class="qualifications_section">
  <div class="container_full">
    <?php if( have_rows('qualifications_group') ): ?>
    <?php while( have_rows('qualifications_group') ): the_row(); 
          $title = get_sub_field('title');
          
    ?>
    <div class="qualifications_part">
      <h2><?php echo $title; ?></h2>
      <hr>
    </div>
    <div class="qualifications_block">

      <?php if( have_rows('qualifications_repeater') ): ?>
      <?php while( have_rows('qualifications_repeater') ): the_row(); 
          $description = get_sub_field('detail');
          $image = get_sub_field('image');
      ?>
      <div class="qualifications_grid">
        <div class="qualifications_logo">
          <?php if($image!=""){ ?>
            <img src="<?php echo $image; ?>" alt="logo" class="img-responsive">
          <?php } ?>
        </div>
        <div class="qualifications_info">
          <p><?php echo $description; ?> </p>
        </div>
      </div>
      <?php endwhile; ?>
      <?php endif; ?>
      
    </div>
    <?php endwhile; ?>
    <?php endif; ?>
  </div>
</div>
<div class="personal_note_section">
  <div class="container_full">
    <?php if( have_rows('note_group') ): ?>
    <?php while( have_rows('note_group') ): the_row(); 
          $title = get_sub_field('title');
          $description = get_sub_field('description');
          $detail = get_sub_field('detail');
          $image = get_sub_field('image');
          $author_name = get_sub_field('author_name');
    ?>
    <div class="personal_note_part">
      <h2><?php echo $title; ?></h2>
      <hr>
      <?php echo $description; ?>
    </div>

    <div class="personal_info_part">
      <div class="personal_info_grid">
        <div class="personal_info_descr">
          <?php echo $detail; ?>
          <h4><?php echo $author_name; ?></h4>
        </div>
        <div class="personal_info_img">
          <img src="<?php echo $image; ?>" alt="logo" class="img-responsive">
        </div>
      </div>
    </div>
    <?php endwhile; ?>
    <?php endif; ?>
  </div>
</div>
<?php include 'contactus.php'; ?>

<?php
get_footer();
?>