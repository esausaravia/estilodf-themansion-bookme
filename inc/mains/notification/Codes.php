<?php

namespace Bookme\Inc\Mains\Notification;

use Bookme\Inc\Mains\Functions;
use Bookme\Inc;

/**
 * Class Codes
 */
class Codes
{
    public $booking_number;
    public $booking_start;
    public $booking_start_info;
    public $booking_end;
    public $booking_end_info;
    public $booking_token;
    public $number_of_persons;
    public $total_price;
    public $cancellation_reason;
    public $payment_type;
    public $customer_name;
    public $customer_first_name;
    public $customer_last_name;
    public $customer_email;
    public $customer_phone;
    public $cart_info;
    public $category_name;
    public $service_name;
    public $service_price;
    public $service_duration;
    public $service_info;
    public $employee_email;
    public $employee_name;
    public $employee_phone;
    public $employee_photo;
    public $employee_info;
    public $custom_fields;
    public $custom_fields_2col;
    public $google_calendar_url;
    public $new_password;
    public $new_username;
    public $next_day_agenda;
    public $site_address;
    public $online_meeting_host_url;
    public $online_meeting_join_url;
    public $online_meeting_password;

    /** @var Inc\Mains\Booking\DataHolders\Order */
    protected $order;
    /** @var Inc\Mains\Booking\DataHolders\Service */
    protected $service;

    /**
     * Get order.
     *
     * @return Inc\Mains\Booking\DataHolders\Order
     */
    public function get_order()
    {
        return $this->order;
    }

    /**
     * Get item.
     *
     * @return Inc\Mains\Booking\DataHolders\Service
     */
    public function get_service()
    {
        return $this->service;
    }

    /**
     * Create for order.
     *
     * @param Inc\Mains\Booking\DataHolders\Order $order
     * @param Inc\Mains\Booking\DataHolders\Service $service
     * @return static
     */
    public static function create_for_order(Inc\Mains\Booking\DataHolders\Order $order, Inc\Mains\Booking\DataHolders\Service $service)
    {
        $codes = new static();

        $codes->order = $order;
        $codes->service = $service;

        $employee_service = new Inc\Mains\Tables\EmployeeService();
        $employee_service->load_by(array('staff_id' => $service->get_staff()->get_id(), 'service_id' => $service->get_service()->get_id()));
        $price = $employee_service->get_price();
        // Normal start and end.
        $booking_start = $service->get_booking()->get_start_date();
        $booking_end = $service->get_booking()->get_end_date();


        $employee_photo = wp_get_attachment_image_src($service->get_staff()->get_attachment_id(), 'full');

        $codes->booking_end = $booking_end;
        $codes->booking_start = $booking_start;
        $codes->booking_token = $service->get_cb()->get_token();
        $codes->booking_number = $service->get_booking()->get_id();
        $codes->customer_email = $order->get_customer()->get_email();
        $codes->customer_name = $order->get_customer()->get_full_name();
        $codes->customer_first_name = $order->get_customer()->get_first_name();
        $codes->customer_last_name = $order->get_customer()->get_last_name();
        $codes->customer_phone = $order->get_customer()->get_phone();
        $codes->number_of_persons = $service->get_cb()->get_number_of_persons();
        $codes->service_price = $price;
        $codes->service_duration = $service->get_service()->get_duration();
        $codes->employee_email = $service->get_staff()->get_email();
        $codes->employee_phone = $service->get_staff()->get_phone();
        $codes->employee_photo = $employee_photo ? $employee_photo[0] : '';
        $codes->booking_start_info = $service->get_service()->get_start_time_info();
        $codes->booking_end_info = $service->get_service()->get_end_time_info();
        $codes->online_meeting_host_url = $service->get_booking()->get_online_meeting_host_url();
        $codes->online_meeting_join_url = $service->get_booking()->get_online_meeting_join_url();
        $codes->online_meeting_password = $service->get_booking()->get_online_meeting_password();

        if ($order->has_payment()) {
            $codes->total_price = $order->get_payment()->get_total();
        } else {
            $codes->total_price = $price * $service->get_cb()->get_number_of_persons();
        }

        $codes->refresh();

        return $codes;
    }

    /**
     * Create for test
     *
     * @return Codes
     */
    public static function create_for_test()
    {
        $codes = new static();
        $customer = new Inc\Mains\Tables\Customer();

        $customer
            ->set_phone('98765432')
            ->set_email('customer@example.com')
            ->set_notes('Customer notes')
            ->set_full_name('Customer Name')
            ->set_first_name('Customer First Name')
            ->set_last_name('Customer Last Name');

        $codes->order = new Inc\Mains\Booking\DataHolders\Order($customer);

        $codes->service;

        $start_date = date_create('-1 month');
        $event_start = $start_date->format('Y-m-d 12:00:00');
        $event_end = $start_date->format('Y-m-d 13:00:00');
        $cart_info = array(array(
            'service_name' => 'Service Name',
            'booking_start' => $event_start,
            'employee_name' => 'Staff Name',
            'booking_price' => 50,
            'cancel_url' => '#',
        ));

        $codes->booking_end = $event_end;
        $codes->booking_start = $event_start;
        $codes->cart_info = $cart_info;
        $codes->category_name = 'Category Name';
        $codes->customer_email = $customer->get_email();
        $codes->customer_name = $customer->get_full_name();
        $codes->customer_first_name = $customer->get_first_name();
        $codes->customer_last_name = $customer->get_last_name();
        $codes->customer_phone = $customer->get_phone();
        $codes->new_password = 'New Password';
        $codes->new_username = 'New User';
        $codes->next_day_agenda = '';
        $codes->number_of_persons = '1';
        $codes->payment_type = Inc\Mains\Tables\Payment::type_to_string(Inc\Mains\Tables\Payment::TYPE_LOCAL);
        $codes->service_info = 'Service info text';
        $codes->service_name = 'Service Name';
        $codes->service_price = '10';
        $codes->service_duration = '3600';
        $codes->employee_email = 'employee@example.com';
        $codes->employee_info = 'Staff info text';
        $codes->employee_name = 'Staff Name';
        $codes->employee_phone = '87654321';
        $codes->employee_photo = 'https://dummyimage.com/100/dddddd/000000';
        $codes->total_price = '50';
        $codes->cancellation_reason = 'Some Reason';
        $codes->online_meeting_host_url = '';
        $codes->online_meeting_join_url = '';
        $codes->online_meeting_password = '';


        return $codes;
    }

    /**
     * Do replacements.
     *
     * format codes {code}
     *
     * @param string $text
     * @param string $format
     * @return string
     */
    public function replace($text, $format = 'text')
    {
        $company_logo = '';
        $employee_photo = '';
        $cart_info_c = $cart_info = '';

        if ($format == 'html') {
            $img = wp_get_attachment_image_src(get_option('bookme_company_logo_id'), 'full');
            // Company logo as <img> tag.
            if ($img) {
                $company_logo = sprintf(
                    '<img src="%s" alt="%s" />',
                    esc_attr($img[0]),
                    esc_attr(get_option('bookme_company_name'))
                );
            }
            if ($this->employee_photo != '') {
                // Staff photo as <img> tag.
                $employee_photo = sprintf(
                    '<img src="%s" alt="%s" />',
                    esc_attr($this->employee_photo),
                    esc_attr($this->employee_name)
                );
            }
        }

        // Cart info.
        $cart_info_data = $this->cart_info;
        if (!empty ($cart_info_data)) {
            $cart_columns = get_option('bookme_cart_columns', array());
            $ths = array();
            foreach ($cart_columns as $column => $attr) {
                if ($attr['show']) {
                    switch ($column) {
                        case 'service':
                            $ths[] = Functions\System::get_translated_option('bookme_lang_title_service');
                            break;
                        case 'date':
                            $ths[] = esc_html__('Date', 'bookme');
                            break;
                        case 'time':
                            $ths[] = esc_html__('Time', 'bookme');
                            break;
                        case 'employee':
                            $ths[] = Functions\System::get_translated_option('bookme_lang_title_employee');
                            break;
                        case 'price':
                            $ths[] = esc_html__('Price', 'bookme');
                            break;
                    }
                }
            }
            $trs = array();
            foreach ($cart_info_data as $codes) {
                $tds = array();
                foreach ($cart_columns as $column => $attr) {
                    if ($attr['show']) {
                        switch ($column) {
                            case 'service':
                                $service_name = $codes['service_name'];
                                $tds[] = $service_name;
                                break;
                            case 'date':
                                $tds[] = Functions\DateTime::format_date($codes['booking_start']);
                                break;
                            case 'time':
                                if ($codes['booking_start_info'] !== null) {
                                    $tds[] = $codes['booking_start_info'];
                                } else {
                                    $tds[] = Functions\DateTime::format_time($codes['booking_start']);
                                }
                                break;
                            case 'employee':
                                $tds[] = $codes['employee_name'];
                                break;
                            case 'price':
                                $tds[] = Functions\Price::format($codes['booking_price']);
                                break;
                        }
                    }
                }
                $tds[] = $codes['cancel_url'];
                $trs[] = $tds;
            }
            if ($format == 'html') {
                $cart_info = '<table cellspacing="1" border="1" cellpadding="5"><thead><tr><th>' . implode('</th><th>', $ths) . '</th></tr></thead><tbody>';
                $cart_info_c = '<table cellspacing="1" border="1" cellpadding="5"><thead><tr><th>' . implode('</th><th>', $ths) . '</th><th>' . esc_html__('Cancel', 'bookme') . '</th></tr></thead><tbody>';
                foreach ($trs as $tr) {
                    $cancel_url = array_pop($tr);
                    $cart_info .= '<tr><td>' . implode('</td><td>', $tr) . '</td></tr>';
                    $cart_info_c .= '<tr><td>' . implode('</td><td>', $tr) . '</td><td><a href="' . $cancel_url . '">' . esc_html__('Cancel', 'bookme') . '</a></td></tr>';
                }
                $cart_info .= '</tbody></table>';
                $cart_info_c .= '</tbody></table>';
            } else {
                foreach ($trs as $tr) {
                    $cancel_url = array_pop($tr);
                    foreach ($ths as $position => $column) {
                        $cart_info .= $column . ' ' . $tr[$position] . "\r\n";
                        $cart_info_c .= $column . ' ' . $tr[$position] . "\r\n";
                    }
                    $cart_info .= "\r\n";
                    $cart_info_c .= esc_html__('Cancel', 'bookme') . ' ' . $cancel_url . "\r\n\r\n";
                }
            }
        }

        // Codes.
        $codes = array(
            '{booking_date}' => Functions\DateTime::format_date($this->booking_start),
            '{booking_time}' => $this->service_duration < DAY_IN_SECONDS ? Functions\DateTime::format_time($this->booking_start) : $this->booking_start_info,
            '{booking_end_date}' => Functions\DateTime::format_date($this->booking_end),
            '{booking_end_time}' => $this->service_duration < DAY_IN_SECONDS ? Functions\DateTime::format_time($this->booking_end) : $this->booking_end_info,
            '{approve_booking_url}' => $this->booking_token ? admin_url('admin-ajax.php?action=bookme_approve_booking&token=' . urlencode(Functions\System::xor_encrypt($this->booking_token, 'approve'))) : '',
            '{booking_number}' => $this->booking_number,
            '{cancel_booking_url}' => $this->booking_token ? admin_url('admin-ajax.php?action=bookme_cancel_booking&token=' . $this->booking_token) : '',
            '{cart_info}' => $cart_info,
            '{cart_info_c}' => $cart_info_c,
            '{category_name}' => $this->category_name,
            '{customer_email}' => $this->customer_email,
            '{customer_name}' => $this->customer_name,
            '{customer_first_name}' => $this->customer_first_name,
            '{customer_last_name}' => $this->customer_last_name,
            '{customer_phone}' => $this->customer_phone,
            '{company_address}' => $format == 'html' ? nl2br(get_option('bookme_company_address')) : get_option('bookme_company_address'),
            '{company_logo}' => $company_logo,
            '{company_name}' => get_option('bookme_company_name'),
            '{company_phone}' => get_option('bookme_company_phone'),
            '{company_website}' => get_option('bookme_company_website'),
            '{custom_fields}' => $this->custom_fields,
            '{custom_fields_2col}' => $format == 'html' ? $this->custom_fields_2col : $this->custom_fields,
            '{google_calendar_url}' => sprintf('https://calendar.google.com/calendar/render?action=TEMPLATE&text=%s&dates=%s/%s&details=%s',
                urlencode($this->service_name),
                date('Ymd\THis', strtotime($this->booking_start)),
                date('Ymd\THis', strtotime($this->booking_end)),
                urlencode(sprintf("%s\n%s", $this->service_name, $this->employee_name))
            ),
            '{new_password}' => $this->new_password,
            '{new_username}' => $this->new_username,
            '{next_day_agenda}' => $this->next_day_agenda,
            '{number_of_persons}' => $this->number_of_persons,
            '{payment_type}' => $this->payment_type,
            '{reject_booking_url}' => $this->booking_token ? admin_url('admin-ajax.php?action=bookme_reject_booking&token=' . urlencode(Functions\System::xor_encrypt($this->booking_token, 'reject'))) : '',
            '{service_info}' => $format == 'html' ? nl2br($this->service_info) : $this->service_info,
            '{service_name}' => $this->service_name,
            '{service_price}' => Functions\Price::format($this->service_price),
            '{service_duration}' => Functions\DateTime::seconds_to_interval($this->service_duration),
            '{site_address}' => $this->site_address,
            '{employee_email}' => $this->employee_email,
            '{employee_info}' => $format == 'html' ? nl2br($this->employee_info) : $this->employee_info,
            '{employee_name}' => $this->employee_name,
            '{employee_phone}' => $this->employee_phone,
            '{employee_photo}' => $employee_photo,
            '{tomorrow_date}' => Functions\DateTime::format_date($this->booking_start),
            '{total_price}' => Functions\Price::format($this->total_price),
            '{cancellation_reason}' => $this->cancellation_reason,
            '{online_meeting_host_url}' => $this->online_meeting_host_url,
            '{online_meeting_join_url}' => $this->online_meeting_join_url,
            '{online_meeting_password}' => $this->online_meeting_password,
        );
        $codes['{cancel_booking}'] = $format == 'html'
            ? sprintf('<a href="%1$s">%1$s</a>', $codes['{cancel_booking_url}'])
            : $codes['{cancel_booking_url}'];

        return strtr($text, $codes);
    }

    public function refresh()
    {
        $order = $this->get_order();
        $service = $this->get_service();

        $this->category_name = $service->get_service()->get_translated_category_name();
        $this->custom_fields = $service->get_cb()->get_formatted_custom_fields('text');
        $this->custom_fields_2col = $service->get_cb()->get_formatted_custom_fields('html');
        $this->service_info = $service->get_service()->get_translated_info();
        $this->service_name = $service->get_service()->get_translated_title();
        $this->employee_info = $service->get_staff()->get_translated_info();
        $this->employee_name = $service->get_staff()->get_translated_name();

        if ($order->has_payment()) {
            $this->payment_type = Inc\Mains\Tables\Payment::type_to_string($order->get_payment()->get_type());
        }

    }
}