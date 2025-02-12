<?php
namespace Bookme\Inc\Mains\Functions;

/**
 * Class Date
 */
class Date
{
    /** @var string */
    protected static $wp_timezone = null;
    /** @var string */
    public static $client_timezone = null;

    /** @var \DateTime */
    protected $datetime;

    /**
     * Constructor.
     * @param \DateTime $datetime
     */
    public function __construct( \DateTime $datetime )
    {
        $this->datetime = $datetime;
    }

    /**
     * Create Date with the current time WP time zone.
     *
     * @return static
     */
    public static function now()
    {
        return new static( date_timestamp_set( date_create( static::wp_tz() ), time() ) );
    }

    /**
     * Create Date from string in WP time zone.
     *
     * @param string $date_string  Format Y-m-d H:i[:s]
     * @return static
     */
    public static function from_string($date_string )
    {
        return new static( date_create( $date_string . ' ' . static::wp_tz() ) );
    }

    /**
     * Create Date from string in client time zone.
     *
     * @param string $date_string  Format Y-m-d H:i[:s]
     * @return static
     */
    public static function from_string_in_client_tz($date_string )
    {
        return new static( date_create( $date_string . ' ' . static::client_tz() ) );
    }

    /**
     * Returns date formatted according to given format.
     *
     * @param string $format
     * @return string
     */
    public function format( $format )
    {
        return $this->datetime->format( $format );
    }

    /**
     * Returns date formatted with date_i18n according to given format.
     *
     * @param string $format
     * @return string
     */
    public function format_i18n($format )
    {
        return date_i18n( $format, $this->datetime->getTimestamp() + $this->datetime->getOffset());
    }

    /**
     * Returns date formatted with date_i18n.
     *
     * @return string
     */
    public function format_i18n_date()
    {
        return $this->format_i18n( get_option( 'date_format' ) );
    }

    /**
     * Returns time formatted with date_i18n.
     *
     * @return string
     */
    public function format_i18n_time()
    {
        return $this->format_i18n( get_option( 'time_format' ) );
    }

    /**
     * Convert date to given timezone.
     *
     * @param string $timezone
     * @return static
     */
    public function to_tz($timezone)
    {
        return new static( date_timestamp_set( date_create( $timezone ), $this->datetime->getTimestamp() ) );
    }

    /**
     * Convert date to WP time zone.
     *
     * @return static
     */
    public function to_wp_tz()
    {
        return $this->to_tz(static::wp_tz());
    }

    /**
     * Convert date to client time zone.
     *
     * @return static
     */
    public function to_client_tz()
    {
        return $this->to_tz(static::client_tz());
    }

    /**
     * Get WP time zone.
     *
     * @return string
     */
    protected static function wp_tz()
    {
        if ( static::$wp_timezone === null ) {
            static::$wp_timezone = System::get_wp_time_zone();
        }

        return static::$wp_timezone;
    }

    /**
     * Get client time zone.
     *
     * @return string
     */
    protected static function client_tz()
    {
        if ( static::$client_timezone === null ) {
            static::$client_timezone = static::wp_tz();
        }

        return static::$client_timezone;
    }

    /**
     * Get value.
     *
     * @return \DateTime
     */
    public function value()
    {
        return $this->datetime;
    }

    /**
     * Tells whether two dates are equal.
     *
     * @param Date $date
     * @return bool
     */
    public function eq( Date $date )
    {
        return $this->datetime == $date->value();
    }

    /**
     * Tells whether two dates are not equal.
     *
     * @param Date $date
     * @return bool
     */
    public function neq( Date $date )
    {
        return $this->datetime != $date->value();
    }

    /**
     * Tells whether one date is less than another.
     *
     * @param Date $date
     * @return bool
     */
    public function lt( Date $date )
    {
        return $this->datetime < $date->value();
    }

    /**
     * Tells whether one date is less or equal than another.
     *
     * @param Date $date
     * @return bool
     */
    public function lte( Date $date )
    {
        return $this->datetime <= $date->value();
    }

    /**
     * Tells whether one date is greater than another.
     *
     * @param Date $date
     * @return bool
     */
    public function gt( Date $date )
    {
        return $this->datetime > $date->value();
    }

    /**
     * Tells whether one date is greater or equal than another.
     *
     * @param Date $date
     * @return bool
     */
    public function gte( Date $date )
    {
        return $this->datetime >= $date->value();
    }

    /**
     * Computes difference between two dates.
     *
     * @param Date $date
     * @return int
     */
    public function diff( Date $date )
    {
        return $this->datetime->getTimestamp() - $date->value()->getTimestamp();
    }

    /**
     * Modify date.
     *
     * @param mixed $value
     * @return static
     */
    public function modify( $value )
    {
        if ( is_numeric( $value ) ) {
            if ( $value ) {
                return new static( date_modify( clone $this->datetime, (int) $value . ' seconds' ) );
            }
        } elseif ( is_string( $value ) ) {
            return new static( date_modify( clone $this->datetime, $value ) );
        }

        return $this;
    }
}