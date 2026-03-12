<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('Order_Form_DB')) {

    /**
     * Model that houses plugin helper functions.
     *
     * @since 1.17
     */
    final class Order_Form_DB
    {

        /**
         * Find 'combo-variation-dropdown' and replace with 'variation-dropdown' on activation or plugin update.
         *
         * @since 1.17
         * @return bool
         */
        public static function version_1point17_update()
        {

            global $wpdb;

            if (WooCommerce_WholeSale_Order_Form::VERSION >= '1.17') {

                // Default settings
                $defaultSettings = array(
                    'selected_category' => '',
                    'filtered_categories' => array(),
                    'tax_display' => '',
                    'excluded_categories' => array(),
                    'subtotal_pretext' => '',
                    'subtotal_suffix' => '',
                );

                // Select all Order Form where the version is lower than v1.17 or has empty version
                // Versioning started in v1.17 so empty version means its lower than 1.17
                $sql1 = "SELECT p.ID
                        FROM $wpdb->posts p
                        LEFT JOIN $wpdb->postmeta pm
                            ON pm.post_id = p.ID AND pm.meta_key = '_wwof_version'
                        WHERE p.post_type = 'order_form'
                        AND (
                            ifnull(pm.meta_value, '') = ''
                            OR
                            ( pm.meta_key = '_wwof_version' AND pm.meta_value < '1.17' ) )";

                // Check if meta value has combo-variation-dropdown string.
                $sql2 = "SELECT p.ID, pm.meta_key
                        FROM $wpdb->posts p
                        LEFT JOIN $wpdb->postmeta pm
                            ON pm.post_id = p.ID
                        WHERE p.post_type = 'order_form'
                        AND pm.meta_value LIKE '%combo-variation-dropdown%'
                        AND p.ID in ( $sql1 )";

                $results = $wpdb->get_results($sql2);

                if (!empty($results)) {

                    foreach ($results as $result) {
                        $id = $result->ID;
                        $key = $result->meta_key;
                        $data = get_post_meta($id, $key, true);

                        // styles
                        if (isset($data['combo-variation-dropdown'])) {
                            $data['variation-dropdown'] = $data['combo-variation-dropdown'];
                            unset($data['combo-variation-dropdown']);
                        }

                        // editor_area
                        if (isset($data['formTable']) && isset($data['formTable']['itemIds']) && in_array('combo-variation-dropdown', $data['formTable']['itemIds'])) {
                            $index = array_search('combo-variation-dropdown', $data['formTable']['itemIds'], true);
                            if ($index >= 0) {
                                $data['formTable']['itemIds'][$index] = 'variation-dropdown';
                            }

                        }

                        // Update data
                        update_post_meta($id, $key, $data);

                        // Update Settings with default data if empty after update
                        $settings = get_post_meta($id, 'settings', true);
                        if (empty($settings)) {
                            update_post_meta($id, 'settings', $defaultSettings);
                        }

                    }
                }
            }

        }

        /**
         * Add Product Meta Data element in the editor for existing order forms.
         *
         * @since 1.21
         * @return bool
         */
        public static function version_1point21_update()
        {

            global $wpdb;

            if (WooCommerce_WholeSale_Order_Form::VERSION >= '1.21') {

                // Select all Order Form where the version is lower than v1.21 or has empty version
                // Versioning started in v1.17 so empty version means its lower than 1.17
                $sql = "SELECT p.ID
                        FROM $wpdb->posts p
                        LEFT JOIN $wpdb->postmeta pm
                            ON pm.post_id = p.ID
                        WHERE p.post_type = 'order_form'
                        AND (
                            ifnull(pm.meta_value, '') = ''
                            OR
                            ( pm.meta_key = '_wwof_version' AND pm.meta_value < '1.21' )
                        )
                        AND pm.meta_value NOT LIKE '%product-meta%'";

                $order_forms = $wpdb->get_results($sql);

                if (!empty($order_forms)) {

                    foreach ($order_forms as $order_form) {

                        $formElements = get_post_meta($order_form->ID, 'form_elements', true);

                        if (!in_array('product-meta', $formElements['tableElements']['itemIds'])) {
                            array_push($formElements['tableElements']['itemIds'], 'product-meta');

                            // Add Product Meta draggable element in the Table Column setting
                            update_post_meta($order_form->ID, 'form_elements', $formElements);
                        }

                    }

                }

            }

        }

    }

}
