<?php
namespace Bookme\Inc\Mains\Functions;

/**
 * Class Time
 */
class Time
{
    /** @var string */
    protected static $wp_timezone_offset = null;
    /** @var string */
    public static $client_timezone_offset = null;

    /** @var int */
    protected $time;

    /**
     * Constructor.
     *
     * @param int $time
     */
    public function __construct( $time )
    {
        $this->time = $time;
    }

    /**
     * Create Time from string.
     *
     * @param string $time  Format H:i[:s]
     * @return static
     */
    public static function from_string($time )
    {
        return new static( DateTime::time_to_seconds( $time ) );
    }

    /**
     * Returns time formatted with date_i18n.
     *
     * @return string
     */
    public function format_i18n_time()
    {
        return date_i18n( get_option( 'time_format' ), $this->time );
    }

    /**
     * Convert time to WP time zone.
     *
     * @return static
     */
    public function to_wp_tz()
    {
        return new static( $this->time - static::client_tz_offset() + static::wp_tz_offset() );
    }

    /**
     * Convert time to client time zone.
     *
     * @return static
     */
    public function to_client_tz()
    {
        return new static( $this->time - static::wp_tz_offset() + static::client_tz_offset() );
    }

    /**
     * Get WP time zone offset.
     *
     * @return int
     */
    protected static function wp_tz_offset()
    {
        if ( static::$wp_timezone_offset === null ) {
            static::$wp_timezone_offset = (int) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
        }

        return static::$wp_timezone_offset;
    }

    /**
     * Get client time zone offset.
     *
     * @return int
     */
    protected static function client_tz_offset()
    {
        if ( static::$client_timezone_offset === null ) {
            static::$client_timezone_offset = static::wp_tz_offset();
        }

        return static::$client_timezone_offset;
    }

    /**
     * Get value.
     *
     * @return int
     */
    public function value()
    {
        return $this->time;
    }

    /**
     * Tells whether two times are equal.
     *
     * @param Time $time
     * @return bool
     */
    public function eq( Time $time )
    {
        return $this->time == $time->value();
    }

    /**
     * Tells whether two times are not equal.
     *
     * @param Time $time
     * @return bool
     */
    public function neq( Time $time )
    {
        return $this->time != $time->value();
    }

    /**
     * Tells whether one time is less than another.
     *
     * @param Time $time
     * @return bool
     */
    public function lt( Time $time )
    {
        return $this->time < $time->value();
    }

    /**
     * Tells whether one time is less or equal than another.
     *
     * @param Time $time
     * @return bool
     */
    public function lte( Time $time )
    {
        return $this->time <= $time->value();
    }

    /**
     * Tells whether one time is greater than another.
     *
     * @param Time $time
     * @return bool
     */
    public function gt( Time $time )
    {
        return $this->time > $time->value();
    }

    /**
     * Tells whether one time is greater or equal than another.
     *
     * @param Time $time
     * @return bool
     */
    public function gte( Time $time )
    {
        return $this->time >= $time->value();
    }

    /**
     * Computes difference between two times.
     *
     * @param Time $time
     * @return int
     */
    public function diff( Time $time )
    {
        return $this->time - $time->value();
    }

    /**
     * Modify time.
     *
     * @param int $value
     * @return static
     */
    public function modify( $value )
    {
        if ( $value ) {
            return new static( $this->time + $value );
        }

        return $this;
    }
}