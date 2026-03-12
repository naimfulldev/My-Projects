<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('Order_Form_I18n_API_Controller')) {

    /**
     * Model that houses the logic of Order Form Settngs REST API.
     *
     * @since 1.21
     */
    class Order_Form_I18n_API_Controller extends WP_REST_Controller
    {

        /**
         * Endpoint namespace.
         *
         * @var string
         */
        protected $namespace = 'wwof/v1';

        /**
         * Route base.
         *
         * @var string
         */
        protected $rest_base = 'i18n';

        /**
         * Order_Form_I18n_API_Controller constructor.
         *
         * @since 1.21
         * @access public
         */
        public function __construct()
        {

            // Fires when preparing to serve an API request.
            add_action("rest_api_init", array($this, "register_routes"));

        }

        /**
         * Register REST API routes and endpoints.
         *
         * @since 1.21
         * @access public
         *
         * @return void
         */
        public function register_routes()
        {

            register_rest_route(
                $this->namespace,
                '/' . $this->rest_base,
                array(
                    array(
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => array($this, 'get_items'),
                        'permission_callback' => array($this, 'permissions_check'),
                    ),
                    'schema' => array($this, 'get_public_item_schema'),
                )
            );

        }

        /**
         * Check whether the user has permission perform the request.
         *
         * @since 1.21
         * @param  WP_REST_Request
         * @return WP_Error|boolean
         */
        public function permissions_check($request)
        {

            // Make GET request public
            if ($request->get_method() === 'GET') {
                return true;
            }

            return false;

        }

        /**
         * Get WWWOF Settings.
         *
         * @since 1.21
         * @param  WP_REST_Request
         * @access public
         * @return WP_REST_Response
         */
        public function get_items($request)
        {
            // App
            $source  = isset($request['source']) ? sanitize_text_field($request['source']) : '';
            $strings = array(
                'backend'  => $this->backend_strings(),
                'frontend' => $this->frontend_strings(),
            );

            $strings = !empty($source) && isset($strings[$source]) ? $strings[$source] : $strings;

            $response = rest_ensure_response($strings);

            return $response;

        }

        /**
         * Get backend app strings.
         *
         * @since 1.21
         * @access public
         * @return array
         */
        private function backend_strings()
        {
            $backend_strings = array(
                // App Component
                'app'                         => array(
                    'add_form'    => __('Add Form', 'woocommerce-wholesale-order-form'),
                    'heading'     => __('Order Forms', 'woocommerce-wholesale-order-form'),
                    'description' => __('Below is a list of all the order forms for displaying products that you have on your store. Click each form to edit it\'s characteristics or add a new form using the button above. Forms can be placed on pages via the given shortcode or via an editor block. Forms can be reused on multiple pages and you can also query what pages each form appears on.', 'woocommerce-wholesale-order-form'),
                ),
                // OrderFormsTable Component
                'order_forms_table'           => array(
                    'name_col'                  => __('Order Form Name', 'woocommerce-wholesale-order-form'),
                    'shortcode_col'             => __('Shortcode', 'woocommerce-wholesale-order-form'),
                    'locations_col'             => __('Locations', 'woocommerce-wholesale-order-form'),
                    'status_col'                => __('Status', 'woocommerce-wholesale-order-form'),
                    'action_col'                => __('Action', 'woocommerce-wholesale-order-form'),
                    'locations_tooltip'         => __('Locations of the order forms.', 'woocommerce-wholesale-order-form'),
                    'edit_str'                  => __('Edit', 'woocommerce-wholesale-order-form'),
                    'delete_str'                => __('Delete', 'woocommerce-wholesale-order-form'),
                    'confirm_delete_msg'        => __('Do you want to delete this form?', 'woocommerce-wholesale-order-form'),
                    'confirm_delete_msg_plural' => __('Do you want to delete these forms?', 'woocommerce-wholesale-order-form'),
                    'draft_str'                 => __('Draft', 'woocommerce-wholesale-order-form'),
                    'published_str'             => __('Published', 'woocommerce-wholesale-order-form'),
                    'copy_str'                  => __('Copy', 'woocommerce-wholesale-order-form'),
                    'copied_str'                => __('Copied', 'woocommerce-wholesale-order-form'),
                    'delete_success'            => __('Deleted Successfully!', 'woocommerce-wholesale-order-form'),
                    'delete_failed'             => __('Unable to delete!', 'woocommerce-wholesale-order-form'),
                    'ok_text'                   => __('OK', 'woocommerce-wholesale-order-form'),
                    'cancel_text'               => __('Cancel', 'woocommerce-wholesale-order-form'),
                    'of_text'                   => __('of', 'woocommerce-wholesale-order-form'),
                    'items_text'                => __('items', 'woocommerce-wholesale-order-form'),
                    'page'                      => __('page', 'woocommerce-wholesale-order-form'),
                    'pages'                     => __('pages', 'woocommerce-wholesale-order-form'),
                ),
                // OrderFormContent Component
                'order_form_content'          => array(
                    'formHeader'    => __('ORDER FORM HEADER', 'woocommerce-wholesale-order-form'),
                    'formTable'     => __('ORDER FORM TABLE', 'woocommerce-wholesale-order-form'),
                    'formFooter'    => __('ORDER FORM FOOTER', 'woocommerce-wholesale-order-form'),
                    // Section Table Component
                    'section_table' => array(
                        'drop_table_columns_here' => __('Drop Tables Column Here', 'woocommerce-wholesale-order-form'),
                    ),
                ),

                // SectionSettings Component
                'section_settings'            => array(
                    'settings_for' => __('Settings for', 'woocommerce-wholesale-order-form'),
                    'formHeader'   => __('Header', 'woocommerce-wholesale-order-form'),
                    'formTable'    => __('Table', 'woocommerce-wholesale-order-form'),
                    'formFooter'   => __('Footer', 'woocommerce-wholesale-order-form'),
                ),
                // EditorRightContent Component
                'editor_right_content'        => array(
                    'edit_form' => __('Edit Form', 'woocommerce-wholesale-order-form'),
                    'settings'  => __('Settings', 'woocommerce-wholesale-order-form'),
                    'locations' => __('Locations', 'woocommerce-wholesale-order-form'),

                ),
                // EditFormTab Component
                'edit_form_tab'               => array(
                    'form_elements_heading' => __('Order Form Elements', 'woocommerce-wholesale-order-form'),
                    'form_elements_desc'    => __('Click and drag the elements you want on your order form into position.', 'woocommerce-wholesale-order-form'),
                    'headerElements'        => __('HEADER/FOOTER ELEMENTS', 'woocommerce-wholesale-order-form'),
                    'tableElements'         => __('TABLE COLUMNS', 'woocommerce-wholesale-order-form'),
                ),
                // FormSettingsTab Component
                'form_settings_tab'           => array(
                    'additional_note' => __('Additional Note', 'woocommerce-wholesale-order-form'),
                    'more_info'       => __('Click For More Info', 'woocommerce-wholesale-order-form'),

                ),
                // LocationsTab Component
                'locations_tab'               => array(
                    'no_data'               => __('No Data', 'woocommerce-wholesale-order-form'),
                    'locations_tab_heading' => __('Locations', 'woocommerce-wholesale-order-form'),
                    'locations_tab_desc'    => __('This form can be found on the following locations.', 'woocommerce-wholesale-order-form'),

                ),
                // EditorLeftContent Component
                'editor_left_content'         => array(
                    'cant_fetch'             => __("Data cannot be fetched!", 'woocommerce-wholesale-order-form'),
                    'not_found'              => __("Sorry, the page you visited does not exist.", 'woocommerce-wholesale-order-form'),
                    'go_back'                => __("Go Back", 'woocommerce-wholesale-order-form'),
                    'order_form_added'       => __("Order Form Added!", 'woocommerce-wholesale-order-form'),
                    'error_adding'           => __("Error Adding Order Form!", 'woocommerce-wholesale-order-form'),
                    'order_form_saved_draft' => __("Order Form Saved as Draft!", 'woocommerce-wholesale-order-form'),
                    'order_form_saved'       => __("Order Form Saved!", 'woocommerce-wholesale-order-form'),
                    'error_updating'         => __("Error Updating Order Form!", 'woocommerce-wholesale-order-form'),
                    'add_order_form'         => __("Add Order Form", 'woocommerce-wholesale-order-form'),
                    'edit_order_form'        => __("Edit Order Form", 'woocommerce-wholesale-order-form'),
                    'save_draft'             => __("Save Draft", 'woocommerce-wholesale-order-form'),
                    'publish_str'            => __("Publish", 'woocommerce-wholesale-order-form'),
                    'update_str'             => __("Update", 'woocommerce-wholesale-order-form'),
                    'delete_form'            => __("Delete Form", 'woocommerce-wholesale-order-form'),
                    'form_title'             => __("Form Title", 'woocommerce-wholesale-order-form'),
                    'is_required'            => __("is required!", 'woocommerce-wholesale-order-form'),
                ),
                // ManageColumn Component
                'manage_column'               => array(
                    'settings_for'   => __("Settings for this", 'woocommerce-wholesale-order-form'),
                    'column_str'     => __("column", 'woocommerce-wholesale-order-form'),
                    'item_str'       => __("item", 'woocommerce-wholesale-order-form'),
                    'reorder_column' => __("Re-order columns in this table.", 'woocommerce-wholesale-order-form'),
                    'reorder_item'   => __("Re-order item in this row.", 'woocommerce-wholesale-order-form'),
                    'delete_msg'     => __("Are you sure to delete this", 'woocommerce-wholesale-order-form'),
                    'yes_str'        => __("Yes", 'woocommerce-wholesale-order-form'),
                    'no_str'         => __("No", 'woocommerce-wholesale-order-form'),
                ),
                // ManageRow Component
                'manage_row'                  => array(
                    'settings_for' => __("Settings for this row", 'woocommerce-wholesale-order-form'),
                    'reorder_row'  => __("Re-order between rows.", 'woocommerce-wholesale-order-form'),
                    'delete_msg'   => __("Are you sure to delete this row?", 'woocommerce-wholesale-order-form'),
                    'yes_str'      => __("Yes", 'woocommerce-wholesale-order-form'),
                    'no_str'       => __("No", 'woocommerce-wholesale-order-form'),
                ),
                // SectionHeaderFooter Component
                'section_header_footer'       => array(
                    'one_column'       => __("1 Column", 'woocommerce-wholesale-order-form'),
                    'two_column'       => __("2 Column", 'woocommerce-wholesale-order-form'),
                    'three_column'     => __("3 Column", 'woocommerce-wholesale-order-form'),
                    'four_column'      => __("4 Column", 'woocommerce-wholesale-order-form'),
                    'new_section'      => __("Add New Section", 'woocommerce-wholesale-order-form'),
                    'select_structure' => __("Select Your Structure", 'woocommerce-wholesale-order-form'),
                ),
                // ProductCount Component
                'product_count'               => array(
                    'products_str' => __("Product(s)", 'woocommerce-wholesale-order-form'),
                ),
                // HeaderFooterElements
                'header_footer_elements'      => array(
                    'add_selected_to_cart' => array(
                        'button_text' => __("Add Selected Products To Cart", 'woocommerce-wholesale-order-form'),
                    ),
                    'cart_subtotal'        => array(
                        'subtotal_pretext' => __("Subtotal:", 'woocommerce-wholesale-order-form'),
                        'empty_cart_text'  => __("Empty Cart", 'woocommerce-wholesale-order-form'),
                    ),
                    'category_filter'      => array(
                        'placeholder_text' => __("Select Category", 'woocommerce-wholesale-order-form'),
                    ),
                    'search_button'        => array(
                        'search_button_text' => __("Search Products", 'woocommerce-wholesale-order-form'),
                        'clear_button_text'  => __("Clear Filters", 'woocommerce-wholesale-order-form'),
                    ),
                    'search_input'         => array(
                        'search_button_text' => __("Search Products", 'woocommerce-wholesale-order-form'),
                    ),
                ),
                // TableElements
                'table_elements'              => array(
                    'add_to_cart_button' => array(
                        'column_heading' => __("Add To Cart Button", 'woocommerce-wholesale-order-form'),
                        'button_text'    => __("Add To Cart", 'woocommerce-wholesale-order-form'),
                    ),
                    'in_stock_amount'    => array(
                        'column_heading'      => __("In Stock", 'woocommerce-wholesale-order-form'),
                        'out_of_stock'        => __("Out of Stock", 'woocommerce-wholesale-order-form'),
                        'avaialble_backorder' => __("Available on backorder", 'woocommerce-wholesale-order-form'),
                        'in_stock'            => __("in stock", 'woocommerce-wholesale-order-form'),
                    ),
                    'price'              => array(
                        'column_heading' => __("Price", 'woocommerce-wholesale-order-form'),
                    ),
                    'product_image'      => array(
                        'column_heading' => __("Image", 'woocommerce-wholesale-order-form'),
                    ),
                    'product_meta'       => array(
                        'column_heading' => __("Product Meta", 'woocommerce-wholesale-order-form'),
                    ),
                    'product_name'       => array(
                        'column_heading' => __("Product Name", 'woocommerce-wholesale-order-form'),
                    ),
                    'product_sku'        => array(
                        'column_heading' => __("SKU", 'woocommerce-wholesale-order-form'),
                    ),
                    'quantity_input'     => array(
                        'column_heading' => __("Quantity", 'woocommerce-wholesale-order-form'),
                        'out_of_stock'   => __("Out of Stock", 'woocommerce-wholesale-order-form'),
                    ),
                    'short_description'  => array(
                        'column_heading' => __("Description", 'woocommerce-wholesale-order-form'),
                    ),
                    'variation_dropdown' => array(
                        'column_heading' => __("Options", 'woocommerce-wholesale-order-form'),
                        'combo'          => array(
                            'placeholder'       => __("Select Variation", 'woocommerce-wholesale-order-form'),
                            'not_found_content' => __("No results found", 'woocommerce-wholesale-order-form'),
                            'loading_text'      => __("Loading...", 'woocommerce-wholesale-order-form'),
                        ),
                        'standard'       => array(
                            'placeholder'       => __("Choose an option", 'woocommerce-wholesale-order-form'),
                            'not_found_content' => __("No results found", 'woocommerce-wholesale-order-form'),
                        ),
                    ),
                ),
                // StylingAndOptionControls Component
                'styling_and_option_controls' => array(
                    'setting_for'          => __("Settings for", 'woocommerce-wholesale-order-form'),
                    'setting_for_this_row' => __("Settings for this row", 'woocommerce-wholesale-order-form'),
                    'styles'               => __("Styles", 'woocommerce-wholesale-order-form'),
                    'options'              => __("Options", 'woocommerce-wholesale-order-form'),

                    // ShowOptions Component
                    'show_options'         => array(
                        'no_options'               => __("No Options", 'woocommerce-wholesale-order-form'),
                        'button_text'              => array(
                            'search_products' => __("Search Products", 'woocommerce-wholesale-order-form'),
                            'label1'          => __('Button Text', 'woocommerce-wholesale-order-form'),
                            'label2'          => __('Search Button Text', 'woocommerce-wholesale-order-form'),
                        ),
                        'clear_button_text'        => array(
                            'text'  => __("Clear Filters", 'woocommerce-wholesale-order-form'),
                            'label' => __("Clear Button Text", 'woocommerce-wholesale-order-form'),
                        ),
                        'column_heading_text'      => array(
                            'label' => __("Column Heading Text", 'woocommerce-wholesale-order-form'),
                        ),
                        'decimal_quantity'         => array(
                            'label'       => __("Decimal Quantity", 'woocommerce-wholesale-order-form'),
                            'description' => __("Allow decimal values in the quantity input boxes.", 'woocommerce-wholesale-order-form'),
                        ),
                        'default_category'         => array(
                            'label' => __("Default Category", 'woocommerce-wholesale-order-form'),
                        ),
                        'empty_cart_text'          => array(
                            'label'           => __("Empty Cart Text", 'woocommerce-wholesale-order-form'),
                            'empty_cart_text' => __("Empty Cart", 'woocommerce-wholesale-order-form'),
                        ),
                        'excluded_categories'      => array(
                            'label' => __("Excluded Category", 'woocommerce-wholesale-order-form'),
                        ),
                        'image_click_action'       => array(
                            'label'                       => __("Click Action", 'woocommerce-wholesale-order-form'),
                            'show_product_details_propup' => __("Show Product Details Popup", 'woocommerce-wholesale-order-form'),
                            'navigate_to_product_page'    => __("Navigate To Single Product Page", 'woocommerce-wholesale-order-form'),
                            'show_image_popup'            => __("Show Image Popup", 'woocommerce-wholesale-order-form'),
                        ),
                        'image_size'               => array(
                            'label'       => __('Image Size', 'woocommerce-wholesale-order-form'),
                            'width_text'  => __('Width', 'woocommerce-wholesale-order-form'),
                            'height_text' => __('Height', 'woocommerce-wholesale-order-form'),
                        ),
                        'included_categories'      => array(
                            'label' => __('Included Category', 'woocommerce-wholesale-order-form'),
                        ),
                        'max_characters'           => array(
                            'label'       => __('Max Characters', 'woocommerce-wholesale-order-form'),
                            'description' => __('Leave blank for unlimited', 'woocommerce-wholesale-order-form'),
                        ),
                        'out_of_stock'             => array(
                            'label'         => __('Out Of Stock Text', 'woocommerce-wholesale-order-form'),
                            'default_value' => __('Out Of Stock', 'woocommerce-wholesale-order-form'),
                        ),
                        'placeholder'              => array(
                            'label' => __('Placeholder', 'woocommerce-wholesale-order-form'),
                        ),
                        'pre_text'                 => array(
                            'label' => __('Pre-Text', 'woocommerce-wholesale-order-form'),
                        ),
                        'product_meta'             => array(
                            'label'          => __('Product Meta', 'woocommerce-wholesale-order-form'),
                            'description'    => __('Note: Meta key with prefix of underscore (_) will not work.', 'woocommerce-wholesale-order-form'),
                            'click_update'   => __('*Click "Update" to save your changes.', 'woocommerce-wholesale-order-form'),
                            'add_row'        => __('Add Row', 'woocommerce-wholesale-order-form'),
                            'the_name'       => __('The name', 'woocommerce-wholesale-order-form'),
                            'sure_to_delete' => __('Sure to delete?', 'woocommerce-wholesale-order-form'),
                            'is_required'    => __('is required', 'woocommerce-wholesale-order-form'),
                            'name'           => __('Name', 'woocommerce-wholesale-order-form'),
                            'meta_key'       => __('Meta Key', 'woocommerce-wholesale-order-form'),
                            'meta_key2'      => __('meta_key', 'woocommerce-wholesale-order-form'),
                            'action'         => __('Action', 'woocommerce-wholesale-order-form'),
                            'ok_text'        => __('OK', 'woocommerce-wholesale-order-form'),
                            'cancel_text'    => __('Cancel', 'woocommerce-wholesale-order-form'),
                        ),
                        'products_per_page'        => array(
                            'label' => __('Products Per Page', 'woocommerce-wholesale-order-form'),
                        ),
                        'quantity_restriction'     => array(
                            'label'           => __('Quantity Restriction', 'woocommerce-wholesale-order-form'),
                            'tooltip_title'   => __("Click For More Info", 'woocommerce-wholesale-order-form'),
                            'popover_title'   => __("Quantity Restriction (WooCommerce Wholesale Prices Premium feature)", 'woocommerce-wholesale-order-form'),
                            'popover_content' => __('<p>
                                                        When <b>Wholesale Minimum Order Quantity</b> is set, with or
                                                        without setting <b>Wholesale Order Quantity Step</b>, only
                                                        allow adding to cart when the quantity complies with the
                                                        minimum quantity (and with the step increments if set).
                                                    </p>
                                                    <p>
                                                        <b>Example:</b> <br />
                                                        Min = 5, valid qty are 5 and up. Invalid qty are below 5.
                                                        <br />
                                                        Min = 5, Step = 3, valid qty are 5, 8, 11 and so on. Invalid
                                                        qty are 6, 7, 9, 10 and so on.
                                                    </p>', 'woocommerce-wholesale-order-form'),
                        ),
                        'show_clear_button'        => array(
                            'label'       => __('Show Clear Button', 'woocommerce-wholesale-order-form'),
                            'description' => __('Show a button to clear filters next to the search button', 'woocommerce-wholesale-order-form'),
                        ),
                        'show_form_footer'         => array(
                            'label'       => __('Show Form Footer', 'woocommerce-wholesale-order-form'),
                            'description' => __('Show the order form footer.', 'woocommerce-wholesale-order-form'),
                        ),
                        'show_form_header'         => array(
                            'label' => __('Show Form Header', 'woocommerce-wholesale-order-form'),
                        ),
                        'show_min_req_notice'      => array(
                            'label'       => __('Show Min Requirement Notices', 'woocommerce-wholesale-order-form'),
                            'description' => __('Show wholesale minimum requirement notices above the search form', 'woocommerce-wholesale-order-form'),
                        ),
                        'sku_search'               => array(
                            'label'       => __('SKU Search', 'woocommerce-wholesale-order-form'),
                            'description' => __('Allow searching by SKU', 'woocommerce-wholesale-order-form'),
                        ),
                        'smart_visibility'         => array(
                            'label'       => __('Smart Visibility', 'woocommerce-wholesale-order-form'),
                            'description' => __('Hide column when no variable products detected', 'woocommerce-wholesale-order-form'),
                        ),
                        'sortable'                 => array(
                            'label'       => __('Sortable', 'woocommerce-wholesale-order-form'),
                            'description' => __('Allow sorting the table by this column', 'woocommerce-wholesale-order-form'),
                        ),
                        'submit_on_change'         => array(
                            'label' => __('Submit On Change', 'woocommerce-wholesale-order-form'),
                        ),
                        'submit_on_enter'          => array(
                            'label' => __('Submit On Enter', 'woocommerce-wholesale-order-form'),
                        ),
                        'subtotal_suffix'          => array(
                            'label' => __('Subtotal Suffix', 'woocommerce-wholesale-order-form'),
                        ),
                        'tax_display'              => array(
                            'label'      => __('Tax Display', 'woocommerce-wholesale-order-form'),
                            'wc_default' => __('WooCommerce Default', 'woocommerce-wholesale-order-form'),
                            'incl'       => __('Including Tax', 'woocommerce-wholesale-order-form'),
                            'excl'       => __('Excluding Tax', 'woocommerce-wholesale-order-form'),
                        ),
                        'variation_selector_style' => array(
                            'label'           => __('Variation Selector Style', 'woocommerce-wholesale-order-form'),
                            'popover_title'   => __('Additional Note', 'woocommerce-wholesale-order-form'),
                            'popover_content' => __('<p>
                                                        <b>Standard</b> - Limitation is only up to 100 variations.
                                                    </p>
                                                    <p>
                                                        <b>Combo</b> - Recommended for product with large variations
                                                        (100 variations and up). Lazy load 20 variations on scroll
                                                        down.
                                                    </p>', 'woocommerce-wholesale-order-form'),
                            'tooltip_title'   => __("Click For More Info", 'woocommerce-wholesale-order-form'),
                            'combo'           => __("Combo (Less Clicks/Faster)", 'woocommerce-wholesale-order-form'),
                            'standard'        => __("Standard (One Box Per Attribute)", 'woocommerce-wholesale-order-form'),
                        ),
                        'wholesale_quantity_based' => array(
                            'label'       => __('Wholesale Quantity Based Pricing Tables', 'woocommerce-wholesale-order-form'),
                            'description' => __('Show Wholesale quantity based pricing tables', 'woocommerce-wholesale-order-form'),
                        ),
                    ),
                    'show_styles'          => array(
                        'no_styles'         => __("No Styles.", 'woocommerce-wholesale-order-form'),
                        'alignment'         => array(
                            'table_alignment'   => __("Table Alignment", 'woocommerce-wholesale-order-form'),
                            'element_alignment' => __("Element Alignment", 'woocommerce-wholesale-order-form'),
                            'cell_alignment'    => __("Cell Alignment", 'woocommerce-wholesale-order-form'),
                            'left'              => __("Left", 'woocommerce-wholesale-order-form'),
                            'center'            => __("Center", 'woocommerce-wholesale-order-form'),
                            'right'             => __("Right", 'woocommerce-wholesale-order-form'),
                        ),
                        'button_color'      => array(
                            'label' => __("Button Color", 'woocommerce-wholesale-order-form'),
                        ),
                        'button_text_color' => array(
                            'label' => __("Button Text Color", 'woocommerce-wholesale-order-form'),
                        ),
                        'font_size'         => array(
                            'label'   => __("Font Size", 'woocommerce-wholesale-order-form'),
                            'auto'    => __("Auto", 'woocommerce-wholesale-order-form'),
                            'percent' => __("%", 'woocommerce-wholesale-order-form'),
                            'px'      => __("px", 'woocommerce-wholesale-order-form'),
                        ),
                        'margin'            => array(
                            'label'      => __("Margin", 'woocommerce-wholesale-order-form'),
                            'linked'     => __("Linked", 'woocommerce-wholesale-order-form'),
                            'not_linked' => __("Not Linked", 'woocommerce-wholesale-order-form'),
                        ),
                        'padding'           => array(
                            'label'      => __("Padding", 'woocommerce-wholesale-order-form'),
                            'linked'     => __("Linked", 'woocommerce-wholesale-order-form'),
                            'not_linked' => __("Not Linked", 'woocommerce-wholesale-order-form'),
                        ),
                        'width'             => array(
                            'table_width'   => __("Table Width", 'woocommerce-wholesale-order-form'),
                            'box_width'     => __("Box Width", 'woocommerce-wholesale-order-form'),
                            'element_width' => __("Element Width", 'woocommerce-wholesale-order-form'),
                            'full_width'    => __("Full Width", 'woocommerce-wholesale-order-form'),
                            'auto'          => __("Auto", 'woocommerce-wholesale-order-form'),
                            'percent'       => __("%", 'woocommerce-wholesale-order-form'),
                            'px'            => __("px", 'woocommerce-wholesale-order-form'),
                        ),
                    ),
                ),
                'drag_and_drop_labels'        => $this->drag_and_drop_labels(),
            );

            return apply_filters('wwof_v2_backend_strings', $backend_strings);
        }

        /**
         * Drag and Drop labels.
         *
         * @since 1.21
         * @access public
         * @return array
         */
        private function drag_and_drop_labels()
        {
            return array(
                // Header & Footer
                "search-input"                => __("Search Input", 'woocommerce-wholesale-order-form'),
                "category-filter"             => __("Category Filter", 'woocommerce-wholesale-order-form'),
                "add-selected-to-cart-button" => __("Add Selected To Cart Button", 'woocommerce-wholesale-order-form'),
                "cart-subtotal"               => __("Cart Subtotal", 'woocommerce-wholesale-order-form'),
                "product-count"               => __("Product Count", 'woocommerce-wholesale-order-form'),
                "pagination"                  => __("Pagination", 'woocommerce-wholesale-order-form'),
                "search-button"               => __("Search & Clear Buttons", 'woocommerce-wholesale-order-form'),
                // Table Elements
                "product-image"               => __("Product Image", 'woocommerce-wholesale-order-form'),
                "product-name"                => __("Product Name", 'woocommerce-wholesale-order-form'),
                "sku"                         => __("SKU", 'woocommerce-wholesale-order-form'),
                "in-stock-amount"             => __("In Stock Amount", 'woocommerce-wholesale-order-form'),
                "price"                       => __("Price", 'woocommerce-wholesale-order-form'),
                "quantity-input"              => __("Quantity Input", 'woocommerce-wholesale-order-form'),
                "add-to-cart-button"          => __("Add To Cart Button", 'woocommerce-wholesale-order-form'),
                "variation-dropdown"          => __("Variation Dropdown", 'woocommerce-wholesale-order-form'),
                "short-description"           => __("Short Description", 'woocommerce-wholesale-order-form'),
                "add-to-cart-checkbox"        => __("Add To Cart Checkbox", 'woocommerce-wholesale-order-form'),
                "product-meta"                => __("Product Meta", 'woocommerce-wholesale-order-form'),
            );
        }

        /**
         * Get frontend app strings.
         *
         * @since 1.21
         * @access public
         * @return array
         */
        private function frontend_strings()
        {
            $frontend_strings = array(
                // App component
                'app'                                  => array(
                    'draft_tooltip' => __('This Order Form is in "Draft" status. This form will only be visible only for admin user.', 'woocommerce-wholesale-order-form'),
                ),
                // FormHeaderFooter
                'add_selected_products_to_cart_button' => array(
                    'button_text'            => __("Add Selected Products To Cart", 'woocommerce-wholesale-order-form'),
                    'zero_qty_error'         => __("Please enter a valid value. Quantity must not be 0.", 'woocommerce-wholesale-order-form'),
                    'minimum_error'          => __("The entered quantity is lower than the allowed minimum", 'woocommerce-wholesale-order-form'),
                    'step_error'             => __("Please enter a valid quantity. The two nearest valid quantity are", 'woocommerce-wholesale-order-form'),
                    'and'                    => __("and", 'woocommerce-wholesale-order-form'),
                    'no_variations_selected' => __("No variation(s) selected.", 'woocommerce-wholesale-order-form'),
                    'successfully_added'     => __("Succesfully Added", 'woocommerce-wholesale-order-form'),
                    'add_to_cart_failed'     => __("Add to Cart Failed", 'woocommerce-wholesale-order-form'),
                ),
                'cart_subtotal'                        => array(
                    'empty_cart' => __('Empty Cart.', 'woocommerce-wholesale-order-form'),
                ),
                'category_filter'                      => array(
                    'placeholder' => __('Select Category', 'woocommerce-wholesale-order-form'),
                ),
                'product_count'                        => array(
                    'products' => __('Product(s)', 'woocommerce-wholesale-order-form'),
                ),
                'search_button'                        => array(
                    'search_btn' => __('Search Products', 'woocommerce-wholesale-order-form'),
                    'clear_btn'  => __("Clear Filters", 'woocommerce-wholesale-order-form'),
                ),
                'search_input'                         => array(
                    'placeholder' => __('Search Products', 'woocommerce-wholesale-order-form'),
                ),
                // FormTable
                'table_column_headers'                 => array(
                    "product-image"        => __("Image", 'woocommerce-wholesale-order-form'),
                    "product-name"         => __("Product Name", 'woocommerce-wholesale-order-form'),
                    "sku"                  => __("SKU", 'woocommerce-wholesale-order-form'),
                    "in-stock-amount"      => __("In Stock", 'woocommerce-wholesale-order-form'),
                    "price"                => __("Price", 'woocommerce-wholesale-order-form'),
                    "quantity-input"       => __("Quantity", 'woocommerce-wholesale-order-form'),
                    "add-to-cart-button"   => __("Add To Cart", 'woocommerce-wholesale-order-form'),
                    "variation-dropdown"   => __("Options", 'woocommerce-wholesale-order-form'),
                    "short-description"    => __("Description", 'woocommerce-wholesale-order-form'),
                    "add-to-cart-checkbox" => __("Add To Cart Checkbox", 'woocommerce-wholesale-order-form'),
                    "product-meta"         => __("Product Meta", 'woocommerce-wholesale-order-form'),
                ),
                'add_to_cart_button'                   => array(
                    'button_text'          => __("Add To Cart", 'woocommerce-wholesale-order-form'),
                    'quantity_restriction' => __('Quantity Restriction', 'woocommerce-wholesale-order-form'),
                ),
                'in_stock_amount'                      => array(
                    "out_of_stock" => __("Out of Stock", 'woocommerce-wholesale-order-form'),
                    "backorder"    => __("Available on backorder", 'woocommerce-wholesale-order-form'),
                    "in_stock"     => __("In stock", 'woocommerce-wholesale-order-form'),
                ),
                'quantity_input'                       => array(
                    'close_popover'        => __("Close", 'woocommerce-wholesale-order-form'),
                    'out_of_stock'         => __("Out of Stock", 'woocommerce-wholesale-order-form'),
                    'quantity_restriction' => __('Quantity Restriction', 'woocommerce-wholesale-order-form'),
                ),
                'combo'                                => array(
                    'placeholder'  => __("Select Variation", 'woocommerce-wholesale-order-form'),
                    'no_results'   => __("No results found", 'woocommerce-wholesale-order-form'),
                    'loading_text' => __("Loading...", 'woocommerce-wholesale-order-form'),
                ),
                'standard'                             => array(
                    'placeholder' => __("Choose an option", 'woocommerce-wholesale-order-form'),
                    'no_results'  => __("No results found", 'woocommerce-wholesale-order-form'),
                ),
                'display_qty_based_table'              => array(
                    'popover_title' => __('Wholesale Quantity Based Pricing', 'woocommerce-wholesale-order-form'),
                    'tooltip'       => __('Quantity Based Wholesale Pricing', 'woocommerce-wholesale-order-form'),
                ),
                'product_modal'                        => array(
                    'unavailable' => __('This product is currently out of stock and unavailable.', 'woocommerce-wholesale-order-form'),
                    'category'    => __('Category', 'woocommerce-wholesale-order-form'),
                    'add_to_cart' => array(
                        'add_to_cart_text' => __('Add To Cart', 'woocommerce-wholesale-order-form'),
                        'out_of_stock'     => __('Out of Stock', 'woocommerce-wholesale-order-form'),
                        'close'            => __('Close', 'woocommerce-wholesale-order-form'),
                        'backorder'        => __("Available on backorder", 'woocommerce-wholesale-order-form'),
                        'in_stock'         => __("in stock", 'woocommerce-wholesale-order-form'),
                    ),
                ),
                'responsive_table'                     => array(
                    'select_all'      => __('Select All Products', 'woocommerce-wholesale-order-form'),
                    'sortable_column' => array(
                        'asc'        => __('Ascending', 'woocommerce-wholesale-order-form'),
                        'desc'       => __('Descending', 'woocommerce-wholesale-order-form'),
                        'sort_by'    => __('Sort By', 'woocommerce-wholesale-order-form'),
                        'sort_order' => __('Sort Order', 'woocommerce-wholesale-order-form'),
                        'no_results' => __('No results found', 'woocommerce-wholesale-order-form'),

                    ),
                ),
                // addProductToCart helper
                'add_product_to_cart'                  => array(
                    'add_to_cart_failed' => __("Add to Cart Failed", 'woocommerce-wholesale-order-form'),
                    'select_variation'   => __("Please select a variation(s).", 'woocommerce-wholesale-order-form'),
                    'zero_qty_error'     => __("Please enter a valid value. Quantity must not be 0.", 'woocommerce-wholesale-order-form'),
                    'min_qty_error'      => __("Please enter a valid value. The entered value is lower than the allowed minimum", 'woocommerce-wholesale-order-form'),
                    'step_error'         => __("Please enter a valid value. The two nearest valid values are", 'woocommerce-wholesale-order-form'),
                    'and'                => __("and", 'woocommerce-wholesale-order-form'),
                    'successfully_added' => __("Succesfully Added:", 'woocommerce-wholesale-order-form'),
                    'view_cart'          => __("View Cart", 'woocommerce-wholesale-order-form'),
                    'empty_error'        => __("Quantity must not be empty.", 'woocommerce-wholesale-order-form'),
                    'cannot_add_to_cart' => __("Unable to add to cart.", 'woocommerce-wholesale-order-form'),
                    'unavailable'        => __("Unavailable", 'woocommerce-wholesale-order-form'),
                ),
                //quantityRestrictionPopover helper
                'quantity_restriction_popover'         => array(
                    'quantity_of'        => __("Quantity of", 'woocommerce-wholesale-order-form'),
                    'is_invalid'         => __("is invalid. Please provide a quantity that is minimum of", 'woocommerce-wholesale-order-form'),
                    'increments_of'      => __("and increments of", 'woocommerce-wholesale-order-form'),
                    'two_nearest_values' => __("The two nearest valid values are", 'woocommerce-wholesale-order-form'),
                    'and'                => __("and", 'woocommerce-wholesale-order-form'),
                    'not_within_range'   => __("is not within range of suggested quantity of minimum of", 'woocommerce-wholesale-order-form'),
                ),
                // Form Table Sort
                'form_table_sort_text'                 => array(
                    'sort_desc'   => __("Click to sort descending", 'woocommerce-wholesale-order-form'),
                    'sort_asc'    => __("Click to sort ascending", 'woocommerce-wholesale-order-form'),
                    'sort_cancel' => __("Click to cancel sorting", 'woocommerce-wholesale-order-form'),
                ),

            );

            return apply_filters('wwof_v2_frontend_strings', $frontend_strings);

        }

    }

}

return new Order_Form_I18n_API_Controller();
