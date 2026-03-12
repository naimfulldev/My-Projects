<?php
/*
  Template Name: neurospychological
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
      <h2><?php the_field('title'); ?></h2>
      <hr>
      <?php the_field('description'); ?>
    </div>

    <?php if( have_rows('evaluations_repeater') ): 
      $row = 1;?>
    <?php while( have_rows('evaluations_repeater') ): the_row(); 

        // Get sub field values.
        $title = get_sub_field('title');
        $description = get_sub_field('description');
        $image = get_sub_field('image');

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
          <?php if( have_rows('areas_repeater') ): ?>
          <?php while( have_rows('areas_repeater') ): the_row(); 

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
      
        <ul class="anxiety_part list-unstyled">
          <?php if( have_rows('areas_repeater') ): ?>
          <?php while( have_rows('areas_repeater') ): the_row(); 

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


    <?php if( have_rows('other_focus_repeater') ): ?>
    <?php while( have_rows('other_focus_repeater') ): the_row(); 

        // Get sub field values.
        $title = get_sub_field('title');
        $description = get_sub_field('description');

    ?>
    <div class="neurological_medical_section">
      <h2><?php echo $title; ?></h2>
      <p><?php echo $description; ?></p>
    </div>
  </div>
  <?php endwhile; ?>
  <?php endif; ?>
</div>

<?php if( have_rows('additional_areas_group') ): ?>
<?php while( have_rows('additional_areas_group') ): the_row(); 

    // Get sub field values.
    $title = get_sub_field('title');
    
?>
<div class="additional-areas_section">
  <div class="container_full">
    <div class="additional-areas_part">
      <h2><?php echo $title; ?></h2>
      <hr>
      <div class="developmental_block_part">
        <?php if( have_rows('additional_areas_repeater') ): ?>
          <?php while( have_rows('additional_areas_repeater') ): the_row(); 

          // Get sub field values.
          $title = get_sub_field('title');
          $image = get_sub_field('image');
          $details = get_sub_field('details');

          ?>
        <div class="developmental_block">
          <div class="developmental_img">
            <img src="<?php echo $image; ?>" alt="logo" class="img-responsive">
          </div>
          <div class="developmental_info">
            <h3><?php echo $title; ?></h3>
            <p><?php echo $details; ?></p>
          </div>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endwhile; ?>
<?php endif; ?>

<div class="type_evaluation_section">
  <div class="container_full">
    <div class="type_evaluation_part">
      <?php if( have_rows('type_evaluation_group') ): ?>
          <?php while( have_rows('type_evaluation_group') ): the_row(); 

          // Get sub field values.
          $title = get_sub_field('title');
          $description = get_sub_field('description');

        ?>
      <div class="type_evaluation_info">
        <h3><?php echo $title; ?></h3>
        <p><?php echo $description; ?></p>
      </div>
      <?php endwhile; ?>
      <?php endif; ?>

      <?php if( have_rows('steps_evaluation_group') ): ?>
          <?php while( have_rows('steps_evaluation_group') ): the_row(); 

          // Get sub field values.
          $title = get_sub_field('title');
          $description = get_sub_field('description');
          $steps_description = get_sub_field('steps_description');

        ?>
      <div class="type_evaluation_info type_evaluation_info1">
        <h3><?php echo $title; ?></h3>
        <?php echo $description; ?>

        <div class="clinical_interview_part">
          <?php echo $steps_description; ?>
        </div>
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