<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('Order_Form_Requirements')) {

    class Order_Form_Requirements
    {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * Property that holds the single main instance of Order_Form_Requirements.
         *
         * @since 1.16
         * @access private
         */
        private static $_instance;

        /**
         * Property that holds required minimum WWPP version.
         *
         * @since 1.16
         * @access public
         */
        const MIN_WWPP_VERSION = '1.27';

        /**
         * Property that holds required minimum WWP version.
         *
         * @since 1.16.6
         * @access public
         */
        const MIN_WWP_VERSION = '1.16';

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        /**
         * Order_Form_Requirements constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of Order_Form_Requirements model.
         *
         * @access public
         * @since 1.16
         */
        public function __construct($dependencies)
        {}

        /**
         * Ensure that only one instance of Order_Form_Requirements is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of Order_Form_Requirements model.
         *
         * @return Order_Form_Requirements
         * @since 1.16
         */
        public static function instance($dependencies = null)
        {

            if (!self::$_instance instanceof self) {
                self::$_instance = new self($dependencies);
            }

            return self::$_instance;

        }

        /**
         * Check if WWP/P minimum requirement is met else show a message in Order Forms page.
         *
         * @since 1.16
         * @access public
         */
        public static function minimum_requirement()
        {

            $wwp_data         = Order_Form_Helpers::get_wwp_data();
            $wwpp_data        = Order_Form_Helpers::get_wwpp_data();
            $min_wwp_version  = self::MIN_WWP_VERSION;
            $min_wwpp_version = self::MIN_WWPP_VERSION;
            $show_notice      = get_option('wwof_show_min_wwpp_requirement_notice');

            // WWP is not active
            if (!Order_Form_Helpers::is_wwp_active()) {
                wp_send_json(
                    array(
                        'status'  => 'hidden',
                        'heading' => '',
                        'message' => '',
                    )
                );
            }

            // Hide notice
            if (!empty($show_notice) && $show_notice === 'no') {

                // Hide notice
                wp_send_json(
                    array(
                        'status'  => 'hidden',
                        'heading' => '',
                        'message' => '',
                    )
                );

            } else {

                // Show Notice in Order Forms Page
                update_option('wwof_show_min_wwpp_requirement_notice', 'yes');

                $img     = "<img style='height: 40px; margin-top: -3px;' src='" . WWOF_IMAGES_ROOT_URL . "/logo.png'>";
                $message = '';

                if ($wwp_data && version_compare($wwp_data['Version'], $min_wwp_version, '<')) {
                    $wwp_basename    = plugin_basename(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'woocommerce-wholesale-prices' . DIRECTORY_SEPARATOR . 'woocommerce-wholesale-prices.bootstrap.php');
                    $wwp_update_link = wp_nonce_url('update.php?action=upgrade-plugin&plugin=' . $wwp_basename, 'upgrade-plugin_' . $wwp_basename);
                    $update_now      = "<a style='font-weight: 600; font-size: 14px; background: #46bf92; color: #fff; padding: 4px 12px; display: inline-table;' href='" . $wwp_update_link . "' class='ant-btn' type='link' target='_blank'>" . __('Update Now', 'woocommerce-wholesale-order-form') . "</a>";
                    $message .= sprintf(__('<br/><p><b>WooCommerce Wholesale Prices</b> needs to be on at least version <b>%1$s</b> to work properly with WooCommerce Wholesale Order Form.</p><p><b>Click here to update.</b></p>%2$s', 'woocommerce-wholesale-order-form'), $min_wwp_version, $update_now);
                }

                if (Order_Form_Helpers::is_wwpp_active() && $wwpp_data && version_compare($wwpp_data['Version'], $min_wwpp_version, '<')) {
                    $license_activated = is_multisite() ? get_site_option('wwpp_license_activated') : get_option('wwpp_license_activated');
                    $wwpp_update_link  = $license_activated ? admin_url('update-core.php') : admin_url('options-general.php?page=wwc_license_settings&tab=wwpp');
                    $update_now        = "<a style='font-weight: 600; font-size: 14px; background: #46bf92; color: #fff; padding: 4px 12px; display: inline-table;' href='" . $wwpp_update_link . "' class='ant-btn' type='link' target='_blank'>" . __('Update Now', 'woocommerce-wholesale-order-form') . "</a>";
                    $message .= sprintf(__('<br/><br/><p><b>WooCommerce Wholesale Prices Premium</b> needs to be on at least version <b>%1$s</b> to work properly with WooCommerce Wholesale Order Form.</p><p><b>Click here to update.</b></p>%2$s', 'woocommerce-wholesale-order-form'), $min_wwpp_version, $update_now);
                }

                if (!empty($message)) {

                    // Print WWP/WWPP requirement
                    wp_send_json(
                        array(
                            'status'  => 'fail',
                            'heading' => sprintf(__('%1$s &nbsp; <b>NEWER VERSION OF WOOCOMMERCE WHOLESALE PRICES & PREMIUM REQUIRED</b>', 'woocommerce-wholesale-order-form'), $img),
                            'message' => $message,
                        )
                    );

                } else {

                    // Will not print this message
                    wp_send_json(
                        array(
                            'status'  => 'success',
                            'heading' => '',
                            'message' => sprintf(__('You have met the minimum WooCommerce Wholesale Prices Premium version of %1$s', 'woocommerce-wholesale-order-form'), $min_wwpp_version),
                        )
                    );

                }

            }

        }

        /**
         * Remove WWPP Minimum requirement message notice.
         *
         * @since 1.16
         * @access public
         */
        public function remove_minimum_requirement_message()
        {

            update_option('wwof_show_min_wwpp_requirement_notice', 'no');

            wp_send_json(
                array(
                    'status'  => 'success',
                    'message' => __('Hiding WWPP minimum requirement message.', 'woocommerce-wholesale-order-form'),
                )
            );

        }

        /**
         * Execute model.
         *
         * @since 1.16
         * @access public
         */
        public function run()
        {

            // Admin only AJAX Interfaces
            add_action('wp_ajax_wwpp_minimum_requirement', array($this, 'minimum_requirement'));
            add_action('wp_ajax_nopriv_wwpp_minimum_requirement', array($this, 'minimum_requirement'));

            add_action('wp_ajax_remove_wwpp_minimum_requirement_message', array($this, 'remove_minimum_requirement_message'));
            add_action('wp_ajax_nopriv_remove_wwpp_minimum_requirement_message', array($this, 'remove_minimum_requirement_message'));

        }
    }
}
