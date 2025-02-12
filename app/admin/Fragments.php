<?php

namespace Bookme\App\Admin;

use Bookme\Inc;

/**
 * Class Fragments
 */
class Fragments
{

    public static function enqueue_global()
    {
        $assets = BOOKME_URL . 'assets/admin/';

        wp_enqueue_style('bookme-feather-icon', $assets . 'icons/css/feather-icon.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-bootstrap', $assets . 'css/bootstrap.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-style', $assets . 'css/style.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-custom', $assets . 'css/custom.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-responsive', $assets . 'css/responsive.css', array(), BOOKME_VERSION);
        if (is_rtl())
            wp_enqueue_style('bookme-rtl', $assets . 'css/rtl.css', array(), BOOKME_VERSION);

        wp_enqueue_script('bookme-popper', $assets . 'js/popper.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-bootstrap', $assets . 'js/bootstrap.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-tippy', $assets . 'js/tippy.all.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-sidebar-menu', $assets . 'js/sidebar-menu.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-script', $assets . 'js/script.js', array('jquery'), BOOKME_VERSION);

        wp_localize_script('bookme-script', 'BookmeScript', array(
            'success' => esc_html__('Success', 'bookme'),
            'error' => esc_html__('Error', 'bookme')
        ));
    }

    /**
     * Render header
     */
    public static function render_header()
    {
        Inc\Core\Template::create('fragments/header')->display();
    }

    /**
     * Render Sidebar Menu
     * @param string $page
     */
    public static function render_sidebar_menu($page = null)
    {
        wp_localize_script('bookme-sidebar-menu', 'BookmeMenu', array(
            'page' => $page
        ));
        Inc\Core\Template::create('fragments/sidebar-menu')->display();
    }

    /**
     * Render Footer
     */
    public static function render_footer()
    {
        Inc\Core\Template::create('fragments/footer')->display();
    }

    /**
     * Render Booking Sidepanel
     */
    public static function render_booking_panel()
    {
        global $wp_locale;
        $assets = BOOKME_URL . 'assets/admin/';

        wp_enqueue_style('bookme-select2', $assets . 'css/select2.min.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-jquery-ui-theme', $assets . 'css/jquery-ui-theme/jquery-ui.min.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-side-panel', $assets . 'css/slidePanel.min.css', array(), BOOKME_VERSION);

        wp_enqueue_script('bookme-side-panel-js', $assets . 'js/sidePanel.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-moment-js', $assets . 'js/moment.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-select2-js', $assets . 'js/select2.full.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-booking-panel-js', $assets . 'js/pages/booking-panel.js', array('jquery', 'jquery-ui-datepicker', 'bookme-side-panel-js', 'bookme-select2-js'), BOOKME_VERSION);

        $result = array(
            'staff' => array(),
            'customers' => array(),
            'start_time' => array(),
            'status' => array(
                'items' => array(
                    'pending' => Inc\Mains\Tables\CustomerBooking::status_to_string(Inc\Mains\Tables\CustomerBooking::STATUS_PENDING),
                    'approved' => Inc\Mains\Tables\CustomerBooking::status_to_string(Inc\Mains\Tables\CustomerBooking::STATUS_APPROVED),
                    'cancelled' => Inc\Mains\Tables\CustomerBooking::status_to_string(Inc\Mains\Tables\CustomerBooking::STATUS_CANCELLED),
                    'rejected' => Inc\Mains\Tables\CustomerBooking::status_to_string(Inc\Mains\Tables\CustomerBooking::STATUS_REJECTED),
                ),
                'default' => get_option('bookme_default_booking_status'),
            ),
        );

        global $wpdb;

        $employees = Inc\Mains\Functions\System::is_current_user_admin()
            ? $wpdb->get_results("SELECT * FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "` ORDER BY position", ARRAY_A)
            : $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "` WHERE wp_user_id = %d", get_current_user_id()), ARRAY_A);

        $employees = Inc\Mains\Functions\System::bind_data_with_table(Inc\Mains\Tables\Employee::class,$employees);

        /** @var Inc\Mains\Tables\Employee $staff_member */
        foreach ($employees as $staff_member) {
            $services = array();
            foreach ($staff_member->get_employee_services() as $staff_service) {
                $services[$staff_service->service->get_id()] = array(
                    'id' => $staff_service->service->get_id(),
                    'title' => sprintf(
                        '%s (%s)',
                        $staff_service->service->get_title(),
                        Inc\Mains\Functions\DateTime::seconds_to_interval($staff_service->service->get_duration())
                    ),
                    'duration' => $staff_service->service->get_duration(),
                    'capacity_min' => $staff_service->get_capacity_min(),
                    'capacity_max' => $staff_service->get_capacity_max(),
                );
            }
            $result['staff'][$staff_member->get_id()] = array(
                'id' => $staff_member->get_id(),
                'full_name' => $staff_member->get_full_name(),
                'services' => $services
            );
        }

        // Customers list.
        foreach ($wpdb->get_results("SELECT * FROM `" . Inc\Mains\Tables\Customer::get_table_name() . "` ORDER BY full_name", ARRAY_A) as $customer) {
            $name = $customer['full_name'];
            if ($customer['email'] != '' || $customer['phone'] != '') {
                $name .= ' (' . trim($customer['email'] . ', ' . $customer['phone'], ', ') . ')';
            }

            $result['customers'][$customer['id']] = array(
                'id' => $customer['id'],
                'name' => $name,
                'custom_fields' => array(),
                'number_of_persons' => 1,
                'status' => get_option('bookme_default_booking_status')
            );
        }

        // Time list.
        $ts_length = Inc\Mains\Functions\System::get_time_slot_length();
        $time_start = 0;

        // Run the loop.
        while ($time_start < DAY_IN_SECONDS) {
            $slot = array(
                'value' => Inc\Mains\Functions\DateTime::build_time_string($time_start, false),
                'title' => Inc\Mains\Functions\DateTime::format_time($time_start),
            );
            $result['start_time'][] = $slot;
            $time_start += $ts_length;
        }

        // Custom fields
        $custom_fields = array_filter(
            json_decode(get_option('bookme_custom_fields')),
            function ($field) {
                return !in_array($field->type, array('text-content'));
            }
        );

        $result['custom_fields'] = $custom_fields;
        $result['notification'] = get_user_meta(get_current_user_id(), 'bookme_appointment_form_send_notifications', true);

        wp_localize_script('bookme-booking-panel-js', 'BookmeBooking', array(
            'csrf_token' => Inc\Mains\Functions\System::get_security_token(),
            'dateOptions' => array(
                'dateFormat' => Inc\Mains\Functions\DateTime::convert_format('date', Inc\Mains\Functions\DateTime::FORMAT_JQUERY_DATEPICKER),
                'dateFormatMoment' => Inc\Mains\Functions\DateTime::convert_format('date', Inc\Mains\Functions\DateTime::FORMAT_MOMENT_JS),
                'monthNamesShort' => array_values($wp_locale->month_abbrev),
                'monthNames' => array_values($wp_locale->month),
                'dayNamesMin' => array_values($wp_locale->weekday_abbrev),
                'longDays' => array_values($wp_locale->weekday),
                'firstDay' => (int)get_option('start_of_week'),
            ),
            'staff_any' => get_option('bookme_lang_select_employee'),
            'cf_per_service' => (int)Inc\Mains\Functions\System::custom_fields_per_service(),
            'select_service' => esc_html__('Select service', 'bookme'),
            'no_result_found' => esc_html__('No result found', 'bookme'),
            'edit_booking' => esc_html__('Edit booking', 'bookme'),
            'new_booking' => esc_html__('New booking', 'bookme'),
            'form' => $result,
        ));

        Inc\Core\Template::create('fragments/booking-panel')->display($result);
    }

    /**
     * Render Customer Sidepanel
     */
    public static function render_customer_panel()
    {
        $assets = BOOKME_URL . 'assets/admin/';
        $public_assets = BOOKME_URL . 'assets/front/';

        if (get_option('bookme_phone_default_country') != 'disabled') {
            wp_enqueue_style('bookme-intlTelInput', $public_assets . 'css/intlTelInput.css', array(), BOOKME_VERSION);
            wp_enqueue_script('bookme-intlTelInput-js', $public_assets . 'js/intlTelInput.min.js', array('jquery'), BOOKME_VERSION);
        }

        wp_enqueue_style('bookme-side-panel', $assets . 'css/slidePanel.min.css', array(), BOOKME_VERSION);

        wp_enqueue_script('bookme-side-panel-js', $assets . 'js/sidePanel.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-customer-panel', $assets . 'js/pages/customer-panel.js', array('jquery','bookme-side-panel-js'), BOOKME_VERSION);

        wp_localize_script('bookme-customer-panel', 'BookmeCustomers', array(
            'intlTelInput' => array(
                'enabled' => get_option('bookme_phone_default_country') != 'disabled',
                'utils' => $public_assets . 'js/intlTelInput.utils.js',
                'country' => get_option('bookme_phone_default_country')
            )
        ));

        Inc\Core\Template::create('fragments/customer-panel')->display();
    }

    /**
    * Render booking delete dialog
    */
    public static function render_booking_delete_dialog()
    {
        Inc\Core\Template::create('fragments/booking-delete-dialog')->display();
    }

    /**
     * Render payment dialog
     */
    public static function render_payment_dialog()
    {
        $assets = BOOKME_URL . 'assets/admin/';
        wp_enqueue_script('bookme-payment-panel', $assets . 'js/pages/payment-panel.js', array('jquery','bookme-side-panel-js'), BOOKME_VERSION);
        Inc\Core\Template::create('fragments/payment-dialog')->display();
    }
}