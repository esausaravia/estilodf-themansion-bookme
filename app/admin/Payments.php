<?php
namespace Bookme\App\Admin;

use Bookme\Inc;
use Bookme\Inc\Mains\Functions\Request;

/**
 * Class Payments
 */
class Payments extends Inc\Core\App {

    const page_slug = 'bookme-payments';

    /**
     * execute page.
     */
    public function execute()
    {
        $assets = BOOKME_URL . 'assets/admin/';

        wp_enqueue_style('bookme-datatable', $assets . 'css/datatables.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-daterangepicker', $assets . 'css/daterangepicker.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-select2', $assets . 'css/select2.min.css', array(), BOOKME_VERSION);
        Fragments::enqueue_global();
        wp_enqueue_style('bookme-side-panel', $assets . 'css/slidePanel.min.css', array(), BOOKME_VERSION);

        wp_enqueue_script('bookme-moment-js', $assets . 'js/moment.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-datatable-js', $assets . 'js/jquery.dataTables.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-daterangepicker-js', $assets . 'js/daterangepicker.js', array('jquery', 'bookme-moment-js'), BOOKME_VERSION);
        wp_enqueue_script('bookme-side-panel-js', $assets . 'js/sidePanel.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-select2-js', $assets . 'js/select2.full.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-payment', $assets . 'js/pages/payment.js', array('jquery'), BOOKME_VERSION);

        global $wpdb, $wp_locale;

        wp_localize_script('bookme-payment', 'Bookme', array(
            'csrf_token' => Inc\Mains\Functions\System::get_security_token(),
            'zeroRecords' => esc_html__('No payments available.', 'bookme'),
            'processing' => esc_html__('Processing...', 'bookme'),
            'are_you_sure' => esc_html__('Are you sure?', 'bookme'),
            'tomorrow' => esc_html__('Tomorrow', 'bookme'),
            'today' => esc_html__('Today', 'bookme'),
            'yesterday' => esc_html__('Yesterday', 'bookme'),
            'last_7' => esc_html__('Last 7 Days', 'bookme'),
            'last_30' => esc_html__('Last 30 Days', 'bookme'),
            'this_month' => esc_html__('This Month', 'bookme'),
            'last_month' => esc_html__('Last Month', 'bookme'),
            'custom_range' => esc_html__('Custom Range', 'bookme'),
            'apply' => esc_html__('Apply', 'bookme'),
            'cancel' => esc_html__('Cancel', 'bookme'),
            'to' => esc_html__('To', 'bookme'),
            'from' => esc_html__('From', 'bookme'),
            'no_result_found' => esc_html__('No result found.', 'bookme'),
            'calendar' => array(
                'longMonths' => array_values($wp_locale->month),
                'shortMonths' => array_values($wp_locale->month_abbrev),
                'longDays' => array_values($wp_locale->weekday),
                'shortDays' => array_values($wp_locale->weekday_abbrev),
            ),
            'mjsDateFormat' => Inc\Mains\Functions\DateTime::convert_format('date', Inc\Mains\Functions\DateTime::FORMAT_MOMENT_JS),
            'startOfWeek' => (int)get_option('start_of_week'),
            'details' => esc_html__('Details', 'bookme'),
        ));


        $employees = $wpdb->get_results("SELECT id, full_name FROM `" . Inc\Mains\Tables\Employee::get_table_name() . "` ORDER BY position", ARRAY_A);
        $services = $wpdb->get_results("SELECT id, title FROM `" . Inc\Mains\Tables\Service::get_table_name() . "` ORDER BY position", ARRAY_A);

        $types = array(
            Inc\Mains\Tables\Payment::TYPE_LOCAL,
            Inc\Mains\Tables\Payment::TYPE_2CHECKOUT,
            Inc\Mains\Tables\Payment::TYPE_PAYPAL,
            Inc\Mains\Tables\Payment::TYPE_AUTHORIZENET,
            Inc\Mains\Tables\Payment::TYPE_STRIPE,
            Inc\Mains\Tables\Payment::TYPE_MOLLIE,
            Inc\Mains\Tables\Payment::TYPE_COUPON,
            Inc\Mains\Tables\Payment::TYPE_WOOCOMMERCE,
        );

        Inc\Core\Template::create('payment/page')->display(compact('types', 'employees', 'services'));
    }

    /**
     * Get payments for datatable
     */
    public function perform_get_payments()
    {
        $columns = Request::get_parameter('columns');
        $order = Request::get_parameter('order');
        $filter = Request::get_parameter('filter');

        global $wpdb;

        // Filters
        $where = " WHERE 1 = 1";

        list ($start, $end) = explode(' - ', $filter['created'], 2);
        $end = date('Y-m-d', strtotime('+1 day', strtotime($end)));

        $where .= sprintf(" AND `p`.`created` BETWEEN '%s' AND '%s'", $start, $end);

        if ($filter['type'] != '') {
            $where .= sprintf(" AND `p`.`type` = '%s'", $filter['type']);
        }

        if ($filter['staff'] != '') {
            $where .= sprintf(" AND `st`.`id` = %d", $filter['staff']);
        }

        if ($filter['service'] != '') {
            $where .= sprintf(" AND `s`.`id` = %d", $filter['service']);
        }

        $sql = "SELECT 
                    p.id, 
                    p.created, 
                    p.type, 
                    p.paid, 
                    p.total, 
                    p.status, 
                    p.details, 
                    c.full_name customer, 
                    st.full_name employee, 
                    s.title service, 
                    a.start_date
                FROM `" . Inc\Mains\Tables\Payment::get_table_name() . "` AS `p` 
                LEFT JOIN `" . Inc\Mains\Tables\CustomerBooking::get_table_name() . "` AS `ca` ON ca.payment_id = p.id
                LEFT JOIN `" . Inc\Mains\Tables\Customer::get_table_name() . "` AS `c` ON c.id = ca.customer_id
                LEFT JOIN `" . Inc\Mains\Tables\Booking::get_table_name() . "` AS `a` ON a.id = ca.booking_id
                LEFT JOIN `" . Inc\Mains\Tables\Service::get_table_name() . "` AS `s` ON s.id = a.service_id
                LEFT JOIN `" . Inc\Mains\Tables\Employee::get_table_name() . "` AS `st` ON st.id = a.staff_id 
                $where
                GROUP BY p.id";

        foreach ($order as $sort_by) {
            $order = $sort_by['dir'] == 'desc' ? 'DESC' : 'ASC';
            $short_by = str_replace('.', '_', $columns[$sort_by['column']]['data']);
            $sql .= " ORDER BY $short_by $order";
        }

        $payments = $wpdb->get_results($sql, ARRAY_A);

        $data = array();
        $total = 0;
        foreach ($payments as $payment) {
            $details = json_decode($payment['details'], true);
            $multiple = count($details['items']) > 1
                ? ' <i class="icon-feather-cart" title="' . esc_attr__('See details for more items', 'bookme') . '"></i>'
                : '';


            $data[] = array(
                'id' => $payment['id'],
                'created_date' => Inc\Mains\Functions\DateTime::format_date($payment['created']),
                'created_time' => Inc\Mains\Functions\DateTime::format_time($payment['created']),
                'type' => Inc\Mains\Tables\Payment::type_to_string($payment['type']),
                'customer' => $payment['customer'] ?: $details['customer'],
                'employee' => ($payment['employee'] ?: $details['items'][0]['staff_name']) . $multiple,
                'service' => ($payment['service'] ?: $details['items'][0]['service_name']) . $multiple,
                'start_date' => ($payment['start_date']
                        ? Inc\Mains\Functions\DateTime::format_date($payment['start_date'])
                        : Inc\Mains\Functions\DateTime::format_date($details['items'][0]['booking_date'])) . $multiple,
                'start_time' => ($payment['start_date']
                        ? Inc\Mains\Functions\DateTime::format_time($payment['start_date'])
                        : Inc\Mains\Functions\DateTime::format_time($details['items'][0]['booking_date'])) . $multiple,
                'amount' => Inc\Mains\Functions\Price::format($payment['total']),
                'status' => sprintf(
                        ' <span%s>%s</span>',
                        $payment['status'] == Inc\Mains\Tables\Payment::STATUS_PENDING ? ' class="text-danger"' : '',
                        Inc\Mains\Tables\Payment::status_to_string($payment['status'])
                    )

            );

            $total += $payment['paid'];
        }

        wp_send_json(array(
            'draw' => ( int )Request::get_parameter('draw'),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data,
        ));
    }

    /**
     * Delete payments
     */
    public function perform_delete_payments()
    {
        global $wpdb;
        $payment_ids = array_map('intval', Request::get_parameter('data', array()));
        $wpdb->query("DELETE FROM `" . Inc\Mains\Tables\Payment::get_table_name() . "` WHERE id IN (" . implode(',', $payment_ids) . ")");
        wp_send_json_success();
    }

    /**
     * Get payment details
     */
    public function perform_get_payment_details()
    {
        $data = array();

        global $wpdb;
        $payment = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                    p.total,
                    p.status,
                    p.created AS created,
                    p.type,
                    p.details,
                    p.paid,
                    c.full_name AS customer
                    FROM ".Inc\Mains\Tables\Payment::get_table_name()." p 
                    LEFT JOIN `" . Inc\Mains\Tables\CustomerBooking::get_table_name() . "` AS `ca` ON ca.payment_id = p.id
                    LEFT JOIN `" . Inc\Mains\Tables\Customer::get_table_name() . "` AS `c` ON c.id = ca.customer_id
                    WHERE p.id = %d",
                Request::get_parameter('payment_id')
            ),
            ARRAY_A
        );

        if ($payment) {
            $details = json_decode($payment['details'], true);
            $data = array(
                'payment' => array(
                    'status' => $payment['status'],
                    'type' => $payment['type'],
                    'coupon' => $details['coupon'],
                    'created' => $payment['created'],
                    'customer' => empty ($payment['customer']) ? $details['customer'] : $payment['customer'],
                    'total' => $payment['total'],
                    'paid' => $payment['paid'],
                ),
                'items' => $details['items'],
            );
        }

        wp_send_json_success(array('html' => Inc\Core\Template::create('payment/payment-details')->display($data, false)));
    }

    /**
     * Complete payment.
     */
    public function perform_complete_payment()
    {
        $payment = Inc\Mains\Tables\Payment::find(Request::get_parameter('payment_id'));
        $payment
            ->set_paid($payment->get_total())
            ->set_status(Inc\Mains\Tables\Payment::STATUS_COMPLETED)
            ->save();

        wp_send_json_success();
    }
}