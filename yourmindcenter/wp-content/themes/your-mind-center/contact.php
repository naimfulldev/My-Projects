<?php
/*
  Template Name: contact
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
    $image = get_field('image');
    $address = get_field('address');
    $mobile = get_field('mobile');
    $email = get_field('email');
?>
<div class="our_philosophy_section contact_section">
  <div class="container_full">
    <div class="our_philosophy_part">
      <h2><?php echo $title ?></h2>
      <hr>
      <p><?php echo $description ?></p>
    </div>
  </div>
</div>

<div class="contact_block">
  <div class="container_full">
    <div class="contact_part">
      <div class="contact_part_left">
        <div class="contact-form">
          <?php echo do_shortcode('[contact-form-7 id="387" title="Contact form 1"]'); ?>
         
        </div>
      </div>
      <div class="contact_part_right">
        <!-- <img src="<?php echo $image ?>" alt="logo" class="img-responsive"> -->

        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3593.674919642881!2d-80.25861008454412!3d25.748261815440017!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x88d9b79797bf2171%3A0x684743ff36cfb151!2s2519%20Galiano%20St%20%23712%2C%20Coral%20Gables%2C%20FL%2033134%2C%20USA!5e0!3m2!1sen!2sin!4v1629258611423!5m2!1sen!2sin" loading="lazy"></iframe>
        
        <div class="contact_address">
          <p><?php echo $address ?></p>
          <p><a href="tel:<?php echo $mobile ?>"><?php echo $mobile ?> </a> &nbsp | &nbsp <a href="mailto:<?php echo $email ?>"> <?php echo $email ?></a></p>
        </div>
      </div>
    </div>
    <div class="emergency_secttion">
      <div class="emergency_part"> 
        <?php if( have_rows('emergency_service_block') ): ?>
        <?php while( have_rows('emergency_service_block') ): the_row(); 
              $title = get_sub_field('title');
              $description = get_sub_field('description');
        ?>
        <div class="emergency_block">
          <p><span><?php echo $title ?></span> <?php echo $description ?></p>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>

        <?php if( have_rows('national_block') ): ?>
        <?php while( have_rows('national_block') ): the_row(); 
              $title = get_sub_field('title');
              $description = get_sub_field('detail');
        ?>
        <div class="emergency_national_block">
          <?php echo $description ?>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include 'contactus.php'; ?>

<?php
get_footer();
?>