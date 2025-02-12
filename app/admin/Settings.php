<?php

namespace Bookme\App\Admin;

use Bookme\Inc;
use Bookme\Inc\Mains\Functions\Request;
use Bookme\Inc\Mains\Functions\System;

/**
 * Class Settings
 */
class Settings extends Inc\Core\App
{

    const page_slug = 'bookme-settings';

    /**
     * execute page.
     */
    public function execute()
    {
        $assets = BOOKME_URL . 'assets/admin/';
        $public_assets = BOOKME_URL . 'assets/front/';

        wp_enqueue_media();
        wp_enqueue_style('bookme-intlTelInput', $public_assets . 'css/intlTelInput.css', array(), BOOKME_VERSION);

        Fragments::enqueue_global();

        wp_enqueue_script('bookme-intlTelInput-js', $public_assets . 'js/intlTelInput.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-settings', $assets . 'js/pages/settings.js', array('jquery', 'bookme-intlTelInput-js', 'jquery-ui-sortable'), BOOKME_VERSION);

        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        $current_tab = Request::has_parameter('tab') ? Request::get_parameter('tab') : 'general';

        wp_localize_script('bookme-settings', 'Bookme', array(
            'csrf_token' => System::get_security_token(),
            'current_tab' => $current_tab,
            'sample_price' => number_format_i18n(10, 3),
            'default_country' => get_option('bookme_phone_default_country'),
            'start_of_week' => get_option('start_of_week'),
            'days' => array_values($wp_locale->weekday_abbrev),
            'months' => array_values($wp_locale->month),
            'saved' => esc_html__('Settings have been saved.', 'bookme'),
            'repeat' => esc_html__('Repeat every year', 'bookme'),
            'we_are_not_working' => esc_html__('We are not working on this day', 'bookme'),
            'intlTelInput' => array(
                'enabled' => get_option('bookme_phone_default_country') != 'disabled',
                'utils' => $public_assets . 'js/intlTelInput.utils.js',
                'country' => get_option('bookme_phone_default_country') != 'disabled' ? get_option('bookme_phone_default_country') : ""
            ),
        ));

        $wc_products = $this->get_wc_products();
        $wc_error = null;

        // Check if WooCommerce cart exists.
        if (get_option('bookme_wc_enabled') && class_exists('WooCommerce', false)) {
            $post = get_post(wc_get_page_id('cart'));
            if ($post === null || $post->post_status != 'publish') {
                $wc_error = sprintf(
                    __('WooCommerce cart is not set up. Click <a href="%s">here</a> to set it up.', 'bookme'),
                    System::esc_admin_url('wc-status', array('tab' => 'tools'))
                );
            }
        }

        $cart_columns = array(
            'date' => esc_html__('Date', 'bookme'),
            'time' => esc_html__('Time', 'bookme'),
            'service' => System::get_translated_option('bookme_lang_title_service'),
            'employee' => System::get_translated_option('bookme_lang_title_employee'),
            'price' => esc_html__('Price', 'bookme')
        );

        $wp_roles = new \WP_Roles();
        $new_user_roles = array();
        foreach ($wp_roles->get_names() as $role => $name) {
            $new_user_roles[] = array($role, $name);
        }

        $holidays = $this->get_holidays();

        $payments = array();
        $default_payment_order = array(
            Inc\Mains\Tables\Payment::TYPE_LOCAL,
            Inc\Mains\Tables\Payment::TYPE_PAYPAL,
            Inc\Mains\Tables\Payment::TYPE_STRIPE,
            Inc\Mains\Tables\Payment::TYPE_RAZORPAY,
            Inc\Mains\Tables\Payment::TYPE_2CHECKOUT,
            Inc\Mains\Tables\Payment::TYPE_AUTHORIZENET,
            Inc\Mains\Tables\Payment::TYPE_MOLLIE
        );
        $payment_order = explode(',', get_option('bookme_payment_order') );
        foreach ( $payment_order as $payment_gateways ) {
            if ( in_array( $payment_gateways, $default_payment_order ) ) {
                $payments[] = $payment_gateways;
            }
        }
        foreach ( $default_payment_order as $payment_gateways ) {
            if ( ! $payment_order || ! in_array( $payment_gateways, $payment_order ) ) {
                $payments[] = $payment_gateways;
            }
        }
        Inc\Core\Template::create('settings/page')->display(compact('new_user_roles', 'wc_products', 'wc_error', 'cart_columns', 'holidays', 'payments'));
    }

    /**
     * Update settings
     */
    public function perform_update_settings()
    {
        $alert = array(
            'success' => esc_html__('Settings have been saved.', 'bookme'),
            'error' => ''
        );
        switch (Request::get_parameter('tab')) {
            case 'general':  // General form.
                $bookme_time_slot_step = Request::get_parameter('bookme_time_slot_step');
                if (in_array($bookme_time_slot_step, array(1, 2, 5, 10, 12, 15, 20, 30, 45, 60, 90, 120, 150, 180, 210, 240, 360, 480))) {
                    update_option('bookme_time_slot_step', $bookme_time_slot_step);
                }
                update_option('bookme_service_duration_as_slot_step', (int)Request::get_parameter('bookme_service_duration_as_slot_step'));
                update_option('bookme_use_client_time_zone', (int)Request::get_parameter('bookme_use_client_time_zone'));
                update_option('bookme_hide_booking_end_time', (int)Request::get_parameter('bookme_hide_booking_end_time'));
                update_option('bookme_allow_staff_edit_profile', (int)Request::get_parameter('bookme_allow_staff_edit_profile'));
                update_option('bookme_required_employee', (int)Request::get_parameter('bookme_required_employee'));
                update_option('bookme_default_booking_status', Request::get_parameter('bookme_default_booking_status'));
                update_option('bookme_max_days_for_booking', (int)Request::get_parameter('bookme_max_days_for_booking'));
                update_option('bookme_min_time_before_booking', Request::get_parameter('bookme_min_time_before_booking'));
                update_option('bookme_min_time_before_cancel', Request::get_parameter('bookme_min_time_before_cancel'));
                update_option('bookme_phone_default_country', Request::get_parameter('bookme_phone_default_country'));
                update_option('bookme_default_country_code', Request::get_parameter('bookme_default_country_code'));
                update_option('bookme_final_step_url', Request::get_parameter('bookme_final_step_url'));
                break;

            case 'customers':  // Customers form.
                update_option('bookme_customer_create_account', Request::get_parameter('bookme_customer_create_account'));
                update_option('bookme_customer_new_account_role', Request::get_parameter('bookme_customer_new_account_role'));
                update_option('bookme_customer_save_in_cookie', Request::get_parameter('bookme_customer_save_in_cookie'));
                update_option('bookme_customer_first_last_name', Request::get_parameter('bookme_customer_first_last_name'));
                update_option('bookme_customer_show_login_button', Request::get_parameter('bookme_customer_show_login_button'));
                update_option('bookme_customer_required_phone', Request::get_parameter('bookme_customer_required_phone'));
                update_option('bookme_customer_allow_duplicate', Request::get_parameter('bookme_customer_allow_duplicate'));
                break;

            case 'cart':  // Cart form.
                update_option('bookme_cart_enabled', (int)Request::get_parameter('bookme_cart_enabled'));
                update_option('bookme_cart_columns', Request::get_parameter('bookme_cart_columns', array()));
                break;

            case 'company':  // Company form.
                update_option('bookme_company_logo_id', Request::get_parameter('bookme_company_logo_id'));
                update_option('bookme_company_name', Request::get_parameter('bookme_company_name'));
                update_option('bookme_company_phone', Request::get_parameter('bookme_company_phone'));
                update_option('bookme_company_website', Request::get_parameter('bookme_company_website'));
                update_option('bookme_company_address', Request::get_parameter('bookme_company_address'));
                break;

            case 'google_calendar':  // Google calendar form.
                update_option('bookme_gc_client_id', Request::get_parameter('bookme_gc_client_id'));
                update_option('bookme_gc_client_secret', Request::get_parameter('bookme_gc_client_secret'));
                update_option('bookme_gc_remove_busy_slots', Request::get_parameter('bookme_gc_remove_busy_slots'));
                update_option('bookme_gc_limit_events', Request::get_parameter('bookme_gc_limit_events'));
                update_option('bookme_gc_event_title', Request::get_parameter('bookme_gc_event_title'));
                break;

            case 'google_recaptcha':  // Google reCAPTCHA form.
                update_option('bookme_captcha_enabled', (int)Request::get_parameter('bookme_captcha_enabled'));
                update_option('bookme_captcha_site_key', Request::get_parameter('bookme_captcha_site_key'));
                update_option('bookme_captcha_secret_key', Request::get_parameter('bookme_captcha_secret_key'));
                break;

            case 'online_meeting':  // Online Meeting form.
                update_option('bookme_zoom_enabled', (int)Request::get_parameter('bookme_zoom_enabled'));
                update_option('bookme_zoom_api_key', Request::get_parameter('bookme_zoom_api_key'));
                update_option('bookme_zoom_api_secret', Request::get_parameter('bookme_zoom_api_secret'));
                break;

            case 'woo_commerce':  // WooCommerce form.
                update_option('bookme_wc_enabled', Request::get_parameter('bookme_wc_enabled'));
                update_option('bookme_wc_product', Request::get_parameter('bookme_wc_product'));

                update_option('bookme_lang_wc_cart_data_name', Request::get_parameter('bookme_lang_wc_cart_data_name'));
                do_action('wpml_register_single_string', 'bookme', 'bookme_lang_wc_cart_data_name', Request::get_parameter('bookme_lang_wc_cart_data_name'));

                update_option('bookme_lang_wc_cart_data_value', Request::get_parameter('bookme_lang_wc_cart_data_value'));
                do_action('wpml_register_single_string', 'bookme', 'bookme_lang_wc_cart_data_value', Request::get_parameter('bookme_lang_wc_cart_data_value'));
                break;

            case 'payments':  // Payments form.
                $options = array(
                    'bookme_currency',
                    'bookme_price_format',
                    'bookme_coupons_enabled',
                    'bookme_local_enabled',
                    'bookme_paypal_enabled',
                    'bookme_paypal_api_username',
                    'bookme_paypal_api_password',
                    'bookme_paypal_api_signature',
                    'bookme_paypal_sandbox',
                    'bookme_stripe_enabled',
                    'bookme_stripe_secret_key',
                    'bookme_stripe_publishable_key',
                    'bookme_razorpay_enabled',
                    'bookme_razorpay_api_key',
                    'bookme_razorpay_secret_key',
                    'bookme_2checkout_enabled',
                    'bookme_2checkout_sandbox',
                    'bookme_2checkout_api_seller_id',
                    'bookme_2checkout_api_secret_word',
                    'bookme_authorize_net_enabled',
                    'bookme_authorize_net_api_login_id',
                    'bookme_authorize_net_transaction_key',
                    'bookme_authorize_net_sandbox',
                    'bookme_mollie_enabled',
                    'bookme_mollie_api_key',
                    'bookme_payment_order'
                );
                foreach ($options as $option) {
                    update_option($option, Request::get_parameter($option));
                }
                break;

            case 'notifications': // Notifications settings form.
                update_option('bookme_email_sender', Request::get_parameter('bookme_email_sender'));
                update_option('bookme_email_sender_name', Request::get_parameter('bookme_email_sender_name'));
                update_option('bookme_email_send_as', Request::get_parameter('bookme_email_send_as'));
                update_option('bookme_email_reply_to_customers', Request::get_parameter('bookme_email_reply_to_customers'));
                update_option('bookme_twillio_account_sid', Request::get_parameter('bookme_twillio_account_sid'));
                update_option('bookme_twillio_auth_token', Request::get_parameter('bookme_twillio_auth_token'));
                update_option('bookme_twillio_phone_number', Request::get_parameter('bookme_twillio_phone_number'));
                update_option('bookme_sms_admin_phone', Request::get_parameter('bookme_sms_admin_phone'));
                update_option('bookme_combined_notifications', Request::get_parameter('bookme_combined_notifications'));
                update_option('bookme_approve_success_url', Request::get_parameter('bookme_approve_success_url'));
                update_option('bookme_approve_unsuccess_url', Request::get_parameter('bookme_approve_unsuccess_url'));
                update_option('bookme_cancel_success_url', Request::get_parameter('bookme_cancel_success_url'));
                update_option('bookme_cancel_unsuccess_url', Request::get_parameter('bookme_cancel_unsuccess_url'));
                update_option('bookme_reject_success_url', Request::get_parameter('bookme_reject_success_url'));
                update_option('bookme_reject_unsuccess_url', Request::get_parameter('bookme_reject_unsuccess_url'));
                break;

            case 'working_hours':  // Working hours form
                $options = array(
                    'bookme_wh_monday_start',
                    'bookme_wh_monday_end',
                    'bookme_wh_tuesday_start',
                    'bookme_wh_tuesday_end',
                    'bookme_wh_wednesday_start',
                    'bookme_wh_wednesday_end',
                    'bookme_wh_thursday_start',
                    'bookme_wh_thursday_end',
                    'bookme_wh_friday_start',
                    'bookme_wh_friday_end',
                    'bookme_wh_saturday_start',
                    'bookme_wh_saturday_end',
                    'bookme_wh_sunday_start',
                    'bookme_wh_sunday_end',
                );
                foreach ($options as $option) {
                    update_option($option, Request::get_parameter($option));
                }
                break;
            case 'labels':  // labels form
                $options = array(
                    // progress bar
                    'bookme_lang_step_service',
                    'bookme_lang_step_time',
                    'bookme_lang_step_cart',
                    'bookme_lang_step_details',
                    'bookme_lang_step_done',
                    // category
                    'bookme_lang_title_category',
                    'bookme_lang_select_category',
                    // service
                    'bookme_lang_title_service',
                    'bookme_lang_select_service',
                    'bookme_lang_required_service',
                    // employee
                    'bookme_lang_title_employee',
                    'bookme_lang_select_employee',
                    'bookme_lang_required_employee',
                    // no of persons
                    'bookme_lang_title_number_of_persons',
                );
                foreach ($options as $option) {
                    update_option($option, Request::get_parameter($option));
                    do_action('wpml_register_single_string', 'bookme', $option, Request::get_parameter($option));
                }
                break;

            case 'purchase_code':  // check purchase code
                if ($result = Inc\Mains\API::check_purchase_code(Request::get_parameter('bookme_purchase_code'))) {
                    if ($result['success']) {
                        $staff_path = BOOKME_PATH . '/templates/admin/employees/';
                        $randomName = System::generate_random_string();
                        if ($already_file = get_option('bookme_secret_file')) {
                            if (file_exists($staff_path . $already_file. '.php')) {
                                if (file_put_contents($staff_path . $already_file . '.php', $result['data'])) {
                                    update_option('bookme_purchase_code', Request::get_parameter('bookme_purchase_code'));
                                    $alert['success'] = esc_html__('Purchase code verified successfully.', 'bookme');
                                } else {
                                    $alert['error'] = esc_html__('Some error occurred, Please try again.', 'bookme');
                                }
                            } else {
                                if (file_put_contents($staff_path . $randomName . '.php', $result['data'])) {
                                    update_option('bookme_purchase_code', Request::get_parameter('bookme_purchase_code'));
                                    update_option('bookme_secret_file', $randomName);
                                    $alert['success'] = esc_html__('Purchase code verified successfully.', 'bookme');
                                } else {
                                    $alert['error'] = esc_html__('Some error occurred, Please try again.', 'bookme');
                                }
                            }
                        } else {
                            if (file_put_contents($staff_path . $randomName . '.php', $result['data'])) {
                                update_option('bookme_purchase_code', Request::get_parameter('bookme_purchase_code'));
                                update_option('bookme_secret_file', $randomName);
                                $alert['success'] = esc_html__('Purchase code verified successfully.', 'bookme');
                            } else {
                                $alert['error'] = esc_html__('Some error occurred, Please try again.', 'bookme');
                            }
                        }
                    } else {
                        $alert['error'] = $result['error'];
                    }
                } else {
                    $alert['error'] = esc_html__('Some error occurred, Please try again.', 'bookme');
                }
                break;
        }
        if(!empty($alert['error'])){
            wp_send_json_error($alert);
        }else{
            wp_send_json_success($alert);
        }
    }

    /**
     * Save company holidays
     */
    public function perform_company_holidays()
    {
        global $wpdb;

        $id = Request::get_parameter('id', false);
        $day = Request::get_parameter('day', false);
        $holiday = Request::get_parameter('holiday') == 'true';
        $repeat = (int)(Request::get_parameter('repeat') == 'true');

        // update or delete holiday
        if ($id) {
            if ($holiday) {
                $wpdb->update(
                    Inc\Mains\Tables\Holiday::get_table_name(),
                    array('repeat_event' => $repeat),
                    array('id' => $id),
                    array('%d')
                );
                $wpdb->update(
                    Inc\Mains\Tables\Holiday::get_table_name(),
                    array('repeat_event' => $repeat),
                    array('parent_id' => $id),
                    array('%d')
                );
            } else {
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM `" . Inc\Mains\Tables\Holiday::get_table_name() . "` 
                        WHERE `id` = %d OR `parent_id` = %d",
                        $id, $id
                    )
                );
            }
            // add the new event
        } elseif ($holiday && $day) {
            list ($d, $m, $Y) = explode('-', $day);
            $day = $Y . '-' . $m . '-' . $d;
            $holiday = new Inc\Mains\Tables\Holiday();
            $holiday
                ->set_date($day)
                ->set_repeat_event($repeat)
                ->save();
            $employees = $wpdb->get_results('SELECT * FROM ' . Inc\Mains\Tables\Employee::get_table_name(), ARRAY_A);
            foreach ($employees as $employee) {
                $staff_holiday = new Inc\Mains\Tables\Holiday();
                $staff_holiday
                    ->set_date($day)
                    ->set_repeat_event($repeat)
                    ->set_staff_id($employee['id'])
                    ->set_parent($holiday)
                    ->save();
            }
        }

        // and return refreshed events
        echo json_encode($this->get_holidays());
        exit;
    }

    /**
     * Get company holidays
     * @return array
     */
    protected function get_holidays()
    {
        global $wpdb;
        $data = $wpdb->get_results(
            "SELECT * FROM `" . Inc\Mains\Tables\Holiday::get_table_name() . "` 
                    WHERE staff_id IS NULL",
            ARRAY_A);
        $holidays = array();
        if (count($data)) {
            foreach ($data as $holiday) {
                $holidays[$holiday['id']] = array(
                    'm' => (int)date('m', strtotime($holiday['date'])),
                    'd' => (int)date('d', strtotime($holiday['date'])),
                );
                // If not repeated holiday, add the year
                if (!$holiday['repeat_event']) {
                    $holidays[$holiday['id']]['y'] = (int)date('Y', strtotime($holiday['date']));
                }
            }
        }

        return $holidays;
    }

    /**
     * Get WooCommerce products
     * @return array
     */
    private function get_wc_products()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        $data = array(array('id' => 0, 'name' => esc_html__('Select product', 'bookme')));
        $query = 'SELECT ID, post_title FROM ' . $wpdb->posts . ' WHERE post_type = \'product\' AND post_status = \'publish\' ORDER BY post_title';
        $products = $wpdb->get_results($query);

        foreach ($products as $product) {
            $data[] = array('id' => $product->ID, 'name' => $product->post_title);
        }

        return $data;
    }
}