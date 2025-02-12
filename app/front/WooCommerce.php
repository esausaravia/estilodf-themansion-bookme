<?php

namespace Bookme\App\Front;

use Bookme\Inc;

/**
 * Class WooCommerce for Payment Gateway
 */
class WooCommerce extends Inc\Core\App
{
    const VERSION = '1.0';

    private $product_id = 0;
    private $checkout_info = array();

    public function __construct()
    {
        if (get_option('bookme_wc_enabled')) {
            $this->product_id = get_option('bookme_wc_product', 0);

            add_action('woocommerce_add_order_item_meta', array($this, 'add_order_item_meta'), 10, 3);
            add_action('woocommerce_after_order_itemmeta', array($this, 'order_item_meta'), 10, 1);
            add_action('woocommerce_order_item_meta_end', array($this, 'order_item_meta'), 10, 1);
            add_action('woocommerce_before_calculate_totals', array($this, 'before_calculate_totals'), 10, 1);
            add_action('woocommerce_check_cart_items', array($this, 'check_time_slots_for_cart'), 10, 0);


            add_action('woocommerce_order_status_cancelled', array($this, 'cancel_order'), 10, 1);
            add_action('woocommerce_order_status_refunded', array($this, 'cancel_order'), 10, 1);
            add_action('woocommerce_order_status_completed', array($this, 'payment_complete'), 10, 1);
            add_action('woocommerce_order_status_on-hold', array($this, 'payment_complete'), 10, 1);
            add_action('woocommerce_order_status_processing', array($this, 'payment_complete'), 10, 1);

            add_filter('woocommerce_quantity_input_args', array($this, 'quantity_args'), 10, 2);
            add_filter('woocommerce_cart_item_price', array($this, 'get_cart_item_price'), 10, 3);
            add_filter('woocommerce_get_item_data', array($this, 'get_item_data'), 10, 2);
            add_filter('woocommerce_checkout_get_value', array($this, 'checkout_value'), 10, 2);

            parent::__construct();
        }
    }

    /**
     * Add product to cart via ajax
     */
    public function perform_add_to_wc_cart()
    {
        if (!get_option('bookme_wc_enabled')) {
            exit;
        }
        $response = null;
        $userData = new Inc\Mains\Booking\UserData(Inc\Mains\Functions\Request::get_parameter('form_id'));

        if ($userData->load()) {
            $session = WC()->session;

            if ($session instanceof \WC_Session_Handler && $session->get_session_cookie() === false) {
                $session->set_customer_session_cookie(true);
            }
            if ($userData->cart->get_failed_cart_key() === null) {
                $cart_item = $this->get_intersected_item($userData->cart->get_items());
                if ($cart_item === null) {
                    if ( get_option( 'bookme_customer_first_last_name' ) ) {
                        $first_name = $userData->get('first_name');
                        $last_name  = $userData->get('last_name');
                    } else {
                        $full_name  = $userData->get('full_name');
                        $first_name = strtok( $full_name, ' ' );
                        $last_name  = strtok( '' );
                    }
                    $bookme = array(
                        'version' => self::VERSION,
                        'email' => $userData->get('email'),
                        'items' => $userData->cart->get_items_data(),
                        'name' => $userData->get('full_name'),
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'phone' => $userData->get('phone'),
                        'time_zone_offset' => $userData->get('time_zone_offset'),
                    );

                    // Qnt 1 product in $userData exist value with number_of_persons
                    WC()->cart->add_to_cart($this->product_id, 1, '', array(), array('bookme' => $bookme));

                    $response = array('success' => true);
                } else {
                    $response = array('success' => false, 'error' => esc_html__('Selected time slot is not available anymore. Please, choose another time slot.', 'bookme'),);
                }
            } else {
                $response = array('success' => false, 'error' => esc_html__('Selected time slot is not available anymore. Please, choose another time slot.', 'bookme'),);
            }
        } else {
            $response = array('success' => false, 'error' => esc_html__('Invalid session data.', 'bookme'));
        }
        wp_send_json($response);
    }

    /**
     * check the availability of all bookings that are in the cart
     */
    public function check_time_slots_for_cart()
    {
        $recalculate_totals = false;
        foreach (WC()->cart->get_cart() as $wc_key => $wc_item) {
            if (array_key_exists('bookme', $wc_item)) {
                if (!isset($wc_item['bookme']['version'])) {
                    if ($this->migration($wc_key, $wc_item) === false) {
                        // Removed item from cart.
                        continue;
                    }
                }
                $userData = new Inc\Mains\Booking\UserData(null);
                $userData->fill_data($wc_item['bookme']);
                $userData->cart->set_items_data($wc_item['bookme']['items']);
                if ($wc_item['quantity'] > 1) {
                    foreach ($userData->cart->get_items() as $cart_item) {
                        // Equal appointments increase quantity
                        $cart_item->set('number_of_persons', $cart_item->get('number_of_persons') * $wc_item['quantity']);
                    }
                }
                // Check if appointment's time is still available
                $failed_cart_key = $userData->cart->get_failed_cart_key();
                if ($failed_cart_key !== null) {
                    $cart_item = $userData->cart->get($failed_cart_key);
                    $slot = $cart_item->get('slots');
                    $notice = strtr(esc_html__('Sorry, the time slot %date_time% for %service% has been already booked.', 'bookme'),
                        array(
                            '%service%' => '<strong>' . $cart_item->get_service()->get_translated_title() . '</strong>',
                            '%date_time%' => Inc\Mains\Functions\DateTime::format_date_time($slot[0][2])
                        ));
                    wc_print_notice($notice, 'notice');
                    WC()->cart->set_quantity($wc_key, 0, false);
                    $recalculate_totals = true;
                }
            }
        }
        if ($recalculate_totals) {
            WC()->cart->calculate_totals();
        }
    }

    /**
     * Assign checkout value
     *
     * @param $null
     * @param $field_name
     * @return string|null
     */
    public function checkout_value($null, $field_name)
    {
        if (empty($this->checkout_info)) {
            foreach (WC()->cart->get_cart() as $wc_key => $wc_item) {
                if (array_key_exists('bookme', $wc_item)) {
                    if (!isset($wc_item['bookme']['version']) || $wc_item['bookme']['version'] < self::VERSION) {
                        if ($this->migration($wc_key, $wc_item) === false) {
                            // Removed item from cart.
                            continue;
                        }
                    }
                    $this->checkout_info = array(
                        'billing_email' => $wc_item['bookme']['email'],
                        'billing_phone' => $wc_item['bookme']['phone']
                    );
                    if (!$wc_item['bookme']['first_name']) {
                        $name = $wc_item['bookme']['name'];
                        $this->checkout_info['billing_first_name'] = strtok($name, ' ');
                        $this->checkout_info['billing_last_name'] = strtok(' ');
                    } else {
                        $this->checkout_info['billing_first_name'] = $wc_item['bookme']['first_name'];
                        $this->checkout_info['billing_last_name'] = $wc_item['bookme']['last_name'];
                    }
                    break;
                }
            }
        }
        if (array_key_exists($field_name, $this->checkout_info)) {
            return $this->checkout_info[$field_name];
        }

        return null;
    }

    /**
     * save bookings after checkout
     *
     * @param $order_id
     */
    public function payment_complete($order_id)
    {
        $order = new \WC_Order($order_id);
        foreach ($order->get_items() as $item_id => $order_item) {
            $data = wc_get_order_item_meta($item_id, 'bookme');
            if ($data && !isset ($data['processed'])) {
                $userData = new Inc\Mains\Booking\UserData(null);
                $userData->fill_data($data);
                $userData->cart->set_items_data($data['items']);
                if ($order_item['qty'] > 1) {
                    foreach ($userData->cart->get_items() as $cart_item) {
                        $cart_item->set('number_of_persons', $cart_item->get('number_of_persons') * $order_item['qty']);
                    }
                }
                list($total, $deposit) = $userData->cart->get_info();
                $payment = new Inc\Mains\Tables\Payment();
                $payment
                    ->set_type(Inc\Mains\Tables\Payment::TYPE_WOOCOMMERCE)
                    ->set_status(Inc\Mains\Tables\Payment::STATUS_COMPLETED)
                    ->set_total($total)
                    ->set_paid($deposit)
                    ->set_created(current_time('mysql'))
                    ->save();
                $order = $userData->save($payment);
                $payment->set_details($order)->save();
                // Mark item as processed.
                $data['processed'] = true;
                $data['ca_list'] = array();
                foreach ($order->get_flat_services() as $item) {
                    $data['ca_list'][] = $item->get_cb()->get_id();
                }
                wc_update_order_item_meta($item_id, 'bookme', $data);
                Inc\Mains\Notification\Sender::send_from_cart($order);
            }
        }
    }

    /**
     * Cancel booking
     *
     * @param $order_id
     */
    public function cancel_order($order_id)
    {
        global $wpdb;
        $order = new \WC_Order($order_id);
        foreach ($order->get_items() as $item_id => $order_item) {
            $data = wc_get_order_item_meta($item_id, 'bookme');
            if (isset ($data['processed'], $data['ca_ids']) && $data['processed']) {
                /** @var Inc\Mains\Tables\CustomerBooking[] $ca_list */
                $data = $wpdb->get_results(
                        " SELECT * FROM `".Inc\Mains\Tables\CustomerBooking::get_table_name()."` WHERE `id` IN (" . implode(',', $data['ca_ids']) . ")"
                    ,
                    ARRAY_A
                );
                $ca_list = Inc\Mains\Functions\System::bind_data_with_table( Inc\Mains\Tables\CustomerBooking::class,$data);
                foreach ($ca_list as $ca) {
                    $ca->cancel();
                }
                $data['ca_ids'] = array();
                wc_update_order_item_meta($item_id, 'bookme', $data);
            }
        }
    }

    /**
     * Change attr for WC quantity input
     *
     * @param array $args
     * @param \WC_Product $product
     * @return mixed
     */
    public function quantity_args($args, $product)
    {
        if ($product->get_id() == $this->product_id) {
            $args['max_value'] = $args['input_value'];
            $args['min_value'] = $args['input_value'];
        }

        return $args;
    }

    /**
     * Change item price in cart.
     *
     * @param \WC_Cart $cart_object
     */
    public function before_calculate_totals($cart_object)
    {
        foreach ($cart_object->cart_contents as $wc_key => $wc_item) {
            if (isset ($wc_item['bookme'])) {
                if (!isset($wc_item['bookme']['version']) || $wc_item['bookme']['version'] < self::VERSION) {
                    if ($this->migration($wc_key, $wc_item) === false) {
                        // Removed item from cart.
                        continue;
                    }
                }
                $userData = new Inc\Mains\Booking\UserData(null);
                $userData->fill_data($wc_item['bookme']);
                $userData->cart->set_items_data($wc_item['bookme']['items']);
                list(, $deposit) = $userData->cart->get_info();
                /** @var \WC_Product $wc_item ['data'] */
                $wc_item['data']->set_price($deposit);
            }
        }
    }

    /**
     * Add meta data
     *
     * @param $item_id
     * @param $values
     * @param $wc_key
     */
    public function add_order_item_meta($item_id, $values, $wc_key)
    {
        if (isset ($values['bookme'])) {
            wc_update_order_item_meta($item_id, 'bookme', $values['bookme']);
        }
    }

    /**
     * Get item data for cart
     *
     * @param $other_data
     * @param $wc_item
     * @return array
     */
    public function get_item_data($other_data, $wc_item)
    {
        if (isset ($wc_item['bookme'])) {
            $userData = new Inc\Mains\Booking\UserData(null);
            $info = array();
            if (isset ($wc_item['bookme']['version']) && $wc_item['bookme']['version'] == self::VERSION) {
                $userData->fill_data($wc_item['bookme']);
                if (Inc\Mains\Functions\System::use_client_time_zone()) {
                    $userData->apply_time_zone();
                }
                $userData->cart->set_items_data($wc_item['bookme']['items']);
                foreach ($userData->cart->get_items() as $cart_item) {
                    $slots = $cart_item->get('slots');
                    $client_dp = Inc\Mains\Functions\Date::from_string($slots[0][2])->to_client_tz();
                    $service = $cart_item->get_service();
                    $staff = $cart_item->get_staff();
                    $codes = array(
                        '{booking_date}' => $client_dp->format_i18n_date(),
                        '{booking_time}' => $client_dp->format_i18n_time(),
                        '{category_name}' => $service ? $service->get_translated_category_name() : '',
                        '{number_of_persons}' => $cart_item->get('number_of_persons'),
                        '{service_info}' => $service ? $service->get_translated_info() : '',
                        '{service_name}' => $service ? $service->get_translated_title() : esc_html__('Service was not found', 'bookme'),
                        '{service_price}' => $service ? Inc\Mains\Functions\Price::format($cart_item->get_service_price()) : '',
                        '{employee_info}' => $staff ? $staff->get_translated_info() : '',
                        '{employee_name}' => $staff ? $staff->get_translated_name() : '',

                        // deprecated
                        '{no_of_person}' => $cart_item->get('number_of_persons'),
                    );

                    $info[] = strtr(Inc\Mains\Functions\System::get_translated_option('bookme_lang_wc_cart_data_value'), $codes);
                }
            }
            $other_data[] = array('name' => Inc\Mains\Functions\System::get_translated_option('bookme_lang_wc_cart_data_name'), 'value' => implode(PHP_EOL . PHP_EOL, $info));
        }

        return $other_data;
    }

    /**
     * Print booking details inside order items in the backend
     *
     * @param int $item_id
     */
    public function order_item_meta($item_id)
    {
        $data = wc_get_order_item_meta($item_id, 'bookme');
        if ($data) {
            $other_data = $this->get_item_data(array(), array('bookme' => $data));
            echo '<br/>' . $other_data[0]['name'] . '<br/>' . nl2br($other_data[0]['value']);
        }
    }

    /**
     * Find intersected CartItem with items in WC Cart.
     *
     *
     * @param Inc\Mains\Booking\CartItem[] $new_items
     * @return Inc\Mains\Booking\CartItem
     */
    private function get_intersected_item(array $new_items)
    {
        /** @var Inc\Mains\Booking\CartItem[] $wc_items */
        $wc_items = array();
        $cart_item = new Inc\Mains\Booking\CartItem();
        foreach (WC()->cart->get_cart() as $wc_key => $wc_item) {
            if (array_key_exists('bookme', $wc_item)) {
                if (!isset($wc_item['bookme']['version'])) {
                    if ($this->migration($wc_key, $wc_item) === false) {
                        // Removed item from cart.
                        continue;
                    }
                }
                foreach ($wc_item['bookme']['items'] as $item_data) {
                    $entity = clone $cart_item;
                    $entity->set_data($item_data);
                    if ($wc_item['quantity'] > 1) {
                        $nop = $item_data['number_of_persons'] *= $wc_item['quantity'];
                        $entity->set('number_of_persons', $nop);
                    }
                    $wc_items[] = $entity;
                }
            }
        }
        $staff_service = array();
        foreach ($new_items as $cart_key => $candidate_cart_item) {
            foreach ($wc_items as $wc_cart_item) {
                $candidate_staff_id = $candidate_cart_item->get_staff()->get_id();
                $candidate_service_id = $candidate_cart_item->get_service()->get_id();
                $candidate_slots = $candidate_cart_item->get('slots');
                $wc_cart_slots = $wc_cart_item->get('slots');
                if ($wc_cart_item->get_staff() && $candidate_cart_item->get_service()) {
                    if ($candidate_staff_id == $wc_cart_item->get_staff()->get_id()) {
                        // Equal Staff
                        $candidate_start = date_create($candidate_slots[0][2]);
                        $candidate_end = date_create($candidate_slots[0][2])->modify(($candidate_cart_item->get_service()->get_duration()) . ' sec');
                        $wc_cart_start = date_create($wc_cart_slots[0][2]);
                        $wc_cart_end = date_create($wc_cart_slots[0][2])->modify(($wc_cart_item->get_service()->get_duration()) . ' sec');
                        if (($wc_cart_end > $candidate_start) && ($candidate_end > $wc_cart_start)) {
                            // Services intersected.
                            if ($candidate_start == $wc_cart_start) {
                                // Equal Staff/Service/Start
                                if (!isset($staff_service[$candidate_staff_id][$candidate_service_id])) {
                                    $staff_service[$candidate_staff_id][$candidate_service_id] = $this->get_capacity($candidate_staff_id, $candidate_service_id);
                                }
                                $allow_capacity = $staff_service[$candidate_staff_id][$candidate_service_id];
                                $nop = $candidate_cart_item->get('number_of_persons') + $wc_cart_item->get('number_of_persons');
                                if ($nop > $allow_capacity) {
                                    // Equal Staff/Service/Start and number_of_persons > capacity
                                    return $candidate_cart_item;
                                }
                            } else {
                                // Intersect Services for some Staff
                                return $candidate_cart_item;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param $product_price
     * @param $wc_item
     * @param $cart_item_key
     * @return mixed
     */
    public function get_cart_item_price($product_price, $wc_item, $cart_item_key)
    {
        if (isset ($wc_item['bookme'])) {
            $userData = new Inc\Mains\Booking\UserData(null);
            $userData->fill_data($wc_item['bookme']);
            $userData->cart->set_items_data($wc_item['bookme']['items']);
            list(, $deposit) = $userData->cart->get_info();
            $product_price = $deposit;
        }

        return $product_price;
    }

    /**
     * @param int $staff_id
     * @param int $service_id
     * @return int
     */
    private function get_capacity($staff_id, $service_id)
    {
        global $wpdb;
        $staff_service = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT capacity_max FROM `".Inc\Mains\Tables\EmployeeService::get_table_name()."` WHERE `staff_id` = %d AND `service_id` = %d",
                $staff_id,
                $service_id
            ),
            ARRAY_A
        );

        return $staff_service['capacity_max'];
    }

    /**
     * Migration deprecated cart items.
     *
     * @param $wc_key
     * @param $data
     * @return bool
     */
    private function migration($wc_key, $data)
    {
        // The current implementation only remove cart items with deprecated format.
        WC()->cart->set_quantity($wc_key, 0, false);
        WC()->cart->calculate_totals();

        return false;
    }

    /**
     * Register ajax for a class
     */
    protected function register_ajax()
    {
        Inc\Core\Ajax::register_ajax_actions($this, array('app' => 'everyone'), array(), true);
    }
}