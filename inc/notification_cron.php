<?php

namespace Bookme\Inc;

use Bookme\Inc;
use Bookme\Inc\Mains\Tables;

define('WP_USE_THEMES', false);
if (isset($argv)) {
    foreach ($argv as $argument) {
        if (strpos($argument, 'host=') === 0) {
            $_SERVER['HTTP_HOST'] = substr($argument, 5);
        }
    }
}
require_once __DIR__ . '/../../../../wp-load.php';
require_once ABSPATH . WPINC . '/formatting.php';
require_once ABSPATH . WPINC . '/general-template.php';
require_once ABSPATH . WPINC . '/pluggable.php';
require_once ABSPATH . WPINC . '/link-template.php';

if (!class_exists('\Bookme\Inc\Mains\Plugin')) {
    // Bookme on host is inactive.
    if (is_multisite()) {
        $working_directory = getcwd();
        // absolute path for dir bookme
        chdir(realpath(__DIR__ . '/../../'));
        include_once 'autoload.php';
        // Restore working directory.
        chdir($working_directory);
    } else {
        die('Bookme is inactive');
    }
} else {
    add_action('bookme_send_cron_notifications', function () {
        new Notifier();
    });
}

/**
 * Class Notifier
 * @package Bookme\Lib\Utils
 */
class Notifier
{
    /** @var string Format: YYYY-MM-DD HH:MM:SS */
    private $mysql_now;

    /** @var int */
    private $hours;

    /** @var Inc\Mains\SMS $sms */
    private $sms;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Disable caching.
        Inc\Mains\Functions\System::no_cache();

        date_default_timezone_set('UTC');

        wp_load_translations_early();

        $now = Inc\Mains\Functions\Date::now();
        $this->mysql_now = $now->format('Y-m-d H:i:s');
        $this->hours = $now->format('H');
        $this->sms = new Inc\Mains\SMS();

        global $wpdb;
        $data = $wpdb->get_results(
            "SELECT * FROM `" . Tables\Notification::get_table_name() . "` 
                WHERE `active` = 1 
                    AND `type` IN ('" . implode("','", array('staff_agenda', 'client_follow_up', 'client_reminder', 'client_reminder_1st', 'client_reminder_2nd', 'client_reminder_3rd', 'client_birthday_greeting')) . "')",
            ARRAY_A
        );
        $built_in_notifications = Inc\Mains\Functions\System::bind_data_with_table(Tables\Notification::class, $data);
        /** @var Tables\Notification $notification */
        foreach ($built_in_notifications as $notification) {
            $this->process_notification($notification);
        }
    }

    /**
     * @param Tables\Notification $notification
     */
    public function process_notification(Tables\Notification $notification)
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $hours = get_option('bookme_cron_times');

        switch ($notification->get_type()) {
            case 'staff_agenda':
                if ($this->hours >= $hours[$notification->get_type()]) {
                    /** @var \stdClass[] $rows */
                    $rows = $wpdb->get_results(
                        'SELECT
                            `a`.*,
                            `ca`.`locale`,
                            `ca`.`extras`,
                            `c`.`full_name`  AS `customer_name`,
                            `s`.`title`      AS `service_title`,
                            `s`.`info`       AS `service_info`,
                            `st`.`email`     AS `staff_email`,
                            `st`.`phone`     AS `staff_phone`,
                            `st`.`full_name` AS `staff_name`,
                            `st`.`info`      AS `staff_info`
                        FROM `' . Tables\CustomerBooking::get_table_name() . '` `ca`
                        LEFT JOIN `' . Tables\Booking::get_table_name() . '` `a`   ON `a`.`id` = `ca`.`booking_id`
                        LEFT JOIN `' . Tables\Customer::get_table_name() . '` `c`      ON `c`.`id` = `ca`.`customer_id`
                        LEFT JOIN `' . Tables\Service::get_table_name() . '` `s`       ON `s`.`id` = `a`.`service_id`
                        LEFT JOIN `' . Tables\Employee::get_table_name() . '` `st`        ON `st`.`id` = `a`.`staff_id`
                        LEFT JOIN `' . Tables\EmployeeService::get_table_name() . '` `ss` ON `ss`.`staff_id` = `a`.`staff_id` AND `ss`.`service_id` = `a`.`service_id`
                        WHERE `ca`.`status` IN("' . Tables\CustomerBooking::STATUS_PENDING . '","' . Tables\CustomerBooking::STATUS_APPROVED . '") AND
                        DATE(DATE_ADD("' . $this->mysql_now . '", INTERVAL 1 DAY)) = DATE(`a`.`start_date`) AND NOT EXISTS (
                            SELECT * FROM `' . Tables\SentNotification::get_table_name() . '` `sn`
                             WHERE DATE(`sn`.`created`) = DATE("' . $this->mysql_now . '")
                               AND `sn`.`notification_id` = ' . $notification->get_id() . ' 
                               AND `sn`.`ref_id` = `a`.`staff_id`
                        )
                        ORDER BY `a`.`start_date`'
                    );

                    if ($rows) {
                        $bookings = array();
                        foreach ($rows as $row) {
                            $bookings[$row->staff_id][] = $row;
                        }

                        $columns = array(
                            '{10_date}' => __('Date', 'bookme'),
                            '{30_service}' => __('Service', 'bookme'),
                            '{40_customer}' => __('Customer', 'bookme'),
                        );

                        ksort($columns);
                        $is_html = (get_option('bookme_email_send_as') == 'html' && $notification->get_gateway() != 'sms');
                        if ($is_html) {
                            $table = '<table cellspacing="1" border="1" cellpadding="5"><thead><tr><td>'
                                . implode('</td><td>', $columns)
                                . '</td></tr></thead><tbody>%s</tbody></table>';
                            $tr = '<tr><td>' . implode('</td><td>', array_keys($columns)) . '</td></tr>';
                        } else {
                            $table = '%s';
                            $tr = implode(', ', array_keys($columns)) . PHP_EOL;
                        }

                        foreach ($bookings as $staff_id => $collection) {
                            $sent = false;
                            $staff_email = null;
                            $staff_phone = null;
                            $agenda = '';
                            foreach ($collection as $booking) {
                                $tr_data = array(
                                    '{10_date}' => Inc\Mains\Functions\DateTime::format_time($booking->start_date) . '-' . Inc\Mains\Functions\DateTime::format_time($booking->end_date),
                                    '{40_customer}' => $booking->customer_name,
                                );


                                $tr_data['{30_service}'] = $booking->service_title;
                                $agenda .= strtr($tr, $tr_data);

                                $staff_email = $booking->staff_email;
                                $staff_phone = $booking->staff_phone;
                            }

                            if ($notification->get_gateway() == 'email' && $staff_email != '' || $notification->get_gateway() == 'sms' && $staff_phone != '') {
                                $codes = new Inc\Mains\Notification\Codes();
                                $codes->next_day_agenda = sprintf($table, $agenda);
                                $codes->booking_start = $booking->start_date;
                                $codes->employee_name = $booking->staff_name;
                                $codes->employee_info = $booking->staff_info;
                                $codes->service_info = $booking->service_info;

                                $sent = Inc\Mains\Notification\Sender::send_from_cron_to_staff_agenda($notification, $codes, $staff_email, $staff_phone);
                            }

                            if ($sent) {
                                $this->notification_sent($notification, $staff_id);
                            }
                        }
                    }
                }
                break;
            case 'client_follow_up':
                if ($this->hours >= $hours[$notification->get_type()]) {
                    $bookings = $wpdb->get_results(
                        'SELECT `ca`.*
                        FROM `' . Tables\CustomerBooking::get_table_name() . '` `ca`
                        LEFT JOIN `' . Tables\Booking::get_table_name() . '` `a` ON `a`.`id` = `ca`.`booking_id`
                        WHERE `ca`.`status` IN("' . Tables\CustomerBooking::STATUS_PENDING . '","' . Tables\CustomerBooking::STATUS_APPROVED . '") AND
                        DATE("' . $this->mysql_now . '") = DATE(`a`.`start_date`) AND NOT EXISTS (
                            SELECT * FROM `' . Tables\SentNotification::get_table_name() . '` `sn`
                             WHERE DATE(`sn`.`created`) = DATE("' . $this->mysql_now . '")
                               AND `sn`.`notification_id` = ' . $notification->get_id() . '
                               AND `sn`.`ref_id` = `ca`.`id`
                        ) ORDER BY `a`.`start_date`',
                        ARRAY_A
                    );

                    if ($bookings) {
                        foreach ($bookings as $ca) {
                            $simple = Inc\Mains\Booking\DataHolders\Service::create(new Tables\CustomerBooking($ca));
                            if (Inc\Mains\Notification\Sender::send_from_cron_to_client($notification, $simple)) {
                                $this->notification_sent($notification, $ca['id']);
                            }
                        }
                    }
                }
                break;
            case 'client_reminder':
                if ($this->hours >= $hours[$notification->get_type()]) {
                    $bookings = $wpdb->get_results(
                        'SELECT `ca`.*
                        FROM `' . Tables\CustomerBooking::get_table_name() . '` `ca`
                        LEFT JOIN `' . Tables\Booking::get_table_name() . '` `a` ON `a`.`id` = `ca`.`booking_id`
                        WHERE `ca`.`status` IN("' . Tables\CustomerBooking::STATUS_PENDING . '","' . Tables\CustomerBooking::STATUS_APPROVED . '") AND
                        DATE(DATE_ADD("' . $this->mysql_now . '", INTERVAL 1 DAY)) = DATE(`a`.`start_date`) AND NOT EXISTS (
                            SELECT * FROM `' . Tables\SentNotification::get_table_name() . '` `sn`
                             WHERE DATE(`sn`.`created`) = DATE("' . $this->mysql_now . '")
                               AND `sn`.`notification_id` = ' . $notification->get_id() . '
                               AND `sn`.`ref_id` = `ca`.`id`
                        ) ORDER BY `a`.`start_date`',
                        ARRAY_A
                    );

                    if ($bookings) {
                        foreach ($bookings as $ca) {
                            $simple = Inc\Mains\Booking\DataHolders\Service::create(new Tables\CustomerBooking($ca));
                            if (Inc\Mains\Notification\Sender::send_from_cron_to_client($notification, $simple)) {
                                $this->notification_sent($notification, $ca['id']);
                            }
                        }
                    }
                }
                break;
        }
    }

    /**
     * Save sent notification
     *
     * @param Tables\Notification $notification
     * @param $ref_id
     */
    private function notification_sent(Tables\Notification $notification, $ref_id)
    {
        $sent_notification = new Tables\SentNotification();
        $sent_notification
            ->set_ref_id($ref_id)
            ->set_notification_id($notification->get_id())
            ->set_created($this->mysql_now)
            ->save();
    }
}

do_action('bookme_send_cron_notifications');