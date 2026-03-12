<?php
/*
  Template Name: blogs
 */
 get_header();
 ?>
 
<div class="blog_section_banner">
  <div class="blog_banner_img">
    <?php $image = wp_get_attachment_url( get_post_thumbnail_id($post->ID),'single-post-thumbnail');  ?>
    <!-- <img src="<?php echo $image; ?>" alt="logo" class="img-responsive"> -->
    <img src="<?php the_field('blog_background_image'); ?>" alt="logo" class="img-responsive">
  </div>
  <div class="blog_banner_info">
    <h2><?php the_field('header_title'); ?></h2>
  </div>
</div>

<?php 
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$data= new WP_Query(array(
    'post_type'=>'blogs', // your post type name
    'posts_per_page' => 9, // post per page
    'paged' => $paged,
    'order' => 'DESC',
    'post_status' => 'publish'
));
$lastposts = $data->posts;

?>
<div class="latest_articles_section latest_articles_blog">
  <div class="container_full">
    <div class="latest_articles_tital">
      <h2><?php the_field('title') ?></h2>
      <hr>
    </div>
    <div class="latest_articles_part latest_articles_blog_part">
      
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
    
  </div>
</div>
        <div class="pagination_part">
          <?php

              $total_pages = $data->max_num_pages;
              if ( $paged == 1 ){
                echo '<a href="#" class="link_disabled">Back</a>';
              }
              if ($total_pages > 1){

                  $current_page = max(1, get_query_var('paged'));

                  echo paginate_links(array(
                      'base' => get_pagenum_link(1) . '%_%',
                      'mid_size'  => 3,
                      'end_size'  => 2,
                      'format' => '/page/%#%',
                      'current' => $current_page,
                      'total' => $total_pages,
                      'prev_text' => __( 'Back', 'textdomain' ),
                      'next_text' => __( 'Next', 'textdomain' ),
                  ));
              }
              
              if ( $paged == $total_pages ){
                echo '<a href="#" class="link_disabled">Next</a>';
              }
              ?> 
        </div>
<?php include 'contactus.php'; ?>
<?php
get_footer();
?>