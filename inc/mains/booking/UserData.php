<?php

namespace Bookme\Inc\Mains\Booking;

use Bookme\Inc;
use Bookme\Inc\Mains\Functions;

/**
 * Class UserData
 */
class UserData
{
    private $form_id = null;

    /**
     * Store user data in session
     * @var array
     */
    private $data = array(
        'time_zone' => null,
        'time_zone_offset' => null,
        'date' => null,
        'days' => null,
        'time_from' => null,
        'time_to' => null,
        'slots' => array(),
        'full_name' => null,
        'first_name' => null,
        'last_name' => null,
        'email' => null,
        'phone' => null,
        'coupon' => null,
        'edit_cart_keys' => array()
    );

    /** @var Cart */
    public $cart = null;
    /** @var Sequence*/
    public $sequence = null;
    /** @var Inc\Mains\Tables\Coupon|null */
    private $coupon = null;
    /** @var array */
    private $booking_numbers = array();
    /** @var integer|null */
    private $payment_id = null;

    /**
     * Constructor.
     *
     * @param $form_id
     */
    public function __construct($form_id)
    {
        $this->form_id = $form_id;
        $this->cart = new Cart($this);
        $this->sequence = new Sequence();

        // If logged in then set name, email and if existing customer then also phone.
        $current_user = wp_get_current_user();
        if ($current_user && $current_user->ID) {
            $customer = new Inc\Mains\Tables\Customer();
            if ($customer->load_by(array('wp_user_id' => $current_user->ID))) {
                $this->set('full_name', $customer->get_full_name());
                $this->set('first_name', $customer->get_first_name());
                $this->set('last_name', $customer->get_last_name());
                $this->set('email', $customer->get_email());
                $this->set('phone', $customer->get_phone());
            } else {
                $this->set('full_name', $current_user->display_name);
                $this->set('first_name', $current_user->user_firstname);
                $this->set('last_name', $current_user->user_lastname);
                $this->set('email', $current_user->user_email);
            }
        } elseif (get_option('bookme_customer_save_in_cookie') && isset($_COOKIE['bookme-customer-full-name'])) {
            $this->set('full_name', $_COOKIE['bookme-customer-full-name']);
            $this->set('first_name', $_COOKIE['bookme-customer-first-name']);
            $this->set('last_name', $_COOKIE['bookme-customer-last-name']);
            $this->set('email', $_COOKIE['bookme-customer-email']);
            $this->set('phone', $_COOKIE['bookme-customer-phone']);
        }

        // Register destructor (should work in cases when regular __destruct() does not work).
        register_shutdown_function(array($this, 'destruct'));
    }

    /**
     * Set data parameter.
     *
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Get data parameter.
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return false;
    }

    /**
     * Load data from session.
     *
     * @return bool
     */
    public function load()
    {
        $data = Functions\Session::getFormVar($this->form_id, 'data');
        if ($data !== null) {
            // Restore data.
            $this->data = $data;
            $this->sequence->set_items_data(Functions\Session::getFormVar($this->form_id, 'sequence'));
            $this->cart->set_items_data(Functions\Session::getFormVar($this->form_id, 'cart'));
            $this->booking_numbers = Functions\Session::getFormVar($this->form_id, 'booking_numbers');
            $this->payment_id = Functions\Session::getFormVar($this->form_id, 'payment_id');
            // Client time zone.
            if (Functions\System::use_client_time_zone()) {
                $this->apply_time_zone();
            }

            return true;
        }

        return false;
    }

    /**
     * Partially update data in session.
     *
     * @param array $data
     */
    public function fill_data(array $data)
    {
        foreach ($data as $name => $value) {
            if (array_key_exists($name, $this->data)) {
                /** Fill array @see UserData::$data */
                $this->set($name, $value);
            } elseif ($name == 'sequence') {
                $sequence_items = $this->sequence->get_items();
                $this->sequence->clear();
                foreach ($value as $key => $_data) {
                    $item = isset ($sequence_items[$key]) ? $sequence_items[$key] : new SequenceItem();
                    $this->sequence->add($item);
                    foreach ($_data as $_name => $_value) {
                        $item->set($_name, $_value);
                    }
                }
            } elseif ($name == 'cart') {
                foreach ($value as $key => $_data) {
                    $item = $this->cart->get($key);
                    foreach ($_data as $_name => $_value) {
                        $item->set($_name, $_value);
                    }
                }
            }
        }
    }

    public function reset_sequence()
    {
        global $wpdb;
        $this->sequence->clear();
        $this->sequence->add(new SequenceItem());

        // Set up default parameters.
        $prior_time = Functions\System::get_minimum_time_prior_booking();
        $this->set('date', date('Y-m-d', current_time('timestamp') + $prior_time));

        $times = $wpdb->get_row(
                " SELECT 
                    SUBSTRING_INDEX(MIN(ss.start_time), ':', 2) AS min_end_time,
                    SUBSTRING_INDEX(MAX(ss.end_time), ':', 2) AS max_end_time
                FROM `".Inc\Mains\Tables\EmployeeSchedule::get_table_name()."` ss 
                LEFT JOIN `".Inc\Mains\Tables\Employee::get_table_name()."` AS `s` ON s.id = ss.staff_id 
                WHERE `ss`.`start_time` IS NOT NULL AND `s`.`visibility` != 'private'",
            ARRAY_A
        );

        $this->set('time_from', $times['min_end_time']);
        $this->set('time_to', $times['max_end_time']);
        $this->set('slots', array());
        $this->set('edit_cart_keys', array());
    }

    /**
     * Set sequence from given cart item.
     *
     * @param integer $cart_key
     */
    public function set_sequence_from_cart_item($cart_key)
    {
        $cart_item = $this->cart->get($cart_key);
        $this->set(
            'date',
            Inc\Mains\Functions\Date::now()
                ->modify(Functions\System::get_minimum_time_prior_booking())
                ->format('Y-m-d')
        );
        $this->set('days', $cart_item->get('days'));
        $this->set('time_from', $cart_item->get('time_from'));
        $this->set('time_to', $cart_item->get('time_to'));
        $this->set('slots', $cart_item->get('slots'));

        $sequence_item = new SequenceItem();
        $sequence_item->set('service_id', $cart_item->get('service_id'));
        $sequence_item->set('staff_ids', $cart_item->get('staff_ids'));
        $sequence_item->set('number_of_persons', $cart_item->get('number_of_persons'));
        $sequence_item->set('quantity', 1);

        $this->sequence->clear();
        $this->sequence->add($sequence_item);
    }

    /**
     * Add sequence items to cart.
     */
    public function add_sequence_to_cart()
    {
        $cart_items = array();
        $edit_cart_keys = $this->get('edit_cart_keys');
        $eck_idx = 0;
        $slots = $this->get('slots');
        $slots_idx = 0;

        foreach ($this->sequence->get_items() as $sequence_item) {

            $cart_item_slots = array();

            $cart_item_slots[] = $slots[$slots_idx++];
            $cart_item = new CartItem();

            $cart_item->set('date', $this->get('date'));
            $cart_item->set('time_from', $this->get('time_from'));
            $cart_item->set('time_to', $this->get('time_to'));

            $cart_item->set('number_of_persons', $sequence_item->get('number_of_persons'));
            $cart_item->set('service_id', $sequence_item->get('service_id'));
            $cart_item->set('slots', $cart_item_slots);
            $cart_item->set('staff_ids', $sequence_item->get('staff_ids'));
            if (isset ($edit_cart_keys[$eck_idx])) {
                $cart_item->set('custom_fields', $this->cart->get($edit_cart_keys[$eck_idx])->get('custom_fields'));
                ++$eck_idx;
            }

            $cart_items[] = $cart_item;

        }

        $count = count($edit_cart_keys);
        $inserted_keys = array();

        if ($count) {
            for ($i = $count - 1; $i > 0; --$i) {
                $this->cart->drop($edit_cart_keys[$i]);
            }
            $inserted_keys = $this->cart->replace($edit_cart_keys[0], $cart_items);
        } else {
            foreach ($cart_items as $cart_item) {
                $inserted_keys[] = $this->cart->add($cart_item);
            }
        }

        $this->set('edit_cart_keys', $inserted_keys);
    }

    /**
     * Validate fields.
     *
     * @param $data
     * @return array
     */
    public function validate($data)
    {
        $validator = new Functions\Validator();
        foreach ($data as $field_name => $field_value) {
            switch ($field_name) {
                case 'service_id':
                    $validator->validate_number($field_name, $field_value);
                    break;
                case 'date':
                    $validator->validate_date($field_name, $field_value, true);
                    break;
                case 'time_from':
                case 'time_to':
                    $validator->validate_time($field_name, $field_value, true);
                    break;
                case 'full_name':
                case 'first_name':
                case 'last_name':
                    $validator->validate_name($field_name, $field_value);
                    break;
                case 'email':
                    $validator->validate_email($field_name, $data);
                    break;
                case 'phone':
                    $validator->validate_phone($field_name, $field_value, Functions\System::phone_required());
                    break;
                case 'cart':
                    $validator->validate_cart($field_value, $data['form_id']);
                    break;
                case 'g_recaptcha_response':
                    $validator->validate_recaptcha($field_value);
                    break;
                default:
            }
        }
        // Post validators.
        if (isset ($data['phone']) || isset ($data['email'])) {
            $validator->validate_customer($data, $this);
        }

        return $validator->get_errors();
    }

    /**
     * Create new booking
     *
     * @param Inc\Mains\Tables\Payment $payment
     * @return Inc\Mains\Booking\DataHolders\Order
     */
    public function save($payment = null)
    {
        // Find or create customer.
        $user_id = get_current_user_id();
        $customer = new Inc\Mains\Tables\Customer();
        if ($user_id > 0) {
            // Find customer by WP user id
            $customer->load_by(array('wp_user_id' => $user_id));
        }
        if (!$customer->is_loaded()) {

            if ( get_option('bookme_customer_allow_duplicate') ) {
                $customer_data = array(
                    'email' => $this->get('email'),
                    'phone' => $this->get('phone'),
                );
                if ( Functions\System::show_first_last_name()  ) {
                    $customer_data['first_name'] = $this->get('first_name');
                    $customer_data['last_name']  = $this->get('last_name');
                } else {
                    $customer_data['full_name'] = $this->get('full_name');
                }
                if ( $this->get('email') != '' ) {
                    $customer_data['email'] = $this->get('email');
                }
                if ( $this->get('phone') != '' ) {
                    $customer_data['phone'] = $this->get('phone');
                }
                $customer->load_by( $customer_data );
            } else {
                $customer->load_by(
                    Functions\System::phone_required()
                        ? array('phone' => $this->get('phone'))
                        : array('email' => $this->get('email'))
                );
                if (!$customer->is_loaded()) {
                    $customer->load_by(
                        Functions\System::phone_required()
                            ? array('email' => $this->get('email'), 'phone' => '')
                            : array('phone' => $this->get('phone'), 'email' => '')
                    );
                }
            }
        }
        foreach (array('full_name', 'first_name', 'last_name', 'phone', 'email') as $field) {
            if ($this->get($field) != '') {
                // Overwrite only if value is not empty.
                $customer->set_fields(array($field => $this->get($field)));
            }
        }
        if (get_option('bookme_customer_create_account', 0) && !$customer->get_wp_user_id()) {
            // Create WP user and link it to customer.
            $customer->set_wp_user_id($user_id);
        }
        $customer->save();

        // Order.
        $order = Inc\Mains\Booking\DataHolders\Order::create($customer);

        // Payment.
        if ($payment) {
            $order->set_payment($payment);
            $this->payment_id = $payment->get_id();
        }

        if (get_option('bookme_customer_save_in_cookie')) {
            setcookie('bookme-customer-full-name', $customer->get_full_name(), time() + YEAR_IN_SECONDS);
            setcookie('bookme-customer-first-name', $customer->get_first_name(), time() + YEAR_IN_SECONDS);
            setcookie('bookme-customer-last-name', $customer->get_last_name(), time() + YEAR_IN_SECONDS);
            setcookie('bookme-customer-phone', $customer->get_phone(), time() + YEAR_IN_SECONDS);
            setcookie('bookme-customer-email', $customer->get_email(), time() + YEAR_IN_SECONDS);
        }

        return $this->cart->save($order, $this->get('time_zone'), $this->get('time_zone_offset'), $this->booking_numbers);
    }

    /**
     * Get coupon.
     *
     * @return Inc\Mains\Tables\Coupon|false
     */
    public function get_coupon()
    {
        if ($this->coupon === null) {
            $coupon = new Inc\Mains\Tables\Coupon();
            $coupon->load_by(array(
                'code' => $this->get('coupon'),
            ));
            if ($coupon->is_loaded() && $coupon->get_used() < $coupon->get_usage_limit()) {
                $this->coupon = $coupon;
            } else {
                $this->coupon = false;
            }
        }

        return $this->coupon;
    }

    /**
     * Set payment ( PayPal, 2Checkout, Mollie ) transaction status.
     *
     * @param string $gateway
     * @param string $status
     * @param mixed $data
     */
    public function set_payment_status($gateway, $status, $data = null)
    {
        Functions\Session::setFormVar($this->form_id, 'payment', array(
            'gateway' => $gateway,
            'status' => $status,
            'data' => $data,
        ));
    }

    /**
     * Get and clear ( PayPal, 2Checkout ) transaction status.
     *
     * @return array|false
     */
    public function extract_payment_status()
    {
        if ($status = Functions\Session::getFormVar($this->form_id, 'payment')) {
            Functions\Session::destroyFormVar($this->form_id, 'payment');

            return $status;
        }

        return false;
    }

    /**
     * Get booking numbers.
     *
     * @return array
     */
    public function get_booking_numbers()
    {
        return $this->booking_numbers;
    }

    /**
     * Get payment ID.
     *
     * @return int|null
     */
    public function get_payment_id()
    {
        return $this->payment_id;
    }

    /**
     * Apply client time zone.
     */
    public function apply_time_zone()
    {
        if ($this->data['time_zone_offset'] !== null) {
            Functions\Time::$client_timezone_offset = -$this->data['time_zone_offset'] * MINUTE_IN_SECONDS;
            Functions\Date::$client_timezone = $this->data['time_zone'] ?: Functions\DateTime::guess_time_zone(Functions\Time::$client_timezone_offset);
        }
    }

    /**
     * Destructor used in register_shutdown_function.
     */
    public function destruct()
    {
        $this->save_in_session();
    }

    /**
     * Save data in session manually
     */
    public function save_in_session()
    {
        Functions\Session::setFormVar($this->form_id, 'data', $this->data);
        Functions\Session::setFormVar($this->form_id, 'cart', $this->cart->get_items_data());
        Functions\Session::setFormVar($this->form_id, 'sequence', $this->sequence->get_items_data());
        Functions\Session::setFormVar($this->form_id, 'booking_numbers', $this->booking_numbers);
        Functions\Session::setFormVar($this->form_id, 'payment_id', $this->payment_id);
        Functions\Session::setFormVar($this->form_id, 'last_touched', time());
    }
}