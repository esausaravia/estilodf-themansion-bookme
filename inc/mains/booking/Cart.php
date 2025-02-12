<?php
namespace Bookme\Inc\Mains\Booking;

use Bookme\Inc;

/**
 * Class Cart
 */
class Cart
{
    /**
     * @var CartItem[]
     */
    private $items = array();

    /**
     * @var UserData
     */
    private $userData = null;

    /**
     * Constructor.
     *
     * @param UserData $userData
     */
    public function __construct(UserData $userData)
    {
        $this->userData = $userData;
    }

    /**
     * Get cart item.
     *
     * @param integer $key
     * @return CartItem|false
     */
    public function get($key)
    {
        if (isset ($this->items[$key])) {
            return $this->items[$key];
        }

        return false;
    }

    /**
     * Add cart item.
     *
     * @param CartItem $item
     * @return integer
     */
    public function add(CartItem $item)
    {
        $this->items[] = $item;
        end($this->items);

        return key($this->items);
    }

    /**
     * Replace given item with other items.
     *
     * @param integer $key
     * @param CartItem[] $items
     * @return array
     */
    public function replace($key, array $items)
    {
        $new_items = array();
        $new_keys = array();
        $new_key = 0;
        foreach ($this->items as $cart_key => $cart_item) {
            if ($cart_key == $key) {
                foreach ($items as $item) {
                    $new_items[$new_key] = $item;
                    $new_keys[] = $new_key;
                    ++$new_key;
                }
            } else {
                $new_items[$new_key++] = $cart_item;
            }
        }
        $this->items = $new_items;

        return $new_keys;
    }

    /**
     * Drop cart item.
     *
     * @param integer $key
     */
    public function drop($key)
    {
        unset ($this->items[$key]);
    }

    /**
     * Get cart items.
     *
     * @return CartItem[]
     */
    public function get_items()
    {
        return $this->items;
    }

    /**
     * Get items data as array.
     *
     * @return array
     */
    public function get_items_data()
    {
        $data = array();
        foreach ($this->items as $key => $item) {
            $data[$key] = $item->get_data();
        }

        return $data;
    }

    /**
     * Set items data from array.
     *
     * @param array $data
     */
    public function set_items_data(array $data)
    {
        foreach ($data as $key => $item_data) {
            $item = new CartItem();
            $item->set_data($item_data);
            $this->items[$key] = $item;
        }
    }

    /**
     * Save all cart items
     *
     * @param Inc\Mains\Booking\DataHolders\Order $order
     * @param string $time_zone
     * @param int $time_zone_offset
     * @param array $booking_numbers
     * @return Inc\Mains\Booking\DataHolders\Order
     */
    public function save(Inc\Mains\Booking\DataHolders\Order $order, $time_zone, $time_zone_offset, &$booking_numbers)
    {
        foreach ($this->get_items() as $i => $cart_item) {
            // Init.
            $payment_id = $order->has_payment() ? $order->get_payment()->get_id() : null;

            $custom_fields = json_encode($cart_item->get('custom_fields'));

            foreach ($cart_item->get('slots') as $slot) {
                list ($service_id, $staff_id, $datetime) = $slot;
                $service = Inc\Mains\Tables\Service::find($service_id);

                /* Check if booking exists with the current data,
                 * if exists then connect the customer with the booking
                 * otherwise create new booking */
                $booking = new Inc\Mains\Tables\Booking();
                $booking->load_by(array(
                    'service_id' => $service_id,
                    'staff_id' => $staff_id,
                    'start_date' => $datetime,
                ));
                if ($booking->is_loaded() == false) {
                    // Create new booking
                    $booking
                        ->set_service_id($service_id)
                        ->set_staff_id($staff_id)
                        ->set_staff_any(count($cart_item->get('staff_ids')) > 1)
                        ->set_start_date($datetime)
                        ->set_end_date(date('Y-m-d H:i:s', strtotime($datetime) + $service->get_duration()))
                        ->save();
                } else {
                    $update = false;
                    if ($booking->get_staff_any() == 1 && count($cart_item->get('staff_ids')) == 1) {
                        // Remove staff Any
                        $booking->set_staff_any(0);
                        $update = true;
                    }
                    if ($update) {
                        $booking->save();
                    }
                }

                // Create CustomerBooking
                $customer_booking = new Inc\Mains\Tables\CustomerBooking();
                $customer_booking
                    ->set_customer($order->get_customer())
                    ->set_booking($booking)
                    ->set_payment_id($payment_id)
                    ->set_number_of_persons($cart_item->get('number_of_persons'))
                    ->set_custom_fields($custom_fields)
                    ->set_status(get_option('bookme_default_booking_status'))
                    ->setTimeZone($time_zone)
                    ->set_time_zone_offset($time_zone_offset)
                    ->set_created_from('frontend')
                    ->set_created(current_time('mysql'))
                    ->save();

                // Online Meeting
                $booking->handle_online_meeting();

                // Google Calendar.
                $booking->handle_google_calendar();

                // Add booking number.
                $booking_numbers[] = $booking->get_id();

                // Only first booking should have custom fields
                $custom_fields = '[]';

                // Add tables to result.
                $item = Inc\Mains\Booking\DataHolders\Service::create($customer_booking)
                    ->set_service($service)
                    ->set_booking($booking);

                $order->add_service($i, $item);

            }
        }

        $booking_numbers = array_unique($booking_numbers);

        return $order;
    }

    /**
     * Get total for cart.
     *
     * @param bool $apply_coupon
     * @return array
     */
    public function get_info($apply_coupon = true)
    {
        $total = $deposit = $item_price = $sub_total = $discount_price = 0;
        $coupon = false;
        $before_coupon = 0;
        $coupon_services = array();
        if ($apply_coupon && $coupon = $this->userData->get_coupon()) {
            global $wpdb;
            $data = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT service_id FROM `".Inc\Mains\Tables\CouponService::get_table_name()."` 
                        WHERE coupon_id = %d",
                    $coupon->get_id()
                ),
                ARRAY_A
            );
            $coupon_services = array();
            foreach ($data as $dt) {
                $coupon_services[$dt['service_id']] = $dt;
            }
        }

        foreach ($this->items as $key => $item) {
            if ($item->get_service()) {
                $discount = array_key_exists($item->get_service()->get_id(), $coupon_services);
                $item_price = $item->get_service_price() * $item->get('number_of_persons');
                if ($discount) {
                    $before_coupon += $item_price;
                }
            }

            $total += $item_price;
            $deposit += $item_price;
        }
        $sub_total = $total;
        if ($coupon) {
            $discount_price = ($before_coupon - $coupon->apply($before_coupon));
            $total -= $discount_price;
            if ($deposit > $total) {
                $deposit = $total;
            }
        }
        $due = 0;

        return array($total, $deposit, $due, $sub_total, $discount_price);
    }

    /**
     * Generate title of cart items (used in payments)
     *
     * @param int $max_length
     * @param bool $multi_byte
     * @return string
     */
    public function get_items_title($max_length = 255, $multi_byte = true)
    {
        reset($this->items);
        $title = $this->get(key($this->items))->get_service()->get_translated_title();
        $tail = '';
        $more = count($this->items) - 1;
        if ($more > 0) {
            $tail = sprintf(_n(' and %d more item', ' and %d more items', $more, 'bookme'), $more);
        }

        if ($multi_byte) {
            if (preg_match_all('/./su', $title . $tail, $matches) > $max_length) {
                $length_tail = preg_match_all('/./su', $tail, $matches);
                $title = preg_replace('/^(.{' . ($max_length - $length_tail - 3) . '}).*/su', '$1', $title) . '...';
            }
        } else {
            if (strlen($title . $tail) > $max_length) {
                while (strlen($title . $tail) + 3 > $max_length) {
                    $title = preg_replace('/.$/su', '', $title);
                }
                $title .= '...';
            }
        }

        return $title . $tail;
    }

    /**
     * Return cart_key for not available booking or NULL.
     *
     * @return int|null
     */
    public function get_failed_cart_key()
    {
        global $wpdb;
        $max_date = date_create('@' . (current_time('timestamp') + Inc\Mains\Functions\System::get_maximum_available_days_for_booking() * DAY_IN_SECONDS))->setTime(0, 0);

        foreach ($this->items as $cart_key => $cart_item) {
            if ($cart_item->get_service()) {
                $service = $cart_item->get_service();
                foreach ($cart_item->get('slots') as $slot) {
                    list ($service_id, $staff_id, $datetime) = $slot;

                    $bound_start = date_create($datetime)->modify('-' . (int)$service->get_padding_left() . ' sec');
                    $bound_end = date_create($datetime)->modify(((int)$service->get_duration() + (int)$service->get_padding_right()) . ' sec');

                    if ($bound_end < $max_date) {

                        $rows = $wpdb->query(
                            $wpdb->prepare(
                                " SELECT 
                                    ss.capacity_max, SUM(ca.number_of_persons) AS total_number_of_persons, DATE_SUB(a.start_date, INTERVAL (COALESCE(s.padding_left,0) ) SECOND) AS bound_left, DATE_ADD(a.end_date, INTERVAL (COALESCE(s.padding_right,0) ) SECOND) AS bound_right 
                                FROM `".Inc\Mains\Tables\CustomerBooking::get_table_name()."` AS `ca` 
                                LEFT JOIN `".Inc\Mains\Tables\Booking::get_table_name()."` AS `a` ON a.id = ca.booking_id 
                                LEFT JOIN `".Inc\Mains\Tables\EmployeeService::get_table_name()."` AS `ss` ON ss.staff_id = a.staff_id AND ss.service_id = a.service_id 
                                LEFT JOIN `".Inc\Mains\Tables\Service::get_table_name()."` AS `s` ON s.id = a.service_id 
                                WHERE `a`.`staff_id` = %d 
                                    AND `ca`.`status` IN ('".Inc\Mains\Tables\CustomerBooking::STATUS_PENDING."','".Inc\Mains\Tables\CustomerBooking::STATUS_APPROVED."') 
                                GROUP BY a.service_id, a.start_date 
                                HAVING (%s > bound_left AND bound_right > %s AND ( total_number_of_persons + %d ) > ss.capacity_max) 
                                ORDER BY `ca`.`id` ASC 
                                LIMIT 1",
                                $staff_id,
                                $bound_end->format('Y-m-d H:i:s'),
                                $bound_start->format('Y-m-d H:i:s'),
                                $cart_item->get('number_of_persons')
                            )
                        );

                        if ($rows != 0) {
                            // Exist intersect booking, time not available.
                            return $cart_key;
                        }
                    } else {
                        return $cart_key;
                    }
                }
            }
        }

        return null;
    }
}