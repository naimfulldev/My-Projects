<?php
/*
    
 */
 get_header();
 //pass same contend of news page on this page
    $page = get_page_by_path( 'blog' );
    $post = get_post( $id );
    
 ?>
 
<div class="blog_section_banner">
    <div class="blog_banner_img">
        <?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $page->ID ), 'single-post-thumbnail' ); ?>
        <!-- <img src="<?php //echo $image[0]; ?>" alt="logo" class="img-responsive"> -->
        <img src="<?php the_field( 'single_blog_background_image' ,$page); ?>" alt="logo" class="img-responsive">
    </div>
    <div class="blog_banner_info">
        <h2><?php the_field( 'single_blog_header_title' ,$page);?></h2>
    </div>
</div>
<div id="single_blog">
<div class="latest_articles_section">
    <div class="container_full">
        <div class="latest_articles_part bloge_articles_info">
            <div class="latest_articles_block">
                <div class="latest_articles_info">
                    <h3><?php the_title(); ?> </h3>
                    <h5><?php echo get_the_date(); ?></h5>
                </div>
                <div class="latest_articles_img">
                    <?php $image = wp_get_attachment_url( get_post_thumbnail_id($post->ID),'single-post-thumbnail');  ?>
                    <img src="<?php echo $image; ?>" alt="logo" class="img-responsive">
                </div>
                <div class="latest_articles_info text_left_info">
                    <?php echo get_the_content(); ?>
                </div>
            </div>      
        </div>
    </div>
</div>
</div>
<?php include 'contactus.php'; ?>
<?php
get_footer();
?>