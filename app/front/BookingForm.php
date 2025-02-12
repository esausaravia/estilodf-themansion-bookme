<?php

namespace Bookme\App\Front;

use Bookme\Inc;
use Bookme\Inc\Mains\Booking\UserData;
use Bookme\Inc\Mains\Functions\Request;
use Bookme\Inc\Mains\Functions\Session;
use Bookme\Inc\Mains\Functions\System;

/**
 * Class BookingForm for shortcode
 */
class BookingForm extends Inc\Core\App
{
    /**
     * Execute booking form shortcode
     * @param $attributes
     * @return string|void
     */
    public function execute($attributes)
    {
        System::no_cache();
        $form_id = uniqid();

        // Custom CSS.
        $custom_css = get_option('bookme_form_custom_css');

        // Find bookings with any of payment statuses.
        $status = array('status' => 'new');
        foreach (Session::getAllFormsData() as $saved_form_id => $data) {
            if (isset ($data['payment'])) {
                if (!isset ($data['payment']['processed'])) {
                    switch ($data['payment']['status']) {
                        case 'success':
                        case 'processing':
                            $form_id = $saved_form_id;
                            $status = array('status' => 'finished');
                            break;
                        case 'cancelled':
                        case 'error':
                            $form_id = $saved_form_id;
                            end($data['cart']);
                            $status = array('status' => 'cancelled', 'cart_key' => key($data['cart']));
                            break;
                    }
                    // Mark this form as processed for cases when there are more than 1 booking form on the page.
                    $data['payment']['processed'] = true;
                    Session::setFormVar($saved_form_id, 'payment', $data['payment']);
                }
            } elseif ($data['last_touched'] + 30 * MINUTE_IN_SECONDS < time()) {
                // Destroy forms older than 30 min.
                Session::destroyFormData($saved_form_id);
            }
        }

        // Handle shortcode attributes.
        $fields_to_hide = isset ($attributes['hide']) ? explode(',', $attributes['hide']) : array();
        $staff_member_id = (int) ( isset( $_GET['staff_id'] ) ? $_GET['staff_id'] : ( isset( $attributes['staff_member_id'] ) ? $attributes['staff_member_id'] : 0 ) );

        $attrs = array(
            'category_id' => (int)( isset( $_GET['cat_id'] ) ? $_GET['cat_id'] : ( isset( $attributes['category_id'] ) ? $attributes['category_id'] : 0 ) ),
            'service_id' => (int)( isset( $_GET['service_id'] ) ? $_GET['service_id'] : ( isset( $attributes['service_id'] ) ? $attributes['service_id'] : 0 ) ),
            'staff_member_id' => $staff_member_id,
            'hide_categories' => in_array('categories', $fields_to_hide),
            'hide_services' => in_array('services', $fields_to_hide),
            'hide_staff_members' => in_array('staff_members', $fields_to_hide)
                && (get_option('bookme_required_employee') ? $staff_member_id : true),
            'show_number_of_persons' => (bool) ( isset( $attributes['show_number_of_persons'] ) ? $attributes['show_number_of_persons'] : false ),
            'show_service_duration' => (bool)get_option('bookme_service_name_with_duration'),
        );

        $service_left = (
            !$attrs['show_number_of_persons'] &&
            $attrs['hide_categories'] &&
            $attrs['hide_services'] &&
            $attrs['service_id'] &&
            $attrs['hide_staff_members']
        );

        // Store attributes in session for later use in Time step.
        Session::setFormVar($form_id, 'attrs', $attrs);
        Session::setFormVar($form_id, 'last_touched', time());


        $skip_steps = array(
            'service_left' => (int)$service_left
        );

        return Inc\Core\Template::create('booking_form/shortcode', true)->display(compact('form_id', 'attrs', 'status', 'skip_steps', 'custom_css'), false);
    }

    /**
     * Save booking data in session.
     * @param bool $only_save
     */
    public function perform_save_session($only_save = false)
    {
        $form_id = Request::get_parameter('form_id');
        $errors = array();
        $user_data = null;
        if ($form_id) {
            $user_data = new UserData($form_id);
            $user_data->load();
            $parameters = Request::get_parameters();
            $errors = $user_data->validate($parameters);
            if (empty ($errors)) {
                if (Request::has_parameter('slots')) {
                    // Decode slots.
                    $parameters['slots'] = json_decode($parameters['slots'], true);
                } elseif (Request::has_parameter('cart')) {
                    foreach ($parameters['cart'] as &$service) {
                        $custom_fields = json_decode($service['custom_fields'], true);
                        // Index the array numerically.
                        $service['custom_fields'] = array_values($custom_fields);
                    }
                    $merge_cf = (int)get_option('bookme_custom_fields_merge');
                    // Copy custom fields to all cart items.
                    $cart = array();
                    foreach ($user_data->cart->get_items() as $cart_key => $_cart_item) {
                        $cart[$cart_key] = System::custom_fields_per_service()
                            ? $parameters['cart'][$merge_cf ? $_cart_item->get_service()->get_id() : $cart_key]
                            : $parameters['cart'][0];
                    }
                    $parameters['cart'] = $cart;
                }
                $user_data->fill_data($parameters);
                $user_data->save_in_session();
            }
        }
        if (!$only_save) {
            $errors['success'] = empty($errors);
            wp_send_json($errors);
        }
    }

    /**
     * Service Step
     */
    public function perform_get_service_step()
    {
        $form_id = Request::get_parameter('form_id');

        if ($form_id) {
            $user_data = new UserData($form_id);
            $user_data->load();

            if (Request::has_parameter('reset_sequence')) {
                $user_data->reset_sequence();
            }

            if (Request::has_parameter('edit_cart_item')) {
                $cart_key = Request::get_parameter('edit_cart_item');
                $user_data->set('edit_cart_keys', array($cart_key));
                $user_data->set_sequence_from_cart_item($cart_key);
            }

            // set the current date for time availability
            $user_data->set(
                'date',
                Inc\Mains\Functions\Date::now()
                    ->modify(System::get_minimum_time_prior_booking())
                    ->format('Y-m-d')
            );

            if (System::use_client_time_zone()) {
                $user_data->set('time_zone', Request::get_parameter('time_zone'));
                $user_data->set('time_zone_offset', Request::get_parameter('time_zone_offset'));
                $user_data->apply_time_zone();
                $user_data->set(
                    'date',
                    Inc\Mains\Functions\Date::now()
                        ->modify(System::get_minimum_time_prior_booking())
                        ->to_client_tz()
                        ->format('Y-m-d')
                );
            }

            $progress_bar = $this->create_progress_bar(1);

            $date = System::get_min_max_date_for_calendar();

            $data = System::get_categories_services_staffs();

            $response = array(
                'success' => true,
                'csrf_token' => System::get_security_token(),
                'categories' => $data['categories'],
                'services' => $data['services'],
                'staff' => $data['staff'],
                'data' => $user_data->sequence->get_items_data()[0], // for now we have only one item in the sequence
                'date_max' => $date['date_max'],
                'date_min' => $date['date_min'],
                'html' => Inc\Core\Template::create('booking_form/service-step', true)
                    ->display(
                        array(
                            'progress_bar' => $progress_bar,
                            'user_data' => $user_data,
                            'show_cart' => $this->show_cart_button($user_data)
                        ),
                        false
                    )
            );
        } else {
            $response = array('success' => false, 'error' => esc_html__('Invalid form id.', 'bookme'));
        }

        wp_send_json($response);
    }

    /**
     * Get number of time slots for calendar
     */
    public function perform_get_availability()
    {
        $this->perform_save_session(true);
        $user_data = new UserData(Request::get_parameter('form_id'));
        $loaded = $user_data->load();

        if (!$loaded && Session::hasFormVar(Request::get_parameter('form_id'), 'attrs')) {
            $loaded = true;
        }
        if ($loaded) {
            $max_end = Inc\Mains\Functions\Date::from_string($user_data->get('date'))->modify('first day of next month');
            $loader = new Inc\Mains\Availability\Loader($user_data, array($this, 'stop_availability'));
            $loader->prepare();
            $loader->end_dp = $max_end;
            $loader->client_end_dp = $max_end->to_client_tz();
            $loader->load();
            $availability = array();
            foreach ($loader->get_slots() as $date => $slot) {
                if($loader->is_service_duration_in_days()){
                    foreach ($slot as $s){
                        $availability[$s->start()->format('Y-m-d')] = sprintf(esc_html_x('%s Available','Available time slots','bookme'), 1);
                    }
                }else{
                    $availability[$date] = sprintf(esc_html_x('%s Available','Available time slots','bookme'), count($slot));
                }
            }

            // Set response.
            $response = array(
                'success' => true,
                'csrf_token' => System::get_security_token(),
                'availability' => $availability
            );

        } else {
            $response = array('success' => false, 'error' => esc_html__('Invalid session data.', 'bookme'));
        }

        // Output JSON response.
        wp_send_json($response);
    }

    /**
     * Custom stop function for time slots
     * @param Inc\Mains\Functions\Date $dp
     * @param $srv_duration_days
     * @param $slots_count
     * @return int
     */
    public function stop_availability(Inc\Mains\Functions\Date $dp, $srv_duration_days, $slots_count)
    {
        $user_data = new UserData(Request::get_parameter('form_id'));
        $user_data->load();
        $end = Inc\Mains\Functions\Date::from_string($user_data->get('date'));
        return $dp->gt($end->modify('first day of next month')->to_client_tz()) ? 1 : 0;
    }

    /**
     * Time step
     */
    public function perform_get_time_step()
    {
        $this->perform_save_session(true);
        $user_data = new UserData(Request::get_parameter('form_id'));
        $loaded = $user_data->load();

        if (!$loaded && Session::hasFormVar(Request::get_parameter('form_id'), 'attrs')) {
            $loaded = true;
        }

        if ($loaded) {
            if (Request::has_parameter('edit_cart_item')) {
                $cart_key = Request::get_parameter('edit_cart_item');
                $user_data->set('edit_cart_keys', array($cart_key));
                $user_data->set_sequence_from_cart_item($cart_key);
            }

            $loader = new Inc\Mains\Availability\Loader($user_data);
            $loader->set_selected_date(Request::get_parameter('date'));
            $loader->prepare()->load();

            $progress_bar = $this->create_progress_bar(2);

            $slots = $user_data->get('slots');
            $selected_date = isset ($slots[0][2]) ? $slots[0][2] : null;



            $response = array(
                'success' => true,
                'csrf_token' => System::get_security_token(),
                'html' => Inc\Core\Template::create('booking_form/time-step', true)
                    ->display(
                        array(
                            'progress_bar' => $progress_bar,
                            'slots' => $loader->get_slots(),
                            'duration_in_days' => $loader->is_service_duration_in_days(),
                            'selected_date' => $selected_date,
                            'show_cart' => $this->show_cart_button($user_data)
                        ),
                        false
                    )
            );
        } else {
            $response = array('success' => false, 'error' => esc_html__('Invalid session data.', 'bookme'));
        }

        // Output JSON response.
        wp_send_json($response);
    }

    /**
     * Cart step
     */
    public function perform_get_cart_step()
    {
        $this->perform_save_session(true);
        $user_data = new UserData(Request::get_parameter('form_id'));

        if ($user_data->load()) {
            if (Request::has_parameter('add_to_cart')) {
                $user_data->add_sequence_to_cart();
            }
            $progress_bar = $this->create_progress_bar(3);

            $items_data = array();
            $cart_columns = get_option('bookme_cart_columns', array());

            foreach ($user_data->cart->get_items() as $cart_key => $cart_item) {
                $slots = $cart_item->get('slots');
                $service_dp = Inc\Mains\Functions\Date::from_string($slots[0][2])->to_client_tz();

                foreach ($cart_columns as $column => $attr) {
                    if ($attr['show']) {
                        switch ($column) {
                            case 'service':
                                $items_data[$cart_key][] = $cart_item->get_service()->get_translated_title();
                                break;
                            case 'date':
                                $items_data[$cart_key][] = $service_dp->format_i18n_date();;
                                break;
                            case 'time':
                                if ($cart_item->get_service()->get_duration() < DAY_IN_SECONDS) {
                                    $items_data[$cart_key][] = $service_dp->format_i18n_time();
                                } else {
                                    $items_data[$cart_key][] = '';
                                }
                                break;
                            case 'employee':
                                $items_data[$cart_key][] = $cart_item->get_staff()->get_translated_name();
                                break;
                            case 'price':
                                if ($cart_item->get('number_of_persons') > 1) {
                                    $items_data[$cart_key][] =
                                        Inc\Mains\Functions\Price::format($cart_item->get_service_price()) . ' &times; '.'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>' . $cart_item->get('number_of_persons') . ' = ' . Inc\Mains\Functions\Price::format($cart_item->get_service_price() * $cart_item->get('number_of_persons'));
                                } else {
                                    $items_data[$cart_key][] = Inc\Mains\Functions\Price::format($cart_item->get_service_price());
                                }
                                break;
                        }
                    }
                }
            }

            $columns = array();
            $position = 0;
            $positions = array();
            foreach ($cart_columns as $column => $attr) {
                if ($attr['show']) {
                    $positions[$column] = $position;

                    switch ($column) {
                        case 'service':
                            $columns[] = System::get_translated_option('bookme_lang_title_service');
                            $position++;
                            break;
                        case 'date':
                            $columns[] = esc_html__('Date', 'bookme');
                            $position++;
                            break;
                        case 'time':
                            $columns[] = esc_html__('Time', 'bookme');
                            $position++;
                            break;
                        case 'employee':
                            $columns[] = System::get_translated_option('bookme_lang_title_employee');
                            $position++;
                            break;
                        case 'price':
                            $columns[] = esc_html__('Price', 'bookme');
                            $position++;
                            break;
                    }
                }
            }
            list($total) = $user_data->cart->get_info(false);   // without coupon
            $response = array(
                'success' => true,
                'csrf_token' => System::get_security_token(),
                'html' => Inc\Core\Template::create('booking_form/cart-step', true)
                    ->display(
                        array(
                            'progress_bar' => $progress_bar,
                            'items_data' => $items_data,
                            'columns' => $columns,
                            'positions' => $positions,
                            'total' => $total,
                            'cart_items' => $user_data->cart->get_items(),
                        ),
                        false
                    )
            );
        } else {
            $response = array('success' => false, 'error' => esc_html__('Invalid session data.', 'bookme'));
        }

        wp_send_json($response);
    }

    /**
     * Delete cart item
     */
    public function perform_cart_delete_item()
    {
        $user_data = new UserData(Request::get_parameter('form_id'));
        $total = $deposit = 0;
        if ($user_data->load()) {
            $cart_key = Request::get_parameter('key');
            $edit_cart_keys = $user_data->get('edit_cart_keys');

            $user_data->cart->drop($cart_key);
            if (($idx = array_search($cart_key, $edit_cart_keys)) !== false) {
                unset ($edit_cart_keys[$idx]);
                $user_data->set('edit_cart_keys', $edit_cart_keys);
            }

            list($total) = $user_data->cart->get_info();
        }
        wp_send_json_success(
            array(
                'total_price' => Inc\Mains\Functions\Price::format($total)
            )
        );
    }

    /**
     * Detail step
     */
    public function perform_get_detail_step()
    {
        $this->perform_save_session(true);

        $form_id = Request::get_parameter('form_id');
        $user_data = new UserData($form_id);

        if ($user_data->load()) {
            if (!System::show_step_cart()) {
                $user_data->add_sequence_to_cart();
                $user_data->save_in_session();
            }

            $cf_data = array();
            if (System::custom_fields_per_service()) {
                // Prepare custom fields data per service.
                foreach ($user_data->cart->get_items() as $cart_key => $cart_item) {
                    $data = array();
                    $service_id = $cart_item->get('service_id');
                    $key = get_option('bookme_custom_fields_merge') ? $service_id : $cart_key;

                    if (!isset($cf_data[$key])) {
                        foreach ($cart_item->get('custom_fields') as $field) {
                            $data[$field['id']] = $field['value'];
                        }
                        $cf_data[$key] = array(
                            'service_title' => Inc\Mains\Tables\Service::find($cart_item->get('service_id'))->get_translated_title(),
                            'custom_fields' => System::get_translated_custom_fields($service_id),
                            'data' => $data,
                        );

                        $cf_data = array_values($cf_data);
                    }
                }
            } else {
                $cart_items = $user_data->cart->get_items();
                $cart_item = array_pop($cart_items);
                $data = array();
                foreach ($cart_item->get('custom_fields') as $field) {
                    $data[$field['id']] = $field['value'];
                }
                $cf_data[] = array(
                    'custom_fields' => System::get_translated_custom_fields(null),
                    'data' => $data,
                );
            }

            $booking_data = array();
            /** @var Inc\Mains\Booking\CartItem $cart_item */
            foreach ($user_data->cart->get_items() as $cart_item) {
                $service = $cart_item->get_service();
                $slot = $cart_item->get('slots');
                $service_dp = Inc\Mains\Functions\Date::from_string($slot[0][2])->to_client_tz();
                $b_data = array();
                $b_data['category_name'] = $service->get_translated_category_name();
                $b_data['number_of_persons'] = $cart_item->get('number_of_persons');
                $b_data['service_date'] = $service_dp->format_i18n_date();
                $b_data['service_info'] = $service->get_translated_info();
                $b_data['service_name'] = $service->get_translated_title();
                $b_data['service_price'] = Inc\Mains\Functions\Price::format($cart_item->get_service_price() * $cart_item->get('number_of_persons'));
                $b_data['service_time'] = $service_dp->format_i18n_time();
                $b_data['staff_info'] = $cart_item->get_staff()->get_translated_info();
                $b_data['staff_name'] = $cart_item->get_staff()->get_translated_name();
                $booking_data[] = $b_data;
            }

            $payment_disabled = System::payment_disabled();
            list ($total, $deposit, , $sub_total, $discount_price) = $user_data->cart->get_info();
            if ($deposit <= 0) {
                $payment_disabled = true;
            }

            if ($payment_disabled == false) {
                $payments = array();
                $default_payment_order = array(
                    Inc\Mains\Tables\Payment::TYPE_LOCAL,
                    Inc\Mains\Tables\Payment::TYPE_PAYPAL,
                    Inc\Mains\Tables\Payment::TYPE_STRIPE,
                    Inc\Mains\Tables\Payment::TYPE_RAZORPAY,
                    Inc\Mains\Tables\Payment::TYPE_2CHECKOUT,
                    Inc\Mains\Tables\Payment::TYPE_AUTHORIZENET,
                    Inc\Mains\Tables\Payment::TYPE_MOLLIE
                );
                $payment_order = explode(',', get_option('bookme_payment_order') );
                foreach ( $payment_order as $payment_gateways ) {
                    if ( in_array( $payment_gateways, $default_payment_order ) ) {
                        $payments[] = $payment_gateways;
                    }
                }
                foreach ( $default_payment_order as $payment_gateways ) {
                    if ( ! $payment_order || ! in_array( $payment_gateways, $payment_order ) ) {
                        $payments[] = $payment_gateways;
                    }
                }
                $payment_methods = array();
                foreach ($payments as $payment){
                    if(System::payment_type_enabled($payment)){
                        $payment_methods[] = $payment;
                    }
                }

                $html = Inc\Core\Template::create('booking_form/detail-step', true)
                    ->display(
                        array(
                            'disabled' => false,
                            'progress_bar' => $this->create_progress_bar(4),
                            'user_data' => $user_data,
                            'cf_data' => $cf_data,
                            'show_service_title' => System::custom_fields_per_service() && count($cf_data) > 1,
                            'form_id' => $form_id,
                            'booking_data' => $booking_data,
                            'total' => Inc\Mains\Functions\Price::format($total),
                            'sub_total' => Inc\Mains\Functions\Price::format($sub_total),
                            'discount_price' => Inc\Mains\Functions\Price::format($discount_price),
                            'coupon_code' => $user_data->get('coupon'),
                            'payment' => $user_data->extract_payment_status(),
                            'payment_methods' => $payment_methods,
                            'pay_paypal' => System::payment_type_enabled(Inc\Mains\Tables\Payment::TYPE_PAYPAL)
                                ? System::get_payment_type_option(Inc\Mains\Tables\Payment::TYPE_PAYPAL)
                                : false,
                            'cards_image_url' => BOOKME_URL . 'assets/front/images/cards.png',
                            'page_url' => Request::get_parameter('page_url'),
                        ),
                        false
                    );
            } else {
                $html = Inc\Core\Template::create('booking_form/detail-step', true)
                        ->display(
                            array(
                                'disabled' => true,
                                'progress_bar' => $this->create_progress_bar(4),
                                'user_data' => $user_data,
                                'cf_data' => $cf_data,
                                'show_service_title' => System::custom_fields_per_service() && count($cf_data) > 1
                            ),
                            false
                        );
            }

            $html .= Inc\Core\Template::create('booking_form/customer-duplicate-dialog', true)->display(array(), false);

            if (!get_current_user_id() && get_option('bookme_customer_show_login_button')) {
                $html .= Inc\Core\Template::create('booking_form/login-form-dialog', true)->display(array(), false);
            }

            $response = array(
                'success' => true,
                'disabled' => $payment_disabled,
                'html' => $html,
                'csrf_token' => System::get_security_token(),
                'stripe_publishable_key' => get_option( 'bookme_stripe_publishable_key' )
            );
        } else {
            $response = array('success' => false, 'error' => esc_html__('Invalid session data.', 'bookme'));
        }

        wp_send_json($response);
    }

    /**
     * Save cart bookings
     */
    public function perform_save_cart_bookings()
    {
        $user_data = new UserData(Request::get_parameter('form_id'));

        if ($user_data->load()) {
            $failed_cart_key = $user_data->cart->get_failed_cart_key();
            if ($failed_cart_key === null) {
                list($total, $deposit) = $user_data->cart->get_info();
                $is_payment_disabled = System::payment_disabled();
                $is_pay_locally_enabled = System::payment_type_enabled(Inc\Mains\Tables\Payment::TYPE_LOCAL);
                if ($is_payment_disabled || $is_pay_locally_enabled || $deposit <= 0) {
                    // coupon
                    $coupon = $user_data->get_coupon();
                    if ($coupon) {
                        $coupon->claim();
                        $coupon->save();
                    }
                    // payment
                    $payment = null;
                    if (!$is_payment_disabled) {
                        if ($coupon && $deposit <= 0) {
                            // Create record for 100% coupon (free)
                            $payment = new Inc\Mains\Tables\Payment();
                            $payment
                                ->set_status(Inc\Mains\Tables\Payment::STATUS_COMPLETED)
                                ->set_paid_type(Inc\Mains\Tables\Payment::PAY_IN_FULL)
                                ->set_created(current_time('mysql'))
                                ->set_type(Inc\Mains\Tables\Payment::TYPE_COUPON)
                                ->set_total(0)
                                ->set_paid(0)
                                ->save();
                        } elseif ($is_pay_locally_enabled && $deposit > 0) {
                            // Create record for local payment
                            $payment = new Inc\Mains\Tables\Payment();
                            $payment
                                ->set_status(Inc\Mains\Tables\Payment::STATUS_PENDING)
                                ->set_paid_type(Inc\Mains\Tables\Payment::PAY_IN_FULL)
                                ->set_created(current_time('mysql'))
                                ->set_type(Inc\Mains\Tables\Payment::TYPE_LOCAL)
                                ->set_total($total)
                                ->set_paid(0)
                                ->save();
                        }
                    }
                    // Save cart.
                    $order = $user_data->save($payment);
                    // Send notifications.
                    Inc\Mains\Notification\Sender::send_from_cart($order);
                    if ($payment !== null) {
                        $payment->set_details($order, $coupon)->save();
                    }
                    $response = array(
                        'success' => true,
                    );
                } else {
                    $response = array(
                        'success' => false,
                        'error' => esc_html__('Pay locally is not available.', 'bookme'),
                    );
                }
            } else {
                $response = array(
                    'success' => false,
                    'failed_cart_key' => $failed_cart_key,
                    'error' => esc_html__('Selected time slot is not available anymore. Please, choose another time slot.', 'bookme'),
                );
            }
        } else {
            $response = array('success' => false, 'error' => esc_html__('Invalid session data.', 'bookme'));
        }

        wp_send_json($response);
    }

    /**
     * Last step
     */
    public function perform_get_done_step()
    {
        $user_data = new UserData(Request::get_parameter('form_id'));
        $errors = Request::get_parameter('errors', array());
        if ($user_data->load()) {
            $progress_bar = $this->create_progress_bar(5);
            if (empty($errors)) {
                $payment = $user_data->extract_payment_status();
                do {
                    if ($payment) {
                        switch ($payment['status']) {
                            case 'processing':
                                $message = esc_html__('Your payment has been accepted for further processing.', 'bookme');
                                break (2);
                        }
                    }
                    $message = esc_html__('Thank you! Your booking is successfully booked.', 'bookme');
                } while (0);

                $response = array(
                    'success' => true,
                    'html' => Inc\Core\Template::create('booking_form/done-step', true)->display(array(
                        'progress_bar' => $progress_bar,
                        'message' => $message,
                        'page_url' => Request::get_parameter('page_url')
                    ), false),
                );
            } else {
                $response = array(
                    'success' => true,
                    'html' => Inc\Core\Template::create('booking_form/done-step', true)->display(array(
                        'progress_bar' => $progress_bar,
                        'message' => esc_html__('You have reached the booking limit, Please contact us to make a booking.', 'bookme'),
                        'page_url' => Request::get_parameter('page_url')
                    ), false),
                );
            }
        } else {
            $response = array('success' => false, 'error' => esc_html__('Invalid session data.', 'bookme'));
        }

        wp_send_json($response);
    }

    /**
     * Cancel Booking via token url
     */
    public function perform_cancel_booking()
    {
        $customer_appointment = new Inc\Mains\Tables\CustomerBooking();

        $allow_cancel = true;
        if ($customer_appointment->load_by(array('token' => Request::get_parameter('token')))) {
            $appointment = new Inc\Mains\Tables\Booking();
            $minimum_time_prior_cancel = (int)get_option('bookme_min_time_before_cancel', 0);
            if ($minimum_time_prior_cancel > 0
                && $appointment->load($customer_appointment->get_booking_id())
            ) {
                $allow_cancel_time = strtotime($appointment->get_start_date()) - $minimum_time_prior_cancel * HOUR_IN_SECONDS;
                if (current_time('timestamp') > $allow_cancel_time) {
                    $allow_cancel = false;
                }
            }
            if ($allow_cancel) {
                $customer_appointment->cancel();
            }
        }

        if ($url = $allow_cancel ? get_option('bookme_cancel_success_url') : get_option('bookme_cancel_unsuccess_url')) {
            wp_redirect($url);
            Inc\Core\Template::create('booking_form/redirection', true)->display(compact('url'));
            exit;
        }

        $url = home_url();
        if (isset ($_SERVER['HTTP_REFERER'])) {
            if (parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) == parse_url($url, PHP_URL_HOST)) {
                // Redirect back if user came from our site.
                $url = $_SERVER['HTTP_REFERER'];
            }
        }
        wp_redirect($url);
        Inc\Core\Template::create('booking_form/redirection', true)->display(compact('url'));
        exit;
    }

    /**
     * Approve Booking via token url
     */
    public function perform_approve_booking()
    {
        $url = get_option('bookme_approve_unsuccess_url');

        // Decode token.
        $token = System::xor_decrypt(Request::get_parameter('token'), 'approve');
        $ca_to_approve = new Inc\Mains\Tables\CustomerBooking();
        if ($ca_to_approve->load_by(array('token' => $token))) {
            $success = true;
            $updates = array();
            /** @var Inc\Mains\Tables\CustomerBooking[] $ca_list */
            $ca_list = array($ca_to_approve);
            
            // Check that all items can be switched to approved.
            foreach ($ca_list as $ca) {
                $ca_status = $ca->get_status();
                if ($ca_status != Inc\Mains\Tables\CustomerBooking::STATUS_APPROVED) {
                    if ($ca_status != Inc\Mains\Tables\CustomerBooking::STATUS_CANCELLED &&
                        $ca_status != Inc\Mains\Tables\CustomerBooking::STATUS_REJECTED
                    ) {
                        $booking = new Inc\Mains\Tables\Booking();
                        $booking->load($ca->get_booking_id());
                        $updates[] = array($ca, $booking);
                    } else {
                        $success = false;
                        break;
                    }
                }
            }

            if ($success) {
                foreach ($updates as $update) {
                    /** @var Inc\Mains\Tables\CustomerBooking $ca */
                    list ($ca, $booking) = $update;
                    $ca->set_status(Inc\Mains\Tables\CustomerBooking::STATUS_APPROVED)->save();
                    $booking->handle_google_calendar();
                }

                if (!empty ($updates)) {
                    $ca_to_approve->set_status(Inc\Mains\Tables\CustomerBooking::STATUS_APPROVED);
                    Inc\Mains\Notification\Sender::send_single(Inc\Mains\Booking\DataHolders\Service::create($ca_to_approve));
                }

                $url = get_option('bookme_approve_success_url');
            }
        }

        wp_redirect($url);
        Inc\Core\Template::create('booking_form/redirection', true)->display(compact('url'));
        exit (0);
    }

    /**
     * Reject Booking via token url
     */
    public function perform_reject_booking()
    {
        $url = get_option('bookme_reject_unsuccess_url');

        // Decode token.
        $token = System::xor_decrypt(Request::get_parameter('token'), 'reject');
        $ca_to_reject = new Inc\Mains\Tables\CustomerBooking();
        if ($ca_to_reject->load_by(array('token' => $token))) {
            $updates = array();
            /** @var Inc\Mains\Tables\CustomerBooking[] $ca_list */
            $ca_list = array($ca_to_reject);
            
            // Check that all items can be switched to rejected.
            foreach ($ca_list as $ca) {
                $ca_status = $ca->get_status();
                if ($ca_status != Inc\Mains\Tables\CustomerBooking::STATUS_REJECTED &&
                    $ca_status != Inc\Mains\Tables\CustomerBooking::STATUS_CANCELLED
                ) {
                    $booking = new Inc\Mains\Tables\Booking();
                    $booking->load($ca->get_booking_id());
                    $updates[] = array($ca, $booking);
                }
            }

            foreach ($updates as $update) {
                /** @var Inc\Mains\Tables\CustomerBooking $ca */
                list ($ca, $booking) = $update;
                $ca->set_status(Inc\Mains\Tables\CustomerBooking::STATUS_REJECTED)->save();
                $booking->handle_google_calendar();
            }

            if (!empty ($updates)) {
                $ca_to_reject->set_status(Inc\Mains\Tables\CustomerBooking::STATUS_REJECTED);
                Inc\Mains\Notification\Sender::send_single(Inc\Mains\Booking\DataHolders\Service::create($ca_to_reject));
                $url = get_option('bookme_reject_success_url');
            }
        }

        wp_redirect($url);
        Inc\Core\Template::create('booking_form/redirection', true)->display(compact('url'));
        exit (0);
    }

    /**
     * Check cart bookings are available right now
     */
    public function perform_check_cart()
    {
        $userData = new UserData(Request::get_parameter('form_id'));

        if ($userData->load()) {
            $failed_cart_key = $userData->cart->get_failed_cart_key();
            if ($failed_cart_key === null) {
                $response = array('success' => true);
            } else {
                $response = array(
                    'success' => false,
                    'failed_cart_key' => $failed_cart_key,
                    'error' => esc_html__('Selected time slot is not available anymore. Please, choose another time slot.', 'bookme'),
                );
            }
        } else {
            $response = array('success' => false, 'error' => esc_html__('Invalid session data.', 'bookme'));
        }

        wp_send_json($response);
    }

    /**
     * Log in to WordPress and return user data
     */
    public function perform_wp_user_login()
    {
        $response = null;
        $user_data = new UserData(Request::get_parameter('form_id'));

        if ($user_data->load()) {
            add_action('set_logged_in_cookie', function ($logged_in_cookie) {
                $_COOKIE[LOGGED_IN_COOKIE] = $logged_in_cookie;
            });
            /** @var \WP_User $user */
            $user = wp_signon();
            if (is_wp_error($user)) {
                $response = array('success' => false, 'error' => esc_html__('Incorrect username or password.'));
            } else {
                wp_set_current_user($user->ID, $user->user_login);
                $customer = new Inc\Mains\Tables\Customer();
                if ($customer->load_by(array('wp_user_id' => $user->ID))) {
                    $user_info = array(
                        'email' => $customer->get_email(),
                        'full_name' => $customer->get_full_name(),
                        'first_name' => $customer->get_first_name(),
                        'last_name' => $customer->get_last_name(),
                        'phone' => $customer->get_phone(),
                        'csrf_token' => System::get_security_token()
                    );
                } else {
                    $user_info = array(
                        'email' => $user->user_email,
                        'full_name' => $user->display_name,
                        'first_name' => $user->user_firstname,
                        'last_name' => $user->user_lastname,
                        'csrf_token' => System::get_security_token()
                    );
                }
                $user_data->fill_data($user_info);
                $response = array(
                    'success' => true,
                    'data' => $user_info
                );
            }
        } else {
            $response = array('success' => false, 'error' => esc_html__('Invalid session data.', 'bookme'));
        }

        wp_send_json($response);
    }

    /**
     * Apply discount coupon
     */
    public function perform_apply_coupon()
    {
        if (!get_option('bookme_coupons_enabled')) {
            wp_send_json_error();
        }

        $response = null;
        $user_data = new UserData(Request::get_parameter('form_id'));

        if ($user_data->load()) {
            $coupon_code = Request::get_parameter('coupon');

            $coupon = new Inc\Mains\Tables\Coupon();
            $coupon->load_by(array(
                'code' => $coupon_code,
            ));


            if ($coupon->is_loaded() && $coupon->get_used() < $coupon->get_usage_limit()) {
                $service_ids = array();
                foreach ($user_data->cart->get_items() as $item) {
                    $service_ids[] = $item->get('service_id');
                }
                if ($coupon->valid($service_ids)) {
                    $user_data->fill_data(array('coupon' => $coupon_code));
                    list ($total, $deposit, , $sub_total, $discount_price) = $user_data->cart->get_info();
                    $response = array(
                        'success' => true,
                        'total_simple' => $deposit,
                        'total' => Inc\Mains\Functions\Price::format($deposit),
                        'discount' => Inc\Mains\Functions\Price::format($discount_price)
                    );
                } else {
                    $user_data->fill_data(array('coupon' => null));
                    $response = array(
                        'success' => false,
                        'error' => esc_html__('This coupon code is invalid', 'bookme'),
                    );
                }
            } else {
                $user_data->fill_data(array('coupon' => null));
                $response = array(
                    'success' => false,
                    'error' => esc_html__('This coupon code is invalid', 'bookme'),
                );
            }
        } else {
            $response = array('success' => false, 'error' => esc_html__('Invalid session data.', 'bookme'));
        }

        wp_send_json($response);
    }

    /**
     * Create progress bar
     * @param $step
     * @return string|void
     */
    private function create_progress_bar($step)
    {
        $result = '';

        if (get_option('bookme_show_progress_bar')) {
            $result = Inc\Core\Template::create('booking_form/progress-bar', true)
                ->display(
                    array(
                        'step' => $step,
                        'show_cart' => System::show_step_cart()
                    ),
                    false
                );
        }

        return $result;
    }

    /**
     * Display cart button or not
     *
     * @param UserData $user_data
     * @return bool
     */
    private function show_cart_button(UserData $user_data)
    {
        return System::show_step_cart() && count($user_data->cart->get_items());
    }

    /**
     * Register ajax for a class
     */
    protected function register_ajax()
    {

        $excluded_actions = array(
            'perform_approve_booking',
            'perform_cancel_booking',
            'perform_reject_booking'
        );

        Inc\Core\Ajax::register_ajax_actions($this, array('app' => 'everyone'), $excluded_actions, true);
    }
}