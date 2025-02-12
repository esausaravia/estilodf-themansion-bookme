<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class Customer
 */
class Customer extends Inc\Core\Table
{
    /** @var  string */
    protected $full_name = '';
    /** @var  string */
    protected $first_name = '';
    /** @var  string */
    protected $last_name = '';
    /** @var  string */
    protected $phone = '';
    /** @var  string */
    protected $email = '';
    /** @var  int */
    protected $wp_user_id;
    /** @var  string */
    protected $notes = '';

    protected static $table = 'bm_customers';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'full_name' => array('format' => '%s'),
        'first_name' => array('format' => '%s'),
        'last_name' => array('format' => '%s'),
        'phone' => array('format' => '%s'),
        'email' => array('format' => '%s'),
        'wp_user_id' => array('format' => '%d'),
        'notes' => array('format' => '%s'),
    );

    /**
     * Save data to database.
     * Fill name, first_name, last_name before save
     *
     * @return int|false
     */
    public function save()
    {
        if ((!Inc\Mains\Functions\System::show_first_last_name() && $this->get_full_name() != '') || ($this->get_full_name() != '' && $this->get_first_name() == '' && $this->get_last_name() == '')) {
            $full_name = explode(' ', $this->get_full_name(), 2);
            $this->set_first_name($full_name[0]);
            $this->set_last_name(isset ($full_name[1]) ? trim($full_name[1]) : '');
        } else {
            $this->set_full_name(trim(rtrim($this->get_first_name()) . ' ' . ltrim($this->get_last_name())));
        }

        return parent::save();
    }

    /**
     * Get wp_user_id
     *
     * @return int
     */
    public function get_wp_user_id()
    {
        return $this->wp_user_id;
    }

    /**
     * Associate WP user with customer.
     *
     * @param int $wp_user_id
     * @return $this
     */
    public function set_wp_user_id($wp_user_id = 0)
    {
        if ($wp_user_id == 0) {
            $wp_user_id = $this->create_wp_user();
        }

        if ($wp_user_id) {
            $this->wp_user_id = $wp_user_id;
        }

        return $this;
    }

    /**
     * Delete customer
     *
     * @param bool $with_wp_user
     */
    public function delete_with_wp_user($with_wp_user)
    {
        if ($with_wp_user && $this->get_wp_user_id()
            // Can't delete your WP account
            && ($this->get_wp_user_id() != get_current_user_id())) {
            wp_delete_user($this->get_wp_user_id());
        }

        /** @var Booking[] $bookings */
        global $wpdb;
        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `" . Booking::get_table_name() . "` a 
                LEFT JOIN `" . CustomerBooking::get_table_name() . "` AS `ca` ON ca.booking_id = a.id
                WHERE ca.customer_id = %d 
                GROUP BY a.id",
                $this->get_id()
            ),
            ARRAY_A
        );
        $bookings = Inc\Mains\Functions\System::bind_data_with_table(Booking::class, $data);

        $this->delete();

        foreach ($bookings as $booking) {
            // Google Calendar.
            $booking->handle_google_calendar();
        }
    }

    /**
     * Get future bookings.
     *
     * @return array
     */
    public function get_future_bookings()
    {
        return $this->get_bookings("a.start_date >= '". current_time( 'Y-m-d 00:00:00' )."'");
    }

    /**
     * Get past bookings.
     *
     * @param $page
     * @param $limit
     * @return array
     */
    public function get_old_bookings( $page, $limit )
    {
        $result = array( 'more' => true, 'bookings' => array() );

        $data = $this->get_bookings(
            "a.start_date < '". current_time( 'Y-m-d 00:00:00' )."'",
            $limit + 1,
            ( $page - 1 ) * $limit
        );

        $result['more'] = count( $data ) > $limit;
        if ( $result['more'] ) {
            array_pop( $data );
        }

        $result['bookings'] = $data;

        return $result;
    }

    /**
     * Create new WP user and send email notification.
     *
     * @return int|false
     */
    private function create_wp_user()
    {
        // Generate unique username.
        $base = Inc\Mains\Functions\System::show_first_last_name()
            ? sanitize_user(sprintf('%s %s', $this->get_first_name(), $this->get_last_name()), true)
            : sanitize_user($this->get_full_name(), true);
        $base = $base != '' ? $base : 'client';
        $username = $base;
        $i = 1;
        while (username_exists($username)) {
            $username = $base . $i;
            ++$i;
        }
        // Generate password.
        $password = wp_generate_password(6, true);
        // Create user.
        $user_id = wp_create_user($username, $password, $this->get_email());
        if (!$user_id instanceof \WP_Error) {
            // Set the role
            $user = new \WP_User($user_id);
            $user->set_role(get_option('bookme_customer_new_account_role', 'subscriber'));

            // Send email/sms notification.
            Inc\Mains\Notification\Sender::send_new_user_credentials($this, $username, $password);

            return $user_id;
        }

        return false;
    }

    /**
     * Get bookings for get_future_bookings and get_past_bookings methods.
     *
     * @return array
     */
    private function get_bookings($where = '', $limit = '', $offset = '')
    {
        $client_diff = get_option( 'gmt_offset' ) * MINUTE_IN_SECONDS;

        global $wpdb;

        $limit = ( $limit > 0 ) ? ' LIMIT ' . $limit : '';
        $offset = ( $offset > 0 ) ? ' OFFSET ' . $offset : '';

        return $wpdb->get_results(
            "SELECT 
                    a.staff_id, a.staff_any, a.service_id, 
                    s.title AS service, s.category_id, 
                    c.name AS category, 
                    st.full_name AS staff,  
                    ca.id AS ca_id, ca.status AS booking_status, ca.number_of_persons, ca.custom_fields, ca.booking_id,
                    ss.price * ca.number_of_persons AS price,
                    IF( ca.time_zone_offset IS NULL,
                        a.start_date,
                        DATE_SUB(a.start_date, INTERVAL $client_diff + ca.time_zone_offset MINUTE)
                       ) AS start_date,
                    ca.token
                FROM `" . Inc\Mains\Tables\Booking::get_table_name() . "` AS `a` 
                LEFT JOIN `" . Inc\Mains\Tables\Employee::get_table_name() . "` AS `st` ON st.id = a.staff_id 
                LEFT JOIN `" . Inc\Mains\Tables\Customer::get_table_name() . "` AS `customer` ON customer.wp_user_id = " . $this->get_wp_user_id() . " 
                INNER JOIN `" . Inc\Mains\Tables\CustomerBooking::get_table_name() . "` AS `ca` ON ca.booking_id = a.id AND ca.customer_id = customer.id 
                LEFT JOIN `" . Inc\Mains\Tables\Service::get_table_name() . "` AS `s` ON s.id = a.service_id 
                LEFT JOIN `" . Inc\Mains\Tables\Category::get_table_name() . "` AS `c` ON c.id = s.category_id 
                LEFT JOIN `" . Inc\Mains\Tables\EmployeeService::get_table_name() . "` AS `ss` ON ss.staff_id = a.staff_id AND ss.service_id = a.service_id 
                LEFT JOIN `" . Inc\Mains\Tables\Payment::get_table_name() . "` AS `p` ON p.id = ca.payment_id 
                WHERE $where  
                ORDER BY start_date DESC
                $limit $offset"
            ,
            ARRAY_A);

        return Appointment::query( 'a' )
            ->select( 'ca.id AS ca_id,
                    c.name AS category,
                    s.title AS service,
                    st.full_name AS staff,
                    a.staff_id,
                    a.staff_any,
                    a.service_id,
                    s.category_id,
                    ca.status AS appointment_status,
                    ca.extras,
                    ca.compound_token,
                    ca.number_of_persons,
                    ca.custom_fields,
                    ca.appointment_id,
                    IF( ca.compound_service_id IS NULL, ss.price, s.price ) * ca.number_of_persons AS price,
                    IF( ca.time_zone_offset IS NULL,
                        a.start_date,
                        DATE_SUB(a.start_date, INTERVAL ' . $client_diff . ' + ca.time_zone_offset MINUTE)
                       ) AS start_date,
                    ca.token' )
            ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id' )
            ->leftJoin( 'Customer', 'customer', 'customer.wp_user_id = ' . $this->getWpUserId() )
            ->innerJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id AND ca.customer_id = customer.id' )
            ->leftJoin( 'Service', 's', 's.id = COALESCE(ca.compound_service_id, a.service_id)' )
            ->leftJoin( 'Category', 'c', 'c.id = s.category_id' )
            ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
            ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
            ->sortBy( 'start_date' )
            ->order( 'DESC' );
    }

    /**
     * Gets full_name
     *
     * @return string
     */
    public function get_full_name()
    {
        return $this->full_name;
    }

    /**
     * Sets full_name
     *
     * @param string $full_name
     * @return $this
     */
    public function set_full_name($full_name)
    {
        $this->full_name = $full_name;

        return $this;
    }

    /**
     * Gets first_name
     *
     * @return string
     */
    public function get_first_name()
    {
        return $this->first_name;
    }

    /**
     * Sets first_name
     *
     * @param string $first_name
     * @return $this
     */
    public function set_first_name($first_name)
    {
        $this->first_name = $first_name;

        return $this;
    }

    /**
     * Gets last_name
     *
     * @return string
     */
    public function get_last_name()
    {
        return $this->last_name;
    }

    /**
     * Sets last_name
     *
     * @param string $last_name
     * @return $this
     */
    public function set_last_name($last_name)
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * Gets phone
     *
     * @return string
     */
    public function get_phone()
    {
        return $this->phone;
    }

    /**
     * Sets phone
     *
     * @param string $phone
     * @return $this
     */
    public function set_phone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Gets email
     *
     * @return string
     */
    public function get_email()
    {
        return $this->email;
    }

    /**
     * Sets email
     *
     * @param string $email
     * @return $this
     */
    public function set_email($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Gets notes
     *
     * @return string
     */
    public function get_notes()
    {
        return $this->notes;
    }

    /**
     * Sets notes
     *
     * @param string $notes
     * @return $this
     */
    public function set_notes($notes)
    {
        $this->notes = $notes;

        return $this;
    }
}