<?php

namespace Bookme\Inc\Mains;

use Bookme\Inc;
use Bookme\Inc\Mains\Tables as Tables;

/**
 * Class Updater
 */
class Updater extends Inc\Core\Updater
{
    /**
     * Version 4.6
     */
    function version_4_6()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        // Add razorpay payment gateway
        $wpdb->query("ALTER TABLE `". Tables\Payment::get_table_name() ."` 
            CHANGE `type` `type` ENUM('local','coupon','paypal','authorize_net','stripe','2checkout','payu_latam','payson','mollie','woocommerce','razorpay') NOT NULL DEFAULT 'local';");
    }

    /**
     * Version 4.5
     */
    function version_4_5()
    {
        // Create custom WP role for Bookme
        $capabilities = array();
        if ( $subscriber = get_role( 'subscriber' ) ) {
            $capabilities = $subscriber->capabilities;
        }
        $capabilities['manage_bookme'] = true;
        add_role( 'bookme_administrator', 'Bookme Administrator', $capabilities );

        /** @global \wpdb $wpdb */
        global $wpdb;

        // Add columns for online meeting
        $wpdb->query("ALTER TABLE `". Tables\Service::get_table_name() ."` 
            ADD `online_meeting` ENUM('off','zoom') NOT NULL DEFAULT 'off' AFTER `staff_preference`");

        $wpdb->query("ALTER TABLE `". Tables\Booking::get_table_name() ."` 
            ADD `online_meeting_provider` ENUM('zoom') NULL DEFAULT NULL AFTER `internal_note`, 
            ADD `online_meeting_id` VARCHAR(255) NULL DEFAULT NULL AFTER `online_meeting_provider`, 
            ADD `online_meeting_data` TEXT NULL DEFAULT NULL AFTER `online_meeting_id`");

        $wpdb->query("ALTER TABLE `". Tables\Employee::get_table_name() ."` 
            ADD `zoom_api_key` VARCHAR(255) NULL DEFAULT NULL AFTER `position`, 
            ADD `zoom_api_secret` VARCHAR(255) NULL DEFAULT NULL AFTER `zoom_api_key`");
    }

    /**
     * Version 4.3
     */
    function version_4_3()
    {
        update_option('bookme_stripe_notice',1);
    }

    /**
     * Version 4.0
     * Back up old bookme data
     */
    function version_4_0()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        // check if the function already run
        if (!(int)get_option('bookme_processed_backup', 0)) {

            // check if old bookme tables available
            if ($wpdb->get_var("SHOW TABLES LIKE '" . $this->get_table_name('bookme_service') . "'") == $this->get_table_name('bookme_service')) {
                // categories
                $categories = $wpdb->get_results('SELECT * FROM `' . $this->get_table_name('bookme_category') . '`', ARRAY_A);
                foreach ($categories as $category) {
                    $wpdb->insert(
                        $this->get_table_name('bm_categories'),
                        array(
                            'id' => $category['id'],
                            'name' => $category['name'],
                        )
                    );
                }

                // employees
                $employees = $wpdb->get_results('SELECT * FROM `' . $this->get_table_name('bookme_employee') . '`', ARRAY_A);
                foreach ($employees as $employee) {
                    $attachment_id = attachment_url_to_postid($employee['img']);
                    $wpdb->insert(
                        $this->get_table_name('bm_employees'),
                        array(
                            'id' => $employee['id'],
                            'full_name' => $employee['name'],
                            'email' => $employee['email'],
                            'phone' => $employee['phone'],
                            'info' => $employee['info'],
                            'attachment_id' => $attachment_id,
                            'google_data' => $employee['google_data'],
                            'visibility' => (int)$employee['visibility'] == 0 ? 'private' : 'public',
                        )
                    );

                    // employee schedule
                    foreach (array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') as $day_index => $week_day) {
                        $schedule = $wpdb->get_row(
                            $wpdb->prepare(
                                'SELECT * FROM `' . $this->get_table_name('bookme_member_schedule') . '` 
                        WHERE `emp_id` = %d AND `day` = %s',
                                $employee['id'], $week_day),
                            ARRAY_A);

                        $start = $end = null;

                        if (!empty($schedule['schedule_start'])) {
                            $start = date('H:i:s', strtotime($schedule['schedule_start']));
                        }
                        if (!empty($schedule['schedule_end'])) {
                            $end = date('H:i:s', strtotime($schedule['schedule_end']));
                        }

                        $item = new Inc\Mains\Tables\EmployeeSchedule();
                        $item->set_staff_id($employee['id'])
                            ->set_day_index($day_index + 1)
                            ->set_start_time($start)
                            ->set_end_time($end)
                            ->save();

                        // employee break
                        if (!empty($schedule['break_start'])) {
                            $start = date('H:i:s', strtotime($schedule['break_start']));
                            $end = date('H:i:s', strtotime($schedule['break_end']));

                            $item = new Inc\Mains\Tables\EmployeeScheduleBreak();
                            $item->set_staff_schedule_id($item->get_id())
                                ->set_start_time($start)
                                ->set_end_time($end)
                                ->save();
                        }
                    }
                }

                // services
                $services = $wpdb->get_results('SELECT * FROM `' . $this->get_table_name('bookme_service') . '`', ARRAY_A);
                foreach ($services as $service) {
                    $wpdb->insert(
                        $this->get_table_name('bm_services'),
                        array(
                            'id' => $service['id'],
                            'category_id' => $service['catId'],
                            'title' => $service['name'],
                            'duration' => $service['duration'],
                            'price' => $service['price'],
                            'capacity_min' => $service['capacity'],
                            'capacity_max' => $service['capacity'],
                            'padding_left' => $service['paddingBefore'],
                            'info' => $service['description'],
                            'visibility' => (int)$service['visibility'] == 0 ? 'private' : 'public',
                        )
                    );

                    $staff = explode(',', $service['staff']);
                    foreach ($staff as $staff_id) {
                        // employee services
                        $wpdb->insert(
                            $this->get_table_name('bm_employee_services'),
                            array(
                                'staff_id' => $staff_id,
                                'service_id' => $service['id'],
                                'price' => $service['price'],
                                'capacity_min' => $service['capacity'],
                                'capacity_max' => $service['capacity']
                            )
                        );
                    }
                }

                // coupons
                $coupons = $wpdb->get_results('SELECT * FROM `' . $this->get_table_name('bookme_coupons') . '`', ARRAY_A);
                foreach ($coupons as $coupon) {
                    $wpdb->insert(
                        $this->get_table_name('bm_coupons'),
                        array(
                            'id' => $coupon['id'],
                            'code' => $coupon['coupon_code'],
                            'discount' => $coupon['discount'],
                            'deduction' => $coupon['deduction'],
                            'usage_limit' => $coupon['usage_limit'],
                            'used' => $coupon['coupon_used_limit'],
                        )
                    );

                    $coupon_services = explode(',', $coupon['ser_id']);
                    foreach ($coupon_services as $service_id) {
                        // coupon services
                        $wpdb->insert(
                            $this->get_table_name('bm_coupons_to_services'),
                            array(
                                'coupon_id' => $coupon['id'],
                                'service_id' => $service_id,
                            )
                        );
                    }
                }

                // customers
                $customers = $wpdb->get_results('SELECT * FROM `' . $this->get_table_name('bookme_customers') . '`', ARRAY_A);
                foreach ($customers as $customer) {
                    $full_name = explode(' ', $customer['name'], 2);
                    $wpdb->insert(
                        $this->get_table_name('bm_customers'),
                        array(
                            'id' => $customer['id'],
                            'full_name' => $customer['name'],
                            'first_name' => $full_name[0],
                            'last_name' => isset ($full_name[1]) ? trim($full_name[1]) : '',
                            'phone' => $customer['phone'],
                            'email' => $customer['email'],
                        )
                    );
                }

                // custom fields
                $custom_field_data = json_decode(get_option('bookme_custom_fields', '[{"type":"textarea","label":'
                    . json_encode(esc_html__('Notes', 'bookme')) . ',"required":false,"id":1,"services":[]}]'), true);
                $custom_fields = $wpdb->get_results('SELECT * FROM `' . $this->get_table_name('bookme_custom_field') . '` ORDER BY position', ARRAY_A);
                foreach ($custom_fields as $custom_field) {
                    $type = null;
                    switch ($custom_field['field_type']) {
                        case 'textField':
                            $type = "text-field";
                            break;
                        case 'textArea':
                            $type = "textarea";
                            break;
                        case 'textContent':
                            $type = "text-content";
                            break;
                        case 'checkboxGroup':
                            $type = "checkboxes";
                            break;
                        case 'radioGroup':
                            $type = "radio-buttons";
                            break;
                        case 'dropDown':
                            $type = "drop-down";
                            break;
                    }
                    if ($type) {
                        $field = array(
                            'id' => $custom_field['id'] + 1,
                            'label' => $custom_field['field_name'],
                            'required' => (bool)$custom_field['required'],
                            'type' => $type,
                            'services' => array()
                        );
                        if ($type = "drop-down" || $type = "radio-buttons" || $type = "checkboxes") {
                            $custom_field_items = array();
                            $items = $wpdb->get_results("SELECT * FROM `" . $this->get_table_name('bookme_custom_field') . "` where associate_with=" . $custom_field['id'], ARRAY_A);
                            foreach ($items as $item) {
                                $custom_field_items[] = $item['field_name'];
                            }
                            $field['items'] = $custom_field_items;
                        }
                        $custom_field_data[] = $field;
                    }
                }
                update_option('bookme_custom_fields', json_encode($custom_field_data));


                // bookings
                $bookings = $wpdb->get_results('SELECT * FROM `' . $this->get_table_name('bookme_current_booking') . '`', ARRAY_A);
                foreach ($bookings as $booking) {
                    $start = new \DateTime($booking['date'] . ' ' . $booking['time']);
                    $end = clone $start;
                    $end->modify($booking['duration'] . ' seconds');
                    $wpdb->insert(
                        $this->get_table_name('bm_bookings'),
                        array(
                            'id' => $booking['id'],
                            'staff_id' => $booking['emp_id'],
                            'service_id' => $booking['ser_id'],
                            'start_date' => $start->format('Y-m-d H:i:s'),
                            'end_date' => $end->format('Y-m-d H:i:s'),
                            'google_event_id' => $booking['google_event_id'],
                        )
                    );
                }

                // payments
                $payments = $wpdb->get_results('SELECT * FROM `' . $this->get_table_name('bookme_payments') . '`', ARRAY_A);
                foreach ($payments as $payment) {
                    $type = Inc\Mains\Tables\Payment::TYPE_LOCAL;
                    switch ($payment['type']) {
                        case 'Paypal':
                            $type = Inc\Mains\Tables\Payment::TYPE_PAYPAL;
                            break;
                        case 'Stripe':
                            $type = Inc\Mains\Tables\Payment::TYPE_STRIPE;
                            break;
                        case 'WooCommerce':
                            $type = Inc\Mains\Tables\Payment::TYPE_WOOCOMMERCE;
                            break;
                    }
                    $wpdb->insert(
                        $this->get_table_name('bm_payments'),
                        array(
                            'id' => $payment['id'],
                            'type' => $type,
                            'total' => $payment['price'],
                            'paid' => $payment['status'] == 'pending'
                                ? 0.00
                                : $payment['price'],
                            'paid_type' => Inc\Mains\Tables\Payment::PAY_IN_FULL,
                            'status' => $payment['status'] == 'pending'
                                ? Inc\Mains\Tables\Payment::STATUS_PENDING
                                : Inc\Mains\Tables\Payment::STATUS_COMPLETED,
                            'created' => $payment['created']
                        )
                    );
                }

                // custom bookings
                $customer_bookings = $wpdb->get_results('SELECT * FROM `' . $this->get_table_name('bookme_customer_booking') . '`', ARRAY_A);
                foreach ($customer_bookings as $cb) {
                    $custom_field_array = array();
                    $custom_field_values = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . $this->get_table_name('bookme_current_booking_fields') . '` WHERE booking_id = %d', $cb['booking_id']), ARRAY_A);
                    foreach ($custom_field_values as $values) {
                        $custom_field_data = json_decode(get_option('bookme_custom_fields', '[]'), true);
                        foreach ($custom_field_data as $array) {
                            if ($array['label'] == $values['key_field']) {
                                $custom_field_array[] = array('id' => $array['id'], 'value' => $values['field_val']);
                            }
                        }
                    }
                    $wpdb->insert(
                        $this->get_table_name('bm_customer_bookings'),
                        array(
                            'id' => $cb['id'],
                            'customer_id' => $cb['customer_id'],
                            'booking_id' => $cb['booking_id'],
                            'payment_id' => $cb['payment_id'] ? $cb['payment_id'] : null,
                            'number_of_persons' => $cb['no_of_person'],
                            'custom_fields' => json_encode($custom_field_array),
                            'status' => Inc\Mains\Tables\CustomerBooking::STATUS_APPROVED,
                            'token' => Inc\Mains\Functions\System::generate_token(Inc\Mains\Tables\CustomerBooking::class, 'token'),
                            'created' => current_time('mysql'),
                        )
                    );

                    // set details for payment
                    if ($cb['payment_id']) {
                        $payment = new Inc\Mains\Tables\Payment();
                        if ($payment->load($cb['payment_id'])) {
                            $payment
                                ->set_details(Inc\Mains\Booking\DataHolders\Order::create_from_payment($payment))
                                ->save();
                        }
                    }
                }

                // holidays
                $holidays = $wpdb->get_results('SELECT * FROM `' . $this->get_table_name('bookme_holidays') . '`', ARRAY_A);
                foreach ($holidays as $holiday) {
                    $wpdb->insert(
                        $this->get_table_name('bm_holidays'),
                        array(
                            'id' => $holiday['id'],
                            'staff_id' => $holiday['staff_id'],
                            'date' => $holiday['holi_date'],
                            'repeat_event' => $holiday['repeat_day'],
                        )
                    );
                }

                // settings
                $settings = $wpdb->get_results('SELECT * FROM `' . $this->get_table_name('bookme_settings') . '`', ARRAY_A);
                foreach ($settings as $setting) {
                    switch ($setting['book_key']) {
                        case 'companyName':
                            update_option('bookme_company_name', $setting['book_value']);
                            break;
                        case 'companyAddress':
                            update_option('bookme_company_address', $setting['book_value']);
                            break;
                        case 'companyPhone':
                            update_option('bookme_company_phone', $setting['book_value']);
                            break;
                        case 'companyWebsite':
                            update_option('bookme_company_website', $setting['book_value']);
                            break;
                        case 'payment_pmt':
                            update_option('bookme_company_name', $setting['book_value']);
                            break;
                        case 'bookme_enable_cart':
                            update_option('bookme_cart_enabled', (int)$setting['book_value']);
                            break;
                        case 'bookme_email_sender_name':
                            update_option('bookme_email_sender_name', $setting['book_value']);
                            break;
                        case 'bookme_email_sender_email':
                            update_option('bookme_email_sender', $setting['book_value']);
                            break;
                        case 'enable_coupan':
                            update_option('bookme_coupons_enabled', $setting['book_value'] == "Yes" ? 1 : 0);
                            break;
                        case 'bookmeDayLimit':
                            update_option('bookme_max_days_for_booking', (int)$setting['book_value']);
                            break;
                        case 'enable_woocommerce':
                            update_option('bookme_wc_enabled', (int)$setting['book_value']);
                            break;
                        case 'woocommerce_product':
                            update_option('bookme_wc_product', (int)$setting['book_value']);
                            break;
                        case 'woocommerce_cart_data':
                            update_option('bookme_lang_wc_cart_data_name', $setting['book_value']);
                            do_action('wpml_register_single_string', 'bookme', 'bookme_lang_wc_cart_data_name', $setting['book_value']);
                            break;
                        case 'woocommerce_cart_data_text':
                            update_option('bookme_lang_wc_cart_data_value', $setting['book_value']);
                            do_action('wpml_register_single_string', 'bookme', 'bookme_lang_wc_cart_data_value', $setting['book_value']);
                            break;
                        case 'bookme_gc_client_id':
                            update_option('bookme_gc_client_id', $setting['book_value']);
                            break;
                        case 'bookme_gc_client_secret':
                            update_option('bookme_gc_client_secret', $setting['book_value']);
                            break;
                        case 'bookme_gc_2_way_sync':
                            update_option('bookme_gc_remove_busy_slots', $setting['book_value']);
                            break;
                        case 'bookme_gc_limit_events':
                            update_option('bookme_gc_limit_events', $setting['book_value']);
                            break;
                        case 'bookme_gc_event_title':
                            update_option('bookme_gc_event_title', $setting['book_value']);
                            break;
                        case 'pmt_currency':
                            update_option('bookme_currency', $setting['book_value']);
                            break;
                        case 'pmt_local':
                            update_option('bookme_local_enabled', $setting['book_value'] == "enabled" ? 1 : 0);
                            break;
                        case 'pmt_paypal':
                            update_option('bookme_paypal_enabled', $setting['book_value']);
                            break;
                        case 'pmt_paypal_api_username':
                            update_option('bookme_paypal_api_username', $setting['book_value']);
                            break;
                        case 'pmt_paypal_api_password':
                            update_option('bookme_paypal_api_password', $setting['book_value']);
                            break;
                        case 'pmt_paypal_api_signature':
                            update_option('bookme_paypal_api_signature', $setting['book_value']);
                            break;
                        case 'pmt_paypal_sandbox':
                            update_option('bookme_paypal_sandbox', $setting['book_value'] == "yes" ? 1 : 0);
                            break;
                        case 'pmt_stripe':
                            update_option('bookme_stripe_enabled', $setting['book_value'] == "enabled" ? 1 : "disabled");
                            break;
                        case 'pmt_stripe_secret_key':
                            update_option('bookme_stripe_secret_key', $setting['book_value']);
                            break;
                    }
                }

                // appearance
                $settings = $wpdb->get_results('SELECT * FROM `' . $this->get_table_name('bookme_appearance') . '`', ARRAY_A);
                foreach ($settings as $setting) {
                    switch ($setting['label_key']) {
                        case 'booking_color':
                            update_option('bookme_primary_color', $setting['label_value']);
                            break;
                        case 'booking_colortxt':
                            update_option('bookme_secondary_color', $setting['label_value']);
                            break;
                        case 'bullet1':
                            update_option('bookme_lang_step_service', $setting['label_value']);
                            do_action('wpml_register_single_string', 'bookme', 'bookme_lang_step_service', $setting['label_value']);
                            break;
                        case 'bullet2':
                            update_option('bookme_lang_step_time', $setting['label_value']);
                            do_action('wpml_register_single_string', 'bookme', 'bookme_lang_step_time', $setting['label_value']);
                            break;
                        case 'bullet_cart':
                            update_option('bookme_lang_step_cart', $setting['label_value']);
                            do_action('wpml_register_single_string', 'bookme', 'bookme_lang_step_cart', $setting['label_value']);
                            break;
                        case 'bullet3':
                            update_option('bookme_lang_step_details', $setting['label_value']);
                            do_action('wpml_register_single_string', 'bookme', 'bookme_lang_step_details', $setting['label_value']);
                            break;
                        case 'bullet5':
                            update_option('bookme_lang_step_done', $setting['label_value']);
                            do_action('wpml_register_single_string', 'bookme', 'bookme_lang_step_done', $setting['label_value']);
                            break;
                        case 'category':
                            update_option('bookme_lang_title_category', $setting['label_value']);
                            do_action('wpml_register_single_string', 'bookme', 'bookme_lang_title_category', $setting['label_value']);
                            break;
                        case 'service':
                            update_option('bookme_lang_title_service', $setting['label_value']);
                            do_action('wpml_register_single_string', 'bookme', 'bookme_lang_title_service', $setting['label_value']);
                            break;
                        case 'employee':
                            update_option('bookme_lang_title_employee', $setting['label_value']);
                            do_action('wpml_register_single_string', 'bookme', 'bookme_lang_title_employee', $setting['label_value']);
                            break;
                        case 'number_of_person':
                            update_option('bookme_lang_title_number_of_persons', $setting['label_value']);
                            do_action('wpml_register_single_string', 'bookme', 'bookme_lang_title_number_of_persons', $setting['label_value']);
                            break;
                    }
                }
            }

            update_option('bookme_processed_backup', 1);
        }
    }
}