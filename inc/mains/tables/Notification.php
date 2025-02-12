<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class Notification
 */
class Notification extends Inc\Core\Table
{
    /** @var  string */
    protected $gateway = 'email';
    /** @var  string */
    protected $type;
    /** @var  string */
    protected $subject = '';
    /** @var  string */
    protected $message = '';
    /** @var  bool */
    protected $active = 0;
    /** @var  int */
    protected $to_staff = 0;
    /** @var  int */
    protected $to_customer = 0;
    /** @var  bool */
    protected $to_admin = 0;

    protected static $table = 'bm_notifications';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'gateway' => array('format' => '%s'),
        'type' => array('format' => '%s'),
        'subject' => array('format' => '%s'),
        'message' => array('format' => '%s'),
        'active' => array('format' => '%d'),
        'to_staff' => array('format' => '%d'),
        'to_customer' => array('format' => '%d'),
        'to_admin' => array('format' => '%d'),
    );

    /**
     * Save data.
     *
     * @return false|int
     */
    public function save()
    {
        $return = parent::save();
        if ($this->is_loaded()) {
            // Register string for translate in WPML.
            do_action('wpml_register_single_string', 'bookme', $this->get_gateway() . '_' . $this->get_type(), $this->get_message());
            if ($this->get_gateway() == 'email') {
                do_action('wpml_register_single_string', 'bookme', $this->get_gateway() . '_' . $this->get_type() . '_subject', $this->get_subject());
            }
        }

        return $return;
    }

    /**
     * Notification name.
     *
     * @param $type
     * @return string
     */
    public static function get_name($type = null)
    {
        $names = array(
            'client_pending_appointment' => esc_html__('Pending Appointment', 'bookme'),
            'client_pending_appointment_cart' => esc_html__('Pending Appointments', 'bookme'),
            'client_approved_appointment' => esc_html__('Approved Appointment', 'bookme'),
            'client_approved_appointment_cart' => esc_html__('Approved Appointments', 'bookme'),
            'client_cancelled_appointment' => esc_html__('Canceled Appointment', 'bookme'),
            'client_rejected_appointment' => esc_html__('Rejected Appointment', 'bookme'),
            'client_follow_up' => esc_html__('Follow Up Message After Appointment ', 'bookme'),
            'client_new_wp_user' => esc_html__('New WP User Login Details', 'bookme'),
            'client_reminder' => esc_html__('Reminder About Next Day Appointment', 'bookme'),

            'staff_pending_appointment' => esc_html__('Pending Appointment', 'bookme'),
            'staff_approved_appointment' => esc_html__('Approved Appointment', 'bookme'),
            'staff_cancelled_appointment' => esc_html__('Canceled Appointment', 'bookme'),
            'staff_rejected_appointment' => esc_html__('Rejected Appointment', 'bookme'),
            'staff_agenda' => esc_html__('Next Day Agenda', 'bookme')
        );

        if (array_key_exists($type, $names)) {
            return $names[$type];
        } else {
            return esc_html__('Message', 'bookme');
        }
    }

    /**
     * Get gateway
     *
     * @return string
     */
    public function get_gateway()
    {
        return $this->gateway;
    }

    /**
     * Set gateway
     *
     * @param string $gateway
     * @return $this
     */
    public function set_gateway($gateway)
    {
        $this->gateway = $gateway;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function get_type()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return $this
     */
    public function set_type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function get_active()
    {
        return $this->active;
    }

    /**
     * Set active
     *
     * @param bool $active
     * @return $this
     */
    public function set_active($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get to admin
     *
     * @return bool
     */
    public function get_to_admin()
    {
        return $this->to_admin;
    }

    /**
     * Set to admin
     *
     * @param bool $to_admin
     * @return $this
     */
    public function set_to_admin($to_admin)
    {
        $this->to_admin = $to_admin;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function get_subject()
    {
        return $this->subject;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return $this
     */
    public function set_subject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function get_message()
    {
        return $this->message;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return $this
     */
    public function set_message($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get to_staff
     *
     * @return int
     */
    public function get_to_staff()
    {
        return $this->to_staff;
    }

    /**
     * Set to_staff
     *
     * @param int $to_staff
     * @return $this
     */
    public function set_to_staff($to_staff)
    {
        $this->to_staff = $to_staff;

        return $this;
    }

    /**
     * Get to_customer
     *
     * @return int
     */
    public function get_to_customer()
    {
        return $this->to_customer;
    }

    /**
     * Set to_customer
     *
     * @param int $to_customer
     * @return $this
     */
    public function set_to_customer($to_customer)
    {
        $this->to_customer = $to_customer;

        return $this;
    }
}