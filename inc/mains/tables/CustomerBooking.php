<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class CustomerBooking
 */
class CustomerBooking extends Inc\Core\Table
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED = 'rejected';

    /** @var  int */
    protected $customer_id;
    /** @var  int */
    protected $booking_id;
    /** @var  int */
    protected $payment_id;
    /** @var  int */
    protected $number_of_persons = 1;
    /** @var  string */
    protected $custom_fields = '[]';
    /** @var  string */
    protected $status;
    /** @var  string */
    protected $token;
    /** @var  string */
    protected $time_zone;
    /** @var  int */
    protected $time_zone_offset;
    /** @var  string */
    protected $locale;
    /** @var  string */
    protected $created_from;
    /** @var  string */
    protected $created;

    protected static $table = 'bm_customer_bookings';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'customer_id' => array('format' => '%d', 'reference' => array('table' => 'Customer')),
        'booking_id' => array('format' => '%d', 'reference' => array('table' => 'Booking')),
        'payment_id' => array('format' => '%d', 'reference' => array('table' => 'Payment')),
        'number_of_persons' => array('format' => '%d'),
        'custom_fields' => array('format' => '%s'),
        'status' => array('format' => '%s'),
        'token' => array('format' => '%s'),
        'time_zone' => array('format' => '%s'),
        'time_zone_offset' => array('format' => '%d'),
        'locale' => array('format' => '%s'),
        'created_from' => array('format' => '%s'),
        'created' => array('format' => '%s'),
    );

    /** @var Customer */
    public $customer;

    /**
     * Save data to database.
     * Generate token before saving.
     *
     * @return int|false
     */
    public function save()
    {
        // Generate new token if it is not set.
        if ($this->get_token() == '') {
            $this->set_token(Inc\Mains\Functions\System::generate_token(get_class($this), 'token'));
        }
        if ($this->get_locale() === null) {
            $this->set_locale(apply_filters('wpml_current_language', null));
        }

        return parent::save();
    }

    public static function status_to_string($status)
    {
        switch ($status) {
            case self::STATUS_PENDING:
                return esc_html__('Pending', 'bookme');
            case self::STATUS_APPROVED:
                return esc_html__('Approved', 'bookme');
            case self::STATUS_CANCELLED:
                return esc_html__('Cancelled', 'bookme');
            case self::STATUS_REJECTED:
                return esc_html__('Rejected', 'bookme');
            default:
                return '';
        }
    }

    /**
     * @return array
     */
    public static function get_statuses()
    {
        $statuses = array(
            CustomerBooking::STATUS_PENDING,
            CustomerBooking::STATUS_APPROVED,
            CustomerBooking::STATUS_CANCELLED,
            CustomerBooking::STATUS_REJECTED,
        );

        return $statuses;
    }

    /**
     * Get array of custom fields with labels and values.
     *
     * @return array
     */
    public function get_custom_fields_data()
    {
        return $this->get_prepared_custom_fields();
    }

    /**
     * Get translated array of custom fields with labels and values.
     *
     * @param string $locale
     * @return array
     */
    public function get_translated_custom_fields($locale = null)
    {
        return $this->get_prepared_custom_fields(true, $locale);
    }

    /**
     * Get formatted custom fields.
     *
     * @param string $format
     * @param string $locale
     * @return string
     */
    public function get_formatted_custom_fields($format, $locale = null)
    {
        $result = '';
        switch ($format) {
            case 'html':
                foreach ($this->get_translated_custom_fields($locale) as $custom_field) {
                    if ($custom_field['value'] != '') {
                        $result .= sprintf(
                            '<tr valign=top><td>%s:&nbsp;</td><td>%s</td></tr>',
                            $custom_field['label'], $custom_field['value']
                        );
                    }
                }
                if ($result != '') {
                    $result = "<table cellspacing=0 cellpadding=0 border=0>$result</table>";
                }
                break;

            case 'text':
                foreach ($this->get_translated_custom_fields($locale) as $custom_field) {
                    if ($custom_field['value'] != '') {
                        $result .= sprintf(
                            "%s: %s\n",
                            $custom_field['label'], $custom_field['value']
                        );
                    }
                }
                break;
        }

        return $result;
    }

    /**
     * Delete data and booking if there are no more customers.
     */
    public function delete_cascade()
    {
        $this->delete();
        $booking = new Booking();
        if ($booking->load($this->get_booking_id())) {
            // Check if there are any customers left.
            global $wpdb;
            if ($wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*) FROM `" . Inc\Mains\Tables\CustomerBooking::get_table_name() . "` 
                WHERE booking_id != %d",
                        $booking->get_id()
                    )
                ) == 0) {
                // If no customers then delete the booking.
                $booking->delete();
            } else {
                // Update GC event.
                $booking->handle_google_calendar();
            }
        }
    }

    public function get_status_title()
    {
        return self::status_to_string($this->get_status());
    }

    public function cancel()
    {
        $booking = new Booking();
        if ($booking->load($this->get_booking_id())) {
            if ($this->get_status() != CustomerBooking::STATUS_CANCELLED
                && $this->get_status() != CustomerBooking::STATUS_REJECTED
            ) {
                $this->set_status(CustomerBooking::STATUS_CANCELLED);
                Inc\Mains\Notification\Sender::send_single(Inc\Mains\Booking\DataHolders\Service::create($this));
            }

            $this->save();
            // Google Calendar.
            $booking->handle_google_calendar();
        }
    }

    /**
     * @param bool $translate
     * @param null $locale
     * @return array
     */
    private function get_prepared_custom_fields($translate = false, $locale = null)
    {
        $service_id = null;
        if (Inc\Mains\Functions\System::custom_fields_per_service()) {
            $service_id = Booking::find($this->get_booking_id())->get_service_id();
        }
        $result = array();
        if ($this->custom_fields != '[]') {
            $custom_fields = array();
            $cf = $translate ? Inc\Mains\Functions\System::get_translated_custom_fields($service_id, $locale) : Inc\Mains\Functions\System::get_custom_fields($service_id);
            foreach ($cf as $field) {
                $custom_fields[$field->id] = $field;
            }
            $data = json_decode($this->custom_fields, true);
            if (is_array($data)) {
                foreach ($data as $customer_custom_field) {
                    if (array_key_exists($customer_custom_field['id'], $custom_fields)) {
                        $field = $custom_fields[$customer_custom_field['id']];
                        $translated_value = array();
                        if (array_key_exists('value', $customer_custom_field)) {
                            // Custom field have items ( radio group, etc. )
                            if (property_exists($field, 'items')) {
                                foreach ($field->items as $item) {
                                    // Customer select many values ( checkbox )
                                    if (is_array($customer_custom_field['value'])) {
                                        foreach ($customer_custom_field['value'] as $field_value) {
                                            if ($item['value'] == $field_value) {
                                                $translated_value[] = $item['label'];
                                            }
                                        }
                                    } elseif ($item['value'] == $customer_custom_field['value']) {
                                        $translated_value[] = $item['label'];
                                    }
                                }
                            } else {
                                $translated_value[] = $customer_custom_field['value'];
                            }
                        }
                        $result[] = array(
                            'id' => $customer_custom_field['id'],
                            'label' => $field->label,
                            'value' => implode(', ', $translated_value)
                        );
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Gets customer_id
     *
     * @return int
     */
    public function get_customer_id()
    {
        return $this->customer_id;
    }

    /**
     * Sets customer
     * @param Customer $customer
     * @return $this
     */
    public function set_customer(Customer $customer)
    {
        return $this->set_customer_id($customer->get_id());
    }

    /**
     * Sets customer_id
     *
     * @param int $customer_id
     * @return $this
     */
    public function set_customer_id($customer_id)
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    /**
     * Gets booking_id
     *
     * @return int
     */
    public function get_booking_id()
    {
        return $this->booking_id;
    }

    /**
     * @param Booking $booking
     * @return $this
     */
    public function set_booking(Booking $booking)
    {
        return $this->set_booking_id($booking->get_id());
    }

    /**
     * Sets booking_id
     *
     * @param int $booking_id
     * @return $this
     */
    public function set_booking_id($booking_id)
    {
        $this->booking_id = $booking_id;

        return $this;
    }

    /**
     * Gets payment_id
     *
     * @return int
     */
    public function get_payment_id()
    {
        return $this->payment_id;
    }

    /**
     * Sets payment_id
     *
     * @param int $payment_id
     * @return $this
     */
    public function set_payment_id($payment_id)
    {
        $this->payment_id = $payment_id;

        return $this;
    }

    /**
     * Gets number_of_persons
     *
     * @return int
     */
    public function get_number_of_persons()
    {
        return $this->number_of_persons;
    }

    /**
     * Sets number_of_persons
     *
     * @param int $number_of_persons
     * @return $this
     */
    public function set_number_of_persons($number_of_persons)
    {
        $this->number_of_persons = $number_of_persons;

        return $this;
    }

    /**
     * Sets custom_fields
     *
     * @param string $custom_fields
     * @return $this
     */
    public function set_custom_fields($custom_fields)
    {
        $this->custom_fields = $custom_fields;

        return $this;
    }

    /**
     * Gets custom_fields
     *
     * @return string
     */
    public function get_custom_fields()
    {
        return $this->custom_fields;
    }

    /**
     * Gets status
     *
     * @return string
     */
    public function get_status()
    {
        return $this->status;
    }

    /**
     * Sets status
     *
     * @param string $status
     * @return $this
     */
    public function set_status($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Gets token
     *
     * @return string
     */
    public function get_token()
    {
        return $this->token;
    }

    /**
     * Sets token
     *
     * @param string $token
     * @return $this
     */
    public function set_token($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Gets time_zone
     *
     * @return string
     */
    public function get_time_zone()
    {
        return $this->time_zone;
    }

    /**
     * Sets time_zone
     *
     * @param string $time_zone
     * @return $this
     */
    public function setTimeZone($time_zone)
    {
        $this->time_zone = $time_zone;

        return $this;
    }

    /**
     * Gets time_zone_offset
     *
     * @return int
     */
    public function get_time_zone_offset()
    {
        return $this->time_zone_offset;
    }

    /**
     * Sets time_zone_offset
     *
     * @param int $time_zone_offset
     * @return $this
     */
    public function set_time_zone_offset($time_zone_offset)
    {
        $this->time_zone_offset = $time_zone_offset;

        return $this;
    }

    /**
     * Gets locale
     *
     * @return string
     */
    public function get_locale()
    {
        return $this->locale;
    }

    /**
     * Sets locale
     *
     * @param string $locale
     * @return $this
     */
    public function set_locale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Gets created_from
     *
     * @return string
     */
    public function get_created_from()
    {
        return $this->created_from;
    }

    /**
     * Sets created_from
     *
     * @param string $created_from
     * @return $this
     */
    public function set_created_from($created_from)
    {
        $this->created_from = $created_from;

        return $this;
    }

    /**
     * Gets created
     *
     * @return string
     */
    public function get_created()
    {
        return $this->created;
    }

    /**
     * Sets created
     *
     * @param string $created
     * @return $this
     */
    public function set_created($created)
    {
        $this->created = $created;

        return $this;
    }
}