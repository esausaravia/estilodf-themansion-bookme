<?php

namespace Bookme\App\Admin;

use Bookme\Inc;
use Bookme\Inc\Mains\Functions\Request;

/**
 * Class Customers
 */
class Customers extends Inc\Core\App
{

    const page_slug = 'bookme-customers';

    /**
     * execute page.
     */
    public function execute()
    {
        $assets = BOOKME_URL . 'assets/admin/';
        $public_assets = BOOKME_URL . 'assets/front/';

        wp_enqueue_style('bookme-datatable', $assets . 'css/datatables.css', array(), BOOKME_VERSION);

        if (get_option('bookme_phone_default_country') != 'disabled') {
            wp_enqueue_style('bookme-intlTelInput', $public_assets . 'css/intlTelInput.css', array(), BOOKME_VERSION);
            wp_enqueue_script('bookme-intlTelInput-js', $public_assets . 'js/intlTelInput.min.js', array('jquery'), BOOKME_VERSION);
        }

        Fragments::enqueue_global();
        wp_enqueue_style('bookme-side-panel', $assets . 'css/slidePanel.min.css', array(), BOOKME_VERSION);

        wp_enqueue_script('bookme-datatable-js', $assets . 'js/jquery.dataTables.min.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-side-panel-js', $assets . 'js/sidePanel.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-customers', $assets . 'js/pages/customers.js', array('jquery', 'bookme-customer-panel'), BOOKME_VERSION);

        wp_localize_script('bookme-customers', 'Bookme', array(
            'csrf_token' => Inc\Mains\Functions\System::get_security_token(),
            'edit' => esc_attr__('Edit', 'bookme'),
            'zeroRecords' => esc_html__('No customers available.', 'bookme'),
            'processing' => esc_html__('Processing...', 'bookme'),
            'save' => esc_html__('Customer has been saved.', 'bookme'),
            'are_you_sure' => esc_html__('Are you sure?', 'bookme')
        ));

        Inc\Core\Template::create('customers/page')->display();
    }

    /**
     * Get all customers.
     */
    public function perform_get_customers()
    {
        global $wpdb;

        $columns = Request::get_parameter('columns');
        $order = Request::get_parameter('order');

        $total = (int)$wpdb->get_var('SELECT COUNT(*) FROM ' . Inc\Mains\Tables\Customer::get_table_name());

        $query = 'SELECT SQL_CALC_FOUND_ROWS c.*,
                (
                    SELECT MAX(a.start_date) FROM ' . Inc\Mains\Tables\Booking::get_table_name() . ' a
                        LEFT JOIN ' . Inc\Mains\Tables\CustomerBooking::get_table_name() . ' cb ON cb.booking_id = a.id
                            WHERE cb.customer_id = c.id
                ) AS last_appointment,
                (
                    SELECT COUNT(DISTINCT cb.booking_id) FROM ' . Inc\Mains\Tables\CustomerBooking::get_table_name() . ' cb
                        WHERE cb.customer_id = c.id
                ) AS total_appointments,
                (
                    SELECT SUM(p.total) FROM ' . Inc\Mains\Tables\Payment::get_table_name() . ' p
                        WHERE p.id IN (
                            SELECT DISTINCT cb.payment_id FROM ' . Inc\Mains\Tables\CustomerBooking::get_table_name() . ' cb
                                WHERE cb.customer_id = c.id
                        )
                ) AS payments,
                wpu.display_name AS wp_user
                FROM ' . Inc\Mains\Tables\Customer::get_table_name() . ' c 
                LEFT JOIN `' . $wpdb->users . '` wpu ON wpu.ID = c.wp_user_id
                GROUP BY c.id';

        foreach ($order as $sort_by) {
            $query .= sprintf(
                ' ORDER BY %s %s',
                str_replace('.', '_', $columns[$sort_by['column']]['data']),
                $sort_by['dir']
            );
        }
        $query .= ' LIMIT %d OFFSET %d';

        $result = $wpdb->get_results(
            $wpdb->prepare(
                $query,
                Request::get_parameter('length'),
                Request::get_parameter('start')
            ),
            ARRAY_A
        );

        $data = array();
        foreach ($result as $row) {
            $data[] = array(
                'id' => $row['id'],
                'full_name' => $row['full_name'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'wp_user' => $row['wp_user'],
                'wp_user_id' => $row['wp_user_id'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'notes' => $row['notes'],
                'last_appointment' => $row['last_appointment'] ? Inc\Mains\Functions\DateTime::format_date_time($row['last_appointment']) : '',
                'total_appointments' => $row['total_appointments'],
                'payments' => Inc\Mains\Functions\Price::format($row['payments']),
            );
        }

        wp_send_json(array(
            'draw' => (int)Request::get_parameter('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => (int)$wpdb->get_var('SELECT FOUND_ROWS()'),
            'data' => $data,
        ));
    }

    /**
     * Create or edit a customer.
     */
    public function perform_save_customer()
    {
        $response = array();

        do {
            if ((get_option('bookme_customer_first_last_name') && Request::get_parameter('first_name') !== '' && Request::get_parameter('last_name') !== '') || (!get_option('bookme_customer_first_last_name') && Request::get_parameter('full_name') !== '')) {
                $params = Request::get_post_parameters();
                if (!$params['wp_user_id']) {
                    $params['wp_user_id'] = null;
                }
                $customer = new Inc\Mains\Tables\Customer();
                $customer->load(Request::get_parameter('id'));
                $customer->set_fields($params);
                if ($customer->save()) {
                    $response['success'] = true;
                    $response['customer'] = array(
                        'id' => $customer->get_id(),
                        'wp_user_id' => $customer->get_wp_user_id(),
                        'full_name' => $customer->get_full_name(),
                        'first_name' => $customer->get_first_name(),
                        'last_name' => $customer->get_last_name(),
                        'phone' => $customer->get_phone(),
                        'email' => $customer->get_email(),
                        'notes' => $customer->get_notes(),
                    );
                    break;
                }
            }
            $response['success'] = false;
            $errors = array();
            if (get_option('bookme_customer_first_last_name')) {
                if (Request::get_parameter('first_name') == '') {
                    $errors[] = esc_html__('First name is required.', 'bookme');
                }
                if (Request::get_parameter('last_name') == '') {
                    $errors[] = esc_html__('Last name is required.', 'bookme');
                }
            } else {
                $errors[] = esc_html__('Name is required.', 'bookme');
            }
            $response['errors'] = implode('<br>', $errors);
        } while (0);

        wp_send_json($response);
    }

    /**
     * Delete customers.
     */
    public function perform_delete_customers()
    {
        foreach (Request::get_parameter('ids', array()) as $id) {
            $customer = new Inc\Mains\Tables\Customer();
            $customer->load($id);
            $customer->delete_with_wp_user(false);
        }
        wp_send_json_success();
    }
}