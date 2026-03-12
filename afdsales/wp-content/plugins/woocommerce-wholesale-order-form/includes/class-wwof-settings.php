<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWOF_Settings')) {

    class WWOF_Settings extends WC_Settings_Page
    {

        /**
         * Constructor.
         */
        public function __construct()
        {

            $this->id    = 'wwof_settings';
            $this->label = __('Wholesale Ordering', 'woocommerce-wholesale-order-form');

            add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_page'), 30); // 30 so it is after the emails tab
            add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
            add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));
            add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));

            add_action('woocommerce_admin_field_wwof_button', array($this, 'render_wwof_button'));
            add_action('woocommerce_admin_field_wwof_editor', array($this, 'render_wwof_editor'));
            add_action('woocommerce_admin_field_wwof_help_resources', array($this, 'render_wwof_help_resources'));
            add_action('woocommerce_admin_field_wwof_image_dimension', array($this, 'render_wwof_image_dimension'));
            add_action('woocommerce_admin_field_wwof_clear_product_caching', array($this, 'render_wwof_clear_product_caching'), 10);
            add_action('woocommerce_admin_field_wwof_api_status', array($this, 'render_wwof_api_status'), 10);

            add_filter('wwof_settings_general_section_settings', array($this, 'show_hide_setting'), 10);

        }

        /**
         * Get sections.
         *
         * @return array
         * @since 1.0.0
         */
        public function get_sections()
        {

            $sections = array(
                ''                                    => __('General', 'woocommerce-wholesale-order-form'),
                'wwof_setting_filters_section'        => __('Filters', 'woocommerce-wholesale-order-form'),
                'wwof_settings_permissions_section'   => __('Permissions', 'woocommerce-wholesale-order-form'),
                'wwof_settings_cache_section'         => __('Cache', 'woocommerce-wholesale-order-form'),
                'wwof_settings_order_form_v2_section' => __('Beta', 'woocommerce-wholesale-order-form'),
                'wwof_settings_help_section'          => __('Help', 'woocommerce-wholesale-order-form'),
            );

            return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
        }

        /**
         * Output the settings.
         *
         * @since 1.0.0
         */
        public function output()
        {

            global $current_section, $wpdb;

            $settings = $this->get_settings($current_section);
            WC_Admin_Settings::output_fields($settings);

            echo '<!-- WWOF-387 Fix loading issue when opening the Filters section also when searching for large amount of products. Example 12k products. -->';
            $exclude_product_filter = get_option('wwof_filters_exclude_product_filter', array());
            $default_products       = array();

            if (!empty($exclude_product_filter)) {

                $fetch_default_products = $wpdb->get_results("
                        SELECT ID, post_title
                        FROM $wpdb->posts
                        WHERE $wpdb->posts.ID IN ( " . implode(',', $exclude_product_filter) . " )");

                if (!empty($fetch_default_products)) {
                    foreach ($fetch_default_products as $product) {
                        $default_products[] = array('id' => $product->ID, 'text' => '[ID : ' . $product->ID . '] ' . $product->post_title);
                    }

                }

            }?>

            <script type="text/javascript">
                jQuery( document ).ready( function( $ ) {

                    var default_products = <?php echo json_encode($default_products); ?>;
                    var default_ids = <?php echo json_encode($exclude_product_filter); ?>;
                    var initialPropertyOptions = [];

                    default_products.forEach(function(product){
                        var initialPropertyOption = {
                            id: product.id,
                            text: product.text,
                            selected: true
                        }
                        initialPropertyOptions.push(initialPropertyOption);
                    });

                    var product_filter = $( "#wwof_filters_exclude_product_filter" ).select2({
                        ajax: {
                            url: ajaxurl,
                            dataType: 'json',
                            delay: 250,
                            type: "POST",
                            data: function (params) {
                                return {
                                    q: params.term,
                                    action: 'wwof_get_products',
                                    page: params.page || 1
                                };
                            },
                            processResults: function (data, params) {
                                params.page = params.page || 1;
                                pageSize = 10;

                                return {
                                    results: data.results.slice((params.page - 1) * pageSize, params.page * pageSize),
                                    pagination: {
                                        more: data.results.length >= params.page * pageSize
                                    }
                                };
                            },
                            cache: true
                        },
                        data: initialPropertyOptions
                    } );
                } );
            </script><?php

        }

        /**
         * Save settings.
         *
         * @since 1.0.0
         */
        public function save()
        {

            global $current_section;

            $settings = $this->get_settings($current_section);

            // Filter wysiwyg content so it gets stored properly after sanitization
            if (isset($_POST['noaccess_message']) && !empty($_POST['noaccess_message'])) {

                foreach ($_POST['noaccess_message'] as $index => $content) {

                    $_POST[$index] = htmlentities(wpautop($content));

                }

            }

            WC_Admin_Settings::save_fields($settings);

            // Clear cache first to make sure product listings are up to date
            if (isset($_GET['tab']) && $_GET['tab'] == 'wwof_settings' && isset($_GET['section'])) {

                if ($_GET['section'] == '' || $_GET['section'] == 'wwof_settings_cache_section') {

                    global $wc_wholesale_order_form;

                    // Clear cache query ids
                    $wc_wholesale_order_form->_wwof_cache->wwof_clear_product_transients_cache();

                    // Clear cache product variations
                    $wc_wholesale_order_form->_wwof_cache->wwof_clear_product_variations_cache();

                }

            }

        }

        /**
         * Get settings array.
         *
         * @param string $current_section
         *
         * @return mixed
         * @since 1.0.0
         */
        public function get_settings($current_section = '')
        {

            if ($current_section == 'wwof_setting_filters_section') {

                // Filters Section
                $settings = apply_filters('wwof_settings_filters_section_settings', $this->_get_filters_section_settings());

            } elseif ($current_section == 'wwof_settings_permissions_section') {

                // Permissions Section
                $settings = apply_filters('wwof_settings_permissions_section_settings', $this->_get_permissions_section_settings());

            } elseif ($current_section == 'wwof_settings_cache_section') {

                // Cache Section
                $settings = apply_filters('wwof_settings_cache_section_settings', $this->_get_cache_section_settings());

            } elseif ($current_section == 'wwof_settings_order_form_v2_section') {

                // React JS Section
                $settings = apply_filters('wwof_settings_order_form_v2_section_settings', $this->_get_order_form_v2_section_settings());

            } elseif ($current_section == 'wwof_settings_help_section') {

                // Help Section
                $settings = apply_filters('wwof_settings_help_section_settings', $this->_get_help_section_settings());

            } else {

                // General Settings
                $settings = apply_filters('wwof_settings_general_section_settings', $this->_get_general_section_settings());

            }

            return apply_filters('woocommerce_get_settings_' . $this->id, $settings, $current_section);

        }

        /*
        |--------------------------------------------------------------------------------------------------------------
        | Section Settings
        |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Get general section settings.
         *
         * @since 1.0.0
         * @since 1.3.0 Add option to show/hide quantity based discounts on wholesale order page.
         *
         * @return array
         */
        private function _get_general_section_settings()
        {

            global $WWOF_SETTINGS_DEFAULT_PPP;

            // issue-233 if strings initialized on init the loco translation will will not work
            $sort_by_settings = array(
                'default' => __('Default Sorting', 'woocommerce-wholesale-order-form'),
                'menu_order' => __('Custom Ordering (menu order) + Name', 'woocommerce-wholesale-order-form'),
                'name' => __('Name', 'woocommerce-wholesale-order-form'),
                //'popularity'    =>  'Popularity (sales)',
                //'rating'        =>  'Average Rating',
                'date' => __('Sort by Date', 'woocommerce-wholesale-order-form'),
                'sku' => __('SKU', 'woocommerce-wholesale-order-form'),
                //'price'         =>  'Sort by price',
            );

            return array(

                array(
                    'title' => __('Display & Style', 'woocommerce-wholesale-order-form'),
                    'type'  => 'title',
                    'desc'  => __('These settings describe the way the order form is displayed to your customers.', 'woocommerce-wholesale-order-form'),
                    'id'    => 'wwof_display_style_title',
                ),

                array(
                    'title'   => __('Order Form Style', 'woocommerce-wholesale-order-form'),
                    'type'    => 'radio',
                    'desc'    => '',
                    'id'      => 'wwof_general_use_alternate_view_of_wholesale_page',
                    'options' => array(
                        'no'  => __('Standard (Add to cart per row) - shows an add to cart button per row which is faster for customers.', 'woocommerce-wholesale-order-form'),
                        'yes' => __('Alternate (Add to cart at bottom) - select boxes per row with an add to cart button at the bottom of the form.', 'woocommerce-wholesale-order-form'),
                    ),
                ),

                array(
                    'title'   => __('Order Form Paging Style', 'woocommerce-wholesale-order-form'),
                    'type'    => 'radio',
                    'desc'    => '',
                    'id'      => 'wwof_general_disable_pagination',
                    'options' => array(
                        'no'  => __('Paginated - split results into pages based on the products per page.', 'woocommerce-wholesale-order-form'),
                        'yes' => __('Lazy Loading - more results are loaded into the page based on the user scrolling.', 'woocommerce-wholesale-order-form'),
                    ),
                ),

                array(
                    'title'       => __('Products Per Page', 'woocommerce-wholesale-order-form'),
                    'type'        => 'number',
                    'desc'        => __('The number of products loaded per page if paginated or the number of products fetched at a time during scrolling when lazy loading.', 'woocommerce-wholesale-order-form'),
                    'id'          => 'wwof_general_products_per_page',
                    'css'         => 'width: 100px;',
                    'placeholder' => '12',
                ),

                array(
                    'title' => __('Show Variations Individually', 'woocommerce-wholesale-order-form'),
                    'type'  => 'checkbox',
                    'desc'  => __('Enabling this setting will list down each product variation individually and have its own row in the wholesale order form.', 'woocommerce-wholesale-order-form'),
                    'id'    => 'wwof_general_list_product_variation_individually',
                ),

                array(
                    'title'   => __('Product Click Action', 'woocommerce-wholesale-order-form'),
                    'type'    => 'radio',
                    'desc'    => '',
                    'id'      => 'wwof_general_display_product_details_on_popup',
                    'options' => array(
                        'no'  => __('Navigate To Single Product Page - takes the user to the single product page for that product.', 'woocommerce-wholesale-order-form'),
                        'yes' => __('Show A Product Details Popup - shows the user a popup without leaving the page.', 'woocommerce-wholesale-order-form'),
                    ),
                ),

                array(
                    'title'         => __('Display Extra Columns', 'woocommerce-wholesale-order-form'),
                    'type'          => 'checkbox',
                    'desc'          => __('Stock Quantity', 'woocommerce-wholesale-order-form'),
                    'id'            => 'wwof_general_show_product_stock_quantity',
                    'checkboxgroup' => 'start',
                ),

                array(
                    'title'         => '',
                    'type'          => 'checkbox',
                    'desc'          => __('Product SKU', 'woocommerce-wholesale-order-form'),
                    'id'            => 'wwof_general_show_product_sku',
                    'checkboxgroup' => '',
                ),

                array(
                    'title'         => '',
                    'type'          => 'checkbox',
                    'desc'          => __('Product Thumbnail', 'woocommerce-wholesale-order-form'),
                    'id'            => 'wwof_general_show_product_thumbnail',
                    'checkboxgroup' => 'end',
                ),

                array(
                    'title'    => __('Product Thumbnail Size', 'woocommerce-wholesale-order-form'),
                    'desc'     => '',
                    'id'       => 'wwof_general_product_thumbnail_image_size',
                    'css'      => '',
                    'type'     => 'wwof_image_dimension',
                    'default'  => array(
                        'width'  => '48',
                        'height' => '48',
                    ),
                    'desc_tip' => true,
                ),

                array(
                    'title' => __('Show Cart Subtotal', 'woocommerce-wholesale-order-form'),
                    'type'  => 'checkbox',
                    'desc'  => __('Shows the current cart subtotal at the bottom of the form.', 'woocommerce-wholesale-order-form'),
                    'id'    => 'wwof_general_display_cart_subtotal',
                ),

                array(
                    'title' => __('Hide Wholesale Quantity Based Pricing Tables', 'woocommerce-wholesale-order-form'),
                    'type'  => 'checkbox',
                    'desc'  => __('If a product has additional quantity based pricing a table is normally shown which can take a lot of vertical space. When checked, this will hide that table.', 'woocommerce-wholesale-order-form'),
                    'id'    => 'wwof_general_hide_quantity_discounts',
                ),

                array(
                    'title'   => __('Show Wholesale Order Requirements', 'woocommerce-wholesale-order-form'),
                    'type'    => 'select',
                    'desc'    => __('If minimum order requirements are defined in WooCommerce Wholesale Prices Premium, show a notice at the top of the form to let customers know what they have to do.', 'woocommerce-wholesale-order-form'),
                    'id'      => 'wwof_display_wholesale_price_requirement',
                    'class'   => 'chosen_select',
                    'options' => array(
                        'yes' => __('Yes', 'woocommerce-wholesale-order-form'),
                        'no'  => __('No', 'woocommerce-wholesale-order-form'),
                    ),
                    'default' => 'yes',
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wwof_display_style_title_sectionend',
                ),

                array(
                    'title' => __('Search & Filtering', 'woocommerce-wholesale-order-form'),
                    'type'  => 'title',
                    'desc'  => __('These settings related to the searchability and filterability of the order form as well as the sorting.', 'woocommerce-wholesale-order-form'),
                    'id'    => 'wwof_search_filtering_title',
                ),

                array(
                    'title' => __('Allow Zero Inventory Products', 'woocommerce-wholesale-order-form'),
                    'type'  => 'checkbox',
                    'desc'  => __('Let products with no inventory be shown in the form.', 'woocommerce-wholesale-order-form'),
                    'id'    => 'wwof_general_display_zero_products',
                ),

                array(
                    'title' => __('Allow Search By SKU', 'woocommerce-wholesale-order-form'),
                    'type'  => 'checkbox',
                    'desc'  => __('Let customers search for products by their product SKU.', 'woocommerce-wholesale-order-form'),
                    'id'    => 'wwof_general_allow_product_sku_search',
                ),

                array(
                    'title'   => __('Product Sorting', 'woocommerce-wholesale-order-form'),
                    'type'    => 'select',
                    'desc'    => __('Changes how products are sorted on the form.', 'woocommerce-wholesale-order-form'),
                    'id'      => 'wwof_general_sort_by',
                    'class'   => 'chosen_select',
                    'options' => $sort_by_settings,
                ),

                array(
                    'title'   => __('Product Sort Order', 'woocommerce-wholesale-order-form'),
                    'type'    => 'select',
                    'desc'    => '',
                    'id'      => 'wwof_general_sort_order',
                    'class'   => 'chosen_select',
                    'options' => array(
                        'asc'  => __('Ascending', 'woocommerce-wholesale-order-form'),
                        'desc' => __('Descending', 'woocommerce-wholesale-order-form'),
                    ),
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wwof_search_filtering_title_sectionend',
                ),

                array(
                    'title' => __('Misc', 'woocommerce-wholesale-order-form'),
                    'type'  => 'title',
                    'desc'  => __('These settings handle other miscellaneous parts of the way your order form works.', 'woocommerce-wholesale-order-form'),
                    'id'    => 'wwof_search_filtering_title',
                ),

                array(
                    'title'   => __('Cart Subtotal Tax', 'woocommerce-wholesale-order-form'),
                    'type'    => 'select',
                    'desc'    => __('Choose if the cart subtotal should display including or excluding taxes. This is only used if you have the <i>Show Cart Subtotal</i> setting turned on.', 'woocommerce-wholesale-order-form'),
                    'id'      => 'wwof_general_cart_subtotal_prices_display',
                    'class'   => 'chosen_select',
                    'options' => array(
                        'incl' => __('Including tax', 'woocommerce-wholesale-order-form'),
                        'excl' => __('Excluding tax', 'woocommerce-wholesale-order-form'),
                    ),
                    'default' => 'incl',
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wwof_misc_sectionend',
                ),

            );

        }

        /**
         * Get filters section settings.
         *
         * @return array
         * @since 1.0.0
         */
        private function _get_filters_section_settings()
        {

            // Get all product categories
            $termArgs = array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
            );
            $productTermsObject = get_terms($termArgs);
            $productTerms       = array();

            if (!is_wp_error($productTermsObject)) {

                foreach ($productTermsObject as $term) {
                    $productTerms[$term->slug] = $term->name;
                }

            }

            // Add "None" category selection for "no default" option
            $productTerms2 = array_merge(array('none' => __('No Default', 'woocommerce-wholesale-order-form')), $productTerms);

            return array(

                array(
                    'title' => __('Product Filtering', 'woocommerce-wholesale-order-form'),
                    'type'  => 'title',
                    'desc'  => __('The filtering options below control which products are shown or not shown on the order form.', 'woocommerce-wholesale-order-form'),
                    'id'    => 'wwof_filters_main_title',
                ),

                array(
                    'title'             => __('Filter Product Categories', 'woocommerce-wholesale-order-form'),
                    'type'              => 'multiselect',
                    'desc'              => __('Tell the order form which categories it should draw products from and don\'t show products from any other categories. Leave this empty to show products from all categories.', 'woocommerce-wholesale-order-form'),
                    'id'                => 'wwof_filters_product_category_filter',
                    'class'             => 'chosen_select',
                    'css'               => 'min-width:300px;',
                    'custom_attributes' => array(
                        'multiple'         => 'multiple',
                        'data-placeholder' => __('Select Some Product Categories...', 'woocommerce-wholesale-order-form'),
                    ),
                    'options'           => $productTerms,
                ),

                array(
                    'title'   => __('Default Category Filter', 'woocommerce-wholesale-order-form'),
                    'type'    => 'select',
                    'desc'    => __('Pre-filter the order form on load by specifying a default category on the search box.', 'woocommerce-wholesale-order-form'),
                    'id'      => 'wwof_general_default_product_category_search_filter',
                    'class'   => 'chosen_select',
                    'options' => $productTerms2,
                    'default' => 'none',
                ),

                array(
                    'title'             => __('Exclude Products', 'woocommerce-wholesale-order-form'),
                    'type'              => 'multiselect',
                    'desc'              => __('Specify specific products to hide from the order form product list.', 'woocommerce-wholesale-order-form'),
                    'id'                => 'wwof_filters_exclude_product_filter',
                    'css'               => 'min-width:300px;',
                    'custom_attributes' => array(
                        'multiple'         => 'multiple',
                        'data-placeholder' => __('Select Some Products...', 'woocommerce-wholesale-order-form'),
                    ),
                    'options'           => array(),
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wwof_filters_sectionend',
                ),

            );

        }

        /**
         * Get permissions section settings.
         *
         * @return array
         * @since 1.0.0
         */
        private function _get_permissions_section_settings()
        {

            // Get all user roles
            global $wp_roles;

            if (!isset($wp_roles)) {
                $wp_roles = new WP_Roles();
            }

            $allUserRoles = $wp_roles->get_names();

            return array(

                array(
                    'title' => __('Permissions Options', 'woocommerce-wholesale-order-form'),
                    'type'  => 'title',
                    'desc'  => '',
                    'id'    => 'wwof_permissions_main_title',
                ),

                array(
                    'title'             => __('User Role Filter', 'woocommerce-wholesale-order-form'),
                    'type'              => 'multiselect',
                    'desc'              => __('Only allow a given user role/s to access the wholesale page. Left blank to disable filter.', 'woocommerce-wholesale-order-form'),
                    'desc_tip'          => true,
                    'id'                => 'wwof_permissions_user_role_filter',
                    'class'             => 'chosen_select',
                    'css'               => 'min-width:300px;',
                    'custom_attributes' => array(
                        'multiple'         => 'multiple',
                        'data-placeholder' => __('Select Some User Roles...', 'woocommerce-wholesale-order-form'),
                    ),
                    'options'           => $allUserRoles,
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wwof_permissions_role_filter_sectionend',
                ),

                array(
                    'title' => __('Access Denied Message', 'woocommerce-wholesale-order-form'),
                    'type'  => 'title',
                    'desc'  => __('Message to display to users who do not have permission to access the wholesale order form.', 'woocommerce-wholesale-order-form'),
                    'id'    => 'wwof_permissions_noaccess_section_title',
                ),

                array(
                    'title'    => __('Title', 'woocommerce-wholesale-order-form'),
                    'type'     => 'text',
                    'desc'     => __('Defaults to <b>"Access Denied"</b> if left blank', 'woocommerce-wholesale-order-form'),
                    'desc_tip' => true,
                    'id'       => 'wwof_permissions_noaccess_title',
                    'css'      => 'min-width: 400px;',
                ),

                array(
                    'title'    => __('Message', 'woocommerce-wholesale-order-form'),
                    'type'     => 'wwof_editor',
                    'desc'     => __('Defaults to <b>"You do not have permission to view wholesale product listing"</b> if left blank', 'woocommerce-wholesale-order-form'),
                    'desc_tip' => true,
                    'id'       => 'wwof_permissions_noaccess_message',
                    'css'      => 'min-width: 400px; min-height: 100px;',
                ),

                array(
                    'title'    => __('Login URL', 'woocommerce-wholesale-order-form'),
                    'type'     => 'text',
                    'desc'     => __('URL of the login page. Uses default WordPress login URL if left blank', 'woocommerce-wholesale-order-form'),
                    'desc_tip' => true,
                    'id'       => 'wwof_permissions_noaccess_login_url',
                    'css'      => 'min-width: 400px;',
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wwof_permissions_sectionend',
                ),

            );

        }

        /**
         * Get Order Form v2 section settings.
         *
         * @return array
         * @since 1.15
         */
        private function _get_order_form_v2_section_settings()
        {

            return array(

                array(
                    'title' => __('Beta Options', 'woocommerce-wholesale-order-form'),
                    'type'  => 'title',
                    'desc'  => __("This beta form feature is a preview of the new version of the Order Form we are building using React JS (a newer and more flexible technology).<br/><br/><a href='https://wholesalesuiteplugin.com/order-form-beta-early-access/' target='_blank'>Please see these instructions</a> for how to enable and test a beta form on the front end of your website.<br/><br/>Warning: This is a beta feature we're making available for early access. As such some integrations and features may be incomplete or not working to the same standard as the current stable form. By enabling you acknowledge you are using it at your own risk. We don't recommend using it for production environments as yet.", 'woocommerce-wholesale-order-form'),
                    'id'    => 'wwof_order_form_v2_main_title',
                ),

                array(
                    'name' => __('Enable React JS Order Form', 'woocommerce-wholesale-order-form'),
                    'type' => 'checkbox',
                    'desc' => __('If checked, you will be able to use the new more efficient order form powered by React JS. To display the new order form please use this shortcode <code>[wwof_product_listing beta="true"]</code>', 'woocommerce-wholesale-order-form'),
                    'id'   => 'wwof_order_form_v2_enable_order_form',
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wwof_order_form_v2_sectionend',
                ),

                array(
                    'title' => __('WooCommerce API', 'woocommerce-wholesale-order-form'),
                    'type'  => 'title',
                    'desc'  => __('Create consumer key and secret in WooCommerce > Settings > Advanced > REST API', 'woocommerce-wholesale-order-form'),
                    'id'    => 'wwof_order_form_v2_main_title',
                ),

                array(
                    'title'    => __('API Status', 'woocommerce-wholesale-order-form'),
                    'type'     => 'wwof_api_status',
                    'desc_tip' => __('Check API Status', 'woocommerce-wholesale-order-form'),
                    'id'       => 'wwof_api_status',
                ),

                array(
                    'title'    => __('Consumer Key', 'woocommerce-wholesale-order-form'),
                    'type'     => 'text',
                    'desc'     => __('WooCommerce API consumer key', 'woocommerce-wholesale-order-form'),
                    'desc_tip' => true,
                    'id'       => 'wwof_order_form_v2_consumer_key',
                ),

                array(
                    'title'    => __('Consumer Secret', 'woocommerce-wholesale-order-form'),
                    'type'     => 'password',
                    'desc'     => __('WooCommerce API Consumer Secret', 'woocommerce-wholesale-order-form'),
                    'desc_tip' => true,
                    'id'       => 'wwof_order_form_v2_consumer_secret',
                ),

                array(
                    'title'       => __('Generate API Keys', 'woocommerce-wholesale-order-form'),
                    'type'        => 'wwof_button',
                    'button_text' => __('Auto Generate', 'woocommerce-wholesale-order-form'),
                    'desc'        => '',
                    'desc_tip'    => __('This button will show up if keys are empty or invalid. Once the keys are generated it is saved directly.', 'woocommerce-wholesale-order-form'),
                    'id'          => 'wwof_generate_api_keys',
                    'class'       => 'button button-secondary',
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wwof_permissions_role_filter_sectionend',
                ),

            );

        }

        /**
         * Get cache section settings.
         *
         * @return array
         * @since 1.14.1
         */
        private function _get_cache_section_settings()
        {

            return array(

                array(
                    'title' => __('Cache Options', 'woocommerce-wholesale-order-form'),
                    'type'  => 'title',
                    'desc'  => '',
                    'id'    => 'wwof_cache_main_title',
                ),

                array(
                    'name' => __('Enable product caching', 'woocommerce-wholesale-order-form'),
                    'type' => 'checkbox',
                    'desc' => __('When enabled, the order form will cache product IDs and product variation IDs in a cache to dramatically decrease the load time. This is especially useful for large product catalogs.', 'woocommerce-wholesale-order-form'),
                    'id'   => 'wwof_enable_product_cache',
                ),

                array(
                    'name' => '',
                    'type' => 'wwof_clear_product_caching',
                    'desc' => '',
                    'id'   => 'wwof_clear_product_caching',
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wwof_cache_sectionend',
                ),

            );

        }

        /**
         * Get help section settings.
         *
         * @return array
         * @since 1.0.0
         */
        private function _get_help_section_settings()
        {

            return array(

                array(
                    'title' => __('Help Options', 'woocommerce-wholesale-order-form'),
                    'type'  => 'title',
                    'desc'  => '',
                    'id'    => 'wwof_help_main_title',
                ),

                array(
                    'name' => '',
                    'type' => 'wwof_help_resources',
                    'desc' => '',
                    'id'   => 'wwof_help_help_resources',
                ),

                array(
                    'title'       => __('Create Wholesale Ordering Page', 'woocommerce-wholesale-order-form'),
                    'type'        => 'wwof_button',
                    'button_text' => __('Create Page', 'woocommerce-wholesale-order-form'),
                    'desc'        => '',
                    'id'          => 'wwof_help_create_wholesale_page',
                    'class'       => 'button button-primary',
                ),

                array(
                    'name' => __('Clean up plugin options on un-installation', 'woocommerce-wholesale-order-form'),
                    'type' => 'checkbox',
                    'desc' => __('If checked, removes all plugin options when this plugin is uninstalled. <b>Warning:</b> This process is irreversible.', 'woocommerce-wholesale-order-form'),
                    'id'   => 'wwof_settings_help_clean_plugin_options_on_uninstall',
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wwof_help_sectionend',
                ),

            );

        }

        /*
        |--------------------------------------------------------------------------------------------------------------
        | Custom Settings Fields
        |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Render custom setting field (wwof button)
         *
         * @param $value
         * @since 1.0.0
         */
        public function render_wwof_button($value)
        {

            if ($value['id'] == 'wwof_generate_api_keys' && Order_Form_API_KEYS::is_api_key_valid() == true) {
                return;
            }

            $field = WC_Admin_Settings::get_field_description($value);

            // Change type accordingly
            $type = $value['type'];
            if ($type == 'wwof_button') {
                $type = 'button';
            }

            // Custom attribute handling
            $custom_attributes = array();

            if (!empty($value['custom_attributes']) && is_array($value['custom_attributes'])) {
                foreach ($value['custom_attributes'] as $attribute => $attribute_value) {
                    $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
                }
            }?>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr($value['id']); ?>">
                        <?php echo esc_html($value['title']); ?>
                        <?php echo $field['tooltip_html'] ?>
                    </label>
                </th>
                <td class="forminp forminp-<?php echo sanitize_title($value['type']); ?>">
                    <input
                        name="<?php echo esc_attr($value['id']); ?>"
                        id="<?php echo esc_attr($value['id']); ?>"
                        type="<?php echo esc_attr($type); ?>"
                        style="<?php echo esc_attr($value['css']); ?>"
                        value="<?php echo esc_attr($value['button_text']); ?>"
                        class="<?php echo esc_attr($value['class']); ?>"
                        <?php echo implode(' ', $custom_attributes); ?>
                        />
                    <span class="spinner" style="margin-top: 3px; float: none;"></span>
                    <?php echo $value['desc']; ?>


                </td>
            </tr><?php

        }

        /**
         * Render custom setting field (wwof editor)
         *
         * @param $value
         * @since 1.1.0
         */
        public function render_wwof_editor($value)
        {

            // Custom attribute handling
            $custom_attributes = array();

            if (!empty($value['custom_attributes']) && is_array($value['custom_attributes'])) {
                foreach ($value['custom_attributes'] as $attribute => $attribute_value) {
                    $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
                }
            }

            // Description handling
            if (true === $value['desc_tip']) {

                $description = '';
                $tip         = $value['desc'];

            } elseif (!empty($value['desc_tip'])) {

                $description = $value['desc'];
                $tip         = $value['desc_tip'];

            } elseif (!empty($value['desc'])) {

                $description = $value['desc'];
                $tip         = '';

            } else {
                $description = $tip = '';
            }

            // Description handling
            $field_description = WC_Admin_Settings::get_field_description($value);

            $val = get_option('wwof_permissions_noaccess_message');
            if (!$val) {
                $val = '';
            }?>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr($value['id']); ?>">
                        <?php echo esc_html($value['title']); ?>
                        <?php echo $field_description['tooltip_html']; ?>
                    </label>
                </th>
                <td class="forminp forminp-<?php echo sanitize_title($value['type']); ?>">
                    <?php wp_editor(html_entity_decode($val), 'wwof_permissions_noaccess_message', array('wpautop' => true, 'textarea_name' => "noaccess_message[" . $value['id'] . "]"));?>
                    <?php echo $description; ?>
                </td>
            </tr><?php

        }

        /**
         * Render help content page.
         *
         * @param $value
         * @since 1.6.0
         */
        public function render_wwof_help_resources($value)
        {

            echo '<tr valign="top">';
            echo '<th scope="row" class="titledesc">';
            echo '<label for="">' . __('Knowledge Base', 'woocommerce-wholesale-order-form') . '</label>';
            echo '</th>';
            echo '<td class="forminp forminp-' . sanitize_title($value['type']) . '">';
            echo sprintf(__('Looking for documentation? Please see our growing <a href="%1$s" target="_blank">Knowledge Base</a>', 'woocommerce-wholesale-order-form'), "https://wholesalesuiteplugin.com/knowledge-base/?utm_source=Order%20Form%20Plugin&utm_medium=Settings&utm_campaign=Knowledge%20Base%20");
            echo '</td>';
            echo '</tr>';

        }

        /**
         * Render custom image dimension setting
         *
         * @param $value
         * @since 1.6.0
         */
        public function render_wwof_image_dimension($value)
        {

            $field_description = WC_Admin_Settings::get_field_description($value);
            $imageSize         = get_option('wwof_general_product_thumbnail_image_size');

            extract($field_description);

            $width  = isset($imageSize) && !empty($imageSize['width']) ? $imageSize['width'] : $value['default']['width'];
            $height = isset($imageSize) && !empty($imageSize['height']) ? $imageSize['height'] : $value['default']['height'];?>

            <tr valign="top" class="<?php echo $value['class']; ?>">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr($value['id']); ?>">
                        <?php echo esc_html($value['title']); ?>
                        <?php echo $tooltip_html; ?>
                    </label>
                </th>
                <td class="forminp image_width_settings">
                    <input name="<?php echo esc_attr($value['id']); ?>[width]" id="<?php echo esc_attr($value['id']); ?>-width" type="text" size="3" value="<?php echo $width; ?>" /> &times; <input name="<?php echo esc_attr($value['id']); ?>[height]" id="<?php echo esc_attr($value['id']); ?>-height" type="text" size="3" value="<?php echo $height; ?>" />px
                </td>
            </tr><?php

        }

        /**
         * Render clear product cache option
         *
         * @param $value
         * @since 1.14.1
         */
        public function render_wwof_clear_product_caching($value)
        {
            ?>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for=""><?php _e('Clear product cache', 'woocommerce-wholesale-order-form');?></label>
                </th>
                <td class="forminp">
                    <input type="button" name="wwof_clear_product_caching" id="wwof_clear_product_caching" class="button button-secondary" value="<?php _e('Clear Cache', 'woocommerce-wholesale-order-form');?>">
                    <span class="spinner" style="float: none; display: inline-block; visibility: hidden;"></span>
                    <p class="desc"><?php _e('Clear both the product ID and variation ID caches. Caches are automatically rebuilt and maintained by the system.', 'woocommerce-wholesale-order-form');?></p>
                </td>
            </tr><?php

        }

        /**
         * Show or hide options
         *
         * @param $value
         * @since 1.15.4
         */
        public function show_hide_setting($settings)
        {

            foreach ($settings as $key => $setting) {

                if (!is_plugin_active('woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php') &&
                    ($setting['id'] === 'wwof_display_wholesale_price_requirement' || $setting['id'] === 'wwof_general_hide_quantity_discounts')) {
                    unset($settings[$key]);
                }

                if (get_option('wwof_general_show_product_thumbnail') !== 'yes' &&
                    $setting['id'] === 'wwof_general_product_thumbnail_image_size') {
                    $settings[$key]['class'] = 'hide-thumbnail';
                }

            }

            return $settings;

        }

        /**
         * Render API Status
         *
         * @param array $value
         * @since 1.20
         */
        public function render_wwof_api_status($value)
        {

            $field = WC_Admin_Settings::get_field_description($value);

            $row = '<tr valign="top">';
            $row .= '<th scope="row" class="titledesc">';
            $row .= '<label for="' . esc_attr($value['id']) . '">';
            $row .= esc_html($value['title']);
            $row .= $field['tooltip_html'];
            $row .= '</label>';
            $row .= '</th>';
            $row .= '<td class="forminp">';
            $is_valid     = Order_Form_API_KEYS::is_api_key_valid();
            $show_valid   = $is_valid ? "display:block;" : "display:none;";
            $show_invalid = !$is_valid ? "display:block;" : "display:none;";
            $row .= '<div class="status valid" style="' . $show_valid . '">' . __('API keys are valid.', 'woocommerce-wholesale-order-form') . ' <span class="dashicons dashicons-yes-alt" style="color:green;"></span></div>';
            $row .= '<div class="status invalid" style="' . $show_invalid . '">' . __('Please enter a valid API keys.', 'woocommerce-wholesale-order-form') . ' <span class="dashicons dashicons-dismiss" style="color:red;"></span></div>';
            $row .= '</td>';
            $row .= '</tr>';

            echo $row;

        }

    }

}

return new WWOF_Settings();