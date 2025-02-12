<?php

namespace Bookme\Inc\Mains\Notification;

use Bookme\Inc\Mains\Functions;
use Bookme\Inc;

/**
 * Class Sender
 */
abstract class Sender
{
    /** @var Inc\Mains\SMS */
    private static $sms = null;

    /**
     * Send notifications for single booking.
     *
     * @param Inc\Mains\Booking\DataHolders\Service $item
     * @param Inc\Mains\Booking\DataHolders\Order $order
     * @param array $codes_data
     * @param bool $to_staff
     * @param bool $to_customer
     */
    public static function send_single(
        Inc\Mains\Booking\DataHolders\Service $item,
        Inc\Mains\Booking\DataHolders\Order $order = null,
        array $codes_data = array(),
        $to_staff = true,
        $to_customer = true
    )
    {
        global $sitepress;

        $wp_locale = $sitepress instanceof \SitePress ? $sitepress->get_default_language() : null;
        $order = $order ?: Inc\Mains\Booking\DataHolders\Order::create_from_service($item);
        $status = $item->get_cb()->get_status();
        $staff_email_notification = $to_staff ? self::get_email_notification('staff', $status) : false;
        $staff_sms_notification = $to_staff ? self::get_sms_notification('staff', $status) : false;
        $client_email_notification = $to_customer ? self::get_email_notification('client', $status) : false;
        $client_sms_notification = $to_customer ? self::get_sms_notification('client', $status) : false;

        if ($staff_email_notification || $staff_sms_notification || $client_email_notification || $client_sms_notification) {
            // Prepare codes.
            $codes = Codes::create_for_order($order, $item);
            if (isset ($codes_data['cancellation_reason'])) {
                $codes->cancellation_reason = $codes_data['cancellation_reason'];
            }

            // Notify staff by email.
            if ($staff_email_notification) {
                self::send_email_to_staff($staff_email_notification, $codes, $item->get_staff()->get_email());
            }
            // Notify staff by SMS.
            if ($staff_sms_notification) {
                self::send_sms_to_staff($staff_sms_notification, $codes, $item->get_staff()->get_phone());
            }

            // Customer locale.
            $customer_locale = $item->get_cb()->get_locale() ?: $wp_locale;
            if ($customer_locale != $wp_locale) {
                self::switch_locale($customer_locale);
                $codes->refresh();
            }

            // Client time zone offset.
            if ($item->get_cb()->get_time_zone_offset() !== null) {
                $codes->booking_start = self::apply_time_zone($codes->booking_start, $item->get_cb());
                $codes->booking_end = self::apply_time_zone($codes->booking_end, $item->get_cb());
            }
            // Notify client by email.
            if ($client_email_notification) {
                self::send_email_to_client($client_email_notification, $codes, $order->get_customer()->get_email());
            }
            // Notify client by SMS.
            if ($client_sms_notification) {
                self::send_sms_to_client($client_sms_notification, $codes, $order->get_customer()->get_phone());
            }

            if ($customer_locale != $wp_locale) {
                self::switch_locale($wp_locale);
            }
        }
    }

    /**
     * Send combined notifications.
     *
     * @param Inc\Mains\Booking\DataHolders\Order $order
     */
    protected static function send_combined(Inc\Mains\Booking\DataHolders\Order $order)
    {
        $status = get_option('bookme_default_booking_status');
        $cart_info = array();
        $total = 0.0;

        foreach ($order->get_services() as $item) {
            // Send notification to staff.
            self::send_single($item, $order, array(), true, false);
            $sub_items[] = $item;
                // Sub-item price.
                $price = $item->get_price();

                // Prepare data for {cart_info} || {cart_info_c}.
                $cart_info[] = array(
                    'booking_price' => $price * $item->get_cb()->get_number_of_persons(),
                    'booking_start' => self::apply_time_zone($item->get_booking()->get_start_date(), $item->get_cb()),
                    'cancel_url' => admin_url('admin-ajax.php?action=bookme_cancel_booking&token=' . $item->get_cb()->get_token()),
                    'service_name' => $item->get_service()->get_translated_title(),
                    'employee_name' => $item->get_staff()->get_translated_name(),
                    'booking_start_info' => $item->get_service()->get_duration() < DAY_IN_SECONDS ? null : $item->get_service()->get_start_time_info(),
                );

                // Total price.
                $total += $price;

        }

        // Prepare codes.
        $items = $order->get_services();
        $codes = Codes::create_for_order($order, $items[0]);
        $codes->cart_info = $cart_info;
        if (!$order->has_payment()) {
            $codes->total_price = $total;
        }

        // Send notifications to client.
        if ($to_client = self::get_combined_email_notification($status)) {
            self::send_email_to_client($to_client, $codes, $order->get_customer()->get_email());
        }
        if ($to_client = self::get_combined_sms_notification($status)) {
            self::send_sms_to_client($to_client, $codes, $order->get_customer()->get_phone());
        }
    }

    /**
     * Send notifications from cart.
     *
     * @param Inc\Mains\Booking\DataHolders\Order $order
     */
    public static function send_from_cart(Inc\Mains\Booking\DataHolders\Order $order)
    {
        if (Functions\System::combined_notifications_enabled()) {
            self::send_combined($order);
        } else {
            foreach ($order->get_services() as $item) {
                self::send_single($item, $order);
            }
        }
    }

    /**
     * Send reminder (email or SMS) to client.
     *
     * @param Inc\Mains\Tables\Notification $notification
     * @param Inc\Mains\Booking\DataHolders\Service $item
     * @return bool
     */
    public static function send_from_cron_to_client(Inc\Mains\Tables\Notification $notification, Inc\Mains\Booking\DataHolders\Service $item)
    {
        global $sitepress;

        $wp_locale = $sitepress instanceof \SitePress ? $sitepress->get_default_language() : null;

        $order = Inc\Mains\Booking\DataHolders\Order::create_from_service($item);

        $customer_locale = $item->get_cb()->get_locale() ?: $wp_locale;
        if ($customer_locale != $wp_locale) {
            self::switch_locale($customer_locale);
        }

        $codes = Codes::create_for_order($order, $item);

        // Client time zone offset.
        if ($item->get_cb()->get_time_zone_offset() !== null) {
            $codes->booking_start = self::apply_time_zone($codes->booking_start, $item->get_cb());
            $codes->booking_end = self::apply_time_zone($codes->booking_end, $item->get_cb());
        }

        // Send notification to client.
        $result = $notification->get_gateway() == 'email'
            ? self::send_email_to_client($notification, $codes, $order->get_customer()->get_email())
            : self::send_sms_to_client($notification, $codes, $order->get_customer()->get_phone());

        if ($customer_locale != $wp_locale) {
            self::switch_locale($wp_locale);
        }

        return $result;
    }

    /**
     * Send notification to Staff.
     *
     * @param Inc\Mains\Tables\Notification $notification
     * @param Inc\Mains\Booking\DataHolders\Service $item
     * @return bool
     */
    public static function send_from_cron_to_staff(Inc\Mains\Tables\Notification $notification, Inc\Mains\Booking\DataHolders\Service $item)
    {
        $order = Inc\Mains\Booking\DataHolders\Order::create_from_service($item);

        $codes = Codes::create_for_order($order, $item);

        // Send notification to client.
        $result = $notification->get_gateway() == 'email'
            ? self::send_email_to_staff($notification, $codes, $item->get_staff()->get_email())
            : self::send_sms_to_staff($notification, $codes, $item->get_staff()->get_phone());

        return $result;
    }

    /**
     * Send notification to administrators.
     *
     * @param Inc\Mains\Tables\Notification $notification
     * @param Inc\Mains\Booking\DataHolders\Service $item
     * @return bool
     */
    public static function send_from_cron_to_admin(Inc\Mains\Tables\Notification $notification, Inc\Mains\Booking\DataHolders\Service $item)
    {
        $order = Inc\Mains\Booking\DataHolders\Order::create_from_service($item);

        $codes = Codes::create_for_order($order, $item);

        // Send notification to client.
        $result = $notification->get_gateway() == 'email'
            ? self::send_email_to_admins($notification, $codes)
            : self::send_sms_to_admin($notification, $codes);

        return $result;
    }

    /**
     * Send reminder (email or SMS) to staff.
     *
     * @param Inc\Mains\Tables\Notification $notification
     * @param Codes $codes
     * @param string $email
     * @param string $phone
     * @return bool
     */
    public static function send_from_cron_to_staff_agenda(Inc\Mains\Tables\Notification $notification, Codes $codes, $email, $phone)
    {
        return $notification->get_gateway() == 'email'
            ? self::send_email_to_staff($notification, $codes, $email, false)
            : self::send_sms_to_staff($notification, $codes, $phone);
    }

    /**
     * Send email/sms with username and password for newly created WP user.
     *
     * @param Inc\Mains\Tables\Customer $customer
     * @param $username
     * @param $password
     */
    public static function send_new_user_credentials(Inc\Mains\Tables\Customer $customer, $username, $password)
    {
        $codes = new Codes();
        $codes->customer_email = $customer->get_email();
        $codes->customer_name = $customer->get_full_name();
        $codes->customer_first_name = $customer->get_first_name();
        $codes->customer_last_name = $customer->get_last_name();
        $codes->customer_phone = $customer->get_phone();
        $codes->new_password = $password;
        $codes->new_username = $username;
        $codes->site_address = site_url();

        $to_client = new Inc\Mains\Tables\Notification();
        if ($to_client->load_by(array('type' => 'client_new_wp_user', 'gateway' => 'email', 'active' => 1))) {
            self::send_email_to_client($to_client, $codes, $customer->get_email());
        }
        if ($to_client->load_by(array('type' => 'client_new_wp_user', 'gateway' => 'sms', 'active' => 1))) {
            self::send_sms_to_client($to_client, $codes, $customer->get_phone());
        }
    }

    /**
     * Send test notification emails.
     *
     * @param string $to_mail
     * @param array $notification_ids
     * @param string $send_as
     */
    public static function send_test_email_notifications($to_mail, array $notification_ids, $send_as)
    {
        $codes = Codes::create_for_test();
        $notification = new Inc\Mains\Tables\Notification();

        $reply_to_customer = false;

        foreach ($notification_ids as $id) {
            $notification->load_by(array('id' => $id, 'gateway' => 'email'));

            switch ($notification->get_type()) {
                case 'client_pending_appointment':
                case 'client_approved_appointment':
                case 'client_cancelled_appointment':
                case 'client_rejected_appointment':
                case 'client_pending_appointment_cart':
                case 'client_approved_appointment_cart':
                case 'client_follow_up':
                case 'client_new_wp_user':
                case 'client_reminder':
                    self::send_email_to_client($notification, $codes, $to_mail, $send_as);
                    break;
                case 'staff_pending_appointment':
                case 'staff_approved_appointment':
                case 'staff_cancelled_appointment':
                case 'staff_rejected_appointment':
                case 'staff_agenda':
                    self::send_email_to_staff($notification, $codes, $to_mail, $reply_to_customer, $send_as);
                    break;
            }
        }
    }

    /**
     * Send email notification to client.
     *
     * @param Inc\Mains\Tables\Notification $notification
     * @param Codes $codes
     * @param string $email
     * @param string|null $send_as
     * @return bool
     */
    protected static function send_email_to_client(Inc\Mains\Tables\Notification $notification, Codes $codes, $email, $send_as = null)
    {
        $subject = $codes->replace(Functions\System::get_translated_string(
            'email_' . $notification->get_type() . '_subject',
            $notification->get_subject()
        ), 'text');

        $message = Functions\System::get_translated_string(
            'email_' . $notification->get_type(),
            $notification->get_message()
        );

        $send_as_html = $send_as === null ? Functions\System::send_email_as_html() : $send_as == 'html';
        if ($send_as_html) {
            $message = wpautop($codes->replace($message, 'html'));
        } else {
            $message = $codes->replace($message, 'text');
        }

        return wp_mail($email, $subject, $message, Functions\System::get_email_headers());
    }

    /**
     * Send email notification to staff.
     *
     * @param Inc\Mains\Tables\Notification $notification
     * @param Codes $codes
     * @param string $email
     * @param bool $reply_to_customer
     * @param string|null $send_as
     * @return bool
     */
    protected static function send_email_to_staff(
        Inc\Mains\Tables\Notification $notification,
        Codes $codes,
        $email,
        $reply_to_customer = null,
        $send_as = null
    )
    {
        // Subject.
        $subject = $codes->replace($notification->get_subject(), 'text');

        // Message.
        $message = self::get_message_for_staff($notification, 'staff', $grace);
        $send_as_html = $send_as === null ? Functions\System::send_email_as_html() : $send_as == 'html';
        if ($send_as_html) {
            $message = wpautop($codes->replace($message, 'html'));
        } else {
            $message = $codes->replace($message, 'text');
        }

        // Headers.
        $extra_headers = array();
        if ($reply_to_customer === null ? get_option('bookme_email_reply_to_customers') : $reply_to_customer) {
            // Codes can be without order.
            if ($codes->get_order() !== null) {
                $customer = $codes->get_order()->get_customer();
                $extra_headers = array('reply-to' => array('email' => $customer->get_email(), 'name' => $customer->get_full_name()));
            }
        }

        $headers = Functions\System::get_email_headers($extra_headers);

        // Send email to staff.
        $result = wp_mail($email, $subject, $message, $headers);

        // Send to administrators.
        if ($notification->get_to_admin()) {
            self::send_email_to_admins($notification, $codes);
        }

        return $result;
    }

    /**
     * Send email notification to admin.
     *
     * @param Inc\Mains\Tables\Notification $notification
     * @param Codes $codes
     *
     * @return bool
     */
    protected static function send_email_to_admins(
        Inc\Mains\Tables\Notification $notification,
        Codes $codes
    )
    {
        $admin_email = get_option('admin_email');
        if (!empty($admin_email)) {
            // Subject.
            $subject = $codes->replace($notification->get_subject(), 'text');

            // Message.
            $message = self::get_message_for_staff($notification, 'staff', $grace);
            $send_as_html = Functions\System::send_email_as_html() == 'html';
            if ($send_as_html) {
                $message = wpautop($codes->replace($message, 'html'));
            } else {
                $message = $codes->replace($message, 'text');
            }

            return wp_mail($admin_email, $subject, $message, Functions\System::get_email_headers());
        }
        return true;
    }

    /**
     * Send SMS notification to client.
     *
     * @param Inc\Mains\Tables\Notification $notification
     * @param Codes $codes
     * @param string $phone
     * @return bool
     */
    protected static function send_sms_to_client(Inc\Mains\Tables\Notification $notification, Codes $codes, $phone)
    {
        $message = $codes->replace(Functions\System::get_translated_string(
            'sms_' . $notification->get_type(),
            $notification->get_message()
        ), 'text');

        if (self::$sms === null) {
            self::$sms = new Inc\Mains\SMS();
        }

        return self::$sms->send_sms($phone, $message);
    }

    /**
     * Send SMS notification to staff.
     *
     * @param Inc\Mains\Tables\Notification $notification
     * @param Codes $codes
     * @param string $phone
     * @return bool
     */
    protected static function send_sms_to_staff(Inc\Mains\Tables\Notification $notification, Codes $codes, $phone)
    {
        // Message.
        $message = $codes->replace(self::get_message_for_staff($notification, 'staff', $grace), 'text');

        // Send SMS to staff.
        if (self::$sms === null) {
            self::$sms = new Inc\Mains\SMS();
        }

        $result = self::$sms->send_sms($phone, $message);

        // Send to administrators.
        if ($notification->get_to_admin()) {
            if ($grace) {
                $message = $codes->replace(self::get_message_for_staff($notification, 'admin'), 'text');
            }

            self::$sms->send_sms(get_option('bookme_sms_administrator_phone', ''), $message);
        }

        return $result;
    }

    /**
     * Send SMS notification to admin.
     *
     * @param Inc\Mains\Tables\Notification $notification
     * @param Codes $codes
     * @return bool
     */
    protected static function send_sms_to_admin(Inc\Mains\Tables\Notification $notification, Codes $codes)
    {
        // Message.
        $message = $codes->replace(self::get_message_for_staff($notification, 'staff', $grace), 'text');

        // Send SMS to staff.
        if (self::$sms === null) {
            self::$sms = new Inc\Mains\SMS();
        }

        // Send to administrators.
        if ($grace) {
            $message = $codes->replace(self::get_message_for_staff($notification, 'admin'), 'text');
        }

        return self::$sms->send_sms(get_option('bookme_sms_administrator_phone', ''), $message);
    }

    /**
     * Get email notification for given recipient and status.
     *
     * @param string $recipient
     * @param string $status
     * @param bool $is_recurring
     * @return Inc\Mains\Tables\Notification|bool
     */
    protected static function get_email_notification($recipient, $status)
    {
        return self::get_notification("{$recipient}_{$status}_appointment", 'email');
    }

    /**
     * Get SMS notification for given recipient and booking status.
     *
     * @param string $recipient
     * @param string $status
     * @param bool $is_recurring
     * @return Inc\Mains\Tables\Notification|bool
     */
    protected static function get_sms_notification($recipient, $status)
    {
        return self::get_notification("{$recipient}_{$status}_appointment", 'sms');
    }

    /**
     * Get combined email notification for given booking status.
     *
     * @param string $status
     * @return Inc\Mains\Tables\Notification|bool
     */
    protected static function get_combined_email_notification($status)
    {
        return self::get_notification("client_{$status}_appointment_cart", 'email');
    }

    /**
     * Get combined SMS notification for given booking status.
     *
     * @param string $status
     * @return Inc\Mains\Tables\Notification|bool
     */
    protected static function get_combined_sms_notification($status)
    {
        return self::get_notification("client_{$status}_appointment_cart", 'sms');
    }

    /**
     * Get notification object.
     *
     * @param string $type
     * @param string $gateway
     * @return Inc\Mains\Tables\Notification|bool
     */
    protected static function get_notification($type, $gateway)
    {
        $notification = new Inc\Mains\Tables\Notification();
        if ($notification->load_by(array(
            'type' => $type,
            'gateway' => $gateway,
            'active' => 1
        ))) {
            return $notification;
        }

        return false;
    }

    /**
     * @param Inc\Mains\Tables\Notification $notification
     * @param string $recipient
     * @param bool $grace
     * @return string
     */
    protected static function get_message_for_staff(Inc\Mains\Tables\Notification $notification, $recipient, &$grace = null)
    {
        return $notification->get_message();
    }

    /**
     * Switch WordPress and WPML locale
     *
     * @param $locale
     */
    protected static function switch_locale($locale)
    {
        global $sitepress;

        if ($sitepress instanceof \SitePress) {
            $languages = apply_filters('wpml_active_languages', 'skip_missing=0');
            $locale_code = isset($languages[$locale]['default_locale']) ? $languages[$locale]['default_locale'] : $locale;
            switch_to_locale($locale_code);

            $sitepress->switch_lang($locale);
        }
    }

    /**
     * Apply client time zone to given datetime string in WP time zone.
     *
     * @param string $datetime
     * @param Inc\Mains\Tables\CustomerBooking $ca
     * @return false|string
     */
    protected static function apply_time_zone($datetime, Inc\Mains\Tables\CustomerBooking $ca)
    {
        $time_zone = $ca->get_time_zone();
        $time_zone_offset = $ca->get_time_zone_offset();

        if ($time_zone !== null) {
            $datetime = date_create($datetime . ' ' . Functions\System::get_wp_time_zone());
            return date_format(date_timestamp_set(date_create($time_zone), $datetime->getTimestamp()), 'Y-m-d H:i:s');
        } else if ($time_zone_offset !== null) {
            return Functions\DateTime::apply_time_zone_offset($datetime, $time_zone_offset);
        }

        return $datetime;
    }
}