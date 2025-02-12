<?php

namespace Bookme\App\Admin;

use Bookme\Inc;
use Bookme\Inc\Mains\Functions\Request;

/**
 * Class Calendar
 */
class Calendar extends Inc\Core\App
{

    const page_slug = 'bookme-calendar';

    /**
     * execute page.
     */
    public function execute()
    {
        $assets = BOOKME_URL . 'assets/admin/';

        wp_enqueue_style('bookme-full-calendar', $assets . 'css/full-calendar.css', array(), BOOKME_VERSION);

        Fragments::enqueue_global();

        wp_enqueue_script('bookme-full-calendar-js', $assets . 'js/full-calendar.min.js', array('jquery', 'bookme-moment-js'), BOOKME_VERSION);

        wp_enqueue_script('bookme-calendar', $assets . 'js/pages/calendar.js', array('jquery', 'bookme-full-calendar-js', 'jquery-ui-datepicker'), BOOKME_VERSION);

        global $wp_locale;
        $slot_length_minutes = get_option('bookme_time_slot_step', '15');
        $slot = new \DateInterval('PT' . $slot_length_minutes . 'M');

        wp_localize_script('bookme-calendar', 'Bookme', array(
            'csrf_token' => Inc\Mains\Functions\System::get_security_token(),
            'slotDuration' => $slot->format('%H:%I:%S'),
            'calendar' => array(
                'shortMonths' => array_values($wp_locale->month_abbrev),
                'longMonths' => array_values($wp_locale->month),
                'shortDays' => array_values($wp_locale->weekday_abbrev),
                'longDays' => array_values($wp_locale->weekday),
            ),
            'dpDateFormat' => Inc\Mains\Functions\DateTime::convert_format('date', Inc\Mains\Functions\DateTime::FORMAT_JQUERY_DATEPICKER),
            'mjsDateFormat' => Inc\Mains\Functions\DateTime::convert_format('date', Inc\Mains\Functions\DateTime::FORMAT_MOMENT_JS),
            'mjsTimeFormat' => Inc\Mains\Functions\DateTime::convert_format('time', Inc\Mains\Functions\DateTime::FORMAT_MOMENT_JS),
            'today' => esc_html__('Today', 'bookme'),
            'week' => esc_html__('Week', 'bookme'),
            'day' => esc_html__('Day', 'bookme'),
            'month' => esc_html__('Month', 'bookme'),
            'allDay' => esc_html__('All Day', 'bookme'),
            'delete' => esc_html__('Delete', 'bookme'),
            'are_you_sure' => esc_html__('Are you sure?', 'bookme'),
            'startOfWeek' => (int)get_option('start_of_week'),
            'is_rtl' => is_rtl(),
        ));

        global $wpdb;
        $employees = Inc\Mains\Functions\System::is_current_user_admin()
            ? $wpdb->get_results("SELECT * FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "` ORDER BY position", ARRAY_A)
            : $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "` WHERE wp_user_id = %d", get_current_user_id()), ARRAY_A);


        Inc\Core\Template::create('calendar/page')->display(compact('employees'));
    }

    /**
     * Get bookings for FullCalendar.
     *
     * return string json
     */
    public function perform_get_bookings_for_calendar()
    {
        global $wpdb;

        $result = array();
        $staff_members = array();
        $one_day = new \DateInterval('P1D');
        $start_date = new \DateTime(Request::get_parameter('start'));
        $end_date = new \DateTime(Request::get_parameter('end'));
        // FullCalendar sends end date as 1 day further.
        $end_date->sub($one_day);

        if (Inc\Mains\Functions\System::is_current_user_admin()) {
            $where = "";
            if ((int)Request::get_parameter('staff_id')) {
                $where = sprintf("WHERE id = %d", Request::get_parameter('staff_id'));
            }
            $staff_members = $wpdb->get_results("SELECT id FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "` $where", ARRAY_A);
        } else {
            $staff_members = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "` 
                    WHERE wp_user_id = %d",
                    get_current_user_id()
                ),
                ARRAY_A);
        }

        foreach ($staff_members as $staff) {
            $result = array_merge($result, $this->get_bookings_for_calendar($staff['id'], $start_date, $end_date));
        }

        wp_send_json($result);
    }

    /**
     * Get data for edit booking form
     */
    public function perform_get_booking_data()
    {
        $response = array('success' => false, 'data' => array('customers' => array()));

        $appointment = new Inc\Mains\Tables\Booking();
        if ($appointment->load(Request::get_parameter('id'))) {
            $response['success'] = true;

            global $wpdb;
            $info = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT SUM(ca.number_of_persons) AS total_number_of_persons, 
                            a.staff_id, a.service_id, a.start_date, a.internal_note 
                        FROM `" . Inc\Mains\Tables\Booking::get_table_name() . "` AS `a` 
                        LEFT JOIN `" . Inc\Mains\Tables\CustomerBooking::get_table_name() . "` AS `ca` ON ca.booking_id = a.id 
                        LEFT JOIN `" . Inc\Mains\Tables\EmployeeService::get_table_name() . "` AS `ss` ON ss.staff_id = a.staff_id AND ss.service_id = a.service_id 
                        WHERE `a`.`id` = %d 
                        ORDER BY `a`.`id` ASC",
                    $appointment->get_id()
                ),
                ARRAY_A);

            $response['data']['total_number_of_persons'] = $info['total_number_of_persons'];
            $response['data']['start_date'] = $info['start_date'];
            $response['data']['staff_id'] = $info['staff_id'];
            $response['data']['service_id'] = $info['service_id'];
            $response['data']['internal_note'] = $info['internal_note'];
            $response['data']['online_meeting_url'] = $appointment->get_online_meeting_host_url();
            $response['data']['online_meeting_password'] = $appointment->get_online_meeting_password();

            $customers = $wpdb->get_results(
                $wpdb->prepare(
                    " SELECT 
                                ca.id, ca.customer_id, ca.custom_fields, ca.number_of_persons, ca.status, ca.payment_id, 
                                p.paid AS payment, p.total AS payment_total, p.type AS payment_type, p.details AS payment_details, p.status AS payment_status 
                            FROM `" . Inc\Mains\Tables\CustomerBooking::get_table_name() . "` AS `ca` 
                            LEFT JOIN `" . Inc\Mains\Tables\Payment::get_table_name() . "` AS `p` ON p.id = ca.payment_id 
                            WHERE `ca`.`booking_id` = %d 
                            ORDER BY `ca`.`id` ASC",
                    $appointment->get_id()
                ),
                ARRAY_A);

            foreach ($customers as $customer) {
                $payment_title = '';
                if ($customer['payment'] !== null) {
                    $payment_title = Inc\Mains\Functions\Price::format($customer['payment']);
                    if ($customer['payment'] != $customer['payment_total']) {
                        $payment_title = sprintf(__('%s of %s', 'bookme'), $payment_title, Inc\Mains\Functions\Price::format($customer['payment_total']));
                    }
                    $payment_title .= sprintf(
                        ' %s <span%s>%s</span>',
                        Inc\Mains\Tables\Payment::type_to_string($customer['payment_type']),
                        $customer['payment_status'] == Inc\Mains\Tables\Payment::STATUS_PENDING ? ' class="text-danger"' : '',
                        Inc\Mains\Tables\Payment::status_to_string($customer['payment_status'])
                    );
                }

                $response['data']['customers'][] = array(
                    'id' => $customer['customer_id'],
                    'ca_id' => $customer['id'],
                    'custom_fields' => (array)json_decode($customer['custom_fields'], true),
                    'number_of_persons' => $customer['number_of_persons'],
                    'payment_id' => $customer['payment_id'],
                    'payment_type' => $customer['payment'] != $customer['payment_total'] ? 'partial' : 'full',
                    'payment_title' => $payment_title,
                    'status' => $customer['status']
                );
            }
        }
        wp_send_json($response);
    }

    /**
     * Save booking (for both create and edit)
     */
    public function perform_save_booking()
    {
        $response = array('success' => false);

        $booking_id = (int)Request::get_parameter('id', 0);
        $staff_id = (int)Request::get_parameter('staff_id');
        $service_id = (int)Request::get_parameter('service_id');
        $start_date = Request::get_parameter('start_date');
        $end_date = Request::get_parameter('end_date');
        $customers = json_decode(Request::get_parameter('customers', '[]'), true);
        $internal_note = Request::get_parameter('internal_note');

        $staff_service = new Inc\Mains\Tables\EmployeeService();
        $staff_service->load_by(array(
            'staff_id' => $staff_id,
            'service_id' => $service_id,
        ));

        // Check for errors.
        if (!$start_date) {
            $response['errors']['time'] = esc_html__('Start time is required.', 'bookme');
        } elseif (!$end_date) {
            $response['errors']['time'] = esc_html__('Unexpected error, try again.', 'bookme');
        } elseif ($start_date == $end_date) {
            $response['errors']['time'] = esc_html__('Unexpected error, try again.', 'bookme');
        }
        if (!$service_id) {
            $response['errors']['service'] = esc_html__('Service is required.', 'bookme');
        }
        if (empty ($customers)) {
            $response['errors']['customer'] = esc_html__('Customer is required.', 'bookme');
        }
        $total_number_of_persons = 0;
        foreach ($customers as $i => $customer) {
            if ($customer['status'] == Inc\Mains\Tables\CustomerBooking::STATUS_PENDING ||
                $customer['status'] == Inc\Mains\Tables\CustomerBooking::STATUS_APPROVED
            ) {
                $total_number_of_persons += $customer['number_of_persons'];
            }
            $customers[$i]['created_from'] = 'backend';
        }
        if ($total_number_of_persons > $staff_service->get_capacity_max()) {
            $response['errors']['customer'] = sprintf(
                esc_html__('Maximum %d customers are allowed for this service.', 'bookme'),
                $staff_service->get_capacity_max()
            );
        }
        $notification = Request::get_parameter('notification');

        // If no errors then try to save the booking.
        if (!isset ($response['errors'])) {
            // Single booking.
            $booking = new Inc\Mains\Tables\Booking();
            if ($booking_id) {
                // Edit.
                $booking->load($booking_id);
                if ($booking->get_staff_id() != $staff_id) {
                    $booking->set_staff_any(0);
                }
            }
            $booking
                ->set_staff_id($staff_id)
                ->set_service_id($service_id)
                ->set_start_date($start_date)
                ->set_end_date($end_date)
                ->set_internal_note($internal_note);

            if ($booking->save() !== false) {
                // Save customer bookings.
                $ca_status_changed = $booking->save_customer_bookings($customers);

                // Online Meeting
                $response['alert'] = $booking->handle_online_meeting();

                // Google Calendar.
                $booking->handle_google_calendar();

                // Send notifications.
                if ($notification == 'changed_status') {
                    foreach ($ca_status_changed as $ca) {
                        Inc\Mains\Notification\Sender::send_single(Inc\Mains\Booking\DataHolders\Service::create($ca)->set_booking($booking));
                    }
                } else if ($notification == 'all') {
                    $ca_list = $booking->get_customer_bookings(true);
                    foreach ($ca_list as $ca) {
                        Inc\Mains\Notification\Sender::send_single(Inc\Mains\Booking\DataHolders\Service::create($ca)->set_booking($booking));
                    }
                }

                $response['success'] = true;
                $response['data'] = $this->get_booking_for_calendar($staff_id, $booking->get_id());
            } else {
                $response['errors'] = array('time' => esc_html__('Unexpected error, try again.', 'bookme'));
            }
        }
        update_user_meta(get_current_user_id(), 'bookme_appointment_form_send_notifications', $notification);
        wp_send_json($response);
    }

    /*
     * Check booking errors
     */
    public function perform_check_booking_errors()
    {
        $start_date = Request::get_parameter('start_date');
        $end_date = Request::get_parameter('end_date');
        $staff_id = (int)Request::get_parameter('staff_id');
        $service_id = (int)Request::get_parameter('service_id');
        $booking_id = (int)Request::get_parameter('id');
        $timestamp_diff = strtotime($end_date) - strtotime($start_date);
        $customers = json_decode(Request::get_parameter('customers', '[]'), true);

        $result = array();

        if (!$this->is_timeslot_available($start_date, $end_date, $staff_id, $booking_id)) {
            $result['time'] = esc_html__('The selected time interval is occupied by another booking', 'bookme');
        }

        if ($service_id) {
            $service = new Inc\Mains\Tables\Service();
            $service->load($service_id);

            $duration = $service->get_duration();

            // Service duration interval is not equal to.
            if ($timestamp_diff != $duration) {
                $result['time'] = esc_html__('Unexpected error, try again.', 'bookme');
            }

            // Check customers for bookings limit
            if ($start_date) {
                $errors = array();
                foreach ($customers as $index => $customer) {
                    if ($service->check_bookings_limit_reached($customer['id'], $start_date)) {
                        $customer_error = Inc\Mains\Tables\Customer::find($customer['id']);
                        $errors[] = sprintf(__('"%s" has reached the limit of bookings for this service', 'bookme'), $customer_error->get_full_name());
                    }
                }
                $result['customer'] = implode('<br>', $errors);
            }
        }

        wp_send_json($result);
    }

    /**
     * Delete single booking.
     */
    public function perform_delete_booking()
    {
        $appointment_id = Request::get_parameter('id');
        $reason = Request::get_parameter('reason');

        if (Request::get_parameter('notify')) {
            global $wpdb;
            $ca_list = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM `" . Inc\Mains\Tables\CustomerBooking::get_table_name() . "` 
                    WHERE booking_id = %d",
                    $appointment_id
                ),
                ARRAY_A);
            $ca_list = Inc\Mains\Functions\System::bind_data_with_table(Inc\Mains\Tables\CustomerBooking::class, $ca_list);
            /** @var Inc\Mains\Tables\CustomerBooking $ca */
            foreach ($ca_list as $ca) {
                switch ($ca->get_status()) {
                    case Inc\Mains\Tables\CustomerBooking::STATUS_PENDING:
                        $ca->set_status(Inc\Mains\Tables\CustomerBooking::STATUS_REJECTED);
                        break;
                    case Inc\Mains\Tables\CustomerBooking::STATUS_APPROVED:
                        $ca->set_status(Inc\Mains\Tables\CustomerBooking::STATUS_CANCELLED);
                        break;
                }
                Inc\Mains\Notification\Sender::send_single(
                    Inc\Mains\Booking\DataHolders\Service::create($ca),
                    null,
                    array('cancellation_reason' => $reason)
                );
            }
        }

        Inc\Mains\Tables\Booking::find($appointment_id)->delete();

        wp_send_json_success();
    }

    /**
     * @param $start_date
     * @param $end_date
     * @param $staff_id
     * @param $booking_id
     * @return bool
     */
    private function is_timeslot_available($start_date, $end_date, $staff_id, $booking_id)
    {
        global $wpdb;
        return $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM `" . Inc\Mains\Tables\Booking::get_table_name() . "` 
                WHERE id != %d 
                    AND staff_id = %d 
                    AND start_date < %s 
                    AND end_date > %s",
                    $booking_id,
                    $staff_id,
                    $end_date,
                    $start_date
                )
            ) == 0;
    }

    /**
     * Get bookings for FullCalendar.
     *
     * @param int $staff_id
     * @param \DateTime $start_date
     * @param \DateTime $end_date
     * @return array
     */
    private function get_bookings_for_calendar($staff_id, \DateTime $start_date, \DateTime $end_date)
    {
        $where = sprintf("st.id = %d", $staff_id);
        $where .= " AND DATE(`a`.`start_date`) BETWEEN '{$start_date->format('Y-m-d')}' AND '{$end_date->format('Y-m-d')}'";

        return $this->get_bookings($staff_id, $where);
    }

    /**
     * Get booking for FullCalendar.
     *
     * @param integer $staff_id
     * @param int $booking_id
     * @return array
     */
    private function get_booking_for_calendar($staff_id, $booking_id)
    {
        $where = sprintf("a.id = %d", $booking_id);

        $bookings = $this->get_bookings($staff_id, $where);

        return $bookings[0];
    }

    /**
     * Build bookings for FullCalendar.
     *
     * @param integer $staff_id
     * @param string $where
     * @return mixed
     */
    private function get_bookings($staff_id, $where)
    {
        global $wpdb;

        $bookings = $wpdb->get_results(
            "SELECT 
                    a.id, a.staff_any, a.start_date, a.end_date, 
                    s.title AS service_name, s.color AS service_color, ss.capacity_max AS service_capacity, ss.price AS service_price, 
                    st.full_name AS staff_name, st.attachment_id AS staff_attachment_id, 
                    (SELECT SUM(ca.number_of_persons) FROM " . Inc\Mains\Tables\CustomerBooking::get_table_name() . " ca WHERE ca.booking_id = a.id) AS total_number_of_persons, 
                    ca.number_of_persons, ca.status AS booking_status, 
                    c.full_name AS client_name, c.phone AS client_phone, c.email AS client_email, c.id AS customer_id
                FROM `" . Inc\Mains\Tables\Booking::get_table_name() . "` AS `a` 
                LEFT JOIN `" . Inc\Mains\Tables\CustomerBooking::get_table_name() . "` AS `ca` ON ca.booking_id = a.id 
                LEFT JOIN `" . Inc\Mains\Tables\Customer::get_table_name() . "` AS `c` ON c.id = ca.customer_id 
                LEFT JOIN `" . Inc\Mains\Tables\Service::get_table_name() . "` AS `s` ON s.id = a.service_id 
                LEFT JOIN `" . Inc\Mains\Tables\Employee::get_table_name() . "` AS `st` ON st.id = a.staff_id 
                LEFT JOIN `" . Inc\Mains\Tables\EmployeeService::get_table_name() . "` AS `ss` ON ss.staff_id = a.staff_id AND ss.service_id = a.service_id 
                WHERE $where 
                GROUP BY a.id 
                ORDER BY `a`.`id` ASC"
            ,
            ARRAY_A);

        foreach ($bookings as $key => $booking) {

            $img = wp_get_attachment_image_src($booking['staff_attachment_id'], 'thumbnail');
            $img_url = $img ? $img[0] : BOOKME_URL . '/assets/admin/images/user-default.png';

            $bookings[$key] = array(
                'id' => $booking['id'],
                'start' => $booking['start_date'],
                'end' => $booking['end_date'],
                'title' => ' ',
                'status' => $booking['booking_status'],
                'status_title' => Inc\Mains\Tables\CustomerBooking::status_to_string($booking['booking_status']),
                'start_time' => Inc\Mains\Functions\DateTime::format_time($booking['start_date']),
                'end_time' => Inc\Mains\Functions\DateTime::format_time($booking['end_date']),
                'staffId' => $staff_id,
                'staff_name' => esc_html($booking['staff_name']),
                'staff_photo' => $img_url,
                'service_color' => $booking['service_color'],
                'service_name' => esc_html($booking['service_name']),
                'service_price' => Inc\Mains\Functions\Price::format($booking['service_price']),
            );

            if ($booking['total_number_of_persons'] == $booking['number_of_persons']) {
                $bookings[$key]['client_name'] = esc_html($booking['client_name']);
                $bookings[$key]['client_phone'] = esc_html($booking['client_phone']);
                $bookings[$key]['client_email'] = esc_html($booking['client_email']);
                $bookings[$key]['clients'] = 0;
            } else {
                $bookings[$key]['clients'] = sprintf(esc_html__('%s customers', 'bookme'), $booking['total_number_of_persons']);
            }
        }

        return $bookings;
    }

    /**
     * Register ajax for a class
     */
    protected function register_ajax()
    {
        // set permissions for user
        $permissions = array(
            'app' => 'user',
        );
        Inc\Core\Ajax::register_ajax_actions($this,$permissions);
    }
}