<?php

namespace Bookme\App\Admin;

use Bookme\Inc;
use Bookme\Inc\Mains\Functions\Request;

/**
 * Class Bookings
 */
class Bookings extends Inc\Core\App
{

    const page_slug = 'bookme-bookings';

    /**
     * execute page.
     */
    public function execute()
    {

        $assets = BOOKME_URL . 'assets/admin/';

        wp_enqueue_style('bookme-datatable', $assets . 'css/datatables.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-daterangepicker', $assets . 'css/daterangepicker.css', array(), BOOKME_VERSION);
        Fragments::enqueue_global();

        wp_enqueue_script('bookme-datatable-js', $assets . 'js/jquery.dataTables.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-daterangepicker-js', $assets . 'js/daterangepicker.js', array('jquery', 'bookme-moment-js'), BOOKME_VERSION);
        wp_enqueue_script('bookme-bookings', $assets . 'js/pages/bookings.js', array('jquery', 'bookme-select2-js'), BOOKME_VERSION);

        global $wpdb, $wp_locale;

        // Custom fields
        $custom_fields = array_filter(json_decode(get_option('bookme_custom_fields')), function ($field) {
            return !in_array($field->type, array('text-content'));
        });

        wp_localize_script('bookme-bookings', 'Bookme', array(
            'csrf_token' => Inc\Mains\Functions\System::get_security_token(),
            'edit' => esc_html__('Edit', 'bookme'),
            'zeroRecords' => esc_html__('No bookings available.', 'bookme'),
            'processing' => esc_html__('Processing...', 'bookme'),
            'are_you_sure' => esc_html__('Are you sure?', 'bookme'),
            'tomorrow' => esc_html__('Tomorrow', 'bookme'),
            'today' => esc_html__('Today', 'bookme'),
            'yesterday' => esc_html__('Yesterday', 'bookme'),
            'last_7' => esc_html__('Last 7 Days', 'bookme'),
            'last_30' => esc_html__('Last 30 Days', 'bookme'),
            'this_month' => esc_html__('This Month', 'bookme'),
            'next_month' => esc_html__('Next Month', 'bookme'),
            'custom_range' => esc_html__('Custom Range', 'bookme'),
            'apply' => esc_html__('Apply', 'bookme'),
            'cancel' => esc_html__('Cancel', 'bookme'),
            'to' => esc_html__('To', 'bookme'),
            'from' => esc_html__('From', 'bookme'),
            'no_result_found' => esc_html__('No result found.', 'bookme'),
            'zoom' => esc_html__('Zoom', 'bookme'),
            'calendar' => array(
                'longMonths' => array_values($wp_locale->month),
                'shortMonths' => array_values($wp_locale->month_abbrev),
                'longDays' => array_values($wp_locale->weekday),
                'shortDays' => array_values($wp_locale->weekday_abbrev),
            ),
            'mjsDateFormat' => Inc\Mains\Functions\DateTime::convert_format('date', Inc\Mains\Functions\DateTime::FORMAT_MOMENT_JS),
            'startOfWeek' => (int)get_option('start_of_week'),
            'cf_columns' => array_map(function ($custom_field) {
                return $custom_field->id;
            }, $custom_fields),
            'filter' => (array)get_user_meta(get_current_user_id(), 'bookme_filter_appointments_list', true),
        ));


        $employees = $wpdb->get_results("SELECT id, full_name FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "` ORDER BY position", ARRAY_A);
        $services = $wpdb->get_results("SELECT id, title FROM `" . Inc\Mains\Tables\Service::get_table_name() . "` ORDER BY position", ARRAY_A);
        $customers = $wpdb->get_results("SELECT id, full_name FROM `" . Inc\Mains\Tables\Customer::get_table_name() . "` ORDER BY full_name", ARRAY_A);

        Inc\Core\Template::create('bookings/page')->display(compact('custom_fields', 'employees', 'services', 'customers'));
    }

    /**
     * Get bookings for datatable
     */
    public function perform_get_dt_bookings()
    {
        $columns = Request::get_parameter('columns');
        $order = Request::get_parameter('order');
        $filter = Request::get_parameter('filter');

        global $wpdb;

        $query_joins = "LEFT JOIN `" . Inc\Mains\Tables\Booking::get_table_name() . "` AS `a` ON a.id = ca.booking_id 
                    LEFT JOIN `" . Inc\Mains\Tables\Service::get_table_name() . "` AS `s` ON s.id = a.service_id 
                    LEFT JOIN `" . Inc\Mains\Tables\Customer::get_table_name() . "` AS `c` ON c.id = ca.customer_id 
                    LEFT JOIN `" . Inc\Mains\Tables\Payment::get_table_name() . "` AS `p` ON p.id = ca.payment_id 
                    LEFT JOIN `" . Inc\Mains\Tables\Employee::get_table_name() . "` AS `st` ON st.id = a.staff_id 
                    LEFT JOIN `" . Inc\Mains\Tables\EmployeeService::get_table_name() . "` AS `ss` ON ss.staff_id = st.id AND ss.service_id = s.id";


        $total = $wpdb->get_var("SELECT COUNT(*) FROM `" . Inc\Mains\Tables\CustomerBooking::get_table_name() . "` AS `ca` $query_joins");

        $where = " WHERE 1 = 1";
        if ($filter['id'] != '') {
            $where .= " AND `a`.`id` = {$filter['id']}";
        }

        list ($start, $end) = explode(' - ', $filter['date'], 2);
        $end = date('Y-m-d', strtotime('+1 day', strtotime($end)));
        $where .= " AND `a`.`start_date` BETWEEN '{$start}' AND '{$end}'";

        if ($filter['staff'] != '') {
            $where .= " AND `a`.`staff_id` = {$filter['staff']}";
        }

        if ($filter['customer'] != '') {
            $where .= " AND `ca`.`customer_id` = {$filter['customer']}";
        }

        if ($filter['service'] != '') {
            $where .= " AND `a`.`service_id` = {$filter['service']}";
        }

        if ($filter['status'] != '') {
            $where .= " AND `ca`.`status` = '{$filter['status']}'";
        }

        $sql = "SELECT a.id,
                ca.payment_id,
                ca.status,
                ca.id        AS ca_id,
                ca.custom_fields,
                a.start_date,
                a.id         AS booking_id,
                c.full_name  AS customer_full_name,
                c.phone      AS customer_phone,
                c.email      AS customer_email,
                s.title      AS service_title,
                s.duration   AS service_duration,
                s.color   AS service_color,
                st.full_name AS staff_name,
                st.attachment_id AS staff_attachment_id,
                p.paid       AS payment,
                p.total      AS payment_total,
                p.type       AS payment_type,
                p.status     AS payment_status
                FROM `" . Inc\Mains\Tables\CustomerBooking::get_table_name() . "` AS `ca` 
                $query_joins $where";

        foreach ($order as $sort_by) {
            $order = $sort_by['dir'] == 'desc' ? 'DESC' : 'ASC';
            $short_by = str_replace('.', '_', $columns[$sort_by['column']]['data']);
            $sql .= " ORDER BY $short_by $order";
        }

        $custom_fields = array();
        $fields_data = array_filter(json_decode(get_option('bookme_custom_fields')), function ($field) {
            return !in_array($field->type, array('text-content'));
        });
        foreach ($fields_data as $field_data) {
            $custom_fields[$field_data->id] = '';
        }

        $data = array();
        foreach ($wpdb->get_results($sql, ARRAY_A) as $row) {
            // Service duration.
            $service_duration = Inc\Mains\Functions\DateTime::seconds_to_interval($row['service_duration']);

            // Payment title.
            $payment_title = '';
            if ($row['payment'] !== null) {
                $payment_title = Inc\Mains\Functions\Price::format($row['payment_total']);

                $payment_title .= sprintf(
                    ' %s <span%s>%s</span>',
                    Inc\Mains\Tables\Payment::type_to_string($row['payment_type']),
                    $row['payment_status'] == Inc\Mains\Tables\Payment::STATUS_PENDING ? ' class="text-danger"' : '',
                    Inc\Mains\Tables\Payment::status_to_string($row['payment_status'])
                );
            }
            // Custom fields
            $customer_appointment = new Inc\Mains\Tables\CustomerBooking();
            $customer_appointment->load($row['ca_id']);
            foreach ($customer_appointment->get_custom_fields_data() as $custom_field) {
                $custom_fields[$custom_field['id']] = $custom_field['value'];
            }


            $img = wp_get_attachment_image_src($row['staff_attachment_id'], 'thumbnail');
            $img_url = $img ? $img[0] : BOOKME_URL . '/assets/admin/images/user-default.png';

            $data[] = array(
                'id' => $row['id'],
                'start_date' => Inc\Mains\Functions\DateTime::format_date($row['start_date']),
                'start_time' => Inc\Mains\Functions\DateTime::format_time($row['start_date']),
                'staff' => array(
                    'name' => $row['staff_name'],
                    'photo' => $img_url
                ),
                'customer' => array(
                    'full_name' => $row['customer_full_name'],
                    'phone' => $row['customer_phone'],
                    'email' => $row['customer_email'],
                ),
                'service' => array(
                    'title' => $row['service_title'],
                    'duration' => $service_duration,
                    'color' => $row['service_color'],
                ),
                'status' => $row['status'],
                'status_title' => Inc\Mains\Tables\CustomerBooking::status_to_string($row['status']),
                'payment' => $payment_title,
                'custom_fields' => $custom_fields,
                'ca_id' => $row['ca_id'],
                'payment_id' => $row['payment_id'],
                'online_meeting' => Inc\Mains\Tables\Booking::find($row['booking_id'])->get_online_meeting_host_url()
            );

            $custom_fields = array_map(function () {
                return '';
            }, $custom_fields);
        }

        unset($filter['date']);
        update_user_meta(get_current_user_id(), 'bookme_filter_appointments_list', $filter);

        wp_send_json(array(
            'draw' => (int)Request::get_parameter('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => count($data),
            'data' => $data,
        ));
    }

    /**
     * Delete datatable bookings
     */
    public function perform_delete_dt_bookings()
    {
        global $wpdb;
        $ids = array_map('intval', Request::get_parameter('data', array()));
        $ca_list = $wpdb->get_results(
            "SELECT * FROM `" . Inc\Mains\Tables\CustomerBooking::get_table_name() . "` 
                    WHERE id IN (" . implode(',', $ids) . ")",
            ARRAY_A);
        $ca_list = Inc\Mains\Functions\System::bind_data_with_table(Inc\Mains\Tables\CustomerBooking::class, $ca_list);
        /** @var Inc\Mains\Tables\CustomerBooking $ca */
        foreach ($ca_list as $ca) {
            if (Request::get_parameter('notify')) {
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
                    array('cancellation_reason' => Request::get_parameter('reason'))
                );
            }
            $ca->delete_cascade();
        }
        wp_send_json_success();
    }
}