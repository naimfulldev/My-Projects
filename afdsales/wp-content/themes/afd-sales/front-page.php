<?php
/*
  Template Name: front-page
 */
 get_header();
 ?>
<!---------------Body-start------------->
<!---------------Body-start------------->
<?php if( have_rows('main_banner') ): ?>
    <?php while( have_rows('main_banner') ): the_row(); 
        $title = get_sub_field('title');
        $description = get_sub_field('description');
        $button = get_sub_field('button');
        $image = get_sub_field('image');
        $image2 = get_sub_field('image2');
    ?>
<div class="main-banner">
    <div class="container_full">
        <div class="main-banner_part">
            <div class="banner-info">
                <h1><?php echo $title; ?></h1>
                <p><?php echo $description; ?></p>
                <a href="<?php echo $button['url']; ?>" class="shop_now"><?php echo $button['title']; ?></a>
            </div>
            <div class="banner-img_part">
                <div class="banner_img">
                    <img src="<?php echo $image; ?>" class="img-responsive">
                </div>
                <div class="banner_img">
                    <img src="<?php echo $image2; ?>" alt="logo" class="img-responsive">
                </div>
            </div>
        </div>
    </div>
</div>
    <?php endwhile; ?>
<?php endif; ?>
<div class="featured_section">
    <div class="container_full">
        <div class="featured_part">
            <div class="featured_intro">
                <div class="featured_title">
                    <h5>featured</h5>
                    <h2>Men's Clothing</h2>
                </div>
                <div class="view_all">
                    <a href="<?php echo home_url(); ?>/product-category/clothing/mens-clothing/">View all</a>
                </div>
            </div>
            <?php
                    
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => '4',
                    'order'      => 'desc',
                    'post_status' => 'publish',
                    'tax_query' => array(
                                    array(
                                        'taxonomy' => 'product_cat',
                                        'field'    => 'slug',
                                        'terms'    => 'mens-clothing',
                                        'operator' => 'IN',
                                        ),
                                        array(
                                            'taxonomy' => 'product_visibility',
                                            'field'    => 'name',
                                            'terms'    => 'featured',
                                            'operator' => 'IN',
                                        ),
                                    ),

                );
                $loop = new WP_Query($args);
            ?> 
            
            <div class="product_grid">
                <?php while ( $loop->have_posts() ) : $loop->the_post(); global $product; ?>
                <?php $current_tags = get_the_terms( get_the_ID(), 'product_tag' ); ?>
                
                <div class="product_block">
                    <div class="product_img">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="banner_img" class="img-responsive"/>'; ?>
                            <!-- <img src="<?php echo get_template_directory_uri() ?>/image/product_img1.jpg" alt="logo" class="img-responsive"> -->
                        </a>
                    </div>
                    <div class="product_info">
                        <div class="product_info_title">
                            <a href="<?php the_permalink(); ?>"><h3><?php the_title(); ?></h3></a>
                            <span><?php echo $product->get_price_html(); ?></span>
                        </div>
                        <?php 
                            $available_variations=$product->get_attribute( 'color' ); // get all attributes by variations
                            $array = explode(',', $available_variations);
                            //print_r($available_variations);
                            $count = count($array);
                            if($count > 1){
                        ?>
                        <div class="product_info_colors">
                            <p><?php echo count($array); ?> MORE COLORS</p>
                        </div>
                        <?php }else if($count == 1){ if(!empty($array[0])){ ?>
                		<div class="product_info_colors">
                			<p><?php echo strtoupper($array[0]); ?></p>
                		</div>
                		<?php } } ?>
                    </div>
                    <?php if($current_tags[0]->slug == 'best-seller'){ ?>
                        <div class="best_seller">
                            <h4>BEST SELLER</h4>
                        </div>
                    <?php } ?>
                </div>
                <?php endwhile; ?>
                <?php wp_reset_query(); ?>

            </div>
            <?php
                    
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => '4',
                    'order'      => 'desc',
                    'post_status' => 'publish',
                    'tax_query' => array(
                                    array(
                                        'taxonomy' => 'product_cat',
                                        'field'    => 'slug',
                                        'terms'    => 'mens-clothing',
                                        'operator' => 'IN',
                                        ),
                                        array(
                                            'taxonomy' => 'product_visibility',
                                            'field'    => 'name',
                                            'terms'    => 'featured',
                                            'operator' => 'IN',
                                        ),
                                    ),

                );
                $loop = new WP_Query($args);
            ?> 
            <div class="product_grid_mob">
                <div class="product_featured_slider owl-carousel owl-loaded">
                    <?php while ( $loop->have_posts() ) : $loop->the_post(); global $product; ?>
                    <?php $current_tags = get_the_terms( get_the_ID(), 'product_tag' ); ?>
                    <div class="product_block">
                        <div class="product_img">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="banner_img" class="img-responsive"/>'; ?>
                                <!-- <img src="<?php echo get_template_directory_uri() ?>/image/product_img1.jpg" alt="logo" class="img-responsive"> -->
                            </a>
                        </div>
                        <div class="product_info">
                            <div class="product_info_title">
                                <a href="<?php the_permalink(); ?>"><h3><?php the_title(); ?></h3></a>
                                <span><?php echo $product->get_price_html(); ?></span>
                            </div>
                            <?php 
                                $available_variations=$product->get_attribute( 'color' ); // get all attributes by variations
                                $array = explode(',', $available_variations);
                                //print_r($available_variations);
                                $count = count($array);
                                if($count > 1){
                            ?>
                            <div class="product_info_colors">
                                <p><?php echo count($array); ?> MORE COLORS</p>
                            </div>
                            <?php }else if($count == 1){ if(!empty($array[0])){ ?>
                    		<div class="product_info_colors">
                    			<p><?php echo strtoupper($array[0]); ?></p>
                    		</div>
                    		<?php } } ?>
                        </div>
                        <?php if($current_tags[0]->slug == 'best-seller'){ ?>
                            <div class="best_seller">
                                <h4>BEST SELLER</h4>
                            </div>
                        <?php } ?>
                    </div>
                    <?php endwhile; ?>
                    <?php wp_reset_query(); ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<div class="shop_footwear_section">
    <div class="container_full">
        <div class="shop_footwear_grid">
            <?php if( have_rows('categories_section') ): ?>
                <?php while( have_rows('categories_section') ): the_row(); 
                    $title = get_sub_field('title');
                    $button = get_sub_field('button');
                    $image = get_sub_field('image');
                ?>
            <div class="shop_footwear_block">
                <div class="shop_footwear_img">
                    <a href="<?php echo $button['url']; ?>"><img src="<?php echo $image; ?>" alt="logo" class="img-responsive"></a>
                </div>
                <div class="shop_footwear_info">
                    <a href="<?php echo $button['url']; ?>"><h3><?php echo $title; ?></h3></a>
                    <a href="<?php echo $button['url']; ?>" class="shop_now"><?php echo $button['title']; ?></a>
                </div>
            </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php if( have_rows('middle_banner') ): ?>
    <?php while( have_rows('middle_banner') ): the_row(); 
        $title = get_sub_field('title');
        $description = get_sub_field('description');
        $button = get_sub_field('button');
        $image = get_sub_field('image');
        $mobile_image = get_sub_field('mobile_image');
    ?>
<div class="shop_ppe_products">
    <div class="container_full">
        <div class="shop_ppe_products_part">
            <div class="shop_ppe_products_img">
                <img src="<?php echo $image; ?>" alt="logo" class="desktop_bg img-responsive">
                <img src="<?php echo $mobile_image; ?>" alt="logo" class="mob_bg img-responsive">
            </div>
            <div class="shop_ppe_products_info">
                <div class="shop_ppe_products_block">
                    <h2><?php echo $title; ?></h2>
                    <p><?php echo $description; ?></p>
                    <a href="<?php echo $button['url']; ?>" class="shop_now"><?php echo $button['title']; ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php endwhile; ?>
<?php endif; ?>


<div class="featured_section childrens_clothing">
    <div class="container_full">
        <div class="featured_part">
            <div class="featured_intro">
                <div class="featured_title">
                    <h5>new</h5>
                    <h2>childrens clothing</h2>
                </div>
                <div class="view_all">
                    <a href="<?php echo home_url(); ?>/product-category/clothing/childrens-clothing/">View all</a>
                </div>
            </div>
            <?php
                    
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => '4',
                    'order'      => 'desc',
                    'post_status' => 'publish',
                    'tax_query' => array(
                                    array(
                                        'taxonomy' => 'product_cat',
                                        'field'    => 'slug',
                                        'terms'    => 'childrens-clothing'
                                        )
                                    ),

                );
                $loop = new WP_Query($args);
            ?>
            <div class="product_grid">
                <?php while ( $loop->have_posts() ) : $loop->the_post(); global $product; ?>
                <?php $current_tags = get_the_terms( get_the_ID(), 'product_tag' ); ?>
                <div class="product_block">
                    <div class="product_img">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="banner_img" class="img-responsive"/>'; ?>
                            <!-- <img src="<?php echo get_template_directory_uri() ?>/image/product_img1.jpg" alt="logo" class="img-responsive"> -->
                        </a>
                    </div>
                    <div class="product_info">
                        <div class="product_info_title">
                            <a href="<?php the_permalink(); ?>"><h3><?php the_title(); ?></h3></a>
                            <span><?php echo $product->get_price_html(); ?></span>
                        </div>
                        <?php 
                            $available_variations=$product->get_attribute( 'color' ); // get all attributes by variations
                            $array = explode(',', $available_variations);
                            //print_r($available_variations);
                            $count = count($array);
                            if($count > 1){
                        ?>
                        <div class="product_info_colors">
                            <p><?php echo count($array); ?> MORE COLORS</p>
                        </div>
                        <?php }else if($count == 1){ if(!empty($array[0])){ ?>
                        <div class="product_info_colors">
                            <p><?php echo strtoupper($array[0]); ?></p>
                        </div>
                        <?php } } ?>
                        <?php if($current_tags[0]->slug == 'best-seller'){ ?>
                            <div class="best_seller">
                                <h4>BEST SELLER</h4>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php wp_reset_query(); ?>

            </div>

            <?php
                    
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => '4',
                    'order'      => 'desc',
                    'post_status' => 'publish',
                    'tax_query' => array(
                                    array(
                                        'taxonomy' => 'product_cat',
                                        'field'    => 'slug',
                                        'terms'    => 'childrens-clothing'
                                        )
                                    ),

                );
                $loop = new WP_Query($args);
            ?>
            <div class="product_grid_mob">
                <div class="product_featured_slider owl-carousel owl-loaded">
                    <?php while ( $loop->have_posts() ) : $loop->the_post(); global $product; ?>
                    <?php $current_tags = get_the_terms( get_the_ID(), 'product_tag' ); ?>
                    <div class="product_block">
                        <div class="product_img">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="banner_img" class="img-responsive"/>'; ?>
                                <!-- <img src="<?php echo get_template_directory_uri() ?>/image/product_img1.jpg" alt="logo" class="img-responsive"> -->
                            </a>
                        </div>
                        <div class="product_info">
                            <div class="product_info_title">
                                <a href="<?php the_permalink(); ?>"><h3><?php the_title(); ?></h3></a>
                                <span><?php echo $product->get_price_html(); ?></span>
                            </div>
                            <?php 
                                $available_variations=$product->get_attribute( 'color' ); // get all attributes by variations
                                $array = explode(',', $available_variations);
                                //print_r($available_variations);
                                $count = count($array);
                                if($count > 1){
                            ?>
                            <div class="product_info_colors">
                                <p><?php echo count($array); ?> MORE COLORS</p>
                            </div>
                            <?php }else if($count == 1){ if(!empty($array[0])){ ?>
                            <div class="product_info_colors">
                                <p><?php echo strtoupper($array[0]); ?></p>
                            </div>
                            <?php } } ?>
                            <?php if($current_tags[0]->slug == 'best-seller'){ ?>
                                <div class="best_seller">
                                    <h4>BEST SELLER</h4>
                                </div>
                            <?php } ?>
                        </div>
                        
                    </div>
                    <?php endwhile; ?>
                    <?php wp_reset_query(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if( have_rows('american_factory_section') ): ?>
    <?php while( have_rows('american_factory_section') ): the_row(); 
        $title = get_sub_field('title');
        $button = get_sub_field('button');
    ?>
<div class="american_factory_section">
    <div class="container_full">
        <div class="american_factory_aside">
            <div class="american_factory_part">
                <p><?php echo $title; ?></p>
            </div>
            <div class="american_factory_grid">
                <?php if( have_rows('items') ): ?>
                    <?php while( have_rows('items') ): the_row(); 
                        $title = get_sub_field('title');
                        $image = get_sub_field('image');
                    ?>
                <div class="american_factory_block">
                    <div class="american_factory_icon">
                        <img src="<?php echo $image; ?>" alt="logo" class="img-responsive">
                    </div>
                    <div class="american_factory_info">
                        <h5><?php echo $title; ?></h5>
                    </div>
                </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
            <a href="<?php echo $button['url']; ?>" class="learn_more"><?php echo $button['title']; ?></a>
        </div>
    </div>
</div>
    <?php endwhile; ?>
<?php endif; ?>

<?php
get_footer();
?>