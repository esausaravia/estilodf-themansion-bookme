<?php
namespace Bookme\App\Front;

use Bookme\Inc;
use Bookme\Inc\Mains\Functions\System;

/**
 * Class CustomerBookings for shortcode
 */
class CustomerBookings extends Inc\Core\App
{
    /**
     * Execute customer bookings shortcode
     * @param $attributes
     * @return string|void
     */
    public function execute($attributes)
    {
        // disable cache
        System::no_cache();

        $customer = new Inc\Mains\Tables\Customer();
        $customer->load_by(array('wp_user_id' => get_current_user_id()));
        if ($customer->is_loaded()) {
            $bookings = $this->translate_bookings($customer->get_future_bookings());
            $old_bookings = $customer->get_old_bookings(1, 1);
            $more = !empty ($old_bookings['bookings']);
        } else {
            $bookings = array();
            $more = false;
        }

        $allow_cancel = current_time('timestamp');
        $minimum_time_before_cancel = (int)get_option('bookme_min_time_before_cancel', 0);
        if ($minimum_time_before_cancel > 0) {
            $allow_cancel += $minimum_time_before_cancel * HOUR_IN_SECONDS;
        }

        // AJAX url with WPML support
        global $sitepress;

        $ajax_url = admin_url('admin-ajax.php');
        if ($sitepress instanceof \SitePress) {
            $ajax_url .= (strpos($ajax_url, '?') ? '&' : '?') . 'lang=' . $sitepress->get_current_language();
        }

        $titles = array(
            'category' => System::get_translated_option('bookme_lang_title_category'),
            'service' => System::get_translated_option('bookme_lang_title_service'),
            'staff' => System::get_translated_option('bookme_lang_title_employee'),
            'price' => __('Price', 'bookme'),
            'date' => __('Date', 'bookme'),
            'time' => __('Time', 'bookme'),
            'status' => __('Status', 'bookme'),
            'cancel' => __('Cancel', 'bookme'),
        );
        foreach (System::get_translated_custom_fields() as $field) {
            if (!in_array($field->type, array('text-content'))) {
                $titles[$field->id] = $field->label;
            }
        }

        $cancel_url = add_query_arg(array('action' => 'bookme_cancel_booking', 'csrf_token' => System::get_security_token()), $ajax_url);

        return Inc\Core\Template::create('customer_bookings/shortcode', true)->display(compact('ajax_url', 'bookings', 'attributes', 'cancel_url', 'titles', 'more', 'allow_cancel'), false);
    }

    /**
     * Get old bookings
     */
    public function perform_get_old_bookings()
    {
        $customer = new Inc\Mains\Tables\Customer();
        $customer->load_by(array('wp_user_id' => get_current_user_id()));
        $old = $customer->get_old_bookings(Inc\Mains\Functions\Request::get_parameter('page'), 30);
        $bookings = $this->translate_bookings($old['bookings']);
        $custom_fields = Inc\Mains\Functions\Request::get_parameter('custom_fields') ? explode(',', Inc\Mains\Functions\Request::get_parameter('custom_fields')) : array();
        $allow_cancel = current_time('timestamp') + (int)get_option('bookme_min_time_before_cancel', 0);
        $columns = (array)Inc\Mains\Functions\Request::get_parameter('columns');
        $with_cancel = in_array('cancel', $columns);
        $html = Inc\Core\Template::create('customer_bookings/rows', true)->display(compact('bookings', 'columns', 'allow_cancel', 'custom_fields', 'with_cancel'), false);

        wp_send_json_success(array('html' => $html, 'more' => $old['more']));
    }

    /**
     * WPML translation
     *
     * @param array $bookings
     * @return array
     */
    private function translate_bookings(array $bookings)
    {
        foreach ($bookings as &$booking) {
            $category = new Inc\Mains\Tables\Category(array('id' => $booking['category_id'], 'name' => $booking['category']));
            $service = new Inc\Mains\Tables\Service(array('id' => $booking['service_id'], 'title' => $booking['service']));
            $staff = new Inc\Mains\Tables\Employee(array('id' => $booking['staff_id'], 'full_name' => $booking['staff']));
            $booking['category'] = $category->get_translated_name();
            $booking['service'] = $service->get_translated_title();

            $any = sprintf(' (%s)', System::get_translated_option('bookme_lang_select_employee'));
            $booking['staff'] = $staff->get_translated_name() . ($booking['staff_any'] ? $any : '');

            // Prepare custom fields.
            $custom_fields = array();
            $cb = new Inc\Mains\Tables\CustomerBooking($booking);
            foreach ($cb->get_translated_custom_fields() as $field) {
                $custom_fields[$field['id']] = $field['value'];
            }
            $booking['custom_fields'] = $custom_fields;
        }

        return $bookings;
    }

    /**
     * Register ajax for a class
     */
    protected function register_ajax()
    {
        Inc\Core\Ajax::register_ajax_actions($this, array('app' => 'user'));
    }
}