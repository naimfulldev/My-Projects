<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('Order_Form_API_Controller')) {

    /**
     * Model that houses the logic of Order Form REST API.
     *
     * @since 1.16
     */
    class Order_Form_API_Controller extends WP_REST_Controller
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
        protected $rest_base = 'order_forms';

        /**
         * Post type.
         *
         * @var string
         */
        protected $post_type = 'order_form';

        /**
         * Wholesale role.
         *
         * @var string
         */
        protected $wholesale_role = '';

        /**
         * WWOF_API_Products_Controller constructor.
         *
         * @since 1.16
         * @access public
         */
        public function __construct()
        {

            // Fires when preparing to serve an API request.
            add_action("rest_api_init", array($this, "register_routes"));

        }

        /**
         * Register cpt REST API routes and endpoints.
         *
         * @since 1.16
         * @access public
         *
         * @return void
         */
        public function register_routes()
        {

            // WWPP Minimum Order Requirements
            register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/get_wholesale_order_requirements',
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array($this, 'get_wholesale_order_requirements'),
                        'permission_callback' => array($this, 'permissions_check'),
                    ),
                    'schema' => array($this, 'get_public_item_schema'),
                )
            );

            // Cart Total Excl/Incl Tax
            register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/get_cart_subtotal',
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array($this, 'get_cart_subtotal'),
                        'permission_callback' => array($this, 'permissions_check'),
                    ),
                    'schema' => array($this, 'get_public_item_schema'),
                )
            );

            // Get Items / Create Items
            register_rest_route(
                $this->namespace,
                '/' . $this->rest_base,
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array($this, 'get_items'),
                        'args' => $this->get_collection_params(),
                        'permission_callback' => array($this, 'permissions_check'),
                    ),
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'create_item'),
                        'permission_callback' => array($this, 'permissions_check'),
                    ),
                    'schema' => array($this, 'get_public_item_schema'),
                )
            );

            // Get Item, Update, Delete
            register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<id>[\d]+)',
                array(
                    'args' => array(
                        'id' => array(
                            'description' => __('Unique identifier for the object.'),
                            'type' => 'integer',
                        ),
                    ),
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array($this, 'get_item'),
                        'permission_callback' => array($this, 'permissions_check'),
                    ),
                    array(
                        'methods' => WP_REST_Server::EDITABLE,
                        'callback' => array($this, 'update_item'),
                        'permission_callback' => array($this, 'permissions_check'),
                    ),
                    array(
                        'methods' => WP_REST_Server::DELETABLE,
                        'callback' => array($this, 'delete_item'),
                        'permission_callback' => array($this, 'permissions_check'),
                        'args' => array(
                            'force' => array(
                                'type' => 'boolean',
                                'default' => false,
                                'description' => __('Whether to bypass trash and force deletion.'),
                            ),
                        ),
                    ),
                    'schema' => array($this, 'get_public_item_schema'),
                )
            );

        }

        /**
         * Handles setting minimum order requirement message.
         * @param  WP_REST_Request
         * @return WP_Error|WP_REST_Response
         *
         * @since 1.17
         */
        public function get_cart_subtotal($request)
        {

            global $wc_wholesale_order_form;

            return rest_ensure_response(
                array('status' => 'success', 'data' => $wc_wholesale_order_form->_wwof_api_request->get_cart_subtotal())
            );

        }

        /**
         * Handles setting minimum order requirement message.
         * @param  WP_REST_Request
         * @return WP_Error|WP_REST_Response
         *
         * @since 1.17
         */
        public function get_wholesale_order_requirements($request)
        {

            global $wc_wholesale_order_form;

            add_filter('wwof_min_requirement_current_user_id', function ($user_id) {
                $request_uid = isset($_REQUEST['uid']) ? absint($_REQUEST['uid']) : '';
                return $request_uid ? $request_uid : $user_id;
            }, 10, 1);

            $wholesale_role = sanitize_text_field($request['wholesale_role']);
            $min_req_data = $wc_wholesale_order_form->_wwof_wwp_wholesale_prices->get_min_requirement_data($wholesale_role);
            $message = '';
            extract($min_req_data);

            if ((!empty($min_order_quantity) || !empty($min_order_price))) {
                $message = $wc_wholesale_order_form->_wwof_wwp_wholesale_prices->wwof_get_wholesale_price_requirement_message($min_order_quantity, $min_order_price, $min_req_logic);
            }

            return rest_ensure_response(
                array('status' => 'success', 'message' => apply_filters('wwof_v2_wholesale_order_requirements_message', $message, $min_order_quantity, $min_order_price, $min_req_logic))
            );

        }

        /**
         * Check whether a given request has permission to edit and delete order forms.
         *
         * @param  WP_REST_Request
         * @return WP_Error|boolean
         */
        public function permissions_check($request)
        {

            // Bypass user checking and for testing API via postman
            if (defined('WWOF_DEV') && WWOF_DEV) {
                return true;
            }

            // Make GET request public
            if ($request->get_method() === 'GET') {
                return true;
            }

            // If not admin then return error
            if (!user_can(get_current_user_id(), 'manage_options')) {
                return new WP_Error('rest_cannot_view', __('Invalid Request.', 'woocommerce-wholesale-order-form'), array('status' => rest_authorization_required_code()));
            }

            return true;

        }

        /**
         * Get locations of the order form. Check if its added either in page or post type.
         *
         * @since 1.16
         * @access public
         *
         * @return void
         */
        public function get_locations($post)
        {

            global $wpdb;
            $shortcode = esc_sql($post->post_content);

            $sql = "SELECT p.ID, p.post_title FROM $wpdb->posts p WHERE p.post_content LIKE '%$shortcode%' AND p.post_type IN ( 'page' , 'post' )";
            $results = $wpdb->get_results($sql);
            $locations = array();

            if (!empty($results)) {
                foreach ($results as $result) {
                    $locations[] = array(
                        'ID' => $result->ID,
                        'post_title' => $result->post_title,
                        'permalink' => get_permalink($result->ID),
                    );
                }
            }

            return !empty($locations) ? $locations : array();

        }

        /**
         * Get all order forms.
         *
         * @since 1.16
         * @access public
         *
         * @param WP_REST_Request $request Full details about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_items($request)
        {

            // Retrieve the list of registered collection query parameters.
            $registered = $this->get_collection_params();
            $args = array();

            /*
             * This array defines mappings between public API query parameters whose
             * values are accepted as-passed, and their internal WP_Query parameter
             * name equivalents (some are the same). Only values which are also
             * present in $registered will be set.
             */
            $parameter_mappings = array(
                'author' => 'author__in',
                'author_exclude' => 'author__not_in',
                'exclude' => 'post__not_in',
                'include' => 'post__in',
                'menu_order' => 'menu_order',
                'offset' => 'offset',
                'order' => 'order',
                'orderby' => 'orderby',
                'page' => 'paged',
                'parent' => 'post_parent__in',
                'parent_exclude' => 'post_parent__not_in',
                'search' => 's',
                'slug' => 'post_name__in',
                'status' => 'post_status',
            );

            /*
             * For each known parameter which is both registered and present in the request,
             * set the parameter's value on the query $args.
             */
            foreach ($parameter_mappings as $api_param => $wp_param) {
                if (isset($registered[$api_param], $request[$api_param])) {
                    $args[$wp_param] = $request[$api_param];
                }
            }

            // Check for & assign any parameters which require special handling or setting.
            $args['date_query'] = array();

            // Set before into date query. Date query must be specified as an array of an array.
            if (isset($registered['before'], $request['before'])) {
                $args['date_query'][0]['before'] = $request['before'];
            }

            // Set after into date query. Date query must be specified as an array of an array.
            if (isset($registered['after'], $request['after'])) {
                $args['date_query'][0]['after'] = $request['after'];
            }

            // Ensure our per_page parameter overrides any provided posts_per_page filter.
            if (isset($registered['per_page'])) {
                $args['posts_per_page'] = $request['per_page'];
            }

            // Force the post_type argument, since it's not a user input variable.
            $args['post_type'] = $this->post_type;

            /**
             * Filters the query arguments for a request.
             *
             * Enables adding extra arguments or setting defaults for a post collection request.
             *
             * @since 4.7.0
             *
             * @link https://developer.wordpress.org/reference/classes/wp_query/
             *
             * @param array           $args    Key value array of query var to query value.
             * @param WP_REST_Request $request The request used.
             */
            $args = apply_filters("rest_{$this->post_type}_query", $args, $request);
            $query_args = $this->prepare_items_query($args, $request);

            $posts_query = new WP_Query();
            $query_result = $posts_query->query($query_args);

            // Allow access to all password protected posts if the context is edit.
            if ('edit' === $request['context']) {
                add_filter('post_password_required', '__return_false');
            }

            $posts = array();

            foreach ($query_result as $post) {

                $data = $this->prepare_item_for_response($post, $request);
                $posts[] = $this->prepare_response_for_collection($data);

            }

            $page = (int) $query_args['paged'];
            $total_posts = $posts_query->found_posts;

            if ($total_posts < 1) {
                // Out-of-bounds, run the query again without LIMIT for total count.
                unset($query_args['paged']);

                $count_query = new WP_Query();
                $count_query->query($query_args);
                $total_posts = $count_query->found_posts;
            }

            $max_pages = ceil($total_posts / (int) $posts_query->query_vars['posts_per_page']);

            if ($page > $max_pages && $total_posts > 0) {
                return new WP_Error(
                    'rest_post_invalid_page_number',
                    __('The page number requested is larger than the number of pages available.'),
                    array('status' => 400)
                );
            }

            $response = rest_ensure_response($posts);

            $response->header('X-WP-Total', (int) $total_posts);
            $response->header('X-WP-TotalPages', (int) $max_pages);

            $request_params = $request->get_query_params();
            $base = add_query_arg(urlencode_deep($request_params), rest_url(sprintf('%s/%s', $this->namespace, $this->rest_base)));

            if ($page > 1) {
                $prev_page = $page - 1;

                if ($prev_page > $max_pages) {
                    $prev_page = $max_pages;
                }

                $prev_link = add_query_arg('page', $prev_page, $base);
                $response->link_header('prev', $prev_link);
            }
            if ($max_pages > $page) {
                $next_page = $page + 1;
                $next_link = add_query_arg('page', $next_page, $base);

                $response->link_header('next', $next_link);
            }

            return $response;

        }

        /**
         * Create new order form.
         *
         * @since 1.16
         * @access public
         *
         * @param WP_REST_Request $request Full details about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function create_item($request)
        {

            if (!empty($request['id'])) {
                return new WP_Error('rest_post_exists', __('Cannot create existing post.', 'woocommerce-wholesale-order-form'), array('status' => 400));
            }

            $prepared_post = $this->prepare_item_for_database($request);

            if (is_wp_error($prepared_post)) {
                return $prepared_post;
            }

            $prepared_post->post_type = $this->post_type;

            $post_id = wp_insert_post(wp_slash((array) $prepared_post), true);

            if (is_wp_error($post_id)) {

                if ('db_insert_error' === $post_id->get_error_code()) {
                    $post_id->add_data(array('status' => 500));
                } else {
                    $post_id->add_data(array('status' => 400));
                }

                return $post_id;

            }

            // Set Order Form Shortcode in post content
            wp_update_post(
                array(
                    'ID' => $post_id,
                    'post_content' => '[wwof_product_listing id="' . $post_id . '" beta="true"]',
                )
            );

            // Order Form Custom Table Columns
            if (isset($request['form_elements'])) {
                update_post_meta($post_id, 'form_elements', $request['form_elements']);
            }

            if (isset($request['editor_area'])) {
                update_post_meta($post_id, 'editor_area', $request['editor_area']);
            }

            if (isset($request['styles'])) {
                update_post_meta($post_id, 'styles', $request['styles']);
            }

            if (isset($request['settings'])) {
                update_post_meta($post_id, 'settings', $request['settings']);
            }

            // Save Version of WWOF in meta
            global $wc_wholesale_order_form;
            update_post_meta($post_id, '_wwof_version', $wc_wholesale_order_form::VERSION);

            $post = get_post($post_id);

            /**
             * Fires after a single post is created or updated via the REST API.
             *
             * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
             *
             * @since 1.0.0
             *
             * @param WP_Post         $post     Inserted or updated post object.
             * @param WP_REST_Request $request  Request object.
             * @param bool            $creating True when creating a post, false when updating.
             */
            do_action("rest_insert_{$this->post_type}", $post, $request, true);

            $schema = $this->get_item_schema();

            $fields_update = $this->update_additional_fields_for_object($post, $request);

            if (is_wp_error($fields_update)) {
                return $fields_update;
            }

            $request->set_param('context', 'edit');

            /**
             * Fires after a single post is completely created or updated via the REST API.
             *
             * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
             *
             * @since 1.0.0
             *
             * @param WP_Post         $post     Inserted or updated post object.
             * @param WP_REST_Request $request  Request object.
             * @param bool            $creating True when creating a post, false when updating.
             */
            do_action("rest_after_insert_{$this->post_type}", $post, $request, true);

            $response = $this->prepare_item_for_response($post, $request);
            $response = rest_ensure_response($response);

            $response->set_status(201);
            $response->header('Location', rest_url(sprintf('%s/%s/%d', $this->namespace, $this->rest_base, $post_id)));

            return $response;

        }

        /**
         * Prepares a single post for create or update.
         *
         * @since 1.16
         * @access public
         *
         * @param WP_REST_Request $request Request object.
         * @return stdClass|WP_Error Post object or WP_Error.
         */
        protected function prepare_item_for_database($request)
        {

            $prepared_post = new stdClass();

            // Post ID.
            if (isset($request['id'])) {

                $existing_post = $this->get_post($request['id']);

                if (is_wp_error($existing_post)) {
                    return $existing_post;
                }

                $prepared_post->ID = $existing_post->ID;

            }

            $schema = $this->get_item_schema();

            // Post title.
            if (!empty($schema['properties']['title']) && isset($request['title'])) {
                if (is_string($request['title'])) {
                    $prepared_post->post_title = $request['title'];
                } elseif (!empty($request['title']['raw'])) {
                    $prepared_post->post_title = $request['title']['raw'];
                }
            }

            // Post content.
            if (!empty($schema['properties']['content']) && isset($request['content'])) {
                $prepared_post->post_content = "";
            }

            // Post type.
            if (empty($request['id'])) {
                // Creating new post, use default type for the controller.
                $prepared_post->post_type = $this->post_type;
            } else {
                // Updating a post, use previous type.
                $prepared_post->post_type = get_post_type($request['id']);
            }

            // Post status.
            if (!empty($schema['properties']['status']) && isset($request['status'])) {
                $prepared_post->post_status = $request['status'];
            }

            // Post date.
            if (!empty($schema['properties']['date']) && !empty($request['date'])) {
                $date_data = rest_get_date_with_gmt($request['date']);

                if (!empty($date_data)) {
                    list($prepared_post->post_date, $prepared_post->post_date_gmt) = $date_data;
                    $prepared_post->edit_date = true;
                }
            } elseif (!empty($schema['properties']['date_gmt']) && !empty($request['date_gmt'])) {
                $date_data = rest_get_date_with_gmt($request['date_gmt'], true);

                if (!empty($date_data)) {
                    list($prepared_post->post_date, $prepared_post->post_date_gmt) = $date_data;
                    $prepared_post->edit_date = true;
                }
            }

            // Post slug.
            if (!empty($schema['properties']['slug']) && isset($request['slug'])) {
                $prepared_post->post_name = $request['slug'];
            }

            // Author.
            if (!empty($schema['properties']['author']) && !empty($request['author'])) {
                $post_author = (int) $request['author'];

                if (get_current_user_id() !== $post_author) {
                    $user_obj = get_userdata($post_author);

                    if (!$user_obj) {
                        return new WP_Error('rest_invalid_author', __('Invalid author ID.'), array('status' => 400));
                    }
                }

                $prepared_post->post_author = $post_author;
            }

            /**
             * Filters a post before it is inserted via the REST API.
             *
             * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
             *
             * @since 4.7.0
             *
             * @param stdClass        $prepared_post An object representing a single post prepared
             *                                       for inserting or updating the database.
             * @param WP_REST_Request $request       Request object.
             */
            return apply_filters("rest_pre_insert_{$this->post_type}", $prepared_post, $request);

        }

        /**
         * Prepares a single post output for response.
         *
         * @since 1.16
         * @access public
         *
         * @param WP_Post         $post    Post object.
         * @param WP_REST_Request $request Request object.
         * @return WP_REST_Response Response object.
         */
        public function prepare_item_for_response($post, $request)
        {

            $GLOBALS['post'] = $post;

            setup_postdata($post);

            $fields = $this->get_fields_for_response($request);

            // Base fields for every post.
            $data = array();

            if (rest_is_field_included('id', $fields)) {
                $data['id'] = $post->ID;
            }

            if (rest_is_field_included('status', $fields)) {
                $data['status'] = $post->post_status;
            }

            if (rest_is_field_included('type', $fields)) {
                $data['type'] = $post->post_type;
            }

            if (rest_is_field_included('title', $fields)) {
                $data['title'] = $post->post_title;
            }

            if (rest_is_field_included('content', $fields)) {
                $data['content'] = $post->post_content;
            }

            if (rest_is_field_included('locations', $fields)) {
                $data['locations'] = $this->get_locations($post);
            }

            if (rest_is_field_included('form_elements', $fields)) {
                $data['meta']['form_elements'] = get_post_meta($post->ID, 'form_elements', true);
            }

            if (rest_is_field_included('editor_area', $fields)) {
                $data['meta']['editor_area'] = get_post_meta($post->ID, 'editor_area', true);
            }

            if (rest_is_field_included('styles', $fields)) {
                $data['meta']['styles'] = get_post_meta($post->ID, 'styles', true);
            }

            if (rest_is_field_included('settings', $fields)) {
                $data['meta']['settings'] = get_post_meta($post->ID, 'settings', true);
            }

            $context = !empty($request['context']) ? $request['context'] : 'view';

            $data = $this->add_additional_fields_to_object($data, $request);
            $data = $this->filter_response_by_context($data, $context);

            // Wrap the data in a response object.
            $response = rest_ensure_response($data);

            /**
             * Filters the post data for a response.
             *
             * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
             *
             * @since 1.0.0
             *
             * @param WP_REST_Response $response The response object.
             * @param WP_Post          $post     Post object.
             * @param WP_REST_Request  $request  Request object.
             */
            return apply_filters("rest_prepare_{$this->post_type}", $response, $post, $request);

        }

        /**
         * Retrieves the cpt schema, conforming to JSON Schema.
         *
         * @since 1.16
         * @access public
         *
         * @return array Item schema data.
         */
        public function get_item_schema()
        {

            if ($this->schema) {
                return $this->add_additional_fields_schema($this->schema);
            }

            $schema = array(
                '$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => $this->post_type,
                'type' => 'object',
                'properties' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the order form cpt object.', 'woocommerce-wholesale-order-form'),
                        'type' => 'integer',
                        'context' => array('view', 'edit', 'embed'),
                        'readonly' => true,
                    ),
                    'title' => array(
                        'description' => __('The title for the order form cpt object.', 'woocommerce-wholesale-order-form'),
                        'type' => 'string',
                        'context' => array('view', 'edit', 'embed'),
                    ),
                    'content' => array(
                        'description' => __('The description for the order form cpt object', 'woocommerce-wholesale-order-form'),
                        'type' => 'string',
                        'context' => array('view', 'edit', 'embed'),
                    ),
                    'locations' => array(
                        'description' => __('Shortcode locations.', 'woocommerce-wholesale-order-form'),
                        'type' => 'array',
                        'context' => array('view', 'edit', 'embed'),
                    ),
                    'status' => array(
                        'description' => __('A named status for the order form cpt object.', 'woocommerce-wholesale-order-form'),
                        'type' => 'string',
                        'enum' => array_keys(get_post_stati(array('internal' => false))),
                        'context' => array('view', 'edit'),
                    ),
                    'type' => array(
                        'description' => __('Type of Post for the order form cpt object.', 'woocommerce-wholesale-order-form'),
                        'type' => 'string',
                        'context' => array('view', 'edit', 'embed'),
                        'readonly' => true,
                    ),
                    'form_elements' => array(
                        'description' => __('Order Form Elements Side Panel.', 'woocommerce-wholesale-order-form'),
                        'type' => 'array',
                        'context' => array('view', 'edit', 'embed'),
                    ),
                    'editor_area' => array(
                        'description' => __('Order Form Editor Elements.', 'woocommerce-wholesale-order-form'),
                        'type' => 'array',
                        'context' => array('view', 'edit', 'embed'),
                    ),
                    'styles' => array(
                        'description' => __('Order Form Editor Styles.', 'woocommerce-wholesale-order-form'),
                        'type' => 'array',
                        'context' => array('view', 'edit', 'embed'),
                    ),
                    'settings' => array(
                        'description' => __('Order Form Settings.', 'woocommerce-wholesale-order-form'),
                        'type' => 'array',
                        'context' => array('view', 'edit', 'embed'),
                    ),
                ),
            );

            $this->schema = $schema;

            return $this->add_additional_fields_schema($this->schema);

        }

        /**
         * Determines the allowed query_vars for a get_items() response and prepares them for WP_Query.
         *
         * @since 1.16
         * @access public
         *
         * @param array           $prepared_args Optional. Prepared WP_Query arguments. Default empty array.
         * @param WP_REST_Request $request       Optional. Full details about the request.
         * @return array Items query arguments.
         */
        protected function prepare_items_query($prepared_args = array(), $request = null)
        {

            $query_args = array();

            foreach ($prepared_args as $key => $value) {
                /**
                 * Filters the query_vars used in get_items() for the constructed query.
                 *
                 * The dynamic portion of the hook name, `$key`, refers to the query_var key.
                 *
                 * @since 4.7.0
                 *
                 * @param string $value The query_var value.
                 */
                $query_args[$key] = apply_filters("rest_query_var-{$key}", $value); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
            }

            // Map to proper WP_Query orderby param.
            if (isset($query_args['orderby']) && isset($request['orderby'])) {
                $orderby_mappings = array(
                    'id' => 'ID',
                    'include' => 'post__in',
                    'slug' => 'post_name',
                    'include_slugs' => 'post_name__in',
                );

                if (isset($orderby_mappings[$request['orderby']])) {
                    $query_args['orderby'] = $orderby_mappings[$request['orderby']];
                }
            }

            return $query_args;

        }

        /**
         * Retrieves the query params for the posts collection.
         *
         * @since 1.16
         *
         * @return array Collection parameters.
         */
        public function get_collection_params()
        {

            $query_params = parent::get_collection_params();

            $query_params['context']['default'] = 'view';

            $query_params['after'] = array(
                'description' => __('Limit response to posts published after a given ISO8601 compliant date.'),
                'type' => 'string',
                'format' => 'date-time',
            );

            if (post_type_supports($this->post_type, 'author')) {
                $query_params['author'] = array(
                    'description' => __('Limit result set to posts assigned to specific authors.'),
                    'type' => 'array',
                    'items' => array(
                        'type' => 'integer',
                    ),
                    'default' => array(),
                );
                $query_params['author_exclude'] = array(
                    'description' => __('Ensure result set excludes posts assigned to specific authors.'),
                    'type' => 'array',
                    'items' => array(
                        'type' => 'integer',
                    ),
                    'default' => array(),
                );
            }

            $query_params['before'] = array(
                'description' => __('Limit response to posts published before a given ISO8601 compliant date.'),
                'type' => 'string',
                'format' => 'date-time',
            );

            $query_params['exclude'] = array(
                'description' => __('Ensure result set excludes specific IDs.'),
                'type' => 'array',
                'items' => array(
                    'type' => 'integer',
                ),
                'default' => array(),
            );

            $query_params['include'] = array(
                'description' => __('Limit result set to specific IDs.'),
                'type' => 'array',
                'items' => array(
                    'type' => 'integer',
                ),
                'default' => array(),
            );

            if ('page' === $this->post_type || post_type_supports($this->post_type, 'page-attributes')) {
                $query_params['menu_order'] = array(
                    'description' => __('Limit result set to posts with a specific menu_order value.'),
                    'type' => 'integer',
                );
            }

            $query_params['offset'] = array(
                'description' => __('Offset the result set by a specific number of items.'),
                'type' => 'integer',
            );

            $query_params['order'] = array(
                'description' => __('Order sort attribute ascending or descending.'),
                'type' => 'string',
                'default' => 'desc',
                'enum' => array('asc', 'desc'),
            );

            $query_params['orderby'] = array(
                'description' => __('Sort collection by object attribute.'),
                'type' => 'string',
                'default' => 'date',
                'enum' => array(
                    'author',
                    'date',
                    'id',
                    'include',
                    'modified',
                    'parent',
                    'relevance',
                    'slug',
                    'include_slugs',
                    'title',
                ),
            );

            if ('page' === $this->post_type || post_type_supports($this->post_type, 'page-attributes')) {
                $query_params['orderby']['enum'][] = 'menu_order';
            }

            $post_type = get_post_type_object($this->post_type);

            $query_params['slug'] = array(
                'description' => __('Limit result set to posts with one or more specific slugs.'),
                'type' => 'array',
                'items' => array(
                    'type' => 'string',
                ),
                'sanitize_callback' => 'wp_parse_slug_list',
            );

            $query_params['status'] = array(
                'default' => array('publish', 'draft'),
                'description' => __('Limit result set to posts assigned one or more statuses.'),
                'type' => 'array',
                'items' => array(
                    'enum' => array_merge(array_keys(get_post_stati()), array('any')),
                    'type' => 'string',
                ),
                'sanitize_callback' => array($this, 'sanitize_post_statuses'),
            );

            $taxonomies = wp_list_filter(get_object_taxonomies($this->post_type, 'objects'), array('show_in_rest' => true));

            if (!empty($taxonomies)) {
                $query_params['tax_relation'] = array(
                    'description' => __('Limit result set based on relationship between multiple taxonomies.'),
                    'type' => 'string',
                    'enum' => array('AND', 'OR'),
                );
            }

            foreach ($taxonomies as $taxonomy) {
                $base = !empty($taxonomy->rest_base) ? $taxonomy->rest_base : $taxonomy->name;

                $query_params[$base] = array(
                    /* translators: %s: Taxonomy name. */
                    'description' => sprintf(__('Limit result set to all items that have the specified term assigned in the %s taxonomy.'), $base),
                    'type' => 'array',
                    'items' => array(
                        'type' => 'integer',
                    ),
                    'default' => array(),
                );

                $query_params[$base . '_exclude'] = array(
                    /* translators: %s: Taxonomy name. */
                    'description' => sprintf(__('Limit result set to all items except those that have the specified term assigned in the %s taxonomy.'), $base),
                    'type' => 'array',
                    'items' => array(
                        'type' => 'integer',
                    ),
                    'default' => array(),
                );
            }

            if ('post' === $this->post_type) {
                $query_params['sticky'] = array(
                    'description' => __('Limit result set to items that are sticky.'),
                    'type' => 'boolean',
                );
            }

            /**
             * Filter collection parameters for the posts controller.
             *
             * The dynamic part of the filter `$this->post_type` refers to the post
             * type slug for the controller.
             *
             * This filter registers the collection parameter, but does not map the
             * collection parameter to an internal WP_Query parameter. Use the
             * `rest_{$this->post_type}_query` filter to set WP_Query parameters.
             *
             * @since 4.7.0
             *
             * @param array        $query_params JSON Schema-formatted collection parameters.
             * @param WP_Post_Type $post_type    Post type object.
             */
            return apply_filters("rest_{$this->post_type}_collection_params", $query_params, $post_type);

        }

        /**
         * Sanitizes and validates the list of post statuses, including whether the
         * user can query private statuses.
         *
         * @since 1.16
         *
         * @param string|array    $statuses  One or more post statuses.
         * @param WP_REST_Request $request   Full details about the request.
         * @param string          $parameter Additional parameter to pass to validation.
         * @return array|WP_Error A list of valid statuses, otherwise WP_Error object.
         */
        public function sanitize_post_statuses($statuses, $request, $parameter)
        {
            $statuses = wp_parse_slug_list($statuses);

            // The default status is different in WP_REST_Attachments_Controller.
            $attributes = $request->get_attributes();
            $default_status = $attributes['args']['status']['default'];

            foreach ($statuses as $status) {

                if (in_array($status, array('draft', 'publish'))) {
                    continue;
                }

                $post_type_obj = get_post_type_object($this->post_type);

                if ($post_type_obj && current_user_can($post_type_obj->cap->edit_posts) || 'private' === $status && current_user_can($post_type_obj->cap->read_private_posts)) {
                    $result = rest_validate_request_arg($status, $request, $parameter);
                    if (is_wp_error($result)) {
                        return $result;
                    }
                } else {
                    return new WP_Error(
                        'rest_forbidden_status',
                        __('Status is forbidden.'),
                        array('status' => rest_authorization_required_code())
                    );
                }
            }

            return $statuses;
        }

        /**
         * Deletes a single order form.
         *
         * @since 1.16
         *
         * @param WP_REST_Request $request Full details about the request.
         * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
         */
        public function delete_item($request)
        {

            // Multiple delete
            if ($request['id'] === 0 && !empty($request['post_ids'])) {

                $post_ids = $request['post_ids'];

                foreach ($post_ids as $post_id) {

                    $post = $this->get_post($post_id);

                    if (!is_wp_error($post)) {
                        wp_delete_post($post_id, true);
                    }

                }

                return rest_ensure_response(
                    array('message' => 'Successfully deleted order forms.', 'data' => $request['post_ids'])
                );

            }

            $post = $this->get_post($request['id']);
            if (is_wp_error($post)) {
                return $post;
            }

            $id = $post->ID;
            $force = (bool) $request['force'];

            $supports_trash = (EMPTY_TRASH_DAYS > 0);

            if ('attachment' === $post->post_type) {
                $supports_trash = $supports_trash && MEDIA_TRASH;
            }

            /**
             * Filters whether a post is trashable.
             *
             * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
             *
             * Pass false to disable Trash support for the post.
             *
             * @since 4.7.0
             *
             * @param bool    $supports_trash Whether the post type support trashing.
             * @param WP_Post $post           The Post object being considered for trashing support.
             */
            $supports_trash = apply_filters("rest_{$this->post_type}_trashable", $supports_trash, $post);

            $request->set_param('context', 'edit');

            // If we're forcing, then delete permanently.
            if ($force) {
                $previous = $this->prepare_item_for_response($post, $request);
                $result = wp_delete_post($id, true);
                $response = new WP_REST_Response();
                $response->set_data(
                    array(
                        'deleted' => true,
                        'previous' => $previous->get_data(),
                    )
                );
            } else {
                // If we don't support trashing for this type, error out.
                if (!$supports_trash) {
                    return new WP_Error(
                        'rest_trash_not_supported',
                        /* translators: %s: force=true */
                        sprintf(__("The post does not support trashing. Set '%s' to delete."), 'force=true'),
                        array('status' => 501)
                    );
                }

                // Otherwise, only trash if we haven't already.
                if ('trash' === $post->post_status) {
                    return new WP_Error(
                        'rest_already_trashed',
                        __('The post has already been deleted.'),
                        array('status' => 410)
                    );
                }

                // (Note that internally this falls through to `wp_delete_post()`
                // if the Trash is disabled.)
                $result = wp_trash_post($id);
                $post = get_post($id);
                $response = $this->prepare_item_for_response($post, $request);
            }

            if (!$result) {
                return new WP_Error(
                    'rest_cannot_delete',
                    __('The post cannot be deleted.'),
                    array('status' => 500)
                );
            }

            /**
             * Fires immediately after a single post is deleted or trashed via the REST API.
             *
             * They dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
             *
             * @since 4.7.0
             *
             * @param WP_Post          $post     The deleted or trashed post.
             * @param WP_REST_Response $response The response data.
             * @param WP_REST_Request  $request  The request sent to the API.
             */
            do_action("rest_delete_{$this->post_type}", $post, $response, $request);

            return $response;
        }

        /**
         * Get the order form, if the ID is valid.
         *
         * @since 1.16
         *
         * @param int $id Supplied ID.
         * @return WP_Post|WP_Error Post object if ID is valid, WP_Error otherwise.
         */
        protected function get_post($id)
        {
            $error = new WP_Error(
                'rest_post_invalid_id',
                __('Invalid post ID.'),
                array('status' => 404)
            );

            if ((int) $id <= 0) {
                return $error;
            }

            $post = get_post((int) $id);
            if (empty($post) || empty($post->ID) || $this->post_type !== $post->post_type) {
                return $error;
            }

            return $post;
        }

        /**
         * Retrieves a single order form.
         *
         * @since 1.16
         *
         * @param WP_REST_Request $request Full details about the request.
         * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
         */
        public function get_item($request)
        {

            $post = $this->get_post($request['id']);
            if (is_wp_error($post)) {
                return $post;
            }

            $data = $this->prepare_item_for_response($post, $request);
            $response = rest_ensure_response($data);

            return $response;
        }

        /**
         * Updates a single order form.
         *
         * @since 1.16
         *
         * @param WP_REST_Request $request Full details about the request.
         * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
         */
        public function update_item($request)
        {

            $valid_check = $this->get_post($request['id']);

            if (is_wp_error($valid_check)) {
                return $valid_check;
            }

            $post = $this->prepare_item_for_database($request);

            if (is_wp_error($post)) {
                return $post;
            }

            // Convert the post object to an array, otherwise wp_update_post() will expect non-escaped input.
            $post_id = wp_update_post(wp_slash((array) $post), true);

            if (is_wp_error($post_id)) {
                if ('db_update_error' === $post_id->get_error_code()) {
                    $post_id->add_data(array('status' => 500));
                } else {
                    $post_id->add_data(array('status' => 400));
                }
                return $post_id;
            }

            $post = get_post($post_id);

            /** This action is documented in wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php */
            do_action("rest_insert_{$this->post_type}", $post, $request, false);

            $schema = $this->get_item_schema();

            // ! empty( $schema['properties']['format'] ) &&
            if (!empty($request['form_elements'])) {
                update_post_meta($post->ID, 'form_elements', $request['form_elements']);
            }

            if (!empty($request['editor_area'])) {
                update_post_meta($post->ID, 'editor_area', $request['editor_area']);
            }

            if (!empty($request['styles'])) {
                update_post_meta($post->ID, 'styles', $request['styles']);
            }

            if (!empty($request['settings'])) {
                update_post_meta($post->ID, 'settings', $request['settings']);
            }

            $post = get_post($post_id);
            $fields_update = $this->update_additional_fields_for_object($post, $request);

            if (is_wp_error($fields_update)) {
                return $fields_update;
            }

            $request->set_param('context', 'edit');

            // Filter is fired in WP_REST_Attachments_Controller subclass.
            if ('attachment' === $this->post_type) {
                $response = $this->prepare_item_for_response($post, $request);
                return rest_ensure_response($response);
            }

            /** This action is documented in wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php */
            do_action("rest_after_insert_{$this->post_type}", $post, $request, false);

            $response = $this->prepare_item_for_response($post, $request);

            return rest_ensure_response($response);

        }

    }

}

return new Order_Form_API_Controller();
