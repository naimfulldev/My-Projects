<?php
/*
  Template Name: fees
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
    <div class="our_philosophy_part our_fees_part">
      <h2><?php the_field('title'); ?></h2>
      <hr>
    </div>
  </div>
  <div class="psychotherapy_fees">
    <div class="container_full">
      <div class="psychotherapy_fees_part">
        <?php if( have_rows('psychotherapy_group') ): ?>
        <?php while( have_rows('psychotherapy_group') ): the_row(); 
              $title = get_sub_field('title');
              $details = get_sub_field('details');
              
        ?>
        <div class="psychotherapy_fees_info">
          <h3><?php echo $title; ?></h3>
          <?php echo $details; ?>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>

        <?php if( have_rows('testing_group') ): ?>
        <?php while( have_rows('testing_group') ): the_row(); 
              $title = get_sub_field('title');
              $details = get_sub_field('details');
              
        ?>
        <div class="psychotherapy_fees_info">
          <h3><?php echo $title; ?></h3>
          <?php echo $details; ?>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>


        <?php if( have_rows('other_services_group') ): ?>
        <?php while( have_rows('other_services_group') ): the_row(); 
              $title = get_sub_field('title');
              $details = get_sub_field('details');
              
        ?>
        <div class="psychotherapy_fees_info">
          <h3><?php echo $title; ?></h3>
          <?php echo $details; ?>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>

        <p><?php the_field('information_text'); ?></p>
      </div>


      <div class="methods_of_payment">

        <?php if( have_rows('payment_group') ): ?>
        <?php while( have_rows('payment_group') ): the_row(); 
              $title = get_sub_field('title');
              $details = get_sub_field('description');
              
        ?>
        <div class="methods_of_payment_info">
          <h3><?php echo $title; ?></h3>
          <?php echo $details; ?>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>

        <?php if( have_rows('policy_group') ): ?>
        <?php while( have_rows('policy_group') ): the_row(); 
              $title = get_sub_field('title');
              $details = get_sub_field('description');
              
        ?>
        <div class="methods_of_payment_info">
          <h3>Cancellation Policy</h3>
          <p>The practice has a 24-hour cancellation policy. If you do not cancel within this timeframe, you will be responsible for the session fee. This is a standard psychotherapy practice.</p>
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