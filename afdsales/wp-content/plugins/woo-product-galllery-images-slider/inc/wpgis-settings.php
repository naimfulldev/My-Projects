<?php

/**
 * WordPress settings API demo class
 *
 * @author Tareq Hasan
 */
if ( !class_exists('WeDevs_Settings_API_Test' ) ):
class WeDevs_Settings_API_Test {

    private $settings_api;

    function __construct() {
        $this->settings_api = new WeDevs_Settings_API;

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }


		
		
    function admin_menu() {
        add_submenu_page( 
            'woocommerce', 'WPGIS Settings', 'WPGIS Settings', 'manage_options', 'pgs-page', array(&$this, 'plugin_page')
			
        );
		
    }
	


    function get_settings_sections() {
        $sections = array(
            array(
                'id'    => 'genaral_options',
                'title' => __( 'General Options', 'wpgis' )
            ),
            array(
                'id'    => 'single_options',
                'title' => __( 'Feature Image Options', 'wpgis' )
            ),
            array(
                'id'    => 'lightbox_options',
                'title' => __( 'LightBox Options', 'wpgis' )
            ),
            array(
                'id'    => 'zoom_magify',
                'title' => __( 'Zoom Options', 'wpgis' )
            ),
            array(
                'id'    => 'wpgis_advance',
                'title' => __( 'Advance Options', 'wpgis' )
            )
           
        );
        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $settings_fields = array(
            'genaral_options' => array(
                array(
                    'name'    => 'layout',
                    'label'   => __( 'Gallery Layout', 'wpgis' ),
                    
                    'type'    => 'select',
                    'default' => 'horizontal',
                    'options' => array(
                        'vertical' => 'Vertical Left',
                        'vertical_r' => 'Vertical Right',
                        'horizontal'  => 'Horizontal'
                    )
                ),
                 array(
                    'name'              => 'thum2show',
                    'label'             => __( 'Thumbnails To Show', 'wpgis' ),
                    'type'              => 'number',
                    'default'           => '4',
                    'sanitize_callback' => 'sanitize_text_field'
                ),

                 array(
                    'name'              => 'thumscrollby',
                    'label'             => __( 'Thumbnails Scroll By', 'wpgis' ),
                    'desc'  => __('Note: You can set the number of thumbails for scrolling when arrows are clicked','wpgis'),
                    'type'              => 'number',
                    'default'           => '3',
                    'sanitize_callback' => 'sanitize_text_field'
                ),

               
                array(
                    'name'    => 'lightbox',
                    'label'   => __( 'LightBox For Gallery', 'wpgis' ),
                    
                    'type'    => 'checkbox',
                    'default' => 'false',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
               
                array(
                    'name'    => 'infinite',
                    'label'   => __( 'Infinite', 'wpgis' ),
                    
                    'type'    => 'checkbox',
                    'default' => 'false',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
                array(
                    'name'    => 'dragging',
                    'label'   => __( 'Mouse Dragging', 'wpgis' ),
                   
                    'type'    => 'checkbox',
                    'default' => 'false',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
                array(
                    'name'    => 'rtl',
                    'label'   => __( 'RTL Mode', 'wpgis' ),
                    
                    'type'    => 'checkbox',
                    'default' => 'false',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
                
                array(
                    'name'    => 'autoplay',
                    'label'   => __( 'Autoplay', 'wpgis' ),
                    'desc'  => __('Note: This option will not work if "LightBox For Gallery" Trun on','wpgis'),
                    'type'    => 'checkbox',
                    
                    'default' => 'false',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
               
                array(
                    'name'    => 'autoplaySpeed',
                    'label'   => __( 'AutoPlay Timeout', 'wpgis' ),
                    'desc'              => __( '1000 = 1 Sec', 'wpgis' ),
                    'type'    => 'text',
                    'default' => '5000',
                    
                ),
                array(
                    'name'    => 'video_icon_color',
                    'label'   => __( 'Video Icon Color', 'wpgis' ),
                    
                    'type'    => 'color',
                    'default' => '#e54634'
                ),
                array(
                    'name'    => 'nav_icon_color',
                    'label'   => __( 'Navigation Icon Color', 'wpgis' ),
                    
                    'type'    => 'color',
                    'default' => '#fff'
                ),
                array(
                    'name'    => 'nav_bg_color',
                    'label'   => __( 'Navigation Background Color', 'wpgis' ),
                    
                    'type'    => 'color',
                    'default' => '#000000'
                ),
            ),
        
            'single_options' => array(

               
                array(
                    'name'    => 'hide_nav',
                    'label'   => __( 'Navigation Arrow', 'wpgis' ),
                    
                    'type'    => 'checkbox',
                    'default' => 'true',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
                array(
                    'name'    => 'fade',
                    'label'   => __( 'Fade Effect', 'wpgis' ),
                    
                    'type'    => 'checkbox',
                    'default' => 'false',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
                array(
                    'name'    => 'swipe',
                    'label'   => __( 'Swipe To Slide', 'wpgis' ),
                    
                    'type'    => 'checkbox',
                    'default' => 'true',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
                array(
                    'name'    => 'dots',
                    'label'   => __( 'Dots', 'wpgis' ),
                    'desc'    => __( 'Note: This option will not work if "LightBox For Gallery" Trun on under the "General options" Tab', 'wpgis' ),
                    'type'    => 'checkbox',
                    'default' => 'false',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
                array(
                    'name'    => 'hide_gallery',
                    'label'   => __( 'Hide Thumbnails', 'wpgis' ),
                    'desc'    => __( 'You need to configure the settings from general Options', 'wpgis' ),
                    'type'    => 'checkbox',
                    'default' => 'false',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
                
               
                
                
            ),
            'lightbox_options' => array(
                array(
                    'name'    => 'arrowsColor',
                    'label'   => __( 'Navigation Arrows Color', 'wpgis' ),
                    
                    'type'    => 'color',
                    'default' => '#fff'
                ),
                array(
                    'name'    => 'bgcolor',
                    'label'   => __( 'Image Border Color', 'wpgis' ),
                    
                    'type'    => 'color',
                    'default' => '#fff'
                ),
                array(
                    'name'    => 'lightbox_framewidth',
                    'label'   => __( 'Image Frame Width', 'wpgis' ),
                    'desc'              => __( 'If the Lightbox image is not Fit to the Screen than you can use this option. <br>Default: 800(in Pixel)', 'wpgis' ),
                    'type'    => 'number',
                    'default' => '800',
                    
                ),
                array(
                    'name'              => 'borderwidth',
                    'label'             => __( 'Image Border Width', 'wpgis' ),
                    'desc'              =>__('In Pixel','wpgis'),
                    'type'              => 'number',
                    'default'           => '5',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                array(
                    'name'    => 'spinColor',
                    'label'   => __( 'Preloader color', 'wpgis' ),
                    
                    'type'    => 'color',
                    'default' => '#fff'
                ),
                array(
                    'name'    => 'spinner1',
                    'label'   => __( 'Preloader', 'wpgis' ),
                    
                    'type'    => 'select',
                    'default' => 'double-bounce',
                    'options' => array(
                        'rotating-plane' => 'Rotating Plane',
                        'double-bounce'  => 'Double Bounce',
                        'wave'  => 'Wave',
                        'cube-grid'  => 'Cube Grid',
                        'three-bounce'  => 'Three Bounce',
                        'spinner-pulse'  => 'Spinner Pulse',
                        'wandering-cubes'  => 'Wandering Cubes'
                    )
                ),

              

                array(
                    'name'    => 'lightbox_infinite',
                    'label'   => __( 'Infinite', 'wpgis' ),
                    
                    'type'    => 'checkbox',
                    'default' => 'false',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
                array(
                    'name'    => 'autoplay_videos',
                    'label'   => __( 'Automatic play for videos', 'wpgis' ),
                    
                    'type'    => 'checkbox',
                    'default' => 'true',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
                array(
                    'name'    => 'numeratio',
                    'label'   => __( 'Show Navigation number', 'wpgis' ),
                    
                    'type'    => 'checkbox',
                    'default' => 'true',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
                array(
                    'name'    => 'titlePosition',
                    'label'   => __( 'Title Position', 'wedevs' ),
                    
                    'type'    => 'select',
                    'default' => 'bottom',
                    'options' => array(
                        'top' => 'Top',
                        'bottom'  => 'Bottom'
                    )
                ),
                array(
                    'name'    => 'titleBackground',
                    'label'   => __( 'Title background color', 'wpgis' ),
                    
                    'type'    => 'color',
                    'default' => '#000000'
                ),
                array(
                    'name'    => 'titleColor',
                    'label'   => __( 'Title Text Color', 'wpgis' ),
                    
                    'type'    => 'color',
                    'default' => '#fff'
                ),
                
            ),
            'wpgis_advance' => array(
                array(
                    'name'    => 'themeco',
                    'label'   => __( 'Confict Fix', 'wpgis' ),
                    'desc'        => __( 'Enable it if you are using Pro & X Theme ', 'wpgis' ),
                    'type'    => 'checkbox',
                    'default' => 'false',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
                array(
                    'name'    => 'layout_broke',
                    'label'   => __( 'Layout Fix', 'wpgis' ),
                    'desc'        => __( 'After activate This plugin if you see the single page layout is broken then Enable this option and the problem will fixed', 'wpgis' ),
                    'type'    => 'checkbox',
                    'default' => 'false',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
                array(
                    'name'    => 'layout_pb',
                    'label'   => __( 'Custom Single Page', 'wpgis' ),
                    'desc'        => __( 'If you are using Visual composer and use custom single Product page <br>then you can see a new shortcode called "wpgis" under WooCommerce tab into VC page Builder.', 'wpgis' ),
                    'type'    => 'checkbox',
                    'default' => 'false',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
                 array(
                'name' => 'custom_css',
                'label' => __('Custom Css', 'wpgis'),
                'desc' => __('If you need to Override any Style', 'wpgis'),
                'type' => 'textarea'
                ),
            ),
            'zoom_magify' => array(

                array(
                    'name'    => 'zoom_start',
                    'label'   => __( 'Zoom', 'wpgis' ),
                    'desc'        => __( 'Turn on Woocommerce Default Zoom for Single Products', 'wpgis' ),
                    'type'    => 'checkbox',
                    'default' => 'false',
                    'options' => array(
                        'true' => 'Yes',
                        'false'  => 'No'
                    )
                ),
            )
		);
            

        return $settings_fields;
    }

    function plugin_page() {
        echo '<div class="wrap wppine-backend-style">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }


}
endif;
