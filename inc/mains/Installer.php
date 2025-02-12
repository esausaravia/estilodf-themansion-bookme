<?php
namespace Bookme\Inc\Mains;

use Bookme\Inc;
use Bookme\Inc\Mains\Tables as Tables;

/**
 * Class Installer
 */
class Installer extends Inc\Core\Installer
{
    protected $notifications;

    /**
     * Create tables in database.
     */
    public function create_tables()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        $charset_collate = $wpdb->has_cap( 'collation' )
            ? $wpdb->get_charset_collate()
            : 'DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci';

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\Category::get_table_name() . '` (
                `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name`     VARCHAR(255) NOT NULL,
                `position` INT NOT NULL DEFAULT 9999
             ) ENGINE = INNODB
             ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\Service::get_table_name() . '` (
                `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `category_id`      INT UNSIGNED DEFAULT NULL,
                `color`            VARCHAR(255) NOT NULL DEFAULT "#333333",
                `title`            VARCHAR(255) DEFAULT "",
                `duration`         INT NOT NULL DEFAULT 900,
                `price`            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `capacity_min`     INT NOT NULL DEFAULT 1,
                `capacity_max`     INT NOT NULL DEFAULT 1,
                `padding_left`     INT NOT NULL DEFAULT 0,
                `padding_right`    INT NOT NULL DEFAULT 0,
                `info`             TEXT DEFAULT NULL,
                `start_time_info`  VARCHAR(255) DEFAULT "",
                `end_time_info`    VARCHAR(255) DEFAULT "",
                `bookings_limit`   INT DEFAULT NULL,
                `limit_period`     ENUM("off", "day","week","month","year") NOT NULL DEFAULT "off",
                `staff_preference` ENUM("order", "least_occupied", "most_occupied", "least_expensive", "most_expensive") NOT NULL DEFAULT "most_expensive",
                `online_meeting`   ENUM("off","zoom") NOT NULL DEFAULT "off",
                `visibility`       ENUM("public","private") NOT NULL DEFAULT "public",
                `position`         INT NOT NULL DEFAULT 9999,
                CONSTRAINT
                    FOREIGN KEY (category_id)
                    REFERENCES ' . Tables\Category::get_table_name() . '(id)
                    ON DELETE SET NULL
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\Employee::get_table_name() . '` (
                `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `full_name`          VARCHAR(255) DEFAULT NULL,
                `email`              VARCHAR(255) DEFAULT NULL,
                `phone`              VARCHAR(255) DEFAULT NULL,
                `info`               TEXT DEFAULT NULL,
                `wp_user_id`         BIGINT(20) UNSIGNED DEFAULT NULL,
                `attachment_id`      INT UNSIGNED DEFAULT NULL,
                `google_data`        TEXT DEFAULT NULL,
                `google_calendar_id` VARCHAR(255) DEFAULT NULL,
                `visibility`         ENUM("public","private") NOT NULL DEFAULT "public",
                `position`           INT NOT NULL DEFAULT 9999,
                `zoom_api_key`       VARCHAR(255) NULL DEFAULT NULL,
                `zoom_api_secret`    VARCHAR(255) NULL DEFAULT NULL
            ) ENGINE = INNODB
            ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\EmployeeService::get_table_name() . '` (
                `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_id`     INT UNSIGNED NOT NULL,
                `service_id`   INT UNSIGNED NOT NULL,
                `price`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `capacity_min` INT NOT NULL DEFAULT 1,
                `capacity_max` INT NOT NULL DEFAULT 1,
                UNIQUE KEY unique_ids_idx (staff_id, service_id),
                CONSTRAINT
                    FOREIGN KEY (staff_id)
                    REFERENCES ' . Tables\Employee::get_table_name() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                CONSTRAINT
                    FOREIGN KEY (service_id)
                    REFERENCES ' . Tables\Service::get_table_name() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\EmployeePreferenceOrder::get_table_name() . '` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `service_id`  INT UNSIGNED NOT NULL,
                `staff_id`    INT UNSIGNED NOT NULL,
                `position`    INT NOT NULL DEFAULT 9999,
                CONSTRAINT
                    FOREIGN KEY (service_id)
                    REFERENCES ' . Tables\Service::get_table_name() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                CONSTRAINT
                    FOREIGN KEY (staff_id)
                    REFERENCES ' . Tables\Employee::get_table_name() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\EmployeeSchedule::get_table_name() . '` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_id`   INT UNSIGNED NOT NULL,
                `day_index`  INT UNSIGNED NOT NULL,
                `start_time` TIME DEFAULT NULL,
                `end_time`   TIME DEFAULT NULL,
                UNIQUE KEY unique_ids_idx (staff_id, day_index),
                CONSTRAINT
                    FOREIGN KEY (staff_id)
                    REFERENCES ' . Tables\Employee::get_table_name() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
             ) ENGINE = INNODB
             ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\EmployeeScheduleBreak::get_table_name() . '` (
                `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_schedule_id` INT UNSIGNED NOT NULL,
                `start_time`        TIME DEFAULT NULL,
                `end_time`          TIME DEFAULT NULL,
                CONSTRAINT
                    FOREIGN KEY (staff_schedule_id)
                    REFERENCES ' . Tables\EmployeeSchedule::get_table_name() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
             ) ENGINE = INNODB
             ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\Customer::get_table_name() . '` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `full_name`  VARCHAR(255) NOT NULL DEFAULT "",
                `first_name` VARCHAR(255) NOT NULL DEFAULT "",
                `last_name`  VARCHAR(255) NOT NULL DEFAULT "",
                `phone`      VARCHAR(255) NOT NULL DEFAULT "",
                `email`      VARCHAR(255) NOT NULL DEFAULT "",
                `wp_user_id` BIGINT(20) UNSIGNED DEFAULT NULL,
                `notes`      TEXT NOT NULL DEFAULT ""
            ) ENGINE = INNODB
            ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\Booking::get_table_name() . '` (
                `id`                      INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_id`                INT UNSIGNED NOT NULL,
                `staff_any`               TINYINT(1)   NOT NULL DEFAULT 0,
                `service_id`              INT UNSIGNED NOT NULL,
                `start_date`              DATETIME NOT NULL,
                `end_date`                DATETIME NOT NULL,
                `google_event_id`         VARCHAR(255) DEFAULT NULL,
                `internal_note`           TEXT DEFAULT NULL,
                `online_meeting_provider` ENUM("zoom") NULL DEFAULT NULL, 
                `online_meeting_id`       VARCHAR(255) NULL DEFAULT NULL,
                `online_meeting_data`     TEXT NULL DEFAULT NULL,
                CONSTRAINT
                    FOREIGN KEY (staff_id)
                    REFERENCES ' . Tables\Employee::get_table_name() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                CONSTRAINT
                    FOREIGN KEY (service_id)
                    REFERENCES ' . Tables\Service::get_table_name() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\Payment::get_table_name() . '` (
                `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `type`      ENUM("local","coupon","paypal","authorize_net","stripe","2checkout","mollie","woocommerce","razorpay") NOT NULL DEFAULT "local",
                `total`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `paid`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `paid_type` ENUM("in_full","deposit") NOT NULL DEFAULT "in_full",
                `status`    ENUM("pending","completed") NOT NULL DEFAULT "completed",
                `details`   TEXT DEFAULT NULL,
                `created`   DATETIME NOT NULL
            ) ENGINE = INNODB
            ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\CustomerBooking::get_table_name() . '` (
                `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `customer_id`         INT UNSIGNED NOT NULL,
                `booking_id`      INT UNSIGNED NOT NULL,
                `payment_id`          INT UNSIGNED DEFAULT NULL,
                `number_of_persons`   INT UNSIGNED NOT NULL DEFAULT 1,
                `custom_fields`       TEXT DEFAULT NULL,
                `status`              ENUM("pending","approved","cancelled","rejected") NOT NULL DEFAULT "approved",
                `token`               VARCHAR(255) DEFAULT NULL,
                `time_zone`           VARCHAR(255) DEFAULT NULL,
                `time_zone_offset`    INT DEFAULT NULL,
                `locale`              VARCHAR(8) NULL,
                `created_from`        ENUM("frontend","backend") NOT NULL DEFAULT "frontend",
                `created`             DATETIME NOT NULL,
                CONSTRAINT
                    FOREIGN KEY (customer_id)
                    REFERENCES  ' . Tables\Customer::get_table_name() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE,
                CONSTRAINT
                    FOREIGN KEY (booking_id)
                    REFERENCES  ' . Tables\Booking::get_table_name() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE,
                CONSTRAINT 
                    FOREIGN KEY (payment_id)
                    REFERENCES ' . Tables\Payment::get_table_name() . '(id)
                    ON DELETE   SET NULL
                    ON UPDATE   CASCADE
            ) ENGINE = INNODB
            ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\Coupon::get_table_name() . '` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `code`        VARCHAR(255) NOT NULL DEFAULT "",
                `discount`    DECIMAL(3,0) NOT NULL DEFAULT 0,
                `deduction`   DECIMAL(10,2) NOT NULL DEFAULT 0,
                `usage_limit` INT UNSIGNED NOT NULL DEFAULT 1,
                `used`        INT UNSIGNED NOT NULL DEFAULT 0
            ) ENGINE = INNODB
            ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\CouponService::get_table_name() . '` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `coupon_id`   INT UNSIGNED NOT NULL,
                `service_id`  INT UNSIGNED NOT NULL,
                CONSTRAINT
                    FOREIGN KEY (coupon_id)
                    REFERENCES  ' . Tables\Coupon::get_table_name() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE,
                CONSTRAINT
                    FOREIGN KEY (service_id)
                    REFERENCES  ' . Tables\Service::get_table_name() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE
            ) ENGINE = INNODB
            ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\Holiday::get_table_name() . '` (
                  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  `staff_id`     INT UNSIGNED NULL DEFAULT NULL,
                  `parent_id`    INT UNSIGNED NULL DEFAULT NULL,
                  `date`         DATE NOT NULL,
                  `repeat_event` TINYINT(1) NOT NULL DEFAULT 0,
                  CONSTRAINT
                      FOREIGN KEY (staff_id)
                      REFERENCES ' . Tables\Employee::get_table_name() . '(id)
                      ON DELETE CASCADE
              ) ENGINE = INNODB
              ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\Notification::get_table_name() . '` (
                `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `gateway`      ENUM("email","sms") NOT NULL DEFAULT "email",
                `type`         VARCHAR(255) NOT NULL DEFAULT "",
                `subject`      VARCHAR(255) NOT NULL DEFAULT "",
                `message`      TEXT DEFAULT NULL,
                `active`       TINYINT(1) NOT NULL DEFAULT 0,
                `to_staff`     TINYINT(1) NOT NULL DEFAULT 0,
                `to_customer`  TINYINT(1) NOT NULL DEFAULT 0,
                `to_admin`     TINYINT(1) NOT NULL DEFAULT 0
            ) ENGINE = INNODB
            ' . $charset_collate
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Tables\SentNotification::get_table_name() . '` (
                `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `ref_id`          INT UNSIGNED NOT NULL,
                `notification_id` INT UNSIGNED NOT NULL,
                `created`         DATETIME NOT NULL,
                INDEX `ref_id_idx` (`ref_id`),
                CONSTRAINT
                    FOREIGN KEY (notification_id) 
                    REFERENCES  ' . Tables\Notification::get_table_name() . ' (`id`)
                    ON DELETE   CASCADE 
                    ON UPDATE   CASCADE
              ) ENGINE = INNODB
              ' . $charset_collate
        );
    }

    /**
     * Load data.
     */
    public function load_data()
    {
        parent::load_data();

        // Insert notifications.
        foreach ($this->notifications as $data) {
            $notification = new Tables\Notification();
            $notification->set_fields($data)->save();
        }

        // Create custom WP role for Bookme
        $capabilities = array();
        if ( $subscriber = get_role( 'subscriber' ) ) {
            $capabilities = $subscriber->capabilities;
        }
        $capabilities['manage_bookme'] = true;
        add_role( 'bookme_administrator', 'Bookme Administrator', $capabilities );

        // Register custom fields for translate in WPML
        foreach (json_decode($this->options['bookme_custom_fields']) as $custom_field) {
            switch ($custom_field->type) {
                case 'textarea':
                case 'text-field':
                    do_action('wpml_register_single_string', 'bookme', 'custom_field_' . $custom_field->id . '_' . sanitize_title($custom_field->label), $custom_field->label);
                    break;
                case 'checkboxes':
                case 'radio-buttons':
                case 'drop-down':
                    do_action('wpml_register_single_string', 'bookme', 'custom_field_' . $custom_field->id . '_' . sanitize_title($custom_field->label), $custom_field->label);
                    foreach ($custom_field->items as $label) {
                        do_action('wpml_register_single_string', 'bookme', 'custom_field_' . $custom_field->id . '_' . sanitize_title($custom_field->label) . '=' . sanitize_title($label), $label);
                    }
                    break;
            }
        }
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Load lang
        load_plugin_textdomain('bookme', false, Plugin::get_slug() . '/languages');

        /*
         * Options.
         */
        $this->options = array(
            'bookme_primary_color' => '#6B76FF',
            'bookme_secondary_color' => '#FFFFFF',
            'bookme_show_progress_bar' => '1',
            'bookme_service_name_with_duration' => '0',
            'bookme_employee_name_with_price' => '1',
            'bookme_form_layout' => '2',
            'bookme_form_custom_css' => '',
            'bookme_time_slot_step' => '15',
            'bookme_service_duration_as_slot_step' => '0',
            'bookme_use_client_time_zone' => '0',
            'bookme_allow_staff_edit_profile' => '1',
            'bookme_required_employee' => '0',
            'bookme_default_booking_status' => Tables\CustomerBooking::STATUS_APPROVED,
            'bookme_max_days_for_booking' => '365',
            'bookme_min_time_before_booking' => '0',
            'bookme_min_time_before_cancel' => '0',
            'bookme_phone_default_country' => 'auto',
            'bookme_default_country_code' => '',
            'bookme_final_step_url' => '',
            'bookme_customer_create_account' => '0',
            'bookme_customer_new_account_role' => 'subscriber',
            'bookme_customer_save_in_cookie' => '0',
            'bookme_customer_first_last_name' => '0',
            'bookme_customer_required_phone' => '1',
            'bookme_customer_show_login_button' => '0',
            'bookme_cart_enabled' => '0',
            'bookme_cart_columns' => array(
                'date' => array('show' => 1), 'time' => array('show' => 1), 'service' => array('show' => 1), 'employee' => array('show' => 1), 'price' => array('show' => 1)
            ),
            'bookme_company_logo_id' => '',
            'bookme_company_name' => '',
            'bookme_company_address' => '',
            'bookme_company_phone' => '',
            'bookme_company_website' => '',
            'bookme_gc_client_id' => '',
            'bookme_gc_client_secret' => '',
            'bookme_gc_remove_busy_slots' => '1',
            'bookme_gc_limit_events' => '50',
            'bookme_gc_event_title' => '{service_name}',
            'bookme_wc_enabled' => '0',
            'bookme_wc_product' => '',
            'bookme_lang_wc_cart_data_name' => esc_html__('Booking', 'bookme'),
            'bookme_lang_wc_cart_data_value' => esc_html__('Date', 'bookme') . ": {booking_date}\n"
                . esc_html__('Time', 'bookme') . ": {booking_time}\n" . esc_html__('Service', 'bookme') . ': {service_name}',
            'bookme_currency' => 'USD',
            'bookme_price_format' => '{symbol}{price|2}',
            'bookme_coupons_enabled' => '0',
            'bookme_local_enabled' => '1',
            'bookme_paypal_enabled' => 'disabled',
            'bookme_paypal_api_username' => '',
            'bookme_paypal_api_password' => '',
            'bookme_paypal_api_signature' => '',
            'bookme_paypal_sandbox' => '0',
            'bookme_stripe_enabled' => 'disabled',
            'bookme_stripe_publishable_key' => '',
            'bookme_stripe_secret_key' => '',
            'bookme_2checkout_enabled' => 'disabled',
            'bookme_2checkout_api_secret_word' => '',
            'bookme_2checkout_api_seller_id' => '',
            'bookme_2checkout_sandbox' => '0',
            'bookme_authorize_net_enabled' => 'disabled',
            'bookme_authorize_net_api_login_id' => '',
            'bookme_authorize_net_transaction_key' => '',
            'bookme_authorize_net_sandbox' => '0',
            'bookme_mollie_enabled' => 'disabled',
            'bookme_mollie_api_key' => '',
            'bookme_email_sender' => get_option('admin_email'),
            'bookme_email_sender_name' => get_option('blogname'),
            'bookme_email_send_as' => 'html',
            'bookme_email_reply_to_customers' => '1',
            'bookme_sms_admin_phone' => '',
            'bookme_twillio_account_sid' => '',
            'bookme_twillio_auth_token' => '',
            'bookme_twillio_phone_number' => '',
            'bookme_combined_notifications' => '0',
            'bookme_approve_success_url' => home_url(),
            'bookme_approve_unsuccess_url' => home_url(),
            'bookme_cancel_success_url' => home_url(),
            'bookme_cancel_unsuccess_url' => home_url(),
            'bookme_reject_success_url' => home_url(),
            'bookme_reject_unsuccess_url' => home_url(),
            'bookme_cron_times' => array('client_follow_up' => 18, 'client_reminder' => 18, 'staff_agenda' => 18),
            'bookme_wh_monday_start' => '08:00:00',
            'bookme_wh_monday_end' => '18:00:00',
            'bookme_wh_tuesday_start' => '08:00:00',
            'bookme_wh_tuesday_end' => '18:00:00',
            'bookme_wh_wednesday_start' => '08:00:00',
            'bookme_wh_wednesday_end' => '18:00:00',
            'bookme_wh_thursday_end' => '18:00:00',
            'bookme_wh_thursday_start' => '08:00:00',
            'bookme_wh_friday_start' => '08:00:00',
            'bookme_wh_friday_end' => '18:00:00',
            'bookme_wh_saturday_start' => '08:00:00',
            'bookme_wh_saturday_end' => '18:00:00',
            'bookme_wh_sunday_start' => '',
            'bookme_wh_sunday_end' => '',
            'bookme_custom_fields' => '[{"type":"textarea","label":'
                . json_encode(esc_html__('Notes', 'bookme')) . ',"required":false,"id":1,"services":[]}]',
            'bookme_custom_fields_per_service' => '0',
            'bookme_custom_fields_merge' => '1',
            'bookme_purchase_code' => '',
            'bookme_secret_file' => '',
            'bookme_lang_title_category' => esc_html__('Category', 'bookme'),
            'bookme_lang_select_category' => esc_html__('Select category', 'bookme'),
            'bookme_lang_title_service' => esc_html__('Service', 'bookme'),
            'bookme_lang_select_service' => esc_html__('Select service', 'bookme'),
            'bookme_lang_required_service' => esc_html__('Please select a service', 'bookme'),
            'bookme_lang_title_employee' => esc_html__('Employee', 'bookme'),
            'bookme_lang_select_employee' => esc_html__('Any', 'bookme'),
            'bookme_lang_required_employee' => esc_html__('Please select an employee', 'bookme'),
            'bookme_lang_title_number_of_persons' => esc_html__('Number of persons', 'bookme'),
            'bookme_lang_step_service' => esc_html__('Service', 'bookme'),
            'bookme_lang_step_time' => esc_html__('Time', 'bookme'),
            'bookme_lang_step_cart' => esc_html__('Cart', 'bookme'),
            'bookme_lang_step_details' => esc_html__('Details', 'bookme'),
            'bookme_lang_step_done' => esc_html__('Done', 'bookme'),
        );
        /*
         * Notifications email & sms.
         */
        $this->notifications = array(
            array(
                'gateway' => 'email',
                'type' => 'client_pending_appointment',
                'subject' => __('{service_name} Booking Pending', 'bookme'),
                'message' => wpautop(__("Dear {customer_name}.\nThis is a confirmation that you have booked {service_name}.\nWe are waiting you at {company_address} on {booking_date} {booking_time}.\n\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookme')),
                'active' => 1,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'email',
                'type' => 'client_approved_appointment',
                'subject' => __('{service_name} Booking Approved', 'bookme'),
                'message' => wpautop(__("Dear {customer_name}.\nThis is a confirmation that you have booked {service_name}.\nWe are waiting you at {company_address} on {booking_date} {booking_time}.\n\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookme')),
                'active' => 1,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'email',
                'type' => 'client_cancelled_appointment',
                'subject' => __('{service_name} Booking Canceled', 'bookme'),
                'message' => wpautop(__("Dear {customer_name}.\nYour booking of {service_name} on {booking_date} {appointment_time} has been canceled.\n\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookme')),
                'active' => 1,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'email',
                'type' => 'client_rejected_appointment',
                'subject' => __('{service_name} Booking Rejected', 'bookme'),
                'message' => wpautop(__("Dear {customer_name}.\nYour booking of {service_name} on {booking_date} {appointment_time} has been rejected.\n\nReason: {cancellation_reason}\n\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookme')),
                'active' => 1,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'email',
                'type' => 'client_new_wp_user',
                'subject' => __('Your login details', 'bookme'),
                'message' => wpautop(__("Hello.\nAn account was created for you at {site_address}\n\nYour login details:\nuser: {new_username}\npassword: {new_password}\n\nThanks.", 'bookme')),
                'active' => 1,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'email',
                'type' => 'client_reminder',
                'subject' => __('{service_name} Appointment Reminder', 'bookme'),
                'message' => wpautop(__("Dear {customer_name}.\nWe would like to remind you that you have booked {service_name} tomorrow at {booking_time}. We are waiting for you at {company_address}.\n\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookme')),
                'active' => 0,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'email',
                'type' => 'client_follow_up',
                'subject' => __('Your visit to {company_name}', 'bookme'),
                'message' => wpautop(__("Dear {customer_name}.\nThank you for choosing {company_name}. We hope you were satisfied with your {service_name}.\nThank you and we look forward to seeing you again soon.\n{company_name}\n{company_phone}\n{company_website}", 'bookme')),
                'active' => 0,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'email',
                'type' => 'client_pending_appointment_cart',
                'subject' => __('Your appointment information', 'bookme'),
                'message' => wpautop(__("Dear {customer_name}.\nThis is a confirmation that you have booked the following services:\n\n{cart_info}\n\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookme')),
                'active' => 1,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'email',
                'type' => 'client_approved_appointment_cart',
                'subject' => __('Your appointment information', 'bookme'),
                'message' => wpautop(__("Dear {customer_name}.\nThis is a confirmation that you have booked the following items:\n\n{cart_info}\n\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookme')),
                'active' => 1,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'email',
                'type' => 'staff_pending_appointment',
                'subject' => __('{service_name} Booking Pending', 'bookme'),
                'message' => wpautop(__("Hello.\n\nYou have a new booking.\n\nService: {service_name}\nDate: {booking_date}\nTime: {booking_time} - {booking_end_time}\nCustomer name: {customer_name}\nCustomer phone: {customer_phone}\nCustomer email: {customer_email}", 'bookme')),
                'active' => 1,
                'to_staff' => 1,
                'to_admin' => 1
            ),
            array(
                'gateway' => 'email',
                'type' => 'staff_approved_appointment',
                'subject' => __('{service_name} Booking Approved', 'bookme'),
                'message' => wpautop(__("Hello.\n\nYou have a new booking.\n\nService: {service_name}\nDate: {booking_date}\nTime: {booking_time} - {booking_end_time}\nCustomer name: {customer_name}\nCustomer phone: {customer_phone}\nCustomer email: {customer_email}", 'bookme')),
                'active' => 1,
                'to_staff' => 1,
                'to_admin' => 1
            ),
            array(
                'gateway' => 'email',
                'type' => 'staff_cancelled_appointment',
                'subject' => __('{service_name} Booking Canceled', 'bookme'),
                'message' => wpautop(__("Hello.\n\nThe following booking has been canceled.\n\nService: {service_name}\nDate: {booking_date}\nTime: {booking_time} - {booking_end_time}\nCustomer name: {customer_name}\nCustomer phone: {customer_phone}\nCustomer email: {customer_email}", 'bookme')),
                'active' => 1,
                'to_staff' => 1,
            ),
            array(
                'gateway' => 'email',
                'type' => 'staff_rejected_appointment',
                'subject' => __('{service_name} Booking Rejected', 'bookme'),
                'message' => wpautop(__("Hello.\n\nThe following booking has been rejected.\n\nReason: {cancellation_reason}\n\nService: {service_name}\nDate: {booking_date}\nTime: {booking_time} - {booking_end_time}\nCustomer name: {customer_name}\nCustomer phone: {customer_phone}\nCustomer email: {customer_email}", 'bookme')),
                'active' => 1,
                'to_staff' => 1,
            ),
            array(
                'gateway' => 'email',
                'type' => 'staff_agenda',
                'subject' => __('Your agenda for {tomorrow_date}', 'bookme'),
                'message' => wpautop(__("Hello.\n\nYour agenda for tomorrow is:\n\n{next_day_agenda}", 'bookme')),
                'active' => 0,
                'to_staff' => 1,
            ),
            array(
                'gateway' => 'sms',
                'type' => 'client_pending_appointment',
                'subject' => '',
                'message' => __("Dear {customer_name}.\nThis is a confirmation that you have booked {service_name}.\nWe are waiting you at {company_address} on {booking_date} {booking_time}.\n\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookme'),
                'active' => 1,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'sms',
                'type' => 'client_approved_appointment',
                'subject' => '',
                'message' => __("Dear {customer_name}.\nThis is a confirmation that you have booked {service_name}.\nWe are waiting you at {company_address} on {booking_date} {booking_time}.\n\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookme'),
                'active' => 1,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'sms',
                'type' => 'client_cancelled_appointment',
                'subject' => '',
                'message' => __("Dear {customer_name}.\nYour booking of {service_name} on {booking_date} {appointment_time} has been canceled.\n\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookme'),
                'active' => 1,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'sms',
                'type' => 'client_rejected_appointment',
                'subject' => '',
                'message' => __("Dear {customer_name}.\nYour booking of {service_name} on {booking_date} {appointment_time} has been rejected.\n\nReason: {cancellation_reason}\n\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookme'),
                'active' => 1,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'sms',
                'type' => 'client_new_wp_user',
                'subject' => '',
                'message' => __("Hello.\nAn account was created for you at {site_address}\n\nYour login details:\nuser: {new_username}\npassword: {new_password}\n\nThanks.", 'bookme'),
                'active' => 1,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'sms',
                'type' => 'client_reminder',
                'subject' => '',
                'message' => __("Dear {customer_name}.\nWe would like to remind you that you have booked {service_name} tomorrow at {booking_time}. We are waiting for you at {company_address}.\n\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookme'),
                'active' => 0,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'sms',
                'type' => 'client_follow_up',
                'subject' => '',
                'message' => __("Dear {customer_name}.\nThank you for choosing {company_name}. We hope you were satisfied with your {service_name}.\nThank you and we look forward to seeing you again soon.\n{company_name}\n{company_phone}\n{company_website}", 'bookme'),
                'active' => 0,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'sms',
                'type' => 'client_pending_appointment_cart',
                'subject' => '',
                'message' => __("Dear {customer_name}.\nThis is a confirmation that you have booked the following services:\n\n{cart_info}\n\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookme'),
                'active' => 1,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'sms',
                'type' => 'client_approved_appointment_cart',
                'subject' => '',
                'message' => __("Dear {customer_name}.\nThis is a confirmation that you have booked the following items:\n\n{cart_info}\n\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookme'),
                'active' => 1,
                'to_customer' => 1
            ),
            array(
                'gateway' => 'sms',
                'type' => 'staff_pending_appointment',
                'subject' => '',
                'message' => __("Hello.\n\nYou have a new booking.\n\nService: {service_name}\nDate: {booking_date}\nTime: {booking_time} - {booking_end_time}\nCustomer name: {customer_name}\nCustomer phone: {customer_phone}\nCustomer email: {customer_email}", 'bookme'),
                'active' => 1,
                'to_staff' => 1,
                'to_admin' => 1
            ),
            array(
                'gateway' => 'sms',
                'type' => 'staff_approved_appointment',
                'subject' => '',
                'message' => __("Hello.\n\nYou have a new booking.\n\nService: {service_name}\nDate: {booking_date}\nTime: {booking_time} - {booking_end_time}\nCustomer name: {customer_name}\nCustomer phone: {customer_phone}\nCustomer email: {customer_email}", 'bookme'),
                'active' => 1,
                'to_staff' => 1,
                'to_admin' => 1
            ),
            array(
                'gateway' => 'sms',
                'type' => 'staff_cancelled_appointment',
                'subject' => '',
                'message' => __("Hello.\n\nThe following booking has been canceled.\n\nService: {service_name}\nDate: {booking_date}\nTime: {booking_time} - {booking_end_time}\nCustomer name: {customer_name}\nCustomer phone: {customer_phone}\nCustomer email: {customer_email}", 'bookme'),
                'active' => 1,
                'to_staff' => 1,
            ),
            array(
                'gateway' => 'sms',
                'type' => 'staff_rejected_appointment',
                'subject' => '',
                'message' => __("Hello.\n\nThe following booking has been rejected.\n\nReason: {cancellation_reason}\n\nService: {service_name}\nDate: {booking_date}\nTime: {booking_time} - {booking_end_time}\nCustomer name: {customer_name}\nCustomer phone: {customer_phone}\nCustomer email: {customer_email}", 'bookme'),
                'active' => 1,
                'to_staff' => 1,
            ),
            array(
                'gateway' => 'sms',
                'type' => 'staff_agenda',
                'subject' => '',
                'message' => __("Hello.\n\nYour agenda for tomorrow is:\n\n{next_day_agenda}", 'bookme'),
                'active' => 0,
                'to_staff' => 1,
            ),
        );

    }
}