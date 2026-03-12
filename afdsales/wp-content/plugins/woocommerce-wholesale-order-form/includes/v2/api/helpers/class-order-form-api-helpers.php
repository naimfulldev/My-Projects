<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWOF_API_Helpers')) {

    class WWOF_API_Helpers
    {

        /**
         * Check if user is a wholesale customer
         *
         * @since 1.15
         * @return bool
         */
        public static function is_wholesale_customer()
        {

            if (is_plugin_active('woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php')) {

                global $wc_wholesale_prices;
                $wholesale_role = $wc_wholesale_prices->wwp_wholesale_roles->getUserWholesaleRole();

                return isset($wholesale_role[0]) ? $wholesale_role[0] : '';

            }

            return false;

        }

        /**
         * Get header total pages and total products.
         *
         * @param string $headers WC API response header
         * @since 1.16.6
         * @return array
         */
        public static function get_header_data($headers)
        {

            $total_pages = 0;
            if (isset($headers['X-WP-TotalPages'])) {
                $total_pages = $headers['X-WP-TotalPages'];
            } else if (isset($headers['x-wp-totalpages'])) {
                $total_pages = $headers['x-wp-totalpages'];
            }

            $total_products = 0;
            if (isset($headers['X-WP-Total'])) {
                $total_products = $headers['X-WP-Total'];
            } else if (isset($headers['x-wp-total'])) {
                $total_products = $headers['x-wp-total'];
            }

            return array(
                'total_pages'    => $total_pages,
                'total_products' => $total_products,
            );
        }

        /**
         * Filter results by categories.
         *
         * @param array $form_settings
         * @since 1.17
         */
        public static function filtered_categories($form_settings)
        {

            if (!empty($form_settings)) {
                if (!empty($form_settings['filtered_categories'])) {
                    // only display included categories - excluded categories
                    $categories = array();
                    foreach ($form_settings['filtered_categories'] as $cat) {
                        $cat_obj = get_term_by('slug', $cat, 'product_cat');
                        if (is_a($cat_obj, 'WP_Term')) {
                            $categories[] = $cat_obj->term_id;
                        }

                    }
                    return !empty($categories) ? implode(",", $categories) : '';
                } else if (!empty($form_settings['excluded_categories'])) {
                    // dont display products from excluded categories
                    $excluded = array();
                    foreach ($form_settings['excluded_categories'] as $cat) {
                        $cat_obj = get_term_by('slug', $cat, 'product_cat');
                        if (is_a($cat_obj, 'WP_Term')) {
                            $excluded[] = $cat_obj->term_id;
                        }

                    }
                    $terms = get_terms(array(
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => false,
                        'exclude'    => $excluded,
                        'fields'     => 'ids',
                    ));

                    return !empty($terms) ? implode(",", $terms) : '';
                }
            }

            return '';
        }

        /**
         * Group category children ito their own parents.
         *
         * @since 1.15.2
         * @param array $cats       List of categories
         * @param array $into       New sorted children
         * @param array $parent_id  The parent ID. 0 is for grand parent.
         * @return array
         */
        public static function assign_category_children(array &$cats, array &$into, $parent_id = 0)
        {

            foreach ($cats as $i => $cat) {

                if ($cat->parent == $parent_id) {

                    $into[] = $cat;
                    unset($cats[$i]);

                }

            }

            foreach ($into as $top_cat) {

                $top_cat->children = array();
                self::assign_category_children($cats, $top_cat->children, $top_cat->id);

            }

        }

        /**
         * Product Category Filter and Exclude Product Filter option.
         *
         * @since 1.15
         * @return array
         */
        public static function include_products_from_category()
        {

            $categories = get_option('wwof_filters_product_category_filter');
            $args       = array(
                'category' => $categories,
                'return'   => 'ids',
                'paginate' => false,
                'exclude'  => get_option('wwof_filters_exclude_product_filter'),
            );

            $products = wc_get_products($args);

            return $products;
        }

        /**
         * Get the total variations for specific variable.
         *
         * @since 1.19
         * @param int       $variable_id        The variable ID
         * @param string    $wholesale_role     The wholesale role key
         * @return int
         */
        public static function get_variations_total_by_variable_id($variable_id, $wholesale_role)
        {

            global $wpdb;

            /*** ============================================================================ ***/
            /*** The code below check and counts all variations for admins and shop managers. ***/
            /*** ============================================================================ ***/

            $user_roles = Order_Form_Helpers::get_user_roles();

            // Admin and shop managers do not have restriction on variations so we need to count all variations
            if (!empty($user_roles) && (in_array('administrator', $user_roles) || in_array('shop_manager', $user_roles))) {
                // Count all variations
                $query = $wpdb->prepare("SELECT count(DISTINCT(p.ID)) FROM $wpdb->posts p
                                WHERE p.post_status = 'publish'
                                    AND p.post_type = 'product_variation'
                                    AND p.post_parent = %d
                    ", $variable_id);

                $totals = $wpdb->get_var($query);

                return $totals;

            }

            /*** ============================================================================= ***/
            /*** The code below check and counts only visible variations for the current user. ***/
            /*** ============================================================================= ***/

            $variable_id          = esc_sql($variable_id);
            $wholesale_role       = esc_sql($wholesale_role);
            $have_wholesale_price = get_post_meta($variable_id, $wholesale_role . '_have_wholesale_price', true); // product level
            $category_discount    = get_post_meta($variable_id, $wholesale_role . '_have_wholesale_price_set_by_product_cat', true); // category level

            $general_discount = array();
            // Make sure WWPP is active so that it will not throw undefined constant error
            if (Order_Form_Helpers::is_wwpp_active()) {
                $general_discount = get_option(WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING, array());
            }

            // If WWPP is active
            // If Only show wholesale products to wholesale users is enabled
            // If product have wholesale price
            // If product has category discount
            // If General discount is not set
            // Then count only variations with wholesale price for per product level.
            if (
                Order_Form_Helpers::is_wwpp_active() &&
                get_option('wwpp_settings_only_show_wholesale_products_to_wholesale_users', 'no') == 'yes' &&
                $have_wholesale_price == 'yes' &&
                $category_discount != 'yes' &&
                !array_key_exists($wholesale_role, $general_discount)
            ) {

                // Count how many wholesale price set per variation level via meta <wholesale_role>_variations_with_wholesale_price
                $query = $wpdb->prepare("SELECT count(*) FROM $wpdb->postmeta
                                        WHERE post_id IN (
                                                            SELECT meta_value FROM $wpdb->postmeta
                                                            WHERE post_id = %d
                                                                AND meta_key = '%s'
                                                                AND meta_value != ''
                                                        )
                                        AND meta_key = '%s'
                                        AND meta_value IN ( 'all' , '%s' )
                                    ", $variable_id, $wholesale_role . '_variations_with_wholesale_price', WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER, $wholesale_role);

                $totals = $wpdb->get_var($query);

            } else {

                if (Order_Form_Helpers::is_wwpp_active()) {

                    // Count all variations visible to current wholesale user
                    $query = $wpdb->prepare("SELECT count(DISTINCT(p.ID)) FROM $wpdb->posts p
                        INNER JOIN $wpdb->postmeta pm1 ON ( p.ID = pm1.post_id )
                        INNER JOIN $wpdb->postmeta pm2 ON ( p.ID = pm2.post_id )
                        WHERE p.post_status = 'publish'
                            AND p.post_type = 'product_variation'
                            AND p.post_parent = %d
                            AND
                            (
                                pm1.meta_key = '_stock_status' AND pm1.meta_value = 'instock'
                            )
                            AND
                            (
                                pm2.meta_key = '%s' AND pm2.meta_value IN ('all','%s')
                            )
                        ", $variable_id, WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER, $wholesale_role);

                    $totals = $wpdb->get_var($query);

                } else {

                    // Count all variations
                    $query = $wpdb->prepare("SELECT count(DISTINCT(p.ID)) FROM $wpdb->posts p
                        INNER JOIN $wpdb->postmeta pm1 ON ( p.ID = pm1.post_id )
                        INNER JOIN $wpdb->postmeta pm2 ON ( p.ID = pm2.post_id )
                        WHERE p.post_status = 'publish'
                            AND p.post_type = 'product_variation'
                            AND p.post_parent = %d
                            AND
                            (
                                pm1.meta_key = '_stock_status' AND pm1.meta_value = 'instock'
                            )
                        ", $variable_id);

                    $totals = $wpdb->get_var($query);

                }

            }

            $wpdb->flush();

            return $totals;

        }

        /**
         * Get variations set via category level discount
         *
         * @since 1.19
         * @param int       $wholesale_products     The wholesale product IDs
         * @param string    $wholesale_role         The wholesale role key
         * @return array
         */
        public static function category_level_wholesale_variations($wholesale_products, $wholesale_role)
        {

            global $wpdb;

            $wholesale_role     = esc_sql($wholesale_role);
            $wholesale_products = esc_sql(implode(', ', $wholesale_products));

            // Wholesale variable set via Category Discount
            $wholesale_variable_via_cat = $wpdb->get_results("SELECT p.ID FROM $wpdb->posts p
                                                                INNER JOIN $wpdb->postmeta pm1 ON (p.ID = pm1.post_id)
                                                                WHERE p.ID IN (" . $wholesale_products . ")
                                                                AND pm1.meta_key = '" . $wholesale_role . "_have_wholesale_price_set_by_product_cat' AND pm1.meta_value = 'yes'
                                                            ", ARRAY_A);

            $wholesale_variable_category_level_ids = array();
            if ($wholesale_variable_via_cat) {

                foreach ($wholesale_variable_via_cat as $variable) {
                    $wholesale_variable_category_level_ids[] = $variable['ID'];
                }

            }

            // Wholesale Variations category level discount
            $wholesale_variation_category_level_ids = array();
            if ($wholesale_variable_category_level_ids) {
                $wholesale_variable_category_level_ids = esc_sql(implode(', ', $wholesale_variable_category_level_ids));
                $wholesale_variations_category_level   = $wpdb->get_results("SELECT ID FROM $wpdb->posts
                                                                            WHERE $wpdb->posts.post_parent
                                                                                IN (" . $wholesale_variable_category_level_ids . ")
                                                                        ", ARRAY_A);

                if ($wholesale_variations_category_level) {

                    foreach ($wholesale_variations_category_level as $variation) {
                        $wholesale_variation_category_level_ids[] = $variation['ID'];
                    }

                }
            }

            return $wholesale_variation_category_level_ids;
        }

        /**
         * Get variations set via product level discount
         *
         * @since 1.19
         * @param int       $wholesale_products     The wholesale product IDs
         * @param string    $wholesale_role         The wholesale role key
         * @return array
         */
        public static function product_level_wholesale_variations($wholesale_products, $wholesale_role)
        {

            global $wpdb;

            $wholesale_role     = esc_sql($wholesale_role);
            $wholesale_products = esc_sql(implode(', ', $wholesale_products));

            // Wholesale variations product level discount
            $wholesale_variations_product_level = $wpdb->get_results("SELECT pm2.meta_value FROM $wpdb->posts p
                                                                        INNER JOIN $wpdb->postmeta pm1 ON (p.ID = pm1.post_id)
                                                                        INNER JOIN $wpdb->postmeta pm2 ON (p.ID = pm2.post_id)
                                                                        WHERE p.ID IN (" . $wholesale_products . ")
                                                                        AND
                                                                        (
                                                                            ( pm1.meta_key = '" . $wholesale_role . "_have_wholesale_price' AND pm1.meta_value = 'yes' )
                                                                            AND
                                                                            ( pm2.meta_key = '" . $wholesale_role . "_variations_with_wholesale_price' AND pm2.meta_value != '' )
                                                                        )
                                                                        ", ARRAY_A);

            $wholesale_variation_product_level_ids = array();
            if ($wholesale_variations_product_level) {

                foreach ($wholesale_variations_product_level as $variation) {
                    $wholesale_variation_product_level_ids[] = $variation['meta_value'];
                }

            }

            return $wholesale_variation_product_level_ids;
        }

        /**
         * Get all variations for "Show Variations Individually" feature.
         *
         * @since 1.19
         * @param int       $wholesale_products     The wholesale product IDs
         * @param string    $wholesale_role         The wholesale role key
         * @return array
         */
        public static function get_variations_to_show_individually($wholesale_products, $wholesale_role)
        {

            global $wpdb;

            // General Discount
            $general_discount = array();

            // Make sure WWPP is active so that it will not throw undefined constant error
            if (Order_Form_Helpers::is_wwpp_active()) {
                $general_discount = get_option(WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING, array());
            }

            // If WWPP is active
            // If Only show wholesale products to wholesale users is enabled
            // If General discount is not set
            // Then fetch wholesale variations product/category level.
            if (
                Order_Form_Helpers::is_wwpp_active() &&
                get_option('wwpp_settings_only_show_wholesale_products_to_wholesale_users', 'no') == 'yes' &&
                !array_key_exists($wholesale_role, $general_discount)
            ) {
                // $updated_wholesale_products = array_diff($wholesale_products, $excluded_variable_ids);
                return array_merge($wholesale_products, self::category_level_wholesale_variations($wholesale_products, $wholesale_role), self::product_level_wholesale_variations($wholesale_products, $wholesale_role));

            } else {

                $wholesale_products = esc_sql(implode(', ', $wholesale_products));

                // Fetch all variations
                $variations = $wpdb->get_results("SELECT ID FROM $wpdb->posts
                                            WHERE $wpdb->posts.post_parent
                                                IN (" . $wholesale_products . ")
                                        ", ARRAY_A);

                $variation_ids = array();
                if ($variations) {

                    foreach ($variations as $variation) {
                        $variation_ids[] = $variation['ID'];
                    }

                }

                return $variation_ids;

            }

        }

    }

}
