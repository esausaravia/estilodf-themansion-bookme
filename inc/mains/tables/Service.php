<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class Service
 */
class Service extends Inc\Core\Table
{
    const PREFERRED_ORDER = 'order';
    const PREFERRED_LEAST_OCCUPIED = 'least_occupied';
    const PREFERRED_MOST_OCCUPIED = 'most_occupied';
    const PREFERRED_LEAST_EXPENSIVE = 'least_expensive';
    const PREFERRED_MOST_EXPENSIVE = 'most_expensive';

    /** @var  int */
    protected $category_id;
    /** @var  string */
    protected $color;
    /** @var  string */
    protected $title;
    /** @var  int */
    protected $duration = 900;
    /** @var  float */
    protected $price = 0;
    /** @var  int */
    protected $capacity_min = 1;
    /** @var  int */
    protected $capacity_max = 1;
    /** @var  int */
    protected $padding_left = 0;
    /** @var  int */
    protected $padding_right = 0;
    /** @var  string */
    protected $info;
    /** @var  string */
    protected $start_time_info;
    /** @var  string */
    protected $end_time_info;
    /** @var  int */
    protected $bookings_limit = null;
    /** @var  string */
    protected $limit_period = 'off';
    /** @var  string */
    protected $staff_preference = Service::PREFERRED_MOST_EXPENSIVE;
    /** @var  string */
    protected $online_meeting = 'off';
    /** @var  string */
    protected $visibility = 'public';
    /** @var  int */
    protected $position = 9999;

    protected static $table = 'bm_services';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'category_id' => array('format' => '%d', 'reference' => array('table' => 'Category')),
        'color' => array('format' => '%s'),
        'title' => array('format' => '%s'),
        'duration' => array('format' => '%d'),
        'price' => array('format' => '%f'),
        'capacity_min' => array('format' => '%d'),
        'capacity_max' => array('format' => '%d'),
        'padding_left' => array('format' => '%d'),
        'padding_right' => array('format' => '%d'),
        'info' => array('format' => '%s'),
        'start_time_info' => array('format' => '%s'),
        'end_time_info' => array('format' => '%s'),
        'bookings_limit' => array('format' => '%d'),
        'limit_period' => array('format' => '%s'),
        'staff_preference' => array('format' => '%s'),
        'visibility' => array('format' => '%s'),
        'online_meeting' => array('format' => '%s'),
        'position' => array('format' => '%d'),
    );

    /**
     * Save service.
     *
     * @return false|int
     */
    public function save()
    {
        $return = parent::save();
        if ($this->is_loaded()) {
            // Register string for translate in WPML.
            do_action('wpml_register_single_string', 'bookme', 'service_' . $this->get_id(), $this->get_title());
            do_action('wpml_register_single_string', 'bookme', 'service_' . $this->get_id() . '_info', $this->get_info());
        }

        return $return;
    }

    /**
     * Get translated title
     *
     * @param string $locale
     * @return string
     */
    public function get_translated_title($locale = null)
    {
        return $this->get_title() != ''
            ? Inc\Mains\Functions\System::get_translated_string('service_' . $this->get_id(), $this->get_title(), $locale)
            : esc_html__('Untitled', 'bookme');
    }

    /**
     * Get category name.
     *
     * @param string $locale
     * @return string
     */
    public function get_translated_category_name($locale = null)
    {
        if ($this->get_category_id()) {
            return Category::find($this->get_category_id())->get_translated_name($locale);
        }

        return esc_html__('Uncategorized', 'bookme');
    }

    /**
     * Get translated info.
     *
     * @param string $locale
     * @return string
     */
    public function get_translated_info($locale = null)
    {
        return Inc\Mains\Functions\System::get_translated_string('service_' . $this->get_id() . '_info', $this->get_info(), $locale);
    }

    /**
     * @param int $customer_id
     * @param string $booking_date format( 'Y-m-d H:i:s' )
     * @return bool
     */
    public function check_bookings_limit_reached($customer_id, $booking_date)
    {
        if ($this->get_limit_period() != 'off' && $this->get_bookings_limit() > 0) {
            $booking_last_date = $booking_date;
            $booking_first_date = date_create($booking_date)->modify(sprintf('-1 %s', $this->get_limit_period()))->format('Y-m-d H:i:s');

            global $wpdb;
            $bookings = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM `" . Inc\Mains\Tables\CustomerBooking::get_table_name() . "` AS `ca` 
                    LEFT JOIN `" . Inc\Mains\Tables\Booking::get_table_name() . "` AS `a` ON ca.booking_id = a.id 
                    WHERE `a`.`service_id` = %d 
                        AND `ca`.`customer_id` = %d 
                        AND `a`.`start_date` > %s 
                        AND `a`.`start_date` < %s",
                    $this->get_id(),
                    $customer_id,
                    $booking_first_date,
                    $booking_last_date
                )
            );
            if ($bookings >= $this->get_bookings_limit()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get category_id
     *
     * @return int
     */
    public function get_category_id()
    {
        return $this->category_id;
    }

    /**
     * Set category
     *
     * @param Category $category
     * @return $this
     */
    public function set_category(Category $category)
    {
        return $this->set_category_id($category->get_id());
    }

    /**
     * Set category_id
     *
     * @param int $category_id
     * @return $this
     */
    public function set_category_id($category_id)
    {
        $this->category_id = $category_id;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function get_title()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function set_title($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get duration
     *
     * @return int
     */
    public function get_duration()
    {
        return $this->duration;
    }

    /**
     * Set duration
     *
     * @param int $duration
     * @return $this
     */
    public function set_duration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function get_price()
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function set_price($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function get_color()
    {
        return $this->color;
    }

    /**
     * Set color
     *
     * @param string $color
     * @return $this
     */
    public function set_color($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get capacity_min
     *
     * @return int
     */
    public function get_capacity_min()
    {
        return $this->capacity_min;
    }

    /**
     * Set capacity_min
     *
     * @param int $capacity_min
     * @return $this
     */
    public function set_capacity_min($capacity_min)
    {
        $this->capacity_min = $capacity_min;

        return $this;
    }

    /**
     * Get capacity_max
     *
     * @return int
     */
    public function get_capacity_max()
    {
        return $this->capacity_max;
    }

    /**
     * Set capacity_max
     *
     * @param int $capacity_max
     * @return $this
     */
    public function set_capacity_max($capacity_max)
    {
        $this->capacity_max = $capacity_max;

        return $this;
    }

    /**
     * Get padding_left
     *
     * @return int
     */
    public function get_padding_left()
    {
        return $this->padding_left;
    }

    /**
     * Set padding_left
     *
     * @param int $padding_left
     * @return $this
     */
    public function set_padding_left($padding_left)
    {
        $this->padding_left = $padding_left;

        return $this;
    }

    /**
     * Get padding_right
     *
     * @return int
     */
    public function get_padding_right()
    {
        return $this->padding_right;
    }

    /**
     * Set padding_right
     *
     * @param int $padding_right
     * @return $this
     */
    public function set_padding_right($padding_right)
    {
        $this->padding_right = $padding_right;

        return $this;
    }

    /**
     * Get info
     *
     * @return string
     */
    public function get_info()
    {
        return $this->info;
    }

    /**
     * Set info
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
     * Get start time info
     *
     * @return string
     */
    public function get_start_time_info()
    {
        return $this->start_time_info;
    }

    /**
     * Set start time info
     *
     * @param string $start_time_info
     * @return $this
     */
    public function set_start_time_info($start_time_info)
    {
        $this->start_time_info = $start_time_info;

        return $this;
    }

    /**
     * Get end time info
     *
     * @return string
     */
    public function get_end_time_info()
    {
        return $this->end_time_info;
    }

    /**
     * Set end time info
     *
     * @param string $end_time_info
     * @return $this
     */
    public function set_end_time_info($end_time_info)
    {
        $this->end_time_info = $end_time_info;

        return $this;
    }

    /**
     * Get bookings_limit
     *
     * @return int
     */
    public function get_bookings_limit()
    {
        return $this->bookings_limit;
    }

    /**
     * Set bookings_limit
     *
     * @param int $bookings_limit
     * @return $this
     */
    public function set_bookings_limit($bookings_limit)
    {
        $this->bookings_limit = $bookings_limit;

        return $this;
    }

    /**
     * Get limit_period
     *
     * @return string
     */
    public function get_limit_period()
    {
        return $this->limit_period;
    }

    /**
     * Set limit_period
     *
     * @param string $limit_period
     * @return $this
     */
    public function set_limit_period($limit_period)
    {
        $this->limit_period = $limit_period;

        return $this;
    }

    /**
     * Get staff_preference
     *
     * @return string
     */
    public function get_staff_preference()
    {
        return $this->staff_preference;
    }

    /**
     * Set staff_preference
     *
     * @param string $staff_preference
     * @return $this
     */
    public function set_staff_preference($staff_preference)
    {
        $this->staff_preference = $staff_preference;

        return $this;
    }

    /**
     * Get online meeting
     *
     * @return string
     */
    public function get_online_meeting()
    {
        return $this->online_meeting;
    }

    /**
     * Set online meeting
     *
     * @param string $online_meeting
     * @return $this
     */
    public function set_online_meeting($online_meeting)
    {
        $this->online_meeting = $online_meeting;

        return $this;
    }

    /**
     * Get visibility
     *
     * @return string
     */
    public function get_visibility()
    {
        return $this->visibility;
    }

    /**
     * Set visibility
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
     * Get position
     *
     * @return int
     */
    public function get_position()
    {
        return $this->position;
    }

    /**
     * Set position
     *
     * @param int $position
     * @return $this
     */
    public function set_position($position)
    {
        $this->position = $position;

        return $this;
    }
}