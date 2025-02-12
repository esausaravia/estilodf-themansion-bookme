<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class Staff
 */
class Employee extends Inc\Core\Table
{
    /** @var  string */
    protected $full_name;
    /** @var  string */
    protected $email;
    /** @var  string */
    protected $phone;
    /** @var  string */
    protected $info;
    /** @var  integer */
    protected $wp_user_id;
    /** @var  integer */
    protected $attachment_id;
    /** @var  string */
    protected $google_data;
    /** @var  string */
    protected $google_calendar_id;
    /** @var  string */
    protected $visibility = 'public';
    /** @var  int */
    protected $position = 9999;
    /** @var string */
    protected $zoom_api_key;
    /** @var string */
    protected $zoom_api_secret;

    protected static $table = 'bm_employees';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'full_name' => array('format' => '%s'),
        'email' => array('format' => '%s'),
        'phone' => array('format' => '%s'),
        'info' => array('format' => '%s'),
        'wp_user_id' => array('format' => '%d'),
        'attachment_id' => array('format' => '%d'),
        'google_data' => array('format' => '%s'),
        'google_calendar_id' => array('format' => '%s'),
        'visibility' => array('format' => '%s'),
        'position' => array('format' => '%d'),
        'zoom_api_key' => array( 'format' => '%s' ),
        'zoom_api_secret' => array( 'format' => '%s' ),
    );

    /**
     * Delete staff member.
     */
    public function delete()
    {
        if ($this->get_google_data()) {
            $google = new Inc\Mains\Google();
            $google->load_by_staff($this);
            $google->revoke_token();
        }

        parent::delete();
    }

    /**
     * @return false|int
     */
    public function save()
    {
        $is_new = !$this->get_id();

        if ($is_new && $this->get_wp_user_id()) {
            $user = get_user_by('id', $this->get_wp_user_id());
            if ($user) {
                $this->set_email($user->get('user_email'));
            }
        }

        $return = parent::save();
        if ($this->is_loaded()) {
            // Register string for translate in WPML.
            do_action('wpml_register_single_string', 'bookme', 'staff_' . $this->get_id(), $this->get_full_name());
            do_action('wpml_register_single_string', 'bookme', 'staff_' . $this->get_id() . '_info', $this->get_info());
        }
        if ($is_new) {
            // Schedule items.
            $staff_id = $this->get_id();
            foreach (array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $day_index => $week_day) {
                $item = new EmployeeSchedule();
                $item->set_staff_id($staff_id)
                    ->set_day_index($day_index + 1)
                    ->set_start_time(get_option('bookme_wh_' . $week_day . '_start') ?: null)
                    ->set_end_time(get_option('bookme_wh_' . $week_day . '_end') ?: null)
                    ->save();
            }

            // Create holidays for staff
            self::$wpdb->query(sprintf(
                'INSERT INTO `' . Holiday::get_table_name() . '` (`parent_id`, `staff_id`, `date`, `repeat_event`)
                SELECT `id`, %d, `date`, `repeat_event` FROM `' . Holiday::get_table_name() . '` WHERE `staff_id` IS NULL',
                $staff_id
            ));
        }

        return $return;
    }

    /**
     * Get schedule items of staff member.
     *
     * @return EmployeeSchedule[]
     */
    public function get_schedule()
    {
        $start_of_week = (int)get_option('start_of_week');

        /** @var \wpdb $wpdb */
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `" . Inc\Mains\Tables\EmployeeSchedule::get_table_name() . "` 
                    WHERE staff_id = %d
                    ORDER BY IF(day_index + 10 - {$start_of_week} > 10, day_index + 10 - {$start_of_week}, 16 + day_index)",
                $this->get_id()
            ),
            ARRAY_A
        );
    }

    /**
     * Get EmployeeService tables associated with this staff member.
     *
     * @return EmployeeService[]
     */
    public function get_employee_services()
    {
        $result = array();

        if ($this->get_id()) {
            global $wpdb;

            $staff_services = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT ss.*, s.title, s.duration, s.price AS service_price, s.color, s.capacity_min AS service_capacity_min, s.capacity_max AS service_capacity_max FROM 
                    `" . Inc\Mains\Tables\EmployeeService::get_table_name() . "` ss 
                    LEFT JOIN `" . Inc\Mains\Tables\Service::get_table_name() . "` s ON s.id = ss.service_id
                    WHERE ss.staff_id = %d",
                    $this->get_id()
                ),
                ARRAY_A);

            foreach ($staff_services as $data) {
                $ss = new EmployeeService($data);

                // Insert Service data.
                $ss->service = new Service();
                $ss->service
                    ->set_id($data['service_id'])
                    ->set_title($data['title'])
                    ->set_color($data['color'])
                    ->set_duration($data['duration'])
                    ->set_price($data['service_price'])
                    ->set_capacity_min($data['service_capacity_min'])
                    ->set_capacity_max($data['service_capacity_max']);

                $result[] = $ss;
            }
        }

        return $result;
    }

    /**
     * @param string $locale
     * @return string
     */
    public function get_translated_name($locale = null)
    {
        return Inc\Mains\Functions\System::get_translated_string('staff_' . $this->get_id(), $this->get_full_name(), $locale);
    }

    /**
     * @param string $locale
     * @return string
     */
    public function get_translated_info($locale = null)
    {
        return Inc\Mains\Functions\System::get_translated_string('staff_' . $this->get_id() . '_info', $this->get_info(), $locale);
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
     * Set wp_user_id
     *
     * @param int $wp_user_id
     * @return $this
     */
    public function set_wp_user_id($wp_user_id)
    {
        $this->wp_user_id = $wp_user_id;

        return $this;
    }

    /**
     * Gets attachment_id
     *
     * @return int
     */
    public function get_attachment_id()
    {
        return $this->attachment_id;
    }

    /**
     * Sets attachment_id
     *
     * @param int $attachment_id
     * @return $this
     */
    public function set_attachment_id($attachment_id)
    {
        $this->attachment_id = $attachment_id;

        return $this;
    }

    /**
     * Gets full name
     *
     * @return string
     */
    public function get_full_name()
    {
        return $this->full_name;
    }

    /**
     * Sets full name
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
     * Gets google data
     *
     * @return string
     */
    public function get_google_data()
    {
        return $this->google_data;
    }

    /**
     * Sets google data
     *
     * @param string $google_data
     * @return $this
     */
    public function set_google_data($google_data)
    {
        $this->google_data = $google_data;

        return $this;
    }

    /**
     * Gets google calendar_id
     *
     * @return string
     */
    public function get_google_calendar_id()
    {
        return $this->google_calendar_id;
    }

    /**
     * Sets google calendar_id
     *
     * @param string $google_calendar_id
     * @return $this
     */
    public function set_google_calendar_id($google_calendar_id)
    {
        $this->google_calendar_id = $google_calendar_id;

        return $this;
    }

    /**
     * Gets info
     *
     * @return string
     */
    public function get_info()
    {
        return $this->info;
    }

    /**
     * Sets info
     *
     * @param string $info
     * @return $this
     */
    public function set_info($info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Gets visibility
     *
     * @return string
     */
    public function get_visibility()
    {
        return $this->visibility;
    }

    /**
     * Sets visibility
     *
     * @param string $visibility
     * @return $this
     */
    public function set_visibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Gets position
     *
     * @return int
     */
    public function get_position()
    {
        return $this->position;
    }

    /**
     * Sets position
     *
     * @param int $position
     * @return $this
     */
    public function set_position($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Gets zoom_api_key
     *
     * @return string
     */
    public function get_zoom_api_key()
    {
        return $this->zoom_api_key;
    }

    /**
     * Sets zoom_api_key
     *
     * @param string $zoom_api_key
     * @return $this
     */
    public function set_zoom_api_key( $zoom_api_key )
    {
        $this->zoom_api_key = $zoom_api_key;

        return $this;
    }

    /**
     * Gets zoom_api_secret
     *
     * @return string
     */
    public function get_zoom_api_secret()
    {
        return $this->zoom_api_secret;
    }

    /**
     * Sets zoom_api_secret
     *
     * @param string $zoom_api_secret
     * @return $this
     */
    public function set_zoom_api_secret( $zoom_api_secret )
    {
        $this->zoom_api_secret = $zoom_api_secret;

        return $this;
    }
}