<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWLC_Emails')) {

    class WWLC_Emails {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * Property that holds the single main instance of WWLC_Emails.
         *
         * @since 1.6.3
         * @access private
         * @var WWLC_Emails
         */
        private static $_instance;

        /**
         * Model that houses the logic of retrieving information relating to WWLC_User_Account.
         *
         * @since 1.8.0
         * @access private
         * @var WWLC_User_Account
         */
        private $wwlc_user_account;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        /**
         * WWLC_Emails constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_Emails model.
         *
         * @access public
         * @since 1.6.3
         */
        public function __construct($dependencies) {

            $this->wwlc_user_account = $dependencies['WWLC_User_Account'];

        }

        /**
         * Ensure that only one instance of WWLC_Emails is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_Emails model.
         *
         * @return WWLC_Emails
         * @since 1.6.3
         */
        public static function instance($dependencies = null) {

            if (!self::$_instance instanceof self) {
                self::$_instance = new self($dependencies);
            }

            return self::$_instance;

        }

        /**
         * Get password reset url.
         *
         * @param $user_login
         *
         * @return bool
         * @since 1.0.0
         * @since 1.7.2 Refactored code so instead of manually creating key, we use get_password_reset_key WP function.
         */
        private function _get_reset_password_url($user_login) {

            global $wpdb;

            $user_login = sanitize_text_field($user_login);

            if (empty($user_login)) {

                return false;

            } elseif (strpos($user_login, '@')) {

                $user_data = get_user_by('email', trim($user_login));
                if (empty($user_data)) {
                    return false;
                }

            } else {

                $login     = trim($user_login);
                $user_data = get_user_by('login', $login);

            }

            do_action('lostpassword_post');

            if (!$user_data || !is_a($user_data, 'WP_User')) {
                return false;
            }

            // redefining user_login ensures we return the right case in the email
            $user_login = $user_data->user_login;

            do_action('retrieve_password', $user_login);

            $allow = apply_filters('allow_password_reset', true, $user_data->ID);

            if (!$allow) {
                return false;
            } elseif (is_wp_error($allow)) {
                return false;
            }

            // this WP function handles creation and storage of password reset key for the user.
            $key = get_password_reset_key($user_data);

            return network_site_url('wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode($user_login), 'login');

        }

        /**
         * Parse email contents, replace email template tags with appropriate values.
         *
         * @param      $userID
         * @param      $content
         * @param null $password
         *
         * @return mixed
         * @since 1.0.0
         * @since 1.7.1 Set password to be only shown for unmoderated and/or unapproved users (new user email).
         * @since 1.8.0 Get page option url via wwlc_get_url_of_page_option function
         * @since 1.13  When auto approve is enabled and new user email is not sent to auto approved user, then show password as link to reset password.
         */
        private function _parse_email_content($userID, $content, $password = null) {

            global $wpdb;

            $new_user      = get_userdata($userID);
            $custom_fields = get_option(WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS, array());

            // "user_wholesale_role" template tag is used in "Approval Email Template"
            $find_replace['user_wholesale_role'] = '';
            if (class_exists('WWP_Wholesale_Roles')) {

                $user_wholesale_role = array();
                $wwp_wholesale_role  = WWP_Wholesale_Roles::getInstance();
                $wholesale_roles     = $wwp_wholesale_role->getAllRegisteredWholesaleRoles();

                // Check wholesale role name
                foreach ($new_user->roles as $role) {
                    if (isset($wholesale_roles[$role])) {
                        $user_wholesale_role[] = $wholesale_roles[$role]['roleName'];
                    }

                }

                if (!empty($user_wholesale_role)) {
                    $find_replace['user_wholesale_role'] = $user_wholesale_role;
                }

            }

            $find_replace['user_role']             = wwlc_get_user_role($userID);
            $find_replace['wholesale_login_url']   = wwlc_get_url_of_page_option('wwlc_general_login_page');
            $find_replace['reset_password_url']    = $this->_get_reset_password_url($new_user->data->user_login);
            $find_replace['site_name']             = get_bloginfo('name');
            $find_replace['full_name']             = $new_user->first_name . ' ' . $new_user->last_name;
            $find_replace['user_management_url']   = get_admin_url(null, 'users.php');
            $find_replace['user_edit_profile_url'] = admin_url('user-edit.php?user_id=' . $new_user->ID);

            $capability              = maybe_unserialize(get_user_meta($userID, $wpdb->get_blog_prefix() . 'capabilities', true));
            $auto_generated_password = get_user_meta($userID, 'wwlc_auto_generated_password', true);

            // Upgrade Account
            if (get_user_meta($userID, 'wwlc_request_upgrade', true)) {
                $find_replace['password'] = __('[your current password]', 'woocommerce-wholesale-lead-capture');
            } else {
                // If {password} tag is used in "New User Email Template"
                if (isset($capability) && (isset($capability['wwlc_unapproved']) && $capability['wwlc_unapproved'] == true) && (isset($capability['wwlc_unmoderated']) && $capability['wwlc_unmoderated'] == true)) {
                    $find_replace['password'] = $password;
                } elseif (!empty($auto_generated_password) && get_option('wwlc_emails_new_user_disable_for_auto_approve') == 'yes' && get_option('wwlc_general_auto_approve_new_leads') == 'yes') {
                    $reset_link               = $this->_generate_user_reset_password_link($userID);
                    $find_replace['password'] = sprintf(__('<a href="%s">Click here to set your password</a>', 'woocommerce-wholesale-lead-capture'), $reset_link);
                } elseif (!empty($auto_generated_password)) {
                    $find_replace['password'] = __('[the password was supplied in your registration email]', 'woocommerce-wholesale-lead-capture');
                } else {
                    $find_replace['password'] = __('[the password supplied upon registration]', 'woocommerce-wholesale-lead-capture');
                }
            }

            $find_replace['email']        = $new_user->user_email;
            $find_replace['first_name']   = $new_user->first_name;
            $find_replace['last_name']    = $new_user->last_name;
            $find_replace['username']     = $new_user->user_login;
            $find_replace['phone']        = $new_user->wwlc_phone;
            $find_replace['company_name'] = $new_user->wwlc_company_name;

            // For backwards compatibility
            $find_replace['address'] = $new_user->wwlc_address;
            if ($new_user->wwlc_address_2) {
                $find_replace['address'] .= "<br/>" . $new_user->wwlc_address_2;
            }

            if ($new_user->wwlc_city) {
                $find_replace['address'] .= "<br/>" . $new_user->wwlc_city;
            }

            if ($new_user->wwlc_state) {
                $find_replace['address'] .= "<br/>" . $new_user->wwlc_state;
            }

            if ($new_user->wwlc_postcode) {
                $find_replace['address'] .= "<br/>" . $new_user->wwlc_postcode;
            }

            if ($new_user->wwlc_country) {
                $find_replace['address'] .= "<br/>" . $new_user->wwlc_country;
            }

            // Specific address elements
            $find_replace['address_1'] = $new_user->wwlc_address;
            $find_replace['address_2'] = $new_user->wwlc_address_2;
            $find_replace['city']      = $new_user->wwlc_city;
            $find_replace['state']     = $new_user->wwlc_state;
            $find_replace['postcode']  = $new_user->wwlc_postcode;
            $find_replace['country']   = $new_user->wwlc_country;

            $find_replace = apply_filters('wwlc_emails_tags', $find_replace);

            if (is_array($custom_fields) && !empty($custom_fields)) {
                foreach ($custom_fields as $field_id => $field) {
                    $find_replace['custom_field:' . $field_id] = $new_user->$field_id;
                }
            }

            foreach ($find_replace as $find => $replace) {

                if (is_array($replace)) {

                    $replace_str = implode(', ', $replace);
                    $content     = str_replace('{' . $find . '}', $replace_str, $content);

                } else {
                    $content = str_replace('{' . $find . '}', $replace, $content);
                }

            }

            return $content;

        }

        /*
        |--------------------------------------------------------------------------------------------------------------
        | Admin Emails
        |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Email sent to admin on new user registration.
         *
         * @param $userID
         * @param $subject
         * @param $message
         * @param $password Deprecated
         *
         * @since 1.0.0
         * @since 1.6.12 Added 'wwlc_send_new_user_admin_notice_email_headers_filter' filter to headers
         * @since 1.7.1  Deprecated password argument.
         */
        public function wwlc_send_new_user_admin_notice_email($userID, $subject, $message, $password = null) {

            // check if notification is set to disabled
            if (apply_filters('wwlc_disable_new_user_admin_notice_email', false)) {
                return;
            }

            $wc_emails = WC_Emails::instance();

            $to = $this->_get_admin_email_recipients();
            $to = apply_filters('wwlc_filter_new_user_admin_notice_email_recipients', $to);

            $cc = $this->_get_admin_email_cc();
            $cc = apply_filters('wwlc_filter_new_user_admin_notice_email_cc', $cc);

            $bcc = $this->_get_admin_email_bcc();
            $bcc = apply_filters('wwlc_filter_new_user_admin_notice_email_bcc', $bcc);

            $from_name  = $this->_get_from_name();
            $from_email = $this->_get_from_email();

            if (!$subject) {
                $subject = __('New User Registration', 'woocommerce-wholesale-lead-capture');
            } else {
                $subject = $this->_parse_email_content($userID, $subject, $password);
            }

            $subject = apply_filters('wwlc_filter_new_user_admin_email_subject', $subject);

            if (!$message) {

                global $newUserAdminNotificationEmailDefault;
                $message = $newUserAdminNotificationEmailDefault;

            }

            $message = $this->_parse_email_content($userID, $message, $password);

            $message                              = apply_filters('wwlc_filter_new_user_admin_email_content', html_entity_decode($message), $userID, $password);
            $wrap_email_with_wc_header_and_footer = trim(get_option("wwlc_email_wrap_wc_header_footer"));

            if ($wrap_email_with_wc_header_and_footer == "yes") {
                $message = $wc_emails->wrap_message($subject, $message);
            }

            $headers = $this->_construct_email_header($from_name, $from_email, $cc, $bcc);
            $headers = apply_filters('wwlc_send_new_user_admin_notice_email_headers_filter', $headers);

            // email attachments can be enabled via add_filter only for now
            $attachments = apply_filters('wwlc_enable_new_user_admin_notice_email_attachments', false) ? $this->_get_custom_field_email_attachments($userID) : '';

            $wc_emails->send($to, $subject, $message, $headers, $attachments);

        }

        /**
         * Email sent to admin on new user registration that is auto approved.
         *
         * @param $userID
         * @param $subject
         * @param $message
         * @param $password Deprecated
         *
         * @since 1.0.0
         * @since 1.6.12 Added 'wwlc_send_new_user_admin_notice_email_auto_approved_headers_filter' filter to headers
         * @since 1.7.1  Deprecated password argument.
         */
        public function wwlc_send_new_user_admin_notice_email_auto_approved($userID, $subject, $message, $password = null) {

            // check if notification is set to disabled
            if (apply_filters('wwlc_disable_new_user_auto_approved_notice_email', false)) {
                return;
            }

            $wc_emails = WC_Emails::instance();

            $to = $this->_get_admin_email_recipients();
            $to = apply_filters('wwlc_filter_new_user_auto_approved_admin_notice_email_recipients', $to);

            $cc = $this->_get_admin_email_cc();
            $cc = apply_filters('wwlc_filter_new_user_auto_approved_admin_notice_email_cc', $cc);

            $bcc = $this->_get_admin_email_bcc();
            $bcc = apply_filters('wwlc_filter_new_user_auto_approved_admin_notice_email_bcc', $bcc);

            $from_name  = $this->_get_from_name();
            $from_email = $this->_get_from_email();

            $headers = $this->_construct_email_header($from_name, $from_email, $cc, $bcc);
            $headers = apply_filters('wwlc_send_new_user_admin_notice_email_auto_approved_headers_filter', $headers);

            if (!$subject) {
                $subject = __('New User Registered And Approved', 'woocommerce-wholesale-lead-capture');
            } else {
                $subject = $this->_parse_email_content($userID, $subject, $password);
            }

            $subject = apply_filters('wwlc_filter_new_user_auto_approved_admin_notice_email_subject', $subject);

            if (!$message) {

                global $newUserAdminNotificationEmailAutoApprovedDefault;
                $message = $newUserAdminNotificationEmailAutoApprovedDefault;

            }

            $message = $this->_parse_email_content($userID, $message, $password);

            $wrap_email_with_wc_header_and_footer = trim(get_option("wwlc_email_wrap_wc_header_footer"));
            if ($wrap_email_with_wc_header_and_footer == "yes") {
                $message = $wc_emails->wrap_message($subject, $message);
            }

            $message = apply_filters('wwlc_filter_new_user_auto_approved_admin_notice_email_content', html_entity_decode($message), $userID, $password);

            // email attachments can be enabled via add_filter only for now
            $attachments = apply_filters('wwlc_enable_new_user_admin_notice_email_attachments', false) ? $this->_get_custom_field_email_attachments($userID) : '';

            $wc_emails->send($to, $subject, $message, $headers, $attachments);

        }

        /*
        |--------------------------------------------------------------------------------------------------------------
        | User Emails
        |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Email sent to user on successful registration.
         *
         * @param $userID
         * @param $subject
         * @param $message
         * @param $password
         *
         * @since 1.0.0
         * @since 1.6.2 WWLC-130, Bug fix: New User Email Template didn't send after successful registration
         * @since 1.6.12 Added 'wwlc_send_new_user_email_headers_filter' filter to headers
         */
        public function wwlc_send_new_user_email($userID, $subject, $message, $password) {

            $auto_approve_new_leads = get_option('wwlc_general_auto_approve_new_leads', 'no');
            $disable_new_user_email = get_option('wwlc_emails_new_user_disable_for_auto_approve', 'no');

            // Check if notification is set to disabled
            if (apply_filters('wwlc_disable_new_user_notice_email', false) ||
                ($auto_approve_new_leads == 'yes' && $disable_new_user_email == 'yes')) {
                return;
            }

            $wc_emails = WC_Emails::instance();

            $new_user = get_userdata($userID);
            $to       = $new_user->data->user_email;

            $from_name  = $this->_get_from_name();
            $from_email = $this->_get_from_email();

            $headers = $this->_construct_email_header($from_name, $from_email);
            $headers = apply_filters('wwlc_send_new_user_email_headers_filter', $headers);

            if (!$subject) {
                $subject = __('Registration Successful', 'woocommerce-wholesale-lead-capture');
            } else {
                $subject = $this->_parse_email_content($userID, $subject, $password);
            }

            $subject = apply_filters('wwlc_filter_new_user_user_notice_email_subject', $subject);

            if (!$message) {

                global $newUserEmailDefault;
                $message = $newUserEmailDefault;

            }

            $message = $this->_parse_email_content($userID, $message, $password);

            $wrap_email_with_wc_header_and_footer = trim(get_option('wwlc_email_wrap_wc_header_footer'));
            if ($wrap_email_with_wc_header_and_footer == 'yes') {
                $message = $wc_emails->wrap_message($subject, $message);
            }

            $message = apply_filters('wwlc_filter_new_user_user_notice_email_content', html_entity_decode($message), $userID, $password);

            $wc_emails->send($to, $subject, $message, $headers);

        }

        /**
         * Email sent to user on account approval.
         *
         * @param $userID
         * @param $subject
         * @param $message
         * @param $password Deprecated
         *
         * @since 1.0.0
         * @since 1.6.12 Added 'wwlc_send_registration_approval_email_headers_filter' filter to headers
         * @since 1.7.1  Deprecated password argument.
         */
        public function wwlc_send_registration_approval_email($userID, $subject, $message, $password = null) {

            // check if notification is set to disabled
            if (apply_filters('wwlc_disable_registration_approved_user_notice_email', false)) {
                return;
            }

            $wc_emails = WC_Emails::instance();

            $new_user = get_userdata($userID);
            $to       = $new_user->data->user_email;

            $from_name  = $this->_get_from_name();
            $from_email = $this->_get_from_email();

            $headers = $this->_construct_email_header($from_name, $from_email);
            $headers = apply_filters('wwlc_send_registration_approval_email_headers_filter', $headers);

            if (!$subject) {
                $subject = __('Registration Approved', 'woocommerce-wholesale-lead-capture');
            } else {
                $subject = $this->_parse_email_content($userID, $subject, $password);
            }

            $subject = apply_filters('wwlc_filter_registration_approved_user_notice_email_subject', $subject);

            if (!$message) {

                global $approvedEmailDefault;
                $message = $approvedEmailDefault;

            }

            $message = $this->_parse_email_content($userID, $message, $password);

            $wrap_email_with_wc_header_and_footer = trim(get_option("wwlc_email_wrap_wc_header_footer"));
            if ($wrap_email_with_wc_header_and_footer == "yes") {
                $message = $wc_emails->wrap_message($subject, $message);
            }

            $message = apply_filters('wwlc_filter_registration_approved_user_notice_email_content', html_entity_decode($message), $userID, $password);

            $wc_emails->send($to, $subject, $message, $headers);

        }

        /**
         * Email sent to user on account rejection.
         *
         * @param $userID
         * @param $subject
         * @param $message
         *
         * @since 1.0.0
         * @since 1.6.12 Added 'wwlc_send_registration_rejection_email_headers_filter' filter to headers
         */
        public function wwlc_send_registration_rejection_email($userID, $subject, $message) {

            // check if notification is set to disabled
            if (apply_filters('wwlc_disable_registration_rejected_user_notice_email', false)) {
                return;
            }

            $wc_emails = WC_Emails::instance();

            $new_user = get_userdata($userID);
            $to       = $new_user->data->user_email;

            $from_name  = $this->_get_from_name();
            $from_email = $this->_get_from_email();

            $headers = $this->_construct_email_header($from_name, $from_email);
            $headers = apply_filters('wwlc_send_registration_rejection_email_headers_filter', $headers);

            if (!$subject) {
                $subject = __('Registration Rejected', 'woocommerce-wholesale-lead-capture');
            } else {
                $subject = $this->_parse_email_content($userID, $subject);
            }

            $subject = apply_filters('wwlc_filter_registration_rejected_user_notice_email_subject', $subject);

            if (!$message) {

                global $rejectedEmailDefault;
                $message = $rejectedEmailDefault;

            }

            $message = $this->_parse_email_content($userID, $message);

            $wrap_email_with_wc_header_and_footer = trim(get_option('wwlc_email_wrap_wc_header_footer'));
            if ($wrap_email_with_wc_header_and_footer == 'yes') {
                $message = $wc_emails->wrap_message($subject, $message);
            }

            $message = apply_filters('wwlc_filter_registration_rejected_user_notice_email_content', html_entity_decode($message), $userID);

            $wc_emails->send($to, $subject, $message, $headers);

        }

        /*
        |--------------------------------------------------------------------------------------------------------------
        | Helper Functions
        |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Get admin email recipients.
         *
         * @return array|string
         *
         * @since 1.3.0
         */
        private function _get_admin_email_recipients() {

            $to = trim(get_option('wwlc_emails_main_recipient'));

            if ($to) {
                $to = explode(',', $to);
            } else {
                $to = array(get_option('admin_email'));
            }

            return $to;

        }

        /**
         * Get admin email cc.
         *
         * @return array|string
         *
         * @since 1.3.0
         */
        private function _get_admin_email_cc() {

            $cc = trim(get_option('wwlc_emails_cc'));

            if ($cc) {
                $cc = explode(',', $cc);
            }

            if (!is_array($cc)) {
                $cc = array();
            }

            return $cc;

        }

        /**
         * Get admin email bcc.
         *
         * @return array|string
         *
         * @since 1.3.0
         */
        private function _get_admin_email_bcc() {

            $bcc = trim(get_option('wwlc_emails_bcc'));

            if ($bcc) {
                $bcc = explode(',', $bcc);
            }

            if (!is_array($bcc)) {
                $bcc = array();
            }

            return $bcc;

        }

        /**
         * Get email from name.
         *
         * @return mixed
         *
         * @since 1.3.0
         */
        private function _get_from_name() {

            $from_name = trim(get_option("woocommerce_email_from_name"));

            if (!$from_name) {
                $from_name = get_bloginfo('name');
            }

            return apply_filters('wwlc_filter_from_name', $from_name);

        }

        /**
         * Get from email.
         *
         * @return mixed
         *
         * @since 1.3.0
         */
        private function _get_from_email() {

            $from_email = trim(get_option('woocommerce_email_from_address'));

            if (!$from_email) {
                $from_email = get_option('admin_email');
            }

            return apply_filters('wwlc_filter_from_email', $from_email);

        }

        /**
         * Construct email headers.
         *
         * @param $from_name
         * @param $from_email
         * @param array $cc
         * @param array $bcc
         * @return array
         *
         * @since 1.3.0
         */
        private function _construct_email_header($from_name, $from_email, $cc = array(), $bcc = array()) {

            $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';

            if (is_array($cc)) {
                foreach ($cc as $c) {
                    $headers[] = 'Cc: ' . $c;
                }
            }

            if (is_array($bcc)) {
                foreach ($bcc as $bc) {
                    $headers[] = 'Bcc: ' . $bc;
                }
            }

            $headers[] = 'Content-Type: text/html; charset=UTF-8';

            return $headers;

        }

        /**
         * Retrieves the attachment
         *
         * @param $userID
         * @return array
         *
         * @since 1.6.0
         */
        private function _get_custom_field_email_attachments($userID) {

            $wwlc_forms         = WWLC_Forms::instance();
            $file_fields        = $wwlc_forms->wwlc_get_file_custom_fields();
            $upload_dir         = wp_upload_dir();
            $user_wholesale_dir = $upload_dir['basedir'] . '/wholesale-customers/' . $userID;
            $attachments        = array();

            if (!is_array($file_fields)) {
                return;
            }

            // process attachments
            foreach ($file_fields as $field) {

                $attachments = $user_wholesale_dir . '/' . get_user_meta($userID, $field['name'], true);
            }

            return $attachments;
        }

        /**
         * Show Approve and Reject action in admin email when 'Allow managing of users via email' option is enabled.
         *
         * @param string     $message
         * @param int         $userID
         * @param string     $password
         * @return string
         *
         * @since 1.8.0
         */
        public function wwlc_allow_managing_of_users_via_email($message, $userID, $password) {

            $allow = get_option('wwlc_email_allow_managing_of_users', false);

            if ($allow == 'yes') {

                $allow_html = '<br><br>';
                $allow_html .= '<a target="_blank" href="' . admin_url('user-edit.php?user_id=' . $userID) . '&action=approve_user">Approve</a>';
                $allow_html .= '&nbsp;&nbsp;';
                $allow_html .= '<a target="_blank" href="' . admin_url('user-edit.php?user_id=' . $userID) . '&action=reject_user">Reject</a>';

                return $message . $allow_html;

            }

            return $message;

        }

        /**
         * Handles approval and rejection of users via email.
         *
         * @since 1.8.0
         */
        public function wwlc_process_approve_reject() {

            $allow              = get_option('wwlc_email_allow_managing_of_users', false);
            $screen             = get_current_screen();
            $current_user       = wp_get_current_user();
            $current_user_roles = $current_user->roles;

            if ($allow == 'yes' && $screen->id == 'user-edit' && in_array('administrator', $current_user_roles) && isset($_GET['action']) && isset($_GET['user_id'])) {

                $action  = $_GET['action'];
                $user_id = $_GET['user_id'];
                $user    = get_userdata($_GET['user_id']);
                $roles   = $user->roles;

                if ($user && array_intersect($roles, array(WWLC_UNAPPROVED_ROLE, WWLC_UNMODERATED_ROLE, WWLC_REJECTED_ROLE))) {

                    if ($action == 'approve_user') {

                        if ($this->wwlc_user_account->wwlc_approve_user(array('userID' => $user_id), $this)) {
                            wp_redirect(wwlc_get_current_url() . '&status=success');exit;
                        }

                    } else if ($action == 'reject_user') {

                        if ($this->wwlc_user_account->wwlc_reject_user(array('userID' => $user_id), $this)) {
                            wp_redirect(wwlc_get_current_url() . '&status=success');exit;
                        }

                    }

                }

            }

        }

        /**
         * Shows user approval notice in user edit screen.
         *
         * @since 1.8.0
         */
        public function wwlc_user_management_notice() {

            if (isset($_GET['action']) && isset($_GET['status'])) {

                // Approved Notice
                if ($_GET['action'] == 'approve_user' && $_GET['status'] == 'success') {
                    ?>

					<div class="notice notice-success is-dismissible">
				        <p><?php _e('Successfully Approved!', 'woocommerce-wholesale-lead-capture');?></p>
				    </div><?php

                }

                // Rejected Notice
                if ($_GET['action'] == 'reject_user' && $_GET['status'] == 'success') {
                    ?>

					<div class="notice notice-success is-dismissible">
				        <p><?php _e('Successfully Rejected!', 'woocommerce-wholesale-lead-capture');?></p>
				    </div><?php

                }

            }

        }

        /**
         * Generate user reset password link.
         *
         * @since 1.13
         * @access private
         *
         * @param int $user_id User ID.
         * @return string User reset password link.
         */
        private function _generate_user_reset_password_link($user_id) {

            $user = get_user_by('ID', absint($user_id));

            if (!is_a($user, 'WP_User')) {
                return wc_lostpassword_url();
            }

            $reset_key = get_password_reset_key($user);
            $username  = rawurlencode($user->user_login);

            return wc_lostpassword_url() . "?key=$reset_key&login=$username";
        }

        /**
         * Ultimate Member Plugin integration - remove UM hooks that send email to admin and user on registration
         *
         * @since 1.17
         */
        public function wwlc_um_email_conflicts_fix() {

            remove_action('um_registration_complete', 'um_send_registration_notification', 10, 2);
            remove_action('um_post_registration_checkmail_hook', 'um_post_registration_checkmail_hook', 10, 2);
            remove_action('um_post_registration_approved_hook', 'um_post_registration_approved_hook', 10, 2);

        }

        /**
         * Ultimate Member Plugin integration - attached new hook callback function to not send email to admin if new user register using WWLC Registration Form
         *
         * @since 1.17
         */
        public function wwlc_um_send_registration_notification_fix($user_id, $args) {
            // Do nothing - Don't send email if registration is coming from wwlc registration page
            // If wwlc_create_user in array is not found, UM New Registration Notification will be sent, else do nothing.
            if (isset($args['action']) && $args['action'] == 'wwlc_create_user')
                return;

            um_fetch_user($user_id);

            $emails = um_multi_admin_email();
            if (!empty($emails)) {
                foreach ($emails as $email) {
                    if (um_user('account_status') != 'pending') {
                        UM()->mail()->send($email, 'notification_new_user', array('admin' => true));
                    } else {
                        UM()->mail()->send($email, 'notification_review', array('admin' => true));
                    }
                }
            }
        }

        /**
         * Ultimate Member Plugin integration - ttached new hook callback function to not send activation email link to customer who register for wholesale customer via wwlc.
         *
         * @since 1.17
         */
        // attached new hook callback function to not send activation email link to customer who register for wholesale customer via wwlc.
        public function wwlc_um_post_registration_checkmail_hook_fix($user_id, $args) {
            // Do nothing - Dont send email for activation link on registration from wwlc, admin will be the one to approve for wholesale customer.
            // If wwlc_create_user in array is not found, UM will send its activation link to regular customer or non-wholesale customers.
            if (isset($args['action']) && $args['action'] == 'wwlc_create_user')
                return;
         
            um_fetch_user($user_id);
            UM()->user()->email_pending();
        }

        /**
         * Ultimate Member Plugin integration - attached new hook callback function to not send welcome  email link to customer who register for wholesale customer via wwlc.
         *
         * @since 1.17
         */
        public function wwlc_um_post_registration_approved_hook_fix($user_id, $args) {
            // Do nothing - Don't send email if registration is coming from wwlc registration page
            // If wwlc_create_user in array is not found, UM will send its welcome email to regular customer or non-wholesale customers.
            if (isset($args['action']) && $args['action'] == 'wwlc_create_user')
                return;
         
            um_fetch_user($user_id);
            UM()->user()->approve();
        }

        /**
         * Execute model.
         *
         * @since 1.8.0
         * @access public
         */
        public function run() {

            add_filter('wwlc_filter_new_user_admin_email_content', array($this, 'wwlc_allow_managing_of_users_via_email'), 10, 3);
            add_action('admin_head', array($this, 'wwlc_process_approve_reject'));
            add_action('admin_notices', array($this, 'wwlc_user_management_notice'));

            // Ultimate Member Plugin integration - registration email conflicts with WWLC wholesale registration
            if (is_plugin_active('ultimate-member/ultimate-member.php')) {
                add_action('init', array($this, 'wwlc_um_email_conflicts_fix'));
                add_action('um_registration_complete', array($this, 'wwlc_um_send_registration_notification_fix'), 10, 2);
                add_action('um_post_registration_checkmail_hook', array($this, 'wwlc_um_post_registration_checkmail_hook_fix'), 10, 2);
                add_action('um_post_registration_approved_hook', array($this, 'wwlc_um_post_registration_approved_hook_fix'), 10, 2);
            }

        }

    }

}
