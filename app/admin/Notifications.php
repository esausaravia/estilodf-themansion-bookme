<?php

namespace Bookme\App\Admin;

use Bookme\Inc;
use Bookme\Inc\Mains\Functions\Request;

/**
 * Class Notifications
 */
class Notifications extends Inc\Core\App
{

    const page_slug = 'bookme-notifications';

    /**
     * execute page.
     */
    public function execute()
    {
        $assets = BOOKME_URL . 'assets/admin/';
        $public_assets = BOOKME_URL . 'assets/front/';

        if (get_option('bookme_phone_default_country') != 'disabled') {
            wp_enqueue_style('bookme-intlTelInput', $public_assets . 'css/intlTelInput.css', array(), BOOKME_VERSION);
            wp_enqueue_script('bookme-intlTelInput-js', $public_assets . 'js/intlTelInput.min.js', array('jquery'), BOOKME_VERSION);
        }

        Fragments::enqueue_global();
        wp_enqueue_style('bookme-side-panel', $assets . 'css/slidePanel.min.css', array(), BOOKME_VERSION);
        wp_enqueue_style('bookme-multi-select', $assets . 'css/jquery.multiselect.css', array(), BOOKME_VERSION);

        wp_enqueue_script('bookme-side-panel-js', $assets . 'js/sidePanel.js', array('jquery'), BOOKME_VERSION);
        wp_enqueue_script('bookme-multi-select-js', $assets . 'js/jquery.multiselect.js', array(), BOOKME_VERSION);
        wp_enqueue_script('bookme-notifications', $assets . 'js/pages/notifications.js', array('jquery'), BOOKME_VERSION);

        $current_tab = Request::has_parameter('tab') ? Request::get_parameter('tab') : 'email';

        wp_localize_script('bookme-notifications', 'Bookme', array(
            'csrf_token' => Inc\Mains\Functions\System::get_security_token(),
            'current_tab' => $current_tab,
            'saved' => esc_html__('Notifications have been saved.', 'bookme'),
            'test_send' => esc_html__('Notifications have been sent.', 'bookme'),
            'intlTelInput' => array(
                'enabled' => get_option('bookme_phone_default_country') != 'disabled',
                'utils' => $public_assets . 'js/intlTelInput.utils.js',
                'country' => get_option('bookme_phone_default_country')
            )
        ));


        $types = array(
            'single' => array(
                'client_pending_appointment',
                'staff_pending_appointment',
                'client_approved_appointment',
                'staff_approved_appointment',
                'client_cancelled_appointment',
                'staff_cancelled_appointment',
                'client_rejected_appointment',
                'staff_rejected_appointment',
                'client_new_wp_user',
                'client_reminder',
                'client_follow_up',
                'staff_agenda',
            ),
            'combined' => array(
                'client_pending_appointment_cart',
                'client_approved_appointment_cart',
            )
        );
        if (!Inc\Mains\Functions\System::combined_notifications_enabled()) {
            $types['combined'] = array();
        }

        global $wpdb;

        // load notifications
        $results = $wpdb->get_results(
            "SELECT * 
                FROM `" . Inc\Mains\Tables\Notification::get_table_name() . "`
                WHERE gateway = 'email'",
            ARRAY_A);

        $sms_results = $wpdb->get_results(
            "SELECT * 
                FROM `" . Inc\Mains\Tables\Notification::get_table_name() . "`
                WHERE gateway = 'sms'",
            ARRAY_A);

        $notifications = $sms_notifications = array();
        $notifications['single'] = $sms_notifications['single'] = array();
        $notifications['combined'] = $sms_notifications['combined'] = array();

        foreach ($types['single'] as $type) {
            foreach ($results as $result) {
                if ($result['type'] == $type) {
                    $notifications['single'][] = $result;
                }
            }
            foreach ($sms_results as $result) {
                if ($result['type'] == $type) {
                    $sms_notifications['single'][] = $result;
                }
            }
        }

        foreach ($types['combined'] as $type) {
            foreach ($results as $result) {
                if ($result['type'] == $type) {
                    $notifications['combined'][] = $result;
                }
            }
            foreach ($sms_results as $result) {
                if ($result['type'] == $type) {
                    $sms_notifications['combined'][] = $result;
                }
            }
        }

        // make Visual Mode as default
        add_filter('wp_default_editor', function () {
            return 'tinymce';
        });

        $cron_url = BOOKME_PATH . 'inc/notification_cron.php';

        Inc\Core\Template::create('notifications/page')->display(compact('notifications', 'sms_notifications', 'cron_url'));
    }

    /**
     * Update notifications
     */
    public function perform_update_notifications()
    {
        $data = Request::get_parameter('notification');

        $notification = new Inc\Mains\Tables\Notification();
        foreach ($data as $id => $fields) {
            if ($notification->load($id)) {
                $notification->set_fields($fields)->save();
            }
        }

        $cron_reminder = (array)get_option('bookme_cron_times');
        foreach (array('staff_agenda', 'client_follow_up', 'client_reminder') as $type) {
            $cron_reminder[$type] = Request::get_parameter($type . '_cron_hour');
        }
        update_option('bookme_cron_times', $cron_reminder);

        wp_send_json_success();
    }

    /**
     * Send test email notifications
     */
    public function perform_test_notifications()
    {
        $recipient_email = Request::get_parameter('recipient_email');
        $notifications = Request::get_parameter('notifications');

        $sender_name = get_option('bookme_email_sender_name') == '' ?
            get_option('blogname') : get_option('bookme_email_sender_name');
        $sender_email = get_option('bookme_email_sender') == '' ?
            get_option('admin_email') : get_option('bookme_email_sender');
        $send_as = get_option('bookme_email_send_as');
        $reply_to_customers = (int) get_option('bookme_email_reply_to_customers');

        // Change 'Content-Type' and 'Reply-To' for test email notification.
        add_filter('bookme_email_headers', function ($headers) use ($sender_name, $sender_email, $send_as, $reply_to_customers) {
            $headers = array();
            if ($send_as == 'html') {
                $headers[] = 'Content-Type: text/html; charset=utf-8';
            } else {
                $headers[] = 'Content-Type: text/plain; charset=utf-8';
            }
            $headers[] = 'From: ' . $sender_name . ' <' . $sender_email . '>';
            if ($reply_to_customers) {
                $headers[] = 'Reply-To: ' . $sender_name . ' <' . $sender_email . '>';
            }

            return $headers;
        }, 10, 1);

        Inc\Mains\Notification\Sender::send_test_email_notifications($recipient_email, $notifications, $send_as);

        wp_send_json_success();
    }

    /**
     * Send test sms notifications
     */
    public function perform_test_sms_notifications()
    {
        $sms = new Inc\Mains\SMS();

        $response = array('success' => $sms->send_sms(
            Request::get_parameter('phone'),
            'Bookme test SMS.'
        ));

        if ($response['success']) {
            $response['message'] = esc_html__('SMS has been sent.', 'bookme');
        } else {
            $response['message'] = implode(' ', $sms->get_errors());
        }

        wp_send_json($response);
    }
}